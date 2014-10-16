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
require_once ("checkout.common.php");
require_once ("db_connect.php");
require_once ("checkout.grid.inc.php");
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');


function init($curpeer){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$peers = array();
	if ($_SESSION['curuser']['usertype'] == 'admin'){
		// set all reseller first
		$reseller = astercrm::getAll('resellergroup');
		$objResponse->addScript("addOption('resellerid','"."0"."','".$locate->Translate("All")."');");
		while	($reseller->fetchInto($row)){
			if($config['synchronize']['display_synchron_server']){
				$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
			}
			
			$objResponse->addScript("addOption('resellerid','".$row['id']."','".$row['resellername']."');");
		}

	}else if ($_SESSION['curuser']['usertype'] == 'reseller'){
		// set one reseller
		$objResponse->addScript("addOption('resellerid','".$_SESSION['curuser']['resellerid']."','".""."');");

		// set all group
		$group = astercrm::getAll('accountgroup','resellerid',$_SESSION['curuser']['resellerid']);
		$objResponse->addScript("addOption('groupid','"."0"."','"."All"."');");
		while	($group->fetchInto($row)){
			if($config['synchronize']['display_synchron_server']){
				$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
			}
			
			$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
		}

		// get limit status and creditlimit
		$reseller = astercc::readRecord("resellergroup","id",$_SESSION['curuser']['resellerid']);
		if ($reseller){
			if ($reseller['limittype'] == ""){
				$html = 	$locate->Translate("Limit Type").":".$locate->Translate("No limit");
			}else{
				$html = $locate->Translate("Limit Type").$accountgroup['limittype']."(".$accountgroup['creditlimit'].")";
			}

			$html = $locate->Translate("Limit Type").$reseller['limittype']."(".$reseller['creditlimit'].")";
			$objResponse->addAssign("divLimitStatus","innerHTML",$html);
		}

	}else{
		$objResponse->addScript("addOption('resellerid','".$_SESSION['curuser']['resellerid']."','".""."');");
		$objResponse->addScript("addOption('groupid','".$_SESSION['curuser']['groupid']."','".""."');");

		$clid = astercrm::getAll('clid','groupid',$_SESSION['curuser']['groupid']);
		$objResponse->addScript("addOption('sltBooth','"."0"."','".$locate->Translate("All")."');");

		while	($clid->fetchInto($row)){
			if($config['synchronize']['display_synchron_server']){
				$clidDisplay = astercrm::getSynchronDisplay($row['id'],$row['clid']);
			}
			
			if ($curpeer == $row['clid'])
				$objResponse->addScript("addOption('sltBooth','".$row['clid']."','".$clidDisplay."',true);");
			else
				$objResponse->addScript("addOption('sltBooth','".$row['clid']."','".$clidDisplay."');");
		}
		$objResponse->addScript("addOption('sltBooth','-1','".$locate->Translate("Callback")."');");
		// get limit status and creditlimit
		$accountgroup = astercc::readRecord("accountgroup","id",$_SESSION['curuser']['groupid']);
		if ($accountgroup){
			if ($accountgroup['limittype'] == ""){
				$html = 	$locate->Translate("Limit Type").":". $locate->Translate("No limit");
			}else{
				$html = $locate->Translate("Limit Type").$accountgroup['limittype']."(".$accountgroup['creditlimit'].")";
			}
			$objResponse->addAssign("divLimitStatus","innerHTML",$html);
		}
	}
	//去除了控制chekcout按钮是否显示的代码//20110303 donnie
    //if($config['system']['useHistoryCdr'] == 1){
       //$objResponse->addScript("document.getElementById('btnCheckOut').style.display='none';");
	//}else if($config['system']['useHistoryCdr'] == 0){
       //$objResponse->addScript("document.getElementById('btnCheckOut').style.display='block';");
	//}

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	return $objResponse;
}

function setGroup($resellerid){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$res = astercrm::getAll("accountgroup",'resellerid',$resellerid);
	//添加option
	$objResponse->addScript("addOption('groupid','"."0"."','".$locate->Translate("All")."');");
	while ($res->fetchInto($row)) {
		if($config['synchronize']['display_synchron_server']){
			$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
		}

		$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
	}
	return $objResponse;
}

function parseReport($myreport,$answeredNum = '',$a2bcost=-1){
	global $locate;
	$ary['recordNum'] = $myreport['recordNum'];
	$ary['seconds'] = $myreport['seconds'];
	$ary['credit'] = $myreport['credit'];
	$ary['callshopcredit'] = $myreport['callshopcredit'];
	$ary['resellercredit'] = $myreport['resellercredit'];
	$hour = intval($myreport['seconds'] / 3600);
	$minute = intval($myreport['seconds'] % 3600 / 60);
	$sec = intval($myreport['seconds'] % 60);
	$mins = intval($myreport['seconds'] / 60);

	$amins = intval($myreport['billsec_leg_a'] / 60);
	if($a2bcost >= 0){
		$a2bcost = round($a2bcost,4);
	}

	if($sec > 0) $mins+=1;
	$asr = round($answeredNum/$myreport['recordNum'] * 100,2);
	$acd = round($myreport['seconds']/$answeredNum/60,1);

	if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
		$html .= $locate->Translate("Calls").": ".$myreport['recordNum']."<br>";
		$html .= $locate->Translate("Answered").": ".$answeredNum."<br>";
		//$html .= $locate->Translate("Billsec").": ".$myreport['seconds']."(".$hour.":".$minute.":".$sec.")<br>";
		$html .= $locate->Translate("Billsec").": ".astercrm::FormatSec($myreport['seconds'])."(".$mins."min)<br>";

		$html .= $locate->Translate("Billsec_Leg_A").": ".astercrm::FormatSec($myreport['billsec_leg_a'])."(".$amins."min)<br>";

		if($answeredNum != ''){
			$html .= $locate->Translate("ASR").": ".$asr."%<br>";
			$html .= $locate->Translate("ACD").": ".$acd." Min<br>";
		}
		$html .= $locate->Translate("Amount").": ".$myreport['credit']."<br>";
		if($a2bcost >= 0){
			$html .= $locate->Translate("A2B Cost").": ".$a2bcost."<br>";
		}else{
			$html .= $locate->Translate("Callshop").": ".$myreport['callshopcredit']."<br>";
		}
		$html .= $locate->Translate("Reseller Cost").": ".$myreport['resellercredit']."<br>";

		if($a2bcost >= 0){
			$html .= $locate->Translate("Profit").": ". ($myreport['credit'] - $a2bcost) ."<br>";
			$ary['markup'] = $myreport['credit'] - $a2bcost;
		}else{
			$html .= $locate->Translate("Markup").": ". ($myreport['callshopcredit'] - $myreport['resellercredit']) ."<br>";
			$ary['markup'] = $myreport['credit'] - $myreport['callshopcredit'];
		}		
		
	}else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
		$html .= $locate->Translate("Calls").": ".$myreport['recordNum']."<br>";
		//$html .= $locate->Translate("Billsec").": ".$myreport['seconds']."(".$hour.":".$minute.":".$sec.")<br>";
		$html .= $locate->Translate("Billsec").": ".astercrm::FormatSec($myreport['seconds'])."(".$mins."min)<br>";

		$html .= $locate->Translate("Billsec_Leg_A").": ".astercrm::FormatSec($myreport['billsec_leg_a'])."(".$amins."min)<br>";

		if($answeredNum != ''){
			$html .= $locate->Translate("ASR").": ".$asr."%<br>";
			$html .= $locate->Translate("ACD").": ".$acd." Min<br>";
		}
		$html .= $locate->Translate("Amount").": ".$myreport['credit']."<br>";
		if($a2bcost >= 0){
			$html .= $locate->Translate("A2B Cost").": ".$a2bcost."<br>";
			$html .= $locate->Translate("Profit").": ". ($myreport['credit'] - $a2bcost) ."<br>";
			$ary['markup'] = $myreport['credit'] - $a2bcost;
		}else{
			$html .= $locate->Translate("Callshop").": ".$myreport['callshopcredit']."<br>";
			$html .= $locate->Translate("Markup").": ". ($myreport['credit'] - $myreport['callshopcredit']) ."<br>";
			$ary['markup'] = $myreport['credit'] - $myreport['callshopcredit'];
		}		
		
	}else if ($_SESSION['curuser']['usertype'] == 'operator'){
		$html .= $locate->Translate("Calls").": ".$myreport['recordNum']."<br>";
		//$html .= $locate->Translate("Billsec").": ".$myreport['seconds']."(".$hour.":".$minute.":".$sec.")<br>";
		$html .= $locate->Translate("Billsec").": ".astercrm::FormatSec($myreport['seconds'])."(".$mins."min)<br>";
		
		$html .= $locate->Translate("Billsec_Leg_A").": ".astercrm::FormatSec($myreport['billsec_leg_a'])."(".$amins."min)<br>";

		if($a2bcost >= 0){
			//$html .= $locate->Translate("A2B Cost").": ".$a2bcost."<br>";
		}

		$html .=  $locate->Translate("Callshop").": ".$myreport['credit']."<br>";
		if($answeredNum != ''){
			$html .= $locate->Translate("ASR").": ".$asr."%<br>";
			$html .= $locate->Translate("ACD").": ".$acd." Min<br>";
		}
	}

	$result['html'] = $html;
	$result['data'] = $ary;
	return $result;
}

function setClid($groupid){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$res = astercrm::getAll("clid",'groupid',$groupid);
	//添加option
	$objResponse->addScript("addOption('sltBooth','"."0"."','".$locate->Translate("All")."');");
	while ($res->fetchInto($row)) {
		if($config['synchronize']['display_synchron_server']){
			$clidDisplay = astercrm::getSynchronDisplay($row['id'],$row['clid']);
		}
		
		$objResponse->addScript("addOption('sltBooth','".$row['clid']."','".$clidDisplay."');");
	}
	$objResponse->addScript("addOption('sltBooth','-1','".$locate->Translate("Callback")."');");
	return $objResponse;
}

function listCDR($aFormValues){
	global $locate,$config;
	
	$reseller = astercrm::getAll('resellergroup');
    
	while ($reseller->fetchInto($row)){
		$id=$row['id'];
		$reseller_arr[$id]=$row['resellername'];
		}
		
	
    $group = astercrm::getAll('accountgroup');
    while	($group->fetchInto($row)){
    	$id=$row['id'];
		$group_arr[$id]=$row['groupname'];
		}
	$objResponse = new xajaxResponse();
	
	$objResponse->addAssign("divMsg","style.visibility","hidden");

	if ($aFormValues['sltBooth'] == '' && $aFormValues['hidCurpeer'] != ''){
		$aFormValues['sltBooth'] = $aFormValues['hidCurpeer'];
	}

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

	if ($aFormValues['reporttype'] == "text"){
		$aFormValues['sdate']=$syear."-".$smonth."-".$sday.' '.$shours.':'.$smins;
		$aFormValues['edate']=$eyear."-".$emonth."-".$eday.' '.$ehours.':'.$emins;
	}else{
		$aFormValues['sdate']=$syear."-".$smonth."-".$sday;
		$aFormValues['edate']=$eyear."-".$emonth."-".$eday;
	}

	if ($aFormValues['listType'] == "none"){
		$res = astercc::readReport($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate']);

		$answeredNum = astercc::readAnsweredNum($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate']);

		$a2bcost = -1;
		if($config['a2billing']['enable']){
			$a2bcost = Customer::readA2Breport($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate']);
		}

		if ($res->fetchInto($myreport)){
			$result = parseReport($myreport,$answeredNum,$a2bcost); 
			$html .= $result['html'];
		}

		$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		return $objResponse;
	}elseif ($aFormValues['listType'] == "sumyear"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
			$html = "";
		}else{
			for ($year = $syear; $year<=$eyear;$year++){
			
				$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$year-1-1 00:00:00","$year-12-31 23:59:59");
				$answeredNum = astercc::readAnsweredNum($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$year-1-1 00:00:00","$year-12-31 23:59:59");

				$a2bcost = -1;
				if($config['a2billing']['enable']){
					$a2bcost = Customer::readA2Breport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$year-1-1 00:00:00","$year-12-31 23:59:59");
				}

				if ($res->fetchInto($myreport)){
					$html .= "<div class='box'>";
					$html .= "$year :<br/>";
					$html .= "<div>";
					$result = parseReport($myreport,$answeredNum,$a2bcost); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['credit'] += $result['data']['credit'];
					$ary['callshopcredit'] += $result['data']['callshopcredit'];
					$ary['resellercredit'] += $result['data']['resellercredit'];
					$ary['billsec_leg_a'] += $myreport['billsec_leg_a'];
					$answeredNumTotal += $answeredNum;
					if($config['a2billing']['enable']){
						$a2bcostTotal += $a2bcost;
					}
				}
			}

			if(!$config['a2billing']['enable']){
				$a2bcostTotal = -1;
			}
			$html .= "<div class='box'>";
			$html .= "total :<br/>";
			$html .= "<div>";
			$result = parseReport($ary,$answeredNumTotal,$a2bcostTotal); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		}
		return $objResponse;

	}elseif ($aFormValues['listType'] == "summonth"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			//for ($year = $syear; $year<=$eyear;$year++){
				$year = $syear;
				for ($month = 1;$month<=12;$month++){
					$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$year-$month-1 00:00:00","$year-$month-31 23:59:59");
					$answeredNum = astercc::readAnsweredNum($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$year-$month-1 00:00:00","$year-$month-31 23:59:59");

					$a2bcost = -1;
					if($config['a2billing']['enable']){
						$a2bcost = Customer::readA2Breport($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$year-$month-1 00:00:00","$year-$month-31 23:59:59");
					}
					if ($res->fetchInto($myreport)){
						$html .= "<div class='box'>";
						$html .= "$year-$month :<br/>";
						$html .= "<div>";
						$result = parseReport($myreport,$answeredNum,$a2bcost); 
						$html .= $result['html'];
						$html .= "</div>";
						$html .= "</div>";
						$ary['recordNum'] += $result['data']['recordNum'];
						$ary['seconds'] += $result['data']['seconds'];
						$ary['credit'] += $result['data']['credit'];
						$ary['callshopcredit'] += $result['data']['callshopcredit'];
						$ary['resellercredit'] += $result['data']['resellercredit'];
						$answeredNumTotal += $answeredNum;
						if($config['a2billing']['enable']){
							$a2bcostTotal += $a2bcost;
						}
					}
				}
			//}
			if(!$config['a2billing']['enable']){
				$a2bcostTotal = -1;
			}
			$html .= "<div class='box'>";
			$html .= "total :<br/>";
			$html .= "<div>";
			$result = parseReport($ary,$answeredNumTotal,$a2bcostTotal); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		}
      
		return $objResponse;
	}elseif ($aFormValues['listType'] == "sumday"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			for ($day = $sday;$day<=31;$day++){
				$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$syear-$smonth-$day 00:00:00","$syear-$smonth-$day 23:59:59");
				$answeredNum = astercc::readAnsweredNum($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$syear-$smonth-$day 00:00:00","$syear-$smonth-$day 23:59:59");

				$a2bcost = -1;
				if($config['a2billing']['enable']){
					$a2bcost = Customer::readA2Breport($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$syear-$smonth-$day 00:00:00","$syear-$smonth-$day 23:59:59");
				}

				if ($res->fetchInto($myreport)){
					$html .= "<div class='box'>";
					$html .= "$syear-$smonth-$day :<br/>";
					$html .= "<div>";
					$result = parseReport($myreport,$answeredNum,$a2bcost); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['credit'] += $result['data']['credit'];
					$ary['callshopcredit'] += $result['data']['callshopcredit'];
					$ary['resellercredit'] += $result['data']['resellercredit'];
					$answeredNumTotal += $answeredNum;
					if($config['a2billing']['enable']){
						$a2bcostTotal += $a2bcost;
					}
				}
			}

			if(!$config['a2billing']['enable']){
				$a2bcostTotal = -1;
			}
			$html .= "<div class='box'>";
			$html .= "total :<br/>";
			$html .= "<div>";
			$result = parseReport($ary,$answeredNumTotal,$a2bcostTotal); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";
			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		}

		return $objResponse;
	}elseif ($aFormValues['listType'] == "sumhour"){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionFlash('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		}else{
			for ($hour = 0;$hour<=23;$hour++){
				$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$syear-$smonth-$sday $hour:00:00","$syear-$smonth-$sday $hour:59:59");
				$answeredNum = astercc::readAnsweredNum($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$syear-$smonth-$sday $hour:00:00","$syear-$smonth-$sday $hour:59:59");

				$a2bcost = -1;
				if($config['a2billing']['enable']){
					$a2bcost = Customer::readA2Breport($aFormValues['resellerid'],$aFormValues['groupid'],$aFormValues['sltBooth'], "$syear-$smonth-$sday $hour:00:00","$syear-$smonth-$sday $hour:59:59");
				}

				if ($res->fetchInto($myreport)){
					$html .= "<div class='box'>";
					$html .= "$syear-$smonth-$sday $hour:<br/>";
					$html .= "<div>";
					$result = parseReport($myreport,$answeredNum,$a2bcost); 
					$html .= $result['html'];
					$html .= "</div>";
					$html .= "</div>";
					$ary['recordNum'] += $result['data']['recordNum'];
					$ary['seconds'] += $result['data']['seconds'];
					$ary['credit'] += $result['data']['credit'];
					$ary['callshopcredit'] += $result['data']['callshopcredit'];
					$ary['resellercredit'] += $result['data']['resellercredit'];
					$answeredNumTotal += $answeredNum;
					if($config['a2billing']['enable']){
						$a2bcostTotal += $a2bcost;
					}
				}
			}

			if(!$config['a2billing']['enable']){
				$a2bcostTotal = -1;
			}
			$html .= "<div class='box'>";
			$html .= "total :<br/>";
			$html .= "<div>";
			$result = parseReport($ary,$answeredNumTotal,$a2bcostTotal); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";

			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		}
		return $objResponse;
	}elseif ($aFormValues['listType'] == "sumdest"){
		
		$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate'],'destination');
		$html .= '<form action="" name="f" id="f">';
		$html .= '<table width="99%">';
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
					$html .= '<tr>
					<td width="60"></td>
					<td width="160">'.$locate->Translate("Destination").'</td>
					<td width="120">'.$locate->Translate("Calls").'</td>
					<td width="120">'.$locate->Translate("Billsec").'</td>
					<td width="120">'.$locate->Translate("Sells").'</td>
					<td width="70">'.$locate->Translate("Callshop Cost").'</td>
					<td width="90">'.$locate->Translate("Reseller Cost").'</td>
					<td width="90">'.$locate->Translate("Markup").'</td>
					</tr>';
			
		}else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
					$html .= '<tr>
					<td width="60"></td>
					<td width="160">'.$locate->Translate("Destination").'</td>
					<td width="120">'.$locate->Translate("Calls").'</td>
					<td width="120">'.$locate->Translate("Billsec").'</td>
					<td width="120">'.$locate->Translate("Sells").'</td>
					<td width="70">'.$locate->Translate("Callshop Cost").'</td>
					<td width="90">'.$locate->Translate("Markup").'</td>
					</tr>';
			
		}else if ($_SESSION['curuser']['usertype'] == 'operator'){
					$html .= '<tr>
					<td width="60"></td>
					<td width="160">'.$locate->Translate("Destination").'</td>
					<td width="120">'.$locate->Translate("Calls").'</td>
					<td width="120">'.$locate->Translate("Billsec").'</td>
					<td width="120">'.$locate->Translate("Sells").'</td>
					</tr>';
		}

		while	($res->fetchInto($row)){
			if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
				$html .= '<tr>
						<td width="60"></td>
						<td width="160">'.$row['destination'].'</td>
						<td width="120">'.$row['recordNum'].'</td>
						<td width="120">'.astercrm::FormatSec($row['seconds']).'</td>
						<td width="120">'.$row['credit'].'</td>
						<td width="120">'.$row['callshopcredit'].'</td>
						<td width="120">'.$row['resellercredit'].'</td>
						<td width="120">'.($row['callshopcredit'] - $row['resellercredit']).'</td>
						</tr>';	
			}else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
					$html .= '<tr>
						<td width="60"></td>
						<td width="160">'.$row['destination'].'</td>
						<td width="120">'.$row['recordNum'].'</td>
						<td width="120">'.astercrm::FormatSec($row['seconds']).'</td>
						<td width="120">'.$row['credit'].'</td>
						<td width="120">'.$row['callshopcredit'].'</td>
						<td width="120">'.($row['credit'] - $row['callshopcredit']).'</td>
						</tr>';	
			}else if ($_SESSION['curuser']['usertype'] == 'operator'){
					$html .= '<tr>
						<td width="60"></td>
						<td width="160">'.$row['destination'].'</td>
						<td width="120">'.$row['recordNum'].'</td>
						<td width="120">'.astercrm::FormatSec($row['seconds']).'</td>
						<td width="120">'.$row['credit'].'</td>
						</tr>';		
				}
		}
		$html .= '</table>';
		$html .= '</form>';
	
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			if ($aFormValues['reporttype'] == "flash"){
				$objResponse->addScript("actionPie1('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		            $html='';		
			}else{
				
				$objResponse->addAssign("divUnbilledList","innerHTML",$html);
			}
		}
		else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			if ($aFormValues['reporttype'] == "flash"){
				$objResponse->addScript("actionPie2('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
			 $html='';	
			}else{
				$objResponse->addAssign("divUnbilledList","innerHTML",$html);
			}
		}
		else if ($_SESSION['curuser']['usertype'] == 'operator'){
		if ($aFormValues['reporttype'] == "flash"){
			$objResponse->addScript("actionPie3('".$aFormValues["resellerid"]."','".$aFormValues["groupid"]."','".$aFormValues["sltBooth"]."','".$aFormValues["sdate"]."','".$aFormValues["edate"]."','".$aFormValues["listType"]."','".$aFormValues["hidCurpeer"]."');");
		 $html='';	
		}else{
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
		}
			
		}
		
		return $objResponse;
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
			$html .= "total :<br/>";
			$html .= "<div>";
			$result = parseReport($ary); 
			$html .= $result['html'];
			$html .= "</div>";
			$html .= "</div>";

			$html .= "<div style='clear:both;'></div>";
			$objResponse->addAssign("divUnbilledList","innerHTML",$html);
			
			
		}
		return $objResponse;
	}
		
	
	$records = astercc::readAll($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'],$aFormValues['sdate'],$aFormValues['edate']);

	$html .= '<form action="" name="f" id="f">';
	$html .= '<table width="99%">';
	$html .= '<tr>
			<td width="60"></td>
			<td width="120">'.$locate->Translate("Calldate").'</td>
			<td width="120">'.$locate->Translate("Clid").'</td>
			<td width="120">'.$locate->Translate("Dst").'</td>
			<td width="70">'.$locate->Translate("Duration").'</td>
			<td width="90">'.$locate->Translate("Disposition").'</td>
			<td width="70">'.$locate->Translate("Billsec").'</td>
			<td width="160">'.$locate->Translate("Destination").'</td>
			<td width="360">'.$locate->Translate("Rate").'</td>
 			<td width="120">'.$locate->Translate("Price").'</td>
 			<td width="70">'.$locate->Translate("Status").'</td>
			<td width="300">'.$locate->Translate("Note").'</td>
			</tr>';
	$html .= '<tr>
			<td width="60">
				<input type="checkbox" onclick="ckbAllOnClick(this);" id="ckbAll[]" name="ckbAll[]">'.$locate->Translate("All").'
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
 			<td></td>
 			<td></td>
			<td></td>
			</tr>';
	
	while	($records->fetchInto($mycdr)){
		$price = '';
		$ratedesc = '';
		$trstyle = '';
		$ratedesc = astercc::readRateDesc($mycdr['memo']);

		$callshop_cost = 0;
		$reseller_cost = 0;

		if ($_SESSION['curuser']['usertype'] == 'operator') {

		} else if($_SESSION['curuser']['usertype'] == 'groupadmin'){

			$callshop_cost = $mycdr['callshopcredit'];

		} else if($_SESSION['curuser']['usertype'] == 'admin'){

			$callshop_cost = $mycdr['callshopcredit'];
			$reseller_cost = $mycdr['resellercredit'];

		}
		if($mycdr['setfreecall'] == 'yes') $trstyle = 'style="background:#d5c59f;"';
		$html .= '	<tr align="left" id="tr-'.$mycdr['id'].'" '.$trstyle.'>
						<td align="right">
							<input type="checkbox" id="ckb[]" name="ckb[]" value="'.$mycdr['id'].'" onclick="ckbOnClick(this);">
							<input type="hidden" id="price-'.$mycdr['id'].'" name="price-'.$mycdr['id'].'" value="'.$mycdr['credit'].'">
							<input type="hidden" id="callshop-'.$mycdr['id'].'" name="callshop-'.$mycdr['id'].'" value="'.$callshop_cost.'">
							<input type="hidden" id="reseller-'.$mycdr['id'].'" name="reseller-'.$mycdr['id'].'" value="'.$reseller_cost.'">
							<input type="hidden" id="free-'.$mycdr['id'].'" name="free-'.$mycdr['id'].'" value="'.$mycdr['setfreecall'].'">
						</td>
						<td>'.$mycdr['calldate'].'</td>
						<td>'.$mycdr['src'].'</td>
						<td>'.$mycdr['dst'].'</td>
						<td>'.astercrm::FormatSec($mycdr['duration']).'</td>
						<td>'.$mycdr['disposition'].'</td>
						<td>'.astercrm::FormatSec($mycdr['billsec']).'</td>
						<td>'.$mycdr['destination'].'</td>
						<td>'.$ratedesc.'</td>';
		if ($_SESSION['curuser']['usertype'] == 'operator') {
			$html .=  '<td>'.$mycdr['credit'].'</td>';
		}else if($_SESSION['curuser']['usertype'] == 'groupadmin') {
			$html .=  '<td>'.$mycdr['credit'].'<br>'.'('.$callshop_cost.')'.'</td>';
		}else if($_SESSION['curuser']['usertype'] == 'admin') {
			$html .=  '<td>'.$mycdr['credit'].'<br>'.'('.$callshop_cost.')'.'<br>'.'('.$reseller_cost.')</td>';
		}

		if ($peer == '-1'){
			if ($mycdr['dst'] == $mycdr['src']){
				//lega
				$addon = ' [lega]';
			}else{
				//legb
				$addon = ' [legb]';
			}
		}

		if ($mycdr['userfield'] == 'UNBILLED')
			$html .='<td bgcolor="red">'.$mycdr['userfield'].$addon.'</td>';
		else
			$html .='<td>'.$mycdr['userfield'].$addon.'</td>';

		$html .= '<td>'.$mycdr['note'].'</td></tr>
					<tr bgcolor="gray">
						<td colspan="12" height="1"></td>
					</tr>
				';
		$i++;
	}

	$html .= '<tr>
			<td width="60">
				<input type="checkbox" onclick="ckbAllOnClick(this);" id="ckbAll[]" name="ckbAll[]">'.$locate->Translate("All").'
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
 			<td></td>
 			<td></td>
 			<td></td>
			<td></td>
			</tr>';
	$html .= '</table>';
	$html .= '</form>';

	$objResponse->addAssign("divUnbilledList","innerHTML",$html);
	$objResponse->addAssign("spanTotal","innerHTML",0);
	$objResponse->addAssign("spanrealTotal","innerHTML",0);
	$objResponse->addAssign("spanCallshopCost","innerHTML",0);
	$objResponse->addAssign("spanResellerCost","innerHTML",0);
	return $objResponse;
}

function checkOut($aFormValues){
	global $locate;
	$objResponse = new xajaxResponse();
	$amounta = 0.00;
	$amountb = 0.00;
	$callshop = 0.00;
	$reseller = 0.00;
	if ($aFormValues['ckb']){
		foreach ($aFormValues['ckb'] as $id){
			$res =  astercc::setBilled($id);
			$amounta += $aFormValues['price-'.$id];
			if($aFormValues['free-'.$id] == 'no'){
               $amountb += $aFormValues['price-'.$id];
			}
			$callshop += $aFormValues['callshop-'.$id];
			$reseller += $aFormValues['reseller-'.$id];
		}
		$objResponse->addScript("listCDR();");
	    $objResponse->addAssign("spanCurrencyTotal","innerHTML",$locate->Translate("should").":".$amounta." ".$locate->Translate("real").":".$amountb);
	    $objResponse->addAssign("spanCurrencyCallshopCost","innerHTML",$callshop);
	    $objResponse->addAssign("spanCurrencyResellerCost","innerHTML",$reseller);
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

	return $objResponse;
}

function checkoutAll($resellerid,$groupid,$clidid){
	global $locate;

	$objResponse = new xajaxResponse();
	$res =  astercc::setAllBilled($resellerid,$groupid,$clidid);

	$objResponse->addAlert($locate->Translate("booth_cleared"));

	return $objResponse;
}

$xajax->processRequests();
?>
