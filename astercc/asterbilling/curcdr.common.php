<?php
/*******************************************************************************
* curcdr.common.php

* curcdr parameter file

* 功能描述
	检查用户权限
	初始化语言变量
	初始化xajax类
	预定义xajaxGrid中需要使用的一些参数

* Function Desc

registed function:
*	call these function by xajax_ + funcionname
*	such as xajax_init()


* Revision 0.01  2011/11/08
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


if (empty($_SESSION['curuser']['usertype'])) {
	header("Location: systemstatus.php");
}

require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'curcdr');

$xajax = new xajax("curcdr.server.php");

$xajax->registerFunction("showGrid");
$xajax->registerFunction("delete");
$xajax->registerFunction("init");
$xajax->registerFunction("searchFormSubmit");

define("ROWSXPAGE", 15); // Number of rows show it per page.
define("MAXROWSXPAGE", 50);  // Total number of rows show it when click on "Show All" button.
?>
