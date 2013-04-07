# -*- perl -*-
#
#  Net::Server - Extensible Perl internet server
#
#  $Id: Server.pm,v 1.158 2013/01/10 07:37:17 rhandom Exp $
#
#  Copyright (C) 2001-2013
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
use Socket qw(AF_INET AF_UNIX SOCK_DGRAM SOCK_STREAM);
use IO::Socket ();
use IO::Select ();
use POSIX ();
use Net::Server::Proto ();
use Net::Server::Daemonize qw(check_pid_file create_pid_file safe_fork
                              get_uid get_gid set_uid set_gid);

our $VERSION = '2.007';

sub new {
    my $class = shift || die "Missing class";
    my $args  = @_ == 1 ? shift : {@_};
    return bless {server => {%$args}}, $class;
}

sub net_server_type { __PACKAGE__ }
sub get_property { $_[0]->{'server'}->{$_[1]} }
sub set_property { $_[0]->{'server'}->{$_[1]} = $_[2] }

sub run {
    my $self = ref($_[0]) ? shift() : shift->new;  # pass package or object
    $self->{'server'}->{'_run_args'} = [@_ == 1 ? %{$_[0]} : @_];
    $self->_initialize;         # configure all parameters

    $self->post_configure;      # verification of passed parameters
    $self->post_configure_hook; # user customizable hook

    $self->pre_bind;            # finalize ports to be bound
    $self->bind;                # connect to port(s), setup selection handle for multi port
    $self->post_bind_hook;      # user customizable hook
    $self->post_bind;           # allow for chrooting, becoming a different user and group

    $self->pre_loop_hook;       # user customizable hook
    $self->loop;                # repeat accept/process cycle

    $self->server_close;        # close the server and release the port
}

sub run_client_connection {
    my $self = shift;
    my $c = $self->{'server'}->{'client'};

    $self->post_accept($c);         # prepare client for processing
    $self->get_client_info($c);     # determines information about peer and local
    $self->post_accept_hook($c);    # user customizable hook

    my $ok = $self->allow_deny($c) && $self->allow_deny_hook($c); # do allow/deny check on client info
    if ($ok) {
        $self->process_request($c);   # This is where the core functionality of a Net::Server should be.
    } else {
        $self->request_denied_hook($c);     # user customizable hook
    }

    $self->post_process_request_hook($ok); # user customizable hook
    $self->post_process_request;           # clean up client connection, etc
    $self->post_client_connection_hook;    # one last hook
}

###----------------------------------------------------------------###

sub _initialize {
    my $self = shift;
    my $prop = $self->{'server'} ||= {};

    $self->commandline($self->_get_commandline) if ! eval { $self->commandline }; # save for a HUP
    $self->configure_hook;      # user customizable hook
    $self->configure;           # allow for reading of commandline, program, and configuration file parameters

    my @defaults = %{ $self->default_values || {} }; # allow yet another way to pass defaults
    $self->process_args(\@defaults) if @defaults;
}

sub commandline {
    my $self = shift;
    $self->{'server'}->{'commandline'} = ref($_[0]) ? shift : \@_ if @_;
    return $self->{'server'}->{'commandline'} || die "commandline was not set during initialization";
}

sub _get_commandline {
    my $self = shift;
    my $script = $0;
    $script = $ENV{'PWD'} .'/'. $script if $script =~ m|^[^/]+/| && $ENV{'PWD'}; # add absolute to relative - avoid Cwd
    $script =~ /^(.+)$/; # untaint for later use in hup
    return [$1, @ARGV]
}

sub configure_hook {}

sub configure {
    my $self = shift;
    my $prop = $self->{'server'};
    my $template = ($_[0] && ref($_[0])) ? shift : undef;

    $self->process_args(\@ARGV, $template) if @ARGV; # command line
    $self->process_args($prop->{'_run_args'}, $template) if $prop->{'_run_args'}; # passed to run

    if ($prop->{'conf_file'}) {
        $self->process_args($self->_read_conf($prop->{'conf_file'}), $template);
    } else {
        my $def = $self->default_values || {};
        $self->process_args($self->_read_conf($def->{'conf_file'}), $template) if $def->{'conf_file'};
    }
}

sub default_values { {} }

sub post_configure {
    my $self = shift;
    my $prop = $self->{'server'};

    $prop->{'log_level'} = 2 if ! defined($prop->{'log_level'}) || $prop->{'log_level'} !~ /^\d+$/;
    $prop->{'log_level'} = 4 if $prop->{'log_level'} > 4;
    $self->initialize_logging;

    if ($prop->{'pid_file'}) { # see if a daemon is already running
        if (! eval{ check_pid_file($prop->{'pid_file'}) }) {
            warn $@ if !$ENV{'BOUND_SOCKETS'};
            $self->fatal(my $e = $@);
        }
    }

    if (! $prop->{'_is_inet'}) { # completetly daemonize by closing STDIN, STDOUT (should be done before fork)
        if ($prop->{'setsid'} || length($prop->{'log_file'})) {
            open(STDIN,  '<', '/dev/null') || die "Cannot read /dev/null  [$!]";
            open(STDOUT, '>', '/dev/null') || die "Cannot write /dev/null [$!]";
        }
    }

    if (!$ENV{'BOUND_SOCKETS'}) { # don't need to redo this if hup'ing
        if ($prop->{'setsid'} || $prop->{'background'}) {
            my $pid = eval { safe_fork() };
            $self->fatal(my $e = $@) if ! defined $pid;
            exit(0) if $pid;
            $self->log(2, "Process Backgrounded");
        }

        POSIX::setsid() if $prop->{'setsid'}; # completely remove myself from parent process
    }

    if (length($prop->{'log_file'})
        && !$prop->{'log_function'}) {
        open STDERR, '>&_SERVER_LOG' || die "Cannot open STDERR to _SERVER_LOG [$!]";
    } elsif ($prop->{'setsid'}) { # completely daemonize by closing STDERR (should be done after fork)
        open STDERR, '>&STDOUT' || die "Cannot open STDERR to STDOUT [$!]";
    }

    # allow for a pid file (must be done after backgrounding and chrooting)
    # Remove of this pid may fail after a chroot to another location... however it doesn't interfere either.
    if ($prop->{'pid_file'}) {
        if (eval { create_pid_file($prop->{'pid_file'}) }) {
            $prop->{'pid_file_unlink'} = 1;
        } else {
            $self->fatal(my $e = $@);
        }
    }

    # make sure that allow and deny look like array refs
    $prop->{$_} = [] for grep {! ref $prop->{$_}} qw(allow deny cidr_allow cidr_deny);
}

sub initialize_logging {
    my $self = shift;
    my $prop = $self->{'server'};
    if (! defined($prop->{'log_file'})) {
        $prop->{'log_file'} = ''; # log to STDERR
        return;
    }

    # pluggable logging
    if ($prop->{'log_file'} =~ /^([a-zA-Z]\w*(?:::[a-zA-Z]\w*)*)$/) {
        my $pkg  = "Net::Server::Log::$prop->{'log_file'}";
        (my $file = "$pkg.pm") =~ s|::|/|g;
        if (eval { require $file }) {
            $prop->{'log_function'} = $pkg->initialize($self);
            $prop->{'log_class'}    = $pkg;
            return;
        } elsif ($file =~ /::/ || grep {-e "$_/$file"} @INC) {
            $self->fatal("Unable to load log module $pkg from file $file: $@");
        }
    }

    # regular file based logging
    die "Unsecure filename \"$prop->{'log_file'}\"" if $prop->{'log_file'} !~ m|^([\:\w\.\-/\\]+)$|;
    $prop->{'log_file'} = $1; # open a logging file
    open(_SERVER_LOG, ">>", $prop->{'log_file'})
        || die "Couldn't open log file \"$prop->{'log_file'}\" [$!].";
    _SERVER_LOG->autoflush(1);
    push @{ $prop->{'chown_files'} }, $prop->{'log_file'};
}

sub post_configure_hook {}

sub _server_type { ref($_[0]) }

sub pre_bind { # make sure we have good port parameters
    my $self = shift;
    my $prop = $self->{'server'};

    my $super = $self->net_server_type;
    my $type  = $self->_server_type;
    if ($self->isa('Net::Server::MultiType')) {
        my $base = delete($prop->{'_recursive_multitype'}) || Net::Server::MultiType->net_server_type;
        $super = "$super -> MultiType -> $base";
    }
    $type .= " (type $super)" if $type ne $super;
    $self->log(2, $self->log_time ." $type starting! pid($$)");

    $prop->{'sock'} = [grep {$_} map { $self->proto_object($_) } @{ $self->prepared_ports }];
    $self->fatal("No valid socket parameters found") if ! @{ $prop->{'sock'} };
}

sub prepared_ports {
    my $self = shift;
    my $prop = $self->{'server'};

    my ($ports, $hosts, $protos, $ipvs) = @$prop{qw(port host proto ipv)};
    $ports ||= $prop->{'ports'};
    if (!defined($ports) || (ref($ports) && !@$ports)) {
        $ports = $self->default_port;
        if (!defined($ports) || (ref($ports) && !@$ports)) {
            $ports = default_port();
            $self->log(2, "Port Not Defined.  Defaulting to '$ports'");
        }
    }

    my %bound;
    my $bind = $prop->{'_bind'} = [];
    for my $_port (ref($ports) ? @$ports : $ports) {
        my $_host  = ref($hosts)  ? $hosts->[ @$bind >= @$hosts  ? -1 : $#$bind + 1] : $hosts; # if ports are greater than hosts - augment with the last host
        my $_proto = ref($protos) ? $protos->[@$bind >= @$protos ? -1 : $#$bind + 1] : $protos;
        my $_ipv   = ref($ipvs)  ? $ipvs->[ @$bind >= @$ipvs  ? -1 : $#$bind + 1] : $ipvs;
        foreach my $info ($self->port_info($_port, $_host, $_proto, $_ipv)) {
            my ($port, $host, $proto, $ipv) = @$info{qw(port host proto ipv)}; # use cleaned values
            if ($port ne "0" && $bound{"$host\e$port\e$proto\e$ipv"}++) {
                $self->log(2, "Duplicate configuration (\U$proto\E) on [$host]:$port with IPv$ipv) - skipping");
                next;
            }
            push @$bind, $info;
        }
    }

    return $bind;
}

sub port_info {
    my ($self, $port, $host, $proto, $ipv) = @_;
    return Net::Server::Proto->parse_info($port, $host, $proto, $ipv, $self);
}

sub proto_object {
    my ($self, $info) = @_;
    return Net::Server::Proto->object($info, $self);
}

sub bind { # bind to the port (This should serve all but INET)
    my $self = shift;
    my $prop = $self->{'server'};

    if (exists $ENV{'BOUND_SOCKETS'}) {
        $self->restart_open_hook;
        $self->log(2, "Binding open file descriptors");
        my %map;
        foreach my $info (split /\s*;\s*/, $ENV{'BOUND_SOCKETS'}) {
            my ($fd, $host, $port, $proto, $ipv, $orig) = split /\|/, $info;
            $orig = $port if ! defined $orig; # allow for things like service ports or port 0
            $fd = ($fd =~ /^(\d+)$/) ? $1 : $self->fatal("Bad file descriptor");
            $map{"$host|$orig|$proto|$ipv"}->{$fd} = $port;
        }
        foreach my $sock (@{ $prop->{'sock'} }) {
            $sock->log_connect($self);
            if (my $ref = $map{$sock->hup_string}) {
                my ($fd, $port) = each %$ref;
                $sock->reconnect($fd, $self, $port);
                delete $ref->{$fd};
                delete $map{$sock->hup_string} if ! keys %$ref;
            } else {
                $self->log(2, "Added new port configuration");
                $sock->connect($self);
            }
        }
        foreach my $str (keys %map) {
            foreach my $fd (keys %{ $map{$str} }) {
                $self->log(2, "Closing un-mapped port ($str) on fd $fd");
                POSIX::close($fd);
            }
        }
        delete $ENV{'BOUND_SOCKETS'};
        $self->{'hup_waitpid'} = 1;

    } else { # connect to fresh ports
        foreach my $sock (@{ $prop->{'sock'} }) {
            $sock->log_connect($self);
            $sock->connect($self);
        }
    }

    if (@{ $prop->{'sock'} } > 1 || $prop->{'multi_port'}) {
        $prop->{'multi_port'} = 1;
        $prop->{'select'} = IO::Select->new; # if more than one socket we'll need to select on it
        $prop->{'select'}->add($_) for @{ $prop->{'sock'} };
    } else {
        $prop->{'multi_port'} = undef;
        $prop->{'select'}     = undef;
    }
}

sub post_bind_hook {}


sub post_bind { # secure the process and background it
    my $self = shift;
    my $prop = $self->{'server'};

    if (! defined $prop->{'group'}) {
        $self->log(1, "Group Not Defined.  Defaulting to EGID '$)'");
        $prop->{'group'} = $);
    } elsif ($prop->{'group'} =~ /^([\w-]+(?: [\w-]+)*)$/) {
        $prop->{'group'} = eval { get_gid($1) };
        $self->fatal(my $e = $@) if $@;
    } else {
        $self->fatal("Invalid group \"$prop->{'group'}\"");
    }

    if (! defined $prop->{'user'}) {
        $self->log(1, "User Not Defined.  Defaulting to EUID '$>'");
        $prop->{'user'} = $>;
    } elsif ($prop->{'user'} =~ /^([\w-]+)$/) {
        $prop->{'user'} = eval { get_uid($1) };
        $self->fatal(my $e = $@) if $@;
    } else {
        $self->fatal("Invalid user \"$prop->{'user'}\"");
    }

    # chown any files or sockets that we need to
    if ($prop->{'group'} ne $) || $prop->{'user'} ne $>) {
        my @chown_files;
        push @chown_files, map {$_->NS_port} grep {$_->NS_proto =~ /^UNIX/} @{ $prop->{'sock'} };
        push @chown_files, $prop->{'pid_file'}  if $prop->{'pid_file_unlink'};
        push @chown_files, $prop->{'lock_file'} if $prop->{'lock_file_unlink'};
        push @chown_files, @{ $prop->{'chown_files'} || [] };
        my $uid = $prop->{'user'};
        my $gid = (split /\ /, $prop->{'group'})[0];
        foreach my $file (@chown_files){
            chown($uid, $gid, $file) || $self->fatal("Couldn't chown \"$file\" [$!]");
        }
    }

    if ($prop->{'chroot'}) {
        $self->fatal("Specified chroot \"$prop->{'chroot'}\" doesn't exist.") if ! -d $prop->{'chroot'};
        $self->log(2, "Chrooting to $prop->{'chroot'}");
        chroot($prop->{'chroot'}) || $self->fatal("Couldn't chroot to \"$prop->{'chroot'}\": $!");
    }

    # drop privileges
    eval {
        if ($prop->{'group'} ne $)) {
            $self->log(2, "Setting gid to \"$prop->{'group'}\"");
            set_gid($prop->{'group'} );
        }
        if ($prop->{'user'} ne $>) {
            $self->log(2, "Setting uid to \"$prop->{'user'}\"");
            set_uid($prop->{'user'});
        }
    };
    if ($@) {
        if ($> == 0) {
            $self->fatal(my $e = $@);
        } elsif ($< == 0) {
            $self->log(2, "NOTICE: Effective UID changed, but Real UID is 0: $@");
        } else {
            $self->log(2, my $e = $@);
        }
    }

    $prop->{'requests'} = 0; # record number of request

    $SIG{'INT'}  = $SIG{'TERM'} = $SIG{'QUIT'} = sub { $self->server_close; };
    $SIG{'PIPE'} = 'IGNORE'; # most cases, a closed pipe will take care of itself
    $SIG{'CHLD'} = \&sig_chld; # catch children (mainly for Fork and PreFork but works for any chld)
    $SIG{'HUP'}  = sub { $self->sig_hup };
}

sub sig_chld {
    1 while waitpid(-1, POSIX::WNOHANG()) > 0;
    $SIG{'CHLD'} = \&sig_chld;
}

sub pre_loop_hook {}

sub loop {
    my $self = shift;
    while ($self->accept) {
        $self->run_client_connection;
        last if $self->done;
    }
}

sub accept {
    my $self = shift;
    my $prop = $self->{'server'};

    my $sock = undef;
    my $retries = 30;
    while ($retries--) {
        if ($prop->{'multi_port'}) { # with more than one port, use select to get the next one
            return 0 if $prop->{'_HUP'};
            $sock = $self->accept_multi_port || next; # keep trying for the rest of retries
            return 0 if $prop->{'_HUP'};
            if ($self->can_read_hook($sock)) {
                $retries++;
                next;
            }
        } else {
            $sock = $prop->{'sock'}->[0]; # single port is bound - just accept
        }
        $self->fatal("Received a bad sock!") if ! defined $sock;

        if (SOCK_DGRAM == $sock->getsockopt(Socket::SOL_SOCKET(), Socket::SO_TYPE())) { # receive a udp packet
            $prop->{'client'}   = $sock;
            $prop->{'udp_true'} = 1;
            $prop->{'udp_peer'} = $sock->recv($prop->{'udp_data'}, $sock->NS_recv_len, $sock->NS_recv_flags);

        } else { # blocking accept per proto
            delete $prop->{'udp_true'};
            $prop->{'client'} = $sock->accept();
        }

        return 0 if $prop->{'_HUP'};
        return 1 if $prop->{'client'};

        $self->log(2,"Accept failed with $retries tries left: $!");
        sleep(1);
    }

    $self->log(1,"Ran out of accept retries!");
    return undef;
}


sub accept_multi_port {
    my @waiting = shift->{'server'}->{'select'}->can_read();
    return undef if ! @waiting;
    return $waiting[rand @waiting];
}

sub can_read_hook {}

sub post_accept {
    my $self = shift;
    my $prop = $self->{'server'};
    my $client = shift || $prop->{'client'};

    $prop->{'requests'}++;
    return if $prop->{'udp_true'}; # no need to do STDIN/STDOUT in UDP

    if (!$client) {
        $self->log(1,"Client socket information could not be determined!");
        return;
    }

    $client->post_accept() if $client->can("post_accept");
    if (! $prop->{'no_client_stdout'}) {
        close STDIN; # duplicate some handles and flush them
        close STDOUT;
        if ($prop->{'tie_client_stdout'} || ($client->can('tie_stdout') && $client->tie_stdout)) {
            open STDIN,  '<', '/dev/null' or die "Couldn't open STDIN to the client socket: $!";
            open STDOUT, '>', '/dev/null' or die "Couldn't open STDOUT to the client socket: $!";
            tie *STDOUT, 'Net::Server::TiedHandle', $client, $prop->{'tied_stdout_callback'} or die "Couldn't tie STDOUT: $!";
            tie *STDIN,  'Net::Server::TiedHandle', $client, $prop->{'tied_stdin_callback'}  or die "Couldn't tie STDIN: $!";
        } elsif (defined(my $fileno = fileno $prop->{'client'})) {
            open STDIN,  '<&', $fileno or die "Couldn't open STDIN to the client socket: $!";
            open STDOUT, '>&', $fileno or die "Couldn't open STDOUT to the client socket: $!";
        } else {
            *STDIN  = \*{ $client };
            *STDOUT = \*{ $client };
        }
        STDIN->autoflush(1);
        STDOUT->autoflush(1);
        select STDOUT;
    }
}

sub get_client_info {
    my $self = shift;
    my $prop = $self->{'server'};
    my $client = shift || $prop->{'client'};

    if ($client->NS_proto =~ /^UNIX/) {
        delete @$prop{qw(sockaddr sockport peeraddr peerport peerhost)};
        $self->log(3, $self->log_time." CONNECT ".$client->NS_proto." Socket: \"".$client->NS_port."\"") if $prop->{'log_level'} && 3 <= $prop->{'log_level'};
        return;
    }

    if (my $sockname = $client->sockname) {
        $prop->{'sockaddr'} = $client->sockhost;
        $prop->{'sockport'} = $client->sockport;
    } else {
        @{ $prop }{qw(sockaddr sockhost sockport)} = ($ENV{'REMOTE_HOST'} || '0.0.0.0', 'inet.test', 0); # commandline
    }

    my $addr;
    if ($prop->{'udp_true'}) {
        if ($client->sockdomain == AF_INET) {
            ($prop->{'peerport'}, $addr) = Socket::sockaddr_in($prop->{'udp_peer'});
            $prop->{'peeraddr'} = Socket::inet_ntoa($addr);
        } else {
            warn "Right here\n";
            ($prop->{'peerport'}, $addr) = Socket6::sockaddr_in6($prop->{'udp_peer'});
            $prop->{'peeraddr'} = Socket6->can('inet_ntop')
                                ? Socket6::inet_ntop($client->sockdomain, $addr)
                                : Socket::inet_ntoa($addr);
        }
    } elsif ($prop->{'peername'} = $client->peername) {
        $addr               = $client->peeraddr;
        $prop->{'peeraddr'} = $client->peerhost;
        $prop->{'peerport'} = $client->peerport;
    } else {
        @{ $prop }{qw(peeraddr peerhost peerport)} = ('0.0.0.0', 'inet.test', 0); # commandline
    }

    if ($addr && defined $prop->{'reverse_lookups'}) {
        if ($INC{'Socket6.pm'} && Socket6->can('getnameinfo')) {
            my @res = Socket6::getnameinfo($addr, 0);
            $prop->{'peerhost'} = $res[0] if @res > 1;
        }else{
            $prop->{'peerhost'} = gethostbyaddr($addr, AF_INET);
        }
    }

    $self->log(3, $self->log_time
               ." CONNECT ".$client->NS_proto
               ." Peer: \"[$prop->{'peeraddr'}]:$prop->{'peerport'}\""
               ." Local: \"[$prop->{'sockaddr'}]:$prop->{'sockport'}\"") if $prop->{'log_level'} && 3 <= $prop->{'log_level'};
}

sub post_accept_hook {}

sub allow_deny {
    my $self = shift;
    my $prop = $self->{'server'};
    my $sock = shift || $prop->{'client'};

    # unix sockets are immune to this check
    return 1 if $sock && $sock->NS_proto =~ /^UNIX/;

    # if no allow or deny parameters are set, allow all
    return 1 if ! @{ $prop->{'allow'} }
             && ! @{ $prop->{'deny'} }
             && ! @{ $prop->{'cidr_allow'} }
             && ! @{ $prop->{'cidr_deny'} };

    # work around Net::CIDR::cidrlookup() croaking,
    # if first parameter is an IPv4 address in IPv6 notation.
    my $peeraddr = ($prop->{'peeraddr'} =~ /^\s*::ffff:([0-9.]+\s*)$/) ? $1 : $prop->{'peeraddr'};

    # if the addr or host matches a deny, reject it immediately
    foreach (@{ $prop->{'deny'} }) {
        return 0 if $prop->{'reverse_lookups'}
            && defined($prop->{'peerhost'}) && $prop->{'peerhost'} =~ /^$_$/;
        return 0 if $peeraddr =~ /^$_$/;
    }
    if (@{ $prop->{'cidr_deny'} }) {
        require Net::CIDR;
        return 0 if Net::CIDR::cidrlookup($peeraddr, @{ $prop->{'cidr_deny'} });
    }

    # if the addr or host isn't blocked yet, allow it if it is allowed
    foreach (@{ $prop->{'allow'} }) {
        return 1 if $prop->{'reverse_lookups'}
            && defined($prop->{'peerhost'}) && $prop->{'peerhost'} =~ /^$_$/;
        return 1 if $peeraddr =~ /^$_$/;
    }
    if (@{ $prop->{'cidr_allow'} }) {
        require Net::CIDR;
        return 1 if Net::CIDR::cidrlookup($peeraddr, @{ $prop->{'cidr_allow'} });
    }

    return 0;
}

sub allow_deny_hook { 1 } # false to deny request

sub request_denied_hook {}

sub process_request { # sample echo server - override for full functionality
    my $self = shift;
    my $prop = $self->{'server'};

    if ($prop->{'udp_true'}) { # udp echo server
        my $client = shift || $prop->{'client'};
        if ($prop->{'udp_data'} =~ /dump/) {
            require Data::Dumper;
            return $client->send(Data::Dumper::Dumper($self), 0);
        }
        return $client->send("You said \"$prop->{'udp_data'}\"", 0);
    }

    print "Welcome to \"".ref($self)."\" ($$)\015\012";
    my $previous_alarm = alarm 30;
    eval {
        local $SIG{'ALRM'} = sub { die "Timed Out!\n" };
        while (<STDIN>) {
            s/[\r\n]+$//;
            print ref($self),":$$: You said \"$_\"\015\012";
            $self->log(5, $_); # very verbose log
            if (/get\s+(\w+)/) { print "$1: $self->{'server'}->{$1}\015\012" }
            elsif (/dump/) { require Data::Dumper; print Data::Dumper::Dumper($self) }
            elsif (/quit/) { last }
            elsif (/exit/) { $self->server_close }
            alarm 30; # another 30
        }
        alarm($previous_alarm);
    };
    alarm 0;
    print "Timed Out.\015\012" if $@ eq "Timed Out!\n";
}

sub post_process_request_hook {}

sub post_client_connection_hook {}

sub post_process_request {
    my $self = shift;
    $self->close_client_stdout;
}

sub close_client_stdout {
    my $self = shift;
    my $prop = $self->{'server'};
    return if $prop->{'udp_true'};

    if (! $prop->{'no_client_stdout'}) {
        my $t = tied *STDOUT; if ($t) { undef $t; untie *STDOUT };
        $t    = tied *STDIN;  if ($t) { undef $t; untie *STDIN  };
        open(STDIN,  '<', '/dev/null') || die "Cannot read /dev/null  [$!]";
        open(STDOUT, '>', '/dev/null') || die "Cannot write /dev/null [$!]";
    }
    $prop->{'client'}->close;
}

sub done {
    my $self = shift;
    $self->{'server'}->{'done'} = shift if @_;
    return $self->{'server'}->{'done'};
}

sub pre_fork_hook {}
sub child_init_hook {}
sub child_finish_hook {}

sub run_dequeue { # fork off a child process to handle dequeuing
    my $self = shift;
    $self->pre_fork_hook('dequeue');
    my $pid  = fork;
    $self->fatal("Bad fork [$!]") if ! defined $pid;
    if (!$pid) { # child
        $SIG{'INT'} = $SIG{'TERM'} = $SIG{'QUIT'} = $SIG{'HUP'} = sub {
            $self->child_finish_hook('dequeue');
            exit;
        };
        $SIG{'PIPE'} = $SIG{'TTIN'} = $SIG{'TTOU'} = 'DEFAULT';
        $self->child_init_hook('dequeue');
        $self->dequeue();
        $self->child_finish_hook('dequeue');
        exit;
    }
    $self->log(4, "Running dequeue child $pid");

    $self->{'server'}->{'children'}->{$pid}->{'status'} = 'dequeue'
        if $self->{'server'}->{'children'};
}

sub default_port { 20203 }

sub dequeue {}

sub pre_server_close_hook {}

sub server_close {
    my ($self, $exit_val) = @_;
    my $prop = $self->{'server'};

    $SIG{'INT'} = 'DEFAULT';

    ### if this is a child process, signal the parent and close
    ### normally the child shouldn't, but if they do...
    ### otherwise the parent continues with the shutdown
    ### this is safe for non standard forked child processes
    ### as they will not have server_close as a handler
    if (defined($prop->{'ppid'})
        && $prop->{'ppid'} != $$
        && ! defined($prop->{'no_close_by_child'})) {
        $self->close_parent;
        exit;
    }

    $self->pre_server_close_hook;

    $self->log(2, $self->log_time . " Server closing!");

    if ($prop->{'kind_quit'} && $prop->{'children'}) {
        $self->log(3, "Attempting a slow shutdown");
        $prop->{$_} = 0 for qw(min_servers max_servers);
        $self->hup_children; # send children signal to finish up
        while (1) {
            Net::Server::SIG::check_sigs();
            $self->coordinate_children if $self->can('coordinate_children');
            last if !keys %{$self->{'server'}->{'children'}};
            sleep 1;
        }
    }

    if ($prop->{'_HUP'} && $prop->{'leave_children_open_on_hup'}) {
        $self->hup_children;

    } else {
        $self->close_children() if $prop->{'children'};
        $self->post_child_cleanup_hook;
    }

    if (defined($prop->{'lock_file'})
        && -e $prop->{'lock_file'}
        && defined($prop->{'lock_file_unlink'})) {
        unlink($prop->{'lock_file'}) || $self->log(1, "Couldn't unlink \"$prop->{'lock_file'}\" [$!]");
    }
    if (defined($prop->{'pid_file'})
        && -e $prop->{'pid_file'}
        && !$prop->{'_HUP'}
        && defined($prop->{'pid_file_unlink'})) {
        unlink($prop->{'pid_file'}) || $self->log(1, "Couldn't unlink \"$prop->{'pid_file'}\" [$!]");
    }

    if ($prop->{'_HUP'}) {
        $self->restart_close_hook();
        $self->hup_server; # execs at the end
    }

    $self->shutdown_sockets;
    return $self if $prop->{'no_exit_on_close'};
    $self->server_exit($exit_val);
}

sub server_exit {
    my ($self, $exit_val) = @_;
    exit($exit_val || 0);
}

sub shutdown_sockets {
    my $self = shift;
    my $prop = $self->{'server'};

    foreach my $sock (@{ $prop->{'sock'} }) { # unlink remaining socket files (if any)
        $sock->shutdown(2);
        unlink $sock->NS_port if $sock->NS_proto =~ /^UNIX/;
    }

    $prop->{'sock'} = []; # delete the sock objects
    return 1;
}

### Allow children to send INT signal to parent (or use another method)
### This method is only used by forking servers
sub close_parent {
    my $self = shift;
    my $prop = $self->{'server'};
    die "Missing parent pid (ppid)" if ! $prop->{'ppid'};
    kill 2, $prop->{'ppid'};
}

### SIG INT the children
### This method is only used by forking servers (ie Fork, PreFork)
sub close_children {
    my $self = shift;
    my $prop = $self->{'server'};
    return unless $prop->{'children'} && scalar keys %{ $prop->{'children'} };

    foreach my $pid (keys %{ $prop->{'children'} }) {
        $self->log(4, "Kill TERM pid $pid");
        if (kill(15, $pid) || ! kill(0, $pid)) { # if it is killable, kill it
            $self->delete_child($pid);
        }
    }

    1 while waitpid(-1, POSIX::WNOHANG()) > 0;
}


sub is_prefork { 0 }

sub hup_children {
    my $self = shift;
    my $prop = $self->{'server'};
    return unless defined $prop->{'children'} && scalar keys %{ $prop->{'children'} };
    return if ! $self->is_prefork;
    $self->log(2, "Sending children hup signal");

    for my $pid (keys %{ $prop->{'children'} }) {
        $self->log(4, "Kill HUP pid $pid");
        kill(1, $pid) or $self->log(2, "Failed to kill pid $pid: $!");
    }
}

sub post_child_cleanup_hook {}

### handle sig hup
### this will prepare the server for a restart via exec
sub sig_hup {
    my $self = shift;
    my $prop = $self->{'server'};

    $self->log(2, "Received a SIG HUP");

    my $i  = 0;
    my @fd;
    $prop->{'_HUP'} = [];
    foreach my $sock (@{ $prop->{'sock'} }) {
        my $fd = POSIX::dup($sock->fileno) || $self->fatal("Cannot duplicate the socket [$!]");

        # hold on to the socket copy until exec;
        # just temporary: any socket domain will do,
        # forked process will decide to use IO::Socket::INET6 if necessary
        $prop->{'_HUP'}->[$i] = IO::Socket::INET->new;
        $prop->{'_HUP'}->[$i]->fdopen($fd, 'w') || $self->fatal("Cannot open to file descriptor [$!]");

        # turn off the FD_CLOEXEC bit to allow reuse on exec
        require Fcntl;
        $prop->{'_HUP'}->[$i]->fcntl(Fcntl::F_SETFD(), my $flags = "");

        push @fd, $fd .'|'. $sock->hup_string; # save file-descriptor and host|port|proto|ipv

        $sock->close();
        $i++;
    }
    delete $prop->{'select'}; # remove any blocking obstacle
    $ENV{'BOUND_SOCKETS'} = join "; ", @fd;

    if ($prop->{'leave_children_open_on_hup'} && scalar keys %{ $prop->{'children'} }) {
        $ENV{'HUP_CHILDREN'} = join "\n", map {"$_\t$prop->{'children'}->{$_}->{'status'}"} sort keys %{ $prop->{'children'} };
    }
}


sub hup_server {
    my $self = shift;
    $self->log(0, $self->log_time()." Re-exec server during HUP");
    delete @ENV{$self->hup_delete_env_keys};
    exec @{ $self->commandline };
}

sub hup_delete_env_keys { return qw(PATH) }

sub restart_open_hook {} # this hook occurs if a server has been HUP'ed it occurs just before opening to the fileno's

sub restart_close_hook {} # this hook occurs if a server has been HUP'ed it occurs just before exec'ing the server

###----------------------------------------------------------###

sub fatal {
    my ($self, $error) = @_;
    my ($package, $file, $line) = caller;
    $self->fatal_hook($error, $package, $file, $line);
    $self->log(0, $self->log_time ." $error\n  at line $line in file $file");
    $self->server_close(1);
}

sub fatal_hook {}

###----------------------------------------------------------###

sub log {
    my ($self, $level, $msg, @therest) = @_;
    my $prop = $self->{'server'};
    return if ! $prop->{'log_level'};
    return if $level =~ /^\d+$/ && $level > $prop->{'log_level'};
    $msg = sprintf($msg, @therest) if @therest; # if multiple arguments are passed, assume that the first is a format string

    if ($prop->{'log_function'}) {
        return if eval { $prop->{'log_function'}->($level, $msg); 1 };
        my $err = $@;
        if ($prop->{'log_class'} && $prop->{'log_class'}->can('handle_error')) {
            $prop->{'log_class'}->handle_log_error($self, $err, [$level, $msg]);
        } else {
            $self->handle_log_error($err, [$level, $msg]);
        }
    }

    return if $level !~ /^\d+$/;
    $self->write_to_log_hook($level, $msg);
}


sub handle_log_error { my ($self, $error) = @_; die $error }
sub handle_syslog_error { &handle_log_error }

sub write_to_log_hook {
    my ($self, $level, $msg) = @_;
    my $prop = $self->{'server'};
    chomp $msg;
    $msg =~ s/([^\n\ -\~])/sprintf("%%%02X",ord($1))/eg;

    if ($prop->{'log_file'}) {
        print _SERVER_LOG $msg, "\n";
    } elsif ($prop->{'setsid'}) {
        # do nothing ?
    } else {
        my $old = select STDERR;
        print $msg. "\n";
        select $old;
    }
}


sub log_time {
    my ($sec,$min,$hour,$day,$mon,$year) = localtime;
    return sprintf "%04d/%02d/%02d-%02d:%02d:%02d", $year + 1900, $mon + 1, $day, $hour, $min, $sec;
}

###----------------------------------------------------------###

sub options {
    my $self = shift;
    my $ref  = shift || {};
    my $prop = $self->{'server'};

    foreach (qw(port host proto ipv allow deny cidr_allow cidr_deny)) {
        if (! defined $prop->{$_}) {
            $prop->{$_} = [];
        } elsif (! ref $prop->{$_}) {
            $prop->{$_} = [$prop->{$_}]; # nicely turn us into an arrayref if we aren't one already
        }
        $ref->{$_} = $prop->{$_};
    }

    foreach (qw(conf_file
                user group chroot log_level
                log_file pid_file background setsid
                listen reverse_lookups
                no_close_by_child
                no_client_stdout tie_client_stdout tied_stdout_callback tied_stdin_callback
                leave_children_open_on_hup
                )) {
        $ref->{$_} = \$prop->{$_};
    }
    return $ref;
}


### routine for parsing commandline, module, and conf file
### method has the benefit of leaving unused arguments in @ARGV
sub process_args {
    my ($self, $args, $template) = @_;
    $self->options($template = {}) if ! $template || ! ref $template;
    if (!$_[2] && !scalar(keys %$template) && !$self->{'server'}->{'_no_options'}++) {
        warn "Configuration options were empty - skipping any commandline, config file, or run argument parsing.\n";
    }

    # we want subsequent calls to not overwrite or add to previously set values so that command line arguments win
    my %previously_set;
    foreach (my $i = 0; $i < @$args; $i++) {
        if ($args->[$i] =~ /^(?:--)?(\w+)(?:[=\ ](\S+))?$/
            && exists $template->{$1}) {
            my ($key, $val) = ($1, $2);
            splice @$args, $i, 1;
            if (! defined $val) {
                if ($i > $#$args
                    || ($args->[$i] && $args->[$i] =~ /^--\w+/)) {
                    $val = 1; # allow for options such as --setsid
                } else {
                    $val = splice @$args, $i, 1;
                    $val = $val->[0] if ref($val) eq 'ARRAY' && @$val == 1 && ref($template->{$key}) ne 'ARRAY';
                }
            }
            $i--;
            $val =~ s/%([A-F0-9])/chr(hex $1)/eig if ! ref $val;

            if (ref $template->{$key} eq 'ARRAY') {
                if (! defined $previously_set{$key}) {
                    $previously_set{$key} = scalar @{ $template->{$key} };
                }
                next if $previously_set{$key};
                push @{ $template->{$key} }, ref($val) eq 'ARRAY' ? @$val : $val;
            } else {
                if (! defined $previously_set{$key}) {
                    $previously_set{$key} = defined(${ $template->{$key} }) ? 1 : 0;
                }
                next if $previously_set{$key};
                die "Found multiple values on the configuration item \"$key\" which expects only one value" if ref($val) eq 'ARRAY';
                ${ $template->{$key} } = $val;
            }
        }
    }
}

sub _read_conf {
    my ($self, $file) = @_;
    my @args;
    $file = ($file =~ m|^([\w\.\-\/\\\:]+)$|) ? $1 : $self->fatal("Unsecure filename \"$file\"");
    open my $fh, '<', $file or do {
        $self->fatal("Couldn't open conf \"$file\" [$!]") if $ENV{'BOUND_SOCKETS'};
        warn "Couldn't open conf \"$file\" [$!]\n";
    };
    while (defined(my $line = <$fh>)) {
        push @args, $1, $2 if $line =~ m/^\s* ((?:--)?\w+) (?:\s*[=:]\s*|\s+) (\S+)/x;
    }
    close $fh;
    return \@args;
}

###----------------------------------------------------------------###

sub other_child_died_hook {}

sub delete_child {
    my ($self, $pid) = @_;
    my $prop = $self->{'server'};

    return $self->other_child_died_hook($pid) if ! exists $prop->{'children'}->{$pid};

    # prefork server check to clear child communication
    if ($prop->{'child_communication'}) {
        if ($prop->{'children'}->{$pid}->{'sock'}) {
            $prop->{'child_select'}->remove($prop->{'children'}->{$pid}->{'sock'});
            $prop->{'children'}->{$pid}->{'sock'}->close;
        }
    }

    delete $prop->{'children'}->{$pid};
}

# send signal to all children - used by forking servers
sub sig_pass {
    my ($self, $sig) = @_;
    foreach my $chld (keys %{ $self->{'server'}->{'children'} }) {
        $self->log(4, "signaling $chld with $sig" );
        kill($sig, $chld) || $self->log(1, "child $chld not signaled with $sig");
    }
}

# register sigs to allow passthrough to children
sub register_sig_pass {
    my $self = shift;
    my $ref  = $self->{'server'}->{'sig_passthrough'} || [];
    $ref = [$ref] if ! ref $ref;
    $self->fatal('invalid sig_passthrough') if ref $ref ne 'ARRAY';
    return if ! @$ref;
    $self->log(4, "sig_passthrough option found");
    require Net::Server::SIG;
    foreach my $sig (map {split /\s*,\s*/, $_} @$ref) {
        my $code = Net::Server::SIG::sig_is_registered($sig);
        if ($code) {
            $self->log(2, "Installing passthrough for $sig even though it is already registered.");
        } else {
            $code = ref($SIG{$sig}) eq 'CODE' ? $SIG{$sig} : undef;
        }
        Net::Server::SIG::register_sig($sig => sub { $self->sig_pass($sig); $code->($sig) if $code; });
        $self->log(2, "Installed passthrough for $sig");
    }
}

###----------------------------------------------------------------###

package Net::Server::TiedHandle;
sub TIEHANDLE { my $pkg = shift; return bless [@_], $pkg }
sub READLINE { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'getline',  @_) : $s->[0]->getline }
sub SAY      { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'say',      @_) : $s->[0]->say(@_) }
sub PRINT    { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'print',    @_) : $s->[0]->print(@_) }
sub PRINTF   { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'printf',   @_) : $s->[0]->printf(@_) }
sub READ     { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'read',     @_) : $s->[0]->read(@_) }
sub WRITE    { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'write',    @_) : $s->[0]->write(@_) }
sub SYSREAD  { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'sysread',  @_) : $s->[0]->sysread(@_) }
sub SYSWRITE { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'syswrite', @_) : $s->[0]->syswrite(@_) }
sub SEEK     { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'seek',     @_) : $s->[0]->seek(@_) }
sub BINMODE  {}
sub FILENO   {}
sub CLOSE    { my $s = shift; $s->[1] ? $s->[1]->($s->[0], 'close',    @_) : $s->[0]->close(@_) }


1;

### The documentation is in Net/Server.pod
