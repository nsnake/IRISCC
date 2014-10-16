<?php
/*******************************************************************************
* import.common.php
* import参数信息文件
* import parameter file
* 功能描述
* Function Desc
		init()  页面load
		selectTable()  选择表
		submitForm()  将csv，xsl格式文件数据插入数据库
		showDivMainRight() 上传后显示csv，xls格式文件的数据

* Revision 0.045  2007/10/18 15:25:00  modified by yunshida
* Desc: page create
* 描述: 页面建立
  
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


if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['import'])) 
	header("Location: portal.php");

require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'import');

$xajax = new xajax("import.server.php");
$xajax->registerFunction("selectTable");
$xajax->registerFunction("init");
$xajax->registerFunction("submitForm");
$xajax->registerFunction("showDivMainRight");
$xajax->registerFunction("setCampaign");
$xajax->registerFunction("deleteFile");


define("ROWSXPAGE", 5); // Number of rows show it per page.
define("MAXROWSXPAGE", 25);  // Total number of rows show it when click on "Show All" button.
?>
