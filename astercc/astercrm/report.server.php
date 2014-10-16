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
require_once ("report.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');


function init(){
	global $locate;
	$objResponse = new xajaxResponse();
	$peers = array();
	if ($_SESSION['curuser']['usertype'] == 'admin'){
		// set all group first
		$group = astercrm::getAll('astercrm_accountgroup');
		$objResponse->addScript("addOption('groupid',0,'".$locate->Translate("All")."');");
		while	($group->fetchInto($row)){
			$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
		}

	}else{
		// set one group
		$objResponse->addScript("addOption('groupid','".$_SESSION['curuser']['groupid']."','".""."');");

		// set all account
		$account = astercrm::getGroupMemberListByID($_SESSION['curuser']['groupid']);
		$objResponse->addScript("addOption('accountid','"."0"."','"."All"."');");
		while	($account->fetchInto($row)){
			$objResponse->addScript("addOption('accountid','".$row['id']."','".$row['username']."');");
		}		
	}

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	return $objResponse;
}

function setAccount($groupid){
	global $locate;
	$objResponse = new xajaxResponse();
	$res = astercrm::getAll("astercrm_account",'groupid',$groupid);
	//添加option
	$objResponse->addScript("addOption('accountid','"."0"."','".$locate->Translate("All")."');");
	while ($res->fetchInto($row)) {
		$objResponse->addScript("addOption('accountid','".$row['id']."','".$row['username']."');");
	}
	return $objResponse;
}

function parseReport($myreport,$sep="<br>"){
	global $locate;
	$ary['recordNum'] = $myreport['recordNum'];
	$ary['answeredNum'] = $myreport['answeredNum'];
	$ary['seconds'] = $myreport['seconds'];

	$hour = intval($myreport['seconds'] / 3600);
	$minute = intval($myreport['seconds'] % 3600 / 60);
	$sec = intval($myreport['seconds'] % 60);
	$asr = round($myreport['answeredNum']/$myreport['recordNum'] * 100,2);
	$acd = round($myreport['seconds']/$myreport['answeredNum']/60,1);

	$html .= '&nbsp;&nbsp;'.$locate->Translate("Total Calls").": ".$myreport['recordNum'].$sep;
	$html .= '&nbsp;&nbsp;'.$locate->Translate("Answered Calls").": ".$myreport['answeredNum'].$sep;
	$html .= '&nbsp;&nbsp;'.$locate->Translate("Answered sec").": ".$myreport['seconds']."(".$hour.":".$minute.":".$sec.")".$sep;
	$html .= '&nbsp;&nbsp;'.$locate->Translate("ASR").": ".$asr."%".$sep;
	$html .= '&nbsp;&nbsp;'.$locate->Translate("ACD").": ".$acd." Min<br>";
	
	$result['html'] = $html;
	$result['data'] = $ary;
	return $result;
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

	$res = astercrm::readReport($aFormValues['groupid'],$aFormValues['accountid'], $aFormValues['sdate'],$aFormValues['edate'],'both');

	if ($aFormValues['listType'] == "none"){
		if ($res['all']->fetchInto($myreport)){
			$myreport['answeredNum'] = $res['answered'];
			$result = parseReport($myreport,"&nbsp;"); 
			$html .= "<b>".$result['html']."</b>";
		}
		$objResponse->addAssign("divGeneralList","innerHTML",$html);
		$objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		$objResponse->addScript("document.getElementById('frmFilter').action = '';");
		return $objResponse;
	}elseif ($aFormValues['listType'] == "list"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
			$html = "";
		}else{
			$exportlist = '<input type="submit" value="'.$locate->Translate("export").'">';
			$objResponse->addAssign("exportlist","innerHTML",$exportlist);
			$objResponse->addScript("document.getElementById('frmFilter').action = 'dataexport.php';");
			$rows = astercrm::readReportAgent($aFormValues['groupid'], $aFormValues['accountid'],  $aFormValues["sdate"],$aFormValues["edate"]);
			$html = '<table class="adminlist" border="1" style="width:800px;">';
			$class = 'row1';
			if($rows['type'] == 'grouplist'){
				$html .= '<tr><th>'.$locate->Translate("groupname").'</th>
								<th>'.$locate->Translate("total calls").'</th>
								<th>'.$locate->Translate("answered calls").'</th>
								<th>'.$locate->Translate("answered duration").'</th>
								<th>'.$locate->Translate("ASR").'</th>
								<th>'.$locate->Translate("ACD").'</th></tr>';
				$class = 'row1';
				foreach($rows as $key => $row){
					if($key != 'type'){
						$hour = intval($row['seconds'] / 3600);
						if($hour < 3 ) $hour = '<font color="red">'.$hour;
						$minute = intval($row['seconds'] % 3600 / 60);
						$sec = intval($row['seconds'] % 60);
						$asr = round($row['arecordNum']/$row['recordNum'] * 100,2);
						$acd = round($row['seconds']/$row['arecordNum'],2);
						$acdminute = intval($acd / 60);
						$acdsec = intval($acd % 60);

						$html .= '<tr class="'.$class.'"><td>'.$row['groupname'].'</td>
								<td>'.$row['recordNum'].'</td>
								<td>'.$row['arecordNum'].'</td>
								<td>'.$hour.$locate->Translate("hour").$minute.$locate->Translate("minute").$sec.$locate->Translate("sec").'</td>
								<td>'.$asr.'%</td>
								<td>'.$acdminute.$locate->Translate("minute").$acdsec.$locate->Translate("sec").'</td></tr>';
						if($class == 'row1') $class = 'row0'; else $class = 'row1';
					}
				}

			}elseif($rows['type'] == 'agentlist'){//print_r($rows);exit;
				$group = astercrm::getRecordByID($aFormValues['groupid'],"astercrm_accountgroup");
				$html .= '<tr><th>'.$locate->Translate("groupname").'</th>
								<th>'.$locate->Translate("username").'</th>
								<th>'.$locate->Translate("name").'</th>
								<th>'.$locate->Translate("total calls").'</th>
								<th>'.$locate->Translate("answered calls").'</th>
								<th>'.$locate->Translate("answered duration").'</th>
								<th>'.$locate->Translate("ASR").'</th>
								<th>'.$locate->Translate("ACD").'</th></tr>';
				$class = 'row1';
				foreach($rows as $key => $row){//print_r($rows);exit;
					if($key != 'type'){
						$hour = intval($row['seconds'] / 3600);
						if($hour < 3 ) $hour = '<font color="red">'.$hour;
						$minute = intval($row['seconds'] % 3600 / 60);
						$sec = intval($row['seconds'] % 60);
						$asr = round($row['arecordNum']/$row['recordNum'] * 100,2);
						$acd = round($row['seconds']/$row['arecordNum'],2);
						$acdminute = intval($acd / 60);
						$acdsec = intval($acd % 60);

						$html .= '<tr class="'.$class.'"><td>'.$group['groupname'].'</td>
								<td>'.$row['username'].'</td>
								<td>'.$row['name'].'</td>
								<td>'.$row['recordNum'].'</td>
								<td>'.$row['arecordNum'].'</td>
								<td>'.$hour.$locate->Translate("hour").$minute.$locate->Translate("minute").$sec.$locate->Translate("sec").'</td>
								<td>'.$asr.'%</td>
								<td>'.$acdminute.$locate->Translate("minute").$acdsec.$locate->Translate("sec").'</td></tr>';
						if($class == 'row1') $class = 'row0'; else $class = 'row1';
					}
				}
				
			}elseif($rows['type'] == 'agentsingle'){print_r($rows);exit;
			}

			$objResponse->addAssign("divGeneralList","innerHTML",$html);
			return $objResponse;
			
		}
	}elseif ($aFormValues['listType'] == "sumyear"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
			$html = "";
		}else{
			for ($year = $syear; $year<=$eyear;$year++){
			
				$res = astercrm::readReport($aFormValues['groupid'], $aFormValues['accountid'],  "$year-1-1 00:00:00","$year-12-31 23:59:59",'both');
				
				if ($res['all']->fetchInto($myreport)){
					$myreport['answeredNum'] = $res['answered'];
					$html .= "<div class='box'>";
					$html .= "$year :<br/>";
					$html .= "<div>";
					$result = parseReport($myreport); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['answeredNum'] += $result['data']['answeredNum'];
				}
			}
			$html .= "<div class='box'>";
			$html .= $locate->Translate("total")." :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$objResponse->addAssign("divGeneralList","innerHTML",$html);
		    $objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		    $objResponse->addScript("document.getElementById('frmFilter').action = '';");
		}

	}elseif ($aFormValues['listType'] == "summonth"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			//for ($year = $syear; $year<=$eyear;$year++){
				$year = $syear;
				for ($month = 1;$month<=12;$month++){
					
					$res = astercrm::readReport($aFormValues['groupid'], $aFormValues['accountid'],  "$year-$month-1 00:00:00","$year-$month-31 23:59:59",'both');
				
					if ($res['all']->fetchInto($myreport)){
						$myreport['answeredNum'] = $res['answered'];
						$html .= "<div class='box'>";
						$html .= "$year-$month :<br/>";
						$html .= "<div>";
						$result = parseReport($myreport); 
						$html .= $result['html'];
						$html .= "</div>";
						$html .= "</div>";
						$ary['recordNum'] += $result['data']['recordNum'];
						$ary['seconds'] += $result['data']['seconds'];
						$ary['answeredNum'] += $result['data']['answeredNum'];
					}
				}
			//}
			$html .= "<div class='box'>";
			$html .= $locate->Translate("total")." :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$objResponse->addAssign("divGeneralList","innerHTML",$html);
		    $objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		    $objResponse->addScript("document.getElementById('frmFilter').action = '';");
		}
      
	}elseif ($aFormValues['listType'] == "sumday"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			for ($day = $sday;$day<=31;$day++){
				$res = astercrm::readReport($aFormValues['groupid'], $aFormValues['accountid'],  "$syear-$smonth-$day 00:00:00","$syear-$smonth-$day 23:59:59",'both');
				
				if ($res['all']->fetchInto($myreport)){
					$myreport['answeredNum'] = $res['answered'];
					$html .= "<div class='box'>";
					$html .= "$syear-$smonth-$day :<br/>";
					$html .= "<div>";
					$result = parseReport($myreport); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['answeredNum'] += $result['data']['answeredNum'];
				}
			}
			$html .= "<div class='box'>";
			$html .= $locate->Translate("total")." :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$objResponse->addAssign("divGeneralList","innerHTML",$html);
		    $objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		    $objResponse->addScript("document.getElementById('frmFilter').action = '';");
		}

	}elseif ($aFormValues['listType'] == "sumhour"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			for ($hour = 0;$hour<=23;$hour++){
				$res = astercrm::readReport($aFormValues['groupid'], $aFormValues['accountid'],  "$syear-$smonth-$sday $hour:00:00","$syear-$smonth-$sday $hour:59:59",'both');
				
				if ($res['all']->fetchInto($myreport)){
					$myreport['answeredNum'] = $res['answered'];
					$html .= "<div class='box'>";
					$html .= "$syear-$smonth-$sday $hour:<br/>";
					$html .= "<div>";
					$result = parseReport($myreport); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['answeredNum'] += $result['data']['answeredNum'];
				}
			}
			$html .= "<div class='box'>";
			$html .= $locate->Translate("total")." :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$objResponse->addAssign("divGeneralList","innerHTML",$html);
		    $objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		    $objResponse->addScript("document.getElementById('frmFilter').action = '';");
		}

	}elseif ($aFormValues['listType'] == "sumgroup"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionPieGroup('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			$res = astercc::readReportPie($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate'],'destination',$aFormValues['action'],'limit');
			while($res->fetchInto($row)){
				$iid=$row['gid'];
					if ($aFormValues['resellerid'] == 0 || $aFormValues['resellerid'] == ''){
						$title ="".$reseller_arr[$iid];
					}
					else{
						if ($aFormValues['groupid'] == 0 || $aFormValues['groupid'] == ''){
							$title="".$group_arr[$iid];
						}
						else 
						$title="".$iid;
					}
				$html .= "<div class='box'>";
				$html .= "$title :<br/>";
					$html .= "<div>";
					$result = parseReport($row); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['credit'] += $result['data']['credit'];
					$ary['callshopcredit'] += $result['data']['callshopcredit'];
					$ary['resellercredit'] += $result['data']['resellercredit'];
			
			}
			$html .= "<div class='box'>";
			$html .= $locate->Translate("total")." :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";

			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);	
		    $objResponse->addScript("document.getElementById('exportlist').innerHTML = '';");
		    $objResponse->addScript("document.getElementById('frmFilter').action = '';");
		}		
	}
	return $objResponse;
}

function checkOut($aFormValues){
	$objResponse = new xajaxResponse();
	if ($aFormValues['ckb']){
		foreach ($aFormValues['ckb'] as $id){
			$res =  astercc::setBilled($id);
		}
		$objResponse->addScript("listReport();");
	}
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

$xajax->processRequests();
?>
