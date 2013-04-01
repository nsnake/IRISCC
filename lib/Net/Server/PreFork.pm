# -*- perl -*-
#
#  Net::Server::PreFork - Net::Server personality
#
#  $Id: PreFork.pm,v 1.35 2007/03/23 22:21:51 rhandom Exp $
#
#  Copyright (C) 2001-2007
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

use base qw(Net::Server::PreForkSimple);
use strict;
use vars qw($VERSION);
use POSIX qw(WNOHANG);
use Net::Server::SIG qw(register_sig check_sigs);
use IO::Select ();
use IO::Socket::UNIX;

$VERSION = $Net::Server::VERSION; # done until separated

### override-able options for this package
sub options {
  my $self = shift;
  my $prop = $self->{server};
  my $ref  = shift;

  $self->SUPER::options($ref);

  foreach ( qw(min_servers
               min_spare_servers max_spare_servers
               spare_servers
               check_for_waiting
               child_communication
               ) ){
    $prop->{$_} = undef unless exists $prop->{$_};
    $ref->{$_} = \$prop->{$_};
  }

}

### make sure some defaults are set
sub post_configure {
  my $self = shift;
  my $prop = $self->{server};

  ### legacy check
  if( defined($prop->{spare_servers}) ){
    die "The Net::Server::PreFork argument \"spare_servers\" has been
deprecated as of version '0.75' in order to implement greater child
control.  The new arguments to take \"spare_servers\" place are
\"min_spare_servers\" and \"max_spare_servers\".  The defaults are 5
and 15 respectively.  Please remove \"spare_servers\" from your
argument list.  See the Perldoc Net::Server::PreFork for more
information.
";
  }

  ### let the parent do the rest
  ### must do this first so that ppid reflects backgrounded process
  $self->SUPER::post_configure;

  ### some default values to check for
  my $d = {
    # max_servers is set in the PreForkSimple server and defaults to 50
    min_servers       => 5,    # min num of servers to always have running
    min_spare_servers => 2,    # min num of servers just sitting there
    max_spare_servers => 10,   # max num of servers just sitting there
    check_for_waiting => 10,   # how often to see if children laying around
  };
  foreach (keys %$d){
    $prop->{$_} = $d->{$_}
    unless defined($prop->{$_}) && $prop->{$_} =~ /^\d+$/;
  }

  if( $prop->{min_spare_servers} > $prop->{max_spare_servers} ){
    $self->fatal("Error: \"min_spare_servers\" must be less than "
                 ."\"max_spare_servers\"");
  }

  if( $prop->{min_spare_servers} > $prop->{min_servers} ){
    $self->fatal("Error: \"min_spare_servers\" must be less than "
                 ."\"min_servers\"");
  }

  if( $prop->{max_spare_servers} >= $prop->{max_servers} ){
    $self->fatal("Error: \"max_spare_servers\" must be less than "
                 ."\"max_servers\"");
  }

}


### prepare for connections
sub loop {
  my $self = shift;
  my $prop = $self->{server};

  ### get ready for child->parent communication
  pipe(_READ,_WRITE);
  _READ->autoflush(1); # ASAP, before first child is ever forked
  _WRITE->autoflush(1);
  $prop->{_READ}  = *_READ;
  $prop->{_WRITE} = *_WRITE;

  ### get ready for children
  $prop->{child_select} = IO::Select->new(\*_READ);
  $prop->{children} = {};
  $prop->{reaped_children} = {};
  if ($ENV{HUP_CHILDREN}) {
      my %children = map {/^(\w+)$/; $1} split(/\s+/, $ENV{HUP_CHILDREN});
      $children{$_} = {status => $children{$_}, hup => 1} foreach keys %children;
      $prop->{children} = \%children;
  }

  my $start = $prop->{min_servers};

  $self->log(3,"Beginning prefork ($start processes)\n");

  ### keep track of the status
  $prop->{tally} = {time       => time(),
                    waiting    => scalar(grep {$_->{'status'} eq 'waiting'}    values %{ $prop->{children} }),
                    processing => scalar(grep {$_->{'status'} eq 'processing'} values %{ $prop->{children} }),
                    dequeue    => scalar(grep {$_->{'status'} eq 'dequeue'}    values %{ $prop->{children} }),};

  ### start up the children
  $self->run_n_children( $start );

  ### finish the parent routines
  $self->run_parent;

}

### sub to kill of a specified number of children
sub kill_n_children {
  my $self = shift;
  my $prop = $self->{server};
  my $n    = shift;
  return unless $n > 0;

  my $time = time;
  return unless $time - $prop->{last_kill} > 10;
  $prop->{last_kill} = $time;

  $self->log(3,"Killing \"$n\" children");

  foreach my $pid (keys %{ $prop->{children} }){
    # Only kill waiting children
    # XXX: This is race condition prone as the child may have
    # started handling a connection, but will have to do for now
    my $child = $prop->{children}->{$pid};
    next unless $child->{status} eq 'waiting';

    $n--;

    ### try to kill the child
    if (! kill('HUP', $pid)) {
      $self->delete_child($pid);
    }

    last if $n <= 0;
  }
}

### subroutine to start up a specified number of children
sub run_n_children {
  my $self  = shift;
  my $prop  = $self->{server};
  my $n     = shift;
  return unless $n > 0;

  $self->run_n_children_hook;

  my ($parentsock, $childsock);

  $self->log(3,"Starting \"$n\" children");
  $prop->{last_start} = time();

  for( 1..$n ){

    if( $prop->{child_communication} ) {
      ($parentsock, $childsock) =
        IO::Socket::UNIX->socketpair(AF_UNIX, SOCK_STREAM, PF_UNSPEC);
    }

    my $pid = fork;

    ### trouble
    if( not defined $pid ){
      if( $prop->{child_communication} ){
        $parentsock->close();
        $childsock->close();
      }

      $self->fatal("Bad fork [$!]");

    ### parent
    }elsif( $pid ){
      if( $prop->{child_communication} ){
	$prop->{child_select}->add($parentsock);
        $prop->{children}->{$pid}->{sock} = $parentsock;
      }

      $prop->{children}->{$pid}->{status} = 'waiting';
      $prop->{tally}->{waiting} ++;

    ### child
    }else{
      if( $prop->{child_communication} ){
        $prop->{parent_sock} = $childsock;
      }
      $self->run_child;

    }
  }
}

### let the parent have more accounting upon startup of children
sub run_n_children_hook {}

### child process which will accept on the port
sub run_child {
  my $self = shift;
  my $prop = $self->{server};

  $SIG{INT} = $SIG{TERM} = $SIG{QUIT} = sub {
    $self->child_finish_hook;
    exit;
  };
  $SIG{PIPE} = 'IGNORE';
  $SIG{CHLD} = 'DEFAULT';
  $SIG{HUP}  = sub {
    if (! $prop->{connected}) {
      $self->child_finish_hook;
      exit;
    }
    $prop->{SigHUPed} = 1;
  };

  # Open in child at start
  open($prop->{lock_fh}, ">$prop->{lock_file}")
    || $self->fatal("Couldn't open lock file \"$prop->{lock_file}\"[$!]");

  $self->log(4,"Child Preforked ($$)\n");

  delete $prop->{$_} foreach qw(children tally last_start last_process);

  $self->child_init_hook;

  ### accept connections
  while( $self->accept() ){

    $prop->{connected} = 1;
    print _WRITE "$$ processing\n";

    eval { $self->run_client_connection };
    if ($@) {
      print _WRITE "$$ exiting\n";
      die $@;
    }

    last if $self->done;

    $prop->{connected} = 0;
    print _WRITE "$$ waiting\n";

  }

  $self->child_finish_hook;

  print _WRITE "$$ exiting\n";
  exit;

}


### now the parent will wait for the kids
sub run_parent {
  my $self=shift;
  my $prop = $self->{server};
  my $id;

  $self->log(4,"Parent ready for children.\n");

  ### prepare to read from children
  local *_READ = $prop->{_READ};

  ### set some waypoints
  $prop->{last_checked_for_dead}
  = $prop->{last_checked_for_waiting}
  = $prop->{last_checked_for_dequeue}
  = $prop->{last_process}
  = $prop->{last_kill}
  = time();

  ### register some of the signals for safe handling
  register_sig(PIPE => 'IGNORE',
               INT  => sub { $self->server_close() },
               TERM => sub { $self->server_close() },
               QUIT => sub { $self->server_close() },
               HUP  => sub { $self->sig_hup() },
               CHLD => sub {
                 while ( defined(my $chld = waitpid(-1, WNOHANG)) ){
                   last unless $chld > 0;
                   # We'll deal with this in coordinate_children to avoid a race
                   $self->{reaped_children}->{$chld} = 1;
                 }
               },
### uncomment this area to allow SIG USR1 to give some runtime debugging
#               USR1 => sub {
#                 require "Data/Dumper.pm";
#                 print Data::Dumper::Dumper($self);
#               },
               );

  ### loop on reading info from the children
  while( 1 ){

    ### Wait to read.
    ## Normally it is not good to do selects with
    ## getline or <$fh> but this is controlled output
    ## where everything that comes through came from us.
    my @fh = $prop->{child_select}->can_read($prop->{check_for_waiting});
    if( &check_sigs() ){
      last if $prop->{_HUP};
    }
    if( ! @fh ){
      $self->coordinate_children();
      next;
    }

    ### process every readable handle
    foreach my $fh (@fh) {

      ### preforking server data
      if ($fh == \*_READ) {

        ### read a line
        my $line = <$fh>;
        next if not defined $line;

        ### optional test by user hook
        last if $self->parent_read_hook($line);

        ### child should say "$pid status\n"
        next unless $line =~ /^(\d+)\ +(waiting|processing|dequeue|exiting)$/;
        my ($pid,$status) = ($1,$2);

        # Check child details still exist
        if (my $child = $prop->{children}->{$pid}) {

          # Delete child if it tells us it's exiting
          if ($status eq 'exiting') {
            $self->delete_child($pid);

          # Changing state
          } else {

            # Decrement tally of state pid was in (plus sanity check)
            my $old_status = $child->{status}
              || $self->log(2, "No status for $pid when changing to $status\n");
            --$prop->{tally}->{$old_status} >= 0
              || $self->log(2, "Tally for $status < 0 changing pid $pid from $old_status to $status\n");

            # Set child status and increment tally
            $child->{status} = $status;
            ++$prop->{tally}->{$status};

            $prop->{last_process} = time()
              if $status eq 'processing';
          }
        }

      ### user defined handler
      }else{
        $self->child_is_talking_hook($fh);
      }
    }

    ### check up on the children
    $self->coordinate_children();

  }

  ### allow fall back to main run method
}


### routine to determine if more children need to be started or stopped
sub coordinate_children {
  my $self = shift;
  my $prop = $self->{server};
  my $time = time();

  ### deleted SIG{CHLD} repeaped children
  foreach my $pid (keys %{ $self->{reaped_children} }) {
    # delete each pid one by one to avoid another race
    delete $self->{reaped_children}->{$pid};

    # Only delete if not already deleted
    next if ! $prop->{children}->{$pid};

    $self->delete_child($pid);
  }

  ### re-tally the possible types (only twice a minute)
  ### this might not be even necessary but is a nice sanity check
  if( $time - $prop->{tally}->{time} > 30 ){
    my $w = $prop->{tally}->{waiting};
    my $p = $prop->{tally}->{processing};
    $prop->{tally} = {time       => $time,
                      waiting    => 0,
                      processing => 0,
                      dequeue    => 0};
    foreach (values %{ $prop->{children} }){
      $prop->{tally}->{$_->{status}} ++;
    }
    $w -= $prop->{tally}->{waiting};
    $p -= $prop->{tally}->{processing};
    $self->log(3,"Processing diff ($p), Waiting diff ($w)")
      if $p || $w;
  }

  my $total = $prop->{tally}->{waiting} + $prop->{tally}->{processing};

  ### need more min_servers
  if( $total < $prop->{min_servers} ){
    $self->run_n_children( $prop->{min_servers} - $total );

  ### need more min_spare_servers (up to max_servers)
  }elsif( $prop->{tally}->{waiting} < $prop->{min_spare_servers}
          && $total < $prop->{max_servers} ){
    my $n1 = $prop->{min_spare_servers} - $prop->{tally}->{waiting};
    my $n2 = $prop->{max_servers} - $total;
    $self->run_n_children( ($n2 > $n1) ? $n1 : $n2 );

  }


  ### check to see if we should kill off some children
  if( $time - $prop->{last_checked_for_waiting} > $prop->{check_for_waiting} ){
    $prop->{last_checked_for_waiting} = $time;

    ### need fewer max_spare_servers (down to min_servers)
    if( $prop->{tally}->{waiting} > $prop->{max_spare_servers}
        && $total > $prop->{min_servers} ){

      ### see if we haven't started any in the last ten seconds
      if( $time - $prop->{last_start} > 10 ){
        my $n1 = $prop->{tally}->{waiting} - $prop->{max_spare_servers};
        my $n2 = $total - $prop->{min_servers};
        $self->kill_n_children( ($n2 > $n1) ? $n1 : $n2 );
      }

    ### how did this happen?
    }elsif( $total > $prop->{max_servers} ){
      $self->kill_n_children( $total - $prop->{max_servers} );

    }
  }

  ### periodically make sure children are alive
  if ($time - $prop->{last_checked_for_dead} > $prop->{check_for_dead}) {
    $prop->{last_checked_for_dead} = $time;
    foreach my $pid (keys %{ $prop->{children} }) {
      ### see if the child can be killed
      if (! kill(0, $pid)) {
        $self->delete_child($pid);
      }
    }
  }

  ### take us down to min if we haven't had a request in a while
  if( $time - $prop->{last_process} > 30 && $prop->{tally}->{waiting} > $prop->{min_spare_servers} ){
    my $n1 = $prop->{tally}->{waiting} - $prop->{min_spare_servers};
    my $n2 = $total - $prop->{min_servers};
    $self->kill_n_children( ($n2 > $n1) ? $n1 : $n2 );
  }

  ### periodically check to see if we should clear the queue
  if( defined $prop->{check_for_dequeue} ){
    if( $time - $prop->{last_checked_for_dequeue} > $prop->{check_for_dequeue} ){
      $prop->{last_checked_for_dequeue} = $time;
      if( defined($prop->{max_dequeue})
          && $prop->{tally}->{dequeue} < $prop->{max_dequeue} ){
        $self->run_dequeue();
      }
    }
  }

}

### delete_child and other modifications contributed by Rob Mueller
sub delete_child {
  my $self = shift;
  my $prop = $self->{server};
  my $pid = shift;

  my $child = $prop->{children}->{$pid};
  if (! $child) {
    $self->log(2, "Attempt to delete already deleted child $pid\n");
    return;
  }

  # Already gone?
  return if ! exists $prop->{children}->{$pid};

  my $status = $child->{status}
    || $self->log(2, "No status for $pid when deleting child\n");
  --$prop->{tally}->{$status} >= 0
    || $self->log(2, "Tally for $status < 0 deleting pid $pid\n");
  $prop->{tally}->{time} = 0 if $child->{hup};

  $self->SUPER::delete_child($pid);
}

### allow for other process to tie in to the parent read
sub parent_read_hook {}

sub child_is_talking_hook {}

1;

__END__

=head1 NAME

Net::Server::PreFork - Net::Server personality

=head1 SYNOPSIS

  use Net::Server::PreFork;
  @ISA = qw(Net::Server::PreFork);

  sub process_request {
     #...code...
  }

  __PACKAGE__->run();

=head1 DESCRIPTION

Please read the pod on Net::Server and Net::Server::PreForkSimple
first.  This module is a personality, or extension, or sub class,
of the Net::Server::PreForkSimple class which is a sub class of
Net::Server.  See L<Net::Server::PreForkSimple>.

This personality binds to one or more ports and then forks
C<min_servers> child process.  The server will make sure
that at any given time there are C<min_spare_servers> available
to receive a client request, up to C<max_servers>.  Each of
these children will process up to C<max_requests> client
connections.  This type is good for a heavily hit site, and
should scale well for most applications.  (Multi port accept
is accomplished using flock to serialize the children).

At this time, it does not appear that this module will pass tests on
Win32 systems.  Any ideas or patches for making the tests pass would be
welcome.

=head1 SAMPLE CODE

Please see the sample listed in Net::Server.

=head1 COMMAND LINE ARGUMENTS

In addition to the command line arguments of the Net::Server
base class and the Net::Server::PreForkSimple parent class,
Net::Server::PreFork contains several other configurable
parameters.  You really should also see
L<Net::Server::PreForkSimple>.

  Key                 Value                   Default
  min_servers         \d+                     5
  min_spare_servers   \d+                     2
  max_spare_servers   \d+                     10
  max_servers         \d+                     50
  max_requests        \d+                     1000

  serialize           (flock|semaphore|pipe)  undef
  # serialize defaults to flock on multi_port or on Solaris
  lock_file           "filename"              POSIX::tmpnam

  check_for_dead      \d+                     30
  check_for_waiting   \d+                     10

  max_dequeue         \d+                     undef
  check_for_dequeue   \d+                     undef

  child_communication 1                       undef

=over 4

=item min_servers

The minimum number of servers to keep running.

=item min_spare_servers

The minimum number of servers to have waiting for requests.
Minimum and maximum numbers should not be set to close to
each other or the server will fork and kill children too
often.

=item max_spare_servers

The maximum number of servers to have waiting for requests.
See I<min_spare_servers>.

=item max_servers

The maximum number of child servers to start.  This does not
apply to dequeue processes.

=item check_for_waiting

Seconds to wait before checking to see if we can kill
off some waiting servers.

=item child_communication

Enable child communication to parent via unix sockets.  If set
to true, will let children write to the socket contained in
$self->{server}->{parent_sock}.  The parent will be notified through
child_is_talking_hook where the first argument is the socket
to the child.  The child's socket is stored in
$self->{server}->{children}->{$child_pid}->{sock}.

=back

=head1 CONFIGURATION FILE

C<Net::Server::PreFork> allows for the use of a
configuration file to read in server parameters.  The format
of this conf file is simple key value pairs.  Comments and
white space are ignored.

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

Process flow follows Net::Server until the loop phase.  At
this point C<min_servers> are forked and wait for
connections.  When a child accepts a connection, finishs
processing a client, or exits, it relays that information to
the parent, which keeps track and makes sure there are
enough children to fulfill C<min_servers>, C<min_spare_servers>,
C<max_spare_servers>, and C<max_servers>.

=head1 HOOKS

The PreFork server has the following hooks in addition
to the hooks provided by PreForkSimple.
See L<Net::Server::PreForkSimple>.

=over 4

=item C<$self-E<gt>run_n_children_hook()>

This hook occurs at the top of run_n_children which is called
each time the server goes to start more child processes.  This
gives the parent to do a little of its own accountting (as desired).
Idea for this hook came from James FitzGibbon.

=item C<$self-E<gt>parent_read_hook()>

This hook occurs any time that the parent reads information
from the child.  The line from the child is sent as an
argument.

=item C<$self-E<gt>child_is_talking_hook()>

This hook occurs if child_communication is true and the child
has written to $self->{server}->{parent_sock}.  The first argument
will be the open socket to the child.

=back

=head1 BUGS

Tests don't seem to work on Win32.  Any ideas or patches would be welcome.

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

