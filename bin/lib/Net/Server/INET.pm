# -*- perl -*-
#
#  Net::Server::INET - Net::Server personality
#
#  $Id: INET.pm,v 1.14 2012/06/07 13:12:34 rhandom Exp $
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

package Net::Server::INET;

use strict;
use base qw(Net::Server);
use Scalar::Util qw(blessed);

sub net_server_type { __PACKAGE__ }

sub post_configure {
    my $self = shift;
    $self->{'server'}->{'_is_inet'} = 1;
    $self->SUPER::post_configure();
    delete $self->{'server'}->{'_is_inet'};
}

sub pre_bind {} # no need to prepare bind

sub bind {} # inet has no port to bind

sub accept { # connection is already accepted
    my $self = shift;
    my $prop = $self->{'server'};

    ### Net::Server::INET will not do any determination of TCP,UDP,Unix
    ### it is up to the programmer to keep these as separate processes
    delete $prop->{'udp_true'}; # not sure if we can do UDP on INET

    1;
}

sub get_client_info {
    my $self = shift;
    my $prop = $self->{'server'};
    my $sock = shift || $prop->{'client'};

    if (blessed($sock) && $sock->can('NS_proto') && $sock->NS_proto eq 'UNIX') {
        $self->log(3, $self->log_time." CONNECT UNIX Socket: \"".$sock->NS_port."\"");
        return;
    }

    $prop->{'sockaddr'} = $ENV{'REMOTE_HOST'} || '0.0.0.0';
    $prop->{'peeraddr'} = '0.0.0.0';
    $prop->{'sockhost'} = $prop->{'peerhost'} = 'inetd.server';
    $prop->{'sockport'} = $prop->{'peerport'} = 0;
    return;
}


sub done { 1 } # accept only one connection per process

sub post_accept { # set up handles
    my $self = shift;

    ### STDIN and STDOUT are already bound

    ### create a handle for those who want to use
    ### an IO::Socket'ish handle - more portable
    ### to just use STDIN and STDOUT though
    $self->{'server'}->{'client'} = Net::Server::INET::Handle->new();

}

### can't hup single process
sub hup_server {}

################################################################
### the rest are methods to tie STDIN and STDOUT to a GLOB
### this most likely isn't necessary, but the methods are there
### support for this is experimental and may go away
################################################################
package Net::Server::INET::Handle;

use base qw(IO::Handle);
use strict;

sub new {
    my $class = shift;
    local *HAND;
    STDIN->autoflush(1);
    STDOUT->autoflush(1);
    tie *HAND, $class, *STDIN, *STDOUT or die "can't tie *HAND: $!";
    bless \*HAND, $class;
    return \*HAND;
}

sub NS_proto { '' }

sub TIEHANDLE {
  my ($class, $in, $out) = @_;
  bless [ \$in, \$out ], $class;
}

sub PRINT {
    my $handle = shift()->[1];
    local *FH = $$handle;
    CORE::print FH @_;
}

sub PRINTF {
    my $handle = shift()->[1];
    local *FH = $$handle;
    CORE::printf FH @_;
}

sub WRITE {
    my $handle = shift()->[1];
    local *FH = $$handle;
    local ($\) = "";
    $_[1] = length($_[0]) unless defined $_[1];
    CORE::print FH substr($_[0], $_[2] || 0, $_[1]);
}

sub READ {
    my $handle = shift()->[0];
    local *FH = $$handle;
    CORE::read(FH, $_[0], $_[1], $_[2] || 0);
}

sub READLINE {
    my $handle = shift()->[0];
    local *FH = $$handle;
    return scalar <FH>;
}

sub GETC {
    my $handle = shift()->[0];
    local *FH = $$handle;
    return CORE::getc(FH);
}

sub EOF {
    my $handle = shift()->[0];
    local *FH = $$handle;
    return CORE::eof(FH);
}

sub OPEN {}

sub CLOSE {
    my $self = shift;
    $self = undef;
}

sub BINMODE {}

sub TELL {}

sub SEEK {}

sub DESTROY {}

sub FILENO {}

sub FETCH {}

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
        }
        elsif (defined($end_qr) && $content =~ $end_qr) {
            $ok = 1;
            last;
        }
    }
    return wantarray ? ($ok, $content) : $content;
}

1;


__END__

=head1 NAME

Net::Server::INET - Net::Server personality

=head1 SYNOPSIS

    use base qw(Net::Server::INET);

    sub process_request {
        #...code...
    }

    Net::Server::INET->run();

=head1 DESCRIPTION

Please read the pod on Net::Server first.  This module is a
personality, or extension, or sub class, of the Net::Server module.

This personality is intended for use with inetd.  It offers no methods
beyond the Net::Server base class.  This module operates by overriding
the pre_bind, bind, accept, and post_accept methods to let all socket
processing to be done by inetd.

=head1 CONFIGURATION FILE

See L<Net::Server>.

=head1 PROCESS FLOW

See L<Net::Server>

=head1 HOOKS

There are no additional hooks in Net::Server::INET.

=head1 TO DO

See L<Net::Server>

=head1 AUTHOR

Paul T. Seamons paul@seamons.com

=head1 SEE ALSO

Please see also
L<Net::Server::Fork>,
L<Net::Server::INET>,
L<Net::Server::PreFork>,
L<Net::Server::MultiType>,
L<Net::Server::Single>

=cut


