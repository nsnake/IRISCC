# -*- perl -*-
#
#  Net::Server::PreForkSimple - Net::Server personality
#
#  $Id: PreForkSimple.pm,v 1.43 2013/01/10 07:16:02 rhandom Exp $
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

package Net::Server::PreForkSimple;

use strict;
use base qw(Net::Server);
use Net::Server::SIG qw(register_sig check_sigs);
use POSIX qw(WNOHANG EINTR);
use Fcntl ();

sub net_server_type { __PACKAGE__ }

sub options {
    my $self = shift;
    my $ref  = $self->SUPER::options(@_);
    my $prop = $self->{'server'};

    $ref->{$_} = \$prop->{$_} for qw(max_servers     max_requests      max_dequeue
                                     check_for_dead  check_for_dequeue
                                     lock_file       serialize);
    $ref->{'sig_passthrough'} = $prop->{'sig_passthrough'} = [];
    return $ref;
}

sub post_configure {
    my $self = shift;
    my $prop = $self->{'server'};
    $self->SUPER::post_configure;

    ### some default values to check for
    my $d = {
        max_servers       => 50,   # max num of servers to run
        max_requests      => 1000, # num of requests for each child to handle
        check_for_dead    => 30,   # how often to see if children are alive
    };
    foreach (keys %$d){
        $prop->{$_} = $d->{$_}
            unless defined($prop->{$_}) && $prop->{$_} =~ /^\d+$/;
    }

    $prop->{'ppid'} = $$;
}


sub post_bind {
    my $self = shift;
    my $prop = $self->{'server'};
    $self->SUPER::post_bind;

    if ($prop->{'multi_port'} && $prop->{'serialize'} && $prop->{'serialize'} eq 'none') {
        $self->log(2, "Passed serialize value of none is incompatible with multiple ports - using default serialize");
        delete $prop->{'serialize'};
    }
    if (!$prop->{'serialize'}
        || $prop->{'serialize'} !~ /^(flock|semaphore|pipe|none)$/i) {
        $prop->{'serialize'} = ($^O eq 'MSWin32') ? 'pipe' : 'flock';
    }
    $prop->{'serialize'} =~ tr/A-Z/a-z/;

    if ($prop->{'serialize'} eq 'flock') {
        $self->log(3, "Setting up serialization via flock");
        if (defined $prop->{'lock_file'}) {
            $prop->{'lock_file_unlink'} = undef;
        } else {
            $prop->{'lock_file'} = eval { require File::Temp } ? File::Temp::tmpnam() : POSIX::tmpnam();
            $prop->{'lock_file_unlink'} = 1;
        }

    } elsif ($prop->{'serialize'} eq 'semaphore') {
        $self->log(3, "Setting up serialization via semaphore");
        require IPC::SysV;
        require IPC::Semaphore;
        my $s = IPC::Semaphore->new(IPC::SysV::IPC_PRIVATE(), 1, IPC::SysV::S_IRWXU() | IPC::SysV::IPC_CREAT())
             or $self->fatal("Semaphore error [$!]");
        $s->setall(1) or $self->fatal("Semaphore create error [$!]");
        $prop->{'sem'} = $s;

    } elsif ($prop->{'serialize'} eq 'pipe') {
        $self->log(3, "Setting up serialization via pipe");
        pipe(my $waiting, my $ready);
        $ready->autoflush(1);
        $waiting->autoflush(1);
        $prop->{'_READY'}   = $ready;
        $prop->{'_WAITING'} = $waiting;
        print $ready "First\n";
    } elsif ($prop->{'serialize'} eq 'none') {
        $self->log(3, "Using no serialization");
    } else {
        $self->fatal("Unknown serialization type \"$prop->{'serialize'}\"");
    }

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

    $self->log(3, "Beginning prefork ($prop->{'max_servers'} processes)");

    $self->run_n_children($prop->{'max_servers'});

    $self->run_parent;

}

sub run_n_children {
    my ($self, $n) = @_;
    return if $n <= 0;
    my $prop = $self->{'server'};

    $self->run_n_children_hook;

    $self->log(3, "Starting \"$n\" children");

    for (1 .. $n) {
        $self->pre_fork_hook;
        local $!;
        my $pid = fork;
        $self->fatal("Bad fork [$!]") if ! defined $pid;

        if ($pid) {
            $prop->{'children'}->{$pid}->{'status'} = 'processing';
        } else {
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

    my $needs_lock = ($prop->{'serialize'} eq 'flock') ? 1 : 0;
    if ($needs_lock) {
        open($prop->{'lock_fh'}, ">", $prop->{'lock_file'})
            or $self->fatal("Couldn't open lock file \"$prop->{'lock_file'}\"[$!]");
    }

    $self->log(4, "Child Preforked ($$)");
    delete $prop->{'children'};

    $self->child_init_hook;

    while ($self->accept()) {
        $prop->{'connected'} = 1;
        $self->run_client_connection;
        $prop->{'connected'} = 0;
        last if $self->done;
    }

    $self->child_finish_hook;

    close($prop->{'lock_fh'}) if $needs_lock && $prop->{'lock_fh'};

    $self->log(4, "Child leaving ($prop->{'max_requests'})");
    exit;

}

sub is_prefork { 1 }

### We can only let one process do the selecting at a time
### this override makes sure that nobody else can do it
### while we are.  We do this either by opening a lock file
### and getting an exclusive lock (this will block all others
### until we release it) or by using semaphores to block
sub accept {
    my $self = shift;
    my $prop = $self->{'server'};

    if ($prop->{'serialize'} eq 'flock') {
        while (! flock $prop->{'lock_fh'}, Fcntl::LOCK_EX()) {
            next if $! == EINTR;
            $self->fatal("Couldn't get lock on file \"$prop->{'lock_file'}\" [$!]");
        }
        my $v = $self->SUPER::accept();
        flock $prop->{'lock_fh'}, Fcntl::LOCK_UN();
        return $v;
    } elsif ($prop->{'serialize'} eq 'semaphore') {
        $prop->{'sem'}->op(0, -1, IPC::SysV::SEM_UNDO()) or $self->fatal("Semaphore Error [$!]");
        my $v = $self->SUPER::accept();
        $prop->{'sem'}->op(0, 1, IPC::SysV::SEM_UNDO()) or $self->fatal("Semaphore Error [$!]");
        return $v;
    } elsif ($prop->{'serialize'} eq 'pipe') {
        my $waiting = $prop->{'_WAITING'};
        scalar <$waiting>; # read one line - kernel says who gets it
        my $v = $self->SUPER::accept();
        print { $prop->{'_READY'} } "Next!\n";
        return $v;
    } else {
        my $v = $self->SUPER::accept();
        return $v;
    }
}

sub done {
    my $self = shift;
    my $prop = $self->{'server'};
    $prop->{'done'} = shift if @_;
    return 1 if $prop->{'done'};
    return 1 if $prop->{'requests'} >= $prop->{'max_requests'};
    return 1 if $prop->{'SigHUPed'};
    if (! kill 0, $prop->{'ppid'}) {
        $self->log(3, "Parent process gone away. Shutting down");
        return 1;
    }
}

sub run_parent {
    my $self=shift;
    my $prop = $self->{'server'};

    $self->log(4, "Parent ready for children.");

    $prop->{'last_checked_for_dead'} = $prop->{'last_checked_for_dequeue'} = time();

    register_sig(
        PIPE => 'IGNORE',
        INT  => sub { $self->server_close() },
        TERM => sub { $self->server_close() },
        HUP  => sub { $self->sig_hup() },
        CHLD => sub {
            while (defined(my $chld = waitpid(-1, WNOHANG))) {
                last unless $chld > 0;
                $self->delete_child($chld);
            }
        },
        QUIT => sub { $self->{'server'}->{'kind_quit'} = 1; $self->server_close() },
        TTIN => sub { $self->{'server'}->{'max_servers'}++; $self->log(3, "Increasing max server count ($self->{'server'}->{'max_servers'})") },
        TTOU => sub {
            $self->{'server'}->{'max_servers'}--;
            $self->log(3, "Decreasing max server count ($self->{'server'}->{'max_servers'})");
            if (defined(my $pid = each %{ $prop->{'children'} })) {
                $self->delete_child($pid) if ! kill('HUP', $pid);
            }
        },
        );

    $self->register_sig_pass;

    if ($ENV{'HUP_CHILDREN'}) {
        while (defined(my $chld = waitpid(-1, WNOHANG))) {
            last unless $chld > 0;
            $self->delete_child($chld);
        }
    }

    while (1) {
        select undef, undef, undef, 10;

        if (check_sigs()){
            last if $prop->{'_HUP'};
        }

        $self->idle_loop_hook();

        # periodically make sure children are alive
        my $time = time();
        if ($time - $prop->{'last_checked_for_dead'} > $prop->{'check_for_dead'}) {
            $prop->{'last_checked_for_dead'} = $time;
            foreach (keys %{ $prop->{'children'} }) {
                kill(0,$_) or $self->delete_child($_);
            }
        }

        # make sure we always have max_servers
        my $total_n = 0;
        my $total_d = 0;
        foreach (values %{ $prop->{'children'} }){
            if( $_->{'status'} eq 'dequeue' ){
                $total_d ++;
            }else{
                $total_n ++;
            }
        }

        if( $prop->{'max_servers'} > $total_n ){
            $self->run_n_children( $prop->{'max_servers'} - $total_n );
        }

        # periodically check to see if we should clear the queue
        if( defined $prop->{'check_for_dequeue'} ){
            if( $time - $prop->{'last_checked_for_dequeue'}
            > $prop->{'check_for_dequeue'} ){
                $prop->{'last_checked_for_dequeue'} = $time;
                if( defined($prop->{'max_dequeue'})
                && $total_d < $prop->{'max_dequeue'} ){
                    $self->run_dequeue();
                }
            }
        }

    }
}

sub idle_loop_hook {}

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

Net::Server::PreForkSimple - Net::Server personality

=head1 SYNOPSIS

    use base qw(Net::Server::PreForkSimple);

    sub process_request {
        #...code...
    }

    __PACKAGE__->run();

=head1 DESCRIPTION

Please read the pod on Net::Server first.  This module is a
personality, or extension, or sub class, of the Net::Server module.

This personality binds to one or more ports and then forks
C<max_servers> child processes.  The server will make sure that at any
given time there are always C<max_servers> available to receive a
client request.  Each of these children will process up to
C<max_requests> client connections.  This type is good for a heavily
hit site that can keep C<max_servers> processes dedicated to the
serving.  (Multi port accept defaults to using flock to serialize the
children).

At this time, it does not appear that this module will pass tests on
Win32 systems.  Any ideas or patches for making the tests pass would
be welcome.

=head1 SAMPLE CODE

Please see the sample listed in Net::Server.

=head1 COMMAND LINE ARGUMENTS

In addition to the command line arguments of the Net::Server base
class, Net::Server::PreFork contains several other configurable
parameters.

    Key               Value                   Default
    max_servers       \d+                     50
    max_requests      \d+                     1000

    serialize         (flock|semaphore
                       |pipe|none)  undef
    # serialize defaults to flock on multi_port or on Solaris
    lock_file         "filename"              File::Temp::tempfile or POSIX::tmpnam

    check_for_dead    \d+                     30

    max_dequeue       \d+                     undef
    check_for_dequeue \d+                     undef

=over 4

=item max_servers

The maximum number of child servers to start and maintain.  This does
not apply to dequeue processes.

=item max_requests

The number of client connections to receive before a child terminates.

=item serialize

Determines whether the server serializes child connections.  Options
are undef, flock, semaphore, pipe, or none.  Default is undef.  On
multi_port servers or on servers running on Solaris, the default is
flock.  The flock option uses blocking exclusive flock on the file
specified in I<lock_file> (see below).  The semaphore option uses
IPC::Semaphore (thanks to Bennett Todd) for giving some sample code.
The pipe option reads on a pipe to choose the next.  the flock option
should be the most bulletproof while the pipe option should be the
most portable.  (Flock is able to reliquish the block if the process
dies between accept on the socket and reading of the client connection
- semaphore and pipe do not).  An option of none will not perform
any serialization.  If "none" is passed and there are multiple ports
then a the default serialization will be used insted of "none."

=item lock_file

Filename to use in flock serialized accept in order to serialize the
accept sequece between the children.  This will default to a generated
temporary filename.  If default value is used the lock_file will be
removed when the server closes.

=item check_for_dead

Seconds to wait before checking to see if a child died without letting
the parent know.

=item max_dequeue

The maximum number of dequeue processes to start.  If a value of zero
or undef is given, no dequeue processes will be started.  The number
of running dequeue processes will be checked by the check_for_dead
variable.

=item check_for_dequeue

Seconds to wait before forking off a dequeue process.  The run_dequeue
hook must be defined when using this setting.  It is intended to use
the dequeue process to take care of items such as mail queues.  If a
value of undef is given, no dequeue processes will be started.

=back

=head1 CONFIGURATION FILE

C<Net::Server::PreFork> allows for the use of a configuration file to
read in server parameters.  The format of this conf file is simple key
value pairs.  Comments and white space are ignored.

    #-------------- file test.conf --------------

    ### server information
    max_servers   80

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

    #-------------- file test.conf --------------

=head1 PROCESS FLOW

Process flow follows Net::Server until the loop phase.  At this point
C<max_servers> are forked and wait for connections.  When a child
accepts a connection, finishs processing a client, or exits, it relays
that information to the parent, which keeps track and makes sure there
are always C<max_servers> running.

=head1 HOOKS

The PreForkSimple server has the following hooks in addition to the
hooks provided by the Net::Server base class.  See L<Net::Server>

=over 4

=item C<$self-E<gt>run_n_children_hook()>

This hook occurs at the top of run_n_children which is called each
time the server goes to start more child processes.  This gives the
parent to do a little of its own accountting (as desired).  Idea for
this hook came from James FitzGibbon.

=item C<$self-E<gt>child_init_hook()>

This hook takes place immeditately after the child process forks from
the parent and before the child begins accepting connections.  It is
intended for any addiotional chrooting or other security measures.  It
is suggested that all perl modules be used by this point, so that the
most shared memory possible is used.

=item C<$self-E<gt>child_finish_hook()>

This hook takes place immediately before the child tells the parent
that it is exiting.  It is intended for saving out logged information
or other general cleanup.

=item C<$self-E<gt>run_dequeue()>

This hook only gets called in conjunction with the check_for_dequeue
setting.

=item C<$self-E<gt>idle_loop_hook()>

This hook is called in every pass through the main process wait loop.

=back

=head1 HOT DEPLOY

Since version 2.000, the PreForkSimple server has accepted the TTIN
and TTOU signals.  When a TTIN is received, the max_servers is
increased by 1.  If a TTOU signal is received the max_servers is
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
L<Net::Server::PreFork>,
L<Net::Server::MultiType>,
L<Net::Server::Single>
L<Net::Server::SIG>
L<Net::Server::Daemonize>
L<Net::Server::Proto>

=cut

