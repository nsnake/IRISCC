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
use strict;
use File::Basename;
use POSIX 'WNOHANG';

# AUTO FLASH
$|=1;

# SIG
$SIG{CHLD}= sub { while ( waitpid(-1,WNOHANG)>0){}};

# CONFIG BASIC
my ($FREEIRIS_GENERAL_FILE,%FREEIRIS_GENERAL);
$FREEIRIS_GENERAL_FILE = '/etc/freeiris2/freeiris.conf';
if (!-e$FREEIRIS_GENERAL_FILE) {
	&error("Can't Read $FREEIRIS_GENERAL_FILE system abort.");
	exit(0);
}
%FREEIRIS_GENERAL=%{&config_parse($FREEIRIS_GENERAL_FILE)};

# PARSE CONFIG
my %PROCESSES=%{&config_parse('/etc/freeiris2/fri2d.conf')};

# FUNCTION OF KILLALL?
#if ($ARGV[0] eq 'killall') {
#	foreach  (keys %PROCESSES) {
#		next if (!defined $PROCESSES{$_}{daemon});
#		`killall $PROCESSES{$_}{daemon}`;
#		&error("KILLALL $PROCESSES{$_}{daemon}");
#	}
#	exit;
#}

# CHECK MY SELF IN MEMORY READLY?
if (&check_processes($FREEIRIS_GENERAL{'general'}{'freeiris_root'}.'/logs/fri2d.pid',$0)) {
	&error("exists fri2d in memory, please restart by manual...");
	exit(0);
}

# INIT STARTUP
system("echo '$$' > ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2d.pid");

# ONCE STARTUP
foreach  (keys %PROCESSES) {
	if (exists($PROCESSES{$_}{startup}) && $PROCESSES{$_}{script_start} ne '') {

		if ($PROCESSES{$_}{enable} ne 'yes') {
			&error("STARTUP ONCE DISABLED $PROCESSES{$_}{script_start}");
		} elsif ($PROCESSES{$_}{check_type} eq 'once') {
			&run_process($PROCESSES{$_}{script_start},$PROCESSES{$_}{daemon});
			&error("STARTUP ONCE $PROCESSES{$_}{script_start}");
			sleep(1);
		} elsif (!&check_processes($PROCESSES{$_}{script_pid},$PROCESSES{$_}{daemon})) {
			&run_process($PROCESSES{$_}{script_start},$PROCESSES{$_}{daemon});
			&error("STARTUP DAEMON $PROCESSES{$_}{script_start}");
			sleep(1);
		} else {
			&error("STARTUP DAEMON EXISTS $PROCESSES{$_}{script_start}");
		}

	}
	$PROCESSES{$_}{LAST_SECOFTIME} = time();
}


#------------------------------------------------------ FORK CHILED TO BACKEND RUN
my $twofish = fork();
# father process
if ($twofish!=0) {

	# INIT STARTUP
	system("echo '$twofish' > ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2d.pid");
	exit(1);

# chiled process
} else {

	#------------------------------------------------------ BEGIN LOOP
	while (1) {
		# ALL PROCESSES
		foreach my $current_prc (keys %PROCESSES) {
			# TIMEOUT
			if (($PROCESSES{$current_prc}{LAST_SECOFTIME}+$PROCESSES{$current_prc}{per}) <= time()) {
				$PROCESSES{$current_prc}{LAST_SECOFTIME} = time();

				#-ONCE
				if ($PROCESSES{$current_prc}{check_type} eq 'once' && $PROCESSES{$current_prc}{enable} eq 'yes') {
					&run_process($PROCESSES{$current_prc}{script_start},$PROCESSES{$current_prc}{daemon});
					&error("ONCE $PROCESSES{$current_prc}{script_start}.");

				#-DAEMON IF NO EXISTS
				} elsif ($PROCESSES{$current_prc}{check_type} eq 'daemon' && $PROCESSES{$current_prc}{enable} eq 'yes') {
					if (!&check_processes($PROCESSES{$current_prc}{script_pid},$PROCESSES{$current_prc}{daemon})) {
						&run_process($PROCESSES{$current_prc}{script_start},$PROCESSES{$current_prc}{daemon});
						&error("DAEMON STARTUP $PROCESSES{$current_prc}{script_start}.");
					}

				}

			}
		}
		sleep(1); # stamp

		#check file command
		if (-e$FREEIRIS_GENERAL{'general'}{'freeiris_root'}.'/logs/fri2d_restart') {
			unlink($FREEIRIS_GENERAL{'general'}{'freeiris_root'}.'/logs/fri2d_restart');
			system('/etc/init.d/fri2ctl restart');
		}
	}
	#------------------------------------------------------ END LOOP
}

exit(1);



#------------------------------------------------------ SUB FUNCTIONS
sub run_process
{
my	$command = shift;
my	$daemon = shift;
my	$child = fork();
	if ($child == 0) {
		if ($daemon ne '') {
			system("killall $daemon > /dev/null 2>&1");
			system("killall $daemon > /dev/null 2>&1");
			sleep(1);
		}
		system("$command");
		exit(1);
	}
return();
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

sub config_parse
{
my	(%CONFIG,$last_section);

	open(CONF,"$_[0]") or die "Can't Open $_[0] : $!";
	while (<CONF>) {
		# trim 
		chomp($_);		$_ =~ s/\;(.*)//;
		next if ($_ eq '');

		if ($_ =~ /\[(.+)\]/) {
			$last_section=$1;
			$CONFIG{$last_section}={};
			next;
		}
		if ($_ =~ /(.+)\=(.+)/) {
		my	$key = $1;
		my	$value = $2;
			chomp($key);	$key =~ s/^\s+//;	$key =~ s/\s+$//;
			chomp($value);	$value =~ s/^\s+//;	$value =~ s/\s+$//;
			# NEW TRANS VALUE VARIABLE
			$value =~ s/\$IRISROOT/$FREEIRIS_GENERAL{'general'}{'freeiris_root'}/g;
			$value =~ s/\/\//\//g;

			$CONFIG{$last_section}{$key}=$value;
		}
	}
	close(CONF);

return(\%CONFIG);
}

sub error
{
my	$msg = shift;
my	$time = localtime;

	if ($ARGV[0] eq '--logfile' || $ARGV[$#ARGV] eq '--verbose') {
		if (defined $FREEIRIS_GENERAL{'general'}{'freeiris_root'}) {
			open(SAVE,">>".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2d.log") 
				or die "Can't Write ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/fri2d.log : $!";
			print SAVE "[$time] $msg\n";
			close(SAVE);
		}
	}
	warn "[$time] $msg\n" if ($ARGV[0] eq '--verbose');
return();
}

1;