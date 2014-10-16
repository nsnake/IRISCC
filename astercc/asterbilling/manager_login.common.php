<?php
/*******************************************************************************
********************************************************************************/
header('Content-Type: text/html; charset=utf-8');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());

require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

//session_start();

if (isset($_SESSION['curuser']['country']) )
	$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');
else
	$GLOBALS['locate']=new Localization('en','US','login');


$xajax = new xajax("manager_login.server.php");
$xajax->registerFunction("processForm");	 //registe xajax_processForm
$xajax->registerFunction("init");				//registe xajax_init
$xajax->registerFunction("setLang");
?>