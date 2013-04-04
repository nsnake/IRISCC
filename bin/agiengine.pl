#!/usr/bin/perl -w

#agiengine - The agiengine is robust AGI Application Server
#implemention in asterisk.
#Author Hao Xu <loveme1314@gmail.com>
#
#See https://github.com/nsnake/perl-agiengine for more information about the
#project.

#use 5.010;
use strict qw(vars subs);
use FindBin();
use lib qw($FindBin::Bin lib);
use Getopt::Long qw(:config no_ignore_case bundling);
use Config::IniFiles;
use agiengine;

start_command();
sub start_command {
    my (%opt,%aeconf);
    my $help = <<"HELP";
agiengine version 1.1
Usage: $0 [options]
    -h, --help          Display this help and exit.
        --start         Start service.
        --stop          Stop service.
HELP

    #get argv options
    my $GetOptionsResult = GetOptions(
        'help|?|h' => \$opt{'help'},
        'start'    => \$opt{'start'},
        'stop'     => \$opt{'stop'},
    );
    if ( !$GetOptionsResult ) { exit; }
    if ( $opt{'help'} ) {
        _display( 'info', $help );
    }
    elsif ( $opt{'start'} ) {
        my $aeconfile = -e '/etc/freeiris2/freeiris.conf' ? '/etc/freeiris2/freeiris.conf' : $FindBin::Bin . '/etc/agiengine.conf';
        tie %aeconf, 'Config::IniFiles',( -file => $aeconfile );
        _agi_service_start(\%aeconf);
    }
    elsif ( $opt{'stop'} ) {
        _agi_service_stop();
    }
}

sub _agi_service_start {
    my $aeconf = shift;
    _check_env();
    if ( _check_run_status() ) {
        _display( 'die', ' Agiengine Sevrer Already Running !' );
    }
    agiengine->new(fri2conf=>$aeconf)->run();
}

sub _agi_service_stop {
    my $pid = _check_run_status();
    if ($pid) {
        _display( 'warn', "Stop Agiengine Service $pid" );
        system("kill 9 $pid");
        unlink("$FindBin::Bin/pid/agiengine.pid");
    }
}

#检测环境变量包括目录和权限等设置
sub _check_env {
    my $path = $FindBin::Bin;
    if (
        !(  -d "$path/modules/dynamic"
            && -d "$path/modules/static"
        )
      )
    {
        _display( 'die', 'modules directory not found!' );
    }
    if ( !-d "$path/pid" ) {
        _display( 'die', 'pid directory not found!' );
    }
}

#arg:no
#return: runing -> pid
#        norun  -> 0
sub _check_run_status {
    my $path = $FindBin::Bin;
    if ( $^O eq 'linux' && open( PID, "$path/pid/agiengine.pid" ) ) {

        # here's what to do if the file opened successfully
        my $line = <PID>;
        my $res  = `ps  --pid=$line 2>&1`;
        if ( $res =~ /\n(.*)\n/ ) {
            return $line;
        }
        else {
            unlink("$path/pid/agiengine.pid");
        }
    }
    return 0;
}

sub _display {
    my ( $type, $message ) = @_;
    #say STDERR $message;
    print $message . "\n";
    exit if ( $type eq 'die' );
}



exit;

