package amips::ami;
use Time::HiRes qw( usleep ualarm gettimeofday tv_interval );
use constant CRLF => "\015\012";
use Errno qw(EWOULDBLOCK);
use strict;

our $VERSION='0.3';

# require this file to amips
#-----------------------------------------------------------------------
#
#   AMI SOCKET FUNCTIONS
#
#-----------------------------------------------------------------------
sub ami_connect
{
my  $self = shift;

    $self->{AMI_SOCK} = undef;
    $self->{AMI_SOCK} = IO::Socket::INET->new(PeerAddr=>$self->{OPT}{'AMI_HOST'},
                            PeerPort=>$self->{OPT}{'AMI_PORT'},
                            Proto   => 'tcp',
                            );
    if (!$self->{AMI_SOCK}) {
        $self->do_log_write(1,"Connect AMI Server Failed.");
        return();
    }
    
my  $conn_buf;
    sysread($self->{AMI_SOCK},$conn_buf,2048);
    syswrite($self->{AMI_SOCK},"Action: login\r\nUsername: ".$self->{OPT}{AMI_USERNAME}."\r\nSecret: ".$self->{OPT}{AMI_SECRET}."\r\n\r\n");
    usleep(100_000);
    sysread($self->{AMI_SOCK},$conn_buf,2048);
    if ($conn_buf !~ /Response: Success/) {
        $self->{AMI_SOCK}->close();
        $self->{AMI_SOCK}=undef;
        $self->do_log_write(1,"Connect AMI Server Failed to Authentication.");
        return();
    }
    $self->{AMI_SOCK}->blocking(0);
    $self->do_log_write(2,"AMI ProxyServer Connect to Asterisk AMI ok...");

    #add to poll
    $self->do_poll_mask($self->{AMI_SOCK});

    return();
}
sub ami_disconnect
{
my  $self = shift;
    $self->{AMI_SOCK}->shutdown(1) if ($self->{AMI_SOCK});
    return();
}
#-----------------------------------------------------------------------
#   AMI READ FUNCTIONS
#-----------------------------------------------------------------------
# ami readly to read
sub ami_read
{
my  $self = shift;
my  $handle = shift;

    #read from handle and find data chunk
my  ($status,$datachunk) = $self->do_sock_getchunk($handle,\$self->{AMI_READBUF});
    # data blocked return
    if ($status == EWOULDBLOCK) {
        $self->do_log_write(4,"[ami_read] AMI Server blocked!");
        return();
    # read failed to reconnect
    } elsif ($status == 0) {
        $self->do_log_write(4,"[ami_read] AMI Server Going Away!");
        $self->ami_connect();
        return();
    # data not found chunk return
    } elsif ($status == 1 && $datachunk < 0) {
        $self->do_log_write(4,"[ami_read] AMI Server input buffer!");
        return();
    # status 2 means Only CRLF ATTACK
    #} elsif ($status == 2 && !defined $datachunk->[0]) {
    #    $self->ua_write_buffer($handle,"Error","Messsage: Missing action in request",undef);
    #    $self->{UA_SESS}{$handle}{'cmdretry'}++;
    #    return();
    }

    # format to command
my  ($responses,$events) = $self->ami_read_parse($handle,$datachunk);

    #write broadcast events
    # broadcast events
    if (defined $events) {
        #$$events .= CRLF.CRLF;   # append CRLFCRLF
        # no memory copy
        if ($self->{OPT}{'AMI_EVENTS_MEMCP'} eq 'no') {
        my  $timeid = gettimeofday();
            push(@{$self->{AMI_EVENTS_IDX}},$timeid);
            $self->{AMI_EVENTS}{$timeid}=$$events;
            $self->do_log_write(4,"[ami_read] Events put queue $timeid");        
        # memory copy
        } else {
            $self->do_log_write(4,"[ami_read] Memcopy events to clients!");
            foreach (keys %{$self->{UA_SESS}}) {
                if ($self->{UA_SESS}{$_}{'allow_events'} eq 'yes') {
                    $self->{UA_SESS}{$_}{'write_buffer'} .= $$events;
                }
            }
        }
    }
    
    #write user data
    foreach (@{$responses}) {
        # check actionid
        if ($_ =~ /actionid: (.+)${\CRLF}/i) {
        my  ($actionid,$ua_ipaddr,$userhandle,$usercustom);
            $actionid = $1;
            ($ua_ipaddr,$usercustom) = split(/\-/,$actionid);
            
            # get user handle from ipaddr
            $userhandle = $self->{UA_IPADDR}{$ua_ipaddr} if ($self->{UA_IPADDR}{$ua_ipaddr});
            
            # send to user write buffer
            if (defined $self->{UA_SESS}{$userhandle}) {
                # set to user self actionid if exists
                if (!$usercustom) {
                    $_ =~ s/actionid: (.+)${\CRLF}//i;
                } else {
                    $_ =~ s/actionid: (.+)${\CRLF}/actionid: $usercustom${\CRLF}/i;
                }
                $self->ua_write_buffer($userhandle,undef,$_,undef,1);
                $self->do_log_write(4,"[ami_read_parse] transfer to user write buffer!");
            # actionid is amips or not found drop response
            } else {
                $self->do_log_write(4,"[ami_read_parse] No valid ActionID drop response!");
            }
        # no actionid
        } else {
            $self->do_log_write(4,"[ami_read_parse] Not found ActionID drop response!");
        }
    }

}

# read ami results and parse it
sub ami_read_parse
{
my  $self = shift;
my  $handle = shift;
my  $chunks = shift;
my  (@responses,$events);

    # checking every chunk
    for (my $i=0;$i<=$#$chunks;$i++) {
        
        # trim begin CRLF+
        $chunks->[$i] =~ s/^[${\CRLF}]+//;
        #$chunks->[$i] =~ s/[${\CRLF}]+$//;

        # get actionid (means need transfer to user)
        if ($chunks->[$i] =~ /actionid: /i) {
            push(@responses,$chunks->[$i]);
            $self->do_log_write(4,"[ami_read_parse] read response!");
        # broadcast events
        } else {
            $events .= $chunks->[$i];
            $self->do_log_write(4,"[ami_read_parse] read events!");            
        }
        # events mode
        #if ($chunks->[$i] =~ /^event: /i) {
            
        # response mode
        #} elsif ($chunks->[$i] =~ /^response: /i) {
        #}
        
        next;
    }

    return(\@responses,\$events);
}

#-----------------------------------------------------------------------
#   AMI WRITE FUNCTIONS
#-----------------------------------------------------------------------
sub ami_write
{
my  $self = shift;
my  $handle = shift;

    #write personal ua data
    if (length($self->{AMI_WRITEBUF}) > 0) {

        local $SIG{PIPE} = 'IGNORE';
        
    my  $bytes = syswrite($handle,$self->{AMI_WRITEBUF});
    
        unless ($bytes) {
            if ($! == EWOULDBLOCK) {
                $self->do_log_write(4,"[ami_read] AMI Server blocked!");
                return();
            } else {
                $self->do_log_write(4,"[ami_read] AMI Server Going Away!");
                $self->ami_connect();
                return();
            }
        }
        substr($self->{AMI_WRITEBUF},0,$bytes) = '';
        $self->do_log_write(4,"[ami_write] ".$bytes." bytes!");
    }

    return();
}

# fill data to write_buffer
sub ami_write_buffer
{
my  $self = shift;
my  $msg = shift;
my  $noappend_crlf = shift;
    $self->{AMI_WRITEBUF} .= $msg;
    if (!$noappend_crlf) {
        $self->{AMI_WRITEBUF} .= CRLF.CRLF;
    }
    return();
}

#-----------------------------------------------------------------------
#   AMI COMMANDS KEEP health
#-----------------------------------------------------------------------
# memnocpy delete old events buffer
sub ami_do_recycle_events
{
my  $self = shift;
my  $cur_float = shift;

my  @loop = @{$self->{AMI_EVENTS_IDX}};
    foreach (@loop) {
        if ($cur_float > ($_+$self->{OPT}{'AMI_EVENTS_MEMNOCP_BUFTIME'})) {
            delete $self->{AMI_EVENTS}{$_};
            shift @{$self->{AMI_EVENTS_IDX}};
            $self->do_log_write(4,"[ami_recycle_events] Events $_ recycle!");
            next;
        }
        last;
    }
}

# ami keep alive
sub ami_do_keepalive
{
my  $self = shift;
    if (!$self->{AMI_SOCK}) {
        $self->ami_connect();
    } else {
        $self->ami_write_buffer("Action: ping".CRLF."ActionID: amips");
    }
    return();
}

1;
