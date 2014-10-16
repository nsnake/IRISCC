#!/usr/bin/perl
use strict;
use FindBin qw($Bin);
use lib "$Bin/lib";
#use JSON::XS;
use DBI;
use Config::IniFiles;
use Time::Local;

my $sec;
my $min;
my $hour;
my $mday;
my $mon;
my $year;
my $wday;
my $yday;
my $isdst;
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
my $curTime = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year + 1900, $mon +1, $mday,$hour,$min,$sec);


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

my %data;
my %dataHistory;

#query account data
my $account_query = "SELECT * FROM account WHERE $localConditions";
&query_to_make_hash($account_query,'','account');

#query account history data
my $account_history_query = "SELECT * FROM account_history WHERE $localConditions";
&query_to_make_hash($account_history_query,'history','account_history');

#query accountgroup data
my $accountgroup_query = "SELECT * FROM accountgroup WHERE $localConditions";
&query_to_make_hash($accountgroup_query,'','accountgroup');

#query accountgroup history data
my $accountgroup_history_query = "SELECT * FROM accountgroup_history WHERE $localConditions";
&query_to_make_hash($accountgroup_history_query,'history','accountgroup_history');

#query resellergroup data
my $resellergroup_query = "SELECT * FROM resellergroup WHERE $localConditions";
&query_to_make_hash($resellergroup_query,'','resellergroup');

#query resellergroup history data
my $resellergroup_history_query = "SELECT * FROM resellergroup_history WHERE $localConditions";
&query_to_make_hash($resellergroup_history_query,'history','resellergroup_history');

#query myrate data
my $myrate_query = "SELECT * FROM myrate WHERE $localConditions";
&query_to_make_hash($myrate_query,'','myrate');

#query myrate history data
my $myrate_history_query = "SELECT * FROM myrate_history WHERE $localConditions";
&query_to_make_hash($myrate_history_query,'history','myrate_history');

#query callshoprate data
my $callshoprate_query = "SELECT * FROM callshoprate WHERE $localConditions";
&query_to_make_hash($callshoprate_query,'','callshoprate');

#query callshoprate history data
my $callshoprate_history_query = "SELECT * FROM callshoprate_history WHERE $localConditions";
&query_to_make_hash($callshoprate_history_query,'history','callshoprate_history');

#query resellerrate data
my $resellerrate_query = "SELECT * FROM resellerrate WHERE $localConditions";
&query_to_make_hash($resellerrate_query,'','resellerrate');

#query resellerrate history data
my $resellerrate_history_query = "SELECT * FROM resellerrate_history WHERE $localConditions";
&query_to_make_hash($resellerrate_history_query,'history','resellerrate_history');

#query clid data
my $clid_query = "SELECT * FROM clid WHERE $localConditions";
&query_to_make_hash($clid_query,'','clid');

#query clid history data
my $clid_history_query = "SELECT * FROM clid_history WHERE $localConditions";
&query_to_make_hash($clid_history_query,'history','clid_history');


my $other_host = $cfg->val('hostlist','otherHost');#other host which need to backup the data to this host
my @otherHost = split /,/, $other_host;

my $host;
my $curdbInfo;
my $inserErrorMsg;
my $repeateMsg;
foreach $host (@otherHost) {#Loop connect the other database
	my @curHostArray = split /:/,$host;
	$repeateMsg .= "Synchronization Host:".@curHostArray[0]." \n ";
	my %curdbInfo = (
		'dbhost'  => trim(@curHostArray[0]),
		'dbname'  => trim(@curHostArray[1]),
		'dbuser'  => trim(@curHostArray[2]),
		'dbpasswd'=> trim(@curHostArray[3]),
	);

	my $cur_dbh = &connect_mysql(%curdbInfo);

	my $history_key;
	foreach $history_key (keys %dataHistory) {
		my $tableName = $history_key;
		$tableName =~ s/\_history$//g;
		
		my $cur;
		foreach $cur (@{$dataHistory{$history_key}}) {
			my $del_query = "DELETE FROM ".$tableName." WHERE id='".$cur->{'id'}."' ";
			$cur_dbh->do($del_query) or &debug($cur_dbh->errstr."($del_query)");
			
			my $del_ori_query = "DELETE FROM $history_key WHERE id='".$cur->{'id'}."' ";
			$dbh->do($del_ori_query) or &debug($dbh->errstr."($del_ori_query)");
		}

	}

	my $original_key;
	foreach $original_key (keys %data) {
		my $ori_table = $original_key;
		my $ori_cur;
		
		foreach $ori_cur (@{$data{$original_key}}) {
			my $curId = $ori_cur->{'id'};
			
			#if account's username repeate  then next;
			if($ori_table eq 'account'){
				my $query = "SELECT * FROM account WHERE username ='".%$ori_cur->{'username'}."' AND id != '$curId'";
				my $repeate = $cur_dbh->do($query);
				if($repeate ne '0E0'){
					$repeateMsg .= "username : ".%$ori_cur->{'username'}." \n ";
					next;
				}
			}
			
			#if both of groupid and resellerid is 0 then next;
			if($ori_table eq 'account' or $ori_table eq 'myrate' or $ori_table eq 'callshoprate'){
				my $curGroupid = %$ori_cur->{'groupid'};
				my $curResellerid = %$ori_cur->{'resellerid'};
				if($curGroupid eq '0' and $curResellerid eq '0') {
					next;
				}
			}

			#if resellerid is 0 then next;
			if($ori_table eq 'resellerrate'){
				my $curResellerid = %$ori_cur->{'resellerid'};
				if($curResellerid eq '0') {
					next;
				}
			}
			
			my $insert_query = "INSERT INTO $ori_table SET ";
			my $select_query = "SELECT * FROM $ori_table WHERE id='$curId' ";
			my $update_query = "UPDATE $ori_table SET ";
			while(my ($key, $value) = each %$ori_cur) {
				if($key ne 'addtime'){
					$insert_query .= "`$key` = '$value',";

					if($key ne 'id'){
						$update_query .= "`$key` = '$value',";
					}
				}
			}
			$insert_query .= "addtime='$curTime'";
			$update_query .= "addtime='$curTime' WHERE id='$curId'";
			
			my $affect = $cur_dbh->do($select_query);
			
			if ($affect ne '0E0'){
				#print $update_query."\n";exit;
				$cur_dbh->do($update_query) or &debug($ori_table.' : this data Id is '.$curId.'  '.$cur_dbh->errstr);
			}else{
				$cur_dbh->trace(0);
				$cur_dbh->do($insert_query) or &debug($ori_table.' : this data Id is '.$curId.'  '.$cur_dbh->errstr);
				
				my $insertId = $cur_dbh->{q{mysql_insertid}};
				
				if($insertId gt '0'){
					&debug("insert to $ori_table success");
				} else {
					$inserErrorMsg .= $ori_table.' : this data Id is '.$curId.'  '.$cur_dbh->errstr." \n ";
				}
			}
		}
	}
	
	$cur_dbh->disconnect();#disconnect cur_dbh database
}


#if has the error message,send the error message to the admin by set in the conf file
if($inserErrorMsg ne '' || $repeateMsg ne ''){
	my $mailMessage ;
	if($repeateMsg ne ''){
		$mailMessage .= $repeateMsg." \n ";
	}

	if($inserErrorMsg ne ''){
		$mailMessage .= $inserErrorMsg;
	}
	&simplemail('Insert Error',"synchronization to ".$cfg->val('database', 'dbhost')." \n ".$mailMessage);
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


sub query_to_make_hash{
	my $query = shift;

	my $queryType = shift;

	my $query_table = shift;
	
	my $rows = $dbh->prepare($query);
	$rows->execute() or &debug($dbh->errstr);
	while(my $rowHash = $rows->fetchrow_hashref()){#fetchrow_array
		if($queryType eq 'history') {
			if(!exists $dataHistory{$query_table}){
				$dataHistory{$query_table} = [];
			}
			push(@{$dataHistory{$query_table}},{%$rowHash});
		} else {
			if(!exists $data{$query_table}){
				$data{$query_table} = [];
			}
			push(@{$data{$query_table}},{%$rowHash});
		}
	}
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

