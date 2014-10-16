<?php
/*******************************************************************************
* profile.common.php
* profile参数信息文件

* profile parameter file

* 功能描述
	检查用户权限
	初始化语言变量
	初始化xajax类
	预定义xajaxGrid中需要使用的一些参数

* Function Desc
	authority
	initialize localization class
	initialize xajax class
	define xajaxGrid parameters

* Revision 0.0057  2009/03/28 15:47:00  last modified by donnie
* Desc: page created

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


if ($_SESSION['curuser']['usertype'] != 'reseller' &&  $_SESSION['curuser']['usertype'] != 'groupadmin') 
	header("Location: admin.php");


require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'profile');

$xajax = new xajax("profile.server.php");

$xajax->registerFunction("init");
$xajax->registerFunction("rechargeByPaypal");
$xajax->registerFunction("resellerPaymentInfoEdit");
$xajax->registerFunction("resellerPaymentInfoUpdate");

?>