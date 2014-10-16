<?php
/*******************************************************************************
* checkout.server.php

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
require_once ("user_online.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');


function init(){
	global $locate;
	$objResponse = new xajaxResponse();
	$peers = array();
	if ($_SESSION['curuser']['usertype'] == 'admin'){
		// set all group first
		$group = astercrm::getAll('astercrm_accountgroup');
		//print_r($group);exit;
		$objResponse->addScript("addOption('groupid',0,'".$locate->Translate("All")."');");
		while($group->fetchInto($row)){
			$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
		}
		$objResponse->addScript("addOption('agent_username','".""."','".$locate->Translate("All")."');");
	}else{
		// set one group
		$objResponse->addScript("addOption('groupid','".$_SESSION['curuser']['groupid']."','".$_SESSION['curuser']['group']['groupname']."');");

		// set all agent
		$agent = getAgentRecords($_SESSION['curuser']['groupid']);
		$objResponse->addScript("addOption('agent_username','".""."','".$locate->Translate("All")."');");
		while($agent->fetchInto($row)){
			$objResponse->addScript("addOption('agent_username','".$row['username']."','".$row['username']."');");
		}
	}

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	return $objResponse;
}

function listReport($aFormValues){
	global $locate;
	$objResponse = new xajaxResponse();
	
	list ($syear,$smonth,$sday,$stime) = split("[ -]",$aFormValues['sdate']);
	$syear = (int)$syear;
	$smonth = (int)$smonth;
	$sday = (int)$sday;
	list($shours,$smins) = split("[ :]",$stime);
	$shours = (int)$shours;
	if($shours == 0) $shours = '00';
	$smins = (int)$smins;
	if($smins == 0) $smins = '00';

	list ($eyear,$emonth,$eday,$etime) = split("[ -]",$aFormValues['edate']);
	$eyear = (int)$eyear;
	$emonth = (int)$emonth;
	$eday = (int)$eday;
	list($ehours,$emins) = split("[ :]",$etime);
	$ehours = (int)$ehours;
	if($ehours == 0) $ehours = '00';
	$emins = (int)$emins;
	if($emins == 0) $emins = '00';

	$ary = array();
	$aFormValues['sdate']=$syear."-".$smonth."-".$sday.' '.$shours.':'.$smins;
	$aFormValues['edate']=$eyear."-".$emonth."-".$eday.' '.$ehours.':'.$emins;

	$html = '<table class="adminlist" border="1" style="width:800px">
					<tr>
						<th width="30%">'.$locate->translate('agent_name').'</th>
						<th width="30%">'.$locate->translate('total_onlinetime').'</th>
						<th width="40%">'.$locate->translate('rate').'</th>
					</tr>';
	$innerHTML = readReport($aFormValues['groupid'],$aFormValues['agent_username'], $aFormValues['sdate'],$aFormValues['edate'],'both');
	if($innerHTML == '') {
		$html .= '<tr>
					<td width="30%">&nbsp;</td>
					<td width="30%">&nbsp;</td>
					<td width="40%">&nbsp;</td>
				</tr>';
	} else {
		$html .= $innerHTML;
	}
	$html .= '</table>';
	$objResponse->addAssign('divGeneralList','innerHTML',$html);

	return $objResponse;
}

function speedDate($date_type){
	switch($date_type){
		case "td":
			$start_date = date("Y-m-d")." 00:00";
			$end_date = date("Y-m-d")." 23:59";
			break;
		case "tw":
			$date = date("Y-m-d");
			$end_date = date("Y-m-d",strtotime("$date Sunday"))." 23:59";
			$start_date = date("Y-m-d",strtotime("$end_date -6 days"))." 00:00";
			break;
		case "tm":
			$date = date("Y-m-d");
			$start_date = date("Y-m-01",strtotime($date))." 00:00";
			$end_date = date("Y-m-d",strtotime("$start_date +1 month -1 day"))." 23:59";
			break;
		case "l3m":
			$date = date("Y-m-d");
			$start_date = date("Y-m-01",strtotime("$date - 2 month"))." 00:00";	
			$date = date("Y-m-01");
			$end_date = date("Y-m-d",strtotime("$date +1 month -1 day"))." 23:59";
			break;
		case "ty":
			$start_date = date("Y-01-01")." 00:00";
			$end_date = date("Y-12-31")." 23:59";
			break;
		case "ly":
			$year = date("Y") - 1;
			$start_date = date("$year-01-01")." 00:00";
			$end_date = date("$year-12-31")." 23:59";			
			break;
			
	}

	$objResponse = new xajaxResponse();
	if(isset($start_date)) $objResponse->addAssign("sdate","value",$start_date);

	if(isset($end_date)) $objResponse->addAssign("edate","value",$end_date);
	$objResponse->addScript("listReport();");
	return $objResponse;
}

function getAgent($groupid=null){
	global $locate;
	$objResponse = new xajaxResponse();
	$objResponse->addScript('document.getElementById("agent_username").options.length = 0;');
	if($groupid == 0) {
		$objResponse->addScript('addOption("agent_username","","'.$locate->translate('All').'");');
	} else {
		$result = getAgentRecords($groupid);
		$objResponse->addScript('addOption("agent_username","","'.$locate->translate('All').'");');
		if(!empty($result)) {
			while($result->fetchInto($row)) {
				$objResponse->addScript('addOption("agent_username","'.$row['username'].'","'.$row['username'].'");');
			}
		}
	}
	return $objResponse;
}

function getAgentRecords($groupid = null){
	global $db;
	$sql = "SELECT * FROM astercrm_account WHERE usertype='agent' ";
	if($groupid != null) {
		$sql .= " AND groupid=$groupid ";
	}
	astercrm::events($sql);
	$result = & $db->query($sql);
	return $result;
}


function readReport($groupid,$agent_username,$sdate,$edate){
	global $db;
	$sql = "SELECT username,SUM(`onlinetime`) AS onlinetime FROM agent_online_time WHERE login_time > '".$sdate."' AND logout_time < '".$edate."' ";
	if($groupid == 0 && $agent_username == '') {
	} else if($groupid != 0 && $agent_username == ''){
		$sql .= " AND username IN (SELECT username FROM astercrm_account WHERE groupid = $groupid) ";
	} else {
		$sql .= " AND username = '".$agent_username."' ";
	}
	$sql .= " GROUP BY username";
	astercrm::events($sql);
	$result = & $db->query($sql);
	$minVal = 0;
	$maxVal = 0;
	$resultArray = array();
	while($result->fetchInto($row)){
		if($minVal == 0) {
			$maxVal = $row['onlinetime'];
			$minVal = $row['onlinetime'];
		}
		if($row['onlinetime'] > $maxVal) {
			$maxVal = $row['onlinetime'];
		}
		if($row['onlinetime'] < $minVal) {
			$minVal = $row['onlinetime'];
		}
		$resultArray[] = $row;
	}
	$multiple = round($maxVal/$minVal);
	$html = '';
	$i=0;
	$curlength = 10;
	if($multiple >= 10){
		$curlength = intval(100/$multiple);
	}
	
	foreach($resultArray as $tmp){
		if($i==0) {
			$html .= '<tr class="row1">';
		} else {
			$html .= '<tr class="row0">';
		}
		$html .= '
					<td>'.$tmp['username'].'</td>
					<td>'.astercrm::FormatSec($tmp['onlinetime']).'</td>
					<td><div style="width:300px;"><div style="width:'.($curlength*round($tmp['onlinetime']/$minVal)).'%;background-color:red">&nbsp;</div></div></td>
				</tr>';
		$i++;
	}
	return $html;
}

$xajax->processRequests();
?>
