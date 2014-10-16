<?php
/*******************************************************************************
* poral.server.php
* agent portal interface

* Function Desc
	agent portal background script

* 功能描述
	座席管理脚本

* Function Desc

	showDetail
	getPrivateDialListNumber
	init
	listenCalls
	incomingCalls
	waitingCalls
	createGrid
	getContact
	monitor
	dial
	transfer
	addWithPhoneNumber
	invite
	chanspy
	searchFormSubmit   多条件搜索，重构显示页面
	knowledgechange
	setKnowledge
	getPreDiallist
	agentWorkstat

* Revision 0.047  2008/2/24 14:45:00  last modified by solo
* Desc: add a new parameter callerid in function monitor
* when monitor, record the callerid and the filename to database

* Revision 0.0456  2007/11/7 14:45:00  last modified by solo
* Desc: add function chanspy

* Revision 0.0456  2007/11/7 11:01:00  last modified by solo
* Desc: fix table width

* Revision 0.0456  2007/11/1 9:48:00  last modified by solo
* Desc: fix bug: when use sendCall method, cant hangup until one party is connected

* Revision 0.0456  2007/10/30 12:47:00  last modified by solo
* Desc: add link for customer and contact

* Revision 0.0456  2007/10/30 8:47:00  last modified by solo
* Desc: add function invite

* Revision 0.0451  2007/10/25 15:21:00  last modified by solo
* Desc: remove confirmCustomer,confirmContact to common file

* Revision 0.0451  2007/10/24 20:37:00  last modified by solo
* Desc: use another dial method: sendCall() to replace Originate

* Revision 0.045  2007/10/18 14:19:00  modified by solo
* Desc: comment added

* Revision 0.045  2007/10/17 20:55:00  modified by solo
* Desc: change callerid match method to like '%callerid'
* 描述: 将电话号码匹配方式修改为前端模糊式检索

* Revision 0.045  2007/10/17 12:55:00  modified by solo
* Desc: fix bugs in search, ordering

********************************************************************************/

require_once ("db_connect.php");
require_once ("portal.common.php");
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('astercrm.server.common.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('portal.grid.inc.php');
require_once ('include/phoogle.php');

/**
*  show customer contact detail
*  @param			noteid		int			noteid
*  @return			object		xajax response object
*/

function showDetail($noteid){
	global $config;
	$objResponse = new xajaxResponse();

	if ($config['system']['portal_display_type'] == "note"){
		$objResponse->addScript("xajax_showContact('$noteid','note');");
		$objResponse->addScript("xajax_showCustomer('$noteid','note');");
	}elseif ($config['system']['portal_display_type'] == "customer"){
		//$objResponse->addScript("xajax_showContact('$noteid','customer');");
		$objResponse->addScript("xajax_showCustomer('$noteid','customer');");
	}

	return $objResponse;
}

/**
*  show phone numbers and dial button if there are phone numbers assigned to this agent
*  in diallist table
*  @param	extension		string			extension
*  @return	object				xajax response object
*/

function getPrivateDialListNumber($extension = null){
	global $locate,$db;
	$objResponse = new xajaxResponse();

	$count = astercrm::getDialNumCountByAgent($extension);
	if ($count == 0){
		$objResponse->addAssign("spanDialList", "innerHTML", $locate->Translate("no_dial_list"));
		$objResponse->addAssign("divWork", "innerHTML", '');
		$objResponse->addAssign("btnWorkStatus","value", "" );
		$objResponse->addAssign("btnWork","value", $locate->Translate("Start work") );
		$objResponse->addAssign("btnWork","disabled", true );
		$_SESSION['curuser']['WorkStatus'] = '';
	} else{
		// add div
		$objResponse->addRemove("spanDialListRecords");
		$objResponse->addRemove("btnGetAPhoneNumber");

		$objResponse->addCreate("spanDialList", "div", "spanDialListRecords");
		$objResponse->addAssign("spanDialListRecords", "innerHTML", $locate->Translate("records_in_dial_list_table").$count);

		// add start campaign button
		$objResponse->addCreateInput("spanDialList", "button", "btnGetAPhoneNumber", "btnGetAPhoneNumber");
		$objResponse->addAssign("btnGetAPhoneNumber", "value", $locate->Translate("get_a_phone_number"));
		$objResponse->addEvent("btnGetAPhoneNumber", "onclick", "btnGetAPhoneNumberOnClick();");
		if($_SESSION['curuser']['WorkStatus'] == ''){
			$objResponse->addAssign("btnWorkStatus","value", "" );
			$objResponse->addAssign("btnWork","value", $locate->Translate("Start work") );
			$objResponse->addAssign("btnWork","disabled", false );
		}
	}

	return $objResponse;
}

/**
*  init page
*  @return	object				xajax response object
*/

function init(){
	global $locate,$config,$db;

	$objResponse = new xajaxResponse();

	$check_interval = 2000;
	if ( is_numeric($config['system']['status_check_interval']) ) $check_interval = $config['system']['status_check_interval'] * 1000;

	$objResponse->addAssign("checkInterval","value", $check_interval );

	if($_SESSION['curuser']['usertype'] == 'agent') {
		$noticeInterval = Customer::getNoticeInterval($_SESSION['curuser']['groupid']);
		$_SESSION['ticketNoticeTime'] = '0000-00-00 00:00:00';
		$_SESSION['noticeInterval'] = $noticeInterval;
	} else {
		unset($_SESSION['ticketNoticeTime']);
		unset($_SESSION['noticeInterval']);
	}
	
	$html = $locate->Translate("welcome").':'.$_SESSION['curuser']['username'].',';
	$html .= $locate->Translate("extension").$_SESSION['curuser']['extension'];
	$objResponse->addAssign("divUserMsg","innerHTML", $html );

	$objResponse->addAssign("username","value", $_SESSION['curuser']['username'] );
	$objResponse->addAssign("extension","value", $_SESSION['curuser']['extension'] );
	$objResponse->addAssign("myevents","innerHTML", $locate->Translate("waiting") );
//	$objResponse->addAssign("status","innerHTML", $locate->Translate("listening") );
	$objResponse->addAssign("extensionStatus","value", 'idle');
	$objResponse->addAssign("processingContent","innerHTML", $locate->Translate("processing_please_wait") );
	
	if($_SESSION['asterisk']['paramdelimiter'] == '|'){
		$objResponse->addAssign("spanAttendtran", "style.display", "none");		
	}

	$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("idle") );
	$objResponse->addAssign("btnMonitorStatus","value", "idle" );
	$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
	if($_SESSION['curuser']['WorkStatus'] == ''){
		$objResponse->addAssign("btnWork","value", $locate->Translate("Start work") );
		$objResponse->addAssign("btnWorkStatus","value", "" );
		$objResponse->addEvent("btnWork", "onclick", "workctrl('start');");
	}else{
		$objResponse->addAssign("btnWork","value", $locate->Translate("Stop work") );
		$objResponse->addAssign("btnWorkStatus","value", "working" );
		$objResponse->addEvent("btnWork", "onclick", "workctrl('stop');");
		$interval = $_SESSION['curuser']['dialinterval'];
		$objResponse->addScript("autoDial('$interval');");
	}
	$objResponse->addAssign("btnMonitor","disabled", true );
	$objResponse->addAssign("divCopyright","innerHTML",Common::generateCopyright($skin));

	if($_SESSION['curuser']['group']['firstring'] == 'caller'){
		$objResponse->addAssign("inviteFlag","innerHTML",'<-');
	}else{
		$objResponse->addAssign("inviteFlag","innerHTML",'->');
	}
	//$objResponse->addAssign("divCopyright","innerHTML",Common::generateCopyright($skin));
	if(strtoupper($config['system']['transfer_pannel']) == 'OFF'){		
		$objResponse->addAssign("spanTransfer", "style.display", "none");		
	}else{
		$objResponse->addAssign("btnTransfer","disabled",true);
	}

	if(strtoupper($config['system']['dial_pannel']) == 'OFF'){		
		$objResponse->addAssign("divInvite", "style.display", "none");
	}

	if(strtoupper($config['system']['monitor_pannel']) == 'OFF'){		
		$objResponse->addAssign("divMonitor", "style.display", "none");		
		$objResponse->addAssign("monitorTitle", "style.display", "none");
	}
	if($_SESSION['curuser']['agent'] != ''){

	}
	if(strtoupper($config['system']['mission_pannel']) == 'OFF' ){
		$objResponse->addAssign("spanDialList", "style.display", "none");
		$objResponse->addAssign("misson", "style.display", "none");
			
	}else{
		$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
	}

	if(strtoupper($config['system']['diallist_pannel']) != 'OFF'){
		$objResponse->addAssign("sptAddDiallist", "style.display", "");	
		$objResponse->addAssign("dpnShow", "value", "1");
		$objResponse->addScript("showDiallist('".$_SESSION['curuser']['extension']."',0,0,5,'','','','formDiallistPannel','','');");

		//$objResponse->addAssign("formDiallistPannel", "style.visibility", "visible");
	}

	foreach ($_SESSION['curuser']['extensions'] as $extension){
		$extension = trim($extension);
		$row = astercrm::getRecordByField('username',$extension,'astercrm_account');		
		$objResponse->addScript("addOption('sltExten','".$row['extension']."','$extension');");
	}
	$speeddial = & Customer::getAllSpeedDialRecords();
	$speednumber['0']['number'] = $_SESSION['curuser']['extension'];
	$speednumber['0']['description'] = $_SESSION['curuser']['username'];
	$n = 1;
	while ($speeddial->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$speednumber[$n]['description'] = $row['description'];
		$speednumber[$n]['number'] = $row['number'];
		$n++;
	}
	$n = count($speednumber);
	for ($i=0;$i<$n;++$i){
		$objResponse->addScript("addOption('iptDestNumber','".$speednumber[$i]['number']."','".$speednumber[$i]['description']."-".$speednumber[$i]['number']."');");
	}
	
	$curmsg = Customer::getTicketInWork();
	$panelHTML = '<a href=? onclick="showMyTickets(\'\',\'agent_tickets\');return false;">'.$locate->Translate("MyTickets")."</a><span id='curticketMsg'>".$curmsg.'</span><br/>';

	if ($config['system']['display_recent_cdr'] == true && $_SESSION['curuser']['usertype'] == "agent"){	

	}else{
		$panelHTML .= '<a href=? onclick="showRecentCdr(\'\',\'recent\');return false;">'.$locate->Translate("recentCDR").'</a><br/>';
	}

	$panelHTML .="<a href=? onclick=\"document.getElementById('dpnShow').value = 1;showDiallist('',0,0,5,'','','','formDiallistPannel','','');return false;\">".$locate->Translate("My Diallist")."</a><br/>";//<span id=\"sptAddDiallist\" style=\"display:none\">
	$panelHTML .="<a href=? id=\"agentWorkstat\" name=\"agentWorkstat\" onclick=\"document.getElementById('awsShow').value = 1;agentWorkstat();return false;\">".$locate->Translate("work stat")."</a><br/>";
	$panelHTML .="<a href=? id=\"knowledge\" name=\"knowledge\" onclick=\"setKnowledge();return false;\">".$locate->Translate("viewknowledge")."</a><br/>";

	$panelHTML .= '<a href=? id="sendSMS" name="sendSMS" onclick="SendSmsForm(\''.$config['system']['enable_sms'].'\');return false;">'.$locate->Translate("Send SMS").'</a><br/>';

	if ( !empty($_SESSION['curuser']['privileges']) || $_SESSION['curuser']['usertype'] == "admin" || $_SESSION['curuser']['usertype'] == "groupadmin" ){
		$panelHTML .= '<a href=# onclick="this.href=\'managerportal.php\'">'.$locate->Translate("manager").'</a><br/>';
	}
	
	$panelHTML .="<a href='login.php'>".$locate->Translate("logout")."</a><br />";

	

	$objResponse->addAssign("divPanel","innerHTML", $panelHTML);

	if ($config['system']['enable_external_crm'] == false){	//use internal crm
		$objResponse->addIncludeScript("js/astercrm.js");
		$objResponse->addIncludeScript("js/ajax.js");
		$objResponse->addIncludeScript("js/ajax-dynamic-list.js");
		$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");
		$objResponse->addAssign("divSearchContact", "style.visibility", "visible");
	} else {
		$objResponse->addIncludeScript("js/extercrm.js");
		
		if($config['system']['open_new_window'] == 'internal'){
			$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'?curid=0" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
			$objResponse->addAssign("divCrm","innerHTML", $mycrm );
		} else if($config['system']['open_new_window'] == 'external'){ 
			//$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'?curid=0" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
			//$objResponse->addAssign("divCrm","innerHTML", $mycrm );
			$mycrm = '<form id="external_crm_form" action="'.$config['system']['external_crm_default_url'].'?curid=0" target="mycrm" method="post"></form>';
			$objResponse->addAssign("external_crm_openNewDiv","innerHTML", $mycrm );
			$objResponse->addScript('document.getElementById("external_crm_form").submit();');
		} else {
			$mycrm = '<form id="external_crm_form" action="'.$config['system']['external_crm_default_url'].'?curid=0" target="mycrm" method="post"></form>';
			$objResponse->addAssign("external_crm_openNewDiv","innerHTML", $mycrm );
			$objResponse->addScript('document.getElementById("external_crm_form").submit();');

			$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'?curid=0" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
			$objResponse->addAssign("divCrm","innerHTML", $mycrm );
		}
		/*if ($config['system']['open_new_window'] == false){
			$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'?curid=0" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
			$objResponse->addAssign("divCrm","innerHTML", $mycrm );
		}else{
			$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'?curid=0" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
			$objResponse->addAssign("divCrm","innerHTML", $mycrm );

			$javascript = "openwindow('".$config['system']['external_crm_default_url']."?curid=0')";
			$objResponse->addScript("document.getElementById('external_crm_form').submit();");
		}*/
	}
	$monitorstatus = astercrm::getRecordByID($_SESSION['curuser']['groupid'],'astercrm_accountgroup'); 
	if ($monitorstatus['monitorforce']) {
		$objResponse->addAssign("chkMonitor","checked", 'true');
		$objResponse->addAssign("chkMonitor","style.visibility", 'hidden');
		$objResponse->addAssign("btnMonitor","disabled", 'true');
	}
	$objResponse->addAssign("clear_popup","value",$monitorstatus['clear_popup']);//for clear popup after ($clear_popup) seconds
	$objResponse->addScript("clearSettimePopup();");
	if($_SESSION['curuser']['group']['allowloginqueue'] == 'yes' && is_array($_SESSION['curuser']['campaign_queue'])){
		//print_r($_SESSION['curuser']['campaign_queue']);exit;
		$objResponse->addScript("getMsgInCampaign();");
	}else{
		$objResponse->addAssign("divGetMsgInCampaignP","style.visibility", 'hidden');
	}

	//if enabled monitor by astercctools
	$configstatus = Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
	if ($configstatus == -2){
		$objResponse->addAlert("fail to read ".$config['system']['astercc_path'].'/astercc.conf');		
	}else{
		if ($asterccConfig['system']['force_record'] == 1 ) {
			//echo $asterccConfig['system']['force_record'];exit;
			$objResponse->addAssign("chkMonitor","checked", false);
			$objResponse->addAssign("chkMonitor","style.visibility", 'hidden');
			$objResponse->addAssign("btnMonitor","disabled", 'true');
		}
	}

	return $objResponse;
}

/**
*	 check if there's new event happen
*
*/
function listenCalls($aFormValues){
	global $config,$locate;

	//print_r($_SESSION['ticketNoticeTime']);exit;
	//print_r($aFormValues);exit;
	
	$objResponse = new xajaxResponse();
//	if($agentData = Customer::getAgentData()){
//		if(strstr($agentData['agent'],'agent')){
//			$objResponse->addAssign("spanDialList", "style.display", "none");
//			$objResponse->addAssign("misson", "style.display", "none");
//		}else{
//			$objResponse->addAssign("spanDialList", "style.display", "");
//			$objResponse->addAssign("misson", "style.display", "");
//		}
////		print_r($agentData);exit;
//		if($aFormValues['breakStatus'] == -1){
//			$span = '<input type="button" value="" name="btnPause" id="btnPause" onclick="queuePaused();" >';
//			$objResponse->addAssign("spnPause","innerHTML", $span );
//		}
//		if($agentData['cretime'] > $aFormValues['clkPauseTime']){
//			$objResponse->addAssign("agentData","innerHTML", $agentData['data'] );
//			if($agentData['agent_status'] != 'paused'){
//				$objResponse->addAssign("btnPause","value", $locate->Translate("Break") );
//				$objResponse->addAssign("breakStatus","value", 0);
//			}else{
//				$objResponse->addAssign("btnPause","value", $locate->Translate("Continue") );
//				$objResponse->addAssign("breakStatus","value", 1);
//			}
//		}
//	}else{
//		if($_SESSION['curuser']['agent'] == '' ){
//			$objResponse->addAssign("agentData","innerHTML", '');
//			$objResponse->addAssign("spnPause","innerHTML", '' );
//			$objResponse->addAssign("breakStatus","value", -1);
//		}
//	}

	
	//根据后台 astercrm_accountgroup里设置的参数 notice_interval 来判断多少分钟的间隔执行 ticket 的提示
	if($_SESSION['curuser']['usertype'] == 'agent') {
		$noticeInterval = $_SESSION['noticeInterval'];
		//print_r($_SESSION['ticketNoticeTime'].' - '.date("Y-m-d H:i:s",strtotime($_SESSION['ticketNoticeTime']." + $noticeInterval minutes")));exit;
		//print_r($noticeInterval > 0 && (strtotime($_SESSION['ticketNoticeTime']." + $noticeInterval minutes") <= strtotime(date("Y-m-d"))));exit;
		
		$noticeArray = array();
		if($noticeInterval > 0 && (strtotime($_SESSION['ticketNoticeTime']." + $noticeInterval minutes") <= strtotime(date("Y-m-d H:i:s")))) {
			$noticeArray = Customer::ticketNoticeValid();

			//更新右上角的mytickets处的数值
			$curTicketmsg = Customer::getTicketInWork();
			$objResponse->addAssign("curticketMsg", "innerHTML", $curTicketmsg);
		}
		
		if(!empty($noticeArray)) {
			$objResponse->addAssign("noticeTicketMsgDiv","innerHTML",str_replace('%d',count($noticeArray),$locate->Translate('you have new tickets')));
			$objResponse->addScript('getTicketNoticeMsg();');

			$_SESSION['ticketNoticeTime'] = date("Y-m-d H:i:s");//更新session里的提醒时间
		}/* else {
			$objResponse->addAssign("noticeTicketMsgDiv","innerHTML",'');
			$objResponse->addScript('closeTicketNotice();');
		}*/
	}
	
	//根据后台设置的update_online_interval 判断多长时间进行更新astecrm_account表里的last_update_time字段值
	if(isset($_SESSION['curuser']['update_online_interval']) && $_SESSION['curuser']['update_online_interval'] != ''){
		if((strtotime(date("Y-m-d H:i:s"))-strtotime($_SESSION['curuser']['update_online_interval'])) >= ($config['system']['update_online_interval']*60)){
			astercrm::updateAgentOnlineTime('update',date("Y-m-d H:i:s"),$_SESSION['curuser']['accountid']);
		}
	}
	
	if($aFormValues['dpnShow'] > 0){ //for refresh diallist pannel
		$lastDiallistId = Customer::getLastOwnDiallistId();
		if($lastDiallistId == '') $lastDiallistId = 1;
		if( $aFormValues['dpnShow'] != $lastDiallistId ){
			$objResponse->addAssign("dpnShow","value", $lastDiallistId );
			$objResponse->addScript("showDiallist('".$_SESSION['curuser']['extension']."',0,0,5,'','','','formDiallistPannel','','');");
		}
	}

	if ($aFormValues['uniqueid'] == ''){
		$objResponse->addAssign("btnDial","disabled",false);
		$objResponse->loadXML(waitingCalls($aFormValues));
	} else{
		$objResponse->addAssign("btnDial","disabled",true);
		$objResponse->loadXML(incomingCalls($aFormValues));
	}

	//set time intervals of update events
	//$check_interval = 2000;
	//if ( is_numeric($config['system']['status_check_interval']) ) $check_interval = $config['system']['status_check_interval'] * 1000;

	//$objResponse->addScript('setTimeout("updateEvents()", '.$check_interval.');');
	return $objResponse;
}

/**
*	 transfer call
*/
function transfer($aFormValues){
	global $config,$db;
	//print_r($aFormValues);exit;
	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	$objResponse = new xajaxResponse();
	
	if ($aFormValues['iptTtansfer'] != ''){
		$action = $aFormValues['iptTtansfer'];
	}elseif ($aFormValues['sltExten'] != ''){
		$action = $aFormValues['sltExten'];
	}else{
		return $objResponse;
	}

	if ($aFormValues['direction'] == 'in'){
		if($aFormValues['attendtran'] == 'yes'){
			if(strstr($aFormValues['calleeChannel'],'agent/')){
				$query = "SELECT * FROM curcdr WHERE id = '".$aFormValues['curid']."'";
				$agentrow = $db->getRow($query);
				//print_r($agentrow);
				//print_r($aFormValues);exit;
				$isagent = 1;
				$aFormValues['calleeChannel'] = $agentrow['agentchan'];
				$query = "UPDATE curcdr SET starttime=now() WHERE srcchan = '".$agentrow['agentchan']."'";
				$db->query($query);
				#print_r($query);exit;
			}

			$sql = "INSERT INTO hold_channel SET number='".$aFormValues['callerid']."',channel='".$aFormValues['callerChannel']."',uniqueid='".$aFormValues['uniqueid']."',status='hold',agentchan='".$aFormValues['calleeChannel']."',direction='in',accountid='".$_SESSION['curuser']['accountid']."',cretime=now()";
			$db->query($sql);
			
			#print_r($res);exit;
			if($isagent){
				$res = $myAsterisk->Redirect($aFormValues['callerChannel'],'','s','astercc-onhold',1);

				#$res1 = $myAsterisk->sendCall("Local/$action@".$config['system']['outcontext'],NULL,NULL,1,'Bridge',$aFormValues['calleeChannel'],30,$_SESSION['curuser']['extension'],NULL,$_SESSION['curuser']['accountcode']);
			}else{
				$res = $myAsterisk->Redirect($aFormValues['callerChannel'],$aFormValues['calleeChannel'],'s','astercc-onhold',1);				
			}
			$res1=$myAsterisk->Redirect($aFormValues['calleeChannel'],'',$action,$config['system']['outcontext'],1);
			#print_r($res);print_r($res1);exit;
		}else{
			$res= $myAsterisk->Redirect($aFormValues['callerChannel'],'',$action,$config['system']['outcontext'],1);
			
		}
		
	}else{
		if($aFormValues['attendtran'] == 'yes'){
			if(strstr($aFormValues['callerChannel'],'agent/')){
				$query = "SELECT * FROM curcdr WHERE id = '".$aFormValues['curid']."'";
				$agentrow = $db->getRow($query);
				//print_r($agentrow);
				//print_r($aFormValues);exit;
				$isagent = 1;
				$aFormValues['callerChannel'] = $agentrow['agentchan'];
				$query = "UPDATE curcdr SET starttime=now() WHERE srcchan = '".$agentrow['agentchan']."'";
				$db->query($query);

				//print_r($query);exit;
			}

			#echo $aFormValues['callerChannel'],$action,$config['system']['outcontext'];exit;

			$sql = "INSERT INTO hold_channel SET number='".$aFormValues['callerid']."',channel='".$aFormValues['calleeChannel']."',uniqueid='".$aFormValues['uniqueid']."',status='hold',agentchan='".$aFormValues['callerChannel']."',direction='out',accountid='".$_SESSION['curuser']['accountid']."',cretime=now()";
			$db->query($sql);

			
			if($isagent){
				$res = $myAsterisk->Redirect($aFormValues['calleeChannel'],'','s','astercc-onhold',1);

				#$res1 = $myAsterisk->sendCall("Local/$action@".$config['system']['outcontext'],NULL,NULL,1,'Bridge',$aFormValues['callerChannel'],30,$_SESSION['curuser']['extension'],NULL,$_SESSION['curuser']['accountcode']);
			}else{
				$res = $myAsterisk->Redirect($aFormValues['calleeChannel'],$aFormValues['callerChannel'],'s','astercc-onhold',1);				
			}

			$res1= $myAsterisk->Redirect($aFormValues['callerChannel'],$aFormValues['callerChannel'],$action,$config['system']['outcontext'],1);

			#print_r($res);print_r($res1);exit;
			
		}else{
			$myAsterisk->Redirect($aFormValues['calleeChannel'],'',$action,$config['system']['outcontext'],1);
		}
	}
	//$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	$objResponse->addAssign("curid", "value", 0);
	return $objResponse;
}

/**
*	attend transfer call
*/
function attendtransfer($channel,$consultchan){
	global $config,$db;
	//echo $channel,$consultchan;exit;
	$objResponse = new xajaxResponse();
	//$channel = split('-',$channel);
	//$consultchan = split('-',$consultchan);
	$consultchan = '';
	if($channel == ''){
		$sql = "SELECT * FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."' ORDER BY id DESC LIMIT 1";
		$hold = $db->getrow($sql);
		$channel = $hold['channel'];
	}

	if($consultchan == ''){
		$curcall = asterEvent::checkNewCall(0,$_SESSION['curuser']['extension'],$_SESSION['curuser']['channel'],$_SESSION['curuser']['agent']);
		$consultchan = $curcall['calleeChannel'];
	}
	
	$sql="DELETE FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."'";
	$db->query($sql);
	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	$myAsterisk->Redirect($channel,'',$consultchan,'astercc-attend',1);
	return $objResponse;
	//$myAsterisk->sendCall($channel['0'],NULL,NULL,1,'Bridge',$consultchan,30,$_SESSION['curuser']['extension'],NULL,$_SESSION['curuser']['accountcode']);
}

function holdhangup($channel,$consultchan){
	global $config,$locate,$db;
	$objResponse = new xajaxResponse();
	//$channel = split('-',$channel);
	//$consultchan = split('-',$consultchan);
	
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();
	if (trim($channel) == '')
		return $objResponse;
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addALert("action Huangup failed");
		return $objResponse;
	}
	$sql="DELETE FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."'";
	$db->query($sql);

	$myAsterisk->Hangup($channel);
	return $objResponse;
}

function turnback($channel,$agentchan){
	global $config,$db;
//	print $channel.'||'.$agentchan;exit;
	$objResponse = new xajaxResponse();
	$sql="SELECT * FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."' ORDER BY id DESC LIMIT 1";
	$hold = $db->getRow($sql);
	$sql="DELETE FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."'";
	$db->query($sql);
	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if($agentchan != ''){
		if($hold['direction'] == 'in'){
			$myAsterisk->Redirect($channel,'',$agentchan,'astercc-attend',1);
		}else{
			$myAsterisk->Redirect($agentchan,'',$channel,'astercc-attend',1);
		}
	}else{
		$context = $_SESSION['curuser']['group']['outcotext'];
		if($context == ''){
			$context=$config['system']['outcontext'];
		}
		$strChannel = "Local/".$_SESSION['curuser']['extension']."@".$context."/n";
		$myAsterisk->sendCall($strChannel,NULL,NULL,1,'Bridge',$channel,30000,$hold['number'],NULL,$_SESSION['curuser']['accountcode']);
	}
	return $objResponse;
}


/*
	add a new parameter callerid		by solo2008/2/24
	when monitor, record the callerid and the filename to database
*/
function monitor($channel,$callerid,$action = 'start',$uniqueid = '',$curid,$direction){
	//echo $callerid;exit;
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();

	if (!$res){
		$objResponse->addAlert($locate->Translate("failed when connect to AMI"));
		return $objResponse;
	}

	if ($action == 'start'){
		//$filename = str_replace("/","-",$channel);
		if($direction == 'IN'){
			$filename = $callerid.'-'.$_SESSION['curuser']['extension'];
		}else{
			$filename = $_SESSION['curuser']['extension'].'-'.$callerid;
		}
		$filename = $config['asterisk']['monitorpath'].date('Y/m/d/H/').$filename;
		$filename .= '.'.time();
		$format = $config['asterisk']['monitorformat'];
		$mix = false;
		$res = $myAsterisk->Monitor($channel,$filename,$format,$mix);

		if ($res['Response'] == 'Error'){
			return $objResponse;
		}
		// 录音信息保存到数据库
		astercrm::insertNewMonitor($callerid,$filename,$uniqueid,$format,$curid);
		$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("recording") );
		$objResponse->addAssign("btnMonitorStatus","value", "recording" );

		$objResponse->addAssign("btnMonitor","value", $locate->Translate("stop_record") );
	}else{
		$myAsterisk->StopMontor($channel);

		$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("idle") );
		$objResponse->addAssign("btnMonitorStatus","value", "idle" );

		$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
	}

	//$objResponse->addAssign("btnMonitor","disabled", false );
	return $objResponse;
}

function waitingCalls($myValue){
	global $db,$config,$locate;
	$objResponse = new xajaxResponse();
	$curid = trim($myValue['curid']);
	
// to improve system efficiency
/**************************
**************************/
	if(strtoupper($config['system']['extension_pannel']) == 'ON'){
		$phone_html = asterEvent::checkExtensionStatus($curid);
		$objResponse->addAssign("divExtension","innerHTML", $phone_html );
		$objResponse->addScript("menuFix();");
	}else{
		$objResponse->addAssign("divExtension","style.visibility", 'hidden');
	}
	
	//	modified 2007/10/30 by solo
	//  start
	//print_r($_SESSION);exit;
	//if ($_SESSION['curuser']['channel'] == '')
		$call = asterEvent::checkNewCall($curid,$_SESSION['curuser']['extension'],$_SESSION['curuser']['channel'],$_SESSION['curuser']['agent']);
	//else
	//	$call = asterEvent::checkNewCall($curid,$_SESSION['curuser']['channel']);
	//  end
	//print_r($call['callerid']);exit;
	if ($call['status'] == ''){
		
		if($call['hold']['number'] != ''){
			//print_r($call);exit;
			$curcallerid = $call['hold']['number'];
			$objResponse->addAssign("divHolding","innerHTML",'<a href="###" onclick="getContact('.$call['hold']['number'].');">['.$call['hold']['number'].']</a>&nbsp;&nbsp;<a onclick="xajax_turnback(\''.$call['hold']['channel'].'\',\''.$myValue['callerChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Turn back").'</font></a>&nbsp;&nbsp;&nbsp;<a onclick="xajax_holdhangup(\''.$call['hold']['channel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Hangup").'</font></a>');
			//return $objResponse;
		}else{
			$objResponse->addAssign("divHolding","innerHTML",'');
		}
		$title	= $locate->Translate("waiting");
		$status	= 'idle';
		//$call['curid'] = $curid;
		$direction	= '';
		$info	= $locate->Translate("stand_by");
		///$objResponse->addAssign("dndlist_campaignid","value","0");
		
	} elseif ($call['status'] == 'incoming'){	//incoming calls here
		if($config['system']['enable_socket'] == 'yes' && $_SESSION['socket_url_flag'] == 'yes'){
			//固定端口
			$service_port = $config['system']['fix_port'];
			//socketURL
			$socket_url = $config['system']['socket_url'];
			if($service_port != '' && $socket_url != '') {
				//本地
				$address = Customer::get_real_ip();
				//创建 TCP/IP socket
				$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
				
				if ($socket === FALSE) {
					$_SESSION['socket_url_flag'] = 'no';

					$objResponse->addAlert("socket创建失败原因: " . socket_strerror($socket) . " \n 请检查当前系统的socket,并且重新登录系统 \n 坐席的ip是 ".$address);
					return $objResponse;
				}
				
				$result = socket_connect($socket, $address, $service_port);
				if ($result === FALSE) {
					$_SESSION['socket_url_flag'] = 'no';
					$objResponse->addAlert("SOCKET连接失败原因: ($result) " . socket_strerror($result) . " \n 请检查当前系统的socket,并且重新登录系统 \n 坐席的ip是 ".$address);
					return $objResponse;
				}

				$socket_url = str_replace('%callerid%',$call['callerid'],$socket_url);

				socket_write($socket, $socket_url, strlen($socket_url));
				#socket_write ($socket, "\r\n", strlen ("\r\n"));
				socket_close($socket);
			}
			
		}
		
//		if(strstr($call['calleeChannel'],'agent')){
//			$objResponse->addAssign("attendtran","disabled",true);
//		}else{
//			$objResponse->addAssign("attendtran","disabled",false);
//		}
		$objResponse->addScript("clearSettimePopup();");
		$title	= $call['callerid'];
		$stauts	= 'ringing';
		$direction	= 'in';

		if(!empty($call['srcname']) && $call['srcname'] != 'unknown' && $call['srcname'] != '<unknown>') {
			$info	= $locate->Translate("incoming"). ' ' . $call['callerid'] . ' (' .$call['srcname']. ')&nbsp;&nbsp;';
		} else {
			$info	= $locate->Translate("incoming"). ' ' . $call['callerid'];
		}
		
		$result = asterCrm::checkDialedlistCall($call['callerid']);
		
		$dialedlistid = $result['id'];
		$campaign_id = $result['campaignid'];
		if($campaign_id != '') {
			$objResponse->addAssign("dndlist_campaignid","value",$campaign_id);
		} else {
			$objResponse->addAssign("dndlist_campaignid","value","0");
		}
		
		if($myValue['callResultStatus'] == '' && $call['callerid'] != ''){
				if($dialedlistid){
					$divCallresult = Customer::getCampaignResultHtml($dialedlistid,'NOANSWER');
					$objResponse->addAssign("divCallresult", "style.display", "");
					$objResponse->addAssign("divCallresult", "innerHTML", $divCallresult);
					$objResponse->addAssign("dialedlistid","value", $dialedlistid );
				}else{
					$objResponse->addAssign("dialedlistid","value", 0 );
					$objResponse->addAssign("divCallresult", "style.display", "none");
				}
				$objResponse->addAssign("callResultStatus","value", '1' );
		}
		if($dialedlistid){
			if($config['diallist']['popup_diallist'] == 1){
				$dialistHtml = Customer::formDiallist($dialedlistid);
				$objResponse->addAssign('formDiallistPopup','innerHTML',$dialistHtml);
				$objResponse->addAssign('formDiallistPopup',"style.visibility", "visible");
			}
		}
		if($call['didnumber'] != ''){
			$didinfo = $locate->Translate("Callee id")."&nbsp;:&nbsp;<b>".$call['didnumber']."</b>";
			$objResponse->addAssign('divDIDinfo','innerHTML',$didinfo);
		}
		
		$trunk = split("-",$call['callerChannel']);
		//print_r($trunk);exit;
		
		$info	= $info. ' channel: ' . $trunk[0];
		// get trunk info
		$mytrunk = astercrm::getTrunkinfo($trunk[0],$call['didnumber']);
		if ($mytrunk){
			$infomsg = "<strong>".$mytrunk['trunkname']."</strong><br>";
			$infomsg .= astercrm::db2html($mytrunk['trunknote']);
			$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
			$objResponse->addAssign('trunkinfo_number',"innerHTML",$mytrunk['trunk_number']);
		}else{
			$infomsg = $locate->Translate("no information get for trunk").": ".$trunk[0];
			$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
			$objResponse->addAssign('trunkinfo_number',"innerHTML");
		}
			
		if($config['system']['enable_sms'] == 'callerid' && $call['callerid'] != '') {
			$objResponse->addScript('xajax_SendSmsForm("callerid",'.$call['callerid'].')');
		} else if($config['system']['enable_sms'] == 'trunk_number' && $mytrunk['trunk_number'] != '') {
			$objResponse->addScript('xajax_SendSmsForm("trunk_number",'.$mytrunk['trunk_number'].')');
		} else if($config['system']['enable_sms'] == 'campaign_number' && $campaign_id != ''){
			$objResponse->addScript('xajax_SendSmsForm("campaign_number",'.$campaign_id.')');
		}
		
		$objResponse->addAssign("iptCallerid","value", $call['callerid'] );
		$objResponse->addAssign("btnHangup","disabled", false );

		if($call['queue'] != ''){
			foreach($_SESSION['curuser']['campaign_queue'] as $row){
				//print_r($row);exit;
				if($row['queuename'] == $call['queue']){
					$objResponse->addAssign("campaignDiv-".$row['id'],"style.background",'red');
				}					
			}
		}

		if ($config['system']['pop_up_when_dial_in']){
			if (strlen($call['callerid']) > $config['system']['phone_number_length'] && $call['callerid'] != '<unknown>'){
				// $config['system']['allow_popup_when_aleady_popup'] 客户弹屏存在是否重新弹出客户窗口
				if ($myValue['popup'] == 'yes' || $config['system']['enable_external_crm'] || $config['system']['allow_popup_when_already_popup']){
					if ($config['system']['enable_external_crm'] == false){
							$objResponse->loadXML(getContact($call['callerid'],0,$campaign_id,0,$call['srcname']));
							if ( $config['system']['browser_maximize_when_pop_up'] == true ){
								$objResponse->addScript('maximizeWin();');
							}
					}else{
						//print_r($call);exit;
						//use external link
						$myurl = $config['system']['external_crm_url'];
						
						$method = "dial_in";
						$callerid = $call['callerid'];
						$calleeid = $_SESSION['curuser']['extension'];
						$uniqueid = $call['uniqueid'];
						$calldate = $call['calldate'];
						$didnumber = $call['didnumber'];

						$cur_srcname = Customer::getSrcnameByCurid($call['curid']);

						$curHtml = '<form id="external_crm_form" action="'.$myurl.'?curid='.$call['curid'].'&srcname='.$cur_srcname.'" target="_blank" method="post">
								<input type="hidden" name="callerid" value="'.$callerid.'" />
								<input type="hidden" name="calleeid" value="'.$calleeid.'" />
								<input type="hidden" name="method" value="'.$method.'" />
								<input type="hidden" name="uniqueid" value="'.$uniqueid.'" />
								<input type="hidden" name="calldate" value="'.$calldate.'" />
								<input type="hidden" name="didnumber" value="'.$didnumber.'" />
							';

						
						if($config['system']['external_url_parm'] != ''){
							if ($config['system']['detail_level'] == 'all')
								$customerid = astercrm::getCustomerByCallerid($call['callerid']);
							else
								$customerid =	astercrm::getCustomerByCallerid($call['callerid'],$_SESSION['curuser']['groupid']);
							
							if($customerid != ''){
								$customer = astercrm::getCustomerByID($customerid,"customer");
								$url_parm = split(',',$config['system']['external_url_parm']);

								foreach($url_parm as $parm){
									if($parm != '' ){
										$curHtml .= '<input type="hidden" name="'.$parm.'" value="'.urlencode($customer[$parm]).'" />';
									}
								}
							}

						}
						$curHtml .="</form>";

						if ($config['system']['open_new_window'] == 'internal'){
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
							$curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
						} else if ($config['system']['open_new_window'] == 'external'){
							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", "" );
						} else {
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );

							$internal_curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$external_curHtml = preg_replace("/external_crm_form/","external_crm_openNew_form",$curHtml);
							$objResponse->addAssign("external_crmDiv","innerHTML", $internal_curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );

							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", $external_curHtml );
							$objResponse->addScript("document.getElementById('external_crm_openNew_form').submit();");
							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", "" );
							
						}
						/*if ($config['system']['open_new_window'] == false){
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
							$curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
						}else{
							
							//print_r($curHtml);exit;
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
							//$javascript = "openwindow('".$myurl."')";
							//$objResponse->addScript($javascript);
						}*/
					}
				}
			}else{

			}
		}
	} elseif ($call['status'] == 'dialout'){	//dailing out here

//		if(strstr($call['callerChannel'],'agent')){
//			$objResponse->addAssign("attendtran","disabled",true);
//		}else{
//			$objResponse->addAssign("attendtran","disabled",false);
//		}
		
		$objResponse->addScript("clearSettimePopup();");
		$title	= $call['callerid'];
		$status	= 'dialing';
		$direction	= 'out';
		$info	= $locate->Translate("dial_out"). ' '. $call['callerid'];
		if($call['hold']['number'] != ''){
			//print_r($call);exit;
			$call['callerid'] = $call['hold']['number'];
			$objResponse->addAssign("divHolding","innerHTML",'<a href="###" onclick="getContact('.$call['hold']['number'].');">['.$call['hold']['number'].']</a>&nbsp;&nbsp;<a onclick="xajax_turnback(\''.$call['hold']['channel'].'\',\''.$myValue['callerChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Turn back").'</font></a>&nbsp;&nbsp;&nbsp;');
			//<a onclick="xajax_holdhangup(\''.$call['hold']['channel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Hangup").'</font></a>
		}else{
			$objResponse->addAssign("divHolding","innerHTML",'');
		}
		if($myValue['callResultStatus'] == '' && $call['callerid'] != ''){
				$result = asterCrm::checkDialedlistCall($call['callerid']);
				//print_r($result);exit;
				$dialedlistid = $result['id'];
				$campaign_id = $result['campaignid'];
				if($campaign_id != '') {
					$objResponse->addAssign("dndlist_campaignid","value",$campaign_id);
				} else {
					$objResponse->addAssign("dndlist_campaignid","value","0");
				}
				if($dialedlistid){
					$divCallresult = Customer::getCampaignResultHtml($dialedlistid,'NOANSWER');
					//echo $divCallresult;exit;
					$objResponse->addAssign("divCallresult", "style.display", "");
					$objResponse->addAssign("divCallresult", "innerHTML", $divCallresult);
					$objResponse->addAssign("dialedlistid","value", $dialedlistid );
				}else{
					$objResponse->addAssign("dialedlistid","value", 0 );
					$objResponse->addAssign("divCallresult", "style.display", "none");
				}
				$objResponse->addAssign("callResultStatus","value", '1' );

				//print_r($config['diallist']);exit;
				if($dialedlistid){
					if($config['diallist']['popup_diallist'] == 1){
						$dialistHtml = Customer::formDiallist($dialedlistid);
						$objResponse->addAssign('formDiallistPopup','innerHTML',$dialistHtml);
						$objResponse->addAssign('formDiallistPopup',"style.visibility", "visible");
					}
				}
		}
		$objResponse->addAssign("iptCallerid","value", $call['callerid'] );
		$objResponse->addAssign("btnHangup","disabled", false );

		if($call['didnumber'] != ''){
			$didinfo = $locate->Translate("Callee id")."&nbsp;:&nbsp;".$call['didnumber'];
			$objResponse->addAssign('divDIDinfo','innerHTML',$didinfo);
		}

		if ($config['system']['pop_up_when_dial_out']){
			if (strlen($call['callerid']) > $config['system']['phone_number_length']){
				
				if ($myValue['popup'] == 'yes' || $config['system']['allow_popup_when_already_popup']){
					if ($config['system']['enable_external_crm'] == false ){
							$objResponse->loadXML(getContact($call['callerid'],0,$campaign_id,0,$call['srcname']));
							if ( $config['system']['browser_maximize_when_pop_up'] == true ){
								$objResponse->addScript('maximizeWin();');
							}
					}else{
						//print_r($call);exit;
						//use external link
						$myurl = $config['system']['external_crm_url'];

						$method = "dial_out";
						$callerid = $_SESSION['curuser']['extension'];
						$calleeid = $call['callerid'];
						$uniqueid = $call['uniqueid'];
						$calldate = $call['calldate'];
						$didnumber = $call['didnumber'];
						
						$cur_srcname = Customer::getSrcnameByCurid($call['curid']);
						
						$curHtml = '<form id="external_crm_form" action="'.$myurl.'?curid='.$call['curid'].'&srcname='.$cur_srcname.'" target="_blank" method="post">
								<input type="hidden" name="callerid" value="'.$callerid.'" />
								<input type="hidden" name="calleeid" value="'.$calleeid.'" />
								<input type="hidden" name="method" value="'.$method.'" />
								<input type="hidden" name="uniqueid" value="'.$uniqueid.'" />
								<input type="hidden" name="calldate" value="'.$calldate.'" />
								<input type="hidden" name="didnumber" value="'.$didnumber.'" />
							</form>';
						if ($config['system']['open_new_window'] == 'internal'){
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
							$curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
						} else if ($config['system']['open_new_window'] == 'external'){
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
							//$javascript = "openwindow('".$myurl."')";
							//$objResponse->addScript($javascript);
						} else {
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
							$internal_curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$external_curHtml = preg_replace("/external_crm_form/","external_crm_openNew_form",$curHtml);
							
							$objResponse->addAssign("external_crmDiv","innerHTML", $internal_curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );

							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", $external_curHtml );
							$objResponse->addScript("document.getElementById('external_crm_openNew_form').submit();");
							$objResponse->addAssign("external_crm_openNewDiv","innerHTML", "" );
						}
						/*if ($config['system']['open_new_window'] == false){
							$mycrm = '<iframe id="mycrm" name="mycrm" width="100%"  frameBorder=0 scrolling=auto height="600"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
							$curHtml = preg_replace("/\_blank/","mycrm",$curHtml);
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
						} else {
							$objResponse->addAssign("external_crmDiv","innerHTML", $curHtml );
							$objResponse->addScript("document.getElementById('external_crm_form').submit();");
							$objResponse->addAssign("external_crmDiv","innerHTML", "" );
							//$javascript = "openwindow('".$myurl."')";
							//$objResponse->addScript($javascript);
						}*/
					}
				}
			}
		}
	}
	
//	$objResponse->addScript('document.title='.$title.';');
//	$objResponse->addAssign("status","innerHTML", $stauts );
	$objResponse->addAssign("extensionStatus","value", $stauts );
	//echo $call['uniqueid'];exit;
	$objResponse->addAssign("uniqueid","value", $call['uniqueid'] );
	$objResponse->addAssign("callerid","value", $call['callerid'] );	
	$objResponse->addAssign("callerChannel","value", $call['callerChannel'] );
	$objResponse->addAssign("calleeChannel","value", $call['calleeChannel'] );
	$objResponse->addAssign("curid","value", $call['curid'] );
	$objResponse->addAssign("direction","value", $direction );
	$objResponse->addAssign("myevents","innerHTML", $info);

	return $objResponse;
}


//check if call (uniqueid) hangup
function incomingCalls($myValue){
	global $db,$locate,$config;
	$objResponse = new xajaxResponse();
	//print_r($myValue);exit;
	if ($myValue['direction'] != ''){
		$call = asterEvent::checkCallStatus($myValue['curid'],$myValue['uniqueid']);
		#print_r($call);exit;
		if ($call['status'] ==''){
			if($call['hold']['number'] != ''){
			//print_r($myValue);exit;
				$curcallerid = $call['hold']['number'];
				$objResponse->addAssign("divHolding","innerHTML",'<a href="###" onclick="getContact('.$call['hold']['number'].');">['.$call['hold']['number'].']</a>&nbsp;&nbsp;<a onclick="xajax_turnback(\''.$call['hold']['channel'].'\',\''.$myValue['callerChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Turn back").'</font></a>&nbsp;&nbsp;&nbsp;');
				//<a onclick="xajax_holdhangup(\''.$call['hold']['channel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Hangup").'</font></a>
			}else{
				$curcallerid = $myValue['callerid'];
				$objResponse->addAssign("divHolding","innerHTML",'');
			}
			return $objResponse;
		} elseif ($call['status'] =='link'){
			$objResponse->addAssign("btnDial","disabled",true);
			$objResponse->addScript("clearSettimePopup();");
			
			if ($myValue['direction'] == 'in' && $myValue['trunkinfoStatus'] == 0){
				if($call['didnumber'] != ''){
					$didinfo = $locate->Translate("Callee id")."&nbsp;:&nbsp;<b>".$call['didnumber']."</b>";
					$objResponse->addAssign('divDIDinfo','innerHTML',$didinfo);
				}
				
				$trunk = split("-",$call['callerChannel']);
				//print_r($trunk);exit;
				
				$info	= $info. ' channel: ' . $trunk[0];
				// get trunk info
				$mytrunk = astercrm::getTrunkinfo($trunk[0],$call['didnumber']);
				if ($mytrunk){
					$infomsg = "<strong>".$mytrunk['trunkname']."</strong><br>";
					$infomsg .= astercrm::db2html($mytrunk['trunknote']);
					$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
					$objResponse->addAssign('trunkinfo_number',"innerHTML",$mytrunk['trunk_number']);
				}else{
					$infomsg = $locate->Translate("no information get for trunk").": ".$trunk[0];
					$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
					$objResponse->addAssign('trunkinfo_number',"innerHTML");
				}
				$objResponse->addAssign('trunkinfoStatus',"value",'1');

				if($config['system']['enable_sms'] == 'callerid' && $myValue['callerid'] != '') {
					$objResponse->addScript('xajax_SendSmsForm("callerid",'.$myValue['callerid'].')');
				} else if($config['system']['enable_sms'] == 'trunk_number' && $mytrunk['trunk_number'] != '') {
					$objResponse->addScript('xajax_SendSmsForm("trunk_number",'.$mytrunk['trunk_number'].')');
				} else if($config['system']['enable_sms'] == 'campaign_number' && $result['campaignid'] != ''){
					$objResponse->addScript('xajax_SendSmsForm("campaign_number",'.$result['campaignid'].')');
				}
			}

			if($myValue['callResultStatus'] != '2'){
				$result = asterCrm::checkDialedlistCall($myValue['callerid']);
				//print_r($result);exit;
				$dialedlistid = $result['id'];//$dialedlistid = 
				$campaign_id = $result['campaignid'];
				if($campaign_id != '') {
					$objResponse->addAssign("dndlist_campaignid","value",$campaign_id);
				} else {
					$objResponse->addAssign("dndlist_campaignid","value","0");
				}
				if($dialedlistid){
					$divCallresult = Customer::getCampaignResultHtml($dialedlistid,'ANSWERED');
					//echo $divCallresult;exit;
					$objResponse->addAssign("divCallresult", "style.display", "");
					$objResponse->addAssign("divCallresult", "innerHTML", $divCallresult);
					$objResponse->addAssign("dialedlistid","value", $dialedlistid );
				}else{
					$objResponse->addAssign("dialedlistid","value", 0 );
				}
				$objResponse->addAssign("callResultStatus","value", '2' );
			}

			if ($myValue['extensionStatus'] == 'link')	 //already get link event
				return $objResponse;
//			if ($call['callerChannel'] == '' or $call['calleeChannel'] == '')
//				return $objResponse;
			$status	= "link";

			if($call['hold']['number'] != ''){
			//print_r($myValue);exit;
				$curcallerid = $call['consultnum'];
				$objResponse->addAssign("divHolding","innerHTML",'<a href="###" onclick="getContact('.$call['hold']['number'].');">['.$call['hold']['number'].']</a>&nbsp;&nbsp;<a onclick="xajax_turnback(\''.$call['hold']['channel'].'\',\''.$myValue['callerChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Turn back").'</font></a>&nbsp;&nbsp;&nbsp;<a onclick="xajax_attendtransfer(\''.$call['hold']['channel'].'\',\''.$myValue['calleeChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Transfer").'</font></a>&nbsp;&nbsp;&nbsp;');
				//<a onclick="xajax_holdhangup(\''.$call['hold']['channel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Hangup").'</font></a>
			}else{
				$curcallerid = $myValue['callerid'];
				$objResponse->addAssign("divHolding","innerHTML",'');
			}
			#print_r($myValue);exit;
			if(!empty($call['srcname']) && $call['srcname'] != 'unknown' && $call['srcname'] != '<unknown>') {
				$info = $locate->Translate("talking_to").$curcallerid.' ('.$call['srcname'].')&nbsp;&nbsp;';
			} else {
				$info = $locate->Translate("talking_to").$curcallerid;
			}
			
			if($call['queue'] != ''){
				foreach($_SESSION['curuser']['campaign_queue'] as $row){
					
					if($row['queuename'] == $call['queue']){
						if($row['autopause'] == 'checked'){
							$pagent = '';
							if(strstr($call['calleeChannel'],'agent')){
								$pagent = $call['calleeChannel'];
							}
							$objResponse->addScript("xajax_queueAgentControl('".$row['queuename']."','pause','".$row['queue_context']."','".$pagent."');");	
						}
						$objResponse->addAssign("campaignDiv-".$row['id'],"style.background",'red');
					}					
				}
			}
			$objResponse->addAssign("callerChannel","value", $call['callerChannel'] );
			$objResponse->addAssign("calleeChannel","value", $call['calleeChannel'] );
			//if chkMonitor be checked or monitor by astercctools btnMonitor must be disabled
			$configstatus = Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
			if ($configstatus != -2){
				if ($myValue['chkMonitor'] != 'on' && $asterccConfig['system']['force_record'] != 1) {
					$objResponse->addAssign("btnMonitor","disabled", false );
				}
			}
			//$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
			astercrm::events($myValue['chkMonitor'].'-chkMonitor');
			astercrm::events($myValue['btnMonitorStatus'].'-btnMonitorStatus');
			//echo $myValue['chkMonitor'];exit;
			if ($myValue['chkMonitor'] == 'on' && $myValue['btnMonitorStatus'] == 'idle') 
				$objResponse->addScript("monitor();");			
			$objResponse->addAssign("btnHangup","disabled", false );
			if(strtoupper($config['system']['transfer_pannel']) == 'ON' && $call['hold']['number'] == ''){
				$objResponse->addAssign("btnTransfer","disabled", false );
			}
		} elseif ($call['status'] =='hangup'){
			if($call['hold']['number'] != ''){
			//print_r($myValue);exit;
				$curcallerid = $call['hold']['number'];
				$objResponse->addAssign("divHolding","innerHTML",'<a href="###" onclick="getContact('.$call['hold']['number'].');">['.$call['hold']['number'].']</a>&nbsp;&nbsp;<a onclick="xajax_turnback(\''.$call['hold']['channel'].'\',\''.$myValue['callerChannel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Turn back").'</font></a>&nbsp;&nbsp;&nbsp;');
				//<a onclick="xajax_holdhangup(\''.$call['hold']['channel'].'\');return false;" href="###"><font size="2px">'.$locate->Translate("Hangup").'</font></a>
			}else{
				$curcallerid = $myValue['callerid'];
				$objResponse->addAssign("divHolding","innerHTML",'');
			}
//			if($call['hold']['channel'] != ''){//检查是否还有onhold的通话,有则呼叫座席进行接回
//
//				$myAsterisk = new Asterisk();
//				$myAsterisk->config['asmanager'] = $config['asterisk'];
//				$res = $myAsterisk->connect();
//				$strChannel = "Local/".$_SESSION['curuser']['extension']."@from-internal/n";
//				$myAsterisk->sendCall($strChannel,NULL,NULL,1,'Bridge',$call['hold']['channel'],30,$_SESSION['curuser']['extension'],NULL,$_SESSION['curuser']['accountcode']);
//			}
			//$objResponse->addAssign("divCallresult", "style.display", "none");
			$objResponse->addAssign("callResultStatus", "value", "");
			$objResponse->addAssign('trunkinfoStatus',"value",'0');
			
			//$objResponse->addAssign("divCallresult", "innerHTML", '<input type="radio" value="normal" id="callresult" name="callresult" onclick="updateCallresult(this.value);" checked>'.$locate->Translate("normal").' <input type="radio" value="fax" id="callresult" name="callresult" onclick="updateCallresult(this.value);">'. $locate->Translate("fax").' <input type="radio" value="voicemail" id="callresult" name="callresult" onclick="updateCallresult(this.value);">'. $locate->Translate("voicemail").'<input type="hidden" id="dialedlistid" name="dialedlistid" value="0">');
			if ($myValue['chkMonitor'] == 'on' && $myValue['btnMonitorStatus'] == 'recording') 
				$objResponse->addScript("monitor();");
			$status	= 'hang up';
			$info	= "Hang up call from " . $myValue['callerid'];
//			$objResponse->addScript('document.title=\'asterCrm\';');
			$objResponse->addAssign("uniqueid","value", "" );
			$objResponse->addAssign("callerid","value", "" );
			$objResponse->addAssign("callerChannel","value", '');
			$objResponse->addAssign("calleeChannel","value", '');
			if(strtoupper($config['system']['transfer_pannel']) == 'ON'){
				$objResponse->addAssign("btnTransfer","disabled", true );
			}

			//disable monitor
			$objResponse->addAssign("btnMonitor","disabled", true );
			$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("idle") );
			$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
			
			foreach($_SESSION['curuser']['campaign_queue'] as $row){
				//print_r($row);exit;
				$objResponse->addAssign("campaignDiv-".$row['id'],"style.background",'');
			}

			//disable hangup button
			$objResponse->addAssign("btnHangup","disabled", true );
			$objResponse->addAssign('divTrunkinfo',"innerHTML",'');
			$objResponse->addAssign('trunkinfo_number',"innerHTML",'');
			$objResponse->addAssign('divDIDinfo','innerHTML','');
			if($myValue['btnWorkStatus'] == 'working') {				
				$interval = $_SESSION['curuser']['dialinterval'];
				$objResponse->addScript("autoDial('$interval');");
			}
			$objResponse->addScript("document.getElementById('btnDial').disabled=false;");
			$objResponse->addScript("setTimeoutforPopup();");
		}
		$objResponse->addAssign("status","innerHTML", $status );
//		$objResponse->addAssign("extensionStatus","value", $status );
		
		$objResponse->addAssign("myevents","innerHTML", $info );
	}

	return $objResponse;
}

//	create grid
function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=null){
	global $locate,$config;

	$_SESSION['ordering'] = $ordering;

	if($filter == null or $content == null or $content == 'Array' or $filter == 'Array'){
		$numRows =& Customer::getNumRows();
		$arreglo =& Customer::getAllRecords($start,$limit,$order);
		$content = null;
		$filter = null;
	}else{
		foreach($content as $value){
			if(trim($value) != ""){  //搜索内容有值
				$flag = "1";
				break;
			}
		}
		foreach($filter as $value){
			if(trim($value) != ""){  //搜索条件有值
				$flag2 = "1";
				break;
			}
		}
		foreach($stype as $value){
			if(trim($value) != ""){  //搜索方式有值
				$flag3 = "1";
				break;
			}
		}
		if($flag != "1" || $flag2 != "1"){  //无值
			$order = null;
			$numRows =& Customer::getNumRows();
			$arreglo =& Customer::getAllRecords($start,$limit,$order);
		}elseif($flag3 != 1){ //无搜索方式
			$order = "id";
			$numRows =& Customer::getNumRows($filter, $content);
			$arreglo =& Customer::getRecordsFiltered($start, $limit, $filter, $content, $order);
		}else{
			$order = "id";
			$numRows =& Customer::getNumRowsMorewithstype($filter, $content,$stype,$table);
			$arreglo =& Customer::getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table);
		}
	}
	// Editable zone

	// Select Box: type table.
	$typeFromSearch = array();
	$typeFromSearch[] = 'like';
	$typeFromSearch[] = 'equal';
	$typeFromSearch[] = 'more';
	$typeFromSearch[] = 'less';

	// Selecct Box: Labels showed on searchtype select box.
	$typeFromSearchShowAs = array();
	$typeFromSearchShowAs[] = $locate->Translate('like');
	$typeFromSearchShowAs[] = '=';
	$typeFromSearchShowAs[] = '>';
	$typeFromSearchShowAs[] = '<';


	// Databse Table: fields
	$fields = array();
	$fields[] = 'customer';
	$fields[] = 'category';
	$fields[] = 'contact';
	$fields[] = 'note';
	$fields[] = 'attitude';   //face
	$fields[] = 'cretime';
	$fields[] = 'creby';
	$fields[] = 'priority';
	$fields[] = 'private';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("customer_name")."<BR>";//"Customer Name";
	$headers[] = $locate->Translate("category")."<BR>";//"Category";
	$headers[] = $locate->Translate("contact")."<BR>";//"Contact";
	$headers[] = $locate->Translate("note")."<BR>";//"Note";
	$headers[] = $locate->Translate("attitude")."<BR>";//"face";
	$headers[] = $locate->Translate("create_time")."<BR>";//"Create Time";
//	$headers[] = $locate->Translate("create_by")."<BR>";//"Create By";
	$headers[] = $locate->Translate("P")."<BR>";
	if ($config['system']['portal_display_type'] == "note")
		$headers[] = $locate->Translate("private")."<BR>";
//	$headers[] = "D";

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="20%" nowrap';
	$attribsHeader[] = 'width="10%" nowrap';
	$attribsHeader[] = 'width="8%" nowrap';
	$attribsHeader[] = 'width="36%" nowrap';//note
	$attribsHeader[] = 'width="8%" nowrap'; //face
	$attribsHeader[] = 'width="10% nowrap"';
//	$attribsHeader[] = 'width="10%"';
//	$attribsHeader[] = 'width="7%"';
	$attribsHeader[] = 'width="8%" nowrap';
	if ($config['system']['portal_display_type'] == "note")
		$attribsHeader[] = 'width="8%" nowrap';

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left; textarea-layout:fixed; word-break:break-all;"';
	$attribsCols[] = 'style="text-align: left"';
//	$attribsCols[] = 'nowrap style="text-align: left"';
	$attribsCols[] = 'style="text-align: left;"';
	if ($config['system']['portal_display_type'] == "note")
		$attribsCols[] = 'style="text-align: left;"';


	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","category","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","attitude","'.$divName.'","ORDERING");return false;\'';  //face
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","cretime","'.$divName.'","ORDERING");return false;\'';
//	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","priority","'.$divName.'","ORDERING");return false;\'';
	if ($config['system']['portal_display_type'] == "note")
		$eventHeader[]= 'onClick=\'showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","private","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	if ($config['system']['portal_display_type'] == "note"){
		$fieldsFromSearch[] = 'customer';
		$fieldsFromSearch[] = 'category';
		$fieldsFromSearch[] = 'contact.contact';
		$fieldsFromSearch[] = 'customer.fax';
		$fieldsFromSearch[] = 'note';
		$fieldsFromSearch[] = 'attitude';  //face
		$fieldsFromSearch[] = 'priority';
		$fieldsFromSearch[] = 'note.cretime';
	}elseif ($config['system']['portal_display_type'] == "customer"){
		$fieldsFromSearch[] = 'customer.customer';
		$fieldsFromSearch[] = 'customer.category';
		$fieldsFromSearch[] = 'customer.contact';
		$fieldsFromSearch[] = 'customer.fax';
		$fieldsFromSearch[] = 'note.note';
		$fieldsFromSearch[] = 'note.attitude';  //face
		$fieldsFromSearch[] = 'note.priority';
		$fieldsFromSearch[] = 'customer.cretime';
	}

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("customer_name");
	$fieldsFromSearchShowAs[] = $locate->Translate("category");
	$fieldsFromSearchShowAs[] = $locate->Translate("contact");
	$fieldsFromSearchShowAs[] = $locate->Translate("fax");
	$fieldsFromSearchShowAs[] = $locate->Translate("note");
	$fieldsFromSearchShowAs[] = $locate->Translate("attitude"); //face
	$fieldsFromSearchShowAs[] = $locate->Translate("priority");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_time");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	if ($config['system']['portal_display_type'] == "note"){
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader);
	}else{
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=true,$delete=false,$detail=true);
	}
	$table->setAttribsCols($attribsCols);
	
	//$table->addRowSearch("note",$fieldsFromSearch,$fieldsFromSearchShowAs);
	//$table->addRowSearchMore("note",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content);
	$table->addRowSearchMore($config['system']['portal_display_type'],$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];

	if ($config['system']['portal_display_type'] == "note"){
		$rowc[] = "<a href=? onclick=\"xajax_showCustomer('".$row['customerid']."');return false;\">".$row['customer']."</a>";
	}else{
		if($row['phone'] != '') {
			$rowc[] = "<a href=? onclick=\"getContact('".$row['phone']."','".$row['id']."');return false;\">".$row['customer']." (".$row['phone'].")</a>";
		} else if($row['mobile'] != ''){
			$rowc[] = "<a href=? onclick=\"getContact('".$row['mobile']."','".$row['id']."');return false;\">".$row['customer']." (".$row['mobile'].")</a>";
		} else {
			$rowc[] = $row['customer'];
		}
	}


		$rowc[] = $row['category'];

	if ($config['system']['portal_display_type'] == "note"){
		$rowc[] = "<a href=? onclick=\"xajax_showContact('".$row['contactid']."');return false;\">".$row['contact']."</a>";
	}else{
		$rowc[] = $row['contact'];
	}


		//$rowc[] = '<textarea readonly="true" style="overflow:auto;width: 240px;height:50px;" wrap="soft">'.str_replace('<br>',chr(13),$row['note']).'</textarea>';
		if($row['private'] == 0 || $row['creby'] == $_SESSION['curuser']['username'])
			$rowc[] = ''.$row['note'].'';
		else
			$rowc[] = '';

		if ($row['attitude'] != '')
			$rowc[] = '<img src="skin/default/images/'.$row['attitude'].'.gif" width="25px" height="25px" border="0" />';
		else 
			$rowc[] = '';

		$rowc[] =  str_replace(" ","<br>",$row['cretime']);
//		$rowc[] = $row['creby'];
		$rowc[] = $row['priority'];
//		$rowc[] = 'Detail';
		if ($config['system']['portal_display_type'] == "note"){
			if($row['private'] == 1) 
				$rowc[] = '<img src="images/groups_icon01.gif"  border="0"';
			else $rowc[] = '';
			$table->addRow("note",$rowc,1,1,1,$divName,$fields,null,'','forportal');
		}else{
			$table->addRow("customer",$rowc,1,0,1,$divName,$fields,null,'','forportal');
		}
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	
 	return $html;
}

function addWithPhoneNumber(){
	$objResponse = new xajaxResponse();
	global $db;
	
	$row = astercrm::getDialNumByAgent($_SESSION['curuser']['extension']);//print_r($row);exit;

	if ($row['id'] == ''){

	} else {
		$sql = "SELECT * FROM dnc_list WHERE number='".$row['dialnumber']."' AND (campaignid=0 OR campaignid = '".$row['campaignid']."') AND (groupid = 0 OR groupid='".$row['groupid']."')  LIMIT 1";
		$dnc_row = $db->getRow($sql);
		
		$phoneNum = $row['dialnumber'];

		if($dnc_row['id'] > 0){
			$row['callresult'] = 'dnc';
			astercrm::deleteRecord($row['id'],"diallist");
			$row['dialednumber'] = $phoneNum;
			$row['dialedby'] = $_SESSION['curuser']['extension'];
			$row['trytime'] = $row['trytime'] + 1;
			astercrm::insertNewDialedlist($row);
		}else{
			$objResponse->loadXML(getContact($phoneNum,0,$row['campaignid'],$row['id']));
		}		
		
//		astercrm::deleteRecord($row['id'],"diallist");
//		$row['dialednumber'] = $phoneNum;
//		$row['dialedby'] = $_SESSION['curuser']['extension'];
//		$row['trytime'] = $row['trytime'] + 1;
//		astercrm::insertNewDialedlist($row);
	
	}

	$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));

	return $objResponse;
}

function checkworkexten() {
	global $db,$locate,$config;

	$objResponse = new xajaxResponse();
	if($config['system']['checkworkexten'] != 'yes'){
		$objResponse->addAssign("workingextenstatus","value", "ok" );
		return $objResponse;
	}
	
	if($_SESSION['curuser']['channel'] == ''){
		$row = astercrm::getRecordByField("peername","sip/".$_SESSION['curuser']['extension'],"peerstatus");
	}else{
		$row = astercrm::getRecordByField("peername",$_SESSION['curuser']['channel'],"peerstatus");
	}

	if($row['status'] != 'reachable' && $row['status'] != 'registered' && !strstr($row['status'],'ok')) {
		$objResponse->addAssign("workingextenstatus","value", $locate->Translate("extension_unavailable") );
	}else{
		$objResponse->addAssign("workingextenstatus","value", "ok" );
	}

	return $objResponse;
}

function workstart() {
	global $db,$locate,$config;
	$objResponse = new xajaxResponse();

	$row = astercrm::getDialNumByAgent($_SESSION['curuser']['extension']);
	if ($row['id'] == ''){

	} else {
		$sql = "SELECT * FROM dnc_list WHERE number='".$row['dialnumber']."' AND (campaignid=0 OR campaignid = '".$row['campaignid']."') AND (groupid = 0 OR groupid='".$row['groupid']."')  LIMIT 1";
		$dnc_row = $db->getRow($sql);
		
		if($dnc_row['id'] > 0){
			$row['callresult'] = 'dnc';
			$phoneNum = $row['dialnumber'];			
			astercrm::deleteRecord($row['id'],"diallist");

			$row['trytime'] = $row['trytime'] + 1;
			$row['dialednumber'] = $phoneNum;
			$row['dialedby'] = $_SESSION['curuser']['extension'];
			$dialedlistid = astercrm::insertNewDialedlist($row);
			$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
			$objResponse->addScript("workctrl('start');");
			return $objResponse;
		}

		$objResponse->addAssign("btnWork","value", $locate->Translate("Stop work"));
		if($config['system']['stop_work_verify'])
			$objResponse->addEvent("btnWork", "onclick", "workctrl('check');");
		else
			$objResponse->addEvent("btnWork", "onclick", "workctrl('stop');");
		$objResponse->addAssign("btnWorkStatus","value", "working" );
		$objResponse->addAssign("divWork","innerHTML", $locate->Translate("dialing to")." ".$row['dialnumber']);
		$_SESSION['curuser']['WorkStatus'] = 'working';
		$phoneNum = $row['dialnumber'];			
		astercrm::deleteRecord($row['id'],"diallist");

		$row['trytime'] = $row['trytime'] + 1;
		$row['dialednumber'] = $phoneNum;
		$row['dialedby'] = $_SESSION['curuser']['extension'];
		$dialedlistid = astercrm::insertNewDialedlist($row);
		$objResponse->loadXML(getContact($phoneNum,0,$row['campaignid']));
		$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
		
		if($row['callresult'] != 'dnc')
		invite($_SESSION['curuser']['extension'],$phoneNum,$row['campaignid'],$dialedlistid);
		
	}		
	return $objResponse;
}

function workoffcheck($f=''){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	if($config['system']['stop_work_verify']){
		if($f['adminname'] == '') return $objResponse;
		$admininfo = astercrm::getRecordByField('username',$f['adminname'],'astercrm_account');
		if($admininfo['password'] == $f['Workoffpwd'] && (($admininfo['usertype'] == 'groupadmin' && $admininfo['groupid'] == $_SESSION['curuser']['groupid']) || $admininfo['usertype'] == 'admin')) {
			
		}else{
			return $objResponse;
		}
	}

	$objResponse->addAssign("btnWork","value", $locate->Translate("Start work") );
	$objResponse->addEvent("btnWork", "onclick", "workctrl('start');");
	$objResponse->addAssign("btnWorkStatus","value", "" );
	$objResponse->addAssign("divWork","innerHTML", "" );
	$_SESSION['curuser']['WorkStatus'] = '';
	$objResponse->addAssign("formWorkoff", "style.visibility", "hidden");
	$objResponse->addAssign("formWorkoff", "innerHTML", '');
	$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
	
	return $objResponse;
}
# click to dial
# $phoneNum	phone to call
# $first	which phone will ring first, caller or callee

function dial($phoneNum,$first = '',$myValue,$dtmf = '',$diallistid=0){
	global $config,$locate;

	$objResponse = new xajaxResponse();
	if(trim($myValue['curid']) > 0) $curid = trim($myValue['curid']) - 1;
	else $curid = trim($myValue['curid']);

	$call = asterEvent::checkNewCall($curid,$curid,$_SESSION['curuser']['extension'],$_SESSION['curuser']['channel'],$_SESSION['curuser']['agent']);
	
	if($call['status'] != '') {
		//$objResponse->addAssign("divMsg", "style.visibility", "hidden");
		$objResponse->addScript("alert('".$locate->Translate("Exten in use")."')");
		return $objResponse->getXML();
	}
	//$group_info = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");

	if ($_SESSION['curuser']['group']['incontext'] != '' ) $incontext = $_SESSION['curuser']['group']['incontext'];
	else $incontext = $config['system']['incontext'];
	if ($_SESSION['curuser']['group']['outcontext'] != '' ) $outcontext = $_SESSION['curuser']['group']['outcontext'];
	else $outcontext = $config['system']['outcontext'];

	if ($dtmf != '') {
		$app = 'Dial';
		$data = 'local/'.$phoneNum.'@'.$incontext.'|30'.'|D'.$dtmf;
		$first = 'caller';
	}

	$myAsterisk = new Asterisk();	
	if ($first == ''){
		if($_SESSION['curuser']['group']['firstring'] != ''){
			$first = $_SESSION['curuser']['group']['firstring'];
		}else{
			$first = $config['system']['firstring'];
		}
	}

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");

	if ($first == 'caller'){	//caller will ring first
		$variable = '__CUSCID='.$_SESSION['curuser']['extension'];
		$strChannel = "local/".$_SESSION['curuser']['extension']."@".$incontext."/n";

		if ($config['system']['allow_dropcall'] == true){
			$sid = Customer::generateUniquePin();

			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$phoneNum,
								'Context'=>$outcontext,
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$variable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$phoneNum));
		}else{
			$myAsterisk->sendCall($strChannel,$phoneNum,$outcontext,1,$app,$data,30,$phoneNum,$variable,$_SESSION['curuser']['accountcode']);
		}
	}else{
		$variable = '__CUSCID='.$_SESSION['curuser']['extension'];
		$strChannel = "local/".$phoneNum."@".$outcontext."/n";

		if ($config['system']['allow_dropcall'] == true){
			$sid = Customer::generateUniquePin('10');
/*
	coz after we use new method to capture dial event
	there's no good method to make both leg display correct clid for now
	so we comment these lines
*/
			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$_SESSION['curuser']['extension'],
								'Context'=>$incontext,
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$variable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$phoneNum));
		}else{
			$myAsterisk->sendCall($strChannel,$_SESSION['curuser']['extension'],$incontext,1,$app,$data,30,$phoneNum,$variable,NULL);
		}
	}
	//$myAsterisk->disconnect();
	//$objResponse->addAssign("divMsg", "style.visibility", "hidden");

	if($diallistid > 0){
		$row = astercrm::getRecordByID($diallistid,'diallist'); 
		if($row['dialnumber'] != ''){
			$row['callresult'] = '';
			astercrm::deleteRecord($row['id'],"diallist");
			$row['dialednumber'] = $phoneNum;
			$row['dialedby'] = $_SESSION['curuser']['extension'];
			$row['trytime'] = $row['trytime'] + 1;
			astercrm::insertNewDialedlist($row);
		}
		$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
	}
	return $objResponse->getXML();
}

/**
*  Originate src and dest extension
*  @param	src			string			extension
*  @param	dest		string			extension
*  @return	object						xajax response object
*/

function invite($src,$dest,$campaignid='',$dialedlistid=0){
	global $config,$locate;
	#print_r($_SESSION['curuser']['group']);exit;
	$src = trim($src);
	$dest = trim($dest);
	$objResponse = new xajaxResponse();	
	//$objResponse->addAssign("dialmsg", "innerHTML", "<b>".$locate->Translate("dailing")." ".$src."</b>");
	if ($src == $_SESSION['curuser']['extension']){
		$callerid = $dest;
	}else{
		$callerid = $src;
	}
	$variable = null;
	$myAsterisk = new Asterisk();
	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");
	if($campaignid != ''){
		$row_campaign = astercrm::getRecordByID($campaignid,"campaign");
		//print_r($row_campaign);exit;
		if(trim($row_campaign['nextcontext']) != '' ){
			$incontext = $row_campaign['nextcontext'];
		}elseif(trim($row_campaign['incontext']) != ''){
			$incontext = $row_campaign['incontext'];
		}else{
			$incontext = $config['system']['incontext'];
		}

		if(trim($row_campaign['firstcontext']) != '' ){
			$outcontext = $row_campaign['firstcontext'];
		}elseif(trim($row_campaign['outcontext']) != ''){
			$outcontext = $row_campaign['outcontext'];
		}else{
			$outcontext = $config['system']['outcontext'];
		}

		if($row_campaign['callerid'] == ""){
			$variable = '__CUSCID='.$_SESSION['curuser']['extension'].$_SESSION['asterisk']['paramdelimiter'];
		}
		//if($row_campaign['inexten'] != '') $src = $row_campaign['inexten'];
		//echo $variable;exit;
		if($_SESSION['curuser']['group']['firstring'] == 'caller'){
			if($row_campaign['dialtwoparty'] == "yes"){
				$strChannel = "local/".$src."@".$incontext."";
			}else{
				$strChannel = "local/".$src."@".$incontext."/n";
			}

			if($row_campaign['callerid'] != ""){
				$callerid = $row_campaign['callerid'];
				$variable = '__CUSCID='.$dest.$_SESSION['asterisk']['paramdelimiter'];
			}

			$incontext = $outcontext;
		}else{
			if($row_campaign['dialtwoparty'] == "yes"){
				$strChannel = "local/".$dest."@".$outcontext."";
			}else{
				$strChannel = "local/".$dest."@".$outcontext."/n";
			}

			if($row_campaign['callerid'] != ""){
				$callerid = $row_campaign['callerid'];
				$variable = '__CUSCID='.$dest.$_SESSION['asterisk']['paramdelimiter'];
			}
			$dest = $src;
		}

		$variable .= '__CAMPAIGNID='.$row_campaign['id'].$_SESSION['asterisk']['paramdelimiter']; #传拨号计划id给asterisk
		$variable .= '__DIALEDLISTID='.$dialedlistid.$_SESSION['asterisk']['paramdelimiter']; #dialedlist id给asterisk
		$variable .= '__DIALEDNUM='.$dest;
		
	}else{
		if($_SESSION['curuser']['callerid'] == '' ){
			$variable .= '__CUSCID='.$_SESSION['curuser']['extension'];
		}else{
			$variable .= '__CUSCID='.$_SESSION['curuser']['callerid'];
		}
		//$group_info = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");

		if ($_SESSION['curuser']['group']['incontext'] != '' ) $incontext = $_SESSION['curuser']['group']['incontext'];
		else $incontext = $config['system']['incontext'];
		if ($_SESSION['curuser']['group']['outcontext'] != '' ) $outcontext = $_SESSION['curuser']['group']['outcontext'];
		else $outcontext = $config['system']['outcontext'];

		
		if($_SESSION['curuser']['group']['firstring'] == 'caller'){
			$strChannel = "local/".$dest."@".$incontext."/n";
			$dest = $src;
			$incontext = $outcontext;
		}else{
			$strChannel = "local/".$src."@".$outcontext."/n";
		}
	}


	if ($config['system']['allow_dropcall'] == true){
		$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
							'WaitTime'=>30,
							'Exten'=>$dest,
							'Context'=>$incontext,
							'Account'=>$_SESSION['curuser']['accountcode'],
							'Variable'=>"$variable",
							'Priority'=>1,
							'MaxRetries'=>0,
							'CallerID'=>$callerid));
	}else{
		$myAsterisk->sendCall($strChannel,$dest,$incontext,1,NULL,NULL,30,$callerid,$variable,$_SESSION['curuser']['accountcode']);
	}
	
	//$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse->getXML();
}

/**
*  hangup a channel
*  @param	channel			string		channel name
*  @return	object						xajax response object
*/


function hangup($channel){
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();
	if (trim($channel) == '')
		return $objResponse;
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addALert("action Huangup failed");
		return $objResponse;
	}
	$myAsterisk->Hangup($channel);
	//$objResponse->addAssign("btnHangup", "disabled", true);
	//$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse;
}

function getContact($callerid,$customer_id=0,$campaignid=0,$diallistid=0,$srcname=null){
	global $db,$locate,$config;
	
	$mycallerid = $callerid;
	$objResponse = new xajaxResponse();
	if($callerid == '') {
		$objResponse->addALert($locate->Translate('Caller number cannot be empty'));
		return $objResponse;
	}
	$objResponse->addAssign("iptCallerid", "value", $callerid);
	if ( $config['system']['trim_prefix'] != ''){
		$prefix = split(",",$config['system']['trim_prefix']);
		foreach ($prefix as $myprefix ) {
			if (substr($mycallerid,0,1) == $myprefix){
				$mycallerid = substr($mycallerid,1);
				break;
			}
		}
	}		

	//check contact table first
	if($config['system']['enable_contact'] == '0' && $customer_id == ''){
		if ($config['system']['detail_level'] == 'all')
			$row = astercrm::getContactByCallerid($mycallerid);
		else
			$row = astercrm::getContactByCallerid($mycallerid,$_SESSION['curuser']['groupid']);
	}
	
	if ($row['id'] == '' || $config['system']['enable_contact'] == '0'){	//no match
		//	print 'no match in contact list';

		//try get customer
		if ($config['system']['detail_level'] == 'all')
			$customerid = astercrm::getCustomerByCallerid($mycallerid);
		else
			$customerid = astercrm::getCustomerByCallerid($mycallerid,$_SESSION['curuser']['groupid']);

		if ($customerid == ''){
			//$objResponse->addScript('xajax_add(\'' . $callerid . '\');');
			if($config['system']['enable_formadd_popup']) {
				$html = Table::Top($locate->Translate("add_record"),"formDiv");  // <-- Set the 
				$html .= Customer::formAdd($callerid,0,0,$campaignid,$diallistid,'',$srcname);

				$html .= Table::Footer();
				$objResponse->addAssign("formDiv", "style.visibility", "visible");
				$objResponse->addAssign("formDiv", "innerHTML", $html);
			}
			

			// callerid smart match
			if ($config['system']['smart_match_remove']) {
				if ($config['system']['detail_level'] == 'all') {
					$contact_res = astercrm::getContactSmartMatch($mycallerid);
					$customer_res = astercrm::getCustomerSmartMatch($mycallerid);
				}else {
					$contact_res = astercrm::getContactSmartMatch($mycallerid,$_SESSION['curuser']['groupid']);
					$customer_res = astercrm::getCustomerSmartMatch($mycallerid,$_SESSION['curuser']['groupid']);
				}
				$smartcount = 0;
				while ($customer_res->fetchInto($row)) {
					$smartcount++;
					$smartmatch_html .= '<a href="###" onclick="xajax_showCustomer(\''.$row['id'].'\',\'customer\','.$callerid.');showMsgBySmartMatch(\'customer\',\''.$row['customer'].'\');">'.$locate->Translate("customer").':&nbsp;'.$row['customer'].'<br>'.$locate->Translate("phone").':'.$row['phone'].'</a><hr>';
				}

				while ($contact_res->fetchInto($row)) {
					$smartcount++;
					$smartmatch_html .= '<a href="###" onclick="xajax_showContact(\''.$row['id'].'\');showMsgBySmartMatch(\'contact\',\''.$row['contact'].'\');">'.$locate->Translate("contact").':&nbsp;'.$row['contact'].'<br>'.$locate->Translate("phone").':'.$row['phone'].'&nbsp;&nbsp;'.$row['phone1'].'&nbsp;&nbsp;'.$row['phone2'].'</a><hr>';
				}

				if ($smartcount < 3 ) {
					$objResponse->addAssign("smartMsgDiv", "style.height", '');
					$objResponse->addAssign("SmartMatchDiv", "style.height", '');
				}else{
					$objResponse->addAssign("smartMsgDiv", "style.height", '160px');
					$objResponse->addAssign("SmartMatchDiv", "style.height", '240px');
				}

				if ($smartcount) {
					$objResponse->addAssign("smartMsgDiv", "innerHTML", $smartmatch_html);
					$objResponse->addScript('getSmartMatchMsg();');
				}
			}
		}else{
			if($config['system']['enable_formadd_popup']) {
				$html = Table::Top($locate->Translate("add_record"),"formDiv");  // <-- Set the title for your form.
				$html .= Customer::formAdd($callerid,$customerid,0,$campaignid,$diallistid,'',$srcname);  // <-- Change by your method
				$html .= Table::Footer();
				$objResponse->addAssign("formDiv", "style.visibility", "visible");
				$objResponse->addAssign("formDiv", "innerHTML", $html);
			}

			$objResponse->addScript('xajax_showCustomer(\''.$customerid.'\',\'customer\','.$callerid.');');
			if($config['system']['auto_note_popup']){
				$objResponse->addScript('xajax_showNote(\''.$customerid.'\',\'customer\');');
			}
		}
	} else{ // one match
		if($customer_id == '') {
			$customerid = $row['customerid'];
		} else {
			$customerid = $customer_id;
		}
		
		$contactid = $row['id'];
		
		if($config['system']['enable_formadd_popup']) {
			$html = Table::Top($locate->Translate("add_record"),"formDiv");  // <-- Set the title for your form.
			$html .= Customer::formAdd($callerid,$customerid,$contactid,$campaignid,$diallistid);  // <-- Change by your method
			$html .= Table::Footer();
			$objResponse->addAssign("formDiv", "style.visibility", "visible");
			$objResponse->addAssign("formDiv", "innerHTML", $html);
		}

		$objResponse->addScript('xajax_showContact(\''.$contactid.'\');');
		
		if ($customerid != 0){
			$objResponse->addScript('xajax_showCustomer(\''.$customerid.'\',\'customer\','.$callerid.');');
			if($config['system']['auto_note_popup']){
				$objResponse->addScript('xajax_showNote(\''.$customerid.'\',\'customer\');');
			}
		}else{
			if($config['system']['auto_note_popup']){
				$objResponse->addScript('xajax_showNote(\''.$contactid.'\',\'contact\');');
			}
		}

	}
//echo $campaignid.$diallistid;exit;
	

	return $objResponse;
}

function displayMap($address){
	global $config,$locate;
	$objResponse = new xajaxResponse();
	if($config['google-map']['key'] == ''){
		$objResponse->addAssign("divMap","style.visibility","hidden");
		$objResponse->addScript("alert('".$locate->Translate("google_map_no_key")."')");	
		return $objResponse;
	}
	if ($address == '')
		return $objResponse;
	$map = new PhoogleMap();
	$map->setAPIKey($config['google-map']['key']);
	$map->addAddress($address);
	//$map->showMap();
	$js = $map->generateJs();

	$objResponse->addAssign("divMap","style.visibility","visible");
	//$objResponse->addScript("alert('".$js."')");
	$objResponse->addScript($js);
	return $objResponse;
}

function chanspy($exten,$spyexten,$pam = ''){
	global $config,$locate;

	if($_SESSION['curuser']['groupid'] > 0){
		$group = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");
		if($group['outcontext'] != ''){
			$exten .= '@'.$group['outcontext'].'/n';
		}else{
			if($config['system']['outcontext'] != ''){
				$exten .= '@'.$config['system']['outcontext'].'/n';
			}
		}
	}else{
		if($config['system']['outcontext'] != ''){
			$exten .= '@'.$config['system']['outcontext'].'/n';
		}
	}

	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}
	$spyexten = split('-',$spyexten);
	$spyexten = $spyexten['0'];

	$myAsterisk->chanSpy($exten,$spyexten,$pam,$_SESSION['asterisk']['paramdelimiter']);
	return $objResponse;
}

function bargeInvite($srcchan,$dstchan,$exten){
	//echo $srcchan,$dstchan,$exten;exit;
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}

	//$group_info = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");

	if ($_SESSION['curuser']['group']['incontext'] != '' ) $incontext = $_SESSION['curuser']['group']['incontext'];
	else $incontext = $config['system']['incontext'];
	//if ($group_info['outcontext'] != '' ) $outcontext = $group_info['outcontext'];
	//else $outcontext = $config['system']['outcontext'];

	$strChannel = "local/".$exten."@".$incontext."/n";
	$myAsterisk->Originate($strChannel,'','',1,'meetme',$exten.$_SESSION['asterisk']['paramdelimiter']."pqdx",30,$exten,NULL,NULL);

	$myAsterisk->Redirect($srcchan,$dstchan,$exten,"astercc-barge","1");

	//$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse;
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];
	$divName = "grid";
	if($type == "delete"){
		if ($config['system']['portal_display_type'] == "note"){
			$res = Customer::deleteRecord($id,'note');
		}else{
			$res = Customer::deleteRecord($id,'customer');
		}
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "");
			$objResponse = new xajaxResponse();
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",$searchType);
	}
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	return $objResponse->getXML();
}

function addSchedulerDial($display='',$number,$customerid = ''){
	global $locate,$db;

	/*$objResponse = new xajaxResponse();
	if($display == "none"){
		$campaignflag = false;
		$html = '<td nowrap align="left">'.$locate->Translate("Scheduler Dial").'</td>
					<td align="left">'.$locate->Translate("DialNumber").' : <input type="text" id="sDialNum" name="sDialNum" size="15" maxlength="35" value="'.$number.'">';
		if($number != ''){
			$curtime = date("Y-m-d H:i:s");
			$curtime = date("Y-m-d H:i:s",strtotime("$curtime - 30 seconds"));
			$sql = "SELECT campaignid FROM dialedlist WHERE dialednumber = '".$number."' AND dialedtime > '".$curtime."' ";
			$curcampaignid = $db->getOne($sql);
			if($curcampaignid != ''){
				$campaignflag = true;
				$curcampaign = astercrm::getRecordByID($curcampaignid,'campaign');
				$curcampaign_name = $curcampaign['campaignname'];
				$html .= '&nbsp;'.$locate->Translate("campaign").' : <input type="text" value="'.$curcampaign_name.'" id="campaignname" name="campaignname" size="15" readonly><input type="hidden" value="'.$curcampaignid.'" id="curCampaignid" name="curCampaignid" size="15" readonly>';
			}
		}
		if(!$campaignflag){
			$campaign_res = astercrm::getRecordsByField("groupid",$_SESSION['curuser']['groupid'],"campaign");
			while ($campaign_res->fetchInto($campaign)) {
				$campaignoption .= '<option value="'.$campaign['id'].'">'.$campaign['campaignname'].'</option>'; 
			}
			$html .= '&nbsp;'.$locate->Translate("campaign").' : <select id="curCampaignid" name="curCampaignid" >'.$campaignoption.'</select>';
		}
		//
		$html .= '<br>'.$locate->Translate("Dialtime").' : <input type="text" name="sDialtime" id="sDialtime" size="15" value="" onfocus="displayCalendar(this,\'yyyy-mm-dd hh:ii\',this,true)">&nbsp;&nbsp;';
		if ($customerid >0 ){
			$html .= '<input type="button" value="'.$locate->Translate("Add").'" onclick="saveSchedulerDial(\''.$customerid.'\');">';
		}
		$html .= '</td>';
		$objResponse->addAssign("trAddSchedulerDial", "innerHTML", $html);
		$objResponse->addAssign("trAddSchedulerDial", "style.display", "");
	}else{
		$objResponse->addAssign("trAddSchedulerDial", "style.display", "none");
	}

	formdAddSechedualaraDiv
	return $objResponse->getXML();*/

	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Scheduler Dial"),"formdAddSechedualaraDiv");
	
	$campaignflag = false;
	$html .= '
		<!-- No edit the next line -->
		<form method="post" name="require_reasion" id="require_reasion">
		<table border="1" width="100%" class="adminlist">
			<tr>
				<td nowrap align="left">'.$locate->Translate("DialNumber").' :</td><td align="left"><input type="text" id="sDialNum" name="sDialNum" size="15" maxlength="35" value="'.$number.'"></td>
			</tr>';
	if($number != ''){
		$curtime = date("Y-m-d H:i:s");
		$curtime = date("Y-m-d H:i:s",strtotime("$curtime - 30 seconds"));
		$sql = "SELECT campaignid FROM dialedlist WHERE dialednumber = '".$number."' AND dialedtime > '".$curtime."' ";
		$curcampaignid = $db->getOne($sql);
		if($curcampaignid != ''){
			$campaignflag = true;
			$curcampaign = astercrm::getRecordByID($curcampaignid,'campaign');
			$curcampaign_name = $curcampaign['campaignname'];
			$html .= '<tr><td nowrap align="left">'.$locate->Translate("campaign").' :</td><td align="left"> <input type="text" value="'.$curcampaign_name.'" id="campaignname" name="campaignname" size="15" readonly><input type="hidden" value="'.$curcampaignid.'" id="curCampaignid" name="curCampaignid" size="15" readonly></td></tr>';
		}
	}
	if(!$campaignflag){
		$campaign_res = astercrm::getRecordsByField("groupid",$_SESSION['curuser']['groupid'],"campaign");
		while ($campaign_res->fetchInto($campaign)) {
			$campaignoption .= '<option value="'.$campaign['id'].'">'.$campaign['campaignname'].'</option>'; 
		}
		$html .= '<tr><td nowrap align="left">'.$locate->Translate("campaign").' :</td><td align="left"> <select id="curCampaignid" name="curCampaignid" >'.$campaignoption.'</select></td></tr>';
	}
	$html .= '<tr><td nowrap align="left">'.$locate->Translate("Dialtime").' :</td><td align="left"> <input type="text" name="sDialtime" id="sDialtime" size="15" value="" onfocus="displayCalendar(this,\'yyyy-mm-dd hh:ii\',this,true)"></td></tr>';
	//if ($customerid >0 ){
		$html .= '<tr><td colspan="2" align="center">
				<input type="button" value="'.$locate->Translate("Add").'" onclick="saveSchedulerDial(\''.$customerid.'\');">
				</td></tr>';
	//}
	$html .= '
		</table>
		';
	$html .='
		</form>';
	$html .= Table::Footer();
	$objResponse->addAssign("formdAddSechedualaraDiv", "style.visibility", "visible");
	$objResponse->addAssign("formdAddSechedualaraDiv", "innerHTML", $html);
	//$objResponse->addScript("relateByCategory();");
	return $objResponse->getXML();
}

function saveSchedulerDial($dialnumber='',$campaignid='',$dialtime='',$customerid){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	if($dialnumber == ''){
		$objResponse->addAlert($locate->Translate("Number can not be blank"));
		return $objResponse->getXML();
	}
	if($campaignid == ''){
		$objResponse->addAlert($locate->Translate("Campaign can not be blank"));
		return $objResponse->getXML();
	}
	$customerMsg = astercrm::getRecordsByField('id',$customerid,'customer');
	
	while($customerMsg->fetchInto($tmp)) {
		$customername = $tmp['customer'];
	}
	/*
	if($dialtime == ''){
		$objResponse->addAlert($locate->Translate("Dial time can not be blank"));
		return $objResponse->getXML();
	}	
	*/
	$f['customerid'] = $customerid;
	$f['customername'] = $customername;
	$f['curCampaignid'] = $campaignid;
	$f['sDialNum'] = $dialnumber;
	$f['sDialtime'] = $dialtime;

	$res = astercrm::insertNewSchedulerDial($f);
	$resultId = mysql_insert_id();
	if($res){
		$objResponse->addAlert($locate->Translate("Add scheduler dial success"));
		$objResponse->addAssign("formdAddSechedualaraDiv", "style.visibility", "hidden");
		$objResponse->addAssign("formdAddSechedualaraDiv", "innerHTML", '');
		$objResponse->addAssign("addedSchedulerDialId", "value",$resultId);
		//$objResponse->addAssign("trAddSchedulerDial", "style.display", "none");
	}else{
		$objResponse->addAlert($locate->Translate("Add scheduler dial failed"));
		$objResponse->addAssign("formdAddSechedualaraDiv", "style.visibility", "hidden");
		$objResponse->addAssign("formdAddSechedualaraDiv", "innerHTML", '');
	}
	return $objResponse->getXML();
}

function addTicket($customerid) {
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("ticket_detail"),"formTicketDetailDiv"); 			
	$html .= Customer::showTicketDetail($customerid);
	$html .= Table::Footer();
	$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "visible");
	$objResponse->addAssign("formTicketDetailDiv", "innerHTML", $html);
	//$objResponse->addScript("relateByCategory();");
	return $objResponse->getXML();
}

function relateByCategory($fid) {
	$objResponse = new xajaxResponse();
	$html = Customer::getTicketByCategory($fid);
	$objResponse->addAssign("ticketMsg", "innerHTML", $html);
	return $objResponse->getXML();
}

function saveTicket($f) {
	global $locate;
	$objResponse = new xajaxResponse();
	if($f['ticketid'] == 0) {
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	$validParentTicket = true;
	if($f['parent_id'] != '') {
		if(!preg_match('/^[\d]*$/',$f['parent_id'])){
			$objResponse->addAlert($locate->Translate("Parent TicketDetail ID must be integer"));
			return $objResponse->getXML();
		}
		//验证写入的parent_id 是否存在
		$validParentTicket = Customer::validParentTicketId($f['parent_id']);
	}

	$result = Customer::insertTicket($f);
	if($result == 1) {
		if(!$validParentTicket) {
			$objResponse->addAlert($locate->Translate("Add ticket success,but Parent TicketDetail ID is not exists"));
		}

		// track the ticket_op_logs
		$new_assign = '';
		if($f['assignto'] != 0) {
			$new_assign = Customer::getAssignToName($f['assignto']);
		}
		Customer::ticketOpLogs('add','status','','new',$new_assign,$f['groupid']);

		$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "hidden");
		$objResponse->addScript('AllTicketOfMyself('.$f['customerid'].');');
	} else {
		$objResponse->addAlert($locate->Translate("Add ticket failed"));
	}
	return $objResponse->getXML();
}

function saveNewTicket($f) {
	global $locate;
	$objResponse = new xajaxResponse();
	
	if($f['ticketid'] == 0) {
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	if(empty($f['customerid'])){
		$objResponse->addAlert($locate->Translate("please_select_a_customer"));
		return $objResponse->getXML();
	}
	
	$validParentTicket = true;
	if($f['parent_id'] != '') {
		if(!preg_match('/^[\d]*$/',$f['parent_id'])){
			$objResponse->addAlert($locate->Translate("Parent TicketDetail ID must be integer"));
			return $objResponse->getXML();
		}
		//验证写入的parent_id 是否存在
		$validParentTicket = Customer::validParentTicketId($f['parent_id']);
	}

	$result = Customer::insertTicket($f);
	if($result == 1) {
		if(!$validParentTicket) {
			$objResponse->addAlert($locate->Translate("Add ticket success,but Parent TicketDetail ID is not exists"));
		}

		// track the ticket_op_logs
		$new_assign = '';
		if($f['assignto'] != 0) {
			$new_assign = Customer::getAssignToName($f['assignto']);
		}
		Customer::ticketOpLogs('add','status','','new',$new_assign,$f['groupid']);

		//$objResponse->addAlert($locate->Translate("Add ticket success"));
		$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "hidden");
		$objResponse->addScript("showMyTickets('','agent_tickets')");
	} else {
		$objResponse->addAlert($locate->Translate("Add ticket failed"));
	}
	return $objResponse->getXML();
}

function AllTicketOfMy($cid='',$Ctype,$start = 0, $limit = 5,$filter = null, $content = null, $order = null, $divName = "formMyTickets", $ordering = "",$stype = null) {
	global $locate;
	$objResponse = new xajaxResponse();

	$ticketHtml = Table::Top($locate->Translate("Customer Tickets"),"formMyTickets");
	$ticketHtml .= astercrm::createTikcetGrid($cid,$Ctype,$start, $limit,$filter, $content, $order, $divName, $ordering, $stype);
	$ticketHtml .= Table::Footer();

	$objResponse->addAssign("formMyTickets", "style.visibility", "visible");
	$objResponse->addAssign("formMyTickets", "innerHTML", $ticketHtml);

	return $objResponse->getXML();
}


function updateCallresult($id,$result,$dialnumber){
	global $locate,$config,$db;
	$objResponse = new xajaxResponse();
	$sql = "SELECT id FROM dialedlist WHERE id=$id";
	$isExist = & $db->getOne($sql);
	if(!empty($isExist) && $isExist != '') {
		$sql = "UPDATE dialedlist SET campaignresult = '$result' , resultby = '".$_SESSION['curuser']['username']."' WHERE id = $id";
		$res =& $db->query($sql);
		if ($res){
			$objResponse->addAssign("updateresultMsg","innerHTML","<font color='red'><b>".$locate->Translate("Update Successful")."<b></font>");
		}else{
			$objResponse->addAlert("fail to update campaign result");
		}
	} else {
		$sql = "UPDATE campaigndialedlist SET campaignresult = '".$result."',resultby = '".$_SESSION['curuser']['username']."' WHERE dialednumber = '".$dialnumber."' AND dialedtime > (now()-INTERVAL 600 SECOND) ORDER BY dialednumber DESC LIMIT 1";
		$res =& $db->query($sql);
	}
	
	return $objResponse;
}

function setSecondCampaignResult($parentid){
	$objResponse = new xajaxResponse();
	$res = Customer::getRecordsByField('parentid',$parentid,"campaignresult");
	
	//添加option
	$n = 0;
	while ($res->fetchInto($row)) {
		$objResponse->addScript("addOption('scallresult','".$row['id']."','".$row['resultname']."');");
		if($n == 0){
			$objResponse->addAssign("callresultname","value", $row['resultname']);
			$objResponse->addAssign("spnScallresult","style.display", "");
			$n++;
		}
	}
	if($n == 0) {
		$objResponse->addAssign("spnScallresult","style.display", "none");
	}

	return $objResponse;
}

function setCallresult($id){
	$objResponse = new xajaxResponse();
	$row = astercrm::getRecordByID($id,'campaignresult');
	$objResponse->addAssign("callresultname","value", $row['resultname']);
	return $objResponse;
}

function knowledgechange($knowledgeid){
	$objResponse = new xajaxResponse();
	$html = Customer::knowledge($knowledgeid);
	//$row = astercrm::getRecordByID($knowledgeid,'knowledge');
	$objResponse->addAssign("tdcontent","innerHTML",$html);
	return $objResponse;
}

function setKnowledge(){
	global $locate,$config,$db;

	$objResponse = new xajaxResponse();
	/*知识库*/
    $knowledge = Customer::getKnowledge();
	$knowledgehtml =Table::Top($locate->Translate("knowledge"),"formKnowlagePannel");
	$knowledgehtml .= '<table><tr><td>'.$locate->Translate("knowledgetitle").':</td><td><select id="knowledgetitle" onchange="knowledgechange(this.value);"><option value="0">'.$locate->Translate("please_select").'</option>';
	while ($knowledge->fetchInto($knowledgerow)) {
           $knowledgehtml .= '<option value="'.$knowledgerow['id'].'">'.$knowledgerow['knowledgetitle'].'</option>';
	}
    $knowledgehtml .= '</select></td></tr><tr><td>'.$locate->Translate("content").':</td><td id="tdcontent"><textarea rows="20" cols="70" id="content" wrap="soft" style="overflow:auto;" readonly></textarea></td></tr></table>';
	$objResponse->addAssign("formKnowlagePannel", "innerHTML", $knowledgehtml);
	$objResponse->addAssign("formKnowlagePannel", "style.visibility", "visible");
	return $objResponse;
	/*知识库*/
}

function getPreDiallist($dialid){
	$objResponse = new xajaxResponse();
	global $db;
	
	$row = astercrm::getRecordByID($dialid,'diallist');

	if ($row['id'] == ''){

	} else {
		$phoneNum = $row['dialnumber'];
		$objResponse->loadXML(getContact($phoneNum));
		astercrm::deleteRecord($row['id'],"diallist");
		$row['dialednumber'] = $phoneNum;
		$row['dialedby'] = $_SESSION['curuser']['extension'];
		$row['trytime'] = $row['trytime'] + 1;
		astercrm::insertNewDialedlist($row);
	}

	$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));

	return $objResponse;
} 

function agentWorkstat(){
	global $locate;
	$objResponse = new xajaxResponse();
	$workstat = Customer::getAgentWorkStat();

	if($workstat['billsec'] == ''){
		$billsec = '00'.$locate->Translate("hour").'00'.$locate->Translate("min").'00'.$locate->Translate("sec");
	}else{
		$billsec = $workstat['billsec'];
		$hour = intval($billsec/3600);
		if($hour < 10 ) $hour = '0'.$hour;
		$min = intval($billsec%3600/60);
		if($min < 10) $min = '0'.$min;
		$sec = $billsec%60;
		if($sec < 10) $sec = '0'.$sec;
		$billsec = $hour.$locate->Translate("hour").$min.$locate->Translate("min").$sec.$locate->Translate("sec");
	}
	$html =Table::Top($locate->Translate("work stat").'-'.date("Y-m-d"),"formAgentWordStatDiv");
	$html .= '<table><tr><td>'.$locate->Translate("total calls").':</td><td>'.$workstat['count'].'</td><tr><tr><td>'.$locate->Translate("duration").':</td><td>'.$billsec.'</td><tr></table>';
	$objResponse->addAssign("formAgentWordStatDiv", "innerHTML", $html);
	$objResponse->addAssign("formAgentWordStatDiv", "style.visibility", "visible");
	return $objResponse;
}


function popupDiallist(){
}

function insertIntoDnc($callerid,$campaignid) {
	global $db,$locate;
	$objResponse = new xajaxResponse();
	if($callerid == '') {
		$objResponse->addScript("alert(\"".$locate->Translate('Save failed phone number is empty')."\");");
		return $objResponse;
	}
	$sql = "INSERT INTO dnc_list SET 
		 number='".$callerid."',
		 campaignid=".$campaignid.",
		 groupid=".$_SESSION['curuser']['groupid'].",
		 status='enable',
		 creby='".$_SESSION['curuser']['username']."',
		 cretime=now()";
	astercrm::events($query);
	$res = & $db->query($sql);
	if($res) {
		$objResponse->addScript("alert(\"".$locate->Translate('Save successful')."\");");
	} else {
		$objResponse->addScript("alert(\"".$locate->Translate('Save failed')."\");");
	}
	return $objResponse;
}

function showMyTickets($id='',$Ctype,$start = 0, $limit = 5,$filter = null, $content = null, $order = null, $divName = "formCurTickets", $ordering = "",$stype = null) {
	global $db,$locate;
	$customerid = Customer::getAccountid();
	
	$html = Table::Top($locate->Translate("My Tickets"),"formCurTickets");
	
	$html .= astercrm::createTikcetGrid($customerid,$Ctype,$start, $limit,$filter, $content, $order, $divName, $ordering, $stype);
	
	$html .= Table::Footer();
	
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formCurTickets", "style.visibility", "visible");
	$objResponse->addAssign("formCurTickets", "innerHTML", $html);

	$curmsg = Customer::getTicketInWork();
	$objResponse->addAssign("curticketMsg", "innerHTML", $curmsg);
	return $objResponse->getXML();
}

/**
*  show curTicketDetail edit form
*  @param	id		int		ticket_detail id
*  @return	objResponse	object		xajax response object
*/

function curTicketDetail($id){
	global $locate;
	
	$html = Table::Top( $locate->Translate("edit_ticket_detail"),"formTicketDetailDiv"); 
	$html .= Customer::formTicketEdit($id);
	$html .= Table::Footer();
	// End edit zone
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "visible");
	$objResponse->addAssign("formTicketDetailDiv", "innerHTML", $html);
	//$objResponse->addScript("relateBycategoryID(document.getElementById('ticketcategoryid').value,'edit')");
	return $objResponse->getXML();
}

function relateByCategoryId($Cid,$curid=0) {
	$objResponse = new xajaxResponse();
	$option = Customer::getTicketByCategory($Cid,$curid);
	$objResponse->addAssign("ticketMsg","innerHTML",$option);

	// group option
	$groupOption = Customer::getGroup($Cid);
	$objResponse->addAssign("groupMsg","innerHTML",$groupOption);
	
	$objResponse->addScript("relateByGroup(document.getElementById('groupid').value)");
	return $objResponse->getXML();
}

function relateByGroup($groupId){
	$objResponse = new xajaxResponse();
	// customer option
	//$customerOption = Customer::getCustomer($groupId);
	//$objResponse->addAssign("customerMsg","innerHTML",$customerOption);

	// account option
	$accountOption = Customer::getAccount($groupId);
	$objResponse->addAssign("accountMsg","innerHTML",$accountOption);
	return $objResponse->getXML();
}

function curCustomerDetail($customername) {
	global $locate;
	$customerid = Customer::getCustomerid($customername);
	$html = Table::Top($locate->Translate("edit_record"),'formEditInfo');
	$html .= Customer::formEdit($customerid,'customer');
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign('formEditInfo', "style.visibility", "visible");
	$objResponse->addAssign('formEditInfo', "innerHTML", $html);
	return $objResponse->getXML();
}

function updateCurTicket($f) {
	global $locate,$db;
	$objResponse = new xajaxResponse();
	
	if(trim($f['ticketcategoryid']) == 0 || trim($f['ticketid']) == 0 || trim($f['customerid']) == 0){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	$validParentTicket = true;
	if($f['parent_id'] != '') {
		if(!preg_match('/^[\d]*$/',$f['parent_id'])){
			$objResponse->addAlert($locate->Translate("Parent TicketDetail ID must be integer"));
			return $objResponse->getXML();
		}
		//验证写入的parent_id 是否存在
		$validParentTicket = Customer::validParentTicketId($f['parent_id']);
	}

	$oriResult = Customer::getOriResult($f['id']);

	$respOk = Customer::updateCurTicket($f);

	$accountid = Customer::getAccountid();
	
	if($respOk){
		if(!$validParentTicket) {
			$objResponse->addAlert($locate->Translate("Update Success,but Parent TicketDetail ID is not exists"));
		}

		$new_assign = '';
		if($f['assignto'] != 0) {
			$new_assign = Customer::getAssignToName($f['assignto']);
		}

		$ori_assign = '';
		if($oriResult['assignto'] != 0) {
			$ori_assign = Customer::getAssignToName($oriResult['assignto']);
		}

		// track the ticket_op_logs
		if($oriResult['status'] != $f['status']) {
			Customer::ticketOpLogs('update','status',$oriResult['status'],$f['status'],$new_assign,$f['groupid']);
		}

		if($oriResult['assignto'] != $f['assignto']) {
			Customer::ticketOpLogs('update','assignto',$ori_assign,$new_assign,$new_assign,$f['groupid']);
		}

		$html = Table::Top($locate->Translate("My Tickets"),"formCurTickets");
		$html .= astercrm::createTikcetGrid($accountid,'agent_tickets',0,ROWSXPAGE,'','','','formCurTickets');
		$html .= Table::Footer();
		$objResponse->addAssign("formCurTickets", "innerHTML", $html);
		$objResponse->addAssign("formCurTickets", "style.visibility", "visible");
		$objResponse->addAssign("formCurTickets", "innerHTML", $html);
		$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "hidden");
		$objResponse->addAssign("formTicketDetailDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_update"));
	}
	
	return $objResponse->getXML();
}

function getMsgInCampaign($form) {
	global $locate,$config;
	$objResponse = new xajaxResponse();	

	$curagentdata = array();
	$agentDatas = Customer::getAgentData();
	$dagentflag = 0;
	$acount = 0;

	while ($agentDatas->fetchInto($agentData)) {

		if($agentData['cretime'] < $form['clkPauseTime']){
			return $objResponse;
		}
		$acount++;

		if(strstr(strtolower($agentData['agent']),'agent') ){
			if((trim(strtolower($agentData['agent_status'])) != 'unavailable' && trim($agentData['agent_status']) != 'invalid')){
				$dagentflag = 1;
				$curagentdata[$agentData['queuename']]['type'] = 'agent';
				$curagentdata[$agentData['queuename']]['status'] = $agentData['agent_status'];
				$curagentdata[$agentData['queuename']]['ispaused'] = $agentData['ispaused'];
				$curagentdata[$agentData['queuename']]['isdynamic'] = $agentData['isdynamic'];
				$curagentdata[$agentData['queuename']]['data'] = $agentData['data'];
				$curagentdata[$agentData['queuename']]['agent'] = $agentData['agent'];
			}else{				
				continue;
			}
		}else{
			if(is_array($curagentdata[$agentData['queuename']]) && $curagentdata[$agentData['queuename']]['type'] == 'agent'){
				continue;
			}
			if(strtolower(trim($agentData['agent'])) ==  strtolower(trim($_SESSION['curuser']['channel']))){ //直接用channel做memmber如:sip/8000
				$curagentdata[$agentData['queuename']]['type'] = 'channel';
			}
			$curagentdata[$agentData['queuename']]['status'] = $agentData['agent_status'];
			$curagentdata[$agentData['queuename']]['ispaused'] = $agentData['ispaused'];
			$curagentdata[$agentData['queuename']]['isdynamic'] = $agentData['isdynamic'];
			$curagentdata[$agentData['queuename']]['data'] = $agentData['data'];
			$curagentdata[$agentData['queuename']]['agent'] = $agentData['agent'];			
		}
		
	}

	if(($acount == 0 || $form['uniqueid'] != '') && $form['clkPauseTime'] > 0){
		return $objResponse;
	}
	if($dagentflag){
		$objResponse->addAssign("spanDialList", "style.display", "none");
		$objResponse->addAssign("misson", "style.display", "none");
	}else{
		$objResponse->addAssign("spanDialList", "style.display", "");
		$objResponse->addAssign("misson", "style.display", "");
	}
	
	$tableHtml = '';
	//print_r($_SESSION['curuser']['campaign_queue']);exit;

	
	//if set the param require_reason_when_pause to yes,can not auto pause the queue;
	$autoSetToPause = '';
	if($config['system']['require_reason_when_pause'] == 'yes') {
		$autoSetToPause = ' disabled ';
	}

	foreach($_SESSION['curuser']['campaign_queue'] as $row) {
		if ($row['use_ext_chan'] == 'yes') $row['queue_context'] = '#use_ext_chan#';
		if(is_array($curagentdata[$row['queuename']]) && !((strtolower($curagentdata[$row['queuename']]['status']) == 'unavailable' || $curagentdata[$row['queuename']]['status'] == 'invalid') && $curagentdata[$row['queuename']]['type'] == 'agent')){ //在队列中或是动态座席可用的情况

			$campaignSpan = '<span style="float:left;cursor:pointer;color:green"  id="campaign-'.$row['id'].'" title="'.$curagentdata[$row['queuename']]['data'].'">'.$row['campaignname'].'('.$row['queuename'].')</span>';
			if($curagentdata[$row['queuename']]['isdynamic']){			
				$loginSpan = '<span id="span-campaign-login-'.$row['id'].'"><input id="autoSetPause_'.$row['id'].'_queue" '.$autoSetToPause.' type="checkbox" onclick="if(this.checked)xajax_setAutoPauseQueue(\''.$row['id'].'\',\'checked\');else xajax_setAutoPauseQueue(\''.$row['id'].'\',\'\')" '.$row['autopause'].'>'.$locate->translate('auto pause').'&nbsp;<a id="campaign-login-'.$row['id'].'" href="javascript:void(null)" title="logoff" onclick="xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('logoff').']</a></span>';
				
			}else{
				if($curagentdata[$row['queuename']]['type'] == 'agent' ){
					$loginSpan = '<input id="autoSetPause_'.$row['id'].'_queue" '.$autoSetToPause.' type="checkbox" onclick="if(this.checked)xajax_setAutoPauseQueue(\''.$row['id'].'\',\'checked\');else xajax_setAutoPauseQueue(\''.$row['id'].'\',\'\')" '.$row['autopause'].'>'.$locate->translate('auto pause').'&nbsp;<span id="span-campaign-login-'.$row['id'].'">['.$locate->translate('Agent').']</span>';
				}elseif( !$curagentdata[$row['queuename']]['isdynamic']){
					$loginSpan = '<input id="autoSetPause_'.$row['id'].'_queue" '.$autoSetToPause.' type="checkbox" onclick="if(this.checked)xajax_setAutoPauseQueue(\''.$row['id'].'\',\'checked\');else xajax_setAutoPauseQueue(\''.$row['id'].'\',\'\')" '.$row['autopause'].'>'.$locate->translate('auto pause').'&nbsp;<span id="span-campaign-login-'.$row['id'].'">['.$locate->translate('Static Member').']</span>';
				}else{
					$loginSpan = '<input id="autoSetPause_'.$row['id'].'_queue" '.$autoSetToPause.' type="checkbox" onclick="if(this.checked)xajax_setAutoPauseQueue(\''.$row['id'].'\',\'checked\');else xajax_setAutoPauseQueue(\''.$row['id'].'\',\'\')" '.$row['autopause'].'>'.$locate->translate('auto pause').'&nbsp;<span id="span-campaign-login-'.$row['id'].'"><a id="campaign-login-'.$row['id'].'" href="javascript:void(null)" title="logoff" onclick="xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('logoff').']</a></span>';
				}				
			}

			if($curagentdata[$row['queuename']]['ispaused']){
				$campaignSpan = '<span style="float:left;cursor:pointer;color:#30569D"  id="campaign-'.$row['id'].'" title="'.$curagentdata[$row['queuename']]['data'].'">'.$row['campaignname'].'('.$row['queuename'].')</span>';
				
				if($curagentdata[$row['queuename']]['type'] == 'agent' ){
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="continuea" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('continue').']</a></span>';
				}elseif($curagentdata[$row['queuename']]['type'] == 'channel'){
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="continuec" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('continue').']</a></span>';
				}else{
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="continue" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('continue').']</a></span>';
				}
			}else{
				if($curagentdata[$row['queuename']]['type'] == 'agent' ){
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="pausea" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('pause').']</a></span>';
				}elseif($curagentdata[$row['queuename']]['type'] == 'channel'){
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="pausec" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('pause').']</a></span>';
				}else{
					$pauseSpan = '<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="pause" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('pause').']</a></span>';
				}
			}

			$tableHtml .= '<div id="campaignDiv-'.$row['id'].'" style="clear:both;"><span style="float:right">'.$loginSpan.'&nbsp;&nbsp;'.$pauseSpan.'</span>'.$campaignSpan.'&nbsp;&nbsp;&nbsp;</div>';
		}else{
			$tableHtml .= '<div style="clear:both;" id="campaignDiv-'.$row['id'].'" ><span style="float:right"><span id="span-campaign-login-'.$row['id'].'"><input id="autoSetPause_'.$row['id'].'_queue" '.$autoSetToPause.' type="checkbox" onclick="if(this.checked)xajax_setAutoPauseQueue(\''.$row['id'].'\',\'checked\');else xajax_setAutoPauseQueue(\''.$row['id'].'\',\'\')" '.$row['autopause'].'>'.$locate->translate('auto pause').'&nbsp;<a id="campaign-login-'.$row['id'].'" href="javascript:void(null)" title="login" onclick="xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('login').']</a></span>&nbsp;&nbsp;<span id="span-campaign-pause-'.$row['id'].'" ><a id="campaign-pause-'.$row['id'].'" href="javascript:void(null)" title="logoff" onclick="if(this.title == \'logoff\'){alert(\''.$locate->translate('Not in the queue').'\');return;} xajax_queueAgentControl(\''.$row['queuename'].'\',this.title,\''.$row['queue_context'].'\',\''.$curagentdata[$row['queuename']]['agent'].'\');">['.$locate->translate('pause').']</a></span></span><span style="float:left;color:blue" id="campaign-'.$row['id'].'">'.$row['campaignname'].'('.$row['queuename'].')&nbsp;&nbsp;&nbsp;</span> </div>';
		}
	}
	$objResponse->addAssign("clkPauseTime","value", date("Y-m-d H:i:s"));
	$objResponse->addAssign("divGetMsgInCampaign","innerHTML",$tableHtml);

	//if set the param require_reason_when_pause to yes,can not auto pause the queue;
	if($config['system']['require_reason_when_pause'] == 'yes') {
		foreach($_SESSION['curuser']['campaign_queue'] as $key=>$tmp) {
			//print_r($tmp);exit;
			$_SESSION['curuser']['campaign_queue'][$tmp['id']]['autopause'] = '';
			$objResponse->addAssign("autoSetPause_".$tmp['id']."_queue","checked",false);
		}
	}
	return $objResponse->getXML();
}

function queueAgentControl($queueno,$action,$context,$agent=''){//echo $agent;exit;
	global $locate,$config,$db;
	$myAsterisk = new Asterisk();	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	$objResponse = new xajaxResponse();

	if($agent == ''){
		if($context == ''){
			if ($_SESSION['curuser']['group']['incontext'] != '' ) $context = $_SESSION['curuser']['group']['incontext'];
			else $context = $config['system']['incontext'];		
		}
		if($context == '#use_ext_chan#'){
			$agentstr = $_SESSION['curuser']['channel'];
			if(trim($agentstr) == ''){
				$objResponse->addAlert($locate->translate("please set up your channel !"));
				return $objResponse;
			}
		}else{

			$agentstr = 'Local/'.$_SESSION['curuser']['extension'].'@'.$context.'/n';
		}
	}else{
		$agentstr = $agent;
	}

	if($action == 'login'){
		$cmd = "queue add member $agentstr to $queueno";
	}elseif($action == 'logoff'){
		$cmd = "queue remove member $agentstr from $queueno";
	}elseif($action == 'pause'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			//$objResponse = new xajaxResponse();
			$html = Table::Top($locate->Translate("Pause Reasion"),"formRequiredReasionDiv");
			$html .= Customer::formRequireReasion($queueno,$context,$agent);
			$html .= Table::Footer();
			$objResponse->addAssign("formRequiredReasionDiv", "style.visibility", "visible");
			$objResponse->addAssign("formRequiredReasionDiv", "innerHTML", $html);
			
			return $objResponse->getXML();
		}
//		print_R($_SESSION['asterisk']['paramdelimiter'] == '|');exit;
		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,1);
		}else{
			$cmd = "queue pause member $agentstr queue $queueno";
		}
	}elseif($action == 'continue'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			Customer::savePauseToContinue($queueno);
		}

		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,0);
		}else{
			$cmd = "queue pause member $agentstr queue $queueno";
		}
		$cmd = "queue unpause member $agentstr queue $queueno";
	}elseif($action == 'pausea'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			//$objResponse = new xajaxResponse();
			$html = Table::Top($locate->Translate("Pause Reasion"),"formRequiredReasionDiv");
			$html .= Customer::formRequireReasion($queueno,$context,$agent);
			$html .= Table::Footer();
			$objResponse->addAssign("formRequiredReasionDiv", "style.visibility", "visible");
			$objResponse->addAssign("formRequiredReasionDiv", "innerHTML", $html);
			
			return $objResponse->getXML();
		}

		$agentstr = 'Agent/'.$_SESSION['curuser']['agent'];
		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,1);
		}else{
			$cmd = "queue pause member $agentstr queue $queueno";
		}
	}elseif($action == 'continuea'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			Customer::savePauseToContinue($queueno);
		}

		$agentstr = 'Agent/'.$_SESSION['curuser']['agent'];
		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,0);
		}else{
			$cmd = "queue unpause member $agentstr queue $queueno";
		}
	}elseif($action == 'pausec'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			//$objResponse = new xajaxResponse();
			$html = Table::Top($locate->Translate("Pause Reasion"),"formRequiredReasionDiv");
			$html .= Customer::formRequireReasion($queueno,$context,$agent);
			$html .= Table::Footer();
			$objResponse->addAssign("formRequiredReasionDiv", "style.visibility", "visible");
			$objResponse->addAssign("formRequiredReasionDiv", "innerHTML", $html);
			
			return $objResponse->getXML();
		}

		//$agentstr = $_SESSION['curuser']['channel'];
		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,1);
		}else{
			$cmd = "queue pause member $agentstr queue $queueno";
		}
	}elseif($action == 'continuec'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			Customer::savePauseToContinue($queueno);
		}

		//$agentstr = $_SESSION['curuser']['channel'];
		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agentstr,0);
		}else{
			$cmd = "queue unpause member $agentstr queue $queueno";
		}
	}

	if(!empty($cmd)){	
		$res = $myAsterisk->Command($cmd);//print_r($res);exit;
	}
	if(strstr($res['data'],'failed')){
		if($action == 'pausea'){
			$action == 'pause';
		}elseif($action == 'continuea'){
			$action == 'continue';
		}
		$objResponse->addAlert($locate->translate($action).' '.$locate->translate('failed'));	
	}else{
		$sql = "SELECT * FROM campaign WHERE queuename = '".$queueno."' AND groupid='".$_SESSION['curuser']['groupid']."' AND enable= 1";
		$res = & $db->query($sql);
		while ($res->fetchInto($row)) {
			if($action == 'login'){
				$objResponse->addAssign("campaign-login-".$row['id'],"innerHTML",'['.$locate->translate('logoff').']');
				$objResponse->addAssign("campaign-login-".$row['id'],"title",'logoff');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'pause');
				#$objResponse->addAssign("campaign-pause-".$row['id'],"style.color",'');

				$objResponse->addAssign("campaign-".$row['id'],"style.color",'green');
			}elseif($action == 'logoff'){
				$objResponse->addAssign("campaign-login-".$row['id'],"innerHTML",'['.$locate->translate('login').']');

				$objResponse->addAssign("campaign-login-".$row['id'],"title",'login');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'logoff');
				#$objResponse->addAssign("campaign-pause-".$row['id'],"style.color",'FFFFFF');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'blue');
				$objResponse->addAssign("campaign-".$row['id'],"style.cursor",'');
				$objResponse->addAssign("campaign-".$row['id'],"title",'');
			}elseif($action == 'pause'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continue');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
			}elseif($action == 'continue'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('pause').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'pause');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'green');
			}elseif($action == 'pausea'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continuea');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
			}elseif($action == 'continuea'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('pause').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'pausea');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'green');
			}elseif($action == 'pausec'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continuec');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
			}elseif($action == 'continuec'){
				$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('pause').']');
				$objResponse->addAssign("campaign-pause-".$row['id'],"title",'pausec');
				$objResponse->addAssign("campaign-".$row['id'],"style.color",'green');
			}
		}
	}
	$objResponse->addAssign("clkPauseTime","value", date("Y-m-d H:i:s"));
	return $objResponse;
}

function requireReasionWhenPause($f){
	global $locate,$config,$db;
	$agent = $f['require_reasion_agent'];
	$queueno = $f['require_reasion_queueno'];
	$context = $f['require_reasion_context'];
	
	$objResponse = new xajaxResponse();

	//add a record to table pause_reasion
	$saveResult = Customer::savePauseReasion($f['require_reasion_queueno'],'pause',$f['require_reasion']);
	
	if(!$saveResult) {
		$objResponse->addAlert('Save Pause Reasion Failed');
	} else {
		$objResponse->addAssign("formRequiredReasionDiv","style.visibility",'hidden');
		$objResponse->addAssign("formRequiredReasionDiv","innerHTML",'');

		$myAsterisk = new Asterisk();	
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		
		$action = 'pause';
		if($agent == ''){
			if($context == ''){
				if ($_SESSION['curuser']['group']['incontext'] != '' ) $context = $_SESSION['curuser']['group']['incontext'];
				else $context = $config['system']['incontext'];		
			}

			$agentstr = 'Local/'.$_SESSION['curuser']['extension'].'@'.$context.'/n';
		}else{
			$agentstr = $agent;
		}

		if($action == 'pause'){
			if($_SESSION['asterisk']['paramdelimiter'] == '|'){
				$res = $myAsterisk->queuePause($queueno,$agentstr,1);
			}else{
				$cmd = "queue pause member $agentstr queue $queueno";
			}
		}else if($action == 'pausea'){
			$agentstr = 'Agent/'.$_SESSION['curuser']['agent'];
			if($_SESSION['asterisk']['paramdelimiter'] == '|'){
				$res = $myAsterisk->queuePause($queueno,$agentstr,1);
			}else{
				$cmd = "queue pause member $agentstr queue $queueno";
			}
		}else if($action == 'pausec'){
			if($_SESSION['asterisk']['paramdelimiter'] == '|'){
				$res = $myAsterisk->queuePause($queueno,$agentstr,1);
			}else{
				$cmd = "queue pause member $agentstr queue $queueno";
			}
		}

		if(!empty($cmd)){	
			$res = $myAsterisk->Command($cmd);
		}
		if(strstr($res['data'],'failed')){
			if($action == 'pausea'){
				$action == 'pause';
			}elseif($action == 'continuea'){
				$action == 'continue';
			}
			$objResponse->addAlert($locate->translate($action).' '.$locate->translate('failed'));	
		}else{
			$sql = "SELECT * FROM campaign WHERE queuename = '".$queueno."' AND groupid='".$_SESSION['curuser']['groupid']."' AND enable= 1";
			$res = & $db->query($sql);
			while ($res->fetchInto($row)) {
				if($action == 'pause'){
					$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
					$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continue');
					$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
				}elseif($action == 'pausea'){
					$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
					$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continuea');
					$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
				}elseif($action == 'pausec'){
					$objResponse->addAssign("campaign-pause-".$row['id'],"innerHTML",'['.$locate->translate('continue').']');
					$objResponse->addAssign("campaign-pause-".$row['id'],"title",'continuec');
					$objResponse->addAssign("campaign-".$row['id'],"style.color",'#30569D');
				}
			}
		}
		$objResponse->addAssign("clkPauseTime","value", date("Y-m-d H:i:s"));
	}
	
	return $objResponse;
}

function setAutoPauseQueue($id,$action){
	$objResponse = new xajaxResponse();
	if($id != ''){
		if($action == 'checked'){
			$_SESSION['curuser']['campaign_queue'][$id]['autopause'] = 'checked';
		}else{
			$_SESSION['curuser']['campaign_queue'][$id]['autopause'] = '';
		}
	}
	return $objResponse;
}

function skipDiallist($dialnumber,$diallistid){
	global $locate;
	$objResponse = new xajaxResponse();
	$row = astercrm::getRecordByID($diallistid,'diallist'); 
	if($row['dialnumber'] != ''){
		$row['callresult'] = 'skip';
		astercrm::deleteRecord($row['id'],"diallist");
		$row['dialednumber'] = $phoneNum;
		$row['dialedby'] = $_SESSION['curuser']['extension'];
		$row['trytime'] = $row['trytime'] + 1;
		astercrm::insertNewDialedlist($row);
	}else{
		$objResponse->addAlert($locate->translate("Option failed"));
		return $objResponse;
	}
	$objResponse->addScript("xajax_clearPopup()");
	$objResponse->loadXML(getPrivateDialListNumber($_SESSION['curuser']['extension']));
	return $objResponse;
}

function SendSmsForm($sendType,$objId){
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Send SMS"),"formSendSMS");
	$html .= Customer::createSMSForm($sendType,$objId);
	$html .= Table::Footer();
	$objResponse->addAssign("formSendSMS", "innerHTML", $html);
	$objResponse->addAssign("formSendSMS", "style.visibility", "visible");
	return $objResponse->getXML();
}

function SendSMS($f){
	global $db,$locate,$config;
	$objResponse = new xajaxResponse();
	if($f['sender'] == '') {
		$objResponse->addAlert($locate->translate('sender can not be empty'));
		return $objResponse;
	}
	
	require_once('astercc-sms.class.php');
	$SendSMS = new SendSMSclass();
	$f['sender'] = str_replace("+","%2b",$f['sender']);
	$f['SMSmessage'] = str_replace("+","%2b",$f['SMSmessage']);
	$result = $SendSMS->SendSMS($f['sender'],$conf['SMSmessage']);

	if($result == '-1') {//发送失败
		$objResponse->addAlert($locate->translate('Send Error'));
	} else {//发送成功
		$sentsResult = Customer::insertSentSms($f);//记录已发送的sms
		$objResponse->addAlert($locate->translate('Send Success'));
	}
	return $objResponse;
	
}

function templateChange($curval){
	global $db;
	$objResponse = new xajaxResponse();
	if($curval == '') {
		$objResponse->addAssign('SMSmessage','innerHTML');
		$objResponse->addAssign('SMSmessage','disabled',false);
	} else {
		$templateResult = Customer::getTemplateById($curval);
		$objResponse->addAssign('SMSmessage','innerHTML',$templateResult['content']);
		$objResponse->addScript('calculateMessage("");');
		if($templateResult['is_edit'] == 'yes') {
			$objResponse->addAssign('SMSmessage','disabled',false);
		} else {
			$objResponse->addAssign('SMSmessage','disabled',true);
		}
		
	}
	return $objResponse;
}


function addNewTicket(){
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("add_ticket"),"formTicketDetailDiv"); 			
	$html .= Customer::addNewTicket($customerid);
	$html .= Table::Footer();
	$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "visible");
	$objResponse->addAssign("formTicketDetailDiv", "innerHTML", $html);
	$objResponse->addScript("relateBycategoryID(document.getElementById('ticketcategoryid').value)");
	return $objResponse->getXML();
}

function viewSubordinateTicket($pid){
	global $locate;
	$html = Table::Top( $locate->Translate("view_subordinate_ticketdetails"),"formSubordinateTicketDiv"); 
	$html .= Customer::subordinateTicket($pid);
	$html .= Table::Footer();
	// End edit zone
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formSubordinateTicketDiv", "style.visibility", "visible");
	$objResponse->addAssign("formSubordinateTicketDiv", "innerHTML", $html);
	return $objResponse->getXML();
	
}

$xajax->processRequests();