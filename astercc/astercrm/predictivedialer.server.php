<?php
/*******************************************************************************
* predictivedialer.server.php

* 账户管理系统后台文件
* predictivedialer management script

* Function Desc
	predictivedialer management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		predictiveDialer

* Revision 0.0461  2008/2/1 20:37:00  last modified by solo
* Desc: fix predictive dialer bug

* Revision 0.0455  2007/10/24 20:37:00  last modified by solo
* Desc: add another dial method: sendCall()

* Revision 0.045  2007/10/18 20:10:00  last modified by solo
* Desc: comment added

*/
require_once ("predictivedialer.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');

function init(){
	global $locate,$config,$db;
	$objResponse = new xajaxResponse();

	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addAssign("divAMIStatus", "innerHTML", $locate->Translate("AMI_connection_failed"));
	}

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));

	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	// get all groups
	if($_SESSION['curuser']['usertype'] == 'admin'){
		$groups = astercrm::getAll("astercrm_accountgroup");
	}else{
		$groups = astercrm::getRecordsByField('id',$_SESSION['curuser']['groupid'],'astercrm_accountgroup');
	}

	while	($groups->fetchInto($group)){
		// get all enabled campaigns
		$query = "SELECT * FROM campaign WHERE enable = 1 AND groupid = ".$group['groupid'];
		$campaigns = $db->query($query);


		$campaignHTML = '';
		while	($campaigns->fetchInto($campaign)){
				// get worktime
				$localtime = localtime(time(), true);
				$wday = $localtime['tm_wday'];
				if($localtime['tm_wday'] == 0){
					$wday = 7;
				}
				$cur = $localtime['tm_hour'].":".$localtime['tm_min'].":".$localtime['tm_sec'];
				$query = " SELECT worktimes.*,worktimepackages.worktimepackage_status FROM worktimepackage_worktimes LEFT JOIN worktimes ON worktimes.id = worktimepackage_worktimes.worktime_id LEFT JOIN worktimepackages ON worktimepackages.id = worktimepackage_worktimes.worktimepackage_id WHERE worktimepackages.worktimepackage_status = 'enable' AND worktimepackage_id = ".$campaign['worktime_package_id']." AND ( starttime <= '{$cur}' AND endtime >= '{$cur}' ) AND (startweek <= {$wday} AND endweek >= {$wday} ) ";
				$worktime = $db->getOne($query);


				// get numbers in diallist
				$query = "SELECT COUNT(*) FROM diallist WHERE campaignid = ".$campaign['id'];
				$phoneNumber = $db->getOne($query);
				
				$has_queue = 0;
				// check if we have a queue in queue_name
				if ($campaign['queuename'] != ""){
					$query = "SELECT id FROM queue_name WHERE queuename = '".$campaign['queuename']."' ";
					$has_queue = $db->getOne($query);
				}

				$status = "";
				$channel_checked = "";
				$queue_checked = "";

				if ($campaign['status'] == "busy"){
					$status = "checked";
				}

				if ($campaign['limit_type'] == "channel"){
					$channel_checked = "checked";
				}else if ($campaign['limit_type'] == "queue"){
					$queue_checked = "checked";
				}

				if($campaign['enablebalance'] == 'strict' && $campaign['balance'] <= 0){
					$curStyle = ' style="color:gray" ';
					$curInputAbled = ' disabled ';
				} else {
					$curStyle = ' ';
					$curInputAbled = '';
				}
				$campaignHTML .= '<div class="group01content" '.$curStyle.'>';
				
				if ($has_queue != 0){
					$campaignHTML .= "<div class='group01l'>".'<img src="images/groups_icon02.gif" width="20" height="20" align="absmiddle" /><acronym title="'.$locate->Translate("inexten").':'.$campaign['inexten'].'&nbsp;|&nbsp;'.$locate->Translate("Outcontext").':'.$campaign['outcontext'].'&nbsp;|&nbsp;'.$locate->Translate("Incontext").':'.$campaign['incontext'].'"> '.$campaign['campaignname'].' ( '.$locate->Translate("queue").': '.$campaign['queuename'].' ) ( <span id="numbers-'.$campaign['id'].'">'.$phoneNumber.'</span> '.$locate->Translate("numbers in dial list").' )</acronym> </div>';
					if(!$worktime && $campaign['worktime_package_id'] != 0){
							$campaignHTML .= '
						<div class="group01r">'.$locate->Translate("not in worktime").'</div>';
					}else{
							$campaignHTML .= '
						<div class="group01r">
						<input type="checkbox" '.$curInputAbled.' onclick="setStatus(this);" id="'.$campaign['id'].'-ckb" '.$status.'>'.$locate->Translate("Start").'
						<input type="radio" '.$curInputAbled.' onclick="setLimitType(this);" id="'.$campaign['id'].'-limittpye" name="'.$campaign['id'].'-limittpye" value="channel" '.$channel_checked.'> '.$locate->Translate("Limited by max calls").' 
						<input type="text" '.$curInputAbled.' value="'.$campaign['max_channel'].'" id="'.$campaign['id'].'-maxchannel" name="'.$campaign['id'].'-maxchannel" size="2" maxlength="3" class="inputlimit" onblur="setMaxChannel(this);">
						<input type="radio" '.$curInputAbled.' onclick="setLimitType(this);" id="'.$campaign['id'].'-limittpye" name="'.$campaign['id'].'-limittpye" value="queue" '.$queue_checked.'> '.$locate->Translate("Limited by agents and multipled by").' 
						<input type="text" '.$curInputAbled.' value="'.$campaign['queue_increasement'].'" id="'.$campaign['id'].'-rate" name="'.$campaign['id'].'-rate" size="4" maxlength="4" class="inputlimit" onblur="setQueueRate(this);">
						</div>';
					}
				}else{
					$campaignHTML .= "<div class='group01l'>".'<img src="images/groups_icon02.gif" width="20" height="20" align="absmiddle" /><acronym title="'.$locate->Translate("inexten").':'.$campaign['inexten'].'&nbsp;|&nbsp;'.$locate->Translate("Outcontext").':'.$campaign['outcontext'].'&nbsp;|&nbsp;'.$locate->Translate("Incontext").':'.$campaign['incontext'].'">'.$campaign['campaignname'].' ( '.$locate->Translate("no queue for this campaign").' ) ( <span id="numbers'.$campaign['id'].'">'.$phoneNumber.'</span> '.$locate->Translate("numbers in dial list").' ) </acronym></div>';
					if(!$worktime && $campaign['worktime_package_id'] != 0){
							$campaignHTML .= '
						<div class="group01r">'.$locate->Translate("not in worktime").'</div>';
					}else{
							$campaignHTML .= '
						<div class="group01r">
						<input type="checkbox" '.$curInputAbled.' onclick="setStatus(this);" id="'.$campaign['id'].'-ckb" '.$status.'>'.$locate->Translate("Start").'
						<input type="radio" '.$curInputAbled.' name="'.$campaign['id'].'-limittpye[]" value="channel" '.$channel_checked.'>
						'.$locate->Translate("Limited by max calls").' 
						<input type="text" '.$curInputAbled.' value="'.$campaign['max_channel'].'" id="'.$campaign['id'].'-maxchannel" name="'.$campaign['id'].'-maxchannel" size="3" maxlength="3" class="inputlimit" onblur="setMaxChannel(this);">
						</div>';
					}
				}
				$campaignHTML .= '</div>';

				$campaignHTML .= '<div class="group01_channel" id="campaign'.$campaign['id'].'" ></div>';
		}

		$divGroup .= '<div class="group01"><img src="images/groups_icon01.gif" align="absmiddle" />'.$group['groupname'].'</div>
												<div id="group'.$group['groupid'].'">'.$campaignHTML.'</div>
											  <div class="group01_channel" id="unknown'.$group['groupid'].'"></div>
											 </div>';
	}
	$objResponse->addAssign("divMain","innerHTML",$divGroup);
	return $objResponse;
}

function setStatus($campaignid, $field, $value){
	global $db;
	$objResponse = new xajaxResponse();
	$query = "UPDATE campaign SET $field = '$value' WHERE id = $campaignid";
	$db->query($query);
	return $objResponse;
}

function predictiveDialer($f){
	global $config,$db,$locate;
	$objResponse = new xajaxResponse();
//print_r($f);exit;
	$aDyadicArray[] = array($locate->Translate("src"),$locate->Translate("dst"),$locate->Translate("srcchan"),$locate->Translate("dstchan"),$locate->Translate("starttime"),$locate->Translate("answertime"),$locate->Translate("disposition"));

	$cDyadicArray[] = array($locate->Translate("src"),$locate->Translate("dst"),$locate->Translate("srcchan"),$locate->Translate("dstchan"),$locate->Translate("starttime"),$locate->Translate("first answertime"),$locate->Translate("answertime"),$locate->Translate("disposition"));

	// 检查系统目前的通话情况

	//if($_SESSION['curuser']['usertype'] == 'admin'){
		$sql = "SELECT curcdr.*,dialedlist.id as did,dialedlist.dialednumber,dialedlist.campaignid,dialedlist.dialedby,dialedlist.channel FROM curcdr LEFT JOIN dialedlist ON curcdr.srcchan=dialedlist.channel OR curcdr.dstchan=dialedlist.channel WHERE curcdr.id > 0 AND dialedlist.channel != '' ORDER by curcdr.id desc";
		$curdiledlist = $db->query($sql);
		//$curcdr = astercrm::getAll("curcdr");
	//}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
	//	$curcdr = astercrm::getGroupCurcdr();
	//}	
	$curdidlist = array();
	while	($curdiledlist->fetchInto($row)){
			
			if($row['did'] > 0){
				if(in_array($row['did'],$curdidlist)){
					continue;
				}else{
					$curdidlist[] = $row['did'];
					$campaignCDR[$row['campaignid']][] = array($row["src"],$row["dst"],$row["srcchan"],$row["dstchan"],$row["starttime"],$row["calldate"],$row["answertime"],$row["disposition"]);
				}
			}else{
				$query = "SELECT groupid FROM astercrm_account WHERE extension = '".$row['dst']."' OR extension = '".$row['dst']."'  GROUP BY groupid ORDER BY groupid DESC LIMIT 0,1";

				$groupid = $db->getOne($query);
				if ( $groupid > 0 ){
					$groupCDR[$groupid][] = array($row["src"],$row["dst"],$row["srcchan"],$row["dstchan"],$row["starttime"],$row["answertime"],$row["disposition"]);
				}elseif($_SESSION['curuser']['usertype'] == 'admin'){
					//print_r($row);exit;
					$systemCDR[] = array($row["src"],$row["dst"],$row["srcchan"],$row["dstchan"],$row["starttime"],$row["answertime"],$row["disposition"]);
				}
			}
			
		}
		if($_SESSION['curuser']['usertype'] == 'admin'){
			$systemChannels = common::generateTabelHtml(array_merge($aDyadicArray , $systemCDR));
		}

		$objResponse->addAssign("idvUnknowChannels", "innerHTML", nl2br(trim($systemChannels)));

		// clear all group
		$groups = astercrm::getAll("astercrm_accountgroup");
		while	($groups->fetchInto($group)){
			$objResponse->addAssign("unknown".$group['groupid'], "innerHTML", "");
		}

		// clear all campaign
		$campaigns = astercrm::getAll("campaign");
		while	($campaigns->fetchInto($campaign)){

			$campaign_queue_name[$campaign['id']] = $campaign['queuename'];
			$objResponse->addAssign("campaign".$campaign['id'], "innerHTML", "");
		}

		// start assign all CDRs
		foreach ($groupCDR as $key => $value){
			if (is_array($value)){
				$groupChannels = common::generateTabelHtml(array_merge($aDyadicArray , $value));
				$objResponse->addAssign("unknown$key", "innerHTML", nl2br(trim($groupChannels)));
			}else{
				$objResponse->addAssign("unknown$key", "innerHTML", "");
			}
		}
		

		foreach ($campaignCDR as $key => $value){
			if (is_array($value)){
				$campaignChannels = common::generateTabelHtml(array_merge($cDyadicArray , $value));
				$objResponse->addAssign("campaign$key", "innerHTML", nl2br(trim($campaignChannels)));
			}else{
				$objResponse->addAssign("campaign$key", "innerHTML", "");
			}
		}
	/*
	// 将$f按组别分类
	foreach ($f as $key => $value){
		list ($campaignid, $field) = split("-",$key);
		$predial_campaigns[$campaignid][$field] = $value;
	}

	foreach ($predial_campaigns as $key => $value){
		if ($value['ckb'] == "on"){
			// 查找是否还有待拨号码
			$diallist_num[$key] = astercrm::getCountByField("campaignid", $key, "diallist");
			$num = 0;
			if ($diallist_num[$key]  > 0){
				if ($value['limittpye'][0] == "channel"){
					// 根据并发限制
					// 检查目前该campaign的并发通道
					$exp = $value['maxchannel'] - count($campaignCDR[$key]);
					if (  $exp > 0 ){
						// 可以发起呼叫, 规则为 (差额 +2)/3
						$num = intval(($exp + 2)/3);
						$i = 0;
						while ($i<$num && placeCall($key)) $i++;
					}else{
						// skip this campaign
					}
				}else{
					// 根据agent限制
					// 获取目前agent的数目
					$query = "SELECT COUNT(*) FROM queue_agent WHERE status = 'In use' AND queuename = '".$campaign_queue_name[$key]."' ";
					$busy_agent_num = $db->getOne($query);

					$query = "SELECT COUNT(*) FROM queue_agent WHERE status = 'Not in use' AND queuename = '".$campaign_queue_name[$key]."' ";
					$free_agent_num = $db->getOne($query);
					$totalagent = ($busy_agent_num + $free_agent_num);
					if (is_numeric($value['rate'])){
						$myagent = intval($totalagent * (1+$rate/100));
					}

					$exp = $myagent - count($campaignCDR[$key]);
					if (  $exp > 0 ){
						// 可以发起呼叫, 规则为 (差额 +2)/3
						$num = intval(($exp + 2)/3);
						$i = 0;
						while ($i<$num && placeCall($key)) $i++;
					}else{
						// skip this campaign
					}
				}
			}
			// refresh campaing number
			$objResponse->addAssign("numbers-$key","innerHTML",$diallist_num[$key] - $i);

		}else{
			unset($predial_campaigns[$key]);
		}
	}
	*/
	//exit;
	$check_interval = 2000;
	if ( is_numeric($config['system']['status_check_interval']) ) $check_interval = $config['system']['status_check_interval'] * 1000;

	$objResponse->addScript("setTimeout(\"startDial()\", ".$check_interval.");");	

	return $objResponse;
}

function placeCall($campaignid){
	global $config;

	$myAsterisk = new Asterisk();
	$row =& astercrm::getDialNumber($campaignid);
	
	// 待拨号码为空
	if (!$row) return false;
	//print_r($row);

	$id = $row['id'];
	$groupid = $row['groupid'];
	$campaignid = $row['campaignid'];
	$phoneNum = $row['dialnumber'];
	$trytime = $row['trytime'];
	$assign = $row['assign'];
	$pdcontext = $row['incontext'];
	$outcontext = $row['outcontext'];

	if ($row['inexten'] != ""){
		$pdextension = $row['inexten'];
	}else{
		if ($row['assign'] != ""){
			$pdextension = $row['assign'];
		}else{
			$pdextension = $row['dialnumber'];
		}
	}

	$res = astercrm::deleteRecord($id,"diallist");

	$f['dialednumber'] = $phoneNum;
	$f['dialedby'] = $_SESSION['curuser']['username'];
	$f['groupid'] = $groupid;
	$f['trytime'] = $trytime + 1;
	$f['assign'] = $assign;
	$f['campaignid'] = $campaignid;
	$res = astercrm::insertNewDialedlist($f);

	$actionid=md5(uniqid(""));

	$strChannel = "local/".$phoneNum."@".$outcontext."/n";
	if ($config['system']['allow_dropcall'] == true){
		$myAsterisk->dropCall($actionid,array('Channel'=>"$strChannel",
									'WaitTime'=>30,
									'Exten'=>$pdextension,
									'Context'=>$pdcontext,
									'Variable'=>"$strVariable",
									'Priority'=>1,
									'MaxRetries'=>0,
									'CallerID'=>$phoneNum));
	}else{
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();

		$myAsterisk->sendCall($strChannel,$pdextension,$pdcontext,1,NULL,NULL,30,$phoneNum,NULL,NULL,NULL,$actionid);
	}

	return true;
}

$xajax->processRequests();
?>
