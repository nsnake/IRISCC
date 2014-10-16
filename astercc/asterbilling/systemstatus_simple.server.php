<?php
/*******************************************************************************
# 接通后如果没有找到destination 那么过5秒钟使用checkDestination再次检查 line 290
********************************************************************************/
require_once ("systemstatus_simple.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');

if($config['customers']['enable']){
	// define database connection string
	define('SQLC', $config['customers']['dbtype']."://".$config['customers']['username'].":".$config['customers']['password']."@".$config['customers']['dbhost']."/".$config['customers']['dbname']."");

	// set a global variable to save customers database connection
	$GLOBALS['customers_db'] = DB::connect(SQLC);

	$GLOBALS['customers_db']->setFetchMode(DB_FETCHMODE_ASSOC);
}

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
		$objResponse->addAssign("AMIStatudDiv", "innerHTML", $locate->Translate("AMI connection failed"));
	}

	$group_row = astercrm::getRecord($_SESSION['curuser']['groupid'],'accountgroup');

	if ( $group_row['grouplogo'] != '' && $group_row['grouplogostatus'] ){
		$logoPath = $config['system']['upload_file_path'].'/callshoplogo/'.$group_row['grouplogo'];
		if (is_file($logoPath)){
			$titleHtml = '<img src="'.$logoPath.'" style="float:left;" >';
		}
	}
	if ( $group_row['grouptitle'] != ''){
		$titleHtml .= '<h1 style="padding: 0 0 0 0;position: relative;font-size: 16pt;">'.$group_row['grouptitle'].'</h1>';
	}
	if ( $group_row['grouptagline'] != ''){
		$titleHtml .= '<h2 style="padding: 0 0 0 0;position: relative;font-size: 11pt;color: #FJDSKB;">'.$group_row['grouptagline'].'</h2>';
	}
	if (isset($titleHtml)){
		//$titleHtml .= '<div style="position:absolute;top:85px;left:0px;width:800px"><hr color="#F1F1F1"></div>';
		$objResponse->addAssign("divTitle", "innerHTML", $titleHtml);
	}else{
		$objResponse->addAssign("divTitle", "style.height", '0px');
		$objResponse->addAssign("divMain", "style.top", '0px');
	}

	$_SESSION['status'] = array();
	$peers = $_SESSION['curuser']['extensions'];

	# 获得当前的channel
	$curchannels = array();
	$curchannels = astercc::checkPeerStatus($_SESSION['curuser']['groupid'],$peers);


	foreach ($peers as $peer){
		// check if the booth is locked
		$clid = astercc::readRecord('clid','clid',$peer);
		if($clid['isshow'] == 'yes'){
			$i++;
			// read booth display
			//$display = astercc::readField('clid','display','clid',$peer);
			$status = $clid['status'];
			$display = $clid['display'];
			if ($curchannels[$peer] && $curchannels[$peer]['creditlimit'] > 0){
				$objResponse->addScript('addDiv("divMainContainer","'.$peer.'","'.$curchannels[$peer]['creditlimit'].'","'.$i.'","'.$status.'","'.$display.'","'.$config['customers']['enable'].'")');
			}else{
				$objResponse->addScript('addDiv("divMainContainer","'.$peer.'","","'.$i.'","'.$status.'","'.$display.'","'.$config['customers']['enable'].'")');
			}
			$objResponse->addScript('xajax_addUnbilled("'.$peer.'");');
		}
	}
if (!isset($_SESSION['callbacks']))
	$_SESSION['callbacks'] = array();

//print_r($_SESSION['callbacks']);
	// get callback from database
	$callback = astercc::getCallback($_SESSION['curuser']['groupid']);
	while	($callback->fetchInto($mycallback)){
		if ($mycallback['dst'] != $mycallback['src']){	 // legB connected
			$_SESSION['callbacks'][$mycallback['dst'].$mycallback['src']] = array('legA' =>$mycallback['src'],'legB' => $mycallback['dst'], 'start' => 1, 'creditLimit' => $mycallback['creditlimit']);
		}
	}
//print_r($_SESSION['callbacks']);

	// get callback from session
	foreach ($_SESSION['callbacks'] as $callback){
		if ($callback['creditlimit'] > 0){
			$objResponse->addScript('addDiv("divMainContainer","local/'.$callback['legB'].'","'.$callback['creditlimit'] .'","","","'.$config['customers']['enable'].'")');
		}else{
			$objResponse->addScript('addDiv("divMainContainer","local/'.$callback['legB'].'","","","","'.$config['customers']['enable'].'")');
		}

		$objResponse->addScript('xajax_addUnbilled("'.$callback['legB'].'","'.$callback['legA'].'");');
	}
//print_r($_SESSION['callbacks']);
	
	//$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin));
	//$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->addScript("checkHangup()");

	$objResponse->addScript("xajax_setGroupBalance();");
	$objResponse->addAssign("creditlimittype","value",$config['system']['creditlimittype']);
	return $objResponse;
}

function searchRate($content,$type){
	$objResponse = new xajaxResponse();
//	echo $type;exit;
	if ($content == ''){
		return $objResponse;
	}
	
	$rate = astercc::searchRate($content,$_SESSION['curuser']['groupid'],$_SESSION['curuser']['resellerid'],"myrate",$type);

	$rateDesc = astercc::readRateDesc($rate,'search');
	// remove the connect charge part
	// $rateDesc = split("seconds",$rateDesc);
	// $rateDesc = $rateDesc[1]." seconds";
	if($type == "prefix")
		$objResponse->addAssign("divRate","innerHTML",$rate['destination']."(".$rateDesc.")");
	else
		$objResponse->addAssign("divRate","innerHTML",$rate['dialprefix']."(".$rateDesc.")");
	return $objResponse;
}

function setGroupBalance(){
	global $config, $locate,$db;
	$objResponse = new xajaxResponse();
	# 检查session是否存在
	if ($_SESSION['curuser']['groupid'] == ""){
		return $objResponse;
	}

	$group = astercrm::getRecordByField("id",$_SESSION['curuser']['groupid'],'accountgroup');
	$startdate = date("Y-m-d")." 00:00";
	$enddate = date("Y-m-d")." 23:59";
	if($config['system']['useHistoryCdr'] == 1){
		$sql = "SELECT SUM(credit) AS todayAmount,SUM(callshopcredit) AS todayCost FROM historycdr WHERE calldate > '".$startdate."' AND calldate < '".$enddate."' AND groupid = ".$_SESSION['curuser']['groupid'];
	}else{
		$sql = "SELECT SUM(credit) AS todayAmount,SUM(callshopcredit) AS todayCost FROM mycdr WHERE calldate > '".$startdate."' AND calldate < '".$enddate."' AND groupid = ".$_SESSION['curuser']['groupid'];
	}

	$row = $db->getRow($sql);

	$amount = $row['todayAmount'];	//  income
	if ($amount == '') $amount = 0;
	$creditlimit = $group['creditlimit']; //  limit
	$callshopcredit = $row['todayCost']; // cost
	if ($callshopcredit == '') $callshopcredit = 0;
	$curcredit = $group['curcredit']; // current cost
	$balance = $callshopcredit - $curcredit; //available balance

	if ($amount == '') $amount = 0;
	if ($cost == '') $cost = 0;
	$divAmountHtml = '';
	if($config['system']['callshop_status_amount']){
		$divAmountHtml .='&nbsp;'.$locate->Translate("Amount").':&nbsp;'.$amount.'&nbsp;&nbsp;&nbsp;&nbsp;';
	}
		
	if ($_SESSION['curuser']['limittype'] == ''){
			$creditlimit = $locate->Translate("no limit");
			$objResponse->addAssign("spanLimitStatus","innerHTML",$creditlimit);
	}else{
		$balance = $creditlimit - $curcredit ;
		if ($balance <= 50) {
			if ($balance <= 0)
				$objResponse->addAssign("spanLimitStatus","innerHTML",$locate->Translate("no credit left all booth locked"));
			else
				$objResponse->addAssign("spanLimitStatus","innerHTML",$locate->Translate("warning no enough credit"));
		}else{
			$objResponse->addAssign("spanLimitStatus","innerHTML",$locate->Translate("normal"));
		}
	}

	if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
		if($config['system']['callshop_status_cost']){
		$divAmountHtml .='&nbsp;'.$locate->Translate("Cost").':&nbsp;'.$cost.'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		if($config['system']['callshop_status_limit']){
			$divAmountHtml .='&nbsp;'.$locate->Translate("Limit").':&nbsp;'.$creditlimit.'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		if($config['system']['callshop_status_credit']){
			$divAmountHtml .='&nbsp;'.$locate->Translate("Current Credit").':&nbsp;'.$curcredit.'&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		if($config['system']['callshop_status_balance']){
			$divAmountHtml .='&nbsp;'.$locate->Translate("Available Balance").':&nbsp;'.$balance;
		}
	}

	$objResponse->addAssign("divAmount","innerHTML",$divAmountHtml);

	if (is_numeric($config['system']['refreshBalance']) && $config['system']['refreshBalance'] != 0){
		$refreshtime = $config['system']['refreshBalance'] * 1000;
		$objResponse->addScript('setTimeout("xajax_setGroupBalance()",'.$refreshtime.');');
	}
	#$objResponse->addAlert('balance refreshed');
	return $objResponse->getXML();
}

function setLocked($clid,$locked){
	$objResponse = new xajaxResponse();
	global $db;
	$query = "UPDATE clid SET locked = '$locked' WHERE clid = '$clid' ";
	$db->query($query);
	return $objResponse;
}

function setStatus($clid,$status){
	$affectrows = astercc::setStatus($clid,$status);
	$objResponse = new xajaxResponse();
	if ($affectrows == 0){
		//$objResponse->addAssign($peer."-limitstatus","value","");
		$objResponse->addAlert($locate->Translate("falied to lock or unlock"));
	}else{
		if ($status == 1){
			$objResponse->addAssign($clid."-lock","style.backgroundColor","");
		}else{
			$objResponse->addAssign($clid."-lock","style.backgroundColor","red");
		}
		//$objResponse->addAlert("lock/unlock success");
	}

	return $objResponse;
}

function setCreditLimit($peer,$channel,$creditlimit){
	if ($creditlimit < 0.01){
		$creditlimit = -10;
	}
	$affectrows = astercc::setCreditLimit($channel,$creditlimit);
	
	$objResponse = new xajaxResponse();
//	$objResponse->addAlert($affectrows);
	if ($affectrows == 0){
		// cant find this channel
//
		$objResponse->addAssign($peer."-limitstatus","value","");
//
	}
	return $objResponse;
}

/**
*  show extension status
*  @return	objResponse		object		xajax response object
*/

function showStatus(){
	global $db;
	// get old status
	$cstatus = $_SESSION['status'];
	$objResponse = new xajaxResponse();
	if ($_SESSION['curuser']['groupid'] == ""){
		return $objResponse;
	}
//print_r($cstatus);exit;
	$peers = $_SESSION['curuser']['extensions'];
	
	$peerstatus = astercc::checkPeerStatus($_SESSION['curuser']['groupid'],$peers);
	#print_r($peerstatus);exit;

	$event = array('ring' => 1, 'dial' => 2, 'ringing' => 3, 'link' => 4);
	$event = array('RING' => 1, 'DIAL' => 2, 'RINGING' => 3, 'LINK' => 4);

	foreach ($peers as $peer){
		// update peer status
		if($_SESSION['curuser']['billingfield'] == 'accountcode'){
			$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=green>NA In Acc Mode</font>");
		}else{
		
			$query = "SELECT status,responsetime FROM peerstatus WHERE peername LIKE '%/$peer' ";

			$peer_status = $db->getRow($query);//print_r($peer_status );exit;
			if ($peer_status){
				if ($peer_status['responsetime'] > 0 ){
					if ($peer_status['responsetime'] > 300){
						if(strstr($peer_status['status'],'ok')){
							$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=red>".$peer_status['status']."</font>");
						}else{
							$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=red>".$peer_status['status']."(".$peer_status['responsetime']." ms)</font>");
						}
					}else{
						if(strstr($peer_status['status'],'ok')){
							$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=green>".$peer_status['status']."</font>");
						}else{
							$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=green>".$peer_status['status']."(".$peer_status['responsetime']." ms)</font>");
						}
					}
				}else{
					$objResponse->addAssign("$peer-peer-status","innerHTML","<font color=red>".$peer_status['status']."</font>");
				}
			}
		}
//echo "C".$cstatus[$peer]['disposition'].'P'.$peerstatus[$peer]['disposition'];exit;
//print_r($peerstatus);exit;
		if ($cstatus[$peer]['disposition'] != $peerstatus[$peer]['disposition']){	// status changed
//echo "C".$cstatus[$peer]['disposition'].'P'.$peerstatus[$peer]['disposition'];exit;
//print_r($peerstatus);exit;
			if ($peerstatus[$peer]['disposition'] == ''){
				// a hangup event
				$objResponse->addScript("clearCurchannel('".$peer."');");

				// set display name
				$objResponse->addAssign("$peer-displayname","style.backgroundColor","");

				// should reload CDR
				$objResponse->addScript("removeTr('".$peer."');");
				$objResponse->addScript('setTimeout("xajax_addUnbilled(\''.$peer.'\')",3000);');	 //wait daemon write data to cdr
			}else{
				//print_r($peerstatus);exit;
				// set display name
				$objResponse->addAssign("$peer-displayname","style.backgroundColor","#009900");
				$destination = '';
				if($peerstatus[$peer]['destination'] != ''){
					$destination = '<br>&nbsp;'.$peerstatus[$peer]['destination'];
				}else{
					#print $peerstatus[$peer]['destination'];die;
					# try get the destination in 5 sec
					$objResponse->addScript('setTimeout("xajax_checkDestination(\''.$peer.'\',\''.$peerstatus[$peer]['direction'].'\')", 5000);');
				}

				if( $peerstatus[$peer]['direction'] == 'outbound'){
					$objResponse->addAssign($peer.'-phone','innerHTML',"<img src='images/outbound.gif'>".$peerstatus[$peer]['dst'].$destination);//here
				}else{
					$objResponse->addAssign($peer.'-phone','innerHTML',"<img src='images/inbound.gif'>".$peerstatus[$peer]['src'].$destination);
					$objResponse->addAssign($peer.'-phone','style.color','#009900');
				}
				$objResponse->addAssign($peer.'-startat','innerHTML',$peerstatus[$peer]['starttime']);
				$objResponse->addAssign($peer.'-channel','value',$peerstatus[$peer]['srcchan']);
				$objResponse->addAssign($peer.'-dstchan','value',$peerstatus[$peer]['dstchan']);
				if ($peerstatus[$peer]['answertime'] != '0000-00-00 00:00:00'){
					if($peerstatus[$peer]['pushcall'] == 'LINK' || $peerstatus[$peer]['pushcall'] == 'no'){
					
						$now = time();
	 					$initSec = $now - strtotime($peerstatus[$peer]['answertime']);
						$objResponse->addScript("putCurrentTime('".$peer."-localanswertime',$initSec);");
					}else{
						$peerstatus[$peer]['disposition'] = $peerstatus[$peer]['pushcall'];
					}
				}
			}
		}
		//credit changed
		if ($cstatus[$peer]['credit'] != $peerstatus[$peer]['credit'] && ($peerstatus[$peer]['pushcall'] == 'LINK' || $peerstatus[$peer]['pushcall'] == 'no')){
				$objResponse->addAssign($peer.'-price','innerHTML',astercc::creditDigits($peerstatus[$peer]['credit']));
		}
	}


	$callbacks = $_SESSION['callbacks'];
	if (count($callbacks) > 0){
		foreach ($callbacks as $key => $callback){

			$localChan = 'local/'.$callback['legB'];
			$res = astercc::getCurLocalChan($localChan,$_SESSION['curuser']['groupid']);
//			print $localChan;
//			print "\n";
//			print $callback['start'];
//			print "\n";
//			print $res->numRows();
			if ($res->numRows() == 0){
				if ( $callback['start'] != 0 ){	//hangup
					$objResponse->addScript("clearCurchannel('".$localChan."');");
					$objResponse->addScript("clearCurchannel('".$localChan."-legb"."');");
					//$objResponse->addAlert("clearCurchannel('".$localChan."-legb"."');");

					// should reload CDR
					$objResponse->addScript("removeTr('".$localChan."');");
					$objResponse->addScript('xajax_addUnbilled("'.$callback['legB'].'","'.$callback['legA'].'");');
	
					//$objResponse->addScript('setTimeout(xajax_addUnbilled("'.$localChan.'"),1000);');

					//unset($_SESSION['callbacks'][$key]);
					$callback = null;
				}else{	//not start yet


				}
				$_SESSION['callbacks'][$key]['start'] = 0;
			}else if ($res->numRows() == 1){	 //calling legA
				$_SESSION['callbacks'][$key]['start'] = 1;
				$res->fetchInto($legA);
					$destination = '';
					if($legA['destination'] != '') $destination = '<br>&nbsp;'.$legA['destination'];

					$objResponse->addAssign($localChan.'-phone','innerHTML',$legA['dst'].$destination);
					$objResponse->addAssign($localChan.'-startat','innerHTML',$legA['starttime']);
					$objResponse->addAssign($localChan.'-channel','value',$legA['srcchan']);
	//				$objResponse->addAlert($legA['answertime']);
					if ($legA['answertime'] != '0000-00-00 00:00:00'){
						$now = time();
		 				$initSec = $now - strtotime($legA['answertime']);

						$objResponse->addScript("putCurrentTime('".$localChan."-localanswertime',$initSec);");
					}
					/*
					if ($legA['dst'] != ''){
						$rate = astercc::readRate($legA['dst'],$_SESSION['curuser']['groupid']);
						$objResponse->addAssign($localChan.'-rateinitial','innerHTML',floor($rate['rateinitial']*100)/100);
						$objResponse->addAssign($localChan.'-initblock','innerHTML',floor($rate['initblock']*100)/100);
						$objResponse->addAssign($localChan.'-billingblock','innerHTML',floor($rate['billingblock']*100)/100);
						$objResponse->addAssign($localChan.'-connectcharge','innerHTML',floor($rate['connectcharge']*100)/100);
					}
					*/
					$objResponse->addAssign($localChan.'-price','innerHTML',astercc::creditDigits($legA['credit']));
			}else if ($res->numRows() == 2){	 //calling legB
				$_SESSION['callbacks'][$key]['start'] = 2;
				$res->fetchInto($legA);
				//**$destination = '';
					if($legA['destination'] != '') $destination = '<br>&nbsp;'.$legA['destination'];
					$objResponse->addAssign($localChan.'-phone','innerHTML',$legA['dst'].$destination);
					$objResponse->addAssign($localChan.'-startat','innerHTML',$legA['starttime']);
					$objResponse->addAssign($localChan.'-channel','value',$legA['srcchan']);
					if ($legA['answertime'] != '0000-00-00 00:00:00'){
						$now = time();
		 				$initSec = $now - strtotime($legA['answertime']);

						$objResponse->addScript("putCurrentTime('".$localChan."-localanswertime',$initSec);");
					}
					/*
					if ($legA['dst'] != ''){
						$rate = astercc::readRate($legA['dst'],$_SESSION['curuser']['groupid']);
						$objResponse->addAssign($localChan.'-rateinitial','innerHTML',floor($rate['rateinitial']*100)/100);
						$objResponse->addAssign($localChan.'-initblock','innerHTML',floor($rate['initblock']*100)/100);
						$objResponse->addAssign($localChan.'-billingblock','innerHTML',floor($rate['billingblock']*100)/100);
						$objResponse->addAssign($localChan.'-connectcharge','innerHTML',floor($rate['connectcharge']*100)/100);
					}
					*/
					$objResponse->addAssign($localChan.'-price','innerHTML',astercc::creditDigits($legA['credit']));

			//**
				$res->fetchInto($legB);
					$destination = '';
					if($legB['destination'] != '') $destination = '<br>&nbsp;'.$legB['destination'];
					$objResponse->addAssign($localChan.'-legb-phone','innerHTML',$legB['dst'].$destination);
					$objResponse->addAssign($localChan.'-legb-startat','innerHTML',$legB['starttime']);
					$objResponse->addAssign($localChan.'-legb-channel','value',$legB['srcchan']);
					if ($legB['answertime'] != '0000-00-00 00:00:00'){
						$now = time();
		 				$initSec = $now - strtotime($legB['answertime']);
						#print $legB['answertime'];
						$objResponse->addScript("putCurrentTime('".$localChan."-legb-localanswertime',$initSec);");
					}
					/*
					if ($legB['dst'] != ''){
						$rate = astercc::readRate($legB['dst'],$_SESSION['curuser']['groupid']);
						$objResponse->addAssign($localChan.'-legb-rateinitial','innerHTML',floor($rate['rateinitial']*100)/100);
						$objResponse->addAssign($localChan.'-legb-initblock','innerHTML',floor($rate['initblock']*100)/100);
						$objResponse->addAssign($localChan.'-legb-billingblock','innerHTML',floor($rate['billingblock']*100)/100);
						$objResponse->addAssign($localChan.'-legb-connectcharge','innerHTML',floor($rate['connectcharge']*100)/100);
					}
					*/
					$objResponse->addAssign($localChan.'-legb-price','innerHTML',astercc::creditDigits($legB['credit']));
			}
		}
	}

	$_SESSION['status'] = $peerstatus;
	//$objResponse->addScript('setTimeout("showStatus()", 2000);');
	$objResponse->addAssign("spanLastRefresh",'innerHTML',date ("Y-m-d H:i:s",time()));
	return $objResponse;
}

function checkDestination($peer,$direction){
	global $db,$config;
	$objResponse = new xajaxResponse();
//echo $direction;exit;
	$peers = $_SESSION['curuser']['extensions'];
	if($_SESSION['curuser']['billingfield'] == 'accountcode'){
		$query = "SELECT * FROM curcdr WHERE accountcode = '$peer' ";
	}else{
		if($direction == 'inbound'){
			$query = "SELECT * FROM curcdr WHERE dst = '$peer' ";
		}else{
			$query = "SELECT * FROM curcdr WHERE src = '$peer' ";
		}
	}
	$curcdr = $db->getRow($query);
	//$direction = 'inbound';
	//print_r($curcdr);exit;
//	if ($curcdr){
//		if($_SESSION['curuser']['billingfield'] == 'accountcode'){
//			if (astercc::array_exist($curcdr['accountcode'], $peers)){
//				$direction = 'outbound';
//			}
//		}else{
//			if (astercc::array_exist($curcdr['src'], $peers) || astercc::array_exist($curcdr['dst'], $peers)){
//				$direction = 'outbound';
//			}else{
//				if (ereg("\/(.*)-", $curcdr['srcchan'], $myAry) ){
//					$direction = 'outbound';
//				}
//			}
//		}
//	}

	if ($direction == 'inbound'){
		$objResponse->addAssign($peer.'-phone','innerHTML',"<img src='images/inbound.gif'>".$curcdr['src'].'<br/>&nbsp;'.$curcdr['destination']);
		$objResponse->addAssign($peer.'-phone','style.color','green');
	}else{
		$objResponse->addAssign($peer.'-phone','innerHTML',"<img src='images/outbound.gif'>".$curcdr['dst'].'<br/>&nbsp;'.$curcdr['destination']);//here
	}
	#print_r($res);
	return $objResponse;
}

function removeLocalChannel($chan_val){
	$objResponse = new xajaxResponse();
	if (is_array($_SESSION['callbacks'])){
		foreach ($_SESSION['callbacks'] as $key=> $callbacks){
			if ('local/'.$callbacks['legB'] = $chan_val){
				unset($_SESSION['callbacks'][$key]);
				break;
			}
		}
	}
	return $objResponse;
}

function hangup($channel){
	global $config,$locate;
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();
//	return $objResponse;

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		return;
	}
	$return = $myAsterisk->Hangup($channel);
	write_hangup_log($channel,$return);
	return $objResponse;
}

function write_hangup_log($channel,$events){
	if(LOG_ENABLED){
		$now = date("Y-M-d H:i:s");
		$fd = fopen(FILE_LOG,'a');
		
		$logMessage = '';
		if(is_array($events)){
			$logMessage = 'Response='.$events['Response'].'|Message='.$events['Message'];
		} else {
			$logMessage = $events;
		}
		
		$log = $now."|AMI|systemstatus-hangup|".$channel.'|'.$logMessage." \n";
		fwrite($fd,$log);
		fclose($fd);
	}
}


function invite($src,$dest,$creditLimit){
	global $config;
	$src = trim($src);
	$dest = trim($dest);
	$credit = trim($credit);
	$myAsterisk = new Asterisk();
	$objResponse = new xajaxResponse();
	
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");
	
	$strChannel = "local/".$src."@".$config['system']['outcontext']."/n";

	$_SESSION['callbacks'][$src.$dest] = array('legA' =>$dest,'legB' => $src, 'start' => 0, 'creditLimit' => $creditLimit);
	if ($config['system']['allow_dropcall'] == true){
		$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
							'WaitTime'=>30,
							'Exten'=>$dest,
							'Context'=>$config['system']['outcontext'],
							'Account'=>$_SESSION['curuser']['accountcode'],
							'Variable'=>"$strVariable",
							'Priority'=>1,
							'MaxRetries'=>0,
							'CallerID'=>$dest));
	}else{
		$myAsterisk->sendCall($strChannel,$dest,$config['system']['outcontext'],1,NULL,NULL,30,$dest,NULL,$_SESSION['curuser']['accountcode']);
	}
	// add to callback table
	$callback['lega'] = $dest;
	$callback['legb'] = $src;
	$callback['credit'] = $creditLimit;
	$callback['groupid'] = $_SESSION['curuser']['groupid'];
	astercc::insertNewCallback($callback);
	return $objResponse->getXML();
}

function addUnbilled($peer,$leg = null){
	$objResponse = new xajaxResponse();
	if ($_SESSION['curuser']['groupid'] ==""){
		return $objResponse;
	}
	$records = astercc::readUnbilled($peer,$leg,$_SESSION['curuser']['groupid']);
	if ($leg != null){
		$peer = 'local/'.$peer;
	}
	$totalprice = 0;
	
	while	($records->fetchInto($mycdr)){
		$price = '';
		$ratedesc = '';
		//$rate = astercc::readRate($mycdr['dst'],$_SESSION['curuser']['groupid']);
		$jsscript = "cdr = new Array();";

		$ratedesc = astercc::readRateDesc($mycdr['memo']).'&nbsp;';

		if ($price == '')
			$price = 0;
		$mycdr['destination'] .= '&nbsp;';
		$totalprice += $mycdr['credit'];
		$jsscript .= "cdr['id'] = '".$mycdr['id']."';";
		$jsscript .= "cdr['clid'] = '".$mycdr['clid']."';";
		//check it is inbound or outbound for show Phone in booth
		if ( $mycdr['src'] == $peer ){
			$jsscript .= "cdr['dst'] = '".$mycdr['dst']."';";
			$jsscript .= "cdr['direction'] = 'outbound';";
		}else{
			$jsscript .= "cdr['dst'] = '".$mycdr['src']."';";
			$jsscript .= "cdr['direction'] = 'inbound';";
		}
		$jsscript .= "cdr['startat'] = '".$mycdr['calldate']."';";
		$jsscript .= "cdr['billsec'] = '".$mycdr['billsec']."';";
		$jsscript .= "cdr['destination'] = '".$mycdr['destination']."';";
		$jsscript .= "cdr['rate'] = '".$ratedesc."';";
		$jsscript .= "cdr['price'] = '".astercc::creditDigits($mycdr['credit'])."';";
		$jsscript .= "appendTr('".$peer."-calllog-tbody',cdr);";
		$objResponse->addAssign($peer."-displayname","style.backgroundColor","#ff0000");
		$objResponse->addScript($jsscript);
	}
	$objResponse->addAssign($peer."-price","innerHTML",$totalprice);
	$objResponse->addAssign($peer."-unbilled","innerHTML",$totalprice);
	$objResponse->addScript("calculateBalance('".$peer."')");
	return $objResponse;
}

function checkOut($aFormValues,$divId,$payment){
	global $locate,$customers_db,$db,$config;
	//print_r($aFormValues);
	//echo $payment;exit;
	$iptCustomerId = $divId."-CustomerId";
	$iptDiscount = $divId."-CustomerDiscount";
	if($aFormValues[$iptCustomerId] != '' ){//&& $aFormValues[$iptDiscount] != 0
		$customerid = $aFormValues[$iptCustomerId];
		$discount = $aFormValues[$iptDiscount];
	}else{
		$customerid = 0;
		$discount = 0;
	}
	$objResponse = new xajaxResponse();
	if (isset($aFormValues['cdrid'])){
		foreach ($aFormValues['cdrid'] as $id){
			$res =  astercc::setBilled($id,$payment,$customerid,$discount);
			$credit += $res;
		}
		$objResponse->addAlert($locate->Translate("booth_cleared"));
		$objResponse->addAssign($divId."-price","innerHTML",0);
		$objResponse->addAssign($divId."-unbilled","innerHTML",0);
		$objResponse->addAssign($divId."-displayname","style.backgroundColor","");
	}

	if( $customerid > 0 ){
		$objResponse->addAssign($divId."-CustomerName",'value','');
		$objResponse->addAssign($divId."-CustomerId",'value','');
		$objResponse->addAssign($divId."-CustomerDiscount",'value','0');
		$objResponse->addAssign($divId."-btnCustomer",'value',$locate->Translate("Update"));

		$sql = "SELECT amount FROM ".$config['customers']['customertable']." WHERE id = $customerid ";

		$curamount = $customers_db->getOne($sql);

		$amount = $curamount + $credit * (1-$discount);

		$query = "UPDATE ".$config['customers']['customertable']." SET amount = $amount WHERE id = $customerid";
		$curamount = $customers_db->query($query);
	}
	$objResponse->addScript("removeTr('".$divId."');");
	$objResponse->addScript("calculateBalance('".$divId."');");
	return $objResponse;
}

function checkCustomer($pin,$divId){
	global $db,$customers_db,$locate,$config;

	$objResponse = new xajaxResponse();
	if( $pin == '' ) {
		$objResponse->addAssign($divId."-CustomerName",'value','');
		$objResponse->addAssign($divId."-CustomerId",'value','');
		$objResponse->addAssign($divId."-CustomerDiscount",'value','0');
		$objResponse->addAssign($divId."-btnCustomer",'value',$locate->Translate("Update"));
		$objResponse->addScript("calculateBalance('".$divId."');");
		return $objResponse;
	}

	$query = "SELECT * FROM ".$config['customers']['customertable']." WHERE pin='".$pin."'";
	$row =& $customers_db->getRow($query);

	if($row['id'] == ''){
		$objResponse->addAssign($divId."-CustomerName",'value','');
		$objResponse->addAssign($divId."-CustomerId",'value','');
		$objResponse->addAssign($divId."-CustomerDiscount",'value','0');
		$objResponse->addAssign($divId."-btnCustomer",'value',$locate->Translate("Update"));
		$objResponse->addScript("calculateBalance('".$divId."');");
		return $objResponse;
	}
	
	$objResponse->addAssign($divId."-CustomerName",'value',$row['first_name']." ".$row['last_name']);
	$objResponse->addAssign($divId."-CustomerId",'value',$row['id']);
	$objResponse->addAssign($divId."-btnCustomer",'value',$locate->Translate("Reset"));
	if( $row['discount'] == -1 ){
		$query = "SELECT discount FROM ".$config['customers']['discounttable']." WHERE amount <= '".$row['amount']."' ORDER BY amount DESC";
		$discount = & $customers_db->getOne($query);
	}else{
		$discount = $row['discount'];
	}
	
	if( $discount == '' ) $discount = 0;
	$objResponse->addAssign($divId."-CustomerDiscount",'value',$discount);
	$objResponse->addScript("calculateBalance('".$divId."');");
	return $objResponse;
}

function removeReceipt($id){
	$objResponse = new xajaxResponse();
	$objResponse->addRemove('rcdr-'.$id);
	return $objResponse;
}

function setFreeCallPage($id){
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Set Free Call"),"formDiv");  // <-- Set the title for your form.
	$html .= '<table border="1" width="100%" class="adminlist">
				<tr><td ><b>'.$locate->Translate("Are you sure to set this call free").'?</b></td><tr>
				<tr><td >'.$locate->Translate("note").':&nbsp;&nbsp;<textarea id="note"></textarea></td><tr>
				<tr><td >'.$locate->Translate("hidden record").':&nbsp;<input type="checkbox" id="hiddenrecord" value="1"></td><tr>
				<tr><td align="center"><input type="button" value="'.$locate->Translate("confirm").'" onclick="xajax_setFreeCall(\''.$id.'\',document.getElementById(\'hiddenrecord\').checked,document.getElementById(\'note\').value,document.getElementById(\'total_price_ori\').value,document.getElementById(\'discount\').value);">&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("cancel").'"  onclick="document.getElementById(\'formDiv\').style.visibility=\'hidden\';document.getElementById(\'formDiv\').innerHTML = \'\';return false;"></td><tr>
			 </table>';
	// End edit zone
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	
	return $objResponse;
}

function setFreeCall($id,$hiddenrecord,$note,$total,$discount){
	global $db;
	//$discount = 0.5;
	$objResponse = new xajaxResponse();

	$query = "SELECT * FROM mycdr WHERE id = $id";
	$row = $db->getRow($query);//print_r($row);exit;

	if($row['credit'] > 0 && $row['setfreecall'] != 'yes'){
		$total = $total - $row['credit'];
	}
	
	$query = "UPDATE mycdr SET note = '".$note."', setfreecall = 'yes' WHERE id = $id";

	if($db->query($query)){
		if($hiddenrecord == 'true'){
			$objResponse->addRemove('rcdr-'.$id);
		}else{
			$objResponse->addAssign("rprice-".$id, "innerHTML", '0.00');
			$objResponse->addAssign("rcdr-".$id, "style.background", '#d5c59f');
		}
	}

    $total_price = $total * (1-$discount);
    $total_price = astercc::creditDigits($total_price,2);
	$objResponse->addAssign("total_price", "innerHTML", $total_price);
	$objResponse->addAssign("total_price_ori", "value", $total);
	$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	$objResponse->addAssign("formDiv", "innerHTML", '');

	return $objResponse;
}

$xajax->processRequests();
?>
