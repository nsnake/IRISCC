#!/usr/bin/perl

# last update by solo 2008-1-8
# add debug mode

use strict;
use Socket;
use DBI;
use POSIX 'setsid';
use FindBin qw($Bin);

my $asterisk = '127.0.0.1';
my $asteriskport = 5038;
my $asteriskuser = 'astercc';
my $asterisksecret = 'astercc';

my $dbhost = '127.0.0.1';
my $dbname = 'astercc01';
my $dbport = 3306;
my $dbuser = '';
my $dbpasswd = '';

my %astInfo = (
        asterisk => $asterisk,
        asteriskport => $asteriskport,
        asteriskuser => $asteriskuser,
		asterisksecret  => $asterisksecret,
   );

my %dbInfo = (
        dbtype => 'mysql',
        dbhost => $dbhost,
        dbname => $dbname,
		dbport  => $dbport,
 		dbuser  => $dbuser,
 		dbpasswd  => $dbpasswd
   );

my $log_life = 180;

$SIG{__DIE__}=\&log_die;
$SIG{__WARN__}=\&log_warn;

$|=1;

################
my $pid_file="/tmp/$asterisk.pid";
my $pid=$$;
my $daemon=0;

if (!&connection_test){
	print("Connection failed, please check the log file for detail.\n");
	exit;
}

if ($ARGV[0] eq '-d'){
	  $daemon=1;
      $pid=&become_daemon;
}
open (HDW,">",$pid_file) or die "[EMERG] $!\n";
print HDW $pid;
close HDW;

#CONNECT

my $SOCK = &connect_ami(host=>$asterisk,
			port=>$asteriskport,
			user=>$asteriskuser,
		      secret=>$asterisksecret);

my $dbh = &connect_mysql(dbname=>$dbname,
			dbhost=>$dbhost,
    			dbport=>$dbport,
                        dbuser=>$dbuser,
    			dbpasswd=>$dbpasswd);

#&auto_create_table();

#Get message
my	$response;
while (my $line = <$SOCK>) {
	#LAST LINE
	if ($line eq "\r\n") {
		print "RECEIVE : $response-----------\n" if ($ARGV[0] ne '-d');
		&putdb($response);
		undef($response);
	} else {
		$line =~ s/\r/ /g;
		$line =~ s/\n/ /g;
		$response .= $line if $line;
	}
}
close($SOCK);

########################################
sub connect_ami
{
my	%info = @_;

#CONNECT
my     ($SOCK,$host,$addr,$msg);
	$host = inet_aton($info{'host'});
	socket($SOCK, AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	$addr = sockaddr_in($info{'port'},$host);

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
	send($SOCK, "ACTION: LOGIN\r\nUSERNAME: $info{'user'}\r\nSECRET: $info{'secret'}\r\nEVENTS: ON\r\n\r\n", 0);
        $msg = <$SOCK>;
        if ($msg =~ /Error/) {die '[Sorry] Login in failed! Maybe your name or password is error!';}
return($SOCK);
}
##############################################

sub connect_mysql{
	my	%info = @_;
	my	$dbh = DBI->connect("DBI:mysql:database=$info{'dbname'};host=$info{'dbhost'};port=$info{'dbport'}",$info{'dbuser'},$info{'dbpasswd'}) or die "Can't Connect Database Server: $!";
	return($dbh);
}

###############################################

sub auto_create_table
{
my	$sth = $dbh->prepare("show tables like 'events'");
	$sth->execute or die $dbh->errstr;
my	$row = $sth->fetchrow_arrayref();
	$sth->finish;

	#if to create table
	if ($row->[0] eq '') {
                warn "Auto Created table";
		$dbh->do(qq~CREATE TABLE events(
                        `id` INT(16) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        `timestamp` DATETIME,
                        `event` TEXT,
                        INDEX `timestamp` (`timestamp`)) ENGINE = Memory;~) or die $dbh->errstr;
		
	}
return();
}

#######################################}
sub putdb
{
my	$response = shift;
	return if ($response eq '');

	#if try to reconnect database
	if (!$dbh->ping) {
	     warn "Reconnect database";
	     $dbh = &connect_mysql(dbname=>$dbname,
                                   dbhost=>$dbhost,
                                   dbport=>$dbport,
                                   dbuser=>$dbuser,
                                   dbpasswd=>$dbpasswd);
	}

	# Delete old
    if($log_life>0){
        my $timestamp = time();	$timestamp -= $log_life;
        my @datetime = localtime($timestamp);	$datetime[5] += 1900;	$datetime[4]++;
	$dbh->do("DELETE FROM events WHERE timestamp <= '$datetime[5]-$datetime[4]-$datetime[3] $datetime[2]:$datetime[1]:$datetime[0]'") or die $dbh->errstr;
        }
	#Insert new
	$dbh->do("INSERT INTO events(timestamp,event) VALUES(now(),".$dbh->quote($response).")") or die $dbh->errstr;

return();
}

##########################################

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
sub connection_test{
	my $result = 1;

	&debug("Connecting to $dbInfo{'dbtype'} database on $dbInfo{'dbhost'}:");
	my $dbh = &connect_mysql(%dbInfo);
	if( !$dbh ){
		&debug("Database connection unsuccessful. Please check your login detials. ".$DBI::errstr);
		$result = 0;
	}else{
		&debug("Database connection successful.");
	}
	&debug("Connecting to asterisk on $astInfo{'asterisk'} port $astInfo{'asteriskport'}:");
	$SOCK = &connect_sock(%astInfo);
	if( !$SOCK ){
		&debug("Asterisk connection failed. Please check your connect parameter.");
		$result = 0;
	}else{
		my $msg = <$SOCK>;
		if ($msg !~ /Asterisk Call Manager/) {
			&debug("Asterisk connection failed!");
			$result = 0;
		}else{
			&debug("Asterisk socket connection successful.");
			# check username and password
			&debug("Check asterisk username & secret:");
			send($SOCK, "ACTION: LOGIN\r\nUSERNAME: $astInfo{'asteriskuser'}\r\nSECRET: $astInfo{'asterisksecret'}\r\n\r\n", 0);
			my $msg = <$SOCK>;
			if ($msg =~ /Response: Success/) {
				&debug("Success");
			}else{
				&debug("Failed");
				$result = 0;
			}
		}
	}
	return $result;
}
############################
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
############################
sub connect_sock{
	my	%info = @_;

	#CONNECT
	my	($SOCK,$host,$addr,$msg);
	$host = inet_aton($info{'asterisk'});
	socket($SOCK, AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	$addr = sockaddr_in(@info{'asteriskport'},$host);

	my $test = connect($SOCK,$addr);
	if ($test) {
		return $SOCK;
	}

	return 0;
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
