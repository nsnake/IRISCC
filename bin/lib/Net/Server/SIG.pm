# -*- perl -*-
#
#  Net::Server::SIG - Safer signals
#
#  $Id: SIG.pm,v 1.8 2013/01/10 06:11:41 rhandom Exp $
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

package Net::Server::SIG;

use strict;
use vars qw($VERSION @ISA @EXPORT_OK
            %_SIG %_SIG_SUB);
use Exporter ();

$VERSION   = '0.03';
@ISA       = qw(Exporter);
@EXPORT_OK = qw(register_sig unregister_sig check_sigs);

sub register_sig {
    die 'Usage: register_sig( SIGNAME => \&code_ref )' if @_ % 2;
    if (@_ > 2) {
        register_sig(shift(),shift()) while @_;
        return;
    }
    my $sig      = shift;
    my $code_ref = shift;
    my $ref = ref($code_ref);

    if (! $ref) {
        if ($code_ref eq 'DEFAULT') {
            delete $_SIG{$sig};
            delete $_SIG_SUB{$sig};
            $SIG{$sig} = 'DEFAULT';
        } elsif ($code_ref eq 'IGNORE') {
            delete $_SIG{$sig};
            delete $_SIG_SUB{$sig};
            $SIG{$sig} = 'IGNORE';
        } else {
            die "Scalar argument limited to \"DEFAULT\" and \"IGNORE\".";
        }
    } elsif ($ref eq 'CODE') {
        $_SIG{$sig} = 0;
        $_SIG_SUB{$sig} = $code_ref;
        $SIG{$sig} = sub{ $Net::Server::SIG::_SIG{$_[0]} = 1; };
    } else {
        die "Unsupported sig type -- must be 'DEFAULT' or a code ref.";
    }
}

sub unregister_sig { register_sig(shift(), 'DEFAULT') }

sub check_sigs {
    my @found;
    foreach my $sig (keys %_SIG){
        next if ! $_SIG{$sig};
        $_SIG{$sig} = 0;
        push @found, $sig;
        $_SIG_SUB{$sig}->($sig);
    }
    return @found;
}

sub sig_is_registered {
    my $sig = shift;
    return $_SIG_SUB{$sig};
}

1;

=head1 NAME

Net::Server::SIG - adpf - Safer signal handling

=head1 SYNOPSIS

    use Net::Server::SIG qw(register_sig check_sigs);
    use IO::Select ();
    use POSIX qw(WNOHANG);

    my $select = IO::Select->new();

    register_sig(PIPE => 'IGNORE',
                 HUP  => 'DEFAULT',
                 USR1 => sub { print "I got a SIG $_[0]\n"; },
                 USR2 => sub { print "I got a SIG $_[0]\n"; },
                 CHLD => sub { 1 while waitpid(-1, WNOHANG) > 0; },
                 );

    # add some handles to the select
    $select->add(\*STDIN);

    # loop forever trying to stay alive
    while (1) {

        # do a timeout to see if any signals got passed us
        # while we were processing another signal
        my @fh = $select->can_read(10);

        my $key;
        my $val;

        # this is the handler for safe (fine under unsafe also)
        if (check_sigs()) {
          # or my @sigs = check_sigs();
          next unless @fh;
        }

        my $handle = $fh[@fh];

        # do something with the handle

    }

=head1 DESCRIPTION

Signals prior in Perl prior to 5.7 were unsafe.  Since then signals
have been implemented in a more safe algorithm.  Net::Server::SIG
provides backwards compatibility, while still working reliably with
newer releases.

Using a property of the select() function, Net::Server::SIG attempts
to fix the unsafe problem.  If a process is blocking on select() any
signal will short circuit the select.  Using this concept,
Net::Server::SIG does the least work possible (changing one bit from 0
to 1).  And depends upon the actual processing of the signals to take
place immediately after the the select call via the "check_sigs"
function.  See the example shown above and also see the sigtest.pl
script located in the examples directory of this distribution.

=head1 FUNCTIONS

=over 4

=item C<register_sig($SIG =E<gt> \&code_ref)>

Takes key/value pairs where the key is the signal name, and the
argument is either a code ref, or the words 'DEFAULT' or 'IGNORE'.
The function register_sig must be used in conjunction with check_sigs,
and with a blocking select() function call -- otherwise, you will
observe the registered signal mysteriously vanish.

=item C<unregister_sig($SIG)>

Takes the name of a signal as an argument.  Calls register_sig with a
this signal name and 'DEFAULT' as arguments (same as
register_sig(SIG,'DEFAULT')

=item C<check_sigs()>

Checks to see if any registered signals have occured.  If so, it will
play the registered code ref for that signal.  Return value is array
containing any SIGNAL names that had occured.

=item C<sig_is_registered($SIG)>

Takes a signal name and returns any registered code_ref for that signal.

=back

=head1 AUTHORS

Paul Seamons (paul@seamons.com)

Rob B Brown (rob@roobik.com) - Provided a sounding board and feedback
in creating Net::Server::SIG and sigtest.pl.

=head1 LICENSE

  This package may be distributed under the terms of either the
  GNU General Public License
    or the
  Perl Artistic License

  All rights reserved.

=cut
