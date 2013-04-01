<?php
include('asteriskconf.inc.php');

$sip = new asteriskconf();
$f = $sip->parse_in_file('sip.conf');

if ($f==false)
{
	echo "false";
}

$sections = $sip->section_list();

$parsed = $sip->section_all();

$keyslist = $sip->key_list('general');

$keyshash = $sip->key_all('general');

$keyvalue = $sip->get('general','language');

$sip->debug();
$sip->keep_resource_array = false;
$sip->reload();
$sip->debug();
?>