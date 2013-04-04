# -*- perl -*-
#
#  Net::Server::Daemonize - Daemonization utilities.
#
#  $Id: Daemonize.pm,v 1.21 2012/05/29 16:09:10 rhandom Exp $
#
#  Copyright (C) 2001-2012
#
#    Jeremy Howard
#    j+daemonize@howard.fm
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

package Net::Server::Daemonize;

use strict;
use base qw(Exporter);
use POSIX qw(SIGINT SIG_BLOCK SIG_UNBLOCK);

our $VERSION = "0.06";

our @EXPORT_OK = qw(check_pid_file    create_pid_file   unlink_pid_file
                    is_root_user      get_uid get_gid   set_uid set_gid
                    set_user          safe_fork         daemonize);

###----------------------------------------------------------------###

### check for existance of pid_file
### if the file exists, check for a running process
sub check_pid_file ($) {
    my $pid_file = shift;
    return 1 if ! -e $pid_file;

    open my $fh, '<', $pid_file or die "Couldn't open existant pid_file \"$pid_file\" [$!]\n";
    my $current_pid = <$fh>;
    close $fh;
    $current_pid = ($current_pid =~ /^(\d{1,10})/) ? $1 : die "Couldn't find pid in existing pid_file";

    my $exists;
    if ($$ == $current_pid) {
        warn "Pid_file created by this same process. Doing nothing.\n";
        return 1;
    } elsif (-d "/proc/$$") { # try a proc file system
        $exists = -e "/proc/$current_pid";
    } elsif (kill 0, $current_pid) {
        $exists = 1;
    }
    die "Pid_file already exists for running process ($current_pid)... aborting\n"
        if $exists;

    # remove the pid_file
    warn "Pid_file \"$pid_file\" already exists.  Overwriting!\n";
    unlink $pid_file || die "Couldn't remove pid_file \"$pid_file\" [$!]\n";
    return 1;
}

### actually create the pid_file, calls check_pid_file
### before proceeding
sub create_pid_file ($) {
    my $pid_file = shift;

    check_pid_file($pid_file);

    open my $fh, '>', $pid_file or die "Couldn't open pid file \"$pid_file\" [$!].\n";
    print $fh "$$\n";
    close $fh;

    die "Pid_file \"$pid_file\" not created.\n" if ! -e $pid_file;
    return 1;
}

### Allow for safe removal of the pid_file.
### Make sure this process owns it.
sub unlink_pid_file ($) {
    my $pid_file = shift;
    return 1 if ! -e $pid_file; # no pid_file = return success

    open my $fh, '<', $pid_file or die "Couldn't open existant pid_file \"$pid_file\" [$!]\n"; # slight race
    my $current_pid = <$fh>;
    close $fh;
    chomp $current_pid;

    die "Process $$ doesn't own pid_file \"$pid_file\". Can't remove it.\n"
        if $current_pid ne $$;

    unlink($pid_file) || die "Couldn't unlink pid_file \"$pid_file\" [$!]\n";
    return 1;
}

###----------------------------------------------------------------###

sub is_root_user () {
    my $id = get_uid('root');
    return ! defined($id) || $< == $id || $> == $id;
}

### get the uid for the passed user
sub get_uid ($) {
    my $user = shift;
    my $uid  = ($user =~ /^(\d+)$/) ? $1 : getpwnam($user);
    die "No such user \"$user\"\n" unless defined $uid;
    return $uid;
}

### get all of the gids that this group is (space delimited)
sub get_gid {
    my @gid;

    foreach my $group ( split( /[, ]+/, join(" ",@_) ) ){
        if( $group =~ /^\d+$/ ){
            push @gid, $group;
        }else{
            my $id = getgrnam($group);
            die "No such group \"$group\"\n" unless defined $id;
            push @gid, $id;
        }
    }

    die "No group found in arguments.\n" unless @gid;
    return join(" ",$gid[0],@gid);
}

### change the process to run as this uid
sub set_uid {
    my $uid = get_uid(shift());

    POSIX::setuid($uid);
    if ($< != $uid || $> != $uid) { # check $> also (rt #21262)
        $< = $> = $uid; # try again - needed by some 5.8.0 linux systems (rt #13450)
        if ($< != $uid) {
            die "Couldn't become uid \"$uid\": $!\n";
        }
    }

    return 1;
}

### change the process to run as this gid(s)
### multiple groups must be space or comma delimited
sub set_gid {
    my $gids = get_gid(@_);
    my $gid  = (split /\s+/, $gids)[0];
    eval { $) = $gids }; # store all the gids - this is really sort of optional

    POSIX::setgid($gid);
    if (! grep {$gid == $_} split /\s+/, $() { # look for any valid id in the list
        die "Couldn't become gid \"$gid\": $!\n";
    }

    return 1;
}

### backward compatibility sub
sub set_user {
    my ($user, @group) = @_;
    set_gid(@group) || return undef;
    set_uid($user)  || return undef;
    return 1;
}

###----------------------------------------------------------------###

### routine to protect process during fork
sub safe_fork () {

    # block signal for fork
    my $sigset = POSIX::SigSet->new(SIGINT);
    POSIX::sigprocmask(SIG_BLOCK, $sigset) or die "Can't block SIGINT for fork: [$!]\n";

    my $pid = fork;
    die "Couldn't fork: [$!]" if ! defined $pid;

    $SIG{'INT'} = 'DEFAULT'; # make SIGINT kill us as it did before

    POSIX::sigprocmask(SIG_UNBLOCK, $sigset) or die "Can't unblock SIGINT for fork: [$!]\n";

    return $pid;
}

###----------------------------------------------------------------###

### routine to completely dissociate from terminal process.
sub daemonize ($$$) {
    my ($user, $group, $pid_file) = @_;

    check_pid_file($pid_file) if defined $pid_file;

    my $uid = get_uid($user);
    my $gid = get_gid($group); # returns list of groups
    $gid = (split /\s+/, $gid)[0];

    my $pid = safe_fork();

    exit(0) if $pid; # exit parent

    # child
    create_pid_file($pid_file) if defined $pid_file;
    chown($uid, $gid, $pid_file) if defined $pid_file;

    set_user($uid, $gid);

    open STDIN,  '<', '/dev/null' or die "Can't open STDIN from /dev/null: [$!]\n";
    open STDOUT, '>', '/dev/null' or die "Can't open STDOUT to /dev/null: [$!]\n";
    open STDERR, '>&STDOUT'       or die "Can't open STDERR to STDOUT: [$!]\n";

    ### does this mean to be chroot ?
    chdir '/' or die "Can't chdir to \"/\": [$!]";

    POSIX::setsid(); # Turn process into session leader, and ensure no controlling terminal

    ### install a signal handler to make sure SIGINT's remove our pid_file
    $SIG{'INT'}  = sub { HUNTSMAN($pid_file) } if defined $pid_file;
    return 1;
}

### SIGINT routine that will remove the pid_file
sub HUNTSMAN {
    my $path = shift;
    unlink $path;

    eval {
        require Unix::Syslog;
        Unix::Syslog::syslog(Unix::Syslog::LOG_ERR(), "Exiting on INT signal.");
    };

    exit;
}


1;

__END__

=head1 NAME

Net::Server::Daemonize - Safe fork and daemonization utilities

=head1 SYNOPSIS

    use Net::Server::Daemonize qw(daemonize);

    daemonize(
      'nobody',                 # User
      'nobody',                 # Group
      '/var/state/mydaemon.pid' # Path to PID file - optional
    );

=head1 DESCRIPTION

This module is intended to let you simply and safely daemonize your
server on systems supporting the POSIX module. This means that your
Perl script runs in the background, and it's process ID is stored in a
file so you can easily stop it later.

=head1 EXPORTED FUNCTIONS

=over 4

=item daemonize

Main routine.  Arguments are user (or userid), group (or group id or
space delimited list of groups), and pid_file (path to file).  This
routine will check on the pid file, safely fork, create the pid file
(storing the pid in the file), become another user and group, close
STDIN, STDOUT and STDERR, separate from the process group (become
session leader), and install $SIG{INT} to remove the pid file.  In
otherwords - daemonize.  All errors result in a die.  As of version
0.89 the pid_file is optional.

=item safe_fork

Block SIGINT during fork.  No arguments.  Returns pid of forked child.
All errors result in a die.

=item set_user

Become another user and group.  Arguments are user (or userid) and
group (or group id or space delimited list of groups).

=item set_uid

Become another user.  Argument is user (or userid).  All errors die.

=item set_gid

Become another group.  Arguments are groups (or group ids or space
delimited list of groups or group ids).  All errors die.

=item get_uid

Find the uid.  Argument is user (userid returns userid).  Returns
userid.  All errors die.

=item get_gid

Find the gids.  Arguments are groups or space delimited list of
groups.  All errors die.

=item is_root_user

Determine if the process is running as root.  Returns 1 or undef.

=item check_pid_file

Arguments are pid_file (full path to pid_file).  Checks for existance
of pid_file.  If file exists, open it and determine if the process
that created it is still running.  This is done first by checking for
a /proc file system and second using a "ps" command (BSD syntax).  (If
neither of these options exist it assumed that the process has ended)
If the process is still running, it aborts.  Otherwise, returns true.
All errors die.

=item create_pid_file.

Arguments are pid_file (full path to pid_file).  Calls check_pid_file.
If it is successful (no pid_file exists), creates a pid file and
stores $$ in the file.

=item unlink_pid_file

Does just that.

=back

=head1 SEE ALSO

L<Net::Server>.
L<Net::Daemon>, The Perl Cookbook Recipe 17.15.

=head1 AUTHORS

Jeremy Howard <j+daemonize@howard.fm>

Program flow, concepts and initial work.

Paul Seamons <paul@seamons.com>

Code rework and componentization.
Ongoing maintainer.

=head1 LICENSE

  This package may be distributed under the terms of either the
  GNU General Public License
    or the
  Perl Artistic License

  All rights reserved.

=cut
