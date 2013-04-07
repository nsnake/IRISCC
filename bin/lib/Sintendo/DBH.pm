package Sintendo::DBH;
use strict;
use Exporter;
use DBI;
use version; our $VERSION = qv(1.2.0);
@Sintendo::DBH::ISA = qw (Exporter DBI);

sub new {
    my ( $class, $parameter ) = @_;
    $class = ( ref $class ) || $class || __PACKAGE__;
    my $self = {};
    bless $self, $class;
    $self->_db_con($parameter) if $parameter;
    return $self;
}

#1.1 使用新的关键字drive来代替驱动选择
#    unxi-socket支持
sub _db_con {
    my ( $self, $parameter ) = @_;
    my %dsn = (
	'mysql' =>
"DBI:mysql:$parameter->{'dbname'}:$parameter->{'server'}:$parameter->{port}",
	'mssql' => "DBI:mSQL:$parameter->{'dbname'}:$parameter->{'server'}",
	'access' =>
"DBI:ODBC:driver=Microsoft Access Driver (*.mdb);dbq=$parameter->{'dbname'}",
    );

    #不可用database作为关键字，否则会出现mysql的错误
    $parameter->{'drive'} = $parameter->{'drive'} || 'mysql';
    $dsn{ $parameter->{'drive'} } .= ';' . $parameter->{'mysql_socket'}
      if ( $parameter->{'drive'} eq 'mysql' && $parameter->{'mysql_socket'} );
    $self->{dbh} = DBI->connect(
	$dsn{ $parameter->{'drive'} }, $parameter->{'user'},
	$parameter->{'password'},      $parameter
    ) || die $DBI::errstr;

    #自定编码格式
    $self->{dbh}->do(qq/SET NAMES $parameter->{'names'}/)
      if ( $parameter->{'drive'} eq 'mysql' && $parameter->{'names'} );
}

#返回原始句柄
sub return_dbh {
    my ($self) = @_;
    return $self->{dbh};
}

#对SQL语句进行预编译,2007.5.16完成批量添加数据
#2007.7.21修正$array_datas为空时的报错
#如果成功返回1
sub db_prepare {
    my ( $self, $sql, $array_datas ) = @_;
    $self->{sth} = $self->{dbh}->prepare($sql);
    if ( defined $array_datas && $array_datas =~ /ARRAY/ ) {
	foreach my $data (@$array_datas) {
	    $self->{sth}->execute(@$data);
	}
    }
    else { $self->{sth}->execute(); }
}

# 返回被插入的数据所在的行数，在事物的情况下返回的数值是准确的
sub return_line {
    my ($self) = @_;
    return $self->{dbh}->{'mysql_insertid'};
}

sub db_row_arrayref {
    my ( $self, $sql, $procedure ) = @_;
    $self->db_prepare($sql);

    unless ( $procedure->{proc} ) {
	return $self->{sth}->fetchrow_arrayref();
    }
    else {
	my @array;
	do {
	    while ( my $temp = $self->{sth}->fetchrow_arrayref() ) {
		push @array, [@$temp];
	    }
	} until ( !$self->{sth}->more_results );
	return \@array;
    }
}

sub db_row_hashref {
    my ( $self, $sql, $procedure ) = @_;
    $self->db_prepare($sql);

    # NAME_lc 强制小写
    # NAME_uc 强制大写
    unless ( $procedure->{proc} ) {
	return $self->{sth}->fetchrow_hashref();
    }
    else {
	my @array;
	do {
	    while ( my $temp = $self->{sth}->fetchrow_hashref() ) {
		push @array, {%$temp};
	    }
	} until ( !$self->{sth}->more_results );
	return \@array;
    }
}

sub db_all_arrayref {
    my ( $self, $sql, $parameter ) = @_;
    $self->db_prepare($sql);
    return $self->{sth}->fetchall_arrayref($parameter) || $DBI::errstr;
}

#返回数组引用，所获取的数据为hash表示
sub db_assocref {
    my ( $self, $sql, $result_type ) = @_;
    $self->db_prepare($sql);
    my @array;
    if ($result_type) {
	do {
	    while ( my $data = $self->{sth}->fetchrow_arrayref() ) {
		push @array, $data;
	    }
	} until ( !$self->{sth}->more_results );
    }
    else {
	do {
	    while ( my $data = $self->{sth}->fetchrow_hashref() ) {
		push @array, $data;
	    }
	} until ( !$self->{sth}->more_results );
    }

    return \@array;
}

#查询指定字段中对应的指定数据的列
sub db_all_hashref {
    my ( $self, $sql, $parameter ) = @_;
    $self->db_prepare($sql);
    return $self->{sth}->fetchall_hashref($parameter) || $DBI::errstr;
}

#sql费时统计
sub db_do_profile {
    my ( $self, $sql ) = @_;
    $self->db_do("SET profiling = 1");
    my @resp = $self->db_do($sql);
    $self->db_do("SET profiling = 0");
    my $prof =
      $self->{dbh}->selectall_arrayref( "SHOW profile ALL", { Slice => {} } );
    return wantarray ? ( $prof, @resp ) : $prof;
}

sub AUTOLOAD {
    my $self = shift;
    my $name = our $AUTOLOAD;
    return if $name =~ /::DESTROY$/;
    $name =~ /.*::(\w*)/;
    $name = $1;
    return $self->{dbh}->$name(@_) || $DBI::errstr;
}

1;

=head1 NAME

Sintendo::DBH 数据库常用操作支持

=head1 VERSION

1.0.0

=head1 存储过程支持

如需存储过程支持需DBD::MYSQL在3.002_5以上版本

=head1 方法

=head2 new({参数})

进行数据连接
       drive    数据库类型:可选,mysql,msql,access(默认:mysql)
       dataname     数据库名
	   server       数据库地址
	   port         数据库端口(默认:3306)
	   user         数据库用户名
	   password     数据库密码
	   names        数据库编码,UTF8，gb2312等.防止MYSQL编码可出现的乱码问题,MYSQL4.10以下版本不支持该属性
	   DeBug        开启除错功能(0,1)
	   AutoCommit   数据自动提交(0,1) 如果开启将不可使用事务功能
	   PrintError   继承于DBI 
       RaiseError   继承于DBI
	   可以直接加入DBI可用的属性

my $dbh=Sintendo::DBH->new({
			      drive     => 'access',
			      dbname    => 'dbname',
			      server    => 'serverIP',
			      port      => 3306,
			      user      => 'root',
			      password  => '',
			      AutoCommit=> 1,
			      PrintError=> 1,
			      RaiseError=> 1,
			      names     => 'UTF8',
			    });

=head2 return_dbh()

返回一个dbh句柄
my $dhb_clone = $dbh->return_dbh();

=head2 return_line()

返回被插入的数据所在的行数.
在执行插入操作后，数据提交前使用(如果启用事物).
在事务开启的状态下返回的数值是准确的.
my $dhb_clone = $dbh->return_line();

=head2 db_prepare()

执行SQL语句,并且可完成多条数据的插入,如果成功返回真值
$dbh->db_prepare($SQL语句,\@数据集)

my @records = (
		[  "Larry Wall" ],
		[  "Tim Bunce" ],
		[  "Randal Schwartz"],
		[ "Doug MacEachern"]

	      );
   $dbh->db_prepare('INSERT INTO table (value) VALUES(?)',\@records);


=head2 db_row_arrayref()

单行检索,返回数组引用
$dbh->db_row_arrayref($SQL语句,[{proc=>1}])

my $datas=$dbh->db_row_arrayref ('sql');
   return $datas->[1];

proc 当调用的是返回多数据的存储过程时需要设置为1.
my $datas=$dbh->db_row_arrayref ('call somproc()',{proc=>1});
   return $datas->[1]->[1];

=head2 db_row_hashref()

单行检索,返回HASH引用
$dbh->db_row_hashref($SQL语句,[{proc=>1}])

my $datas=$dbh->db_row_hashref ('sql');
   return $datas->{name};

proc 当调用的是返回多数据的存储过程时需要设置为1.
my $datas=$dbh->db_row_hashref ('call somproc()',{proc=>1});
   return $datas->[1]->{name};

=head2 db_all_arrayref()

批量查询检索,不可用于有多条数据返回的存储过程,当有参数时则表示只取里面相应的数据
$dbh->db_all_arrayref($SQL语句,[参数])

my $datas=$dbh->db_all_arrayref('sql');
   foreach my $row (@$datas){
			     my ($nu1,$nu2) = @$row;
   }

参数:
    HASH  {字段名1 => 1, 字段名2 => 0}
	foreach my $row (@$datas){
			      $row->{字段名1}，$row->{字段名2}
    }

    ARRAY [0,2]   0,2代表该字段在表中的排列位置
    foreach my $row (@$datas){
			      $row->[0]，$row->[1]
    }

=head2 db_all_hashref() 

查询在指定字段中是否是有与指定数据一样列,如果有则返会该列,否则返回空。该方法不可用于有多条数据返回的存储过程
$dbh->db_all_hashref($SQL语句,[参数])
my $datas = $dbh->db_all_hashref('sql');

my $datas = $dbh->db_all_hashref('sql',['字段']);
   print $datas->{'10'}->{time}
   这代表取得字段数值为10的time字段的值

也支持多个字段
db_all_hashref($SQL语句,[ qw(foo bar) ]);
$datas ->{42}->{38}->{baz}

=head2 db_assocref([int result_type])

批量查询检索,以数组引用的方式返回数据。
当result_type为1:行数据表现形式为数组引用
foreach(@{$dbh->db_assocref($sql,1)}){
         print $_->[0];
}

当result_type为0:行数据表现形式为hash引用
foreach(@{$dbh->db_assocref($sql)}){
         print $_->{KEY};
}
默认result_type为0

=head2 其它

其它方法继承DBI部分
$dbh->commit()   对数据库事务进行提交操作
$dbh->rollback() 对数据库事务进行回滚操作
事物需要INNODB
$dbh->ping() 
