#!/usr/bin/perl
use strict;
use FindBin qw($Bin);
use lib "$Bin/lib";
#use JSON::XS;
use DBI;
use Config::IniFiles;
use Time::Local;

# Author: Suebao@astercc.org

my $conf_file = "./synchronization.conf" ;
# read parameter from conf file
my $cfg = new Config::IniFiles -file => $conf_file;
if (not defined $cfg) {
	print "Failed to parse $conf_file:\n";
	foreach(@Config::IniFiles::errors) {
		print "Error: $_";
	}
	exit(1);
}

my $local_minId = trim($cfg->val('local','minid'));
my $local_maxId = trim($cfg->val('local','maxid'));
my $localConditions = " id between $local_minId AND $local_maxId ";

my %dbInfo = (
	'dbtype' => trim($cfg->val('database', 'dbtype')),
	'dbhost' => trim($cfg->val('database', 'dbhost')),
	'dbname' => trim($cfg->val('database', 'dbname')),
	'dbport'  => trim($cfg->val('database', 'dbport')),
	'dbuser'  => trim($cfg->val('database', 'username')),
	'dbpasswd'  => trim($cfg->val('database', 'password')),
	'dbprefix'  => trim($cfg->val('database', 'prefix')),
);

my $dbh = &connect_mysql(%dbInfo);

my @clidData;

#query clid data
my $clid_query = "SELECT * FROM clid ";#WHERE $localConditions
my $rows = $dbh->prepare($clid_query);
$rows->execute() or &debug($dbh->errstr);
while(my $rowHash = $rows->fetchrow_hashref()){#fetchrow_array
	my $curClid = $rowHash->{'clid'};
	
	push(@clidData,$curClid);
}

my $other_host = $cfg->val('hostlist','otherHost');#other host which need to backup the data to this host
my @otherHost = split /,/, $other_host;

my $host;
my $curdbInfo;
my $repeateMsg;
foreach $host (@otherHost) {#Loop connect the other database
	my @curHostArray = split /:/,$host;
	my %curdbInfo = (
		'dbhost'  => trim(@curHostArray[0]),
		'dbname'  => trim(@curHostArray[1]),
		'dbuser'  => trim(@curHostArray[2]),
		'dbpasswd'=> trim(@curHostArray[3]),
	);

	my $cur_dbh = &connect_mysql(%curdbInfo);

	my $query .= "SELECT * FROM clid ";
	
	my $rows = $cur_dbh->prepare($query);
	$rows->execute() or &debug($dbh->errstr);
	while(my $rowHash = $rows->fetchrow_hashref()){
		my $cur_clid = $rowHash->{'clid'};
		if(grep {$cur_clid eq $_ } @clidData){
			$repeateMsg .= "clid : ".$cur_clid." | resellerid :".$rowHash->{'resellerid'}." | groupid :".$rowHash->{'groupid'}." \n ";
		}
	}
	
	$cur_dbh->disconnect();#disconnect cur_dbh database
}

#if has the error message,send the error message to the admin by set in the conf file
if($repeateMsg ne ''){
	&simplemail('Repeate ',"Repeate Clid: \n ".$cfg->val('database', 'dbhost')." \n ".$repeateMsg);
}

sub connect_mysql
{
	my	%info = @_;
	my	$connect_dbh = DBI->connect("DBI:mysql:database=$info{'dbname'};host=$info{'dbhost'};port=$info{'dbport'}",$info{'dbuser'},$info{'dbpasswd'});
	return($connect_dbh);
}

sub trim($)
{
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	return $string;
}

sub debug{
	my $message = shift;
	my $time=scalar localtime;
	
	open (HDW,">>$Bin/synchronization.log");
	print HDW $time," ",$message,"\n";
	close HDW;
}

sub simplemail{
	my $email = $cfg->val('user','mail');
	
	my $mail_subject = shift;
	my $content = shift;
	my $sendmail = "/usr/sbin/sendmail -t";
	
	my $reply_to = "Reply-to: no-reply\@astercc.com\n";
	my $subject = "Subject: $mail_subject\n";
	my $from = "From: no-reply\@astercc.com\n";
	foreach (split(/\,/,$email)) {
		if ($_ eq "") {
			return;
		}
		my $send_to = "To: $_\n";
		open(SENDMAIL, "|$sendmail") or &debug("Cannot open $sendmail: $!");
		print SENDMAIL $send_to;
		print SENDMAIL $from;
		print SENDMAIL $reply_to;
		print SENDMAIL $subject;
		print SENDMAIL "Content-type: text/plain\n\n";
		print SENDMAIL $content;
		close(SENDMAIL);
		&debug("mail sent to $_");
	}
	#exit;
}



$dbh->disconnect();#disconnect local database

exit;

