# -*- perl -*-
#
#  Net::Server - Extensible Perl internet server
#
#  $Id: Server.pm,v 1.114 2007/07/25 16:21:14 rhandom Exp $
#
#  Copyright (C) 2001-2007
#
#    Paul Seamons
#    paul@seamons.com
#    http://seamons.com/
#
#    Rob Brown bbb@cpan,org
#
#  This package may be distributed under the terms of either the
#  GNU General Public License
#    or the
#  Perl Artistic License
#
#  All rights reserved.
#
################################################################

package Net::Server;

use strict;
use vars qw($VERSION);
use Socket qw(inet_aton inet_ntoa AF_INET AF_UNIX SOCK_DGRAM SOCK_STREAM);
use IO::Socket ();
use IO::Select ();
use POSIX ();
use Fcntl ();
use FileHandle;
use Net::Server::Proto ();
use Net::Server::Daemonize qw(check_pid_file create_pid_file
                              get_uid get_gid set_uid set_gid
                              safe_fork
                              );

$VERSION = '0.97';

###----------------------------------------------------------------###

sub new {
  my $class = shift || die "Missing class";
  my $args  = @_ == 1 ? shift : {@_};
  my $self  = bless {server => { %$args }}, $class;
  return $self;
}

sub _initialize {
  my $self = shift;

  ### need a place to store properties
  $self->{server} = {} unless defined($self->{server}) && ref($self->{server});

  ### save for a HUP
  $self->commandline($self->_get_commandline)
      if ! eval { $self->commandline };

  ### prepare to cache configuration parameters
  $self->{server}->{conf_file_args} = undef;
  $self->{server}->{configure_args} = undef;

  $self->configure_hook;      # user customizable hook

  $self->configure(@_);       # allow for reading of commandline,
                              # program, and configuration file parameters

  ### allow yet another way to pass defaults
  my $defaults = $self->default_values || {};
  foreach my $key (keys %$defaults) {
    next if ! exists $self->{server}->{$key};
    if (ref $self->{server}->{$key} eq 'ARRAY') {
      if (! @{ $self->{server}->{$key} }) { # was empty
        my $val = $defaults->{$key};
        $self->{server}->{$key} = ref($val) ? $val : [$val];
      }
    } elsif (! defined $self->{server}->{$key}) {
      $self->{server}->{$key} = $defaults->{$key};
    }
  }

  ### get rid of cached config parameters
  delete $self->{server}->{conf_file_args};
  delete $self->{server}->{configure_args};

}

###----------------------------------------------------------------###

### program flow
sub run {

  ### pass package or object
  my $self = ref($_[0]) ? shift() : shift->new;

  $self->_initialize(@_ == 1 ? %{$_[0]} : @_);     # configure all parameters

  $self->post_configure;      # verification of passed parameters

  $self->post_configure_hook; # user customizable hook

  $self->pre_bind;            # finalize ports to be bound

  $self->bind;                # connect to port(s)
                              # setup selection handle for multi port

  $self->post_bind_hook;      # user customizable hook

  $self->post_bind;           # allow for chrooting,
                              # becoming a different user and group

  $self->pre_loop_hook;       # user customizable hook

  $self->loop;                # repeat accept/process cycle

  ### routines inside a standard $self->loop
  # $self->accept             # wait for client connection
  # $self->run_client_connection # process client
  # $self->done               # indicate if connection is done

  $self->server_close;        # close the server and release the port
                              # this will run pre_server_close_hook
                              #               close_children
                              #               post_child_cleanup_hook
                              #               shutdown_sockets
                              # and either exit or run restart_close_hook
}

### standard connection flow
sub run_client_connection {
  my $self = shift;

  $self->post_accept;         # prepare client for processing

  $self->get_client_info;     # determines information about peer and local

  $self->post_accept_hook;    # user customizable hook

  if( $self->allow_deny             # do allow/deny check on client info
      && $self->allow_deny_hook ){  # user customizable hook

    $self->process_request;   # This is where the core functionality
                              # of a Net::Server should be.  This is the
                              # only method necessary to override.
  }else{

    $self->request_denied_hook;     # user customizable hook

  }

  $self->post_process_request_hook; # user customizable hook

  $self->post_process_request;      # clean up client connection, etc

  $self->post_client_connection_hook; # one last hook
}

###----------------------------------------------------------------###

sub _get_commandline {
  my $self = shift;
  my $prop = $self->{server};

  ### see if we can find the full command line
  if (open _CMDLINE, "/proc/$$/cmdline") { # unix specific
    my $line = do { local $/ = undef; <_CMDLINE> };
    close _CMDLINE;
    if ($line =~ /^(.+)$/) { # need to untaint to allow for later hup
      return [split /\0/, $1];
    }
  }

  my $script = $0;
  $script = $ENV{'PWD'} .'/'. $script if $script =~ m|^[^/]+/| && $ENV{'PWD'}; # add absolute to relative
  $script =~ /^(.+)$/; # untaint for later use in hup
  return [ $1, @ARGV ]
}

sub commandline {
    my $self = shift;
    if (@_) { # allow for set
      $self->{server}->{commandline} = ref($_[0]) ? shift : \@_;
    }
    return $self->{server}->{commandline} || die "commandline was not set during initialization";
}

###----------------------------------------------------------------###

### any values to set if no configuration could be found
sub default_values { {} }

### any pre-initialization stuff
sub configure_hook {}


### set up the object a little bit better
sub configure {
  my $self = shift;
  my $prop = $self->{server};
  my $template = undef;
  local @_ = @_; # fix some issues under old perls on alpha systems

  ### allow for a template to be passed
  if( $_[0] && ref($_[0]) ){
    $template = shift;
  }

  ### do command line
  $self->process_args( \@ARGV, $template ) if defined @ARGV;

  ### do startup file args
  ### cache a reference for multiple calls later
  my $args = undef;
  if( $prop->{configure_args} && ref($prop->{configure_args}) ){
    $args = $prop->{configure_args};
  }else{
    $args = $prop->{configure_args} = \@_;
  }
  $self->process_args( $args, $template ) if defined $args;

  ### do a config file
  if( defined $prop->{conf_file} ){
    $self->process_conf( $prop->{conf_file}, $template );
  } else {
    ### look for a default conf_file
    my $def = $self->default_values || {};
    if ($def->{conf_file}) {
        $self->process_conf( $def->{conf_file}, $template );
    }
  }

}


### make sure it has been configured properly
sub post_configure {
  my $self = shift;
  my $prop = $self->{server};

  ### set the log level
  if( !defined $prop->{log_level} || $prop->{log_level} !~ /^\d+$/ ){
    $prop->{log_level} = 2;
  }
  $prop->{log_level} = 4 if $prop->{log_level} > 4;


  ### log to STDERR
  if( ! defined($prop->{log_file}) ){
    $prop->{log_file} = '';

  ### log to syslog
  }elsif( $prop->{log_file} eq 'Sys::Syslog' ){

    $self->open_syslog;

  ### open a logging file
  }elsif( $prop->{log_file} && $prop->{log_file} ne 'Sys::Syslog' ){

    die "Unsecure filename \"$prop->{log_file}\""
      unless $prop->{log_file} =~ m|^([\w\.\-/\\]+)$|;
    $prop->{log_file} = $1;
    open(_SERVER_LOG, ">>$prop->{log_file}")
      or die "Couldn't open log file \"$prop->{log_file}\" [$!].";
    _SERVER_LOG->autoflush(1);
    $prop->{chown_log_file} = 1;

  }

  ### see if a daemon is already running
  if( defined $prop->{pid_file} ){
    if( ! eval{ check_pid_file( $prop->{pid_file} ) } ){
      if (! $ENV{BOUND_SOCKETS}) {
        warn $@;
      }
      $self->fatal( $@ );
    }
  }

  ### completetly daemonize by closing STDIN, STDOUT (should be done before fork)
  if( ! $prop->{_is_inet} ){
    if( $prop->{setsid} || length($prop->{log_file}) ){
      open(STDIN,  '</dev/null') || die "Can't read /dev/null  [$!]";
      open(STDOUT, '>/dev/null') || die "Can't write /dev/null [$!]";
    }
  }

  if (! $ENV{BOUND_SOCKETS}) {
    ### background the process - unless we are hup'ing
    if( $prop->{setsid} || defined($prop->{background}) ){
      my $pid = eval{ safe_fork() };
      if( not defined $pid ){ $self->fatal( $@ ); }
      exit(0) if $pid;
      $self->log(2,"Process Backgrounded");
    }

    ### completely remove myself from parent process - unless we are hup'ing
    if( $prop->{setsid} ){
      &POSIX::setsid();
    }
  }

  ### completetly daemonize by closing STDERR (should be done after fork)
  if( length($prop->{log_file}) && $prop->{log_file} ne 'Sys::Syslog' ){
    open STDERR, '>&_SERVER_LOG' || die "Can't open STDERR to _SERVER_LOG [$!]";
  }elsif( $prop->{setsid} ){
    open STDERR, '>&STDOUT' || die "Can't open STDERR to STDOUT [$!]";
  }

  ### allow for a pid file (must be done after backgrounding and chrooting)
  ### Remove of this pid may fail after a chroot to another location...
  ### however it doesn't interfere either.
  if( defined $prop->{pid_file} ){
    if( eval{ create_pid_file( $prop->{pid_file} ) } ){
      $prop->{pid_file_unlink} = 1;
    }else{
      $self->fatal( $@ );
    }
  }

  ### make sure that allow and deny look like array refs
  $prop->{allow} = [] unless defined($prop->{allow}) && ref($prop->{allow});
  $prop->{deny}  = [] unless defined($prop->{deny})  && ref($prop->{deny} );
  $prop->{cidr_allow} = [] unless defined($prop->{cidr_allow}) && ref($prop->{cidr_allow});
  $prop->{cidr_deny}  = [] unless defined($prop->{cidr_deny})  && ref($prop->{cidr_deny} );

}


### user customizable hook
sub post_configure_hook {}


### make sure we have good port parameters
sub pre_bind {
  my $self = shift;
  my $prop = $self->{server};

  my $ref   = ref($self);
  no strict 'refs';
  my $super = ${"${ref}::ISA"}[0];
  use strict 'refs';
  my $ns_type = (! $super || $ref eq $super) ? '' : " (type $super)";
  $self->log(2,$self->log_time ." ". ref($self) .$ns_type. " starting! pid($$)");

  ### set a default port, host, and proto
  $prop->{port} = [$prop->{port}] if defined($prop->{port}) && ! ref($prop->{port});
  if (! defined($prop->{port}) || ! @{ $prop->{port} }) {
    $self->log(2,"Port Not Defined.  Defaulting to '20203'\n");
    $prop->{port}  = [ 20203 ];
  }

  $prop->{host} = []              if ! defined $prop->{host};
  $prop->{host} = [$prop->{host}] if ! ref     $prop->{host};
  push @{ $prop->{host} }, (($prop->{host}->[-1]) x (@{ $prop->{port} } - @{ $prop->{host}})); # augment hosts with as many as port
  foreach my $host (@{ $prop->{host} }) {
    $host = '*' if ! defined $host || ! length $host;;
    $host = ($host =~ /^([\w\.\-\*\/]+)$/) ? $1 : $self->fatal("Unsecure host \"$host\"");
  }

  $prop->{proto} = []               if ! defined $prop->{proto};
  $prop->{proto} = [$prop->{proto}] if ! ref     $prop->{proto};
  push @{ $prop->{proto} }, (($prop->{proto}->[-1]) x (@{ $prop->{port} } - @{ $prop->{proto}})); # augment hosts with as many as port
  foreach my $proto (@{ $prop->{proto} }) {
      $proto ||= 'tcp';
      $proto = ($proto =~ /^(\w+)$/) ? $1 : $self->fatal("Unsecure proto \"$proto\"");
  }

  ### loop through the passed ports
  ### set up parallel arrays of hosts, ports, and protos
  ### port can be any of many types (tcp,udp,unix, etc)
  ### see perldoc Net::Server::Proto for more information
  my %bound;
  foreach (my $i = 0 ; $i < @{ $prop->{port} } ; $i++) {
    my $port  = $prop->{port}->[$i];
    my $host  = $prop->{host}->[$i];
    my $proto = $prop->{proto}->[$i];
    if ($bound{"$host/$port/$proto"}++) {
      $self->log(2, "Duplicate configuration (".(uc $proto)." port $port on host $host - skipping");
      next;
    }
    my $obj = $self->proto_object($host, $port, $proto) || next;
    push @{ $prop->{sock} }, $obj;
  }
  if (! @{ $prop->{sock} }) {
    $self->fatal("No valid socket parameters found");
  }

  $prop->{listen} = Socket::SOMAXCONN()
    unless defined($prop->{listen}) && $prop->{listen} =~ /^\d{1,3}$/;

}

### method for invoking procol specific bindings
sub proto_object {
  my $self = shift;
  my ($host,$port,$proto) = @_;
  return Net::Server::Proto->object($host,$port,$proto,$self);
}

### bind to the port (This should serve all but INET)
sub bind {
  my $self = shift;
  my $prop = $self->{server};

  ### connect to previously bound ports
  if( exists $ENV{BOUND_SOCKETS} ){

    $self->restart_open_hook();

    $self->log(2, "Binding open file descriptors");

    ### loop through the past information and match things up
    foreach my $info (split /\n/, $ENV{BOUND_SOCKETS}) {
      my ($fd, $hup_string) = split /\|/, $info, 2;
      $fd = ($fd =~ /^(\d+)$/) ? $1 : $self->fatal("Bad file descriptor");
      foreach my $sock ( @{ $prop->{sock} } ){
        if ($hup_string eq $sock->hup_string) {
          $sock->log_connect($self);
          $sock->reconnect($fd, $self);
          last;
        }
      }
    }
    delete $ENV{BOUND_SOCKETS};

  ### connect to fresh ports
  }else{

    foreach my $sock ( @{ $prop->{sock} } ){
      $sock->log_connect($self);
      $sock->connect( $self );
    }

  }

  ### if more than one port we'll need to select on it
  if( @{ $prop->{port} } > 1 || $prop->{multi_port} ){
    $prop->{multi_port} = 1;
    $prop->{select} = IO::Select->new();
    foreach ( @{ $prop->{sock} } ){
      $prop->{select}->add( $_ );
    }
  }else{
    $prop->{multi_port} = undef;
    $prop->{select}     = undef;
  }

}


### user customizable hook
sub post_bind_hook {}


### secure the process and background it
sub post_bind {
  my $self = shift;
  my $prop = $self->{server};


  ### figure out the group(s) to run as
  if( ! defined $prop->{group} ){
    $self->log(1,"Group Not Defined.  Defaulting to EGID '$)'\n");
    $prop->{group}  = $);
  }else{
    if( $prop->{group} =~ /^([\w-]+( [\w-]+)*)$/ ){
      $prop->{group} = eval{ get_gid( $1 ) };
      $self->fatal( $@ ) if $@;
    }else{
      $self->fatal("Invalid group \"$prop->{group}\"");
    }
  }


  ### figure out the user to run as
  if( ! defined $prop->{user} ){
    $self->log(1,"User Not Defined.  Defaulting to EUID '$>'\n");
    $prop->{user}  = $>;
  }else{
    if( $prop->{user} =~ /^([\w-]+)$/ ){
      $prop->{user} = eval{ get_uid( $1 ) };
      $self->fatal( $@ ) if $@;
    }else{
      $self->fatal("Invalid user \"$prop->{user}\"");
    }
  }


  ### chown any files or sockets that we need to
  if( $prop->{group} ne $) || $prop->{user} ne $> ){
    my @chown_files = ();
    foreach my $sock ( @{ $prop->{sock} } ){
      push @chown_files, $sock->NS_unix_path
        if $sock->NS_proto eq 'UNIX';
    }
    if( $prop->{pid_file_unlink} ){
      push @chown_files, $prop->{pid_file};
    }
    if( $prop->{lock_file_unlink} ){
      push @chown_files, $prop->{lock_file};
    }
    if( $prop->{chown_log_file} ){
      delete $prop->{chown_log_file};
      push @chown_files, $prop->{log_file};
    }
    my $uid = $prop->{user};
    my $gid = (split(/\ /,$prop->{group}))[0];
    foreach my $file (@chown_files){
      chown($uid,$gid,$file)
        or $self->fatal("Couldn't chown \"$file\" [$!]\n");
    }
  }


  ### perform the chroot operation
  if( defined $prop->{chroot} ){
    if( ! -d $prop->{chroot} ){
      $self->fatal("Specified chroot \"$prop->{chroot}\" doesn't exist.\n");
    }else{
      $self->log(2,"Chrooting to $prop->{chroot}\n");
      chroot( $prop->{chroot} )
        or $self->fatal("Couldn't chroot to \"$prop->{chroot}\": $!");
    }
  }


  ### drop privileges
  eval{
    if( $prop->{group} ne $) ){
      $self->log(2,"Setting gid to \"$prop->{group}\"");
      set_gid( $prop->{group} );
    }
    if( $prop->{user} ne $> ){
      $self->log(2,"Setting uid to \"$prop->{user}\"");
      set_uid( $prop->{user} );
    }
  };
  if( $@ ){
    if( $> == 0 ){
      $self->fatal( $@ );
    } elsif( $< == 0){
      $self->log(2,"NOTICE: Effective UID changed, but Real UID is 0: $@");
    }else{
      $self->log(2,$@);
    }
  }

  ### record number of request
  $prop->{requests} = 0;

  ### set some sigs
  $SIG{INT} = $SIG{TERM} = $SIG{QUIT} = sub { $self->server_close; };

  ### most cases, a closed pipe will take care of itself
  $SIG{PIPE} = 'IGNORE';

  ### catch children (mainly for Fork and PreFork but works for any chld)
  $SIG{CHLD} = \&sig_chld;

  ### catch sighup
  $SIG{HUP} = sub { $self->sig_hup; }

}

### routine to avoid zombie children
sub sig_chld {
  1 while (waitpid(-1, POSIX::WNOHANG()) > 0);
  $SIG{CHLD} = \&sig_chld;
}


### user customizable hook
sub pre_loop_hook {}


### receive requests
sub loop {
  my $self = shift;

  while( $self->accept ){

    $self->run_client_connection;

    last if $self->done;

  }
}


### wait for the connection
sub accept {
  my $self = shift;
  my $prop = $self->{server};
  my $sock = undef;
  my $retries = 30;

  ### try awhile to get a defined client handle
  ### normally a good handle should occur every time
  while( $retries-- ){

    ### with more than one port, use select to get the next one
    if( defined $prop->{multi_port} ){

      return 0 if defined $prop->{_HUP};

      ### anything server type specific
      $sock = $self->accept_multi_port;
      next unless $sock; # keep trying for the rest of retries

      return 0 if defined $prop->{_HUP};

      if ($self->can_read_hook($sock)) {
        $retries ++;
        next;
      }

    ### single port is bound - just accept
    }else{

      $sock = $prop->{sock}->[0];

    }

    ### make sure we got a good sock
    if( not defined $sock ){
      $self->fatal("Received a bad sock!");
    }

    ### receive a udp packet
    if( SOCK_DGRAM == $sock->getsockopt(Socket::SOL_SOCKET(),Socket::SO_TYPE()) ){
      $prop->{client}   = $sock;
      $prop->{udp_true} = 1;
      $prop->{udp_peer} = $sock->recv($prop->{udp_data},
                                      $sock->NS_recv_len,
                                      $sock->NS_recv_flags,
                                      );

    ### blocking accept per proto
    }else{
      delete $prop->{udp_true};
      $prop->{client} = $sock->accept();

    }

    ### last one if HUPed
    return 0 if defined $prop->{_HUP};

    ### success
    return 1 if defined $prop->{client};

    $self->log(2,"Accept failed with $retries tries left: $!");

    ### try again in a second
    sleep(1);

  }
  $self->log(1,"Ran out of accept retries!");

  return undef;
}


### server specific hook for multi port applications
### this actually applies to all but INET
sub accept_multi_port {
  my $self = shift;
  my $prop = $self->{server};

  if( not exists $prop->{select} ){
    $self->fatal("No select property during multi_port execution.");
  }

  ### this will block until a client arrives
  my @waiting = $prop->{select}->can_read();

  ### if no sockets, return failure
  return undef unless @waiting;

  ### choose a socket
  return $waiting[ rand(@waiting) ];

}

### this occurs after a socket becomes readible on an accept_multi_port call.
### It is passed $self and the $sock that is readible.  A return value
### of true indicates to not pass the handle on to the process_request method and
### to return to accepting
sub can_read_hook {}


### this occurs after the request has been processed
### this is server type specific (actually applies to all by INET)
sub post_accept {
  my $self = shift;
  my $prop = $self->{server};

  ### keep track of the requests
  $prop->{requests} ++;

  return if $prop->{udp_true}; # no need to do STDIN/STDOUT in UDP

  ### duplicate some handles and flush them
  ### maybe we should save these somewhere - maybe not
  if( defined $prop->{client} ){
    if( ! $prop->{no_client_stdout} ){
      my $fileno= fileno $prop->{client};
      close STDIN;
      close STDOUT;
      if( defined $fileno ){
          open STDIN,  "<&$fileno" or die "Couldn't open STDIN to the client socket: $!";
          open STDOUT, ">&$fileno" or die "Couldn't open STDOUT to the client socket: $!";
      } else {
          *STDIN= \*{ $prop->{client} };
          *STDOUT= \*{ $prop->{client} } if ! $prop->{client}->isa('IO::Socket::SSL');
      }
      STDIN->autoflush(1);
      STDOUT->autoflush(1);
      select(STDOUT);
    }
  }else{
    $self->log(1,"Client socket information could not be determined!");
  }

}

### read information about the client connection
sub get_client_info {
  my $self = shift;
  my $prop = $self->{server};
  my $sock = $prop->{client};

  ### handle unix style connections
  if( UNIVERSAL::can($sock,'NS_proto') && $sock->NS_proto eq 'UNIX' ){
    my $path = $sock->NS_unix_path;
    $self->log(3,$self->log_time
               ." CONNECT UNIX Socket: \"$path\"\n");

    return;
  }

  ### read information about this connection
  my $sockname = getsockname( $sock );
  if( $sockname ){
    ($prop->{sockport}, $prop->{sockaddr})
      = Socket::unpack_sockaddr_in( $sockname );
    $prop->{sockaddr} = inet_ntoa( $prop->{sockaddr} );

  }else{
    ### does this only happen from command line?
    $prop->{sockaddr} = '0.0.0.0';
    $prop->{sockhost} = 'inet.test';
    $prop->{sockport} = 0;
  }

  ### try to get some info about the remote host
  my $proto_type = 'TCP';
  if( $prop->{udp_true} ){
    $proto_type = 'UDP';
    ($prop->{peerport} ,$prop->{peeraddr})
      = Socket::sockaddr_in( $prop->{udp_peer} );
  }elsif( $prop->{peername} = getpeername( $sock ) ){
    ($prop->{peerport}, $prop->{peeraddr})
      = Socket::unpack_sockaddr_in( $prop->{peername} );
  }

  if( $prop->{peername} || $prop->{udp_true} ){
    $prop->{peeraddr} = inet_ntoa( $prop->{peeraddr} );

    if( defined $prop->{reverse_lookups} ){
      $prop->{peerhost} = gethostbyaddr( inet_aton($prop->{peeraddr}), AF_INET );
    }
    $prop->{peerhost} = '' unless defined $prop->{peerhost};

  }else{
    ### does this only happen from command line?
    $prop->{peeraddr} = '0.0.0.0';
    $prop->{peerhost} = 'inet.test';
    $prop->{peerport} = 0;
  }

  $self->log(3,$self->log_time
             ." CONNECT $proto_type Peer: \"$prop->{peeraddr}:$prop->{peerport}\""
             ." Local: \"$prop->{sockaddr}:$prop->{sockport}\"\n");

}

### user customizable hook
sub post_accept_hook {}


### perform basic allow/deny service
sub allow_deny {
  my $self = shift;
  my $prop = $self->{server};
  my $sock = $prop->{client};

  ### unix sockets are immune to this check
  if( UNIVERSAL::can($sock,'NS_proto') && $sock->NS_proto eq 'UNIX' ){
    return 1;
  }

  ### if no allow or deny parameters are set, allow all
  return 1 if
       $#{ $prop->{allow} } == -1
    && $#{ $prop->{deny} }  == -1
    && $#{ $prop->{cidr_allow} } == -1
    && $#{ $prop->{cidr_deny} }  == -1;

  ### if the addr or host matches a deny, reject it immediately
  foreach ( @{ $prop->{deny} } ){
    return 0 if $prop->{peerhost} =~ /^$_$/ && defined($prop->{reverse_lookups});
    return 0 if $prop->{peeraddr} =~ /^$_$/;
  }
  if ($#{ $prop->{cidr_deny} } != -1) {
    require Net::CIDR;
    return 0 if Net::CIDR::cidrlookup($prop->{peeraddr}, @{ $prop->{cidr_deny} });
  }


  ### if the addr or host isn't blocked yet, allow it if it is allowed
  foreach ( @{ $prop->{allow} } ){
    return 1 if $prop->{peerhost} =~ /^$_$/ && defined($prop->{reverse_lookups});
    return 1 if $prop->{peeraddr} =~ /^$_$/;
  }
  if ($#{ $prop->{cidr_allow} } != -1) {
    require Net::CIDR;
    return 1 if Net::CIDR::cidrlookup($prop->{peeraddr}, @{ $prop->{cidr_allow} });
  }

  return 0;
}


### user customizable hook
### if this hook returns 1 the request is processed
### if this hook returns 0 the request is denied
sub allow_deny_hook { 1 }


### user customizable hook
sub request_denied_hook {}


### this is the main method to override
### this is where most of the work will occur
### A sample server is shown below.
sub process_request {
  my $self = shift;
  my $prop = $self->{server};

  ### handle udp packets (udp echo server)
  if( $prop->{udp_true} ){
    if( $prop->{udp_data} =~ /dump/ ){
      require Data::Dumper;
      $prop->{client}->send( Data::Dumper::Dumper( $self ) , 0);
    }else{
      $prop->{client}->send("You said \"$prop->{udp_data}\"", 0 );
    }
    return;
  }


  ### handle tcp connections (tcp echo server)
  print "Welcome to \"".ref($self)."\" ($$)\r\n";

  ### eval block needed to prevent DoS by using timeout
  my $timeout = 30; # give the user 30 seconds to type a line
  my $previous_alarm = alarm($timeout);
  eval {

    local $SIG{ALRM} = sub { die "Timed Out!\n" };

    while( <STDIN> ){

      s/\r?\n$//;

      print ref($self),":$$: You said \"$_\"\r\n";
      $self->log(5,$_); # very verbose log

      if( /get (\w+)/ ){
        print "$1: $self->{server}->{$1}\r\n";
      }

      if( /dump/ ){
        require Data::Dumper;
        print Data::Dumper::Dumper( $self );
      }

      if( /quit/ ){ last }

      if( /exit/ ){ $self->server_close }

      alarm($timeout);
    }

  };
  alarm($previous_alarm);


  if ($@ eq "Timed Out!\n") {
    print STDOUT "Timed Out.\r\n";
    return;
  }

}


### user customizable hook
sub post_process_request_hook {}

sub post_client_connection_hook {}

### this is server type specific functions after the process
sub post_process_request {
  my $self = shift;
  my $prop = $self->{server};

  ### don't do anything for udp
  return if $prop->{udp_true};

  ### close the client socket handle
  if( ! $prop->{no_client_stdout} ){
    # close handles - but leave fd's around to prevent spurious messages (Rob Mueller)
    #close STDIN;
    #close STDOUT;
    open(STDIN,  '</dev/null') || die "Can't read /dev/null  [$!]";
    open(STDOUT, '>/dev/null') || die "Can't write /dev/null [$!]";
  }
  close($prop->{client});

}


### determine if I am done with a request
### in the base type, we are never done until a SIG occurs
sub done {
  my $self = shift;
  $self->{server}->{done} = shift if @_;
  return $self->{server}->{done};
}


### fork off a child process to handle dequeuing
sub run_dequeue {
  my $self = shift;
  my $pid  = fork;

  ### trouble
  if( not defined $pid ){
    $self->fatal("Bad fork [$!]");

  ### parent
  }elsif( $pid ){
    $self->{server}->{children}->{$pid}->{status} = 'dequeue';

  ### child
  }else{
    $self->dequeue();
    exit;
  }
}

### sub process which could be implemented to
### perform tasks such as clearing a mail queue.
### currently only supported in PreFork
sub dequeue {}


### user customizable hook
sub pre_server_close_hook {}

### this happens when the server reaches the end
sub server_close{
  my $self = shift;
  my $prop = $self->{server};

  $SIG{INT} = 'DEFAULT';

  ### if this is a child process, signal the parent and close
  ### normally the child shouldn't, but if they do...
  ### otherwise the parent continues with the shutdown
  ### this is safe for non standard forked child processes
  ### as they will not have server_close as a handler
  if (defined $prop->{ppid}
      && $prop->{ppid} != $$
      && ! defined $prop->{no_close_by_child}) {
    $self->close_parent;
    exit;
  }

  ### allow for customizable closing
  $self->pre_server_close_hook;

  $self->log(2,$self->log_time . " Server closing!");

  if (defined $prop->{_HUP} && $prop->{leave_children_open_on_hup}) {
      $self->hup_children;

  } else {
      ### shut down children if any
      if( defined $prop->{children} ){
          $self->close_children();
      }

      ### allow for additional cleanup phase
      $self->post_child_cleanup_hook();
  }

  ### remove files
  if( defined $prop->{lock_file}
      && -e $prop->{lock_file}
      && defined $prop->{lock_file_unlink} ){
    unlink($prop->{lock_file}) || $self->log(1, "Couldn't unlink \"$prop->{lock_file}\" [$!]");
  }
  if( defined $prop->{pid_file}
      && -e $prop->{pid_file}
      && defined $prop->{pid_file_unlink} ){
    unlink($prop->{pid_file}) || $self->log(1, "Couldn't unlink \"$prop->{pid_file}\" [$!]");
  }

  ### HUP process
  if( defined $prop->{_HUP} ){

    $self->restart_close_hook();

    $self->hup_server; # execs at the end
  }

  ### we don't need the ports - close everything down
  $self->shutdown_sockets;

  ### all done - exit
  $self->server_exit;
}

### called at end once the server has exited
sub server_exit { exit }

### allow for fully shutting down the bound sockets
sub shutdown_sockets {
  my $self = shift;
  my $prop = $self->{server};

  ### unlink remaining socket files (if any)
  foreach my $sock ( @{ $prop->{sock} } ){
    $sock->shutdown(2); # close sockets - nobody should be reading/writing still

    unlink $sock->NS_unix_path
      if $sock->NS_proto eq 'UNIX';
  }

  ### delete the sock objects
  $prop->{sock} = [];

  return 1;
}

### Allow children to send INT signal to parent (or use another method)
### This method is only used by forking servers
sub close_parent {
  my $self = shift;
  my $prop = $self->{server};
  die "Missing parent pid (ppid)" if ! $prop->{ppid};
  kill 2, $prop->{ppid};
}

### SIG INT the children
### This method is only used by forking servers (ie Fork, PreFork)
sub close_children {
  my $self = shift;
  my $prop = $self->{server};

  return unless defined $prop->{children} && scalar keys %{ $prop->{children} };

  foreach my $pid (keys %{ $prop->{children} }) {
    ### if it is killable, kill it
    if( ! defined($pid) || kill(15,$pid) || ! kill(0,$pid) ){
      $self->delete_child( $pid );
    }

  }

  ### need to wait off the children
  ### eventually this should probably use &check_sigs
  1 while waitpid(-1, POSIX::WNOHANG()) > 0;

}


sub is_prefork { 0 }

sub hup_children {
  my $self = shift;
  my $prop = $self->{server};

  return unless defined $prop->{children} && scalar keys %{ $prop->{children} };
  return if ! $self->is_prefork;
  $self->log(2, "Sending children hup signal during HUP on prefork server\n");

  foreach my $pid (keys %{ $prop->{children} }) {
      kill(1,$pid); # try to hup it
  }
}

sub post_child_cleanup_hook {}

### handle sig hup
### this will prepare the server for a restart via exec
sub sig_hup {
  my $self = shift;
  my $prop = $self->{server};

  ### prepare for exec
  my $i  = 0;
  my @fd = ();
  $prop->{_HUP} = [];
  foreach my $sock ( @{ $prop->{sock} } ){

    ### duplicate the sock
    my $fd = POSIX::dup($sock->fileno)
      or $self->fatal("Can't dup socket [$!]");

    ### hold on to the socket copy until exec
    $prop->{_HUP}->[$i] = IO::Socket::INET->new;
    $prop->{_HUP}->[$i]->fdopen($fd, 'w')
      or $self->fatal("Can't open to file descriptor [$!]");

    ### turn off the FD_CLOEXEC bit to allow reuse on exec
    $prop->{_HUP}->[$i]->fcntl( Fcntl::F_SETFD(), my $flags = "" );

    ### save host,port,proto, and file descriptor
    push @fd, $fd .'|'. $sock->hup_string;

    ### remove anything that may be blocking
    $sock->close();

    $i++;
  }

  ### remove any blocking obstacle
  if( defined $prop->{select} ){
    delete $prop->{select};
  }

  $ENV{BOUND_SOCKETS} = join("\n", @fd);

  if ($prop->{leave_children_open_on_hup} && scalar keys %{ $prop->{children} }) {
      $ENV{HUP_CHILDREN} = join("\n", map {"$_\t$prop->{children}->{$_}->{status}"} sort keys %{ $prop->{children} });
  }
}

### restart the server using prebound sockets
sub hup_server {
  my $self = shift;

  $self->log(0,$self->log_time()." HUP'ing server");

  delete $ENV{$_} for $self->hup_delete_env_keys;

  exec @{ $self->commandline };
}

sub hup_delete_env_keys { return qw(PATH) }

### this hook occurs if a server has been HUP'ed
### it occurs just before opening to the fileno's
sub restart_open_hook {}

### this hook occurs if a server has been HUP'ed
### it occurs just before exec'ing the server
sub restart_close_hook {}

###----------------------------------------------------------###

### what to do when all else fails
sub fatal {
  my $self = shift;
  my $error = shift;
  my ($package,$file,$line) = caller;
  $self->fatal_hook($error, $package, $file, $line);

  $self->log(0, $self->log_time ." ". $error
             ."\n  at line $line in file $file");

  $self->server_close;
}


### user customizable hook
sub fatal_hook {}

###----------------------------------------------------------###

### handle opening syslog
sub open_syslog {
  my $self = shift;
  my $prop = $self->{server};

  require Sys::Syslog;

  if (ref($prop->{syslog_logsock}) eq 'ARRAY') {
    # do nothing - assume they have what they want
  } else {
    if (! defined $prop->{syslog_logsock}) {
      $prop->{syslog_logsock} = ($Sys::Syslog::VERSION < 0.15) ? 'unix' : '';
    }
    if ($prop->{syslog_logsock} =~ /^(|native|tcp|udp|unix|inet|stream|console)$/) {
      $prop->{syslog_logsock} = $1;
    } else {
      $prop->{syslog_logsock} = ($Sys::Syslog::VERSION < 0.15) ? 'unix' : '';
    }
  }

  my $ident = defined($prop->{syslog_ident})
    ? $prop->{syslog_ident} : 'net_server';
  $prop->{syslog_ident} = ($ident =~ /^([\ -~]+)$/)
    ? $1 : 'net_server';


  my $opt = defined($prop->{syslog_logopt})
    ? $prop->{syslog_logopt} : $Sys::Syslog::VERSION ge '0.15' ? 'pid,nofatal' : 'pid';
  $prop->{syslog_logopt} = ($opt =~ /^( (?: (?:cons|ndelay|nowait|pid|nofatal) (?:$|[,|]) )* )/x)
    ? $1 : 'pid';

  my $fac = defined($prop->{syslog_facility})
    ? $prop->{syslog_facility} : 'daemon';
  $prop->{syslog_facility} = ($fac =~ /^((\w+)($|\|))*/)
    ? $1 : 'daemon';

  if ($prop->{syslog_logsock}) {
    Sys::Syslog::setlogsock($prop->{syslog_logsock}) || die "Syslog err [$!]";
  }
  if( ! Sys::Syslog::openlog($prop->{syslog_ident},
                             $prop->{syslog_logopt},
                             $prop->{syslog_facility}) ){
    die "Couldn't open syslog [$!]" if $prop->{syslog_logopt} ne 'ndelay';
  }
}

### how internal levels map to syslog levels
$Net::Server::syslog_map = {0 => 'err',
                            1 => 'warning',
                            2 => 'notice',
                            3 => 'info',
                            4 => 'debug'};

### record output
sub log {
  my ($self, $level, $msg, @therest) = @_;
  my $prop = $self->{server};

  return if ! $prop->{log_level};

  ### log only to syslog if setup to do syslog
  if (defined($prop->{log_file}) && $prop->{log_file} eq 'Sys::Syslog') {
    if ($level =~ /^\d+$/) {
        return if $level > $prop->{log_level};
        $level = $Net::Server::syslog_map->{$level} || $level;
    }

    my $ok = eval {
      if (@therest) { # if more parameters are passed, we must assume that the first is a format string
        Sys::Syslog::syslog($level, $msg, @therest);
      } else {
        Sys::Syslog::syslog($level, '%s', $msg);
      }
      1;
    };

    if (! $ok) {
        my $err = $@;
        $self->handle_syslog_error($err, [$level, $msg, @therest]);
    }

    return;
  } else {
    return if $level !~ /^\d+$/ || $level > $prop->{log_level};
  }

  $self->write_to_log_hook($level, $msg);
}

### allow catching syslog errors
sub handle_syslog_error {
  my ($self, $error) = @_;
  die $error;
}

### standard log routine, this could very easily be
### overridden with a syslog call
sub write_to_log_hook {
  my ($self, $level, $msg) = @_;
  my $prop = $self->{server};
  chomp $msg;
  $msg =~ s/([^\n\ -\~])/sprintf("%%%02X",ord($1))/eg;

  if( $prop->{log_file} ){
    print _SERVER_LOG $msg, "\n";
  }elsif( $prop->{setsid} ){
    # do nothing
  }else{
    my $old = select(STDERR);
    print $msg. "\n";
    select($old);
  }

}


### default time format
sub log_time {
  my ($sec,$min,$hour,$day,$mon,$year) = localtime;
  return sprintf("%04d/%02d/%02d-%02d:%02d:%02d",
                 $year+1900, $mon+1, $day, $hour, $min, $sec);
}

###----------------------------------------------------------###

### set up default structure
sub options {
  my $self = shift;
  my $prop = $self->{server};
  my $ref  = shift;

  foreach ( qw(port host proto allow deny cidr_allow cidr_deny) ){
    if (! defined $prop->{$_}) {
      $prop->{$_} = [];
    } elsif (! ref $prop->{$_}) {
      $prop->{$_} = [$prop->{$_}]; # nicely turn us into an arrayref if we aren't one already
    }
    $ref->{$_} = $prop->{$_};
  }

  foreach ( qw(conf_file
               user group chroot log_level
               log_file pid_file background setsid
               listen reverse_lookups
               syslog_logsock syslog_ident
               syslog_logopt syslog_facility
               no_close_by_child
               no_client_stdout
               leave_children_open_on_hup
               ) ){
    $ref->{$_} = \$prop->{$_};
  }

}


### routine for parsing commandline, module, and conf file
### possibly should use Getopt::Long but this
### method has the benefit of leaving unused arguments in @ARGV
sub process_args {
  my $self = shift;
  my $ref  = shift;
  my $template = shift; # allow for custom passed in template

  ### if no template is passed, obtain our own
  if (! $template || ! ref($template)) {
    $template = {};
    $self->options( $template );
  }

  ### we want subsequent calls to not overwrite or add to
  ### previously set values so that command line arguments win
  my %previously_set;

  foreach (my $i=0 ; $i < @$ref ; $i++) {

    if ($ref->[$i] =~ /^(?:--)?(\w+)([=\ ](\S+))?$/
        && exists $template->{$1}) {
      my ($key,$val) = ($1,$3);
      splice( @$ref, $i, 1 );
      if (not defined($val)) {
        if ($i > $#$ref
            || ($ref->[$i] && $ref->[$i] =~ /^--\w+/)) {
          $val = 1; # allow for options such as --setsid
        } else {
          $val = splice( @$ref, $i, 1 );
          if (ref $val) {
            die "Found an invalid configuration value for \"$key\" ($val)" if ref($val) ne 'ARRAY';
            $val = $val->[0] if @$val == 1;
          }
        }
      }
      $i--;
      $val =~ s/%([A-F0-9])/chr(hex $1)/eig if ! ref $val;;

      if (ref $template->{$key} eq 'ARRAY') {
        if (! defined $previously_set{$key}) {
          $previously_set{$key} = scalar @{ $template->{$key} };
        }
        next if $previously_set{$key};
        push @{ $template->{$key} }, ref($val) ? @$val : $val;
      } else {
        if (! defined $previously_set{$key}) {
          $previously_set{$key} = defined(${ $template->{$key} }) ? 1 : 0;
        }
        next if $previously_set{$key};
        die "Found multiple values on the configuration item \"$key\" which expects only one value" if ref $val;
        ${ $template->{$key} } = $val;
      }
    }

  }

}


### routine for loading conf file parameters
### cache the args temporarily to handle multiple calls
sub process_conf {
  my $self = shift;
  my $file = shift;
  my $template = shift;
  $template = undef if ! $template || ! ref($template);
  my @args = ();

  if( ! $self->{server}->{conf_file_args} ){
    $file = ($file =~ m|^([\w\.\-\/\\\:]+)$|)
      ? $1 : $self->fatal("Unsecure filename \"$file\"");

    if( not open(_CONF,"<$file") ){
      if (! $ENV{BOUND_SOCKETS}) {
        warn "Couldn't open conf \"$file\" [$!]\n";
      }
      $self->fatal("Couldn't open conf \"$file\" [$!]");
    }

    while(<_CONF>){
      push( @args, "$1=$2") if m/^\s*((?:--)?\w+)(?:\s*[=:]\s*|\s+)(\S+)/;
    }

    close(_CONF);

    $self->{server}->{conf_file_args} = \@args;
  }

  $self->process_args( $self->{server}->{conf_file_args}, $template );
}

### remove a child from the children hash. Not to be called by user.
### if UNIX sockets are in use the socket is removed from the select object.
sub delete_child {
  my $self = shift;
  my $pid  = shift;
  my $prop = $self->{server};

  ### don't remove children that don't belong to me (Christian Mock, Luca Filipozzi)
  return unless exists $prop->{children}->{$pid};

  ### prefork server check to clear child communication
  if( $prop->{child_communication} ){
    if ($prop->{children}->{$pid}->{sock}) {
      $prop->{child_select}->remove( $prop->{children}->{$pid}->{sock} );
      $prop->{children}->{$pid}->{sock}->close;
    }
  }

  delete $prop->{children}->{$pid};
}

###----------------------------------------------------------###
sub get_property {
  my $self = shift;
  my $key  = shift;
  $self->{server} = {} unless defined $self->{server};
  return $self->{server}->{$key} if exists $self->{server}->{$key};
  return undef;
}

sub set_property {
  my $self = shift;
  my $key  = shift;
  $self->{server} = {} unless defined $self->{server};
  $self->{server}->{$key}  = shift;
}

###----------------------------------------------------------------###

1;

### The documentation is in Net/Server.pod
