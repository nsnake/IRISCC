package Stingray::AGI;
use 5.010;
use strict;
use warnings;
use base qw(Asterisk::AGI);

sub new {
    my ( $class, %args ) = @_;
    my $self = {};
    $self->{'callback'}     = undef;
    $self->{'status'}       = undef;
    $self->{'lastresponse'} = undef;
    $self->{'lastresult'}   = undef;
    $self->{'hungup'}       = 0;
    $self->{'debug'}        = 0;
    $self->{'env'}          = undef;
    $self->{'fagiargs'}     = [];
    bless $self, ref $class || $class;
    return $self;

}

sub _ReallyReadParse {
    my ( $self, $fh ) = @_;

    my %input = ();

    $fh = \*STDIN if ( !$fh );

    while (<$fh>) {
        chomp;
        last unless length($_);
        if (/^agi_arg_\d+\:\s+(.*)$/) {
            push( @{ $self->{'fagiargs'} }, $1 );
        }
        elsif (/^agi_(\w+)\:\s+(.*)$/) {
            $input{$1} = $2;
        }
    }

    if ( $self->_debug > 0 ) {
        print STDERR "AGI Environment Dump:\n";
        foreach my $i ( sort keys %input ) {
            print STDERR " -- $i = $input{$i}\n";
        }
    }

    $self->_env(%input);
    return %input;
}

sub _checkresult {
    my ( $self, $response ) = @_;

    return undef if ( !defined($response) );
    my $result = undef;

    $self->_lastresponse($response);
    given ($response) {
        when (/^\d{3}(?: result=(.*)?(?: \(?.*\))?)|(?:-.*)$/) {
            $result = $self->{'lastresult'} = $1;
        }
        when (/\(noresponse\)/) {
            $self->_status('noresponse');
        }
    }
    print STDERR "_checkresult("
      . ( defined($response) ? $response : '' ) . ") = "
      . ( defined($result)   ? $result   : '' ) . "\n"
      if ( $self->_debug > 3 );
    return $result;

}

=item $AGI->get_fagiagrs()

When Asterisk 1.6 +, arguments passed to the AGI command are passed through to the FastAGI as AGI variables

Example: $AGI->get_fagiagrs();

Returns: Returns array reference from AGI

=cut

sub get_fagiagrs {
    my $self = shift;
    return $self->{'fagiargs'};
}

sub asyncagibreak {
    my $self = shift;
    return $self->execute("asyncagi break");
}

sub speechactivategrammar {
    my ($self,$grammarname) = @_;
    return $self->execute("speech activate grammar $grammarname");
}

sub speechcreate {
    my ($self,$engine) = @_;
    return $self->execute("speech create $engine");
}

sub speechdeactivategrammar {
    my ($self,$grammarname) = @_;
    return $self->execute("speech deactivate grammar $grammarname");
}

sub speechdestroy {
    my $self = shift;
    return $self->execute("speech destroy");
}

sub speechloadgrammar {
    my ($self,$grammarname) = @_;
    return $self->execute("speech load grammar $grammarname");

}

sub speechrecognize {
    my ($self,$prompt,$timeout,$offset) = @_;
    if (!defined $offset){
        $offset = '';
    }
    return $self->execute("speech recognize $prompt $timeout $offset");
}

sub speechset {
    my ($self,$name,$value) = @_;
    return $self->execute("speech set $name $value");
}

sub speechunloadgrammar {
    my ($self,$grammarname) = @_;
    return $self->execute("speech unload grammar $grammarname");
}

sub gosub {
    my ( $self, $context, $extension, $priority, $optional ) = @_;
    if ( !defined $optional ) {
        $optional = '';
    }
    return $self->execute("GOSUB $context $extension $priority $optional");
}

#For agi
#请求的脚本文件名
sub get_agirequest {
    return shift->{'env'}->{'request'};
}

#当前通道
sub get_agichannel {
    return shift->{'env'}->{'channel'};
}

sub get_agilanguage {
    return shift->{'env'}->{'language'};
}

#当前通道类别(例. "SIP" or "ZAP")
sub get_agitype {
    return shift->{'env'}->{'type'};
}

#当前unique ID
sub get_agiuniqueid {
    return shift->{'env'}->{'uniqueid'};
}

#Asterisk版本 (1.6)
sub get_agiversion {
    return shift->{'env'}->{'version'};
}

sub get_agicallerid {
    return shift->{'env'}->{'callerid'};
}

sub get_agicalleridname {
    return shift->{'env'}->{'calleridname'};
}

#The presentation for the callerid in a ZAP channel
sub get_agicallingpres {
    return shift->{'env'}->{'callingpres'};
}

#The number which is defined in ANI2 see Asterisk Detailed Variable List (only for PRI Channels)
sub get_agicallingani2 {
    return shift->{'env'}->{'callingani2'};
}

#The type of number used in PRI Channels see Asterisk Detailed Variable List
sub get_agicallington {
    return shift->{'env'}->{'callington'};
}

#An optional 4 digit number (Transit Network Selector) used in PRI Channels see Asterisk Detailed Variable List
sub get_agicallingtns {
    return shift->{'env'}->{'callingtns'};
}

#- The dialed number id (or "unknown")
sub get_agidnid {
    return shift->{'env'}->{'dnid'};
}

#The referring DNIS number (or "unknown")
sub get_agirdnis {
    return shift->{'env'}->{'rdnis'};
}

#当前的context
sub get_agicontext {
    return shift->{'env'}->{'context'};
}

#被叫号码
sub get_agiextension {
    return shift->{'env'}->{'extension'};
}

#当前跳数The priority it was executed as in the dial plan
sub get_agipriority {
    return shift->{'env'}->{'priority'};
}

#数值为1.0 如果是EAGI则为 0.0  以此类推
sub get_agienhanced {
    return shift->{'env'}->{'enhanced'};
}

#当前通道的Account code
sub get_agiaccountcode {
    return shift->{'env'}->{'accountcode'};
}

#执行该AGI脚本的线程ID (1.6)
sub get_agithreadid {
    return shift->{'env'}->{'threadid'};
}

1;
