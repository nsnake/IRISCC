<?php
/*******************************************************************************
* ticket_details.common.php
* ticket_details参数信息文件
* ticket_details parameter file

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
	dial					click to dial
	transfer				click to transfer
	addWithPhoneNumber
	monitor					monitor control
	hangup					hangup a channel
	chanspy					spy on a extension

astercrm functions
	showGrid
	add
	edit
	delete
	save
	update
	init
	showDetail
	searchFormSubmit

* Revision 0.0456  2007/11/7 14:45:00  modified by solo
* Desc:


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


if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['ticket_details'])) 
	header("Location: portal.php");


require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'ticket_details');


$xajax = new xajax("ticket_details.server.php");

$xajax->registerFunction("showGrid");
$xajax->registerFunction("add");
$xajax->registerFunction("save");
$xajax->registerFunction("edit");
$xajax->registerFunction("update");
$xajax->registerFunction("delete");
$xajax->registerFunction("init");
$xajax->registerFunction("showDetail");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("relateByCategoryId");
$xajax->registerFunction("relateByGroup");
$xajax->registerFunction("deleteByButton");
$xajax->registerFunction("viewSubordinateTicket");

define("ROWSXPAGE", 10); // Number of rows show it per page.
define("MAXROWSXPAGE", 25);  // Total number of rows show it when click on "Show All" button.
?>