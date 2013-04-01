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
# use modules
use FindBin qw($Bin);
use lib "$Bin/../lib/";
use Config::IniFiles;
use Socket;
use DBI;
use strict;

my (%FREEIRIS_GENERAL);
#global configure
tie %FREEIRIS_GENERAL, 'Config::IniFiles', ( -file => "/etc/freeiris2/freeiris.conf" );

# Config;
my $log_life = 180;

# AUTO FLASH
$|=1;


# PID
system("echo '$$' > ".$FREEIRIS_GENERAL{'general'}{'freeiris_root'}."/logs/famp.pid");

#CONNECT MYSQL SERVER
my $dbh = &connect_mysql(dbname=>$FREEIRIS_GENERAL{'database'}{'dbname'},
						dbhost=>$FREEIRIS_GENERAL{'database'}{'dbhost'},
						dbport=>$FREEIRIS_GENERAL{'database'}{'dbport'},
						dbuser=>$FREEIRIS_GENERAL{'database'}{'dbuser'},
						dbpasswd=>$FREEIRIS_GENERAL{'database'}{'dbpasswd'},
						dbsock=>$FREEIRIS_GENERAL{'database'}{'dbsock'});

#CONNECT
my $SOCK = &connect_ami(host=>'localhost',
						port=>5038,
						user=>'freeiris',
						secret=>'freeiris');
#READ
my	$response;
while (my $line = <$SOCK>) {
	#LAST LINE
	if ($line eq "\r\n") {
		warn "RECEIVE : $response-----------\n" if ($ARGV[$#ARGV] eq '--verbose');
		&putdb($response);
		undef($response);
	} else {
		$response .= $line if $line;
	}
}

# DISCONNECT ALL
close($SOCK);
$dbh->disconnect();


sub putdb
{
my	$response = shift;
	return if ($response eq '');

	#if try to reconnect database
	if (!$dbh->ping) {
		warn "Reconnect database\n" if ($ARGV[$#ARGV] eq '--verbose');
		$dbh = &connect_mysql(dbname=>$FREEIRIS_GENERAL{'database'}{'dbname'},
								dbhost=>$FREEIRIS_GENERAL{'database'}{'dbhost'},
								dbport=>$FREEIRIS_GENERAL{'database'}{'dbport'},
								dbuser=>$FREEIRIS_GENERAL{'database'}{'dbuser'},
								dbpasswd=>$FREEIRIS_GENERAL{'database'}{'dbpasswd'},
								dbsock=>$FREEIRIS_GENERAL{'database'}{'dbsock'});
	}

	# Delete old
my	$timestamp = time();	$timestamp -= $log_life;
my	@datetime = localtime($timestamp);	$datetime[5] += 1900;	$datetime[4]++;
	$dbh->do("DELETE FROM ami_event WHERE cretime <= '$datetime[5]-$datetime[4]-$datetime[3] $datetime[2]:$datetime[1]:$datetime[0]'")
		or die $dbh->errstr;

	# data split
my ($event1,$event2,$event3,$event4);
	$event4 = substr($response,720,240) if (length($response) > 720);
	$event3 = substr($response,480,240) if (length($response) > 480);
	$event2 = substr($response,240,240) if (length($response) > 240);
	$event1 = substr($response,0,240);

	# Insert new
	$dbh->do("INSERT INTO ami_event(cretime,event,event2,event3,event4) VALUES(now(),".$dbh->quote($event1).",".$dbh->quote($event2).",".$dbh->quote($event3).",".$dbh->quote($event4).")") or die $dbh->errstr;

return();
}


sub connect_ami
{
my	%info = @_;

#CONNECT
my	($SOCK,$host,$addr,$msg);
	$host = inet_aton($info{'host'});
	socket($SOCK, AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	$addr = sockaddr_in($info{'port'},$host);

	connect($SOCK,$addr) or die "Can't Connect to Asterisk Manager Port : $!";

	$msg = <$SOCK>;
	if ($msg !~ /Asterisk Call Manager/) {
		die "Connect not ok!";exit;
	}

	#LOGIN IN
	send($SOCK, "ACTION: LOGIN\r\nUSERNAME: $info{'user'}\r\nSECRET: $info{'secret'}\r\nEVENTS: ON\r\n\r\n", 0);

return($SOCK);
}

sub connect_mysql
{
my	%info = @_;
my	$dbh = DBI->connect("DBI:mysql:database=$info{'dbname'};host=$info{'dbhost'};port=$info{'dbport'};mysql_socket=$info{'dbsock'}",$info{'dbuser'},
			$info{'dbpasswd'}) or die "Can't Connect Database Server: $!";
return($dbh);
}

