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
	editField
	updateField
	confirmCustomer
	confirmContact
	showCustomer
	showContact
	showNote
	showDetail
	noteAdd
	surveyAdd
	saveNote
	saveSurvey
	getContact
	invite

* Revision 0.0456  2007/11/7 14:45:00  modified by solo
* Desc: add function chanspy

* Revision 0.0456  2007/10/31 10:34:00  modified by solo
* Desc: add function hangup

* Revision 0.0456  2007/10/30 8:49:00  modified by solo
* Desc: add function invite

* Revision 0.0456  2007/10/29 21:26:00  modified by solo
* Desc: add function getContact

* Revision 0.045  2007/10/18 14:42:00  modified by solo
* Desc: comment added


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


if ($_SESSION['curuser']['extension'] == '' && $_SESSION['curuser']['usertype'] != 'admin') {
	header("Location: login.php");
}

if (!isset($_SESSION['curid']) && $_SESSION['curid'] =='' ) $_SESSION['curid']=0;

require_once ('include/localization.class.php');
require_once ("include/xajax.inc.php");



$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');


$xajax = new xajax("portal.server.php");
//$xajax->debugOn();
$xajax->waitCursorOff();
$xajax->registerFunction("listenCalls");
$xajax->registerFunction("dial");
$xajax->registerFunction("transfer");
$xajax->registerFunction("attendtransfer");
$xajax->registerFunction("turnback");
$xajax->registerFunction("holdhangup");
$xajax->registerFunction("init");
$xajax->registerFunction("addWithPhoneNumber");
$xajax->registerFunction("monitor");
$xajax->registerFunction("invite");
$xajax->registerFunction("hangup");
$xajax->registerFunction("chanspy");
$xajax->registerFunction("bargeInvite");
$xajax->registerFunction("searchFormSubmit");
$xajax->registerFunction("displayMap");
$xajax->registerFunction("workstart");
$xajax->registerFunction("showWorkoff");
$xajax->registerFunction("workoffcheck");
$xajax->registerFunction("checkworkexten");
$xajax->registerFunction("queuePaused");
$xajax->registerFunction("updateCallresult");
$xajax->registerFunction("setSecondCampaignResult");
$xajax->registerFunction("setCallresult");
$xajax->registerFunction("setKnowledge");
$xajax->registerFunction("getPreDiallist");
$xajax->registerFunction("agentWorkstat");
$xajax->registerFunction("insertIntoDnc");
$xajax->registerFunction("showMyTickets");
$xajax->registerFunction("curTicketDetail");
$xajax->registerFunction("relateByCategoryId");
$xajax->registerFunction("curCustomerDetail");
$xajax->registerFunction("updateCurTicket");
$xajax->registerFunction("relateByCategory");
$xajax->registerFunction("searchTicketsFormSubmit");
$xajax->registerFunction("getMsgInCampaign");
$xajax->registerFunction("queueAgentControl");
$xajax->registerFunction("setAutoPauseQueue");
$xajax->registerFunction("loadingFunction");
$xajax->registerFunction("doneLoadingFunction");
$xajax->registerFunction("SendSmsForm");
$xajax->registerFunction("SendSMS");
$xajax->registerFunction("templateChange");
$xajax->registerFunction("relateByGroup");
$xajax->registerFunction("addNewTicket");
$xajax->registerFunction("saveNewTicket");
$xajax->registerFunction("showHighestAndLastestNote");
$xajax->registerFunction("viewSubordinateTicket");

//2011#6#7
$xajax->registerFunction("requireReasionWhenPause");


if ($config['system']['enable_external_crm'] == false){
	//crm function
	$xajax->registerFunction("showGrid");
	$xajax->registerFunction("add");
	$xajax->registerFunction("edit");
	$xajax->registerFunction("delete");
	$xajax->registerFunction("save");
	$xajax->registerFunction("update");
	$xajax->registerFunction("editField");
	$xajax->registerFunction("updateField");
	$xajax->registerFunction("confirmCustomer");
	$xajax->registerFunction("confirmContact");
	$xajax->registerFunction("showCustomer");
	$xajax->registerFunction("showContact");
	$xajax->registerFunction("showNote");
	$xajax->registerFunction("showDetail");
	$xajax->registerFunction("noteAdd");
	$xajax->registerFunction("surveyList");
	$xajax->registerFunction("surveySave");
	$xajax->registerFunction("saveNote");
	$xajax->registerFunction("getContact");
	$xajax->registerFunction("showCdr");
	$xajax->registerFunction("showDiallist");
	$xajax->registerFunction("addDiallist");
	$xajax->registerFunction("setCampaign");
	$xajax->registerFunction("saveDiallist");
	$xajax->registerFunction("saveDiallistMain");
	$xajax->registerFunction("searchCdrFormSubmit");
	$xajax->registerFunction("searchDiallistFormSubmit");
	$xajax->registerFunction("showRecords");
	$xajax->registerFunction("searchRecordsFormSubmit");
	$xajax->registerFunction("playmonitor");
	$xajax->registerFunction("showRecentCdr");	
	$xajax->registerFunction("showSurvey");
	$xajax->registerFunction("addSchedulerDial");
	$xajax->registerFunction("saveSchedulerDial");
	$xajax->registerFunction("clearPopup");
	$xajax->registerFunction("knowledgechange");
	$xajax->registerFunction("addTicket");
	$xajax->registerFunction("saveTicket");
	$xajax->registerFunction("AllTicketOfMy");
	$xajax->registerFunction("skipDiallist");
}

define("ROWSXPAGE", 5); // Number of rows show it per page.
define("MAXROWSXPAGE", 25);  // Total number of rows show it when click on "Show All" button.

?>