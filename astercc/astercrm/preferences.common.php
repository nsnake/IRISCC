<?php
/*******************************************************************************
* preferences.common.php
* preferences参数信息文件

* preferences parameter file

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

* Revision 0.0456  2007/11/13 9:25:00  modified by solo
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


if ($_SESSION['curuser']['extension'] == '' or  ($_SESSION['curuser']['usertype'] != 'admin' && !is_array($_SESSION['curuser']['privileges']['preferences'])) ) 
	header("Location: portal.php");


require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'preferences');

$xajax = new xajax("preferences.server.php");

$xajax->registerFunction("init");
$xajax->registerFunction("savePreferences");
$xajax->registerFunction("checkDb");
$xajax->registerFunction("checkAMI");
$xajax->registerFunction("checkSys");
$xajax->registerFunction("saveLicence");
$xajax->registerFunction("systemAction");

?>