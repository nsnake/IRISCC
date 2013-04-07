# -*- perl -*-
#
#  Net::Server::Proto::TCP - Net::Server Protocol module
#
#  $Id: TCP.pm,v 1.28 2013/01/10 06:11:27 rhandom Exp $
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

package Net::Server::Proto::TCP;

use strict;
use warnings;
use IO::Socket::INET;
use Net::Server::Proto;

our @ISA = qw(IO::Socket::INET); # we may dynamically change this to INET6 based upon our server configuration

sub NS_proto { 'TCP' }
sub NS_port   { my $sock = shift; ${*$sock}{'NS_port'}   = shift if @_; return ${*$sock}{'NS_port'}   }
sub NS_host   { my $sock = shift; ${*$sock}{'NS_host'}   = shift if @_; return ${*$sock}{'NS_host'}   }
sub NS_ipv    { my $sock = shift; ${*$sock}{'NS_ipv'}    = shift if @_; return ${*$sock}{'NS_ipv'}    }
sub NS_listen { my $sock = shift; ${*$sock}{'NS_listen'} = shift if @_; return ${*$sock}{'NS_listen'} }

sub object {
    my ($class, $info, $server) = @_;

    # we cannot do this at compile time because we have not yet read the configuration then
    @ISA = qw(IO::Socket::INET6) if $ISA[0] eq 'IO::Socket::INET' && Net::Server::Proto->requires_ipv6($server);

    my @sock = $class->SUPER::new();
    foreach my $sock (@sock) {
        $sock->NS_host($info->{'host'});
        $sock->NS_port($info->{'port'});
        $sock->NS_ipv( $info->{'ipv'} );
        $sock->NS_listen(defined($info->{'listen'}) ? $info->{'listen'}
                        : defined($server->{'server'}->{'listen'}) ? $server->{'server'}->{'listen'}
                        : Socket::SOMAXCONN());
        ${*$sock}{'NS_orig_port'} = $info->{'orig_port'} if defined $info->{'orig_port'};
    }
    return wantarray ? @sock : $sock[0];
}

sub log_connect {
    my ($sock, $server) = @_;
    $server->log(2, "Binding to ".$sock->NS_proto." port ".$sock->NS_port." on host ".$sock->NS_host." with IPv".$sock->NS_ipv);
}

sub connect {
    my ($sock, $server) = @_;
    my $host = $sock->NS_host;
    my $port = $sock->NS_port;
    my $ipv  = $sock->NS_ipv;
    my $lstn = $sock->NS_listen;

    $sock->SUPER::configure({
        LocalPort => $port,
        Proto     => 'tcp',
        Listen    => $lstn,
        ReuseAddr => 1,
        Reuse     => 1,
        (($host ne '*') ? (LocalAddr => $host) : ()), # * is all
        ($sock->isa("IO::Socket::INET6") ? (Domain => ($ipv eq '6') ? Socket6::AF_INET6() : ($ipv eq '4') ? Socket::AF_INET() : Socket::AF_UNSPEC()) : ()),
    }) || $server->fatal("Can't connect to TCP port $port on $host [$!]");

    if ($port eq '0' and $port = $sock->sockport) {
        $server->log(2, "  Bound to auto-assigned port $port");
        ${*$sock}{'NS_orig_port'} = $sock->NS_port;
        $sock->NS_port($port);
    } elsif ($port =~ /\D/ and $port = $sock->sockport) {
        $server->log(2, "  Bound to service port ".$sock->NS_port()."($port)");
        ${*$sock}{'NS_orig_port'} = $sock->NS_port;
        $sock->NS_port($port);
    }
}

sub reconnect { # after a sig HUP
    my ($sock, $fd, $server, $port) = @_;
    $server->log(3,"Reassociating file descriptor $fd with ".$sock->NS_proto." on [".$sock->NS_host."]:".$sock->NS_port.", using IPv".$sock->NS_ipv);
    $sock->fdopen($fd, 'w') or $server->fatal("Error opening to file descriptor ($fd) [$!]");

    if ($sock->isa("IO::Socket::INET6")) {
        my $ipv = $sock->NS_ipv;
        ${*$sock}{'io_socket_domain'} = ($ipv eq '6') ? Socket6::AF_INET6() : ($ipv eq '4') ? Socket::AF_INET() : Socket::AF_UNSPEC();
    }

    if ($port ne $sock->NS_port) {
        $server->log(2, "  Re-bound to previously assigned port $port");
        ${*$sock}{'NS_orig_port'} = $sock->NS_port;
        $sock->NS_port($port);
    }
}

sub accept {
    my ($sock, $class) = (@_);
    my ($client, $peername);
    if (wantarray) {
        ($client, $peername) = $sock->SUPER::accept($class);
    } else {
        $client = $sock->SUPER::accept($class);
    }
    if (defined $client) {
        $client->NS_port($sock->NS_port);
    }
    return wantarray ? ($client, $peername) : $client;
}

sub poll_cb { # implemented for psgi compatibility - TODO - should poll appropriately for Multipex
    my ($self, $cb) = @_;
    return $cb->($self);
}

###----------------------------------------------------------------###

sub read_until { # only sips the data - but it allows for compatibility with SSLEAY
    my ($client, $bytes, $end_qr) = @_;
    die "One of bytes or end_qr should be defined for TCP read_until\n" if !defined($bytes) && !defined($end_qr);
    my $content = '';
    my $ok = 0;
    while (1) {
        $client->read($content, 1, length($content));
        if (defined($bytes) && length($content) >= $bytes) {
            $ok = 2;
            last;
        } elsif (defined($end_qr) && $content =~ $end_qr) {
            $ok = 1;
            last;
        }
    }
    return wantarray ? ($ok, $content) : $content;
}

###----------------------------------------------------------------###

### a string containing any information necessary for restarting the server
### via a -HUP signal
### a newline is not allowed
### the hup_string must be a unique identifier based on configuration info
sub hup_string {
    my $sock = shift;
    return join "|", $sock->NS_host, $sock->NS_port, $sock->NS_proto, 'ipv'.$sock->NS_ipv, (defined(${*$sock}{'NS_orig_port'}) ? ${*$sock}{'NS_orig_port'} : ());
}

sub show {
    my $sock = shift;
    return "Ref = \"".ref($sock). "\" (".$sock->hup_string.")\n";
}

1;

__END__

=head1 NAME

  Net::Server::Proto::TCP - Net::Server TCP protocol.

=head1 SYNOPSIS

See L<Net::Server::Proto>.

=head1 DESCRIPTION

Protocol module for Net::Server.  This module implements the
SOCK_STREAM socket type under INET (also known as TCP).
See L<Net::Server::Proto>.

=head1 PARAMETERS

There are no additional parameters that can be specified.
See L<Net::Server> for more information on reading arguments.

=head1 INTERNAL METHODS

=over 4

=item C<object>

Returns an object with parameters suitable for eventual creation of
a IO::Socket::INET object listining on UDP.

=item C<log_connect>

Called before binding the socket to provide useful information to the logs.

=item C<connect>

Called when actually binding the port.  Handles default parameters
before calling parent method.

=item C<reconnect>

Called instead of connect method during a server hup.

=item C<accept>

Override of the parent class to make sure necessary parameters are passed down to client sockets.

=item C<poll_cb>

Allow for psgi compatible interface during HTTP server.

=item C<read_until>

Takes a regular expression, reads from the socket until the regular expression is matched.

=item C<hup_string>

Returns a unique identifier that can be passed to the re-exec'ed process during HUP.

=item C<show>

Basic dumper of properties stored in the glob.

=item C<AUTOLOAD>

Handle accessor methods.

=back

=head1 LICENCE

Distributed under the same terms as Net::Server

=cut

