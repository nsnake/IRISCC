<?php
/*******************************************************************************
* astercrmclient.server.php
* agent astercrmclient interface

* Function Desc
	agent portal background script

* 功能描述
	座席客户端管理脚本

* Function Desc
	init
	listenCalls
	incomingCalls
	waitingCalls
	getContact
	monitor
	transfer
	invite
********************************************************************************/

require_once ("db_connect.php");
require_once ("astercrmclient.common.php");
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/localization.class.php');

/**
*  init page
*  @return object xajax response object
*/

function init($username="",$passwd="",$lang='en_US'){
	global $config,$db;
//echo $username.$passwd.$lang;exit;
	//if($_SESSION['curuser']['username'] != $username){
		$row = astercrm::getRecordByField("username",$username,"astercrm_account");
			if ($row['id'] != '' ){
				if (md5($row['password']) == $passwd)
				{
					$_SESSION = array();
					$_SESSION['curuser']['username'] = trim($username);
					$_SESSION['curuser']['extension'] = $row['extension'];
					$_SESSION['curuser']['usertype'] = $row['usertype'];
					$_SESSION['curuser']['accountcode'] = $row['accountcode'];
					$_SESSION['curuser']['agent'] = $row['agent'];
					$_SESSION['curuser']['extensions'] = array();

					// added by solo 2007-10-90
					$_SESSION['curuser']['channel'] = $row['channel'];
					$_SESSION['curuser']['groupid'] = $row['groupid'];
					if ($row['extensions'] != ''){
						$_SESSION['curuser']['extensions'] = split(',',$row['extensions']);
					}
				}
			}
	//}	
//echo $lang;exit;
	list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $lang);
//echo $_SESSION['curuser']['language'];exit;
	$locate = new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');
//echo $locate->Translate("Search");exit;
	$objResponse = new xajaxResponse();

	$objResponse->addAssign("username","value", $_SESSION['curuser']['username'] );
	$objResponse->addAssign("extension","value", $_SESSION['curuser']['extension'] );
	$objResponse->addAssign("myevents","innerHTML", $locate->Translate("extension").$_SESSION['curuser']['extension']."-".$locate->Translate("waiting") );
	$objResponse->addAssign("btnShowPortal","value", $locate->Translate("portal"));
	$objResponse->addAssign("btnTransfer","value", $locate->Translate("Transfer"));
	$objResponse->addAssign("btnSearchContact","value", $locate->Translate("Search"));
	$objResponse->addAssign("spanMonitor","innerHTML", $locate->Translate("monitor") );
	$objResponse->addAssign("extensionStatus","value", 'idle');
	$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("idle") );
	$objResponse->addAssign("btnMonitorStatus","value", "idle" );
	$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
	$objResponse->addAssign("btnMonitor","disabled", true );
	$objResponse->addAssign("btnCallCtrl","value", $locate->Translate("Dial") );

	$objResponse->addAssign("btnTransfer","disabled",true);

	foreach ($_SESSION['curuser']['extensions'] as $extension){
		$extension = trim($extension);
		$row = astercrm::getRecordByField('username',$extension,'astercrm_account');		
		$objResponse->addScript("addOption('sltExten','".$row['extension']."','$extension');");
	}

	$speeddial = & astercrm::getAllSpeedDialRecords();
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
		$objResponse->addScript("addOption('iptDestNumber','".$speednumber[$i]['number']."','".$speednumber[$i]['description']."');");
	}
	$panelHTML = '<a href=? onclick="xajax_showRecentCdr(\'\',\'recent\');return false;">'.$locate->Translate("recentCDR").'</a>&nbsp;&nbsp;';
	if ($_SESSION['curuser']['usertype'] != "agent"  ){
		$panelHTML .= '<a href=# onclick="this.href=\'managerportal.php\'">'.$locate->Translate("manager").'</a>&nbsp;&nbsp;';
	}

	if ($config['system']['enable_external_crm'] == false){	//use internal crm
		$objResponse->addIncludeScript("js/astercrm.js");
		$objResponse->addIncludeScript("js/ajax.js");
		$objResponse->addIncludeScript("js/ajax-dynamic-list.js");
		$objResponse->addAssign("divSearchContact", "style.visibility", "visible");
	} else {
		$objResponse->addIncludeScript("js/extercrm.js");
		if ($config['system']['open_new_window'] == false){
			$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$config['system']['external_crm_default_url'].'" width="100%"  frameBorder=0 scrolling=auto height="100%"></iframe>';
			$objResponse->addAssign("divCrm","innerHTML", $mycrm );
		}else{
			$javascript = "openwindow('".$config['system']['external_crm_default_url']."')";
			$objResponse->addScript($javascript);
		}
	}

	$monitorstatus = astercrm::getRecordByID($_SESSION['curuser']['groupid'],'astercrm_accountgroup');
	
	if ($monitorstatus['monitorforce']) {
		$objResponse->addAssign("chkMonitor","checked", 'true');
		$objResponse->addAssign("chkMonitor","style.visibility", 'hidden');
		$objResponse->addAssign("btnMonitor","disabled", 'true');
		
	}
	$objResponse->addAssign("divSearchContact", "style.visibility", "visible");
	//if enabled monitor by astercctools
	Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);

	if ($asterccConfig['system']['force_record'] == 1 ) {
		$objResponse->addAssign("chkMonitor","checked", 'false');
		$objResponse->addAssign("chkMonitor","style.visibility", 'hidden');
		$objResponse->addAssign("btnMonitor","disabled", 'true');
	}
	return $objResponse;
}

/**
*	 check if there's new event happen
*
*/
function listenCalls($aFormValues){
	global $config;
	$objResponse = new xajaxResponse();

	if ($aFormValues['uniqueid'] == ''){
		$objResponse->loadXML(waitingCalls($aFormValues));
	} else{
		$objResponse->loadXML(incomingCalls($aFormValues));
	}
	
	$objResponse->addScript('setTimeout("updateEvents()", "1000");');
	return $objResponse;
}

/**
*	 check if there's new event happen
*
*/
function transfer($aFormValues){
	global $config;
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

	if ($aFormValues['direction'] == 'in')		
		$myAsterisk->Redirect($aFormValues['callerChannel'],'',$action,$config['system']['outcontext'],1);
	else
		$myAsterisk->Redirect($aFormValues['calleeChannel'],'',$action,$config['system']['outcontext'],1);
	$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse;
}

//check if call (uniqueid) hangup
function incomingCalls($myValue){
	global $db,$config;
echo $_SESSION['curuser']['country'];exit;
	$locate = new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');

	$objResponse = new xajaxResponse();

	if ($myValue['direction'] != ''){
		$call = asterEvent::checkCallStatus($myValue['curid'],$myValue['uniqueid']);

		if ($call['status'] ==''){
			return $objResponse;
		} elseif ($call['status'] =='link'){

			if ($myValue['extensionStatus'] == 'link')	 //already get link event
				return $objResponse;
//			if ($call['callerChannel'] == '' or $call['calleeChannel'] == '')
//				return $objResponse;
			$status	= "link";
			$info	= $locate->Translate("talking_to").$myValue['callerid'];
			$objResponse->addAssign("callerChannel","value", $call['callerChannel'] );
			$objResponse->addAssign("calleeChannel","value", $call['calleeChannel'] );
			//if chkMonitor be checked or monitor by astercctools btnMonitor must be disabled
			Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
			if ($myValue['chkMonitor'] != 'on' && $asterccConfig['system']['force_record'] != 1) {
				$objResponse->addAssign("btnMonitor","disabled", false );
			}
			//$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );
			astercrm::events($myValue['chkMonitor'].'-chkMonitor');
			astercrm::events($myValue['btnMonitorStatus'].'-btnMonitorStatus');
			if ($myValue['chkMonitor'] == 'on' && $myValue['btnMonitorStatus'] == 'idle') 
				$objResponse->addScript("monitor();");
			$spanCallCtrl = '<input type="button" id="btnCallCtrl" name="btnCallCtrl" value="'.$locate->Translate("Hangup").'" onclick="hangup();">';
			$objResponse->addAssign("spanCallCtrl", "innerHTML", $spanCallCtrl );
			$objResponse->addAssign("btnTransfer","disabled", false );
		} elseif ($call['status'] =='hangup'){
			if ($myValue['chkMonitor'] == 'on' && $myValue['btnMonitorStatus'] == 'recording') 
				$objResponse->addScript("monitor();");
			$status	= 'hang up';
			$info	= $locate->Translate("Hang up call from")." ". $myValue['callerid'];
//			$objResponse->addScript('document.title=\'asterCrm\';');
			$objResponse->addAssign("uniqueid","value", "" );
			$objResponse->addAssign("callerid","value", "" );
			$objResponse->addAssign("callerChannel","value", '');
			$objResponse->addAssign("calleeChannel","value", '');
			$objResponse->addAssign("btnTransfer","disabled", true );

			//disable monitor
			$objResponse->addAssign("btnMonitor","disabled", true );
			$objResponse->addAssign("spanMonitorStatus","innerHTML", $locate->Translate("idle") );
			$objResponse->addAssign("btnMonitor","value", $locate->Translate("start_record") );

			//disable hangup button
			$spanCallCtrl = '<input type="button" id="btnCallCtrl" name="btnCallCtrl" value="'.$locate->Translate("Dial").'" onclick="invite();">';
			$objResponse->addAssign("spanCallCtrl","innerHTML", $spanCallCtrl );
			$objResponse->addAssign('divTrunkinfo',"innerHTML",'');
			$objResponse->addAssign('divDIDinfo','innerHTML','');
			if($myValue['btnWorkStatus'] == 'working') {				
				$interval = $_SESSION['curuser']['dialinterval'];
				$objResponse->addScript("autoDial('$interval');");
			}
		}
		$objResponse->addAssign("status","innerHTML", $status );
//		$objResponse->addAssign("extensionStatus","value", $status );
		$objResponse->addAssign("myevents","innerHTML", $info );
	}

	return $objResponse;
}

/*
	add a new parameter callerid		by solo2008/2/24
	when monitor, record the callerid and the filename to database
*/
function monitor($channel,$callerid,$action = 'start',$uniqueid = ''){
	global $config;

	$locate = new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');

	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addAlert($locate->Translate("failed when connect to AMI"));
		return;
	}

	if ($action == 'start'){
		$filename = str_replace("/","-",$channel);
		$filename = $config['asterisk']['monitorpath'].date('Y/m/d/H/').$filename;
		$filename .= '.'.time();
		$format = $config['asterisk']['monitorformat'];
		$mix = true;
		$res = $myAsterisk->Monitor($channel,$filename,$format,$mix);
		if ($res['Response'] == 'Error'){
			$objResponse->addAlert($res['Message']);
			return $objResponse;
		}
		// 录音信息保存到数据库
		astercrm::insertNewMonitor($callerid,$filename,$uniqueid,$format);
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
	global $db,$config;

	$locate = new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'portal');

	$objResponse = new xajaxResponse();
	$curid = trim($myValue['curid']);

// to improve system efficiency
/**************************
**************************/
	
	//	modified 2007/10/30 by solo
	//  start
	//print_r($_SESSION);exit;
	//if ($_SESSION['curuser']['channel'] == '')
		$call = asterEvent::checkNewCall($curid,$_SESSION['curuser']['extension'],$_SESSION['curuser']['channel'],$_SESSION['curuser']['agent']);
	//else
	//	$call = asterEvent::checkNewCall($curid,$_SESSION['curuser']['channel']);
	//  end
	if ($call['status'] == ''){
		$title	= $locate->Translate("waiting");
		$status	= 'idle';
		//$call['curid'] = $curid;
		$direction	= '';
		$info	= $locate->Translate("extension").$_SESSION['curuser']['extension']."-".$locate->Translate("stand_by");
	} elseif ($call['status'] == 'incoming'){	//incoming calls here
		$title	= $call['callerid'];
		$stauts	= 'ringing';
		$direction	= 'in';
		$info	= $locate->Translate("incoming"). ' ' . $call['callerid'];

		$trunk = split("-",$call['callerChannel']);
		//print_r($trunk);exit;
		$trunk_name = split('@',$trunk[0]);
		$info	= $info. ' channel: ' . $trunk_name[0];
		// get trunk info
		$mytrunk = astercrm::getTrunkinfo($trunk[0],$call['didnumber']);
		if ($mytrunk){
			$infomsg = "<strong>".$mytrunk['trunkname']."</strong><br>";
			$infomsg .= mb_substr(astercrm::db2html($mytrunk['trunknote']),0,10,"UTF-8").'...';
			if($call['didnumber'] != ''){
				$infomsg .= "&nbsp;|".$locate->Translate("Callee id")."&nbsp;:&nbsp;<b>".$call['didnumber']."</b>";				
			}
			$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
		}else{
			$trunk_name = split('@',$trunk[0]);
			$infomsg = $locate->Translate("no information get for trunk").": ".$trunk_name[0];
			$objResponse->addAssign('divTrunkinfo',"innerHTML",$infomsg);
		}
		
		$objResponse->addAssign("iptSrcNumber","value", $call['callerid'] );
		$objResponse->addAssign("iptCallerid","value", $call['callerid'] );
		$objResponse->addAssign("btnHangup","disabled", false );

		if ($config['system']['pop_up_when_dial_in']){
			if (strlen($call['callerid']) > $config['system']['phone_number_length'] && $call['callerid'] != '<unknown>'){
				if ($myValue['popup'] == 'yes'){
					if ($config['system']['enable_external_crm'] == false){
							$objResponse->loadXML(getContact($call['callerid']));
							if ( $config['system']['browser_maximize_when_pop_up'] == true ){
								$objResponse->addScript('maximizeWin();');
							}
					}else{
						//use external link
						$myurl = $config['system']['external_crm_url'];
						$myurl = preg_replace("/\%method/","dial_in",$myurl);
						$myurl = preg_replace("/\%callerid/",$call['callerid'],$myurl);
						$myurl = preg_replace("/\%calleeid/",$_SESSION['curuser']['extension'],$myurl);

						if ($config['system']['open_new_window'] == false){
								$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$myurl.'" width="100%"  frameBorder=0 scrolling=auto height="100%"></iframe>';
								$objResponse->addAssign("divCrm","innerHTML", $mycrm );
						}else{
							$javascript = "openwindow('".$myurl."')";
							$objResponse->addScript($javascript);
						}
					}
				}
			}else{

			}
		}
	} elseif ($call['status'] == 'dialout'){	//dailing out here

		$title	= $call['callerid'];
		$status	= 'dialing';
		$direction	= 'out';
		$info	= $locate->Translate("dial_out"). ' '. $call['callerid'];
		
		$objResponse->addAssign("iptCallerid","value", $call['callerid'] );
		$objResponse->addAssign("btnHangup","disabled", false );

		if($call['didnumber'] != ''){
			$didinfo = $locate->Translate("Callee id")."&nbsp;:&nbsp;".$call['didnumber'];
			$objResponse->addAssign('divDIDinfo','innerHTML',$didinfo);
		}

		if ($config['system']['pop_up_when_dial_out']){
			if (strlen($call['callerid']) > $config['system']['phone_number_length']){
				if ($myValue['popup'] == 'yes'){
					if ($config['system']['enable_external_crm'] == false ){
							$objResponse->loadXML(getContact($call['callerid']));
							if ( $config['system']['browser_maximize_when_pop_up'] == true ){
								$objResponse->addScript('maximizeWin();');
							}
					}else{
						//use external link
						$myurl = $config['system']['external_crm_url'];
						$myurl = preg_replace("/\%method/","dial_out",$myurl);
						$myurl = preg_replace("/\%callerid/",$_SESSION['curuser']['extension'],$myurl);
						$myurl = preg_replace("/\%calleeid/",$call['callerid'],$myurl);
						if ($config['system']['open_new_window'] == false){
							$mycrm = '<iframe id="mycrm" name="mycrm" src="'.$myurl.'" width="100%"  frameBorder=0 scrolling=auto height="100%"></iframe>';
							$objResponse->addAssign("divCrm","innerHTML", $mycrm );
						} else {
							$javascript = "openwindow('".$myurl."')";
							$objResponse->addScript($javascript);
						}
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

/**
*  Originate src and dest extension
*  @param	src			string			extension
*  @param	dest		string			extension
*  @return	object						xajax response object
*/

function invite($src,$dest,$campaignid=''){
	global $config;
	$src = trim($src);
	$dest = trim($dest);
	$objResponse = new xajaxResponse();	
	//$objResponse->addAssign("dialmsg", "innerHTML", "<b>".$locate->Translate("dailing")." ".$src."</b>");
	if ($src == $_SESSION['curuser']['extension'])
		$callerid = $dest;
	else //if ($dest == $_SESSION['curuser']['extension'])
		$callerid = $src;
//	else
//		return $objResponse;
	
	$myAsterisk = new Asterisk();
	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");
	if($campaignid != ''){
		$row_campaign = astercrm::getRecordByID($campaignid,"campaign");
		if(trim($row_campaign['incontext']) != '' ) $incontext = $row_campaign['incontext'];
		else $incontext = $config['system']['incontext'];
		if(trim($row_campaign['outcontext']) != '' ) $outcontext = $row_campaign['outcontext'];
		else $outcontext = $config['system']['outcontext'];
		//if($row_campaign['inexten'] != '') $src = $row_campaign['inexten'];
	}else{
		$group_info = astercrm::getRecordByID($_SESSION['curuser']['groupid'],"astercrm_accountgroup");

		if ($group_info['incontext'] != '' ) $incontext = $group_info['incontext'];
		else $incontext = $config['system']['incontext'];
		if ($group_info['outcontext'] != '' ) $outcontext = $group_info['outcontext'];
		else $outcontext = $config['system']['outcontext'];
	}
	$strChannel = "local/".$src."@".$incontext."/n";

	if ($config['system']['allow_dropcall'] == true){
		$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
							'WaitTime'=>30,
							'Exten'=>$dest,
							'Context'=>$outcontext,
							'Account'=>$_SESSION['curuser']['accountcode'],
							'Variable'=>"$strVariable",
							'Priority'=>1,
							'MaxRetries'=>0,
							'CallerID'=>$callerid));
	}else{
		$myAsterisk->sendCall($strChannel,$dest,$outcontext,1,NULL,NULL,30,$callerid,NULL,$_SESSION['curuser']['accountcode']);
	}
	
	$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse->getXML();
}

/**
*  hangup a channel
*  @param	channel			string		channel name
*  @return	object						xajax response object
*/


function hangup($channel){
	global $config;
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
	$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse;
}

function showportal($callerid){
	global $db,$locate,$config;	
	$mycallerid = $callerid;
	$objResponse = new xajaxResponse();
	
	return $objResponse;
}

$xajax->processRequests();

?>