# -*- perl -*-
#
#  Net::Server::Proto - Net::Server Protocol compatibility layer
#
#  $Id: Proto.pm,v 1.31 2013/01/10 06:03:13 rhandom Exp $
#
#  Copyright (C) 2001-2012
#
#    Paul Seamons
#    paul@seamons.com
#    http://seamons.com/
#
#  This package may be distributed under the terms of either the
#  GNU General Public License
#    or the
#  Perl Artistic License
#
#  All rights reserved.
#
################################################################

package Net::Server::Proto;

use strict;
use warnings;
use Socket ();

my $requires_ipv6 = 0;

sub parse_info {
    my ($class, $port, $host, $proto, $ipv, $server) = @_;

    my $info;
    if (ref($port) eq 'HASH') {
        die "Missing port in hashref passed in port argument.\n" if ! $port->{'port'};
        $info = $port;
    } else {
        $info = {};
        $info->{'unix_type'} = $1
                    if $port =~ s{ (?<=[\w*\]]) [,|\s:/]+ (sock_stream|sock_dgram) \b }{}x; # legacy /some/path|sock_dgram
        $ipv   = $1 if $port =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv*
        $ipv  .= $1 if $port =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv4|IPv6 stacked
        $proto = $1 if $port =~ s{ (?<=[\w*\]]) [,|\s:/]+ (tcp|udp|ssl|ssleay|unix|unixdgram|\w+(?: ::\w+)+) $ }{}xi # allow for 80/tcp or 200/udb or 90/Net::Server::Proto::TCP
                    || $port =~ s{ / (\w+) $ }{}x; # legacy 80/MyTcp support
        $host  = $1 if $port =~ s{ ^ (.*?)      [,|\s:]+  (?= \w+ $) }{}x; # allow localhost:80
        $info->{'port'} = $port;
    }
    $info->{'port'} ||= 0;


    $info->{'host'} ||= (defined($host) && length($host)) ? $host : '*';
    $ipv  = $1 if $info->{'host'} =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv*
    $ipv .= $1 if $info->{'host'} =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv4|IPv6 stacked
    if (     $info->{'host'} =~ m{^ \[ ([\w/.\-:]+ | \*?) \] $ }x) { # allow for [::1] or [host.example.com]
        $info->{'host'} = length($1) ? $1 : '*';
    } elsif ($info->{'host'} =~ m{^    ([\w/.\-:]+ | \*?)    $ }x) {
        $info->{'host'} = $1; # untaint
    } else {
        $server->fatal("Could not determine host from \"$info->{'host'}\"");
    }


    $info->{'proto'} ||= $proto || 'tcp';
    $ipv  = $1 if $info->{'proto'} =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv*
    $ipv .= $1 if $info->{'proto'} =~ s{ (?<=[\w*\]]) [,|\s:/]+ IPv([*\d]+) }{}xi; # allow for 80|IPv4|IPv6 stacked
    if ($info->{'proto'} =~ /^(\w+ (?:::\w+)*)$/x) {
        $info->{'proto'} = $1;
    } else {
        $server->fatal("Could not determine proto from \"$proto\"");
    }
    $proto = lc $info->{'proto'};

    if ($info->{'proto'} =~ /^UNIX/i) {
        return ({%$info, ipv => '*'});
    }
    $ipv = $info->{'ipv'} || $ipv || $ENV{'IPV'} || '';
    $ipv = join '', @$ipv if ref($ipv) eq 'ARRAY';
    $server->fatal("Invalid ipv parameter - must contain 4, 6, or *") if $ipv && $ipv !~ /[46*]/;
    my @_info;
    if (!$ipv || $ipv =~ /[*]/) {
        my @rows = eval { $class->get_addr_info(@$info{qw(host port proto)}) };
        $server->fatal($@ || "Could not find valid addresses for [$info->{'host'}]:$info->{'port'} with ipv set to '*'") if ! @rows;
        foreach my $row (@rows) {
            my ($host, $port, $ipv, $warn) = @$row;
            push @_info, {host => $host, port => $port, ipv => $ipv, proto => $info->{'proto'}, $warn ? (warn => $warn) : ()};
            $requires_ipv6++ if $ipv ne '4' && $proto ne 'ssl'; # we need to know if Proto::TCP needs to reparent as a child of IO::Socket::INET6
        }
        if (@rows > 1 && $rows[0]->[1] == 0) {
            $server->log(2, "Determining auto-assigned port (0) for host $info->{'host'} (prebind)");
            my $sock = $class->object($_info[-1], $server);
            $sock->connect($server);
            @$_{qw(port orig_port)} = ($sock->NS_port, 0) for @_info;
        }
        foreach my $_info (@_info) {
            $server->log(2, "Resolved [$info->{'host'}]:$info->{'port'} to [$_info->{'host'}]:$_info->{'port'}, IPv$_info->{'ipv'}")
                if $_info->{'host'} ne $info->{'host'} || $_info->{'port'} ne $info->{'port'};
            $server->log(2, delete $_info->{'warn'}) if $_info->{'warn'};
        }
    } elsif ($ipv =~ /6/ || $info->{'host'} =~ /:/) {
        push @_info, {%$info, ipv => '6'};
        $requires_ipv6++ if $proto ne 'ssl'; # IO::Socket::SSL does its own determination
        push @_info, {%$info, ipv => '4'} if $ipv =~ /4/ && $info->{'host'} !~ /:/;
    } else {
        push @_info, {%$info, ipv => '4'};
    }

    return @_info;
}

sub get_addr_info {
    my ($class, $host, $port, $proto) = @_;
    $host  = '*'   if ! defined $host;
    $port  = 0     if ! defined $port;
    $proto = 'tcp' if ! defined $proto;
    return ([$host, $port, '*']) if $proto =~ /UNIX/i;
    $port = (getservbyname($port, $proto))[2] or die "Could not determine port number from host [$host]:$_[2]\n" if $port =~ /\D/;

    my @info;
    if ($host =~ /^\d+(?:\.\d+){3}$/) {
        my $addr = Socket::inet_aton($host) or die "Unresolveable host [$host]:$port: invalid ip\n";
        push @info, [Socket::inet_ntoa($addr), $port, 4]
    } elsif (!$ENV{'NO_IPV6'} && eval { require Socket6; require IO::Socket::INET6 }) {
        my $proto_id = getprotobyname(lc($proto) eq 'udp' ? 'udp' : 'tcp');
        my $socktype = lc($proto) eq 'udp' ? Socket::SOCK_DGRAM() : Socket::SOCK_STREAM();
        my @res = Socket6::getaddrinfo($host eq '*' ? '' : $host, $port, Socket::AF_UNSPEC(), $socktype, $proto_id, Socket6::AI_PASSIVE());
        die "Unresolveable [$host]:$port: $res[0]\n" if @res < 5;
        while (@res >= 5) {
            my ($afam, $socktype, $proto, $saddr, $canonname) = splice @res, 0, 5;
            my @res2 = Socket6::getnameinfo($saddr, Socket6::NI_NUMERICHOST() | Socket6::NI_NUMERICSERV());
            die "getnameinfo failed on [$host]:$port: $res2[0]\n" if @res2 < 2;
            my ($ip, $port) = @res2;
            my $ipv = ($afam == Socket6::AF_INET6()) ? 6 : ($afam == Socket::AF_INET()) ? 4 : '*';
            push @info, [$ip, $port, $ipv];
        }
        my %ipv6mapped = map {$_->[0] eq '::' ? ('0.0.0.0' => $_) : $_->[0] =~ /^::ffff:(\d+(?:\.\d+){3})$/ ? ($1 => $_) : ()} @info;
        if ((scalar(keys %ipv6mapped)
             && grep {$ipv6mapped{$_->[0]}} @info)
            && not my $only = $class->_bindv6only) {
            for my $i4 (@info) {
                my $i6 = $ipv6mapped{$i4->[0]} || next;
                if ($host eq '*' && $i6->[0] eq '::' && !length($only)
                    && !eval{IO::Socket::INET6->new->configure({LocalAddr => '', LocalPort => 0, Listen => 1, ReuseAddr => 1, Domain => Socket6::AF_INET6()}) or die $!}) {
                    $i4->[3] = "Host [*] resolved to IPv6 address [::] but IO::Socket::INET6->new fails: $@";
                    $i6->[0] = '';
                } else {
                    $i6->[3] = "Not including resolved host [$i4->[0]] IPv4 because it ".(length($only) ? 'will' : 'should')." be handled by [$i6->[0]] IPv6";
                    $i4->[0] = '';
                }
            }
            @info = grep {length $_->[0]} @info;
        }
    } elsif ($host =~ /:/) {
        die "Unresolveable host [$host]:$port - could not load IO::Socket::INET6: $@";
    } else {
        my @addr;
        if ($host eq '*') {
            push @addr, Socket::INADDR_ANY();
        } else {
            (undef, undef, undef, undef, @addr) = gethostbyname($host);
            die "Unresolveable host [$host]:$port via IPv4 gethostbyname\n" if !@addr;
        }
        push @info, [Socket::inet_ntoa($_), $port, 4] for @addr
    }

    return @info;
}

sub _bindv6only {
    my $class = shift;
    my $val = $class->_sysctl('net.ipv6.bindv6only'); # linux
    $val = $class->_sysctl('net.inet6.ip6.v6only') if ! length($val); # bsd
    return $val;
}

sub _sysctl {
    my ($class, $key) = @_;
    (my $file = "/proc/sys/$key") =~ y|.|/|;
    if (-e $file) {
        open my $fh, "<", $file or return '';
        my $val = <$fh> || return '';
        chomp $val;
        return $val;
    } elsif (-x "/sbin/sysctl") {
        my $val = (split /\s+/, `/sbin/sysctl -n $key 2>/dev/null`)[0];
        return defined($val) ? $val : '';
    }
    return '';
}

sub object {
    my ($class, $info, $server) = @_;
    my $proto_class = $info->{'proto'};
    if ($proto_class !~ /::/) {
        $server->fatal("Invalid proto class \"$proto_class\"") if $proto_class !~ /^\w+$/;
        $proto_class = "Net::Server::Proto::" .uc($proto_class);
    }
    (my $file = "${proto_class}.pm") =~ s|::|/|g;
    $server->fatal("Unable to load module for proto \"$proto_class\": $@") if ! eval { require $file };
    return $proto_class->object($info, $server);
}

sub requires_ipv6 {
    my ($class, $server) = @_;
    return if ! $requires_ipv6;

    if (! $INC{'IO/Socket/INET6.pm'}) {
        eval {
            require Socket6;
            require IO::Socket::INET6;
        } or $server->fatal("Port configuration using IPv6 could not be started becauses of Socket6 library issues: $@");
    }
    return 1;
}

1;

__END__

=head1 NAME

Net::Server::Proto - Net::Server Protocol compatibility layer

=head1 SYNOPSIS

    NOTE: beginning in Net::Server 2.005, the default value for
          ipv is IPv* meaning that if no host is passed, or
          a hostname is past, all available socket types will be
          bound.  You can force IPv4 only by adding an ipv => 4
          configuration in any of the half dozen ways we let you
          specify it.

    # Net::Server::Proto and its accompanying modules are not
    # intended to be used outside the scope of Net::Server.

    # That being said, here is how you use them.  This is
    # only intended for anybody wishing to extend the
    # protocols to include some other set (ie maybe a
    # database connection protocol)

    use Net::Server::Proto;

    my @info = Net::Server::Proto->parse_info(
        $port,            # port to connect to
        $default_host,    # host to use if none found in port
        $default_proto,   # proto to use if none found in port
        $default_ipv,     # default of IPv6 or IPv4 if none found in port
        $server_obj,      # Net::Server object
    );

    my @raw_info = Net::Server::Proto->get_addr_info($host, $port, $proto);
    # returns arrayref of resolved ips, ports, and ipv values

    my $sock = Net::Server::Proto->object({
        port  => $port,
        host  => $host,
        proto => $proto,
        ipv   => $ipv, # * (IPv*) if false (default false)
    }, $server);

    # Net::Server::Proto will attempt to interface with
    # sub modules named similar to Net::Server::Proto::TCP
    # Individual sub modules will be loaded by
    # Net::Server::Proto as they are needed.

    use Net::Server::Proto::TCP; # or UDP or UNIX etc

    # Return an object which is a sub class of IO::Socket
    # At this point the object is not connected.
    # The method can gather any other information that it
    # needs from the server object.
    my $sock = Net::Server::Proto::TCP->object({
        port  => $port,
        host  => $host,
        proto => $proto,
        ipv   => 6, # IPv6 - default is * - can also be '4'
    }, $server);


    # Log that a connection is about to occur.
    # Use the facilities of the passed Net::Server object.
    $sock->log_connect( $server );

    # Actually bind to port or socket file.  This
    # is typically done by calling the configure method.
    $sock->connect();

    # Allow for rebinding to an already open fileno.
    # Typically will just do an fdopen.
    $sock->reconnect();

    ### Return a unique identifying string for this sock that
    # can be used when reconnecting.
    my $str = $sock->hup_string();

    # Return the proto that is being used by this module.
    my $proto = $sock->NS_proto();


=head1 DESCRIPTION

Net::Server::Proto is an intermediate module which returns IO::Socket
style objects blessed into its own set of classes (ie
Net::Server::Proto::TCP, Net::Server::Proto::UNIX).

Only three or four protocols come bundled with Net::Server.  TCP, UDP,
UNIX, UNIXDGRAM, and SSLEAY.  TCP is an implementation of SOCK_STREAM
across an INET socket.  UDP is an implementation of SOCK_DGRAM across
an INET socket.  UNIX uses a unix style socket file with the
SOCK_STREAM protocol.  UNIXGRAM uses a unix style socket file with the
SOCK_DGRAM protocol.  SSLEAY is actually just a layer on top of TCP
but uses Net::SSLeay to read and write from the stream.

The protocol that is passed to Net::Server can be the name of another
module which contains the protocol bindings.  If a protocol of
MyServer::MyTCP was passed, the socket would be blessed into that
class.  If Net::Server::Proto::TCP was passed, it would get that
class.  If a bareword, such as tcp, udp, unix, unixdgram or ssleay, is
passed, the word is uppercased, and post pended to
"Net::Server::Proto::" (ie tcp = Net::Server::Proto::TCP).

=head1 METHODS

Protocol names used by the Net::Server::Proto should be sub classes of
IO::Socket.  These classes should also contain, as a minimum, the
following methods should be provided:

=over 4

=item object

Return an object which is a sub class of IO::Socket At this point the
object is not connected.  The method can gather any other information
that it needs from the server object.  Arguments are default_host,
port, and a Net::Server style server object.

=item log_connect

Log that a connection is about to occur.  Use the facilities of the
passed Net::Server object.  This should be an informative string
explaining which properties are being used.

=item connect

Actually bind to port or socket file.  This is typically done
internally by calling the configure method of the IO::Socket super
class.

=item reconnect

Allow for rebinding to an already open fileno.  Typically will just do
an fdopen using the IO::Socket super class.

=item hup_string

Return a unique identifying string for this sock that can be used when
reconnecting.  This is done to allow information including the file
descriptor of the open sockets to be passed via %ENV during an exec.
This string should always be the same based upon the configuration
parameters.

=item NS_port

Net::Server protocol.  Return the port that is being used by this
module.  If the underlying type is UNIX then port will actually be
the path to the unix socket file.

=item NS_host

Net::Server protocol.  Return the protocol that is being used by this
module.  This does not have to be a registered or known protocol.

=item NS_proto

Net::Server protocol.  Return the protocol that is being used by this
module.  This does not have to be a registered or known protocol.

=item show

Similar to log_connect, but simply shows a listing of which
properties were found.  Can be used at any time.

=back

=head1 HOST

The hostname may be either blank, '*', be an IPv4 address, an IPv6 address,
a bare hostname, or a hostname with IPv* specifications.

    host => "127.0.0.1",  # an IPv4 address

    host => "::1",        # an IPv6 address

    host => 'localhost',  # addresses returned by localhost (default IPv* - IPv4 and/or IPv6)

    host => 'localhost/IPv*',  # same

    ipv  => '*',
    host => 'localhost',  # same

    ipv  => 6,
    host => 'localhost',  # addresses returned by localhost (IPv6)

    ipv  => 'IPv4 IPv6',
    host => 'localhost',  # addresses returned by localhost (requires IPv6 and IPv4)


    host => '*',          # any local interfaces (default IPv*)

    ipv  => '*',
    host => '*',          # any local interfaces (any IPv6 or IPv4)

    host => '*/IPv*',     # same

=head1 IPV

In addition to being able to specify IPV as a separate parameter, ipv may
also be passed as a part of the host, as part of the port, as part of the protocol
or may be specified via $ENV{'IPV'}.  The order of precidence is as follows:

     1) Explicit IPv4 or IPv6 address - wins
     2) ipv specified in port
     3) ipv specified in host
     4) ipv specified in proto
     5) ipv specified in default settings
     6) ipv specified in $ENV{'IPV'}
     7) default to IPv*

=head1 PORT

The port is the most important argument passed to the sub
module classes and to Net::Server::Proto itself.  For tcp,
udp, and ssleay style ports, the form is generally host:port/protocol,
[host]:port/protocol, host|port|protocol, host/port, or port.
If I<host> is a numerical IPv6 address it should be enclosed in square
brackets to avoid ambiguity in parsing a port number, e.g.: "[::1]:80".
Separating with spaces, commas, or pipes is also allowed, e.g. "::1, 80".
For unix sockets the form is generally socket_file|unix or socket_file.

To help overcome parsing ambiguity, it is also possible to pass port as
a hashref (or as an array of hashrefs) of information such as:

    port => {
        host  => "localhost",
        ipv   => 6, # could also pass IPv6 (* is default)
        port  => 20203,
        proto => 'tcp',
    }

If a hashref does not include host, ipv, or proto - it will use the default
value supplied by the general configuration.

A socket protocol family PF_INET or PF_INET6 is derived from a specified
address family of the binding address. A PF_INET socket can only accept
IPv4 connections. A PF_INET6 socket accepts IPv6 connections, but may also
accept IPv4 connections, depending on OS and its settings. For example,
on FreeBSD systems setting a sysctl net.inet6.ip6.v6only to 0 will allow
IPv4 connections to a PF_INET6 socket.  By default on linux, binding to
host [::] will accept IPv4 or IPv6 connections.

The Net::Server::Proto::object method returns a list of objects corresponding
to created sockets. For Unix and INET sockets the list typically contains
just one element, but may return multiple objects when multiple protocol
families are allowed or when a host name resolves to multiple local
binding addresses.  This is particularly true when an ipv value of '*' is
passed in allowing hostname resolution.

You can see what Net::Server::Proto parsed out by looking at
the logs to see what log_connect said.  You could also include
a post_bind_hook similar to the following to debug what happened:

    sub post_bind_hook {
        my $self = shift;
        foreach my $sock ( @{ $self->{server}->{sock} } ){
            $self->log(2,$sock->show);
        }
    }

Rather than try to explain further, please look at the following
examples:

    # example 1 #----------------------------------

    $port      = "20203";
    $def_host  = "default-domain.com";
    $def_proto = undef;
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => 'default-domain.com',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => *, # IPv*
    # };

    # example 2 #----------------------------------

    $port      = "someother.com:20203";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => *,
    # };

    # example 3 #----------------------------------

    $port      = "someother.com:20203/udp";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'udp', # will use Net::Server::Proto::UDP
    #     ipv   => *,
    # };

    # example 4 #----------------------------------

    $port      = "someother.com:20203/Net::Server::Proto::UDP";
    $def_host  = "default-domain.com";
    $def_proto = "TCP";
    $def_ipv   = 4;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'Net::Server::Proto::UDP',
    #     ipv   => 4,
    # };

    # example 5 #----------------------------------

    $port      = "someother.com:20203/MyObject::TCP";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto);
    # @info = {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'MyObject::TCP',
    # };

    # example 6 #----------------------------------

    $port      = "/tmp/mysock.file|unix";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '*', # irrelevant for UNIX socket
    #     port  => '/tmp/mysock.file', # not really a port
    #     proto => 'unix', # will use Net::Server::Proto::UNIX
    #     ipv   => '*', # irrelevant for UNIX socket
    # };

    # example 7 #----------------------------------

    $port      = "/tmp/mysock.file|unixdgram";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '*', # irrelevant for UNIX socket
    #     port  => '/tmp/mysock.file', # not really a port
    #     proto => 'unixdgram', # will use Net::Server::Proto::UNIXDGRAM
    #     ipv   => '*', # irrelevant for UNIX socket
    # };

    # example 8 #----------------------------------

    $port      = "/tmp/mysock.file|SOCK_STREAM|unix"; # legacy
    $def_host  = "";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '*', # irrelevant for UNIX socket
    #     port  => '/tmp/mysock.file', # not really a port
    #     proto => 'unix', # will use Net::Server::Proto::UNIX
    #     unix_type => 'SOCK_STREAM',
    #     ipv   => '*', # irrelevant for UNIX socket
    # };

    # example 9 #----------------------------------

    $port      = "/tmp/mysock.file|SOCK_DGRAM|unix"; # legacy
    $def_host  = "";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '*', # irrelevant for UNIX socket
    #     port  => '/tmp/mysock.file', # not really a port
    #     proto => 'unix', # will use Net::Server::Proto::UNIXDGRAM
    #     unix_type => 'SOCK_DGRAM',
    #     ipv   => '*', # irrelevant for UNIX socket
    # };

    # example 10 #----------------------------------

    $port = "someother.com:20203/ssleay";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'ssleay', # will use Net::Server::Proto::SSLEAY
    #     ipv   => *,
    # };

    # example 11 #----------------------------------

    $port = "[::1]:20203 ipv6 tcp";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '::1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 6,
    # };

    # example 12 #----------------------------------

    $port = "[::1]:20203 tcp";
    $def_host  = "default-domain.com/IPv6";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = {
    #     host  => '::1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 6,
    # };

    # example 13 #----------------------------------

    $port = "[someother.com]:20203 ipv6 ipv4 tcp";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = ({
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 4,
    # }, {
    #     host  => 'someother.com',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 6,
    # });

    # example 14 #----------------------------------

    # depending upon your configuration
    $port = "localhost:20203";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = ({
    #     host  => '127.0.0.1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 4, # IPv4
    # }, {
    #     host  => '::1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 6, # IPv6
    # });

    # example 15 #----------------------------------

    # depending upon your configuration
    $port = "localhost:20203";
    $def_host  = "default-domain.com IPv*";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = ({
    #     host  => '127.0.0.1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 4, # IPv4
    # }, {
    #     host  => '::1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 6, # IPv6
    # });

    # example 16 #----------------------------------

    # depending upon your configuration
    $ENV{'IPV'} = '4';
    $port = "localhost:20203";
    $def_host  = "default-domain.com";
    $def_proto = "tcp";
    $def_ipv   = undef;
    @info = Net::Server::Proto->parse_info($port,$def_host,$def_proto,$def_ipv);
    # @info = ({
    #     host  => '127.0.0.1',
    #     port  => 20203,
    #     proto => 'tcp', # will use Net::Server::Proto::TCP
    #     ipv   => 4, # IPv4
    # });

=head1 LICENCE

Distributed under the same terms as Net::Server

=cut
