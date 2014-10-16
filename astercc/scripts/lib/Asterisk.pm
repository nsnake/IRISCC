#
# $Id: Asterisk.pm,v 1.14 2007/07/27 18:08:40 james Exp $
#
package Asterisk;

require 5.004;

use vars qw($VERSION);

$VERSION = '0.10';

sub version { $VERSION; }

sub new {
	my ($class, %args) = @_;
	my $self = {};
	$self->{configfile} = undef;
	$self->{config} = {};
	bless $self, ref $class || $class;
	return $self;
}

sub DESTROY { }

1;
