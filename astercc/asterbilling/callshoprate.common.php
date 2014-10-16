<?php
/*******************************************************************************
* rate.common.php

* rate parameter file

* 功能描述
	检查用户权限
	初始化语言变量
	初始化xajax类
	预定义xajaxGrid中需要使用的一些参数

* Function Desc

registed function:
*	call these function by xajax_ + funcionname
*	such as xajax_init()


* Revision 0.01  2007/11/21
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

require_once ('include/localization.class.php');

if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'reseller' &&  $_SESSION['curuser']['usertype'] != 'groupadmin') 
	header("Location: systemstatus.php");

require_once ("include/xajax.inc.php");
$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'rate');


$xajax = new xajax("callshoprate.server.php");

$xajax->registerFunction("showGrid");
$xajax->registerFunction("add");
$xajax->registerFunction("save");
$xajax->registerFunction("edit");
$xajax->registerFunction("update");
$xajax->registerFunction("delete");
$xajax->registerFunction("init");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("setGroup");
$xajax->registerFunction("multiEditUpdate");
$xajax->registerFunction("setMultieditType");
$xajax->registerFunction("showBuyRate");

define("ROWSXPAGE", 25); // Number of rows show it per page.
define("MAXROWSXPAGE", 50);  // Total number of rows show it when click on "Show All" button.
?>
