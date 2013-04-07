package agiengine;

#agiengine - The agiengine is robust AGI Application Server
#implemention in asterisk.
#Author Hao Xu <loveme1314@gmail.com>
#
#See https://github.com/nsnake/perl-agiengine for more information about the
#project.

use 5.010;
use warnings;
use strict qw(vars subs);
use Sintendo::DBH;
use Stingray::AGI();
use Time::HiRes qw(gettimeofday);
use File::Basename qw(basename);
use base qw(Net::Server::PreFork);

#use Data::Dumper qw(Dumper);

#####################    hooks
sub configure_hook
{
    my $self   = shift;
    my $path   = $FindBin::Bin;
    my $aeconf = $self->{server}->{fri2conf};
    $self->{server}->{host}        = $aeconf->{agiengine}{host};
    $self->{server}->{port}        = $aeconf->{agiengine}{port};
    $self->{server}->{user}        = $aeconf->{agiengine}{user};
    $self->{server}->{group}       = $aeconf->{agiengine}{group};
    $self->{server}->{min_servers} = $aeconf->{agiengine}{min_servers};
    $self->{server}->{min_spare_servers} =
      $aeconf->{agiengine}{min_spare_servers};
    $self->{server}->{max_spare_servers} =
      $aeconf->{agiengine}{max_spare_servers};
    $self->{server}->{max_servers}  = $aeconf->{agiengine}{max_servers};
    $self->{server}->{max_requests} = $aeconf->{agiengine}{max_requests};

    #这两个需要传入的是数组引用
    $self->{server}->{cidr_allow} = $aeconf->{agiengine}{cidr_allow};
    $self->{server}->{cidr_deny}  = $aeconf->{agiengine}{cidr_deny};
    $self->{server}->{log_level}  = $aeconf->{agiengine}{log_level};

    $self->{server}->{log_file}       = $aeconf->{agiengine}{log_file} || undef;
    $self->{server}->{pid_file}       = "$path/pid/agiengine.pid";
    $self->{server}->{dbautoconn}     = $aeconf->{database}{autoconn};
    $self->{server}->{check_for_dead} = 16;

    #$self->{server}->{check_for_waiting} = 8;

    if ($aeconf->{agiengine}{background})
    {
	$self->{server}->{setsid} =
	  $aeconf->{agiengine}{log_enable} ? undef : 1;
	$self->{server}->{background} = 1;
    }

}

#
#       configure_hook : This hook occurs just after the reading of
#                        configuration parameters and initiation of logging
#                        and pid_file creation.
#
sub post_configure_hook
{
    my $self = shift;
    my $path = $FindBin::Bin;
    $self->log(1, "\nServices starting !");

    #------------------------------------------------------------------
    # Preload static Agispeedy Modules
    #------------------------------------------------------------------
    while (<$path/modules/static/*.am>)
    {
	next if (!-e $_);
	my $scriptname = basename($_);
	$scriptname =~ s/\.(.*)//;

	#load static Agispeedy modules
	#eval{require $_;};  #need more test
	eval { do $_; };    #only for file is not exist
	if ($@)
	{
	    $self->log(
		       3,
		       $self->log_time
			 . ' Load static module failed: '
			 . $_ . "\n"
			 . $@
		      );
	    exit;
	}
	if (!defined *{$scriptname}{CODE})
	{
	    $self->log(
		       3,
		       $self->log_time
			 . '  Load static module failed: No entry function:'
			 . $scriptname
		      );
	    exit;
	}
	$self->log(
		   2,
		   $self->log_time
		     . ' Loading static modules successful: '
		     . $scriptname
		  );
    }
    return 1;
}

#       child_init_hook : This hook takes place immediately after the
#                        child process was init. if you want to make fast
#                        database connect, your can write your database handle
#                        followed sub child_init_hook()
#
sub child_init_hook
{
    my $self = shift;
    if ($self->{server}->{dbautoconn})
    {
	$self->database_pconnect();
    }
}

sub request_denied_hook
{
    my $self = shift;
    my $prop = $self->{server};
    $prop->{client}->send("Blocked IP", 0);
    $self->log(2, $self->log_time . "Block IP: $prop->{peeraddr}");
}

# when child request call followed function
sub process_request
{
    my $self        = shift;
    my $path        = $FindBin::Bin;
    my $server_prop = $self->{server};
    $server_prop->{agi} = Stingray::AGI->new();
    my $v8 = 0;
    #eval { require JavaScript::V8; };
    #my $v8 = !!$@;

#    if (!$v8)
#    {
#	$self->log(2, $self->log_time . "Enable v8: " . $v8);
#	$v8 = JavaScript::V8::Context->new();
#	$v8->bind('$AGI' => $server_prop->{agi});
#	$v8->bind('$DBH' => $server_prop->{dbh});
#    }

    # open agi debug if set verbose
    $server_prop->{agi}->_debug(5) if ($self->{server}->{log_level} > 3);

    # GET PARSE PARAM
    $self->{server}{input} = {$server_prop->{agi}->ReadParse()};

    my (%params, $request_all, $method, $param_string);

    ($method, $param_string) =
      $self->{server}{input}{request} =~
      m{(?<=://)(?:[\w-]+\.)+[\w-]+/?(\w+)\??(.*)};

    #support asterisk 1.4.X only
    if ($param_string)
    {
	foreach (split(/[&;]/, $param_string))
	{
	    my ($p, $v) = split('=', $_, 2);
	    if ($p) { $params{$p} = $v; }
	}
    }

    #support asterisk 1.6.X +
#    foreach (@{$server_prop->{agi}->get_fagiagrs()})
#    {
#	my ($p, $v) = split('=', $_, 2);
#	if ($p) { $params{$p} = $v; }
#    }

    $self->{server}{params} = \%params;

    #------------------------------------------------------------------
    # load agispeedy script modules
    #------------------------------------------------------------------
    if ($self->can($method))
    {
	$self->log(
		   2,
		   $self->log_time
		     . " Request-static : "
		     . $server_prop->{agi}{env}{request}
		  );
	$self->$method();
    }
    else
    {
	if (-e "$path/modules/dynamic/$method.am")
	{

	    #trying from dynamic
	    $self->log(
		       2,
		       $self->log_time
			 . " Request-dynamic : "
			 . $server_prop->{agi}{env}{request}
		      );

	    #load static module
	    eval { do "$path/modules/dynamic/$method.am"; };

	    #eval { require "$path/modules/dynamic/$method.am"; };
	    if ($@ || !defined *{$method}{CODE})
	    {
		$self->log(
			   3,
			   $self->log_time
			     . " Load dynamic module: $method failed !"
			  );
		$server_prop->{agi}->hangup();
		exit;
	    }

	    #run current agispeedy script modules
	    $self->$method() if ($self->can($method));
	}
	elsif ($v8 && -e "$path/modules/dynamic/$method.jam")
	{
	        my $reads;
		my $v8_context;
                open FILE, '<', "$path/modules/dynamic/$method.jam";
                undef $/; 
                $v8_context=<FILE>; 
                close(FILE); 
                $/="\n";

    
	    $v8->eval(
		qq/
			 $v8_context;
			 $method();
	       /
		     );
	    if ($@)
	    {
		$self->log(2, $self->log_time . " debug: " . $@);
	    }
	}
	else
	{
	    $self->log(3,
		     $self->log_time . " Request function: $method Not Found!");
	    $self->{server}{agi}->hangup();
	}
    }
    exit;
}

#public functuion
sub database_pconnect
{
    my $self       = shift;
    my $mysql_conf = $self->{server}->{fri2conf};
    if (!defined $self->{server}{dbh} || !$self->{server}{dbh}->ping)
    {
	$self->{server}{dbh} =
	  Sintendo::DBH->new(
			     {
			      dbname     => $mysql_conf->{database}{dbname},
			      server     => $mysql_conf->{database}{dbhost},
			      port       => $mysql_conf->{database}{dbport},
			      user       => $mysql_conf->{database}{dbuser},
			      password   => $mysql_conf->{database}{dbpass},
			      AutoCommit => 1,
			      RaiseError => 1,
			     }
			    );
	$self->log(2, $self->log_time . " Database Connected ! ");
    }
    return ($self->{server}{dbh});
}

1;
