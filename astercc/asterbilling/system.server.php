<?php
/*******************************************************************************
* system.server.php

* 配置管理系统后台文件
* system background management script

* Function Desc
	provide system management script

* 功能描述
	提供配置管理脚本

* Function Desc
		init				初始化页面元素

* Revision 0.0057  2009/03/28 15:47:00  last modified by donnie
* Desc: page created
********************************************************************************/
require_once ("db_connect.php");
require_once ("system.common.php");
require_once ('include/asterisk.class.php');

/**
*  initialize page elements
*
*/

function init(){
	global $config,$locate;
	$objResponse = new xajaxResponse();
	
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));

	$curchannels = getCurchannels();
	$objResponse->addAssign("curchanels","innerHTML",$curchannels);

	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	return $objResponse;
}

function systemAction($type){
	global $locate;
	$objResponse = new xajaxResponse();
	if($_SESSION['curuser']['usertype'] != 'admin') return $objResponse;

	$myAsterisk = new Asterisk();
	if($type == 'reload'){
		$r = $myAsterisk->reloadAsterisk();
		$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('asterisk have been reloaded')."</span");
	}elseif($type == "restartasterrc"){
		$pso = exec("ps -ef |grep -v grep |grep -E /asterr[a-z]{0,1\}[.\ ]+-d |awk '{print $2}'");
		
		$rk = exec("sudo /opt/asterisk/scripts/astercc/asterrc -k",$rkd);

		$rd = exec("sudo /opt/asterisk/scripts/astercc/asterrc -d",$rdd,$rdv);

		$psn = exec("ps -ef |grep -v grep |grep -E /asterr[a-z]{0,1\}[.\ ]+-d |awk '{print $2}'");
		if($psn == ''){
			$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('start asterrc failed, asterrc is not running')."</span");
		}elseif($psn != $pso){
			$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('asterrc have been restart')."</span");
		}elseif($psn == $pso ){
			$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('asterrc restart failed')."</span");
		}
		
	}elseif($type == "restart"){
		$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('asterisk have been restart')."</span");
		$myAsterisk->restartAsterisk();
		
	}elseif($type == "reboot"){
		exec ('sudo /sbin/shutdown -r now');
		$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('Server is rebooting')."...</span");
	}elseif($type == "shutdown"){
		exec ('sudo /sbin/shutdown -h now');
		$objResponse->addAssign("divmsg","innerHTML","<span class='passed'>".$locate->Translate('Server is shuting down')."...</span");
	}
	return $objResponse;
}

function getCurchannels(){
	global $config,$locate;

	$html = '<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="650"> ';
	$myAsterisk = new Asterisk();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();			
	if (!$res){
		return;
	}
	
	$res = $myAsterisk->execute("core show channels concise");

	$channels = explode("\n",$res);
	foreach($channels as $channel){
		if(strstr(strtolower($channel),'privilege') || trim($channel) == ''){
			continue;
		}
		$channel = explode('!',$channel);
		if(strtolower($channel[0]) == '') continue;

		$html .= '<tr bgcolor="#F7F7F7"><td  align="center" valign="center" height="30">'.$channel[0].'</td><td  align="center" valign="center" height="30"><a href="javascript:void(null)" onclick="xajax_hangupchnnel(\''.$channel[0].'\')">'.$locate->Translate('hangup').'</a></td></tr>';
	}
	$html .= '</table>';
	return $html;
}

function hangupchnnel($channel){
	global $config;

	$objResponse = new xajaxResponse();
	if($channel == ''){		
		$curchannels = getCurchannels();
		$objResponse->addAssign("curchanels","innerHTML",$curchannels);
		return $objResponse;
	}

	$myAsterisk = new Asterisk();

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();			
	if (!$res){
		return;
	}
	$myAsterisk->Hangup($channel);
	sleep(1);
	$curchannels = getCurchannels();
	$objResponse->addAssign("curchanels","innerHTML",$curchannels);
	return $objResponse;
	
}

$xajax->processRequests();
?>
