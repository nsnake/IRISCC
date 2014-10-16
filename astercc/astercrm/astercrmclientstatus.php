<?php
header("content-type:text/html;charset=utf-8");
require_once('db_connect.php');
require_once ('include/asterevent.class.php');

$username = trim($_GET['username']);
if ( $username == "" ){
	echo 0;
	exit;
}

if ( ereg("[0-9a-zA-Z\@\.]+",$username) ){
	$query = "SELECT * FROM astercrm_account WHERE username = '$username' ";
	$row = $db->getRow($query);

	if ( $row['id'] == '' ){
		echo 0;
		exit;
	}
}

$event = asterEvent::checkNewCall(0,$row['extension'],$row['channel'],$row['agent']);
if ( $event['status'] == '' )
	echo 0;
else
	echo 1;
exit;
?>