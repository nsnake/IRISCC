<?php
include('asteriskconf.inc.php');

$sip = new asteriskconf();
	
	$sip->parse_in_file('delfrom.conf');

$sip->assign_delsection('1000');
//	$sip->debug();

	$sip->keep_resource_array=false;
	$sip->save_file('output.conf');
	print $sip->errstr;
?>