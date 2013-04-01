package freeiris::utils;
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
use Carp qw(carp croak);
use strict;
use vars qw($VERSION @ISA);
use Digest::MD5 qw(md5 md5_hex md5_base64);
use Time::HiRes qw(gettimeofday);
use Socket;

$VERSION='0.1';

BEGIN {
	@ISA = qw();
}

sub new 
{
my	$class = shift;
my	$self = {};
my	%args = @_;

	srand;

	$self->{astmanager_sock} = undef;

	bless $self, $class;

return $self;
}

sub get_unique_id
{
my	$self = shift;
my	$length = shift;
my	$encrypt = shift;
my	$sessionid;
	$length = $length - 12;
	$length = 0 if ($length < 0);

	for(my $i=0 ; $i< $length ;)
	{
	my	$j = chr(int(rand(127)));

		if($j =~ /[a-zA-Z0-9]/)
		{
			$sessionid .=$j;
			$i++;
		}
	}
my	($seconds, $microseconds) = gettimeofday;

	if ($encrypt eq 'md5_hex') {
		$sessionid=md5_hex($sessionid.substr($seconds,4).$microseconds);
	} else {
		$sessionid=$sessionid.substr($seconds,4).$microseconds;
	}

return($sessionid);
}

sub format_pc2unix
{
my	$self = shift;
my	$data = shift;
	$data =~ s/\r\n/\n/g;
return($data);
}

sub check_currency_number
{
my	$self = shift;
my	$currency = shift;
	$currency =~ s/^\-//;
	return(1) if ($currency eq '');
	return(0) if ($currency =~ /^\.|\.$/);
	return(0) if ($currency =~ /[^0-9\.]/);
#	return(0) if ($currency < 0.0001);
	return(0) if ($currency > 1000000000);
return(1);
}

sub is_digi_numberic
{
my	$self = shift;
my	$digi = shift;
	return(0) if ($digi =~ /[^0-9]/);
return(1);
}


# Asterisk Manager Connector
# hash args : host port user secret
sub astmanager_conn
{
my	$self = shift;
my	%info = @_;

	$info{'port'} = 5038 if (!exists($info{'port'}));
	$info{'host'} = '127.0.0.1' if (!exists($info{'host'}));

my	$SOCK;
my	$host = inet_aton($info{'host'});
	socket($SOCK, AF_INET, SOCK_STREAM, getprotobyname('tcp'));
my	$addr = sockaddr_in($info{'port'},$host);

	connect($SOCK,$addr) or die return(0);

my	$msg = <$SOCK>;
	return(0) if ($msg !~ /Asterisk Call Manager/);

	$self->{astmanager_sock} = $SOCK;
	my @response = $self->astmanager_cmd("ACTION: LOGIN\r\nUSERNAME: $info{'user'}\r\nSECRET: $info{'secret'}\r\nEVENTS: OFF\r\n\r\n");
	return(0) if ($response[0] !~ /Response: Success/);


return(1);
}

# Asterisk Manager disconnectory
sub astmanager_discon
{
my	$self = shift;
	close $self->{astmanager_sock};
}

sub astmanager_cmd
{
my	$self = shift;
my	$handle = $self->{astmanager_sock};
my	$command = shift;
my	$EOF = shift;

	$EOF = "\r\n" unless (defined $EOF);

	send($handle, $command, 0);
my	@response;

	while (my $line = <$handle>) {
		last if ($line eq "$EOF");

		if (wantarray) {
			$line =~ s/$EOF//g;
			push(@response, $line) if $line;
		} else {
			$response[0] .= $line;
		}
	}

return(@response);
}

1;

