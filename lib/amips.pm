package amips;
use Sys::Syslog;
use IO::Socket qw(:DEFAULT);
use IO::Poll qw(POLLIN POLLOUT POLLERR POLLHUP);
use Time::HiRes qw( usleep ualarm gettimeofday tv_interval );
use Errno qw(EWOULDBLOCK);
use constant CRLF => "\015\012";
use amips::ami;
use amips::ua;
use strict;
no strict "refs";

require Exporter;
our @ISA = qw(Exporter amips::ua amips::ami);

our $VERSION='0.4';

$/ = CRLF.CRLF;

sub new 
{
my  $class = shift;
my  $self = {
            'OPT'   =>  $_[0],
            
            'POLL'          =>  IO::Poll->new(),
            'POLL_STAMP'    =>  [],
            'POLL_STARTTIME'=>  time(),
            
            'AMI_SOCK'      =>  undef,
            'AMI_READBUF'   =>  '',
            'AMI_WRITEBUF'  =>  '',
            'AMI_EVENTS'    =>  {},
            'AMI_EVENTS_IDX'=>  [],
            
            'UA_USERS'      =>  {},
            'UA_SOCK'       =>  undef,
            'UA_SESS'       =>  {},
            'UA_SESS_IPADDR'=>  {},
            'UA_SESS_COUNT' =>  0,
            };
            
    bless($self,$class);

    return($self);
}

#-----------------------------------------------------------------------
#   Unix Signal
#-----------------------------------------------------------------------
sub do_sig_term 
{
my  $self = shift;
    $self->do_log_write(3,"TERM signal received, terminating amips...");
    exit;
}
sub do_sig_hup
{
my  $self = shift;    
    $self->do_log_write(3,"HUP signal received, reinitializing...");
    unlink($self->{OPT}{CFG_PID});
    $self->ua_service_off();
    $self->ami_disconnect();
    $self->do_log_close();
    exec $self->{OPT}{CMDLINE} or die "Can't reinit amips: $!";
}

#-----------------------------------------------------------------------
#   POLL handles
#-----------------------------------------------------------------------
sub do_poll_mask
{
my  $self = shift;
my  $handle = shift;
    $self->{POLL}->mask($handle=>POLLIN|POLLOUT);
    return();
}
sub do_poll_remove
{
my  $self = shift;
my  $handle = shift;
    $self->{POLL}->remove($handle);
    return();
}
sub do_poll_start
{
my  $self = shift;

    #------------ poll handles----------------------------------
    $self->do_poll_stamp_init();
    while(1) {

        ## emergency check health $_AMI_SOCKE
        # unless ($self->{AMI_SOCK});

        # handles events poll release to next while 1 seconds
        if ($self->{POLL}->poll(1)) {
            # readly to read
            for my $handle ($self->{POLL}->handles(POLLIN|POLLHUP|POLLERR)) {
                # read from ami server
                if ($self->{AMI_SOCK} && $handle eq $self->{AMI_SOCK}) {
                    $self->ami_read($handle);
                # new ua_socket connect incoming
                } elsif ($handle eq $self->{UA_SOCK}) {
                    $self->ua_clients_connect($handle);
                # read from ua_socket 
                } elsif ($self->{UA_SESS}{$handle}) {
                    $self->ua_read($handle);
                }
            }
            ## readly to write
            for my $handle ($self->{POLL}->handles(POLLOUT|POLLERR)) {
                # readly to write to ami server
                if ($self->{AMI_SOCK} && $handle eq $self->{AMI_SOCK}) {
                    $self->ami_write($handle);
                # readly to write to ua_socket           
                #} elsif ($handle eq $self->{UA_SOCK}) {

                # readly to write user socket
                } elsif ($self->{UA_SESS}{$handle}) {
                    $self->ua_write($handle);
                }
            }
        }

        # release CPU with usleep microseconds
        usleep(100_000);  

        # poll stamp
        $self->do_poll_stamp_select();

    }   # end of POLL
    #------------ poll handles----------------------------------
}

#   init poll stamp
sub do_poll_stamp_init
{
my  $self = shift;
    # recycle events buffer timer
    $self->{POLL_STAMP}->[0]=[gettimeofday];
    # ami_keepalive timer
    $self->{POLL_STAMP}->[1]=[gettimeofday];
}

#   do poll stamp
sub do_poll_stamp_select
{
my  $self = shift;
my  ($cur_epoch,$cur_float,$cur_btw);
    # count timestap
    $cur_epoch = [gettimeofday];
    $cur_float = $cur_epoch->[0].'.'.$cur_epoch->[1];
    
    # each one seconds
    $cur_btw = tv_interval($self->{POLL_STAMP}->[0],$cur_epoch);
    if ($cur_btw >= 1) {
        $self->{POLL_STAMP}->[0]=[gettimeofday];
        # memnocpy delete old events buffer
        $self->ami_do_recycle_events($cur_float);
        # check user clients timeout
        $self->ua_do_timeout($cur_epoch->[0]) unless (!$self->{OPT}{UA_TIMEOUT});
    }
    
    # for ami_keepalive
    $cur_btw = tv_interval($self->{POLL_STAMP}->[1],$cur_epoch);
    if ($cur_btw >= $self->{OPT}{AMI_KEEPALIVE}) {
        $self->{POLL_STAMP}->[1]=[gettimeofday];
        # keep alive to AMI server
        $self->ami_do_keepalive();
    }
    
}

#-----------------------------------------------------------------------
#   CONFIG FILES
#-----------------------------------------------------------------------
sub do_config_parse
{
my  $self = shift;
my  $filename = shift;

    if (open(CONF,"$filename")) {
    my  (%CONFIG,$last_section);
        foreach (split(/\n/,<CONF>)) {
            # trim 
            $_ =~ s/\n$//;
            $_ =~ s/[\;|\#](.*)//;
            
            # space line
            next if ($_ eq '');
            
            # current line is section
            if ($_ =~ /\[(.+)\]/) {
                $last_section=$1;
                $last_section =~ s/\n$//;
                $last_section =~ s/^\s+//;
                $last_section =~ s/\s+$//;
                $CONFIG{$last_section}={};
                next;
            } elsif ($_ =~ /=/) {
            my  ($key,$value)=split(/\=/,$_);
                $key =~ s/\n$//;
                $key =~ s/^\s+//;
                $key =~ s/\s+$//;
                $value =~ s/\n$//;
                $value =~ s/^\s+//;
                $value =~ s/\s+$//;
                $CONFIG{$last_section}{$key}=$value;
            }
        }
        close(CONF);
        return(\%CONFIG);
    }
    
    return();
}
# this config run before syslog opened
sub do_config_check
{
my  $self = shift;
my  $OPT = $self->{OPT};

my  $CONFILE=$self->do_config_parse($OPT->{'CFG_GENERAL'});
    if (! defined $CONFILE) {
        die "ERROR [".__LINE__."] : Unable to open amips.conf !\n";
    }
    #------------------------------------------------------------------
    # followed config load only from amips.conf
    #------------------------------------------------------------------
    if (defined$CONFILE->{'syslog'}{'enable'} && $CONFILE->{'syslog'}{'enable'} eq 'yes') {
        $OPT->{'SYSLOG_ENABLE'} = 'yes';
    }
    if (defined$CONFILE->{'syslog'}{'level'} && $CONFILE->{'syslog'}{'level'} !~ /[^0-9]/) {
        if ($CONFILE->{'syslog'}{'level'} >= 0 && $CONFILE->{'syslog'}{'level'} <= 4) {
            $OPT->{'SYSLOG_LEVEL'} = $CONFILE->{'syslog'}{'level'};
        } else {
            $OPT->{'SYSLOG_LEVEL'} = 0;
        }
    }
    
    if (defined$CONFILE->{'ami'}{'host'} && $CONFILE->{'ami'}{'host'} !~ /[^0-9]/) {
        $OPT->{'AMI_HOST'} = $CONFILE->{'ami'}{'host'};
    }
    if (defined$CONFILE->{'ami'}{'port'} && $CONFILE->{'ami'}{'port'} !~ /[^0-9]/) {
        $OPT->{'AMI_PORT'} = $CONFILE->{'ami'}{'port'};
    }
    if (defined$CONFILE->{'ami'}{'username'} && $CONFILE->{'ami'}{'username'} ne '') {
        $OPT->{'AMI_USERNAME'} = $CONFILE->{'ami'}{'username'};
    }
    if (defined$CONFILE->{'ami'}{'secret'} && $CONFILE->{'ami'}{'secret'} ne '') {
        $OPT->{'AMI_SECRET'} = $CONFILE->{'ami'}{'secret'};
    }
    if (defined$CONFILE->{'ami'}{'keepalive'} && $CONFILE->{'ami'}{'keepalive'} !~ /[^0-9]/) {
        $OPT->{'AMI_KEEPALIVE'} = $CONFILE->{'ami'}{'keepalive'};
    }
    if (defined$CONFILE->{'ami'}{'events_memcp'} && $CONFILE->{'ami'}{'events_memcp'} eq 'yes') {
        $OPT->{'AMI_EVENTS_MEMCP'} = $CONFILE->{'ami'}{'events_memcp'};
    } else {
        $OPT->{'AMI_EVENTS_MEMCP'} = 'no';
    }
    if (defined$CONFILE->{'ami'}{'events_memnocp_buftime'} && $CONFILE->{'ami'}{'events_memnocp_buftime'} !~ /[^0-9]/) {
        $OPT->{'AMI_EVENTS_MEMNOCP_BUFTIME'} = $CONFILE->{'ami'}{'events_memnocp_buftime'};
    }
    if (defined$CONFILE->{'clients'}{'host'} && $CONFILE->{'clients'}{'host'} ne '') {
        $OPT->{'UA_HOST'} = $CONFILE->{'clients'}{'host'};
    }
    if (defined$CONFILE->{'clients'}{'port'} && $CONFILE->{'clients'}{'port'} !~ /[^0-9]/) {
        $OPT->{'UA_PORT'} = $CONFILE->{'clients'}{'port'};
    }
    if (defined$CONFILE->{'clients'}{'timeout'} && $CONFILE->{'clients'}{'timeout'} !~ /[^0-9]/) {
        $OPT->{'UA_TIMEOUT'} = $CONFILE->{'clients'}{'timeout'};
    }
    if (defined$CONFILE->{'clients'}{'max_clients'} && $CONFILE->{'clients'}{'max_clients'} !~ /[^0-9]/) {
        $OPT->{'UA_MAX_CLIENTS'} = $CONFILE->{'clients'}{'max_clients'};
    }
    if (defined$CONFILE->{'clients'}{'max_cmdretry_disconnect'} && $CONFILE->{'clients'}{'max_cmdretry_disconnect'} !~ /[^0-9]/) {
        $OPT->{'UA_MAX_CMDRETRY_DIS'} = $CONFILE->{'clients'}{'max_cmdretry_disconnect'};
    }
    if (defined$CONFILE->{'clients'}{'max_readbuffer_disconnect'} && $CONFILE->{'clients'}{'max_readbuffer_disconnect'} !~ /[^0-9]/) {
        $OPT->{'UA_MAX_READBUF_DIS'} = $CONFILE->{'clients'}{'max_readbuffer_disconnect'};
    }
    if (defined$CONFILE->{'clients'}{'max_writebuffer_disconnect'} && $CONFILE->{'clients'}{'max_writebuffer_disconnect'} !~ /[^0-9]/) {
        $OPT->{'UA_MAX_WRITEBUF_DIS'} = $CONFILE->{'clients'}{'max_writebuffer_disconnect'};
    }
    if (defined$CONFILE->{'clients'}{'max_amicmd_disconnect'} && $CONFILE->{'clients'}{'max_amicmd_disconnect'} !~ /[^0-9]/) {
        $OPT->{'UA_AMICMD_DIS'} = $CONFILE->{'clients'}{'max_amicmd_disconnect'};
    }    
    
    return();
}
sub do_config_users
{
my  $self = shift;
    $self->{UA_USERS}=$self->do_config_parse($self->{OPT}{'CFG_USERS'});
    if (! defined $self->{UA_USERS}) {
        die "ERROR [".__LINE__."] : Unable to open amips_users.conf !\n";
    }
    return();
}

#-----------------------------------------------------------------------
#   SYSLOG SYSTEM
#   0 err
#   1 warning
#   2 notice
#   3 info
#   4 debug
#-----------------------------------------------------------------------
sub do_log_open
{
my  $self = shift;

    # try open syslog
    if ($self->{OPT}{'SYSLOG_ENABLE'} eq 'yes') {
        if (!openlog('amips','ndelay,nowait,pid','local0')) {
            $self->{OPT}{'SYSLOG_ENABLE'}='no';
        }
    }

    return();
}

sub do_log_close
{
my  $self = shift;
    closelog() if ($self->{OPT}{'SYSLOG_ENABLE'} eq 'yes');
    return();
}

sub do_log_write
{
my  $self = shift;
my  $level = shift;
my  $msg = shift;

my  $LEVELMSG=['err','warning','notice','info','debug'];

    if ($self->{OPT}{'SYSLOG_ENABLE'} eq 'yes' && $level <= $self->{OPT}{'SYSLOG_LEVEL'}) {
        syslog($LEVELMSG->[$level],$msg);
    }
    if ($self->{OPT}{'CFG_RUNMODE'} ne 'background') {
        warn "[".$LEVELMSG->[$level]."] ".$msg."\n";
    }
    
    return();
}

#-----------------------------------------------------------------------
#   checking pid exists and create new pid
#-----------------------------------------------------------------------
sub do_create_pid
{
my  $self = shift;
    
    if (-e$self->{OPT}{'CFG_PID'}) {
    my  $pid_number;
        open(READ,$self->{OPT}{'CFG_PID'}) or die "Can't open pid!";
        read(READ,$pid_number,32);
        close(READ);
        $pid_number=~ s/\n$//;

        if (-e"/proc/$pid_number/cmdline" && $pid_number ne $$) {
        my  $pid_cmdline = `cat /proc/$pid_number/cmdline`;
            $pid_cmdline =~ s/\n$//;
            #pid found
            if ($pid_cmdline =~ /$0/) {
                $self->do_log_write(1,"Already running: $pid_number");
                exit;
            } else {
                unlink($self->{OPT}{'CFG_PID'});
            }
        #pid not exists
        } else {
            unlink($self->{OPT}{'CFG_PID'});
        }

    }
    open(WRITE,">".$self->{OPT}{'CFG_PID'}) or die "Can't write pid file : $!";
    print WRITE $$;
    close(WRITE);
}

#-----------------------------------------------------------------------
#   SOCKET getchunk from handles with CRLFCLRF
#-----------------------------------------------------------------------
sub do_sock_getchunk
{
my  $self = shift;
my  $handle = shift;
my  $buffer_ref = shift;

my  (@chunk,$tmp_buffer,$find_startposition,$find_index_offset);
    
    #buffer old index position
    $find_startposition = length $$buffer_ref;

    #read buffer from handle
my  $rc = sysread($handle,$tmp_buffer,30000);
    unless (defined $rc) {# got an error
        if ($! == EWOULDBLOCK) {#blocked
            return($!,undef);
        } else {#read a little from handle
            $$buffer_ref .= $tmp_buffer;
        }
    } elsif ($rc == 0) { # failed to sysread
        return(0,undef);
    } else {# read full
        $$buffer_ref .= $tmp_buffer;
    }

    # set index offset
    if ($find_startposition < length($/)) {
        $find_index_offset=0;
    } else {
        $find_index_offset=$find_startposition-length($/);
    }
        
    # find end character
    while (1) {
        # try find CRLFCRLF
    my  $find = index($$buffer_ref,$/,$find_index_offset);
        # not found
        last if ($find < 0);
        # fill to array
    my  $one_chunk = substr($$buffer_ref,0,$find+length($/));
        push(@chunk,$one_chunk) if ($one_chunk ne $/); # if chunk only CRLFCRLF
        # earse from read buffer
        substr($$buffer_ref,0,$find+length($/)) = '';
        # buffer still need get chunk
        if (length $$buffer_ref != 0) {
            $find_index_offset=0;
            next;
        } else {
            last;
        }
    }
    
    # buffer read ok and not any chunk
    if (length $$buffer_ref == 0 && $#chunk < 0) {
        return(2,undef);
    }

    return(1,\@chunk);
}
#-----------------------------------------------------------------------
#   SOCKET getkeyvalue from datachunk to hash ref
#-----------------------------------------------------------------------
# split data to keyvalue hash ref
sub do_sock_getkeyvalue
{
my  $self = shift;
my  $data = shift;
my  %CMD;

    foreach (split(/\015\012/,$$data)) {
    my  ($key,$value) = split(/\:/,$_);
        $key =~ s/^\s+//;
        $key =~ s/\s+$//;
        $value =~ s/^\s+//;
        $value =~ s/\s+$//;
        if ($key ne '') {
            $CMD{lc($key)}=lc($value);
        }
    }
    return(\%CMD);
}

1;
