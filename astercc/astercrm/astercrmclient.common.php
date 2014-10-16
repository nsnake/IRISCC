<?php
/*******************************************************************************
* portal.common.php
* portal参数信息文件
* portal parameter file

* 功能描述
	检查用户权限
	初始化语言变量
	初始化xajax类
	预定义xajaxGrid中需要使用的一些参数
	根据用户定义, 注册xajax函数

* Function Desc
	authority
	initialize localization class
	initialize xajax class
	define xajaxGrid parameters

registed function:
*	call these function by xajax_ + funcionname
*	such as xajax_init()

basic functions
	init					init html page
	listenCalls				check database for new event
	transfer				click to transfer
	monitor					monitor control
	hangup					hangup a channel
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


//if ($_SESSION['curuser']['extension'] == '' && $_SESSION['curuser']['usertype'] != 'admin') 
//	header("Location: login.php");

if (!isset($_SESSION['curid']) && $_SESSION['curid'] =='' ) $_SESSION['curid']=0;

//require_once ('include/localization.class.php');
require_once ("include/xajax.inc.php");

//echo 0;exit;

//$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');


$xajax = new xajax("astercrmclient.server.php");
//$xajax->debugOn();
$xajax->waitCursorOff();
$xajax->registerFunction("listenCalls");
$xajax->registerFunction("transfer");
$xajax->registerFunction("init");
$xajax->registerFunction("monitor");
$xajax->registerFunction("hangup");
$xajax->registerFunction("invite");
$xajax->registerFunction("showportal");
?>