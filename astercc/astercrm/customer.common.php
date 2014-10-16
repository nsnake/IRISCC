<?php
/*******************************************************************************
* customer.common.php
* customer参数信息文件
* customer parameter file

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

* Revision 0.0456  2007/10/25 15:20:00  modified by solo
* Desc: add confirmCustomer,confirmContact,showCustomer function

* Revision 0.045  2007/10/23 21:50:00  modified by solo
* Desc: add surveyAdd,saveSurvey function

* Revision 0.045  2007/10/22 16:45:00  modified by solo
* Desc: delete importCSV,export function

* Revision 0.045  2007/10/18 14:16:00  modified by solo
* Desc: change localization file to astercrm

* Revision 0.045  2007/10/18 13:34:00  modified by solo
* Desc: comment added

* Revision 0.0443  2007/09/29 15:25:00  modified by solo
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


if ( $_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['customer'])) 
	header("Location: portal.php");


require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'astercrm');

$xajax = new xajax("customer.server.php");
//$xajax->debugOn();
$xajax->registerFunction("init");
$xajax->registerFunction("showGrid");
$xajax->registerFunction("add");
$xajax->registerFunction("edit");
$xajax->registerFunction("delete");
$xajax->registerFunction("save");
$xajax->registerFunction("update");
$xajax->registerFunction("editField");
$xajax->registerFunction("updateField");
$xajax->registerFunction("showDetail");
$xajax->registerFunction("showContact");
$xajax->registerFunction("showNote");
$xajax->registerFunction("saveSurvey");
$xajax->registerFunction("surveyAdd");
$xajax->registerFunction("confirmCustomer");
$xajax->registerFunction("confirmContact");
$xajax->registerFunction("showCustomer");
$xajax->registerFunction("addSearchTr");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("importTOExcel");
$xajax->registerFunction("searchCdrFormSubmit");
$xajax->registerFunction("searchDiallistFormSubmit");
$xajax->registerFunction("showCdr");
$xajax->registerFunction("showDiallist");
$xajax->registerFunction("addDiallist");
$xajax->registerFunction("setCampaign");
$xajax->registerFunction("saveDiallist");
$xajax->registerFunction("searchCdrFormSubmit");
$xajax->registerFunction("searchDiallistFormSubmit");
$xajax->registerFunction("showRecords");
$xajax->registerFunction("searchRecordsFormSubmit");
$xajax->registerFunction("deleteByButton");
$xajax->registerFunction("dial");
$xajax->registerFunction("surveyList");
$xajax->registerFunction("showSurvey");
$xajax->registerFunction("surveySave");
$xajax->registerFunction("addSchedulerDial");
$xajax->registerFunction("saveSchedulerDial");
$xajax->registerFunction("noteAdd");
$xajax->registerFunction("saveNote");
$xajax->registerFunction("displayMap");
$xajax->registerFunction("customerLeadsAction");
$xajax->registerFunction("addTicket");
$xajax->registerFunction("relateByCategoryId");
$xajax->registerFunction("relateByCategory");
$xajax->registerFunction("AllTicketOfMy");
$xajax->registerFunction("saveTicket");
$xajax->registerFunction("playmonitor");

//define(ENABLE_CONTACT, $config['system']['enable_contact']);  // Enable contact
define("ROWSXPAGE", 15); // Number of rows show it per page.
define("MAXROWSXPAGE", 50);  // Total number of rows show it when click on "Show All" button.
?>
