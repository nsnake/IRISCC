#!/usr/bin/perl
#
#	Freeiris2 -- An Opensource telephony project.
#       Copyright (C) 2013, CGI.NET <loveme1314@gmail.com>
#	Copyright (C) 2005 - 2009, Sun bing.
#	Sun bing <hoowa.sun@gmail.com>
#
#	See http://www.freeiris.org for more information about
#	the Freeiris project.
#
#	This program is free software, distributed under the terms of
#	the GNU General Public License Version 2. See the LICENSE file
#	at the top of the source tree.
#
#	Freeiris2 -- 开源通信系统
#	本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
#	请查阅LICENSE文件。
#
#
#   $Id$
#

#=================================================================
# initialization preload and construction
#=================================================================
$|=1;
use FindBin qw($Bin);

my	$INPUT = STDIN;
my	$OUTPUT = STDOUT;
my	$ERRPUT = STDERR;
my	$VERSION = 2.5;

#=================================================================
# call command args
#=================================================================
if (defined$ARGV[0] && $ARGV[0] eq '--help') {
	&help();
} elsif(defined$ARGV[0] && $ARGV[0] eq '--install') {
	&install();
} elsif(defined$ARGV[0] && $ARGV[0] eq '--setup') {
	&setup();
#} elsif(defined$ARGV[0] && $ARGV[0] eq '--autosetup') {
#	&autosetup();
} elsif(defined$ARGV[0] && $ARGV[0] eq '--uninstall') {
	&uninstall();
}else {
	&help();
}
exit;

#=================================================================
# display help
#=================================================================
sub help
{
print qq~
  Freeiris2 Install Stage by CGI.NET $VERSION

syntax:
  install freeiris2  :    ./install.pl --install
  setup freeiris2    :    ./install.pl --setup
  uninstall freeiris2:    ./install.pl --uninstall
  this help          :    ./install.pl --help
  this help          :    ./install.pl
~;
exit;
}


#=================================================================
# Install startup 
#=================================================================
sub install
{
my	$type = shift;

	#---------------------------------------------------------------------------------------------------------prerequest checking
	&println('error',"Your are not root") if ($< ne '0');
	&println(undef,qq~
 Freeiris2 Install Stage by CGI.NET $VERSION
CGI.NET <loveme1314\@gmail.com>
----------------------------------------------------------
WARNING:
  This is free Open Source software.
  IT COMES WITHOUT WARRANTY OF ANY KIND.
----------------------------------------------------------~);

	#环境检测
	&println('step',"Prerequest checking.........");
my	%instvar=&prequest();

	#安装文件部分
	&println('step',"Install files.........");
	&install_process(\%instvar);

	#提示信息
#	print `cat $instvar{'install_target'}/contrib/rpmversion/alert`;
	print qq~
#==============================================#
#    CGI.NET <loveme1314\@gmail.com>           #
#    www.freeiris.org                          #
#==============================================#
~;

	#执行设置流程
	&setup();

exit;
}

sub prequest
{
my	%instvar;

	#---------------------------------------------checking
	#检测是否redhat系列
	if (!-e"/etc/redhat-release") {
		$instvar{'os'}='unknown';

	#检测是否centos5系列
	} else {
		if (`cat /etc/redhat-release` =~ /^CentOS Linux release 6.0/) {
			$instvar{'os'}='c5x';
		} else {
			$instvar{'os'}='redhat';
		}
	}

	#检测是否关闭selinux
	foreach  (split(/\n/,`cat /etc/sysconfig/selinux`)) {
		if ($_ =~ /^SELINUX\=enforcing/) {
			&println('error',"Your need to disable selinux by manual operation");
		} elsif ($_ =~ /^SELINUX\=permissive/) {
			&println('error',"Your need to disable selinux ---> \'setup\'");
		}
	}

	#如果不是c5x或unknown
	if ($instvar{'os'} eq 'unknown') {
		&println('error',"Your system is ".$instvar{'os'}." , freeiris2 install script not support!");
	} elsif ($instvar{'os'} ne 'c5x') {
		&println('failed',"Your system is ".$instvar{'os'}." we don't know how complitable, but install still continue.");
	}

	#测试系统RPM包安装量
	foreach  (('httpd','mysql-server','mysql','mysql-devel','php','php-mysql','perl','libdbi-dbd-mysql','perl-libwww-perl')) {
		if (`rpm -q $_` =~ /is not installed/) {
			&println('failed',"Maybe you need install $_ ---> \'yum install $_\'");
			exit;
		}
	}

	#测试dahdi
	&println('error',"Your need to install dahdi driver.") if (!-e"/usr/sbin/dahdi_cfg");
	#测试libpri
	&println('error',"Your need to install libpri driver.") if (!-e"/usr/lib/libpri.so");
	#测试asterisk是否安装
	&println('error',"Your need to install asterisk.") if (!-e"/usr/sbin/asterisk");
	#测试asterisk-addons是否安装
	&println('error',"Your need to install asterisk-cdr_mysql.") if (!-e"/usr/lib/asterisk/modules/cdr_mysql.so");

	#测试是否已经安装了
	&println('error',"Your need to remove your old install, try to use 'install.pl --uninstall'") if (-d "/freeiris2/" || -e "/etc/init.d/fri2d");

	#启动检测部分
my	$checkisrun;
	#检测是否启动了httpd
	$checkisrun=0;
	foreach  (split(/\n/,`ps -o pid,command -C 'httpd'`)) {
		next if ($_ =~ /$$\s/);	# no self
		next if ($_ !~ /httpd/);	# no other line
		$checkisrun=1;
		last;
	}
	if (!$checkisrun) {
		&println('error',"Your need to run httpd ! ---> \'/etc/init.d/httpd start\'");
	}
	#检测是否启动了mysql
	$checkisrun=0;
	foreach  (split(/\n/,`ps -o pid,command -C 'mysqld'`)) {
		next if ($_ =~ /$$\s/);	# no self
		next if ($_ !~ /mysqld/);	# no other line
		$checkisrun=1;
		last;
	}
	if (!$checkisrun) {
		&println('error',"Your need to run mysqld ! ---> \'/etc/init.d/mysqld start\'");
	}

	#是否有指定的配置/etc/httpd/conf/httpd.conf
	&println('error',"Can't find /etc/httpd/conf/httpd.conf") if (!-e"/etc/httpd/conf/httpd.conf");
	&println('error',"Can't find /var/www/html/") if (!-d"/var/www/html/");

	#设置安装路径
	$instvar{'install_target'}='/freeiris2/';
	$instvar{'asterisk_etc'}='/etc/asterisk/';

return(%instvar);
}


#
# 安装流程,本流程不会做任何询问,只会复制文件设置权限,如果遇到不可逾越错误就直接结束
#
sub install_process
{
my	$instvar = shift;


	#---------------主目录是否存在
	if (!-d$instvar->{'install_target'}) {
		mkdir($instvar->{'install_target'}) or die "$!";
		&println('response',"create folder --> ".$instvar->{'install_target'});
	}
	if (!-d$instvar->{'asterisk_etc'}) {
		mkdir($instvar->{'asterisk_etc'}) or die "$!";
		&println('response',"create folder --> ".$instvar->{'asterisk_etc'});
	}

	#---------------进行/freeiris2/文件复制
	&println('response',"Copying $Bin/* ---> ".$instvar->{'install_target'}."......");
	system("cp -af $Bin/* ".$instvar->{'install_target'});

	#---------------设置软链接到/etc/下名字
	unlink("/etc/freeiris2");
	system("ln -s ".$instvar->{'install_target'}."/etc /etc/freeiris2");

	#---------------修整asterisk安装后的目录和文件
	#替换asterisk配置
	system("rm -rf /etc/asterisk/*");
	system("cp -af ".$instvar->{'install_target'}."/contrib/astetc/*.* /etc/asterisk/");
	#替换asterisk语音
	system("mkdir /var/lib/asterisk/sounds/") if (!-d"/var/lib/asterisk/sounds/");
	system("rm -rf /var/lib/asterisk/sounds/*");
	system("cp -af ".$instvar->{'install_target'}."/var/lib/sounds/. /var/lib/asterisk/sounds/");
	system("mkdir /var/lib/asterisk/moh/") if (!-d"/var/lib/asterisk/moh/");
	system("rm -rf /var/lib/asterisk/moh/*");
	system("cp -af ".$instvar->{'install_target'}."/var/lib/moh/. /var/lib/asterisk/moh/");
	#创建录音和语音目录
	system("mkdir /var/spool/asterisk/voicemail/freeiris/");
	system("mkdir /var/spool/asterisk/ivrmenu/");

	#---------------添加权限
	# 设置asterisk部分的可写权限
	system("chmod -R 777 /etc/asterisk/");
	system("chmod -R 777 /var/lib/asterisk/sounds/");
	system("chmod -R 777 /var/lib/asterisk/moh/");
	system("chmod -R 777 /var/spool/asterisk/");
	# 设置freeiris2部分的可写权限
	system("chmod -R 777 ".$instvar->{'install_target'}."/etc/");
	system("chmod -R 777 ".$instvar->{'install_target'}."/webclient/templates_c");
	system("chmod -R 777 ".$instvar->{'install_target'}."/logs/");
	system("chmod -R 755 ".$instvar->{'install_target'}."/bin/");

	#---------------安装启动文件(未启动,启动工作由初始化部分完成)
	system("cp -avf ".$instvar->{'install_target'}."/contrib/init.d/fri2d /etc/init.d/");
	system("cp -avf ".$instvar->{'install_target'}."/contrib/init.d/hardware /etc/init.d/");

	#---------------设置apache参数
	unlink("/etc/httpd/conf.d/freeiris.httpd.conf");
	system("ln -s ".$instvar->{'install_target'}."/etc/freeiris.httpd.conf /etc/httpd/conf.d/");

	#---------------设置sudoers系统
	open(SDV,"/etc/sudoers");
my	@sudoers=<SDV>;
	close(SDV);
	open(SDV,">/etc/sudoers");
	foreach  (@sudoers) {
		chomp($_);
		if ($_=~/^Defaults    requiretty/) {
			print SDV "#Defaults    requiretty\n";
		} else {
			print SDV $_."\n";
		}
	}
	close(SDV);
	#增加新sudoers指令
	system("cat ".$instvar->{'install_target'}."/contrib/sudoers.append >> /etc/sudoers");

return(1);
}


sub setup
{
	&println('error',"Your are not root") if ($< ne '0');
	&println(undef,qq~
----------------------------------------------------------
Setup Freeiris2 Please Wait......
----------------------------------------------------------
~);

my	%setvar;
	$setvar{'install_target'}='/freeiris2';

	#检测是否应该执行setup
	&println('error',"Your freeiris2 doesn't need setup.") if (!-e"$setvar{'install_target'}/NOTDONE");

	#检测是否启动了mysql
my	$checkisrun=0;
	foreach  (split(/\n/,`ps -o pid,command -C 'mysqld'`)) {
		next if ($_ =~ /$$\s/);	# no self
		next if ($_ !~ /mysqld/);	# no other line
		$checkisrun=1;
		last;
	}
	if (!$checkisrun) {
		&println('error',"Your need to run mysqld ! ---> \'/etc/init.d/mysqld start\'");
	}

	#---------------------------------------------ask questions
	&println('step',"Setup your Mysql Database.............");

	#加载库
	require DBI;

my	$dbh;
	&println('response',"Try To mysql with user: root pass: null host: localhost");
	#尝试以默认帐户登陆mysql失败
	if (!eval {$dbh = DBI->connect("DBI:mysql:database=;host=127.0.0.1;port=3306",'root','')}) {

		while (1) {
			&println('input',"Please input Mysql address (default 127.0.0.1)?");
		my	$dbhost = <$INPUT>;	chomp($dbhost);
			$dbhost = '127.0.0.1' if ($dbhost eq '' || $dbhost eq 'localhost');

			&println('input',"Please input Mysql port (default 3306)?");
		my	$dbport = <$INPUT>;	chomp($dbport);
			$dbport = 3306 if ($dbport eq '');

		my	$dbname;
			while (1) {
				&println('input',"Please input Mysql databasename ?");
				$dbname = <$INPUT>;	chomp($dbname);
				last if ($dbname ne '');
			}

		my	$dbuser;
			while (1) {
				&println('input',"Please input Mysql username ?");
				$dbuser = <$INPUT>;	chomp($dbuser);
				last if ($dbuser ne '');
			}

		my	$dbpass;
			&println('input',"Please input Mysql user password ?");
			$dbpass = <$INPUT>;	chomp($dbpass);
			
			#try to connect
			&println('response',"Try to Connect MySQL Server......");
		my	$trycon = `/usr/bin/mysql --host $dbhost --port=$dbport --database=$dbname --user=$dbuser --password=$dbpass --silent --execute='select "OK"'`;

			if ($trycon =~ /^OK/) {
				$setvar{'dbhost'}=$dbhost;
				$setvar{'dbuser'}=$dbuser;
				$setvar{'dbpasswd'}=$dbpass;
				$setvar{'dbname'}=$dbname;
				$setvar{'dbport'}=$dbport;
				last;
			}

			&println('response',"Connect MySQL Server Failed !......");

		}

	#尝试默认帐户登陆成功
	} else {

		$setvar{'dbhost'}='127.0.0.1';
		$setvar{'dbuser'}='root';
		$setvar{'dbpasswd'}='';
		$setvar{'dbport'}='3306';
		$setvar{'dbname'}='freeiris2';
		#尝试连接库
		while (1) {
			if ($setvar{'dbname'} eq '' || !$dbh->do("create database ".$setvar{'dbname'})) {
				&println('input',"Please input Database name to create ?");
				$setvar{'dbname'} = <$INPUT>;
				chomp($setvar{'dbname'});
				next;
			} else {
				last;
			}
		}
		$dbh->disconnect();
	}

	#---------------初始化数据库部分的基本参数和基本系统设置
	&println('response',"Initlization database......");

	#---------------生成表结构
	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
		" --port=".$setvar{'dbport'}.
		" --user=".$setvar{'dbuser'}.
		" --password=".$setvar{'dbpasswd'}.
		" --one-database ".$setvar{'dbname'}.
		" < ".$setvar{'install_target'}."/contrib/createdb.sql");
	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
		" --port=".$setvar{'dbport'}.
		" --user=".$setvar{'dbuser'}.
		" --password=".$setvar{'dbpasswd'}.
		" --one-database ".$setvar{'dbname'}.
		" < ".$setvar{'install_target'}."/contrib/initdb.sql");

	#---------------设置fri2和asterisk的数据库参数
	open(SDV,"/etc/freeiris2/freeiris.conf") or die "$!";
my	@freeiris=<SDV>;
	close(SDV);
	open(SDV,">/etc/freeiris2/freeiris.conf");
	foreach  (@freeiris) {
		chomp($_);
		if ($_=~/^dbhost/) {
			print SDV "dbhost=".$setvar{'dbhost'}."\n";
		} elsif ($_=~/^dbuser/) {
			print SDV "dbuser=".$setvar{'dbuser'}."\n";
		} elsif ($_=~/^dbpasswd/) {
			print SDV "dbpasswd=".$setvar{'dbpasswd'}."\n";
		} elsif ($_=~/^dbname/) {
			print SDV "dbname=".$setvar{'dbname'}."\n";
		} elsif ($_=~/^dbport/) {
			print SDV "dbport=".$setvar{'dbport'}."\n";
#		} elsif ($_=~/^dbsock/) {
#			print SDV "dbsock=".$setvar{'dbsock'}."\n";
		} else {
			print SDV $_."\n";
		}
	}
	close(SDV);
	open(SDV,"/etc/asterisk/cdr_mysql.conf") or die "$!";
my	@cdr_mysql=<SDV>;
	close(SDV);
	open(SDV,">/etc/asterisk/cdr_mysql.conf");
	foreach  (@cdr_mysql) {
		chomp($_);
		if ($_=~/^hostname\=/) {
			print SDV "hostname=".$setvar{'dbhost'}."\n";
		} elsif ($_=~/^user\=/) {
			print SDV "user=".$setvar{'dbuser'}."\n";
		} elsif ($_=~/^password\=/) {
			print SDV "password=".$setvar{'dbpasswd'}."\n";
		} elsif ($_=~/^dbname\=/) {
			print SDV "dbname=".$setvar{'dbname'}."\n";
		} elsif ($_=~/^port\=/) {
			print SDV "port=".$setvar{'dbport'}."\n";
#		} elsif ($_=~/^sock\=/) {
#			print SDV "sock=".$setvar{'dbsock'}."\n";
		} else {
			print SDV $_."\n";
		}
	}
	close(SDV);


	#---------------设置为html默认页
#	&println('input',"Set to html default(yes/no)?");
#my	$input = <$INPUT>;	chomp($input);
#	if ($input !~ /no/) {
		system("cp -af ".$setvar{'install_target'}."/webclient/index.html /var/www/html/");
#	}

	#---------------设置freeiris2的服务为启动项
	&println('response',"set freeiris2 services");
	sleep(1);
	system("chkconfig --add fri2d");
	system("chkconfig --add hardware");
	sleep(1);

	#---------------执行example的安装
	&println('response',"install example");
	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
		" --port=".$setvar{'dbport'}.
		" --user=".$setvar{'dbuser'}.
		" --password=".$setvar{'dbpasswd'}.
		" --one-database ".$setvar{'dbname'}.
		" < ".$setvar{'install_target'}."/contrib/example/example.sql");
	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.queues_list.conf /etc/asterisk/queues_list.conf");
	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.sip_exten.conf /etc/asterisk/sip_exten.conf");
	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.extensions_hints.conf /etc/asterisk/extensions_hints.conf");
	system("chmod -R 777 /etc/asterisk/");

	#---------------设置安装全部完成
	&println('response',"all done!");
	unlink("$setvar{'install_target'}/NOTDONE");

	&println('response',"Please Reboot your system!");

exit;
}

#sub autosetup
#{
#	&println('error',"Your are not root") if ($< ne '0');
#
#my	%setvar;
#	$setvar{'install_target'}='/freeiris2';
#	$setvar{'dbhost'}='127.0.0.1';
#	$setvar{'dbuser'}='root';
#	$setvar{'dbpasswd'}='';
#	$setvar{'dbname'}='freeiris2';
#	$setvar{'dbport'}='3306';
#
#	#检测是否应该执行setup
#	&println('error',"Your freeiris2 doesn't need setup.") if (!-e"$setvar{'install_target'}/NOTDONE");
#
#	#启动mysql
#	system("/etc/init.d/mysqld start");
#	sleep(2);
#my	$checkisrun=0;
#	foreach  (split(/\n/,`ps -o pid,command -C 'mysqld'`)) {
#		next if ($_ =~ /$$\s/);	# no self
#		next if ($_ !~ /mysqld/);	# no other line
#		$checkisrun=1;
#		last;
#	}
#	if (!$checkisrun) {
#		&println('error',"Your need to run mysqld ! ---> \'/etc/init.d/mysqld start\'");
#	}
#
#	#---------------初始化数据库部分的基本参数和基本系统设置
#	`/usr/bin/mysql --host $setvar{'dbhost'} --port=$setvar{'dbport'} --user=$setvar{'dbuser'} --password=$setvar{'dbpass'} --silent --execute='create database $setvar{'dbname'}'`;
#
#	#---------------生成表结构
#	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
#		" --port=".$setvar{'dbport'}.
#		" --user=".$setvar{'dbuser'}.
#		" --password=".$setvar{'dbpasswd'}.
#		" --one-database ".$setvar{'dbname'}.
#		" < ".$setvar{'install_target'}."/contrib/createdb.sql");
#	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
#		" --port=".$setvar{'dbport'}.
#		" --user=".$setvar{'dbuser'}.
#		" --password=".$setvar{'dbpasswd'}.
#		" --one-database ".$setvar{'dbname'}.
#		" < ".$setvar{'install_target'}."/contrib/initdb.sql");
#
#	#---------------设置fri2和asterisk的数据库参数
#	open(SDV,"/etc/freeiris2/freeiris.conf") or die "$!";
#my	@freeiris=<SDV>;
#	close(SDV);
#	open(SDV,">/etc/freeiris2/freeiris.conf");
#	foreach  (@freeiris) {
#		chomp($_);
#		if ($_=~/^dbhost/) {
#			print SDV "dbhost=".$setvar{'dbhost'}."\n";
#		} elsif ($_=~/^dbuser/) {
#			print SDV "dbuser=".$setvar{'dbuser'}."\n";
#		} elsif ($_=~/^dbpasswd/) {
#			print SDV "dbpasswd=".$setvar{'dbpasswd'}."\n";
#		} elsif ($_=~/^dbname/) {
#			print SDV "dbname=".$setvar{'dbname'}."\n";
#		} elsif ($_=~/^dbport/) {
#			print SDV "dbport=".$setvar{'dbport'}."\n";
##		} elsif ($_=~/^dbsock/) {
##			print SDV "dbsock=".$setvar{'dbsock'}."\n";
#		} else {
#			print SDV $_."\n";
#		}
#	}
#	close(SDV);
#	open(SDV,"/etc/asterisk/cdr_mysql.conf") or die "$!";
#my	@cdr_mysql=<SDV>;
#	close(SDV);
#	open(SDV,">/etc/asterisk/cdr_mysql.conf");
#	foreach  (@cdr_mysql) {
#		chomp($_);
#		if ($_=~/^hostname/) {
#			print SDV "hostname=".$setvar{'dbhost'}."\n";
#		} elsif ($_=~/^user/) {
#			print SDV "user=".$setvar{'dbuser'}."\n";
#		} elsif ($_=~/^password/) {
#			print SDV "password=".$setvar{'dbpasswd'}."\n";
#		} elsif ($_=~/^dbname/) {
#			print SDV "dbname=".$setvar{'dbname'}."\n";
#		} elsif ($_=~/^port/) {
#			print SDV "port=".$setvar{'dbport'}."\n";
##		} elsif ($_=~/^sock/) {
##			print SDV "sock=".$setvar{'dbsock'}."\n";
#		} else {
#			print SDV $_."\n";
#		}
#	}
#	close(SDV);
#
#	#---------------设置为html默认页
#	system("cp -af ".$setvar{'install_target'}."/webclient/index.html /var/www/html/");
#
#	#---------------设置freeiris2的服务为启动项
#	sleep(1);
#	system("chkconfig --add fri2d");
#	system("chkconfig --add hardware");
#	sleep(1);
#
#	#---------------执行example的安装
#	system("/usr/bin/mysql --host=".$setvar{'dbhost'}.
#		" --port=".$setvar{'dbport'}.
#		" --user=".$setvar{'dbuser'}.
#		" --password=".$setvar{'dbpasswd'}.
#		" --one-database ".$setvar{'dbname'}.
#		" < ".$setvar{'install_target'}."/contrib/example/example.sql");
#	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.queues_list.conf /etc/asterisk/queues_list.conf");
#	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.sip_exten.conf /etc/asterisk/sip_exten.conf");
#	system("cp -af ".$setvar{'install_target'}."/contrib/example/example.extensions_hints.conf /etc/asterisk/extensions_hints.conf");
#	system("chmod -R 777 /etc/asterisk/");
#
#	#---------------设置安装全部完成
#	unlink("$setvar{'install_target'}/NOTDONE");
#
#exit;
#}

sub uninstall
{
system('/etc/init.d/fri2d stop');
system('rm -rf /freeiris2');
system('rm -rf /etc/init.d/hardware');
system('rm -rf /etc/init.d/fri2d');
#system("rm -rf /usr/bin/mysql -uroot --silent --execute='drop database freeiris2'");
unlink("/etc/httpd/conf.d/freeiris.httpd.conf");
}

sub println
{
my $type = shift;
my $msg = shift;

	if ($type eq 'step') {
		print $OUTPUT "\n[STEP] $msg\n";
	} elsif ($type eq 'input') {
		print $OUTPUT "    [INPUT] $msg";
	} elsif ($type eq 'response') {
		print $OUTPUT "  [RESPONSE] $msg\n";
	} elsif ($type eq 'failed') {
		print $OUTPUT "  [FAILED] $msg\n";
	} elsif ($type eq 'error') {
		print $ERRPUT "\n[ERROR] $msg\n\n";
		exit;
	} else {
		print $OUTPUT "$msg\n";
	}

return();
}
