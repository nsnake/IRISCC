#!/usr/bin/perl
use FindBin qw($Bin);
use lib "$Bin/lib";
use POSIX 'setsid';
use strict;
use DBI;
use Config::IniFiles;
use Data::Dumper;

my $conf_file = "$Bin/astercc.conf";

# read parameter from conf file
my $cfg = new Config::IniFiles -file => $conf_file;
if (not defined $cfg) {
	print "can't find the config file\n";
	exit(1);
}

my %dbInfo = (
        dbtype => trim($cfg->val('database', 'dbtype')),
        dbhost => trim($cfg->val('database', 'dbhost')),
        dbname => trim($cfg->val('database', 'dbname')),
		dbport  => trim($cfg->val('database', 'dbport')),
 		dbuser  => trim($cfg->val('database', 'username')),
 		dbpasswd  => trim($cfg->val('database', 'password'))
   );

my $dbprefix = '';

my $debug = trim($cfg->val('database', 'debug'));

my $curcdrTbl = trim($cfg->val('database', 'tb_curchan'));
my $cdrTbl = trim($cfg->val('database', 'tb_cdr'));

if($curcdrTbl eq '' ){
	$curcdrTbl = 'curcdr';
}

if($cdrTbl eq '' ){
	$cdrTbl = 'mycdr';
}

my $pidFile = "/var/run/processdata.pid";

$| =1 ;

if ($ARGV[0] eq '-v'){		# print version
	print "processdata version 0.01-101206\n";
	print "copyright \@2009-2010\n";
	exit;
}elsif ($ARGV[0] eq '-t'){	 # test database & asterisk connection 
	&connection_test;
	exit;
}elsif ($ARGV[0] eq '-k'){
    if (open(MYFILE, $pidFile)) {
	    # here's what to do if the file opened successfully

		my $line = <MYFILE>;
		my $res;
		my $res = `kill -9 $line 2>&1`; 
		if ($res eq '') {
			print "processdata process: $line is killed. \n";
		}else{
			print "$res \n";
			print "cant kill processdata process. \n";
			exit;
		}
		unlink $pidFile;
    }else{
		print "cant find $pidFile. \n";
	}
	exit;
}elsif  ($ARGV[0] eq '-s'){
    if (open(MYFILE, $pidFile)) {
	    # here's what to do if the file opened successfully

		my $line = <MYFILE>;
		my $res;
		my $res = `ps  --pid=$line 2>&1`; 
		if ($res =~ /\n(.*)\n/) {
			print "processdata status: [start]\n";
		}else{
			print "processdata status: [stop]\n";
		}
    }else{
		print "cant find $pidFile, processdata may not start \n";
	}
	exit;
}elsif  ($ARGV[0] eq '-h'){
	print "********* processdata parameters *********\n";
	print "    -h show help message\n";
	#print "    -i parse all queue logs in the log file\n";
	print "    -d start as a daemon\n";
	print "    -s show processdata status\n";
	print "    -k stop processdata\n";
	print "    -v show processdata version \n";
	exit;
}


if (-e $pidFile){
    if (open(MYFILE, $pidFile)) {
		my $line = <MYFILE>;
		my $res;
		my $res = `ps  --pid=$line 2>&1`; 
		if ($res =~ /\n(.*)\n/) {
			print "processdata daemon is still running. Please stop first.\n"; #If no please del $pidFile \n";
			exit;
		}else{
			unlink $pidFile;
		}
    }
}

if (!&connection_test){
	print("Connection failed, please check the log file for detail.\n");
	exit;
}

my $dbh = &connect_mysql(%dbInfo);


my %ini;
tie %ini, 'Config::IniFiles', ( -file => "$conf_file" );

my %chkduplist;
if(exists $ini{'check_duplicate'}){
	my $check_duplicate = $ini{'check_duplicate'};
	my $n = 1;
	foreach my $ctbl ( keys %$check_duplicate) {
		$chkduplist{$n}{'table'} = $ctbl;
		my @params = split(/\|/,trim(%$check_duplicate->{$ctbl}));

		if($params[0] ne ''){
			my @fields = split(/\:/,$params[0]);
			my $i = 1;
			foreach(@fields){
				$chkduplist{$n}{'fields'}{$i} = trim($_);
				$i++;
			}			
		}

		if($params[1] ne ''){
			my @orders = split(/\,/,$params[1]);
			my $i = 1;

			if(trim($orders[0]) ne ''){
				$chkduplist{$n}{'orders'}{1} = trim($orders[0]);
			}else{
				$chkduplist{$n}{'orders'}{1} = 'id';
			}

			if(uc(trim($orders[1])) eq 'DESC'){
				$chkduplist{$n}{'orders'}{2} = 'DESC';
			}else{
				$chkduplist{$n}{'orders'}{2} = 'ASC';
			}
		}else{
			$chkduplist{$n}{'orders'}{1} = 'id';
			$chkduplist{$n}{'orders'}{2} = 'ASC';
		}

		if($params[2] ne ''){
			my @conditions = split(/:/,$params[2]);
			my $i = 1;
			foreach(@conditions){
				$chkduplist{$n}{'conditions'}{$i} = trim($_);
				$i++;
			}
		}
		$n++;
	}
}

my %trclist;
if(exists $ini{'truncate_table'}){
	my $truncate_table = $ini{'truncate_table'};
	my $i=1;
	foreach my $tctbl ( keys %$truncate_table) {
		if(trim(%$truncate_table->{$tctbl}) eq 1){
			$trclist{$i} = $tctbl;
			$i++;
		}
	}
}

my $io = 0;
print "Please select a option([C]heck Duplicates/[T]runcate Table):";
my $option = '';
while( $option ne 'c' && $option ne 't' ){
	if($io){
		print "Invalid option, please select a option([C]heck Duplicates/[T]runcate Table):";
	}
	$option = <STDIN>;
	$option = trim(lc($option));
	$io++;
}

$io = 0;
my $tbl = '';
if($option eq 'c'){
	if(!(keys %chkduplist)){
		print "No table defined for check duplicate in config file!\n";
		exit;
	}
	print "Please select a table:\n";

	foreach my $tblnum (sort keys %chkduplist) {
		print "$tblnum. $chkduplist{$tblnum}{'table'}\n";
	}
	print "Select:";
	while( !exists $chkduplist{$tbl} ){
		if($io){
			print "Invalid table, please select a table:";
		}
		$tbl = <STDIN>;
		$tbl = trim(lc($tbl));
		$io++;
	}

}elsif($option eq 't'){
	if(!(keys %trclist)){
		print "No table defined for truncate in config file!\n";
		exit;
	}
	print "Please select a table:\n";
	
	foreach my $tblnum (sort keys %trclist) {
		print "$tblnum. $trclist{$tblnum}\n";
	}
	print "Select:";
	while( !exists $trclist{$tbl} ){
		if($io){
			print "Invalid table, please select a table:";
		}
		$tbl = <STDIN>;
		$tbl = trim(lc($tbl));
		$io++;
	}
}

my $query = '';
$io = 0;
my $citem = '';
my $field = '';
my $condition = 0;
my %condition_tmp;

if($option eq 'c'){
	if(!exists $chkduplist{$tbl}{'fields'}){
		print "No field defind for check table:$chkduplist{$tbl}{'table'} in config file!\n";
		exit;
	}

	print "Please select fields for check duplicates:\n";
	my $fields = $chkduplist{$tbl}{'fields'};
	foreach my $curfield (sort keys %$fields) {
		print "$curfield. $fields->{$curfield}\n";
	}

	print "Select:";
	while( !exists $fields->{$field} ){
		if($io){
			print "Invalid field, please select:";
		}
		$field = <STDIN>;
		$field = trim(lc($field));
		$io++;
	}
	$io = 0;

	if(exists $chkduplist{$tbl}{'conditions'}{1} && exists $chkduplist{$tbl}{'conditions'}{2} && exists $chkduplist{$tbl}{'conditions'}{3}){
		print "Please select a $chkduplist{$tbl}{'conditions'}{1} in diallist for check duplicates:\n";
		
		my $i = 2;
		print "1. All $chkduplist{$tbl}{'conditions'}{1}\n";
		$condition_tmp{1}{'id'} = 'all';
		$condition_tmp{1}{$chkduplist{$tbl}{'conditions'}{2}} = 'all';

		$query = "SELECT id,$chkduplist{$tbl}{'conditions'}{2} FROM $chkduplist{$tbl}{'conditions'}{1} ORDER BY id ASC ";
		my $rows = &executeQuery($query,'rows');	
		while(my $ref = $rows->fetchrow_hashref() ) {
			$condition_tmp{$i}{'id'} = $ref->{'id'};
			$condition_tmp{$i}{$chkduplist{$tbl}{'conditions'}{2}} = $ref->{$chkduplist{$tbl}{'conditions'}{2}};
			print "$i. $ref->{$chkduplist{$tbl}{'conditions'}{2}}:$ref->{'id'}\n";
			$i++;
		}

		print "Select:";
		while(!exists $condition_tmp{$condition}){
			if($io){
				print "Invalid $chkduplist{$tbl}{'conditions'}{1}, please select:";
			}
			$condition = <STDIN>;
			$condition = trim(lc($condition));
			$io++;
		}
	}
	
	$io = 0;
	print "Please select a item for check duplicates:\n";
	print "1. Just record duplicates data to csv file\n";
	print "2. Just delete duplicates data from database\n";
	print "3. Record duplicates data to csv file and delete from database\n";
	print "Select:";
	while( $citem ne '1' && $citem ne '2' && $citem ne '3'){
		if($io){
			print "Invalid item, please select:";
		}
		$citem = <STDIN>;
		$citem = trim(lc($citem));
		$io++;
	}
}


if($option eq 'c'){
	my $debuginfo = "############################################\n";
	print "You selected:\n";
	$debuginfo .= "You selected:\n";
	print "Option: Check duplicates data\n";
	$debuginfo .= "Option: Check duplicates data\n";
	print "Item: ";
	$debuginfo .= "Item: ";
	if($citem eq '1'){
		print "Just record duplicates data to csv file\n";
		$debuginfo .= "Just record duplicates data to csv file\n";
	}elsif($citem eq '2'){
		print "Just delete duplicates data from database\n";
		$debuginfo .= "Just delete duplicates data from database\n";
	}elsif($citem eq '3'){
		print "Record duplicates data to csv file and delete from database\n";
		$debuginfo .= "Record duplicates data to csv file and delete from database\n";
	}
	print "Table: $chkduplist{$tbl}{'table'}\n";
	$debuginfo .= "Table: $chkduplist{$tbl}{'table'}\n";
	print "Field: $chkduplist{$tbl}{'fields'}{$field}\n";
	$debuginfo .= "Field: $chkduplist{$tbl}{'fields'}{$field}\n";
	if($condition > 0){
		print "Condition: $chkduplist{$tbl}{'conditions'}{1}:$chkduplist{$tbl}{'conditions'}{2}:$condition_tmp{$condition}{$chkduplist{$tbl}{'conditions'}{2}}\n";
		$debuginfo .= "Condition: $chkduplist{$tbl}{'conditions'}{1}:$chkduplist{$tbl}{'conditions'}{2}:$condition_tmp{$condition}{$chkduplist{$tbl}{'conditions'}{2}}\n";
	}
	print "Order: $chkduplist{$tbl}{'orders'}{1}:$chkduplist{$tbl}{'orders'}{2}\n";
	$debuginfo .= "Order: $chkduplist{$tbl}{'orders'}{1}:$chkduplist{$tbl}{'orders'}{2}\n";

	print "press [Y]es to contine, press other else to exit:";
	my $confirm = <STDIN>;
	$confirm = trim(lc($confirm));	

	my $recordfile = "$Bin/";
	if($confirm eq 'y' || $confirm eq 'yes'){
		if($debug <= 0){
			$debug = 1;
		}

		if ($ARGV[0] eq '-d'){
			# run background
			my $daemon=1;
			my $pid=&become_daemon;

			open PIDFILE, ">$pidFile" or die "can't open $pidFile: $!\n";
			print PIDFILE $pid;
			close PIDFILE;

			&debug($debuginfo);
		}

		my $curtime = time();
		#print "curtimecurtimecurtime:$curtime\n";
		$query = "DROP TABLE IF EXISTS `dup_$curtime`";
		&executeQuery($query,'');
		$query = "CREATE TABLE `dup_$curtime` (  `id` int(11) NOT NULL auto_increment,  `dupfield` varchar(255) NOT NULL default '',  UNIQUE KEY `id` (`id`), UNIQUE KEY `dupfield`(`dupfield`)) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;";
		&executeQuery($query,'');
		$recordfile .= "check_duplicate_$chkduplist{$tbl}{'table'}_$curtime.csv";

		my @checkfields = split(/\,/,$chkduplist{$tbl}{'fields'}{$field});
		my $fieldcount = @checkfields;
		my $fieldstr = '';
		foreach(@checkfields){
			$fieldstr .= " ".trim($_)." != '' OR";
		}
		$fieldstr =~ s/OR+$//;

		if($condition > 0 && $condition_tmp{$condition}{'id'} ne 'all'){
			$fieldstr = "($fieldstr) AND $chkduplist{$tbl}{'conditions'}{3} = $condition_tmp{$condition}{'id'} "
		}

		$query = "SELECT COUNT(*) AS total FROM $chkduplist{$tbl}{'table'} WHERE $fieldstr ";
		my $rows = &executeQuery($query,'rows');

		if ( my $ref = $rows->fetchrow_hashref() ) {
			my $total = $ref->{'total'};
			my $s = 0;
			my $l = 1000;
			my $t = $total;
			my $title = '';
			my $ts = 1;
			my $dupconut = 0;
			my $deletcount = 0;
			&debug("Checking...");
			while($total){
				my $recordcontent = '';
				my $i = $s;
				if(($s - $total) >= 0){
					$total = 0;
					$i = $t;
				}

				$query = "SELECT * FROM $chkduplist{$tbl}{'table'} WHERE $fieldstr ORDER BY $chkduplist{$tbl}{'orders'}{1} $chkduplist{$tbl}{'orders'}{2} LIMIT $s,$l";

				$rows = &executeQuery($query,'rows');
				
				while( my $ref = $rows->fetchrow_hashref() ) {
					my $insertcount = 0;
					foreach(@checkfields){						
						my $chkfield = trim($_);
						if($ref->{$chkfield} ne ''){
							$insertcount ++;
							$query = "INSERT INTO dup_$curtime SET id='$ref->{'id'}', dupfield = 'p$ref->{$chkfield}'";
							my $dupid = &executeQuery($query,'try_insert');
							
							if($dupid eq 'error'){
								if($insertcount > 0){
									$query = "DELETE FROM dup_$curtime WHERE id='$ref->{'id'}'";
									&executeQuery($query,'');
								}

								$query = "INSERT INTO dup_$curtime SET id='$ref->{'id'}',dupfield = '$ref->{'id'}'";
								my $eid = &executeQuery($query,'insert');

								if($citem eq '1' || $citem eq '3'){
									
									foreach my $key (sort keys %$ref) {
										if($ts){
											$title .= "$key,";
										}
										$recordcontent .= "$ref->{$key},";
									}
									$ts = 0;
									$recordcontent .= "\n";
								}
								
								$dupconut ++;
								last;
							}
						}
					}
				}

				if(($citem eq '1' || $citem eq '3') && $dupconut > 0){
					open RECORDFILE, ">>$recordfile" or die "can't open $recordfile: $!\n";
					if($title ne ''){
						print RECORDFILE "$title\n";
						$title = '';
					}
					print RECORDFILE $recordcontent;
					close RECORDFILE;
				}

				$s += $l;
				proc_bar($i,$t);
			    select(undef, undef, undef, 0.2);

			}
			print "\n";
			&debug("Checking Finished.");

			if(($citem eq '3' || $citem eq '2') && $dupconut > 0){
				&debug("Deleting...");
				$query = "DELETE FROM $chkduplist{$tbl}{'table'} WHERE id in (SELECT id FROM dup_$curtime WHERE id=dupfield)";
				my $effect = &executeQuery($query,'');
				$deletcount = $effect;
			}		
			
			$query = "DROP TABLE IF EXISTS `dup_$curtime`";
			&executeQuery($query,'');

			if($dupconut > 0){
				&debug("$dupconut duplicated records have been detected.");
				if($citem eq '3' || $citem eq '2'){
					&debug("$dupconut duplicated records have been deleted.");
				}
				if($citem eq '1' || $citem eq '3'){					
					&debug("Recorded to $recordfile.");					
				}
			}else{
				&debug("No duplicated record has been detected.");
			}

		}		

	}else{
		print "Cancel by user\n";
		exit;
	}
}elsif($option eq 't'){
	print "You selected:\n";
	print "Option: Truncate Table\n";
	print "Table: $trclist{$tbl}\n";
	print "press [Y]es to contine, press other else to exit:";
	my $confirm = <STDIN>;
	$confirm = trim(lc($confirm));
	if($confirm eq 'y'){
		$query = "TRUNCATE TABLE $trclist{$tbl}";
		&executeQuery($query,'');
		print "$trclist{$tbl} has been truncated!\n";
	}else{
		print "Cancel by user\n";
		exit;
	}
}

unlink($pidFile);
exit;

sub connect_mysql
{
	my	%info = @_;
	my	$dbh = DBI->connect("DBI:mysql:database=$info{'dbname'};host=$info{'dbhost'};port=$info{'dbport'}",$info{'dbuser'},$info{'dbpasswd'});
	return($dbh);
}

sub connection_test{
	my $result = 1;

	&debug("Connecting to $dbInfo{'dbtype'} database on $dbInfo{'dbhost'}:");
	my $dbh = &connect_mysql(%dbInfo);
	if( !$dbh ){
		&debug("Database connection unsuccessful. Please check your login details. ".$DBI::errstr);
		$result = 0;
	}else{
		&debug("Database connection successful.");
	}
	return $result;
}

sub executeQuery
{
	my	$query = shift;
	return if ($query eq '');

	my	$queryType = shift;

	if (!$dbh->ping) {
		 &debug("Reconnect database");
		 $dbh = &connect_mysql(%dbInfo);
	}

	if ($debug > 10) {
		&debug("$query");
	}

	if ($queryType eq '') {
			$dbh->{PrintError} = 1;
			my $affect = $dbh->do($query) or &debug($dbh->errstr."($query)");
			if ($affect eq '0E0'){
				return 0;
			}else{
				return $affect;
			}
	}elsif ($queryType eq 'rows'){
			$dbh->{PrintError} = 1;
			my $rows = $dbh->prepare($query);
			$rows->execute() or &debug($dbh->errstr);
			return $rows;
	}elsif ($queryType eq 'insert'){
		$dbh->{PrintError} = 1;
		$dbh->do($query) or &debug($dbh->errstr);
		return $dbh->{q{mysql_insertid}};
	}elsif ($queryType eq 'try_insert'){
		$dbh->{PrintError} = 0;
		$dbh->do($query) or return "error";
		return $dbh->{q{mysql_insertid}};
	}
}

sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}

sub become_daemon {
    die "Can't fork" unless defined (my $child = fork);
    exit 0 if $child;#kill父进程
    setsid();
    open( STDIN, "</dev/null" );
    open( STDOUT, ">/dev/null" );
    open( STDERR, ">&STDOUT" );

	$SIG{__WARN__} = sub {
		&debug ("NOTE! " . join(" ", @_));
	};

	$SIG{__DIE__} = sub { 
		&debug ("FATAL! " . join(" ", @_));
		unlink $pidFile;
		exit;
	};

	$SIG{HUP} = $SIG{INT} = $SIG{TERM} = sub {
		# Any sort of death trigger results in death of all
		my $sig = shift;
		$SIG{$sig} = 'IGNORE';
		die "killed by $sig\n";
		exit;
	};

    umask(0);
	#$ENV{PATH} = '/bin:/sbin:/usr/bin:/usr/sbin';
    return $$;
}

sub debug{
	my $message = shift;
	my $time=scalar localtime;
	if ($debug > 0) {
		if ($ARGV[0] eq '-d'){		# output to file
			open (HDW,">>$Bin/processdatalog.txt");
			print HDW $time," ",$message,"\n";
			close HDW;
		}else{
			print $time," ",$message,"\n";
		}
	}
}

sub proc_bar{
 local $| = 1;
 my $i = $_[0] || return 0;
 my $n = $_[1] || return 0;
 print   "\r\033[36m[\033[33m".("#" x int(($i/$n)*50)).(" " x (50 - int(($i/$n)*50)))."\033[36m]";
 #print $i.$n;
 printf("%2.1f%%\033[0m",$i/$n*100);
 local $| = 0;
}