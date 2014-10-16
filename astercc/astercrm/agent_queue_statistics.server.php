<?php
/*******************************************************************************
* agent_queue_statistics.server.php
********************************************************************************/
require_once ("agent_queue_statistics.common.php");
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
					<th width="25%">'.$locate->translate('Account').'</th>
					<th width="25%">'.$locate->translate('Queue').'</th>
					<th width="25%">'.$locate->translate('Pause Nums').'</th>
					<th width="25%">'.$locate->translate('Pause Time').'</th>
				</tr>';
	$innerHTML = readReport($aFormValues['groupid'],$aFormValues['agent_username'],$aFormValues['sdate'],$aFormValues['edate'],'both');
	if($innerHTML == '') {
		$html .= '<tr>
					<td width="25%">&nbsp;</td>
					<td width="25%">&nbsp;</td>
					<td width="25%">&nbsp;</td>
					<td width="25%">&nbsp;</td>
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
	$sql = "SELECT * FROM astercrm_account WHERE 1 ";
	if($groupid != null) {
		$sql .= " AND groupid=$groupid ";
	}
	astercrm::events($sql);
	$result = & $db->query($sql);
	return $result;
}


function readReport($groupid,$agent_username,$sdate,$edate){
	global $db;
	$sql = "SELECT * FROM agent_queue_log WHERE cretime > '".$sdate."' AND cretime < '".$edate."' ";
	if($groupid == 0 && $agent_username == '') {
	} else if($groupid != 0 && $agent_username == ''){
		$sql .= " AND account IN (SELECT username FROM astercrm_account WHERE groupid = $groupid) ";
	} else {
		$sql .= " AND account = '".$agent_username."' ";
	}
	$sql .= " AND action='pause' ";
	astercrm::events($sql);
	$result = & $db->query($sql);

	$resultArray = array();
	while($result->fetchInto($row)){
		if(!isset($resultArray[$row['account'].'-'.$row['queue']])) {
			$resultArray[$row['account'].'-'.$row['queue']] = array();
		}
		$resultArray[$row['account'].'-'.$row['queue']]['pauseNums'] ++;
		$resultArray[$row['account'].'-'.$row['queue']]['pauseTime'] += $row['pausetime'];
	}
	
	$html = '';
	$i=0;
	foreach($resultArray as $key=>$val){
		if($i==0) {
			$html .= '<tr class="row1">';
		} else {
			$html .= '<tr class="row0">';
		}
		$kArray = explode('-',$key);
		$html .= '
				<td>'.$kArray[0].'</td>
				<td>'.$kArray[1].'</td>
				<td>'.$val['pauseNums'].'</td>
				<td>'.astercrm::FormatSec($val['pauseTime']).'</td>
			</tr>';
		$i++;
	}
	return $html;
}

$xajax->processRequests();
?>
