#!/usr/bin/perl
#
#	Freeiris2 -- An Opensource telephony project.
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
use FindBin qw($Bin);
use lib "$Bin/../lib/";
use DBI;
use Config::IniFiles;
use POSIX 'WNOHANG';
use File::Basename;
use strict;
use warnings;

my (%FREEIRIS_GENERAL);
#global configure
tie %FREEIRIS_GENERAL, 'Config::IniFiles', ( -file => "/etc/freeiris2/freeiris.conf" );

# AUTO FLASH
$|=1;

# CHECK MY SELF IN MEMORY READLY?
if (&check_processes($FREEIRIS_GENERAL{'general'}{'freeiris_root'}.'/logs/fri2bill.pid',$0)) {
	exit(0);
}

# INIT STARTUP
system("echo '$$' > ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2bill.pid");

#=================================================================
# call command args
#=================================================================
if (defined$ARGV[0] && $ARGV[0] eq '--help') {
print qq~
  Freeiris2 Billing Script

syntax:
  print help          :    ./fri2bill.pl --help
  run in silence mode :    ./fri2bill.pl --silence
  run in noise mode   :    ./fri2bill.pl --verbose
~;
} elsif(defined$ARGV[0] && $ARGV[0] eq '--silence') {
	&billing;
} elsif(defined$ARGV[0] && $ARGV[0] eq '--verbose') {
	&billing;
} else {
	print "syntax :    ./fri2bill.pl --help\n";
}
exit;

#=================================================================
# Core 
#=================================================================

sub billing()
{
my ($DBH,$sth);
my (@callrule);

	#connect database
	$DBH = DBI->connect("DBI:mysql:database=".$FREEIRIS_GENERAL{'database'}{'dbname'}.";host=".$FREEIRIS_GENERAL{'database'}{'dbhost'}.
			";port=".$FREEIRIS_GENERAL{'database'}{'dbport'}.";mysql_socket=".$FREEIRIS_GENERAL{'database'}{'dbsock'},
			$FREEIRIS_GENERAL{'database'}{'dbuser'},$FREEIRIS_GENERAL{'database'}{'dbpasswd'},{'RaiseError' => 1});
	&logfile('Connected database!');

	#得到费率表
 	$sth = $DBH->prepare("select * from billingrate order by dst_prefix desc");
	$sth->execute or $DBH->errstr;
	while (my $row = $sth->fetchrow_hashref()) {
		push(@callrule,$row);
	}
	$sth->finish;
	if ($#callrule < 0) {
		&logfile('no call rule exit!');
	} else {
		&logfile("find ".($#callrule+1)." billing rate rules.");
	}

	#取得月份
my	@datetime = localtime();	$datetime[5] += 1900;	$datetime[4]++;
my	$billingdate;
	if ($datetime[4] == 1) {
		$billingdate = ($datetime[5]-1).'-12';
	} else {
		$billingdate = $datetime[5].'-'.($datetime[4]-1);
	}

	#检测上个月的计费是否已经做过了
 	$sth = $DBH->prepare("select * from billinginvoice where calldate >= '".$billingdate.
		"-01 00:00:00' and calldate <= '".$billingdate."-31 23:59:59' limit 1");
	$sth->execute or $DBH->errstr;
	if ($sth->rows && $sth->rows > 0) {
		&logfile($billingdate.'-01 alreadly done.');
		exit;
	}

	#取出上个月的全部ANSWERD数据
 	$sth = $DBH->prepare("select id,src,dst,dcontext,duration,billsec,disposition,amaflags,accountcode,".
		"userfield,calldate from cdr where disposition = 'ANSWERED' and calldate >= '".$billingdate.
		"-01 00:00:00' and calldate <= '".$billingdate."-31 23:59:59'");
	$sth->execute or $DBH->errstr;
	&logfile("Found CDR ".$sth->rows." Records");
	while (my $row = $sth->fetchrow_hashref()) {
		
		#检测这个数据是否应该计费处理的数据,from-trunk表示呼叫来自外线,表示为呼入不计算.只计算呼出部分
		next if ($row->{'dcontext'} =~ /from-trunk/ || $row->{'dcontext'} =~ /sub-queuefindnumber/);

		#查找是否有适合的计费指标
	my	$matched=undef;
		foreach my $eachrule (@callrule) {
			if ($row->{'dst'} =~ /^$eachrule->{'dst_prefix'}/) {
				$matched=$eachrule;
				last;
			}
		}

		#如果有计费算法
		if (defined $matched) {
		my	$makelog = $row->{'id'}." CDR ".$row->{'src'}." -> ".$row->{'dst'}.
				" Matched Prefix ".$matched->{'dst_prefix'}." billsec ".$row->{'billsec'};

			#计算费用
		my	($persecond,$percost,$billroundsec,$cost);
			if ($matched->{'persecond'} && $matched->{'persecond'} > 0) {
				$persecond = $matched->{'persecond'};
			} else {
				$persecond = 0;
			}
			if ($matched->{'percost'} && $matched->{'percost'} > 0) {
				$percost = $matched->{'percost'};
			} else {
				$percost = 0;
			}
			$makelog .= " rate:$persecond/$percost";
			#计算
			$billroundsec=(int($row->{'billsec'}/$persecond)+1)*$persecond;
			$cost=$billroundsec*($percost/$persecond);
			$makelog .= " pay:$billroundsec/$cost";

			#存储到表中
			$DBH->do("insert into billinginvoice set cdrid = '".$row->{'id'}."',".
				"cretime = now() ,".
				"calldate = '".$row->{'calldate'}."',".
				"accountcode = '".$row->{'accountcode'}."',".
				"src = '".$row->{'src'}."',".
				"dst = '".$row->{'dst'}."',".
				"billsec = '".$row->{'billsec'}."',".
				"billroundsec = '".$billroundsec."',".
				"persecond = '".$persecond."',".
				"percost = '".$percost."',".
				"cost = '".$cost."'") or die $DBH->errstr;

			&logfile($makelog);

		} else {
		my	$makelog = "CDR ".$row->{'id'}." from ".$row->{'src'}." --> ".$row->{'dst'}.
				" No Matched Rule!";

			#存储到表中
			$DBH->do("insert into billinginvoice set cdrid = '".$row->{'id'}."',".
				"cretime = now() ,".
				"calldate = '".$row->{'calldate'}."',".
				"accountcode = '".$row->{'accountcode'}."',".
				"src = '".$row->{'src'}."',".
				"dst = '".$row->{'dst'}."',".
				"billsec = '".$row->{'billsec'}."',".
				"billroundsec = '0',".
				"persecond = '0',".
				"percost = '0',".
				"cost = '0'") or die $DBH->errstr;

			&logfile($makelog);

		}

	}
	$sth->finish;

exit;
}

sub check_processes
{
my	$pid = shift;
my	$keyname = shift;
	return if ($pid eq '' && $keyname eq '');

my	$exists;

	#如果PID文件存在
	if (-e$pid) {
	my	$pid_number = `cat $pid`;		chomp($pid_number);

		#如果存在这个进程
		if (-e"/proc/$pid_number/cmdline") {
		my	$pid_cmdline = `cat /proc/$pid_number/cmdline`;
			chomp($pid_cmdline);
		my	$myname = basename($keyname);
			#如果这个进程的名字正好是指定的名字
			if ($pid_cmdline =~ /$myname/) {
				$exists = 1;
			#这个进程是其他进程(未启动)
			} else {
				$exists = 0;
			}

		#不存在这个进程(未启动)
		} else {
			$exists = 0;
		}
	}

return($exists);
}

#
# subroute : file logger record
#
sub logfile
{
my	$msg = shift;
my	$time = localtime;

#	if (defined $FREEIRIS_GENERAL{'general'}{'freeiris_root'}) {
#		open(SAVE,">>".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2bill.log") 
#			or die "Can't Write ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2bill.log : $!";
#		print SAVE "[$time] $msg\n";
#		close(SAVE);
#	}
	warn "[$time] $msg\n" if ($ARGV[$#ARGV] eq '--verbose');
}

1;