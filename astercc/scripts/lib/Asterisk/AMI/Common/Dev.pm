#!/usr/bin/perl

=head1 NAME

Asterisk::AMI::Common::Dev - Extends Asterisk::AMI::Common to include functions for the current development branch of asterisk

=head1 VERSION

0.2.4

=head1 SYNOPSIS

	use Asterisk::AMI::Common:Dev;

	my $astman = Asterisk::AMI::Common::Dev->new(	PeerAddr	=>	'127.0.0.1',
							PeerPort	=>	'5038',
							Username	=>	'admin',
							Secret		=>	'supersecrect'
					);

	die "Unable to connect to asterisk" unless ($astman);

	$astman->bridge($channel1, $channel2);

=head1 DESCRIPTION

This module extends Asterisk::AMI::Common to include additional functions for working with the development branch of Asterisk.
It will also be the launching ground for new functions be they are merged into AMI::Common.

=head2 Constructor

=head3 new([ARGS])

Creates new a Asterisk::AMI::Common::Dev object which takes the arguments as key-value pairs.

This module inherits all options from the AMI module.

=head2 Methods

This module currently does not provide any additional methods.

=head1 See Also

Asterisk::AMI, Asterisk::AMI::Common

=head1 AUTHOR

Ryan Bullock (rrb3942@gmail.com)

=head1 BUG REPORTING AND FEEBACK

Please report any bugs or errors to our github issue tracker at http://github.com/rrb3942/perl-Asterisk-AMI/issues
or the cpan request tracker at https://rt.cpan.org/Public/Bug/Report.html?Queue=perl-Asterisk-AMI

=head1 LICENSE

Copyright (C) 2010 by Ryan Bullock (rrb3942@gmail.com)

This module is free software.  You can redistribute it and/or
modify it under the terms of the Artistic License 2.0.

This program is distributed in the hope that it will be useful,
but without any warranty; without even the implied warranty of
merchantability or fitness for a particular purpose.

=cut

package Asterisk::AMI::Common::Dev;

use strict;
use warnings;
use parent qw(Asterisk::AMI::Common);

use version; our $VERSION = qv(0.2.4);

sub new {
	my ($class, %options) = @_;

	return $class->SUPER::new(%options);
}

1;
