# -*- perl -*-
#
#  Net::Server::PreFork - Net::Server personality
#
#  $Id: PreFork.pm,v 1.46 2013/01/10 07:16:20 rhandom Exp $
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

package Net::Server::PreFork;

use strict;
use base qw(Net::Server::PreForkSimple);
use Net::Server::SIG qw(register_sig check_sigs);
use POSIX qw(WNOHANG);
use IO::Select ();
use Time::HiRes qw(time);

sub net_server_type { __PACKAGE__ }

sub options {
    my $self = shift;
    my $ref  = $self->SUPER::options(@_);
    my $prop = $self->{'server'};
    $ref->{$_} = \$prop->{$_} for qw(min_servers min_spare_servers max_spare_servers spare_servers
                                     check_for_waiting child_communication check_for_spawn min_child_ttl);
    return $ref;
}


sub post_configure {
    my $self = shift;
    my $prop = $self->{'server'};
    $self->SUPER::post_configure;

    my $d = {
        # max_servers is set in the PreForkSimple server and defaults to 50
        min_servers       => 5,    # min num of servers to always have running
        min_spare_servers => 2,    # min num of servers just sitting there
        max_spare_servers => 10,   # max num of servers just sitting there
        check_for_waiting => 10,   # how often to see if children laying around
        check_for_spawn   => 30,   # how often to see if more children are needed
        min_child_ttl     => 10,   # min time between starting a child and killing one
    };
    $prop->{'min_servers'} = $prop->{'max_servers'}
        if !!defined($prop->{'min_servers'}) && $d->{'min_servers'} > $prop->{'max_servers'};
    $prop->{'max_spare_servers'} = $prop->{'max_servers'} - 1
        if !defined($prop->{'max_spare_servers'}) && $d->{'max_spare_servers'} >= $prop->{'max_servers'};
    if (! defined $prop->{'min_spare_servers'}) {
        my $min = defined($prop->{'min_servers'}) ? $prop->{'min_servers'} : $d->{'min_servers'};
        $prop->{'min_spare_servers'} = $min if $prop > $min;
    }

    foreach (keys %$d){
        $prop->{$_} = $d->{$_} if !defined($prop->{$_}) || $prop->{$_} !~ /^\d+(?:\.\d+)?$/;
    }

    if( $prop->{'max_spare_servers'} >= $prop->{'max_servers'} ){
        $self->fatal("Error: \"max_spare_servers\" must be less than \"max_servers\"");
    }

    if ($prop->{'min_spare_servers'}) {
        $self->fatal("Error: \"min_spare_servers\" ($prop->{'min_spare_servers'}) must be less than \"$_\" ($prop->{$_})")
            for grep {$prop->{'min_spare_servers'} > $prop->{$_}} qw(min_servers max_spare_servers);
    }
}


sub loop {
    my $self = shift;
    my $prop = $self->{'server'};

    pipe(my $read, my $write); # get ready for child->parent communication
    $read->autoflush(1);
    $write->autoflush(1);
    $prop->{'_READ'}  = $read;
    $prop->{'_WRITE'} = $write;

    # get ready for children
    $prop->{'child_select'} = IO::Select->new($read);
    $prop->{'children'} = {};
    $prop->{'reaped_children'} = {};
    if ($ENV{'HUP_CHILDREN'}) {
        foreach my $line (split /\n/, $ENV{'HUP_CHILDREN'}) {
            my ($pid, $status) = ($line =~ /^(\d+)\t(\w+)$/) ? ($1, $2) : next;
            $prop->{'children'}->{$pid} = {status => $status, hup => 1};
        }
    }

    $prop->{'tally'} = {
        time       => time(),
        waiting    => scalar(grep {$_->{'status'} eq 'waiting'}    values %{ $prop->{'children'} }),
        processing => scalar(grep {$_->{'status'} eq 'processing'} values %{ $prop->{'children'} }),
        dequeue    => scalar(grep {$_->{'status'} eq 'dequeue'}    values %{ $prop->{'children'} }),
    };

    my $start = $prop->{'min_servers'};
    $self->log(3, "Beginning prefork ($start processes)");
    $self->run_n_children($start);

    $self->run_parent;
}


sub kill_n_children {
    my ($self, $n) = @_;
    my $prop = $self->{'server'};
    return unless $n > 0;

    my $time = time;
    return unless $time - $prop->{'last_kill'} > 10;
    $prop->{'last_kill'} = $time;

    $self->log(3, "Killing \"$n\" children");

    foreach my $pid (keys %{ $prop->{'children'} }){
        # Only kill waiting children
        # XXX: This is race condition prone as the child may have
        # started handling a connection, but will have to do for now
        my $child = $prop->{'children'}->{$pid};
        next if $child->{'status'} ne 'waiting';

        $n--;

        if (! kill('HUP', $pid)) {
            $self->delete_child($pid);
        }

        last if $n <= 0;
    }
}

sub run_n_children {
    my ($self, $n) = @_;
    my $prop  = $self->{'server'};
    return unless $n > 0;

    $self->run_n_children_hook($n);

    my ($parentsock, $childsock);
    $self->log(3, "Starting \"$n\" children");
    $prop->{'last_start'} = time();

    for (1 .. $n) {

        if ($prop->{'child_communication'}) {
            require IO::Socket::UNIX;
            ($parentsock, $childsock) = IO::Socket::UNIX->socketpair(IO::Socket::AF_UNIX, IO::Socket::SOCK_STREAM, IO::Socket::PF_UNSPEC);
        }

        $self->pre_fork_hook;
        local $!;
        my $pid = fork;
        if (! defined $pid) {
            if ($prop->{'child_communication'}) {
                $parentsock->close();
                $childsock->close();
            }
            $self->fatal("Bad fork [$!]");
        }

        if ($pid) { # parent
            if( $prop->{'child_communication'} ){
                $prop->{'child_select'}->add($parentsock);
                $prop->{'children'}->{$pid}->{'sock'} = $parentsock;
            }

            $prop->{'children'}->{$pid}->{'status'} = 'waiting';
            $prop->{'tally'}->{'waiting'} ++;

        } else { # child
            if ($prop->{'child_communication'}) {
                $prop->{'parent_sock'} = $childsock;
            }
            $self->run_child;
        }
    }
}

sub run_n_children_hook {}

sub run_child {
    my $self = shift;
    my $prop = $self->{'server'};

    $SIG{'INT'} = $SIG{'TERM'} = $SIG{'QUIT'} = sub {
        $self->child_finish_hook;
        exit;
    };
    $SIG{'PIPE'} = 'IGNORE';
    $SIG{'CHLD'} = 'DEFAULT';
    $SIG{'HUP'}  = sub {
        if (! $prop->{'connected'}) {
            $self->child_finish_hook;
            exit;
        }
        $prop->{'SigHUPed'} = 1;
    };

    # Open in child at start
    if ($prop->{'serialize'} eq 'flock') {
        open $prop->{'lock_fh'}, ">", $prop->{'lock_file'}
            or $self->fatal("Couldn't open lock file \"$prop->{'lock_file'}\"[$!]");
    }

    $self->log(4, "Child Preforked ($$)");

    delete @{ $prop }{qw(children tally last_start last_process)};

    $self->child_init_hook;
    my $write = $prop->{'_WRITE'};

    while ($self->accept()) {
        $prop->{'connected'} = 1;
        print $write "$$ processing\n";

        my $ok = eval { $self->run_client_connection; 1 };
        if (! $ok) {
            print $write "$$ exiting\n";
            die $@;
        }

        last if $self->done;

        $prop->{'connected'} = 0;
        print $write "$$ waiting\n";
    }

    $self->child_finish_hook;

    print $write "$$ exiting\n";
    exit;
}


sub run_parent {
    my $self = shift;
    my $prop = $self->{'server'};
    my $id;

    $self->log(4, "Parent ready for children.");
    my $read_fh = $prop->{'_READ'};

    @{ $prop }{qw(last_checked_for_dead last_checked_for_waiting last_checked_for_dequeue last_process last_kill)} = (time) x 5;

    register_sig(
        PIPE => 'IGNORE',
        INT  => sub { $self->server_close() },
        TERM => sub { $self->server_close() },
        HUP  => sub { $self->sig_hup() },
        CHLD => sub {
            while (defined(my $chld = waitpid(-1, WNOHANG))) {
                last unless $chld > 0;
                $self->{'reaped_children'}->{$chld} = 1; # We'll deal with this in coordinate_children to avoid a race
            }
        },
        QUIT => sub { $self->{'server'}->{'kind_quit'} = 1; $self->server_close() },
        TTIN => sub { $self->{'server'}->{$_}++ for qw(min_servers max_servers); $self->log(3, "Increasing server count ($self->{'server'}->{'max_servers'})") },
        TTOU => sub { $self->{'server'}->{$_}-- for qw(min_servers max_servers); $self->log(3, "Decreasing server count ($self->{'server'}->{'max_servers'})") },
    );

    $self->register_sig_pass;

    if ($ENV{'HUP_CHILDREN'}) {
        while (defined(my $chld = waitpid(-1, WNOHANG))) {
            last unless $chld > 0;
            $self->{'reaped_children'}->{$chld} = 1;
        }
    }

    while (1) {
        ### Wait to read.
        ## Normally it is not good to do selects with
        ## getline or <$fh> but this is controlled output
        ## where everything that comes through came from us.
        my @fh = $prop->{'child_select'}->can_read($prop->{'check_for_waiting'});
        if (check_sigs()) {
            last if $prop->{'_HUP'};
        }

        $self->idle_loop_hook(\@fh);

        if (! @fh) {
            $self->coordinate_children();
            next;
        }

        foreach my $fh (@fh) {
            if ($fh != $read_fh) { # preforking server data
                $self->child_is_talking_hook($fh);
                next;
            }

            my $line = <$fh>;
            next if ! defined $line;

            last if $self->parent_read_hook($line); # optional test by user hook

            # child should say "$pid status\n"
            next if $line !~ /^(\d+)\ +(waiting|processing|dequeue|exiting)$/;
            my ($pid, $status) = ($1, $2);

            if (my $child = $prop->{'children'}->{$pid}) {
                if ($status eq 'exiting') {
                    $self->delete_child($pid);

                } else {
                    # Decrement tally of state pid was in (plus sanity check)
                    my $old_status = $child->{'status'}    || $self->log(2, "No status for $pid when changing to $status");
                    --$prop->{'tally'}->{$old_status} >= 0 || $self->log(2, "Tally for $status < 0 changing pid $pid from $old_status to $status");

                    $child->{'status'} = $status;
                    ++$prop->{'tally'}->{$status};

                    $prop->{'last_process'} = time() if $status eq 'processing';
                }
            }
        }
        $self->coordinate_children();
    }
}

sub run_dequeue {
    my $self = shift;
    $self->SUPER::run_dequeue;
    $self->{'server'}->{'tally'}->{'dequeue'}++;
}

sub coordinate_children {
    my $self = shift;
    my $prop = $self->{'server'};
    my $time = time();

    # deleted SIG{'CHLD'} reaped children
    foreach my $pid (keys %{ $self->{'reaped_children'} }) {
        delete $self->{'reaped_children'}->{$pid}; # delete each pid one by one to avoid another race
        next if ! $prop->{'children'}->{$pid};
        $self->delete_child($pid);
    }

    # re-tally the possible types (only twice a minute)
    # this might not be even necessary but is a nice sanity check
    my $tally = $prop->{'tally'} ||= {};
    if ($time - $tally->{'time'} > $prop->{'check_for_spawn'}) {
        my $w = $tally->{'waiting'};
        my $p = $tally->{'processing'};
        $tally = $prop->{'tally'} = {
            time       => $time,
            waiting    => 0,
            processing => 0,
            dequeue    => 0,
        };
        foreach (values %{ $prop->{'children'} }) {
            $tally->{$_->{'status'}}++;
        }
        $w -= $tally->{'waiting'};
        $p -= $tally->{'processing'};
        $self->log(3, "Processing diff ($p), Waiting diff ($w)") if $p || $w;
    }

    my $total = $tally->{'waiting'} + $tally->{'processing'};

    if ($total < $prop->{'min_servers'}) {
        $self->run_n_children($prop->{'min_servers'} - $total); # need more min_servers

    } elsif ($tally->{'waiting'} < $prop->{'min_spare_servers'}
             && $total < $prop->{'max_servers'}) { # need more min_spare_servers (up to max_servers)
        my $n1 = $prop->{'min_spare_servers'} - $tally->{'waiting'};
        my $n2 = $prop->{'max_servers'} - $total;
        $self->run_n_children(($n2 > $n1) ? $n1 : $n2);
    }

    # check to see if we should kill off some children
    if ($time - $prop->{'last_checked_for_waiting'} > $prop->{'check_for_waiting'}) {
        $prop->{'last_checked_for_waiting'} = $time;

        # need fewer max_spare_servers (down to min_servers)
        if ($tally->{'waiting'} > $prop->{'max_spare_servers'}
            && $total > $prop->{'min_servers'}) {

            ### see if we haven't started any in the last ten seconds
            if ($time - $prop->{'last_start'} > $prop->{'min_child_ttl'}) {
                my $n1 = $tally->{'waiting'} - $prop->{'max_spare_servers'};
                my $n2 = $total - $prop->{'min_servers'};
                $self->kill_n_children(($n2 > $n1) ? $n1 : $n2);
            }

        } elsif ($total > $prop->{'max_servers'}) { # how did this happen?
            $self->kill_n_children($total - $prop->{'max_servers'});
        }
    }

    # periodically make sure children are alive
    if ($time - $prop->{'last_checked_for_dead'} > $prop->{'check_for_dead'}) {
        $prop->{'last_checked_for_dead'} = $time;
        foreach my $pid (keys %{ $prop->{'children'} }) {
            kill(0, $pid) || $self->delete_child($pid);
        }
    }

    # take us down to min if we haven't had a request in a while
    if ($time - $prop->{'last_process'} > 30 && $tally->{'waiting'} > $prop->{'min_spare_servers'}) {
        my $n1 = $tally->{'waiting'} - $prop->{'min_spare_servers'};
        my $n2 = $total - $prop->{'min_servers'};
        $self->kill_n_children( ($n2 > $n1) ? $n1 : $n2 );
    }

    # periodically check to see if we should clear the queue
    if (defined $prop->{'check_for_dequeue'}) {
        if ($time - $prop->{'last_checked_for_dequeue'} > $prop->{'check_for_dequeue'}) {
            $prop->{'last_checked_for_dequeue'} = $time;
            if (defined($prop->{'max_dequeue'})
                && $tally->{'dequeue'} < $prop->{'max_dequeue'}) {
                $self->run_dequeue();
            }
        }
    }
}

### delete_child and other modifications contributed by Rob Mueller
sub delete_child {
    my ($self, $pid) = @_;
    my $prop = $self->{'server'};

    my $child = $prop->{'children'}->{$pid};
    if (! $child) {
        $self->log(2, "Attempt to delete already deleted child $pid");
        return;
    }

    return if ! exists $prop->{'children'}->{$pid}; # Already gone?

    my $status = $child->{'status'}    || $self->log(2, "No status for $pid when deleting child");
    --$prop->{'tally'}->{$status} >= 0 || $self->log(2, "Tally for $status < 0 deleting pid $pid");
    $prop->{'tally'}->{'time'} = 0 if $child->{'hup'};

    $self->SUPER::delete_child($pid);
}

sub parent_read_hook {}

sub child_is_talking_hook {}

1;

__END__

=head1 NAME

Net::Server::PreFork - Net::Server personality

=head1 SYNOPSIS

    use base qw(Net::Server::PreFork);

    sub process_request {
        #...code...
    }

    __PACKAGE__->run();

=head1 DESCRIPTION

Please read the pod on Net::Server and Net::Server::PreForkSimple
first.  This module is a personality, or extension, or sub class, of
the Net::Server::PreForkSimple class which is a sub class of
Net::Server.  See L<Net::Server::PreForkSimple>.

This personality binds to one or more ports and then forks
C<min_servers> child process.  The server will make sure that at any
given time there are C<min_spare_servers> available to receive a
client request, up to C<max_servers>.  Each of these children will
process up to C<max_requests> client connections.  This type is good
for a heavily hit site, and should scale well for most applications.
(Multi port accept is accomplished using flock to serialize the
children).

At this time, it does not appear that this module will pass tests on
Win32 systems.  Any ideas or patches for making the tests pass would
be welcome.

=head1 SAMPLE CODE

Please see the sample listed in Net::Server.

=head1 COMMAND LINE ARGUMENTS

In addition to the command line arguments of the Net::Server base
class and the Net::Server::PreForkSimple parent class,
Net::Server::PreFork contains several other configurable parameters.
You really should also see L<Net::Server::PreForkSimple>.

    Key                 Value                   Default
    min_servers         \d+                     5
    min_spare_servers   \d+                     2
    max_spare_servers   \d+                     10
    max_servers         \d+                     50
    max_requests        \d+                     1000

    serialize           (flock|semaphore
                         |pipe|none)            undef
    # serialize defaults to flock on multi_port or on Solaris
    lock_file           "filename"              File::Temp::tempfile or POSIX::tmpnam

    check_for_dead      \d+                     30
    check_for_waiting   \d+                     10

    max_dequeue         \d+                     undef
    check_for_dequeue   \d+                     undef

    child_communication 1                       undef

=over 4

=item min_servers

The minimum number of servers to keep running.

=item min_spare_servers

The minimum number of servers to have waiting for requests.  Minimum
and maximum numbers should not be set to close to each other or the
server will fork and kill children too often.

=item max_spare_servers

The maximum number of servers to have waiting for requests.  See
I<min_spare_servers>.

=item max_servers

The maximum number of child servers to start.  This does not apply to
dequeue processes.

=item check_for_waiting

Seconds to wait before checking to see if we can kill off some waiting
servers.

=item check_for_spawn

Seconds between checking to see if we need to spawn more children

=item min_child_ttl

Minimum number of seconds between starting children and killing a
child process

=item child_communication

Enable child communication to parent via unix sockets.  If set to
true, will let children write to the socket contained in
$self->{'server'}->{'parent_sock'}.  The parent will be notified
through child_is_talking_hook where the first argument is the socket
to the child.  The child's socket is stored in
$self->{'server'}->{'children'}->{$child_pid}->{'sock'}.

=item serialize

See the documentation under L<Net::Server::PreForkSimple>.

=back

=head1 CONFIGURATION FILE

C<Net::Server::PreFork> allows for the use of a configuration file to
read in server parameters.  The format of this conf file is simple key
value pairs.  Comments and white space are ignored.

    #-------------- file test.conf --------------

    ### server information
    min_servers   20
    max_servers   80
    min_spare_servers 10
    min_spare_servers 15

    max_requests  1000

    ### user and group to become
    user        somebody
    group       everybody

    ### logging ?
    log_file    /var/log/server.log
    log_level   3
    pid_file    /tmp/server.pid

    ### access control
    allow       .+\.(net|com)
    allow       domain\.com
    deny        a.+

    ### background the process?
    background  1

    ### ports to bind
    host        127.0.0.1
    port        localhost:20204
    port        20205

    ### reverse lookups ?
    # reverse_lookups on

    ### enable child communication ?
    # child_communication

    #-------------- file test.conf --------------

=head1 PROCESS FLOW

Process flow follows Net::Server until the loop phase.  At this point
C<min_servers> are forked and wait for connections.  When a child
accepts a connection, finishs processing a client, or exits, it relays
that information to the parent, which keeps track and makes sure there
are enough children to fulfill C<min_servers>, C<min_spare_servers>,
C<max_spare_servers>, and C<max_servers>.

=head1 HOOKS

The PreFork server has the following hooks in addition to the hooks
provided by PreForkSimple.  See L<Net::Server::PreForkSimple>.

=over 4

=item C<$self-E<gt>run_n_children_hook()>

This hook occurs at the top of run_n_children which is called each
time the server goes to start more child processes.  This gives the
parent to do a little of its own accountting (as desired).  Idea for
this hook came from James FitzGibbon.

=item C<$self-E<gt>parent_read_hook()>

This hook occurs any time that the parent reads information from the
child.  The line from the child is sent as an argument.

=item C<$self-E<gt>child_is_talking_hook()>

This hook occurs if child_communication is true and the child has
written to $self->{'server'}->{'parent_sock'}.  The first argument
will be the open socket to the child.

=item C<$self-E<gt>idle_loop_hook()>

This hook is called in every pass through the main process wait loop,
every C<check_for_waiting> seconds.  The first argument is a reference
to an array of file descriptors that can be read at the moment.

=back

=head1 HOT DEPLOY

Since version 2.000, the PreFork server has accepted the TTIN and TTOU
signals.  When a TTIN is received, the min and max_servers are
increased by 1.  If a TTOU signal is received the min max_servers are
decreased by 1.  This allows for adjusting the number of handling
processes without having to restart the server.

=head1 BUGS

Tests don't seem to work on Win32.  Any ideas or patches would be
welcome.

=head1 TO DO

See L<Net::Server>

=head1 AUTHOR

Paul T. Seamons paul@seamons.com

=head1 THANKS

See L<Net::Server>

=head1 SEE ALSO

Please see also
L<Net::Server::Fork>,
L<Net::Server::INET>,
L<Net::Server::PreForkSimple>,
L<Net::Server::MultiType>,
L<Net::Server::Single>
L<Net::Server::SIG>
L<Net::Server::Daemonize>
L<Net::Server::Proto>

=cut

