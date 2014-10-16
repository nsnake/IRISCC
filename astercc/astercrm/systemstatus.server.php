<?php
/*******************************************************************************
* systemstatus.server.php

* Function Desc
	show sip status and active channels

* 功能描述
	提供SIP分机状态信息和正在进行的通道

* Function Desc

	showGrid
	init				初始化页面元素
	showStatus			显示sip分机状态信息
	showChannelsInfo	显示激活的通道信息

* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("systemstatus.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/common.class.php');

/**
*  initialize page elements
*
*/

function init(){
	global $locate,$config;
	$objResponse = new xajaxResponse();

	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addAssign("AMIStatudDiv", "innerHTML", $locate->Translate("AMI_connection_failed"));
	}
	$objResponse->addAssign("msgChannelsInfo", "value", $locate->Translate("msgChannelsInfo"));
	
	////set time intervals of check system status
	$check_interval = 2000;
	if ( is_numeric($config['system']['status_check_interval']) ) {
		$check_interval = $config['system']['status_check_interval'] * 1000;
		$objResponse->addAssign("check_interval","value",$check_interval);
	}
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	return $objResponse;
}


function listCommands(){
	global $config;

	$objResponse = new xajaxResponse();
	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addAssign("AMIStatudDiv", "innerHTML", $locate->Translate("AMI_connection_failed"));
	}else{
		print_r($myAsterisk->ListCommands());
	}

	return $objResponse;
}

/**
*  show extension status
*  @return	objResponse		object		xajax response object
*/

function showStatus($curhover){
	
	$objResponse = new xajaxResponse();
	$html .= "<br><br><br><br>";
	$html .= asterEvent::checkExtensionStatus(0,'table',$curhover);
	$objResponse->addAssign("divStatus", "innerHTML", $html);
	$objResponse->addScript("menuFix();");
	return $objResponse;
}


/**
*  initialize page elements
*  @return	objResponse		object		xajax response object
*/

function showChannelsInfo(){
	global $locate,$config,$db;
	
	$aDyadicArray[] = array($locate->Translate("src"),$locate->Translate("dst"),$locate->Translate("srcchan"),$locate->Translate("dstchan"),$locate->Translate("starttime"),$locate->Translate("answertime"),$locate->Translate("disposition"));

	$objResponse = new xajaxResponse();

	if($config['system']['eventtype'] == 'curcdr'){
		if($_SESSION['curuser']['usertype'] == 'admin'){		
			$curcdr = astercrm::getAll("curcdr");			
		}else{
			//print_r($_SESSION['curuser']['memberExtens']);exit;
			$curcdr = astercrm::getGroupCurcdr();
		}

		while	($curcdr->fetchInto($row)){
			$systemCDR[] = array($row["src"],$row["dst"],$row["srcchan"],$row["dstchan"],$row["starttime"],$row["answertime"],$row["disposition"]);
		}

		$activeCalls = "&nbsp;&nbsp;".count($systemCDR)."&nbsp;".$locate->Translate("active calls");
		$objResponse->addAssign("divActiveCalls", "innerHTML", $activeCalls);
		$systemChannels = common::generateTabelHtml(array_merge($aDyadicArray , $systemCDR));		
		$objResponse->addAssign("channels", "innerHTML", nl2br(trim($systemChannels)));
		return $objResponse;
	}

	$channels = split(chr(13),asterisk::getCommandData('show channels verbose'));
	
/*
	if ($channels == null){
			$objResponse->addAssign("channels", "innerHTML", "can not connect to AMI, please check config.php");
			return $objResponse;
	}
*/	$channels = split(chr(10),$channels[1]);
	//trim the first two records and the last three records

	//	array_pop($channels); 
	array_pop($channels); 
	$activeCalls = array_pop($channels); 
	$activeChannels = array_pop($channels); 

	array_shift($channels); 
	$title = array_shift($channels); 
	$title = split("_",implode("_",array_filter(split(" ",$title))));
	$myInfo[] = $title;

	foreach ($channels as $channel ){
		if (strstr($channel," Dial")) {
			$myItem = split("_",implode("_",array_filter(split(" ",$channel))));
			$myInfo[] = $myItem;
		}
	}
	
	$myChannels = common::generateTabelHtml($myInfo);
	
	$objResponse->addAssign("divActiveCalls", "innerHTML", $activeCalls);

	$objResponse->addAssign("channels", "innerHTML", nl2br(trim($myChannels)));
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
#	echo $spyexten;exit;

	$myAsterisk->chanSpy($exten,$spyexten,$pam,$_SESSION['asterisk']['paramdelimiter']);
	#$myAsterisk->chanSpy($exten,"agent/1000",$pam,$_SESSION['asterisk']['paramdelimiter']);
	//$objResponse->addAlert($spyexten);
	return $objResponse;

}

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
	return $objResponse;
}

function dial($phoneNum,$first = ''){
	global $config,$locate;

	$myAsterisk = new Asterisk();	
	if ($first == ''){
		$first = $config['system']['firstring'];
	}

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");

	if ($first == 'caller'){	//caller will ring first
		$strChannel = "local/".$_SESSION['curuser']['extension']."@".$config['system']['incontext']."/n";

		if ($config['system']['allow_dropcall'] == true){
			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$phoneNum,
								'Context'=>$config['system']['outcontext'],
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$strVariable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$phoneNum));
		}else{
			$myAsterisk->sendCall($strChannel,$phoneNum,$config['system']['outcontext'],1,NULL,NULL,30,$phoneNum,NULL,$_SESSION['curuser']['accountcode']);
		}
	}else{
		$strChannel = "local/".$phoneNum."@".$config['system']['outcontext']."/n";

		if ($config['system']['allow_dropcall'] == true){

/*
	coz after we use new method to capture dial event
	there's no good method to make both leg display correct clid for now
	so we comment these lines
*/
			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$_SESSION['curuser']['extension'],
								'Context'=>$config['system']['incontext'],
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$strVariable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$_SESSION['curuser']['extension']));
		}else{
			$myAsterisk->sendCall($strChannel,$_SESSION['curuser']['extension'],$config['system']['incotext'],1,NULL,NULL,30,$_SESSION['curuser']['extension'],NULL,NULL);
		}
	}

	return;
}

function barge($srcchan,$dstchan){
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}

	$group_info = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");

	if ($group_info['incontext'] != '' ) $incontext = $group_info['incontext'];
	else $incontext = $config['system']['incontext'];
	//if ($group_info['outcontext'] != '' ) $outcontext = $group_info['outcontext'];
	//else $outcontext = $config['system']['outcontext'];

	$strChannel = "local/".$_SESSION['curuser']['extension']."@".$incontext."/n";
	$myAsterisk->Originate($strChannel,'','',1,'meetme',$_SESSION['curuser']['extension'].$_SESSION['asterisk']['paramdelimiter']."pqdx",30,$_SESSION['curuser']['extension'],NULL,$_SESSION['curuser']['accountcode']);

	$myAsterisk->Redirect($srcchan,$dstchan,$_SESSION['curuser']['extension'],"astercc-barge","1");
	return $objResponse;
}

$xajax->processRequests();
?>
