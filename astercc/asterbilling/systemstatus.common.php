<?php
/*******************************************************************************
* systemstatus.common.php
* systemstatus参数信息文件
* systemstatus parameter file

* 功能描述
	检查用户权限
	初始化语言变量
	初始化xajax类
	预定义xajaxGrid中需要使用的一些参数
	注册xajax函数

* Function Desc
	authority
	initialize localization class
	initialize xajax class
	define xajaxGrid parameters

registed function:
*	call these function by xajax_ + funcionname
*	such as xajax_init()

	init					init html page
	showStatus				show sip extension status
	showChannelsInfo		show asterisk active channels

* Revision 0.045  2007/10/18 15:25:00  modified by solo
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
require_once 'config.php';

if ($_SESSION['curuser']['usertype'] != 'groupadmin' && $_SESSION['curuser']['usertype'] != 'operator')
	header("Location: index.php");


require_once ("include/xajax.inc.php");

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'systemstatus');

$xajax = new xajax("systemstatus.server.php");
$xajax->waitCursorOff();

$xajax->registerFunction("showStatus");
$xajax->registerFunction("init");
$xajax->registerFunction("hangup");
$xajax->registerFunction("addUnbilled");
$xajax->registerFunction("checkOut");
$xajax->registerFunction("invite");
$xajax->registerFunction("removeLocalChannel");
$xajax->registerFunction("checkOut");
$xajax->registerFunction("setCreditLimit");
$xajax->registerFunction("setStatus");
$xajax->registerFunction("setGroupBalance");
$xajax->registerFunction("searchRate");
$xajax->registerFunction("checkCustomer");
$xajax->registerFunction("setFreeCall");
$xajax->registerFunction("setFreeCallPage");
$xajax->registerFunction("removeReceipt");
$xajax->registerFunction("checkDestination");
$xajax->registerFunction("setLocked");

?>