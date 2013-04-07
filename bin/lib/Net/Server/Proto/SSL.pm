# -*- perl -*-
#
#  Net::Server::Proto::SSL - Net::Server Protocol module
#
#  $Id: SSL.pm,v 1.25 2013/01/10 05:43:14 rhandom Exp $
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

package Net::Server::Proto::SSL;

use strict;
use warnings;

BEGIN {
    # IO::Socket::SSL will automatically become IO::Socket::INET6 if it is available.
    # This is different from Net::Server::Proto::SSLEAY that only does it if IPv6 is requested.
    if (! eval { require IO::Socket::SSL }) {
        die "Module IO::Socket::SSL is required for SSL - you may alternately try SSLEAY. $@";
    }
}

our @ISA = qw(IO::Socket::SSL);
our $AUTOLOAD;

my @ssl_args = qw(
    SSL_use_cert
    SSL_verify_mode
    SSL_key_file
    SSL_cert_file
    SSL_ca_path
    SSL_ca_file
    SSL_cipher_list
    SSL_passwd_cb
    SSL_max_getline_length
    SSL_error_callback
);

sub NS_proto { 'SSL' }
sub NS_port   { my $sock = shift; ${*$sock}{'NS_port'}   = shift if @_; return ${*$sock}{'NS_port'}   }
sub NS_host   { my $sock = shift; ${*$sock}{'NS_host'}   = shift if @_; return ${*$sock}{'NS_host'}   }
sub NS_ipv    { my $sock = shift; ${*$sock}{'NS_ipv'}    = shift if @_; return ${*$sock}{'NS_ipv'}    }
sub NS_listen { my $sock = shift; ${*$sock}{'NS_listen'} = shift if @_; return ${*$sock}{'NS_listen'} }

sub object {
    my ($class, $info, $server) = @_;

    my $ssl = $server->{'server'}->{'ssl_args'} ||= do {
        my %temp = map {$_ => undef} @ssl_args;
        $server->configure({map {$_ => \$temp{$_}} @ssl_args});
        \%temp;
    };

    my @sock = $class->SUPER::new();
    foreach my $sock (@sock) {
        $sock->NS_host($info->{'host'});
        $sock->NS_port($info->{'port'});
        $sock->NS_ipv( $info->{'ipv'} );
        $sock->NS_listen(defined($info->{'listen'}) ? $info->{'listen'}
                        : defined($server->{'server'}->{'listen'}) ? $server->{'server'}->{'listen'}
                        : Socket::SOMAXCONN());
        ${*$sock}{'NS_orig_port'} = $info->{'orig_port'} if defined $info->{'orig_port'};

        my %seen;
        for my $key (grep {!$seen{$_}++} (@ssl_args, sort grep {/^SSL_/} keys %$info)) { # allow for any SSL_ arg to get passed in via 
            my $val = defined($info->{$key}) ? $info->{$key}
                    : defined($ssl->{$key})  ? $ssl->{$key}
                    : $server->can($key) ? $server->$key($info->{'host'}, $info->{'port'}, 'SSL')
                    : undef;
            next if ! defined $val;
            $sock->$key($val) if defined $val;
        }
    }
    return wantarray ? @sock : $sock[0];
}

sub log_connect {
    my ($sock, $server) = @_;
    $server->log(2, "Binding to ".$sock->NS_proto." port ".$sock->NS_port." on host ".$sock->NS_host." with IPv".($sock->NS_ipv));
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
        ($sock->isa('IO::Socket::INET6') ? (Domain => ($ipv eq '6') ? Socket6::AF_INET6() : ($ipv eq '4') ? Socket::AF_INET() : Socket::AF_UNSPEC()) : ()),
        (map {$_ => $sock->$_();} grep {/^SSL_/} keys %{*$sock}),
        SSL_server => 1,
    }) or $server->fatal("Cannot connect to SSL port $port on $host [$!]");

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

    $sock->configure_SSL({
        (map {$_ => $sock->$_();} grep {/^SSL_/} keys %{*$sock}),
        SSL_server => 1,
    });
    $sock->IO::Socket::INET::fdopen($fd, 'w') or $server->fatal("Error opening to file descriptor ($fd) [$!]");

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
    my ($sock, $class) = @_;
    my ($client, $peername);
    my $code = $sock->isa('IO::Socket::INET6') ? 'IO::Socket::INET6'->can('accept') : 'IO::Socket::INET'->can('accept'); # TODO - cache this lookup
    if (wantarray) {
        ($client, $peername) = $code->($sock, $class || ref($sock));
    } else {
        $client = $code->($sock, $class || ref($sock));
    }
    ${*$client}{'_parent_sock'} = $sock;

    if (defined $client) {
        $client->NS_proto($sock->NS_proto);
        $client->NS_ipv(  $sock->NS_ipv);
        $client->NS_host( $sock->NS_host);
        $client->NS_port( $sock->NS_port);
    }

    return wantarray ? ($client, $peername) : $client;
}

sub hup_string {
    my $sock = shift;
    return join "|", $sock->NS_host, $sock->NS_port, $sock->NS_proto, 'ipv'.$sock->NS_ipv, (defined(${*$sock}{'NS_orig_port'}) ? ${*$sock}{'NS_orig_port'} : ());
}

sub show {
    my $sock = shift;
    my $t = "Ref = \"".ref($sock). "\" (".$sock->hup_string.")\n";
    foreach my $prop (qw(SSLeay_context SSLeay_is_client)) {
        $t .= "  $prop = \"" .$sock->$prop()."\"\n";
    }
    return $t;
}

sub AUTOLOAD {
    my $sock = shift;
    my $prop = $AUTOLOAD =~ /::([^:]+)$/ ? $1 : die "Missing property in AUTOLOAD.";
    die "Unknown method or property [$prop]" if $prop !~ /^(SSL_\w+)$/;

    no strict 'refs';
    *{__PACKAGE__."::${prop}"} = sub {
        my $sock = shift;
        if (@_) {
            ${*$sock}{$prop} = shift;
            return delete ${*$sock}{$prop} if ! defined ${*$sock}{$prop};
        } else {
            return ${*$sock}{$prop};
        }
    };
    return $sock->$prop(@_);
}

sub tie_stdout { 1 }

sub post_accept {
    my $client = shift;
    $client->_accept_ssl if !${*$client}{'_accept_ssl'};
}

sub _accept_ssl {
    my $client = shift;
    ${*$client}{'_accept_ssl'} = 1;
    my $sock = delete(${*$client}{'_parent_sock'}) || die "Could not get handshake from accept\n";
    $sock->accept_SSL($client) || die "Could not finalize SSL connection with client handle ($@)\n";
}

sub read_until { # allow for an interface that can be tied to STDOUT
    my ($client, $bytes, $end_qr) = @_;
    die "One of bytes or end_qr should be defined for TCP read_until\n" if !defined($bytes) && !defined($end_qr);

    $client->_accept_ssl if !${*$client}{'_accept_ssl'};

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

1;

=head1 NAME

Net::Server::Proto::SSL - Net::Server SSL protocol.

=head1 SYNOPSIS

Until this release, it was preferrable to use the Net::Server::Proto::SSLEAY
module.  Recent versions include code that overcomes original limitations.

See L<Net::Server::Proto>.
See L<Net::Server::Proto::SSLEAY>.

    use base qw(Net::Server::HTTP);
    main->run(
        proto => 'ssl',
        SSL_key_file  => "/path/to/my/file.key",
        SSL_cert_file => "/path/to/my/file.crt",
    );


    # OR

    sub SSL_key_file  { "/path/to/my/file.key" }
    sub SSL_cert_file { "/path/to/my/file.crt" }
    main->run(proto = 'ssl');


    # OR

    main->run(
        port => [443, 8443, "80/tcp"],  # bind to two ssl ports and one tcp
        proto => "ssl",       # use ssl as the default
        ipv  => "*",          # bind both IPv4 and IPv6 interfaces
        SSL_key_file  => "/path/to/my/file.key",
        SSL_cert_file => "/path/to/my/file.crt",
    );


    # OR

    main->run(port => [{
        port  => "443",
        proto => "ssl",
        # ipv => 4, # default - only do IPv4
        SSL_key_file  => "/path/to/my/file.key",
        SSL_cert_file => "/path/to/my/file.crt",
    }, {
        port  => "8443",
        proto => "ssl",
        ipv   => "*", # IPv4 and IPv6
        SSL_key_file  => "/path/to/my/file2.key", # separate key
        SSL_cert_file => "/path/to/my/file2.crt", # separate cert

        SSL_foo => 1, # Any key prefixed with SSL_ passed as a port hashref
                      # key/value will automatically be passed to IO::Socket::SSL
    }]);


=head1 DESCRIPTION

Protocol module for Net::Server based on IO::Socket::SSL.  This module
implements a secure socket layer over tcp (also known as SSL) via the
IO::Socket::SSL module.  If this module does not work in your
situation, please also consider using the SSLEAY protocol
(Net::Server::Proto::SSLEAY) which interfaces directly with
Net::SSLeay.  See L<Net::Server::Proto>.

If you know that your server will only need IPv4 (which is the default
for Net::Server), you can load IO::Socket::SSL in inet4 mode which
will prevent it from using Socket6 and IO::Socket::INET6 since they
would represent additional and unsued overhead.

    use IO::Socket::SSL qw(inet4);
    use base qw(Net::Server::Fork);

    __PACKAGE__->run(proto => "ssl");

=head1 PARAMETERS

In addition to the normal Net::Server parameters, any of the SSL
parameters from IO::Socket::SSL may also be specified.  See
L<IO::Socket::SSL> for information on setting this up.  All arguments
prefixed with SSL_ will be passed to the IO::Socket::SSL->configure
method.

=head1 BUGS

Until version Net::Server version 2, Net::Server::Proto::SSL used the
default IO::Socket::SSL::accept method.  This old approach introduces a
DDOS vulnerability into the server, where the socket is accepted, but
the parent server then has to block until the client negotiates the
SSL connection.  This has now been overcome by overriding the accept
method and accepting the SSL negotiation after the parent socket has
had the chance to go back to listening.

=head1 LICENCE

Distributed under the same terms as Net::Server

=head1 THANKS

Thanks to Vadim for pointing out the IO::Socket::SSL accept
was returning objects blessed into the wrong class.

=cut
