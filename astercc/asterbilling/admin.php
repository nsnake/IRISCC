<?
	session_start();
	
	if (!isset($_SESSION['curuser']))
		header("Location: manager_login.php");

	if ($_SESSION['curuser']['usertype'] == 'groupadmin' ||  $_SESSION['curuser']['usertype'] == 'operator') 
		header("Location: systemstatus.php");
	elseif($_SESSION['curuser']['usertype'] == '')
		header("Location: manager_login.php");
	else
		header("Location: account.php");
?>