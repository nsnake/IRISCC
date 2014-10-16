#!/usr/bin/perl

# last update by solo 2008-1-8
# add debug mode

use strict;
use FindBin qw($Bin);
use lib "$Bin/lib";
use Socket;
use POSIX 'setsid';
use IO::Handle;
use Config::IniFiles;
use Data::Dumper;

my $conf_file = "$Bin/astercc.conf" ;
# read parameter from conf file
my $cfg = new Config::IniFiles -file => $conf_file;
if (not defined $cfg) {
	print "Failed to parse $conf_file: \n";
	foreach(@Config::IniFiles::errors) {
		print "Error: $_\n" ;
	}
	exit(1);
}

my %astInfo = (
        asterisk => trim($cfg->val('asterisk', 'server')),
        asteriskport => trim($cfg->val('asterisk', 'port')),
        asteriskuser => trim($cfg->val('asterisk', 'username')),
		asterisksecret  => trim($cfg->val('asterisk', 'secret')),
   );


#$SIG{__DIE__}=\&log_die;
#$SIG{__WARN__}=\&log_warn;

$|=1;

################
my $pid_file="/var/run/events2filedaemon.pid";
my $pid=$$;
my $daemon=0;

my $events_file = trim($cfg->val('system', 'eventsfile'));
if (!defined($events_file) || $events_file eq '') {
	$events_file = '/tmp/asterccevents.log';
}

if ($ARGV[0] eq '-d'){
	$daemon=1;
	$pid=&become_daemon;
	open (HDW,">",$pid_file) or die "[EMERG] $!\n";
	print HDW $pid;
	close HDW;
}

#CONNECT
my $SOCK = &connect_ami(%astInfo);

&debug("AMI connected");
#Get message
my	$response;

open (FD, "> $events_file") or die $!;
FD->autoflush(1);
while (my $line = <$SOCK>) {
	#LAST LINE
	if ($line eq "\r\n") {
		print "RECEIVE : $response-----------\n" if ($ARGV[0] ne '-d');
        my @datetime = localtime(time());$datetime[5] += 1900;	$datetime[4]++;
	    print FD "$datetime[5]-$datetime[4]-$datetime[3] $datetime[2]:$datetime[1]:$datetime[0] ### $response\n";
		undef($response);
	} else {
		$line =~ s/\r/ /g;
		$line =~ s/\n/ /g;
		$response .= $line if $line;
	}
}
close($SOCK);
close FD;
exit;

########################################
sub connect_ami
{
	my	%info = @_;
	#CONNECT
	my ($SOCK,$host,$addr,$msg);
	$host = inet_aton($info{'asterisk'});
	socket($SOCK, AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	$addr = sockaddr_in($info{'asteriskport'},$host);

	warn '[warn] Connect to Asterisk now';
        foreach my $failed (0..3)
                {
                    if($failed >= 3){die '[Sorry] I try my best Connect to Asterisk Manager Port ,But I am tired!';}
                    elsif(connect($SOCK,$addr)){last;}
                    else
                    {
                    warn "[Sorry] Can not Connect to Asterisk Manager Port $!";
                    #sleep 180;
                    }
                }

	warn '[warn] Connect successful, Waiting for the message!';
        $msg = <$SOCK>;       
	if ($msg !~ /Asterisk Call Manager/) {die "[Sorry] Connect failed! Message is $msg";}

	#LOGIN IN
    warn '[warn] Login in Asterisk Manager';
	send($SOCK, "ACTION: LOGIN\r\nUSERNAME: $info{'asteriskuser'}\r\nSECRET: $info{'asterisksecret'}\r\nEVENTS: ON\r\n\r\n", 0);
        $msg = <$SOCK>;
        if ($msg =~ /Error/) {die '[Sorry] Login in failed! Maybe your name or password is error!';}
	return($SOCK);
}
##############################################

sub become_daemon {
    die "Can't fork" unless defined (my $child = fork);
    exit 0 if $child;
    setsid();
    open( STDIN, "</dev/null" );
    open( STDOUT, ">/dev/null" );
    open( STDERR, ">&STDOUT" );
    chdir '/';
    umask(0);
   $ENV{PATH} = '/bin:/sbin:/usr/bin:/usr/sbin';
    return $$;
}

#############################################
sub log_die{
	my $message =shift;
	my $time=scalar localtime;
	open (HDW,">>$Bin/eventsdaemonlog.txt");
	print HDW $time," ",$message;
	close HDW;
	exit;
#die @_;
}

###############################################
sub log_warn
{
	my $message =shift;
	print $message;
	my $time=scalar localtime;
	open (HDW,">>$Bin/eventsdaemonlog.txt");
	print HDW $time," ",$message;
	close HDW;
}
###############################################
sub debug{
	my $message = shift;
	my $time=scalar localtime;
	if ($ARGV[0] eq '-d'){		# output to file
		open (HDW,">>$Bin/eventsdaemonlog.txt");
		print HDW $time," ",$message,"\n";
		close HDW;
	}else{
		print $time," ",$message,"\n";
	}
}

sub trim($){
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}

############################
END{
	if($daemon)
		{
		warn('eventsdaemon will in daemon start!');
		}
	else{
		print "Thanks for using, eventsdaemon stopped!\n";
		}
	}
