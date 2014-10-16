<?php
/*******************************************************************************
* customer_leads.common.php
* customer_leads参数信息文件
* customer_leads parameter file

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

	init				init html page
	showGrid
	add					show contact add form
	edit				show contact edit form
	delete				delete a contact
	save				save contact information
	update				update contact information
	editField			change table cell to input box
	updateField			update editField when it lost focus
	showDetail			show contact information
	showContact			show contact information 
	showCustomer		show customer information
	showNote			show note information 
	export				download contact data csv file
	surveyAdd			show survey add form
	saveSurvey			save survey result
	confirmCustomer
	confirmContact
	searchFormSubmit    show search message

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


if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['customer_leads'])) 
	header("Location: portal.php");


require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'astercrm');

$xajax = new xajax("customer_leads.server.php");
//$xajax->debugOn();
$xajax->registerFunction("init");
$xajax->registerFunction("showGrid");
$xajax->registerFunction("edit");
$xajax->registerFunction("delete");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("showDetail");
$xajax->registerFunction("update");
$xajax->registerFunction("CustomrLeadEdit");
$xajax->registerFunction("updateCustomerLead");
$xajax->registerFunction("deleteByButton");
$xajax->registerFunction("addNote");
$xajax->registerFunction("saveCustomerLeadNote");
$xajax->registerFunction("showNoteLeads");

//define(ENABLE_CONTACT, $config['system']['enable_contact']);  // Enable contact
define("ROWSXPAGE", 15); // Number of rows show it per page.
define("MAXROWSXPAGE", 50);  // Total number of rows show it when click on "Show All" button.
?>
