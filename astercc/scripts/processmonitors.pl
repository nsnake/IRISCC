#!/usr/bin/perl
use FindBin qw($Bin);
use lib "$Bin/lib";
use POSIX 'setsid';
use strict;
use DBI;
use Config::IniFiles;
use Data::Dumper;
use Time::Local;

my $conf_file = "$Bin/astercc.conf" ;
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
my $convert_mp3 = trim($cfg->val('system', 'convert_mp3'));

my $pidFile = "/var/run/processmonitors.pid";

my $lamecmd = '';
if( -e '/usr/bin/lame'){
	$lamecmd = '/usr/bin/lame';
}elsif( -e '/usr/local/bin/lame'){
	$lamecmd = '/usr/local/bin/lame';
}

if($lamecmd eq ''){
	print "Warning: can't find 'lame' commond,could not convert the records to mp3!\n";
	$convert_mp3 = 0;
	#exit;
}

my $soxcmd = '';
if( -e '/usr/bin/sox'){
	$soxcmd = '/usr/bin/sox';
}elsif( -e '/usr/local/bin/sox'){
	$soxcmd = '/usr/local/bin/sox';
}else{
	print "can't find 'sox' commond\n";
	exit;
}

my $soxmixcmd = '';
if( -e '/usr/bin/soxmix'){
	$soxmixcmd = '/usr/bin/soxmix';
}elsif( -e '/usr/local/bin/soxmix'){
	$soxmixcmd = '/usr/local/bin/soxmix';
}

#if($soxmixcmd eq '' && $soxcmd eq ''){
#	exit;
#}

$| =1 ;

if ($ARGV[0] eq '-v'){		# print version
	print "processmonitors version 0.011-100510\n";
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
			print "processmonitors process: $line is killed. \n";
		}else{
			print "$res \n";
			print "cant kill processmonitors process. \n";
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
			print "processmonitors status: [start]\n";
		}else{
			print "processmonitors status: [stop]\n";
		}
    }else{
		print "cant find $pidFile, processmonitors may not start \n";
	}
	exit;
}elsif  ($ARGV[0] eq '-h'){
	print "********* processmonitors parameters *********\n";
	print "    -h show help message\n";
	#print "    -i parse all queue logs in the log file\n";
	print "    -d start as a daemon\n";
	print "    -s show processmonitors status\n";
	print "    -k stop processmonitors\n";
	print "    -v show processmonitors version \n";
	exit;
}


if (-e $pidFile){
    if (open(MYFILE, $pidFile)) {
		my $line = <MYFILE>;
		my $res;
		my $res = `ps  --pid=$line 2>&1`; 
		if ($res =~ /\n(.*)\n/) {
			print "processmonitors daemon is still running. Please stop first.\n"; #If no please del $pidFile \n";
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

if ($ARGV[0] eq '-d'){
	# run background
	my $daemon=1;
	my $pid=&become_daemon;

	open PIDFILE, ">$pidFile" or die "can't open $pidFile: $!\n";
	print PIDFILE $pid;
	close PIDFILE;
}


my $dbh = &connect_mysql(%dbInfo);


my $query = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored WHERE mycdr.processed = '1' AND ischild = 'no'   ORDER BY calldate ASC ";#AND mycdr.id > 5
my $rows = &executeQuery($query,'rows');

while ( my $ref = $rows->fetchrow_hashref() ) {
	my %cdrinfo;
	my $joinmainstr = '';
	if(trim($ref->{'children'}) ne ''){#有子cdr的多条cdr处理
		$cdrinfo{$ref->{'dstchannel'}}->{$ref->{'id'}} = $ref;
		$cdrinfo{$ref->{'dstchannel'}}->{'filename'} = $ref->{'filename'};
		$cdrinfo{$ref->{'dstchannel'}}->{'fileformat'} = $ref->{'fileformat'};
		if($ref->{'filename'} ne ''){
			$joinmainstr .= " $ref->{'filename'}.$ref->{'fileformat'} ";
		}
		my $childs = $ref->{'children'};
		$childs =~ s/\,+$//;
		$query = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored WHERE mycdr.id > $ref->{'id'} AND mycdr.id in($childs) ORDER BY calldate ASC";
		my $child_rows = &executeQuery($query,'rows');
		while ( my $child_ref = $child_rows->fetchrow_hashref() ) {
			if($child_ref->{'dstchannel'} ne ''){
				$cdrinfo{$child_ref->{'dstchannel'}}->{$child_ref->{'id'}} = $child_ref;
				$cdrinfo{$child_ref->{'dstchannel'}}->{'filename'} = $child_ref->{'filename'};
				$cdrinfo{$child_ref->{'dstchannel'}}->{'fileformat'} = $child_ref->{'fileformat'};
				if($child_ref->{'filename'} ne ''){
					$joinmainstr .= " $child_ref->{'filename'}.$child_ref->{'fileformat'} ";
				}
			}
			#print Dumper($child_ref);
		}
	}else{ #单条cdr处理	
		if($ref->{'monitored'} > 0){
			if( (-e "$ref->{'filename'}\-in.$ref->{'fileformat'}") && (-e "$ref->{'filename'}\-out.$ref->{'fileformat'}")){
				if($soxmixcmd ne ''){
					my $execstr = "$soxmixcmd $ref->{'filename'}-in.$ref->{'fileformat'} $ref->{'filename'}-out.$ref->{'fileformat'} $ref->{'filename'}.$ref->{'fileformat'}";
					#print "soxmixcmd:$execstr\n";
					system($execstr);
					system("rm -f $ref->{'filename'}-in.$ref->{'fileformat'} $ref->{'filename'}-out.$ref->{'fileformat'}");
				}elsif($soxcmd ne ''){
					my $execstr = "$soxcmd -m $ref->{'filename'}-in.$ref->{'fileformat'} $ref->{'filename'}-out.$ref->{'fileformat'} $ref->{'filename'}.$ref->{'fileformat'}";
					system($execstr);
					system("rm -f $ref->{'filename'}-in.$ref->{'fileformat'} $ref->{'filename'}-out.$ref->{'fileformat'}");
				}else{
					print "ERROR:Can't find sox and soxmix commond!\n";
					exit;
				}
			}else{
				if(-e "$ref->{'filename'}\-in.$ref->{'fileformat'}"){
					system("rm -f $ref->{'filename'}\-in.$ref->{'fileformat'}");
				}elsif(-e "$ref->{'filename'}\-out.$ref->{'fileformat'}"){
					system("rm -f $ref->{'filename'}\-out.$ref->{'fileformat'}");
				}
			}

			if( -e "$ref->{'filename'}.$ref->{'fileformat'}" ){
				if($ref->{'fileformat'} eq 'wav' && $convert_mp3){
					my $execstr = "$lamecmd --cbr -m m -t -F $ref->{'filename'}.$ref->{'fileformat'} $ref->{'filename'}.mp3 2>&1";
					#print "convertcmd:$execstr";
					system($execstr);
					if( -e "$ref->{'filename'}.mp3" ){
						$query = "UPDATE monitorrecord SET processed = 'yes', fileformat='mp3' WHERE id ='$ref->{'monitored'}'";
						&executeQuery($query,'');
						system("rm -f $ref->{'filename'}.$ref->{'fileformat'}");
					}else{
						$query = "UPDATE monitorrecord SET processed = 'yes' WHERE id ='$ref->{'monitored'}'";
							&executeQuery($query,'');
					}
				}else{
					$query = "UPDATE monitorrecord SET processed = 'yes' WHERE id ='$ref->{'monitored'}'";
					&executeQuery($query,'');
				}
			}else{
				$query = "UPDATE monitorrecord SET processed = 'yes',fileformat='error' WHERE id ='$ref->{'monitored'}'";
				&executeQuery($query,'');
			}
		}
		$query = "UPDATE mycdr SET processed='2' WHERE id='$ref->{'id'}'";
		&executeQuery($query,'');
		next;
	}

	my $hcdrinfo = \%cdrinfo;
	
	#print Dumper($hcdrinfo);exit;
	foreach my $curchan (sort keys %$hcdrinfo) {

		#print Dumper $hcdrinfo->{$curchan};exit;
		my $fileflag = 1;
		my $filename = $hcdrinfo->{$curchan}->{'filename'};
		delete $hcdrinfo->{$curchan}->{'filename'};
		my $fileformat = $hcdrinfo->{$curchan}->{'fileformat'};
		delete $hcdrinfo->{$curchan}->{'fileformat'};

		my $chancount = keys( %{$hcdrinfo->{$curchan}} );

		if( (-e "$filename\-in.$fileformat") && (-e "$filename\-out.$fileformat") && $filename ne ''){
			if($soxmixcmd ne ''){
				my $execstr = "$soxmixcmd $filename-in.$fileformat $filename-out.$fileformat $filename.$fileformat";
				#print "soxmixcmd:$execstr\n";
				system($execstr);
				system("rm -f $filename-in.$fileformat $filename-out.$fileformat");
			}elsif($soxcmd ne ''){
				my $execstr = "$soxcmd -m $filename-in.$fileformat $filename-out.$fileformat $filename.$fileformat";
				system($execstr);
				system("rm -f $filename-in.$fileformat $filename-out.$fileformat");
			}else{
				print "ERROR:Can't find sox and soxmix commond!\n";
				exit;
			}
		}else{
			if(-e "$filename\-in.$fileformat" && $filename ne ''){
				system("rm -f $filename\-in.$fileformat");
			}elsif(-e "$filename\-out.$fileformat" && $filename ne ''){
				system("rm -f $filename\-out.$fileformat");
			}
		}

		if( -e "$filename.$fileformat" && $filename ne ''){
			if($chancount > 1){ #多条cdr共用一个录音文件
				system("mv $filename.$fileformat $filename-all.$fileformat");
				#print "mv $filename.$fileformat $filename-all.$fileformat";
			}
		}else{
			#print "mv $filename.$fileformat $filename-all.$fileformat\n";
			print "can't find monitor file:$filename\n";
			$fileflag = 0;
		}
		
		my $i = 1;
		my $first_calldate = 0;
		my $first_ringtime = 0;
		foreach	my $curid (sort keys %{$hcdrinfo->{$curchan}}) {
			
			if($fileflag){
				#print "priv_billsec:$first_calldate|$first_ringtime\n";				
				my $start = 0;

				my ($tmpYear, $tmpMon, $tmpDay, $tmpHour, $tmpMin, $tmpSec) = split(/[\s\-\:]/,$hcdrinfo->{$curchan}->{$curid}->{'calldate'});
				my $curcalldatestr = timelocal($tmpSec, $tmpMin, $tmpHour, $tmpDay, $tmpMon-1, $tmpYear-1900);

				my $curfile = $hcdrinfo->{$curchan}->{$curid}->{'filename'};

				if($chancount > 1){#多条cdr共用一个录音文件
					if($i == 1){
						$first_calldate = $curcalldatestr;
						$first_ringtime = $hcdrinfo->{$curchan}->{$curid}->{'duration'} - $hcdrinfo->{$curchan}->{$curid}->{'billsec'};;
						$start = 0;
						$i ++;
					}else{
						$start = ($curcalldatestr - $first_calldate - $first_ringtime);
					}
						
					#print "curcalldatestr:$curcalldatestr\n";
					
					my $thisbillsec = $hcdrinfo->{$curchan}->{$curid}->{'billsec'};
					if($thisbillsec > 0 && $curfile ne ''){
						my $execstr = "$soxcmd $filename-all.$fileformat $curfile.$fileformat trim $start $thisbillsec";
						#print "trimcmd:$execstr\n";
						system($execstr);
					}
				}
				#print "$curid|||$curfile\n";

				#转成mp3文件
				if( -e "$curfile.$fileformat" && $curfile ne ''){
					
					if($fileformat eq 'wav' && $convert_mp3){
						my $execstr = "$lamecmd --cbr -m m -t -F $curfile.$fileformat $curfile.mp3 2>&1";
						#print "convertcmd:$execstr";
						system($execstr);
						if( -e "$curfile.mp3" ){
							$query = "UPDATE monitorrecord SET processed = 'yes', fileformat='mp3' WHERE id ='$hcdrinfo->{$curchan}->{$curid}->{'monitored'}'";
							&executeQuery($query,'');
							#system("rm -f $curfile.$fileformat");
						}else{
							$query = "UPDATE monitorrecord SET processed = 'yes' WHERE id ='$hcdrinfo->{$curchan}->{$curid}->{'monitored'}'";
								&executeQuery($query,'');
						}
					}else{
						$query = "UPDATE monitorrecord SET processed = 'yes' WHERE id ='$hcdrinfo->{$curchan}->{$curid}->{'monitored'}'";
						&executeQuery($query,'');
					}
				}else{
					#文件不存在, 从合并主录音串中移除
					if($curfile ne ''){
						$joinmainstr =~ s/$curfile\.$fileformat//;
					}
					$query = "UPDATE monitorrecord SET processed = 'yes', fileformat='error' WHERE id ='$hcdrinfo->{$curchan}->{$curid}->{'monitored'}'";
					&executeQuery($query,'');
				}
				#print Dumper($hcdrinfo->{$curchan}->{$curid});
				#print Dumper($curchandata{$curid});exit;
				
			}else{
				if($hcdrinfo->{$curchan}->{$curid}->{'filename'} ne ''){
					#文件不存在, 从合并主录音串中移除
					$joinmainstr =~ s/$hcdrinfo->{$curchan}->{$curid}->{'filename'}\.$fileformat//;				
				}
				$query = "UPDATE monitorrecord SET processed = 'yes', fileformat='error' WHERE id ='$hcdrinfo->{$curchan}->{$curid}->{'monitored'}'";
				&executeQuery($query,'');
			}
			$query = "UPDATE mycdr SET processed='2' WHERE id='$hcdrinfo->{$curchan}->{$curid}->{'id'}'";
			&executeQuery($query,'');
		}
		#多条cdr共用一个录音文件
		if($chancount > 1){
			if( -e "$filename-all.$fileformat" && $filename ne ''){
				system("rm -f $filename-all.$fileformat");
			}
		}
	}
	
	if($joinmainstr ne '' && $ref->{'filename'} ne ''){
		my $execstr = "$soxcmd $joinmainstr $ref->{'filename'}-all.$ref->{'fileformat'}";
		#print "joinmainstr:$execstr\n";
		system($execstr);
		system("rm -f $joinmainstr");
		if( -e "$ref->{'filename'}-all.$ref->{'fileformat'}" && $ref->{'filename'} ne ''){
			print "mv -f $ref->{'filename'}-all.$ref->{'fileformat'} $ref->{'filename'}.$ref->{'fileformat'}\n";
			system("mv -f $ref->{'filename'}-all.$ref->{'fileformat'} $ref->{'filename'}.$ref->{'fileformat'}");

			if( -e "$ref->{'filename'}.$ref->{'fileformat'}" && $ref->{'fileformat'} == 'wav' && $convert_mp3){
				my $execstr = "$lamecmd --cbr -m m -t -F $ref->{'filename'}.$ref->{'fileformat'} $ref->{'filename'}.mp3 2>&1";
				#print "convertcmd:$execstr";
				system($execstr);
				if( -e "$ref->{'filename'}.mp3" ){
					$query = "UPDATE monitorrecord SET processed = 'yes', fileformat='mp3' WHERE id ='$ref->{'monitored'}'";
					&executeQuery($query,'');
					system("rm -f $ref->{'filename'}.$ref->{'fileformat'}");
				}
			}
		}
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
			my $affect = $dbh->do($query) or &debug($dbh->errstr."($query)");
			if ($affect eq '0E0'){
				return 0;
			}else{
				return $affect;
			}
	}elsif ($queryType eq 'rows'){
			my $rows = $dbh->prepare($query);
			$rows->execute() or &debug($dbh->errstr);
			return $rows;
	}elsif ($queryType eq 'insert'){
		$dbh->do($query) or &debug($dbh->errstr);
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
			open (HDW,">>$Bin/processmonitorslog.txt");
			print HDW $time," ",$message,"\n";
			close HDW;
		}else{
			print $time," ",$message,"\n";
		}
	}
}