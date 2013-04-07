# -*- perl -*-
#
#  Net::Server::Single - Net::Server personality
#
#  $Id: Single.pm,v 1.6 2012/05/29 22:53:00 rhandom Exp $
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

package Net::Server::Single;

use strict;
use base qw(Net::Server);

sub net_server_type { __PACKAGE__ }

### this module is simple a place holder so that
### Net::Server::MultiType can ask for Single as one of
### the fall back methods (which it does any way).
### Essentially all we are doing here is providing parallelism.

1;

__END__

=head1 NAME

Net::Server::Single - Net::Server personality

=head1 SYNOPSIS

    use base qw(Net::Server::Single);

    sub process_request {
        #...code...
    }

=head1 DESCRIPTION

This module offers no functionality beyond the Net::Server
base class.  This modules only purpose is to provide
parallelism for the MultiType personality.

See L<Net::Server>

=cut
