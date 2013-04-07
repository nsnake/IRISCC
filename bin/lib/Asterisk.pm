#
# $Id: Asterisk.pm,v 1.16 2009/06/26 03:31:39 james Exp $
#
package Asterisk;

require 5.004;

use vars qw($VERSION);

$VERSION = '1.01';

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
