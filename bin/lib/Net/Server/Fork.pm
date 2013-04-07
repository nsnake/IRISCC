# -*- perl -*-
#
#  Net::Server::Fork - Net::Server personality
#
#  $Id: Fork.pm,v 1.31 2013/01/10 07:17:21 rhandom Exp $
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

package Net::Server::Fork;

use strict;
use base qw(Net::Server);
use Net::Server::SIG qw(register_sig check_sigs);
use Socket qw(SO_TYPE SOL_SOCKET SOCK_DGRAM);
use POSIX qw(WNOHANG);

sub net_server_type { __PACKAGE__ }

sub options {
    my $self = shift;
    my $ref  = $self->SUPER::options(@_);
    my $prop = $self->{'server'};
    $ref->{$_} = \$prop->{$_} for qw(max_servers max_dequeue check_for_dead check_for_dequeue);
    $ref->{'sig_passthrough'} = $prop->{'sig_passthrough'} = [];
    return $ref;
}

sub post_configure {
    my $self = shift;
    my $prop = $self->{'server'};
    $self->SUPER::post_configure(@_);

    $prop->{'max_servers'}    = 256 if ! defined $prop->{'max_servers'};
    $prop->{'check_for_dead'} = 60  if ! defined $prop->{'check_for_dead'};

    $prop->{'ppid'} = $$;
    $prop->{'multi_port'} = 1;
}

sub loop {
    my $self = shift;
    my $prop = $self->{'server'};

    $prop->{'children'} = {};
    if ($ENV{'HUP_CHILDREN'}) {
        my %children = map {/^(\w+)$/; $1} split(/\s+/, $ENV{'HUP_CHILDREN'});
        $children{$_} = {status => $children{$_}, hup => 1} foreach keys %children;
        $prop->{'children'} = \%children;
    }

    # register some of the signals for safe handling
    register_sig(
        PIPE => 'IGNORE',
        INT  => sub { $self->server_close() },
        TERM => sub { $self->server_close() },
        HUP  => sub { $self->sig_hup() },
        CHLD => sub {
            while (defined(my $chld = waitpid(-1, WNOHANG))) {
                last if $chld <= 0;
                $self->delete_child($chld);
            }
        },
        QUIT => sub { $self->{'server'}->{'kind_quit'} = 1; $self->server_close() },
        TTIN => sub { $self->{'server'}->{'max_servers'}++; $self->log(3, "Increasing max server count ($self->{'server'}->{'max_servers'})") },
        TTOU => sub { $self->{'server'}->{'max_servers'}--; $self->log(3, "Decreasing max server count ($self->{'server'}->{'max_servers'})") },
        );

    $self->register_sig_pass;

    if ($ENV{'HUP_CHILDREN'}) {
        while (defined(my $chld = waitpid(-1, WNOHANG))) {
            last unless $chld > 0;
            $self->delete_child($chld);
        }
    }

    my ($last_checked_for_dead, $last_checked_for_dequeue) = (time(), time());

    while (1) {

        ### make sure we don't use too many processes
        my $n_children = grep { $_->{'status'} !~ /dequeue/ } values %{ $prop->{'children'} };
        while ($n_children > $prop->{'max_servers'}){

            select(undef, undef, undef, 5); # block for a moment (don't look too often)
            check_sigs();

            my $time = time();
            if ($time - $last_checked_for_dead > $prop->{'check_for_dead'}) {
                $last_checked_for_dead = $time;
                $self->log(2, "Max number of children reached ($prop->{max_servers}) -- checking for alive.");
                foreach (keys %{ $prop->{'children'} }){
                    kill(0,$_) or $self->delete_child($_);
                }
            }
            $n_children = grep { $_->{'status'} !~ /dequeue/ } values %{ $prop->{'children'} };
        }

        if ($prop->{'check_for_dequeue'}) {
            my $time = time();
            if ($time - $last_checked_for_dequeue > $prop->{'check_for_dequeue'}) {
                $last_checked_for_dequeue = $time;
                if ($prop->{'max_dequeue'}) {
                    my $n_dequeue = grep { $_->{'status'} =~ /dequeue/ } values %{ $prop->{'children'} };
                    $self->run_dequeue() if $n_dequeue < $prop->{'max_dequeue'};
                }
            }
        }

        $self->pre_accept_hook;

        if (! $self->accept()) {
            last if $prop->{'_HUP'};
            last if $prop->{'done'};
            next;
        }

        $self->pre_fork_hook;

        ### fork a child so the parent can go back to listening
        local $!;
        my $pid = fork;
        if (! defined $pid) {
            $self->log(1, "Bad fork [$!]");
            sleep 5;
            next;
        }

        # child
        if (! $pid) {
            $self->run_client_connection;
            exit;
        }

        # parent
        close($prop->{'client'}) if !$prop->{'udp_true'};
        $prop->{'children'}->{$pid}->{'status'} = 'processing';
    }
}

sub pre_accept_hook {};

sub accept {
    my ($self, $class) = @_;
    my $prop = $self->{'server'};

    # block on trying to get a handle (select created because we specified multi_port)
    my @socks = $prop->{'select'}->can_read(2);
    if (check_sigs()) {
        return undef if $prop->{'_HUP'};
        return undef if ! @socks; # don't continue unless we have a connection
    }

    my $sock = $socks[rand @socks];
    return undef if ! defined $sock;

    # check if this is UDP
    if (SOCK_DGRAM == $sock->getsockopt(SOL_SOCKET,SO_TYPE)) {
        $prop->{'udp_true'} = 1;
        $prop->{'client'}   = $sock;
        $prop->{'udp_peer'} = $sock->recv($prop->{'udp_data'}, $sock->NS_recv_len, $sock->NS_recv_flags);

    # Receive a SOCK_STREAM (TCP or UNIX) packet
    } else {
        delete $prop->{'udp_true'};
        $prop->{'client'} = $sock->accept($class) || return;
    }
}

sub run_client_connection {
    my $self = shift;

    ### close the main sock, we still have
    ### the client handle, this will allow us
    ### to HUP the parent at any time
    $_ = undef foreach @{ $self->{'server'}->{'sock'} };

    ### restore sigs (for the child)
    $SIG{'HUP'} = $SIG{'CHLD'} = $SIG{'INT'} = $SIG{'TERM'} = $SIG{'QUIT'} = 'DEFAULT';
    $SIG{'PIPE'} = 'IGNORE';

    delete $self->{'server'}->{'children'};

    $self->child_init_hook;

    $self->SUPER::run_client_connection;

    $self->child_finish_hook;
}

sub close_children {
    my $self = shift;
    $self->SUPER::close_children(@_);

    check_sigs(); # since we have captured signals - make sure we handle them

    register_sig(PIPE => 'DEFAULT',
                 INT  => 'DEFAULT',
                 TERM => 'DEFAULT',
                 QUIT => 'DEFAULT',
                 HUP  => 'DEFAULT',
                 CHLD => 'DEFAULT',
                 TTIN => 'DEFAULT',
                 TTOU => 'DEFAULT',
                 );
}

1;

__END__

=head1 NAME

Net::Server::Fork - Net::Server personality

=head1 SYNOPSIS

    use base qw(Net::Server::Fork);

    sub process_request {
        #...code...
    }

    __PACKAGE__->run();

=head1 DESCRIPTION

Please read the pod on Net::Server first.  This module is a
personality, or extension, or sub class, of the Net::Server module.

This personality binds to one or more ports and then waits for a
client connection.  When a connection is received, the server forks a
child.  The child handles the request and then closes.

With the exception of parent/child signaling, this module will work
(with basic functionality) on Win32 systems.

=head1 ARGUMENTS

=over 4

=item check_for_dead

Number of seconds to wait before looking for dead children.  This only
takes place if the maximum number of child processes (max_servers) has
been reached.  Default is 60 seconds.

=item max_servers

The maximum number of children to fork.  The server will not accept
connections until there are free children. Default is 256 children.

=item max_dequeue

The maximum number of dequeue processes to start.  If a value of zero
or undef is given, no dequeue processes will be started.  The number
of running dequeue processes will be checked by the check_for_dead
variable.

=item check_for_dequeue

Seconds to wait before forking off a dequeue process.  It is intended
to use the dequeue process to take care of items such as mail queues.
If a value of undef is given, no dequeue processes will be started.

=back

=head1 CONFIGURATION FILE

See L<Net::Server>.

=head1 PROCESS FLOW

Process flow follows Net::Server until the post_accept phase.  At this
point a child is forked.  The parent is immediately able to wait for
another request.  The child handles the request and then exits.

=head1 HOOKS

The Fork server has the following hooks in addition to the hooks
provided by the Net::Server base class.  See L<Net::Server>

=over 4

=item C<$self-E<gt>pre_accept_hook()>

This hook occurs just before the accept is called.

=item C<$self-E<gt>post_accept_hook()>

This hook occurs in the child after the accept and fork.

=item C<$self-E<gt>run_dequeue()>

This hook only gets called in conjunction with the check_for_dequeue
setting.

=back

=head1 HOT DEPLOY

Since version 2.000, the Fork server has accepted the TTIN and TTOU
signals.  When a TTIN is received, the max_servers is increased by 1.
If a TTOU signal is received the max_servers is decreased by 1.  This
allows for adjusting the number of handling processes without having
to restart the server.

=head1 AUTHOR

Paul Seamons <paul@seamons.com>

Rob Brown <bbb@cpan.org>

=head1 SEE ALSO

Please see also
L<Net::Server::INET>,
L<Net::Server::PreFork>,
L<Net::Server::MultiType>,
L<Net::Server::SIG>
L<Net::Server::Single>

=cut

