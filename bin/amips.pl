#!/usr/bin/perl
#   AMI ProxyServer - an ami proxy server of asterisk
#   Copyright (C) 2010, Fonoirs Co.,LTD.
#   By Sun bing <hoowa.sun@gmail.com>
#
#   See http://www.freeiris.org/amips for more.
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
#   MA 02110-1301, USA.
#

# BASIC FUNCTIONS
use FindBin qw($Bin);
use Getopt::Long qw(:config no_ignore_case bundling);
use File::Basename;
use warnings;
use vars qw( $VERSION $OPT );

#-----------------------------------------------------------------------
#   BEGIN OF PROGRAM
#-----------------------------------------------------------------------
BEGIN {
our ($VERSION,$OPT);
my  ($MAINNAME,$OPT_RESULT,$CMDLINE);

    $VERSION = '0.5';
    $MAINNAME = fileparse($0);
    $MAINNAME =~ s/\.(.*)//;
    $CMDLINE = "$Bin/".$0." ".join(" ",@ARGV);
    $OPT = {
        'VERSION'       =>  $VERSION,
        'CMDLINE'       =>  $CMDLINE,
        # commandline opts
        'GET_HELP'      =>  '',       #boolean &opt_help()
        'GET_VERSION'   =>  '',       #boolean &opt_version()
        'GET_VERBOSE'   =>  '',       #boolean run services as level 2
        'GET_QUIET'     =>  '',       #boolean run services as background
        'GET_CONFIG'    =>  '',       #priority 1. cmd 2. default
        'GET_LIB'       =>  '',       #point which lib path
        # mainconfigure
        'CFG_RUNMODE'       =>  'verbose',    #runmode
        'CFG_MAINNAME'      =>  $MAINNAME,   # filename
        'CFG_GENERAL'       =>  '',   # amips.conf full path
        'CFG_USERS'         =>  '',   # amips_users.conf full path
        'CFG_LIB'           =>  '',   # lib folder
        'CFG_PID'           =>  '/var/run/'.$MAINNAME.'.pid',   # default only
        # syslog
        'SYSLOG_ENABLE' =>  'no',
        'SYSLOG_LEVEL'  =>  1,
        # AMI
        'AMI_HOST'          =>  '127.0.0.1',
        'AMI_PORT'          =>  5038,
        'AMI_USERNAME'      =>  'freeiris',
        'AMI_SECRET'        =>  'freeiris',
        'AMI_KEEPALIVE'     =>  30,
        'AMI_EVENTS_MEMCP'  =>  'yes',
        'AMI_EVENTS_MEMNOCP_BUFTIME'    =>  120,
        # UA
        'UA_PORT'               => 2012,
        'UA_HOST'               => '0.0.0.0',
        'UA_TIMEOUT'            => undef,
        'UA_MAX_CLIENTS'        => 1024,
        'UA_MAX_CMDRETRY_DIS'   => 8,
        'UA_MAX_READBUF_DIS'    => 1048576,
        'UA_MAX_WRITEBUF_DIS'   => 1048576,
        # SOCKET
        'SOCK_READ_BUFSIZE'     => 30000,
    };
    ## get argv options
    $OPT_RESULT = GetOptions(
            'help|?|h'=>\$OPT->{'GET_HELP'},
            'version|V'=>\$OPT->{'GET_VERSION'},
            'verbose|v'=>\$OPT->{'GET_VERBOSE'},
            'quiet|q'=>\$OPT->{'GET_QUIET'},
            'config|c=s'=>\$OPT->{'GET_CONFIG'},
            'lib|l=s'=>\$OPT->{'GET_LIB'},
            );
    exit unless ($OPT_RESULT);

    # user argv options
    if ($OPT->{'GET_HELP'} || (!$OPT->{'GET_VERSION'} && !$OPT->{'GET_VERBOSE'} && !$OPT->{'GET_QUIET'})) {
        print   "AMI ProxyServer version $VERSION\n".
                "Copyright (C) 2010, Fonoirs Co.,LTD.\n".
                "By Sun bing <hoowa.sun\@gmail.com>\n".
                "This is free software, and you are welcome to modify and redistribute it\n".
                "under the GPL version 2 license.\n".
                "This software comes with ABSOLUTELY NO WARRANTY.\n".
                "\n".
                "Usage: $0 [options]\n".
                "  -?, --help          Display this help and exit.\n".
                "  -V, --version       Output version information and exit.\n".
                "  -v, --verbose       Display more messages on screen.\n".
                "  -q, --quiet         Start Agispeedy as background.\n".
                "  -c, --config=path   Specify Config folder (exp. /etc/amips/).  \n".
                "  -l, --lib=lib       Specify amips library path. \n";
        exit;
    } elsif ($OPT->{'GET_VERSION'}) {
        print "AMI ProxyServer version $VERSION\n";
        exit;
    } elsif ($OPT->{'GET_VERBOSE'} && $OPT->{'GET_QUIET'}) {
        print "Your may set run AMI ProxyServer services as verbose or quiet.\n";
        exit;
    }

    #------------------------------------------------------------------
    # find where is amips.conf and LIB PATH
    #------------------------------------------------------------------
    foreach (@{[$OPT->{'GET_CONFIG'},"/etc/amips/","$Bin/../etc/","$Bin/"]}) {
        if (defined $_ && -e$_."/amips.conf" && -e$_."/amips_users.conf") {
            $OPT->{'CFG_GENERAL'} = $_."/amips.conf";
            $OPT->{'CFG_USERS'} = $_."/amips_users.conf";
            last;
        }
    }
    die "ERROR [".__LINE__."] : Not Found amips.conf, your may specify --config=[path] !\n" unless ($OPT->{'CFG_GENERAL'} ne '');
    foreach (@{[$OPT{'GET_LIB'},"$Bin/../lib/","$Bin/lib/"]}) {
        if (defined $_ && -e$_."/amips.pm") {
            $OPT{'CFG_LIB'} = $_;
            last;
        }
    }
    die "ERROR [".__LINE__."] : Not Found library path, your may specify --lib=[path] !\n" unless ($OPT{'CFG_LIB'} ne '');

    #------------------------------------------------------------------
    # runmode set
    #------------------------------------------------------------------
    if ($OPT->{'GET_VERBOSE'}) {
        $OPT->{'CFG_RUNMODE'}='verbose';
    } elsif ($OPT->{'GET_QUIET'}) {
        $OPT->{'CFG_RUNMODE'}='background';
        if (fork) {
            exit;
        }
    }

    # end of BEGIN Block
};


#-----------------------------------------------------------------------
#   Initilization and Running
#-----------------------------------------------------------------------
use lib "$OPT{'CFG_LIB'}/";
use amips;
use strict;

# amips library
my $amips = new amips($OPT);

# SIG settings
$SIG{__DIE__} = sub { $amips->do_log_write(0,$_[0]); exit; };
$SIG{TERM} = $SIG{INT} = sub { $amips->do_sig_term(); };
$SIG{HUP} = sub { $amips->do_sig_hup(); };

# file opts --> OPT
$amips->do_config_check();

# open syslog
$amips->do_log_open();

# file users --> OPT
$amips->do_config_users();

# PID
$amips->do_create_pid();

# start ua services
$amips->ua_service_on();

# start ami connect
$amips->ami_connect();

# start poll
$amips->do_poll_start();

#-----------------------------------------------------------------------
#   END OF PROGRAM
#-----------------------------------------------------------------------
END {
    $amips->ua_service_off();
    $amips->ami_disconnect();
    $amips->do_log_close();
}
