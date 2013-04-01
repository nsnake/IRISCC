package amips::ua;
use Time::HiRes qw( usleep ualarm gettimeofday tv_interval );
use constant CRLF => "\015\012";
use Errno qw(EWOULDBLOCK);
use strict;
no strict "refs";

our $VERSION='0.5';

# require this file to amips
#-----------------------------------------------------------------------
#
#   UA SOCKET FUNCTIONS
#
#-----------------------------------------------------------------------
sub ua_service_on
{
my  $self = shift;
my  $poll = shift;

    # listen useragent socket
    $self->{UA_SOCK} = IO::Socket::INET->new(LocalHost => $self->{OPT}{'UA_HOST'},
                                    LocalPort => $self->{OPT}{'UA_PORT'},
                                    Listen    => 20,
                                    Proto     => 'tcp',
                                    Reuse     => 1,
                                    );
    die $@ unless $self->{UA_SOCK};
    $self->{UA_SOCK}->blocking(0);
    $self->do_poll_mask($self->{UA_SOCK});
    $self->do_log_write(2,"AMI ProxyServer ".$self->{OPT}{VERSION}." Services on ".$self->{OPT}{'UA_HOST'}.":".$self->{OPT}{'UA_PORT'}."...");
    return();
}
sub ua_service_off
{
my  $self = shift;
    $self->{UA_SOCK}->shutdown(1) if ($self->{UA_SOCK});
    return();
}

#-----------------------------------------------------------------------
#   UA CLIENTS HANDLE
#-----------------------------------------------------------------------
# ua new clients connect
sub ua_clients_connect
{
my  $self = shift;
my  $handle = shift;

my  $connect = $self->{UA_SOCK}->accept();
    if (!$connect) {
        $self->do_log_write(1,"[ua_do_clients_connect] : Invalid Connect may be need set an big ulimit values -> ulimit -n 65535");
        exit;
    # checked max clients        
    } elsif ($self->{UA_SESS_COUNT} >= $self->{OPT}{'UA_MAX_CLIENTS'}) {
        $self->do_log_write(1,"[ua_do_clients_connect] : Max of Clients limit.");
        $connect->close();
        return();
    }    
    $self->{UA_SESS_COUNT}++;

    #create session data
my  $curtime = gettimeofday();
    $self->{UA_SESS}{$connect} = {
        #sock
        'socket'=>$connect,
        #buffers
        'write_buffer'=>'',
        'read_buffer'=>'',
        #info
        'type'=>'',
        'username'=>'',
        'logtime'=>time(),
        'allow_command'=>'no',
        'allow_events'=>'no',
        'peer_ip'=>$connect->peerhost,
        'peer_port'=>$connect->peerport,
        #check
        'cmdretry'=>0,
        'last_action_time'=>time(),
        #memnocp resource
        'bcevents_id'=>$curtime,
        'bcevents_index'=>-1,
    };
    # put ipaddr array
    $self->{UA_IPADDR}{$connect->peerhost.'.'.$connect->peerport}=$connect;

    $self->do_poll_mask($connect);
    $self->ua_write_buffer($connect,undef,"AMI ProxyServer ".$VERSION.CRLF,undef,1);
    $self->do_log_write(2,"[ua_connect] : ".$self->{UA_SESS}{$connect}{'peer_ip'}.':'.$self->{UA_SESS}{$connect}{'peer_port'});
}

# ua clients disconnect
sub ua_clients_disconnect
{
my  $self = shift;
my  $handle = shift;

    # delete user data obj
    delete($self->{UA_IPADDR}{$handle->peerhost.'.'.$handle->peerport});
    delete($self->{UA_SESS}{$handle});
    # remove from poll
    $self->do_poll_remove($handle);
    # disconnect
    $handle->shutdown(1);
    # count --
    $self->{UA_SESS_COUNT}--;
    
    return();
}

#-----------------------------------------------------------------------
#   UA WRITE FUNCTIONS
#-----------------------------------------------------------------------
# fill data to write_buffer
sub ua_write_buffer
{
my  $self = shift;
my  $handle = shift;
my  $Response = shift;
my  $msg = shift;
my  $ua_actionid = shift;
my  $noappend_crlf = shift;

    if (defined $Response) {
        $self->{UA_SESS}{$handle}{'write_buffer'} .= "Response: $Response".CRLF;
    }
    $self->{UA_SESS}{$handle}{'write_buffer'} .= $msg;
    if (defined $ua_actionid) {
        $self->{UA_SESS}{$handle}{'write_buffer'} .= "ActionID: $ua_actionid".CRLF;
    }
    if (!defined $noappend_crlf) {
        $self->{UA_SESS}{$handle}{'write_buffer'} .= CRLF.CRLF;
    }
    return();
}

# ua readly to write
sub ua_write
{
my  $self = shift;
my  $handle = shift;

    #write personal ua data
    if (length($self->{UA_SESS}{$handle}{'write_buffer'}) > 0) {

        # update user last action time
        $self->{$handle}{last_action_time}=time();

        #max write buffer kickout
        if ((length $self->{UA_SESS}{$handle}{write_buffer}) >= $self->{OPT}{UA_MAX_WRITEBUF_DIS}) {
            $self->ua_clients_disconnect($handle);
            return();
        }

        local $SIG{PIPE} = 'IGNORE';
        
    my  $bytes = syswrite($handle,$self->{UA_SESS}{$handle}{'write_buffer'});
    
        unless ($bytes) {
            if ($! == EWOULDBLOCK) {
                $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                                $self->{UA_SESS}{$handle}{'peer_port'}." blocked!");
                return();
            } else {
                $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                                $self->{UA_SESS}{$handle}{'peer_port'}." remote disconnect!");
                $self->ua_clients_disconnect($handle);
                return();
            }
        }
        substr($self->{UA_SESS}{$handle}{'write_buffer'},0,$bytes) = '';
        $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                        $self->{UA_SESS}{$handle}{'peer_port'}." ".$bytes." bytes!");
    }

    #write memnocp data
    if (defined $self->{AMI_EVENTS_IDX}->[0] && $self->{UA_SESS}{$handle}{allow_events} eq 'yes') {
        
        # update user last action time
        $self->{$handle}{last_action_time}=time();
        
        # get new data from events buffer
        if ($self->{UA_SESS}{$handle}{'bcevents_index'} < 0) {
            foreach (@{$self->{AMI_EVENTS_IDX}}) {
                if ($_ > $self->{UA_SESS}{$handle}->{'bcevents_id'}) {
                    $self->{UA_SESS}{$handle}{'bcevents_id'} = $_;
                    $self->{UA_SESS}{$handle}{'bcevents_index'} = 0;
                    last;
                }
            }
        # continue to write but buffer no exists
        } elsif (!defined $self->{AMI_EVENTS}{$self->{UA_SESS}{$handle}->{'bcevents_id'}}) {
            if (defined $self->{AMI_EVENTS}{$self->{AMI_EVENTS_IDX}->[0]}) {
                $self->{UA_SESS}{$handle}{'bcevents_id'} = $self->{AMI_EVENTS_IDX}->[0];
                $self->{UA_SESS}{$handle}{'bcevents_index'} = 0;
            }
        }
        
        # continue to write or new write
        if ($self->{UA_SESS}{$handle}->{'bcevents_index'} >= 0) {
        local $SIG{PIPE} = 'IGNORE';
        my  $full = length $self->{AMI_EVENTS}{$self->{UA_SESS}{$handle}->{'bcevents_id'}};
        my  $bytes = syswrite($handle,$self->{AMI_EVENTS}{$self->{UA_SESS}{$handle}->{'bcevents_id'}},$full,$self->{UA_SESS}{$handle}{'bcevents_index'});
            unless ($bytes) {
                if ($! == EWOULDBLOCK) {
                    $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.$self->{UA_SESS}{$handle}{'peer_port'}." bcevents blocked!");
                    return();
                } else {
                    $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.$self->{UA_SESS}{$handle}{'peer_port'}." bcevents remote disconnect!");
                    $self->ua_clients_disconnect($handle);
                    return();
                }
            }
            #complited set index to -1
            if ($bytes == $full) {
                $self->{UA_SESS}{$handle}->{'bcevents_index'} = -1;            
            #not done set index to length
            } else {
                $self->{UA_SESS}{$handle}{'bcevents_index'}=$self->{UA_SESS}{$handle}{'bcevents_index'}+$bytes;
            }
            $self->do_log_write(4,"[ua_write] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.$self->{UA_SESS}{$handle}{'peer_port'}." bcevents ".$bytes." bytes!");        
        }
    }
    
    return();
}

#-----------------------------------------------------------------------
#   UA READ FUNCTIONS
#-----------------------------------------------------------------------
# ua readly to read
sub ua_read
{
my  $self = shift;
my  $handle = shift;

    # max read buffer kickout
    if ((length $self->{UA_SESS}{$handle}{read_buffer}) >= $self->{OPT}{UA_MAX_READBUF_DIS}) {
        $self->ua_clients_disconnect($handle);
        return();
    }
    
    # max command retry kickout
    if ($self->{UA_SESS}{$handle}{cmdretry} >= $self->{OPT}{UA_MAX_CMDRETRY_DIS}) {
        $self->ua_clients_disconnect($handle);
        return();
    }
    
    #read from handle and find data chunk
my  ($status,$datachunk) = $self->do_sock_getchunk($handle,\$self->{UA_SESS}{$handle}{'read_buffer'});
    # data blocked return
    if ($status == EWOULDBLOCK) {
        $self->do_log_write(4,"[ua_read] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                        $self->{UA_SESS}{$handle}{'peer_port'}." blocked!");
        return();
    # read failed to remove
    } elsif ($status == 0) {
        $self->do_log_write(4,"[ua_read] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                        $self->{UA_SESS}{$handle}{'peer_port'}." remote disconnect!");
        $self->ua_clients_disconnect($handle);
        return();
    # data not found chunk return
    } elsif ($status == 1 && !defined $datachunk->[0]) {
        $self->do_log_write(4,"[ua_read] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                        $self->{UA_SESS}{$handle}{'peer_port'}." input buffer!");
        return();
    # status 2 means Only CRLF ATTACK
    } elsif ($status == 2 && !defined $datachunk->[0]) {
        $self->ua_write_buffer($handle,"Error","Messsage: Missing action in request");
        $self->{UA_SESS}{$handle}{'cmdretry'}++;
        return();
    }
    
    # update user last action time
    $self->{$handle}{last_action_time}=time();

    # run commands
    $self->ua_read_commands($handle,$datachunk);
    
    return();
}

# read command parser
sub ua_read_commands
{
my  $self = shift;
my  $handle = shift;
my  $chunks = shift;

    # checking every chunk
    for (my $i=0;$i<=$#$chunks;$i++) {
        
        # max command retry
        if ($self->{UA_SESS}{$handle}{cmdretry} >= $self->{OPT}{UA_MAX_CMDRETRY_DIS}) {
            $self->ua_clients_disconnect($handle);
            return();
        }

        # trim begin CRLF+
        $chunks->[$i] =~ s/^[${\CRLF}]+//;
        #$chunks->[$i] =~ s/[${\CRLF}]+$//;
        
        # get action name and try to run commands
    my  $action = substr($chunks->[$i],0,index($chunks->[$i],CRLF));
        if ($action =~ /^action: (.+)/i) {
            $action = lc($1);
            $action =~ s/^\s+//;
            $action =~ s/\s+$//;
            
            # local command
            if ($self->can('ua_cmd_'.$action)) {
                # writelog
                $self->do_log_write(4,"[ua_read] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.
                        $self->{UA_SESS}{$handle}{'peer_port'}." local cmd ".$action."!");
                #run command                        
            my  $cmdname = 'ua_cmd_'.$action;
            my  $cmdargs = $self->do_sock_getkeyvalue(\$chunks->[$i]);            
                $self->$cmdname($handle,$cmdargs);
                next;
                
            # remote command with not allow, not user or command not allow
            } elsif ($self->{UA_SESS}{$handle}{type} eq '' || $self->{UA_SESS}{$handle}{allow_command} ne 'yes') {
                $self->ua_write_buffer($handle,"Error","Messsage: Permission denied");
                $self->{UA_SESS}{$handle}->{'cmdretry'}++;
                next;

            # remote command with (allowed users/admin)
            } elsif ($self->{UA_SESS}{$handle}{type} ne '') {
                
                if (!$self->{AMI_SOCK}) {
                    $self->ua_write_buffer($handle,"Error","Messsage: AMI Server Going Away!");
                    $self->ami_connect();
                } else {
                    my  $ua_ipaddr = $handle->peerhost.'.'.$handle->peerport;
                    # check actionid
                    if ($chunks->[$i] =~ /actionid: (.+)${\CRLF}/i) {
                    my  $actionid = $1;
                        $chunks->[$i] =~ s/actionid: $actionid${\CRLF}/ActionID: $ua_ipaddr\-$actionid${\CRLF}/i;
                    # no actionid add it
                    } else {
                        $chunks->[$i] =~ s/action: (.+)${\CRLF}/Action: $1${\CRLF}ActionID: $ua_ipaddr${\CRLF}/i;
                    }
                    # send command to ami server
                    $self->ami_write_buffer($chunks->[$i],1);
                    $self->do_log_write(4,"[ua_read] transfer $ua_ipaddr to ami server");
                }
                next;
                
            # not found local cmd and not agree remote command
            } else {
                $self->do_log_write(4,"[ua_read] ".$self->{UA_SESS}{$handle}{'peer_ip'}.':'.$self->{UA_SESS}{$handle}{'peer_port'}." error command!");
                $self->ua_write_buffer($handle,"Error","Messsage: Invalid/unknown command");
                $self->{UA_SESS}{$handle}->{'cmdretry'}++;
                next;
            }
            next;
            
        # not found action
        } else {
            $self->ua_write_buffer($handle,"Error","Messsage: Missing action in request");
            $self->{UA_SESS}{$handle}{'cmdretry'}++;
            next;            
        }
        # get action name and try to run commands
        
    }

    return();
}

#-----------------------------------------------------------------------
#
#   UA READS OF COMMANDS
#
#-----------------------------------------------------------------------
# not need auth
sub ua_cmd_login
{
my  $self = shift;
my  $handle = shift;
my  $command = shift;

    if ($self->{UA_SESS}{$handle}{'type'} ne '') {
        $self->ua_write_buffer($handle,"Success","Messsage: Already logged in",$command->{'actionid'});
    } elsif (defined $self->{UA_USERS}{$command->{'username'}} && $self->{UA_USERS}{$command->{'username'}}{'secret'} eq $command->{'secret'}) {
        $self->{UA_SESS}{$handle}{'type'} = $self->{UA_USERS}{$command->{'username'}}{'type'};
        $self->{UA_SESS}{$handle}{'allow_command'} = $self->{UA_USERS}{$command->{'username'}}{'allow_command'};
        $self->{UA_SESS}{$handle}{'allow_events'} = $self->{UA_USERS}{$command->{'username'}}{'allow_events'};
        $self->{UA_SESS}{$handle}{'username'} = $command->{'username'};
        $self->ua_write_buffer($handle,"Success","Messsage: Authentication accepted",$command->{'actionid'});

        #tune on events
        if (defined $command->{'eventmask'} && $command->{'eventmask'} eq 'on') {
            $self->{UA_SESS}{$handle}{allow_events} = 'yes';
        } elsif (defined $command->{'eventmask'} && $command->{'eventmask'} eq 'off') {
            $self->{UA_SESS}{$handle}{allow_events} = 'no';
        }
        
    } else {
        $self->ua_write_buffer($handle,"Error","Messsage: Authentication failed",$command->{'actionid'});
    }

    return();
}
# not need auth
sub ua_cmd_logoff
{
my  $self = shift;
my  $handle = shift;

    syswrite($handle,"Response: Goodbye".CRLF."Messsage: Thanks for all the fish.".CRLF.CRLF);
    $self->ua_clients_disconnect($handle);
    
    return();
}

sub ua_cmd_ping
{
my  $self = shift;
my  $handle = shift;
my  $command = shift;

    if ($self->{UA_SESS}{$handle}{'type'} eq '') {#not user / admin
        $self->ua_write_buffer($handle,"Error","Messsage: Permission denied",$command->{'actionid'});
        return();
    }
    
my  $timestamp = gettimeofday;
    $self->ua_write_buffer($handle,"Success",'Ping: Pong'.CRLF.'Timestamp: '.$timestamp,$command->{'actionid'});
    return();
}

# followed command need type=user or type=admin
sub ua_cmd_amips_status
{
my  $self = shift;
my  $handle = shift;
my  $command = shift;

    if ($self->{UA_SESS}{$handle}{'type'} eq '') {#not user / admin
        $self->ua_write_buffer($handle,"Error","Messsage: Permission denied",$command->{'actionid'});
        return();
    }

my  $data;
    $data .= 'server-name: AMI ProxyServer '.$VERSION.CRLF;
    $data .= 'server-starttime: '.$self->{POLL_STARTTIME}.'s'.CRLF;
    $data .= 'server-uptime: '.(time()-$self->{POLL_STARTTIME}).'s'.CRLF;
    $data .= 'server-sessions: '.$self->{UA_SESS_COUNT}.CRLF;
    if ($self->{AMI_SOCK}) {
        $data .= 'server-ami: online'.CRLF;
    } else {
        $data .= 'server-ami: offline'.CRLF;
    }
    $data .= 'user-type: '.$self->{UA_SESS}{$handle}{type}.CRLF;
    $data .= 'user-username: '.$self->{UA_SESS}{$handle}{username}.CRLF;
    $data .= 'user-logtime: '.$self->{UA_SESS}{$handle}{logtime}.CRLF;
    $data .= 'user-allow_command: '.$self->{UA_SESS}{$handle}{allow_command}.CRLF;
    $data .= 'user-allow_events: '.$self->{UA_SESS}{$handle}{allow_events}.CRLF;
    $data .= 'user-peer_ip: '.$self->{UA_SESS}{$handle}{peer_ip}.CRLF;
    $data .= 'user-peer_port: '.$self->{UA_SESS}{$handle}{peer_port}.CRLF;

    $self->ua_write_buffer($handle,"Follows",$data,$command->{'actionid'});

    return();
}

# reload user mapping(type=admin)
sub ua_cmd_amips_reload
{
my  $self = shift;
my  $handle = shift;
my  $command = shift;
    if ($self->{UA_SESS}{$handle}{type} ne 'admin') {
        $self->ua_write_buffer($handle,"Error","Messsage: Permission denied",$_->{'actionid'});
    } else {
        $self->do_config_users();
        $self->ua_write_buffer($handle,"Success",'',$command->{'actionid'});
        $self->do_log_write(3,"[ua_cmd_reload] Reload amips_users.conf");
    }
    return();
}

# open events
sub ua_cmd_events
{
my  $self = shift;
my  $handle = shift;
my  $command = shift;

    if ($self->{UA_SESS}{$handle}{'type'} eq '') {#not user / admin
        $self->ua_write_buffer($handle,"Error","Messsage: Permission denied",$command->{'actionid'});
        return();
    }
    
    if (defined $command->{'eventmask'} && $command->{'eventmask'} eq 'on') {
        $self->{UA_SESS}{$handle}{allow_events} = 'yes';
        $self->ua_write_buffer($handle,"Events On",'',$command->{'actionid'});
    } else {
        $self->{UA_SESS}{$handle}{allow_events} = 'no';
        $self->ua_write_buffer($handle,"Events Off",'',$command->{'actionid'});
    }

    return();
}

#-----------------------------------------------------------------------
#   UA COMMANDS KEEP health
#-----------------------------------------------------------------------
# ua_do_timeout
sub ua_do_timeout
{
my  $self = shift;
my  $current = shift;

my  @loop = keys %{$self->{UA_SESS}};
    foreach (@loop) {
        if ($current > ($self->{UA_SESS}{$_}{last_action_time}+$self->{OPT}{'UA_TIMEOUT'})) {
            $self->ua_clients_disconnect($self->{UA_SESS}{$_}{'socket'});
            $self->do_log_write(4,"[ua_do_timeout] user client socket timeout!");
        }
    }
}

1;
