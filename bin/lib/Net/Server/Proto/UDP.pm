# -*- perl -*-
#
#  Net::Server::Proto::UDP - Net::Server Protocol module
#
#  $Id: UDP.pm,v 1.27 2013/01/10 05:44:03 rhandom Exp $
#
#  Copyright (C) 2001-2012
#
#    Paul Seamons
#    paul@seamons.com
#    http://seamons.com/
#
#  Modified 2005 by Timothy Watt
#    Added ability to deal with broadcast packets.
#
#  This package may be distributed under the terms of either the
#  GNU General Public License
#    or the
#  Perl Artistic License
#
#  All rights reserved.
#
################################################################

package Net::Server::Proto::UDP;

use strict;
use base qw(Net::Server::Proto::TCP);

my @udp_args = qw(
    udp_recv_len
    udp_recv_flags
    udp_broadcast
);

sub NS_proto { 'UDP' }
sub NS_recv_len   { my $sock = shift; ${*$sock}{'NS_recv_len'}   = shift if @_; return ${*$sock}{'NS_recv_len'}   }
sub NS_recv_flags { my $sock = shift; ${*$sock}{'NS_recv_flags'} = shift if @_; return ${*$sock}{'NS_recv_flags'} }
sub NS_broadcast  { my $sock = shift; ${*$sock}{'NS_broadcast'}  = shift if @_; return ${*$sock}{'NS_broadcast'}  }

sub object {
    my ($class, $info, $server) = @_;

    # we cannot do this at compile time because we have not yet read the configuration then
    # (this is the height of rudeness changing another's class on their behalf)
    @Net::Server::Proto::TCP::ISA = qw(IO::Socket::INET6) if $Net::Server::Proto::TCP::ISA[0] eq 'IO::Socket::INET' && Net::Server::Proto->requires_ipv6($server);

    my $udp = $server->{'server'}->{'udp_args'} ||= do {
        my %temp = map {$_ => undef} @udp_args;
        $server->configure({map {$_ => \$temp{$_}} @udp_args});
        \%temp;
    };

    my $len = defined($info->{'udp_recv_len'}) ? $info->{'udp_recv_len'}
            : defined($udp->{'udp_recv_len'})  ? $udp->{'udp_recv_len'}
            : 4096;
    $len = ($len =~ /^(\d+)$/) ? $1 : 4096;

    my $flg = defined($info->{'udp_recv_flags'}) ? $info->{'udp_recv_flags'}
            : defined($udp->{'udp_recv_flags'})  ? $udp->{'udp_recv_flags'}
            : 0;
    $flg = ($flg =~ /^(\d+)$/) ? $1 : 0;

    my @sock = $class->SUPER::new(); # it is possible that multiple connections will be returned if INET6 is in effect
    foreach my $sock (@sock) {
        $sock->NS_host($info->{'host'});
        $sock->NS_port($info->{'port'});
        $sock->NS_ipv( $info->{'ipv'} );
        $sock->NS_recv_len($len);
        $sock->NS_recv_flags($flg);
        $sock->NS_broadcast(exists($info->{'udp_broadcast'}) ? $info->{'udp_broadcast'} : $udp->{'upd_broadcast'});
        ${*$sock}{'NS_orig_port'} = $info->{'orig_port'} if defined $info->{'orig_port'};
    }
    return wantarray ? @sock : $sock[0];
}

sub connect {
    my ($sock, $server) = @_;
    my $host = $sock->NS_host;
    my $port = $sock->NS_port;
    my $ipv  = $sock->NS_ipv;

    $sock->SUPER::configure({
        LocalPort => $port,
        Proto     => 'udp',
        ReuseAddr => 1,
        Reuse => 1, # may not be needed on UDP
        (($host ne '*') ? (LocalAddr => $host) : ()), # * is all
        ($sock->isa("IO::Socket::INET6") ? (Domain => ($ipv eq '6') ? Socket6::AF_INET6() : ($ipv eq '4') ? Socket::AF_INET() : Socket::AF_UNSPEC()) : ()),
        ($sock->NS_broadcast ? (Broadcast => 1) : ()),
    }) or $server->fatal("Cannot bind to UDP port $port on $host [$!]");

    if ($port eq 0 and $port = $sock->sockport) {
        $server->log(2, "  Bound to auto-assigned port $port");
        ${*$sock}{'NS_orig_port'} = $sock->NS_port;
        $sock->NS_port($port);
    } elsif ($port =~ /\D/ and $port = $sock->sockport) {
        $server->log(2, "  Bound to service port ".$sock->NS_port()."($port)");
        ${*$sock}{'NS_orig_port'} = $sock->NS_port;
        $sock->NS_port($port);
    }
}

1;

__END__

=head1 NAME

Net::Server::Proto::UDP - Net::Server UDP protocol.

=head1 SYNOPSIS

See L<Net::Server::Proto>.

=head1 DESCRIPTION

Protocol module for Net::Server.  This module implements the
SOCK_DGRAM socket type under INET (also known as UDP).
See L<Net::Server::Proto>.

=head1 PARAMETERS

The following paramaters may be specified in addition to
normal command line parameters for a Net::Server.  See
L<Net::Server> for more information on reading arguments.

=over 4

=item udp_recv_len

Specifies the number of bytes to read from the UDP connection
handle.  Data will be read into $self->{'server'}->{'udp_data'}.
Default is 4096.  See L<IO::Socket::INET> and L<recv>.

=item udp_recv_flags

See L<recv>.  Default is 0.

=item udp_broadcast

Default is undef.

=back

=head1 QUICK PARAMETER LIST

  Key               Value                    Default

  ## UDP protocol parameters
  udp_recv_len      \d+                      4096
  udp_recv_flags    \d+                      0
  udp_broadcast     bool                     undef

=head1 INTERNAL METHODS

=over 4

=item C<object>

Returns an object with parameters suitable for eventual creation of
a IO::Socket::INET object listining on UDP.

=item C<connect>

Called when actually binding the port.  Handles default parameters
before calling parent method.

=back

=head1 LICENCE

Distributed under the same terms as Net::Server

=cut

