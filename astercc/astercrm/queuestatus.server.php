<?php
/*******************************************************************************
* queuestatus.server.php

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
require_once ("queuestatus.common.php");
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


/**
*  show extension status
*  @return	objResponse		object		xajax response object
*/

function showStatus($curupdated){
	global $db,$locate;
	//echo $curupdated;exit;
	$objResponse = new xajaxResponse();
	if ($_SESSION['curuser']['usertype'] == 'admin') {
		// display all queue
		$query = "SELECT * FROM queue_name";
	}else{
		// display queue in campaign for group
		$query = "SELECT campaign.groupid, queue_name.* FROM queue_name LEFT JOIN campaign ON queue_name.queuename = campaign.queuename WHERE campaign.groupid = ".$_SESSION['curuser']['groupid'];
	}
	$res = $db->query($query);
	$html = '<table class="groups_channel" cellspacing="0" cellpadding="0" border="0" width="95%"><tbody>';
	$updated = 0;
	while ($res->fetchInto($row)) {
		//"<li></li>"
		$html .= '<tr><th colspan="2">'.$row['data'].'</th></tr>';
		$html .= '<tr><td width="70%"><b>'.$locate->Translate("Members").'</b></td><td><b>'.$locate->Translate("Waiting callers").'</b></td></tr>';
		
		$query = "SELECT * FROM queue_agent WHERE queuename = '".$row['queuename']."' ORDER BY agent ASC";
		$res_agent = $db->query($query);
		$dthtml ='<tr><td valign="top">';
		$dthtml .='<table class="groups_channel" cellspacing="0" cellpadding="0" border="0" width="95%"><tbody>';
		$inusecount = 0;
		$pausedcount = 0;
		$invalidcount = 0;
		$unavailablecount = 0;
		$nocallcount = 0;
		$waittingcount = 0;
		$agenttotal = 0;
		$longestidle = '';
		$longestidletime = 0;

		while ($res_agent->fetchInto($row_agent)) {
			if($updated == 0){
				$updated = $row_agent['cretime'];
			}
			if($updated < $curupdated){
				return $objResponse;
			}
			
			$agenttotal++;
			
			//$idletime = explode('(',$row_agent['data']);
			//print $row_agent['data'];exit;
			if(preg_match("/was.+[0-9]+.+secs/i",$row_agent['data'],$idletime_tmp)){			
				$idletime_tmp = $idletime_tmp['0'];
				preg_match("/[0-9]+/",$idletime_tmp,$idletime);
				$idletime = $idletime['0'];
				if($idletime > $longestidletime && !$row_agent['ispaused'] && strtolower($row_agent['agent_status']) == 'not in use'){
					$longestidle = $row_agent['agent']."($idletime secs)";
					$longestidletime = $idletime;
				}
			}else{
				$nocallcount++;
			}
			
			
			//echo $idletime;exit;

			if(strtolower($row_agent['agent_status']) == 'in use' || strtolower($row_agent['agent_status']) == 'busy'){
				$inusecount++;
			}elseif(strtolower($row_agent['agent_status']) == 'unavailable'){
				$unavailablecount++;
			}elseif(strtolower($row_agent['agent_status']) == 'invalid'){
				$invalidcount++;
			}else{
				if($row_agent['ispaused']){
					$pausedcount++;
				}elseif(strtolower($row_agent['agent_status']) == 'not in use'){
					$waittingcount++;
				}
			}
			
			$logoffBtn = '';
			$able = 'disabled';
			$hable = 'disabled';	
			$dhtml .='<tr><td>';
			if(strstr(strtolower($row_agent['agent']),'agent')){
				$agent = substr($row_agent['agent'],6);

				$logoffBtn .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Logoff").'" onclick="xajax_agentLogoff(\''.$agent.'\');this.disabled=true;"';
				if(strtolower($row_agent['agent_status']) == 'unavailable' || $row_agent['agent_status'] == 'invalid'){
					$logoffBtn .= 'disabled';
				}
				$logoffBtn .= '>';//echo $logoffBtn;exit;
				if(strtolower($row_agent['agent_status']) == 'busy'){
					$query = "SELECT * FROM curcdr WHERE dstchan = '".strtoupper($row_agent['agent'])."'  AND queue='".$row_agent['queuename']."'";
					if($agent_cdr = $db->getRow($query)){
						$dstchan = $agent_cdr['dstchan'];
						if($agent_cdr['disposition'] == 'LINK'){
							$able = '';
						}
						$hable = '';
					}
					
					$query = "SELECT * FROM astercrm_account WHERE agent = '$agent'";
					if($agent_exten = $db->getRow($query)){
						$exten = $agent_exten['extension'];
					}
				}
			}else{
				$logoffBtn .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Logoff").'" onclick="xajax_agentLogoff(\''.$row_agent['agent'].'\',\''.$row_agent['queuename'].'\');this.disabled=true;"';
				if(!$row_agent['isdynamic']){
					$logoffBtn .= 'disabled';
				}
				$logoffBtn .= '>';

				if(strtolower($row_agent['agent_status']) == 'in use'){
					$dstchan = explode('@',$row_agent['agent']);
					$dstchan = $dstchan['0'];
					$exten = explode('/',$dstchan);
					$exten = $exten['1'];
					$query = "SELECT * FROM curcdr WHERE dstchan LIKE  '%/".$exten."-%' AND queue='".$row_agent['queuename']."'";
					
					if($agent_cdr = $db->getRow($query)){
						$dstchan = $agent_cdr['dstchan'];
						if($agent_cdr['disposition'] == 'LINK'){
							$able = '';
						}
						$hable = '';
					}					
				}
			}
			
			$dhtml .= '<input type="button" value="'.$locate->Translate("Spy").'" onclick="xajax_chanspy(\''.$_SESSION['curuser']['extension'].'\',\''.$exten.'\')" '.$able.'>';
			$dhtml .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Whisper").'" onclick="xajax_chanspy(\''.$_SESSION['curuser']['extension'].'\',\''.$exten.'\',\'w\')" '.$able.'>';
			$dhtml .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Hangup").'" onclick="xajax_hangup(\''.$dstchan.'\')" '.$hable.'>';
			
			if($row_agent['ispaused']){
				$dhtml .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Continue").'" title="continue"  onclick="xajax_agentPause(\''.$row_agent['agent'].'\',\''.$row_agent['queuename'].'\',this.title);this.title=\'pause\';this.value=\''.$locate->Translate("Pause").'\'" >';
			}else{
				$dhtml .= '&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Pause").'"  title="pause" onclick="xajax_agentPause(\''.$row_agent['agent'].'\',\''.$row_agent['queuename'].'\',this.title);this.title=\'continue\';this.value=\''.$locate->Translate("Continue").'\'" >';
			}

			$dhtml .= $logoffBtn;

			if(strtolower($row_agent['agent_status']) == 'in use' ||  strtolower($row_agent['agent_status']) == 'busy'){
				$dhtml .= '&nbsp;&nbsp;<span style="background: none repeat scroll 0% 0% rgb(208, 48, 63); width: 1em;">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.$row_agent['data'].'&nbsp;&nbsp;';
			}elseif(strtolower($row_agent['agent_status']) == 'not in use' || strtolower($row_agent['agent_status']) == 'unknown'){
				if($row_agent['ispaused']){
					$dhtml .= '&nbsp;&nbsp;<span style="background: none repeat scroll 0% 0% rgb(0, 0, 0);">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.$row_agent['data'].'</span>&nbsp;&nbsp;';
				}else{
					$dhtml .= '&nbsp;&nbsp;<span style=" background: none repeat scroll 0% 0% rgb(0, 255, 0);">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;'.$row_agent['data'].'</span>&nbsp;&nbsp;';
				}
			}else{
				$dhtml .= '&nbsp;&nbsp;<span style="background: none repeat scroll 0% 0% rgb(218, 218, 218);">&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color:#999999;">&nbsp;'.$row_agent['data'].'</span>&nbsp;&nbsp;';
			}
			$dhtml .= '</td></tr>';
			
		}//<button>Spy</button><button>Whisper</button>
		$dhtml .='</tbody></table></td><td valign="top">';
		$query = "SELECT * FROM queue_caller WHERE queuename = '".$row['queuename']."' ";
		$res_caller = $db->query($query);
		$dhtml .='<table class="groups_channel" cellspacing="0" cellpadding="0" border="0" width="90%"><tbody>';
		while ($res_caller->fetchInto($row_caller)) {
			$dhtml .= "<tr><td>".$row_caller['data']."</td></tr>";
		}

		$agenthtml = "<tr><td><b>".$locate->Translate("agenttotal").":&nbsp;</b>$agenttotal &nbsp;<b>".$locate->Translate("In use").":&nbsp;</b>$inusecount &nbsp;<b>".$locate->Translate("Waitting").":&nbsp;</b>$waittingcount &nbsp;<b>".$locate->Translate("Paused").":&nbsp;</b>$pausedcount &nbsp;<b>".$locate->Translate("Unavailable").":&nbsp;</b>$unavailablecount &nbsp;<b>".$locate->Translate("Invalid").":&nbsp;</b>$invalidcount  &nbsp;<b>".$locate->Translate("has taken no calls yet").":&nbsp;</b>$nocallcount	<div><b>".$locate->Translate("Longest waitting agent").":&nbsp;</b>$longestidle</div></td></tr>";
		$dhtml = $dthtml.$agenthtml.$dhtml;
		$html .= $dhtml."</tbody></table></td></tr>";
		$dhtml = '';
		
	}
	$html .= '</tbody></table>';//echo $html;exit;
	$objResponse->addAssign("channels","innerHTML",$html);
	//echo $updated;exit;
	$objResponse->addAssign("updated","value",$updated);
	return $objResponse;
}


function chanspy($exten,$spyexten,$pam = ''){
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}
	$myAsterisk->chanSpy($exten,"sip/".$spyexten,$pam,$_SESSION['asterisk']['paramdelimiter']);
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


function agentLogoff($agent,$queueno='',$action){
	global $locate,$config;

	$myAsterisk = new Asterisk();	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}
	if($queueno != ''){
		$cmd = "queue remove member $agent from $queueno";
		//echo $cmd;exit;
		$res = $myAsterisk->Command($cmd);
	}else{
		$res = $myAsterisk->agentLogoff($agent);
	}
	
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("updated","value", date("Y-m-d H:i:s"));
	return $objResponse;
}

function agentPause($agent,$queueno='',$action){
	global $locate,$config;

	$myAsterisk = new Asterisk();	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}
	
	if($action == 'pause'){
		if($config['system']['require_reason_when_pause'] == 'yes') {
			savePauseReasion($agent,$queueno,$action,'admin pause');
		}

		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agent,1);
		}else{
			$cmd = "queue pause member $agent queue $queueno";
			$res = $myAsterisk->Command($cmd);
		}
	}else{
		if($config['system']['require_reason_when_pause'] == 'yes') {
			savePauseToContinue($agent,$queueno);
		}

		if($_SESSION['asterisk']['paramdelimiter'] == '|'){
			$res = $myAsterisk->queuePause($queueno,$agent,0);
		}else{
			$cmd = "queue unpause member $agent queue $queueno";
			$res = $myAsterisk->Command($cmd);
		}
	}

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("updated","value", date("Y-m-d H:i:s"));
	return $objResponse;
}

function savePauseReasion($agent,$queueno,$action,$reasion){
	global $db;
	//query the username by extension 
	$tmpArray = explode('@',$agent);
	$curArray = explode('/',$tmpArray[0]);
	$tSql = "SELECT * FROM astercrm_account WHERE extension='".$curArray[1]."' ";
	$accountResult = & $db->getRow($tSql);

	$sql = 
		"INSERT INTO `agent_queue_log` SET 
			action = '".$action."',
			queue = '".$queueno."',
			account = '".$accountResult['username']."',
			reasion = '".$reasion."',
			groupid = '".$accountResult['groupid']."',
			pausetime = 0,
			cretime = now() 
		";
	//astercrm::events($sql);
	$result = & $db->query($sql);
	return $result;
}

function savePauseToContinue($agent,$queueno){
	global $db;
	$tmpArray = explode('@',$agent);
	$curArray = explode('/',$tmpArray[0]);
	$tSql = "SELECT * FROM astercrm_account WHERE extension='".$curArray[1]."' ";
	$accountResult = & $db->getRow($tSql);

	$chkSql = "SELECT * FROM `agent_queue_log` WHERE account='".$accountResult['username']."' ORDER BY cretime DESC LIMIT 1 ; ";
	$chkResult = & $db->getRow($chkSql);
	
	if($chkResult['action'] == 'pause') {
		$sql = 
		"INSERT INTO `agent_queue_log` SET 
			action = 'continue',
			queue = '".$queueno."',
			account = '".$accountResult['username']."',
			reasion = 'admin continue ',
			groupid = '".$accountResult['groupid']."',
			pausetime = 0,
			cretime = now() 
		";
		$saveResult = & $db->query($sql);

		if($saveResult) {
			$pausetime = strtotime(date("Y-m-d H:i:s"))-strtotime($chkResult['cretime']);
			$updateSql = "UPDATE `agent_queue_log` SET pausetime='".$pausetime."' WHERE id='".$chkResult['id']."' ";
			$chkResult = & $db->query($updateSql);
		}
	}
}


$xajax->processRequests();
?>
