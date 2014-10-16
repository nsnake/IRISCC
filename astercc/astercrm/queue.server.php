<?php
/*******************************************************************************
* queue.server.php

* 队列管理系统后台文件
* queue management script

* Function Desc

* 功能描述

* Function Desc
		init				初始化页面元素

* Revision 0.0456  2007/11/07 16:10:00  last modified by solo
* Desc: page created

*/
require_once ("queue.common.php");
require_once ("db_connect.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/common.class.php');

function init(){
	global $locate,$config;
	$objResponse = new xajaxResponse();

	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res){
		$objResponse->addAssign("divAMIStatus", "innerHTML", $locate->Translate("AMI_connection_failed"));
	}
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->addAssign("msgChannelsInfo", "value", $locate->Translate("msgChannelsInfo"));

	return $objResponse;
}

function showQueuesStatus(){
	global $config;
	$objResponse = new xajaxResponse();

	$myAsterisk = new Asterisk();
	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	
	if (!$res){
		$objResponse->addAssign("divAMIStatus", "innerHTML", $locate->Translate("AMI_connection_failed"));
	}else{
		$peer = $myAsterisk->command("show queues");
		if(!strpos($peer['data'], ':'))
	       echo $peer['data'];
		else{
			//print $peer['data'];
			$data = array();
			$HTML .= "<table>";

			foreach(explode("\n", $peer['data']) as $line){
				$a = strpos('z'.$line, ':') - 1;
				if($a >= 0) {
					$data[trim(substr($line, 0, $a))] = trim(substr($line, $a + 1));
					$HTML .= "<tr><td>".trim(substr($line, 0, $a))."</td></tr>";
					$HTML .= "<tr><td>".trim(substr($line, $a + 1))."</td></tr>";
				}
				//print_r(trim(substr($line, $a + 1)));
				//exit;
			}

			//foreach ($data as $row){
			//}
			$HTML .= "</table>";
//			print_r($data);
		}
	}
	//print $HTML;
	$objResponse->addAssign("divQueue","innerHTML",$HTML);
	//	print_r($myAsterisk->QueueStatus());

	return $objResponse;
}

$xajax->processRequests();
?>
