<?php
header("content-type:text/html;charset=utf-8");
include_once('db_connect.php');

// get username/passwd first
$username = $_GET['username'];
$passwd = $_GET['passwd'];
if ($username == "" || $passwd == "") die;
if (ereg("[0-9a-zA-Z\@\.]+",$username) && ereg("[0-9a-zA-Z]+",$passwd)){
	$query = "SELECT * FROM astercrm_account WHERE username = '$username' ";
	$account = $db->getRow($query);
	$url = $_SERVER['SERVER_NAME'];
	$url .= substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/"));
	if ($passwd == md5($account['password'])){
		echo "200|http://$url/astercrmclient.php|http://$url/astercrmclientstatus.php|350#300";
	}else{
		echo "404";
	}
}
?>