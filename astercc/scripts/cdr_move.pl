#!/usr/bin/perl

use strict;
use DBI;
use Data::Dumper;

my	$dbhSource = DBI->connect("DBI:mysql:database=callshop;host=localhost;port=3306",'root','loh3288lgj');
$| = 1;
my $dateflag = "2008-06-15 00:00:00";

# set table CustomerReferral
{
	my $query = "SELECT * FROM historycdr WHERE calldate < '$dateflag' ";
	my $rows = $dbhSource->prepare($query);
	my $record = 0;
	$rows->execute() or {die $query.":".$dbhSource->errstr};
	while (my $ref = $rows->fetchrow_hashref()) {
		$record ++;
		$query = "INSERT INTO mycdr SET calldate = '$ref->{'calldate'}', src = '$ref->{'src'}', dst = '$ref->{'dst'}', channel = '$ref->{'channel'}', dstchannel = '$ref->{'dstchannel'}', duration = '$ref->{'duration'}', billsec = '$ref->{'billsec'}', disposition = '$ref->{'disposition'}', accountcode = '$ref->{'accountcode'}', userfield = '$ref->{'userfield'}', srcuid = '$ref->{'srcuid'}', dstuid = '$ref->{'dstuid'}', calltype = '$ref->{'calltype'}', credit = '$ref->{'credit'}', groupid = '$ref->{'groupid'}', callshopcredit = '$ref->{'callshopcredit'}', resellercredit = '$ref->{'resellercredit'}', resellerid = '$ref->{'resellerid'}', memo = '$ref->{'memo'}', destination = '$ref->{'destination'}' ";
		$dbhSource->do($query) or {die $query.":".$dbhSource->errstr};
		print "$query\n";

		$query = "DELETE FROM historycdr WHERE id = $ref->{'id'} ";
		$dbhSource->do($query) or {die $query.":".$dbhSource->errstr};
		print "$query\n";
		print "$record\n";
	}
	print "table: historycdr $record records updated\n";
}
