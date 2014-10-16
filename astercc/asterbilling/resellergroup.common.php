<?php
/*******************************************************************************
* resellergroup.common.php
* resellergroup参数信息文件

* resellergroup parameter file

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

registed function:
*	call these function by xajax_ + funcionname
*	such as xajax_init()

	showGrid
	add					show resellergroup add form
	save				save resellergroup information
	edit				show resellergroup edit form
	update				update resellergroup information
	delete				delete an resellergroup
	showDetail			show detail information about an resellergroup
						return null for now
	init				init html page

* Revision 0.045  2007/10/17 15:25:00  modified by solo
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

if ($_SESSION['curuser']['usertype'] == 'clid' || $_SESSION['curuser']['usertype'] == ''){
	header("Location: index.php");
}elseif ($_SESSION['curuser']['usertype'] != 'admin') {
	header("Location: systemstatus.php");
}

require_once ("include/xajax.inc.php");

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'accountgroup');

$xajax = new xajax("resellergroup.server.php");

$xajax->registerFunction("showGrid");
$xajax->registerFunction("add");
$xajax->registerFunction("save");
$xajax->registerFunction("edit");
$xajax->registerFunction("update");
$xajax->registerFunction("delete");
$xajax->registerFunction("init");
$xajax->registerFunction("showDetail");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("updateBillingtime");
$xajax->registerFunction("reload");
$xajax->registerFunction("reloadSip");
$xajax->registerFunction("saveTrunk");
$xajax->registerFunction("trunkdetail");
$xajax->registerFunction("delTrunk");

define("ROWSXPAGE", 25); // Number of rows show it per page.
define("MAXROWSXPAGE", 50);  // Total number of rows show it when click on "Show All" button.

?>
