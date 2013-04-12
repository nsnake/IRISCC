<?php
/*
	Freeiris2 -- An Opensource telephony project.
	Copyright (C) 2005 - 2009, Sun bing.
	Sun bing <hoowa.sun@gmail.com>

	See http://www.freeiris.org for more information about
	the Freeiris project.

	This program is free software, distributed under the terms of
	the GNU General Public License Version 2. See the LICENSE file
	at the top of the source tree.

	Freeiris2 -- 开源通信系统
	本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
	请查阅LICENSE文件。

*/
/* 
	this file : features settings

    $Id$
*/

/*------------------------------------
	include and initization of modules
--------------------------------------*/
require_once("../include/hprose/HproseHttpClient.php");
require_once("../include/smarty/Smarty.class.php");
require_once("../include/asteriskconf/asteriskconf.inc.php");
require_once("../include/freeiris_common_inc.php");

// rpc url
$rpcpbx = new HproseHttpClient($friconf['friextra_urlbase'].'/rpcpbx.php');

// init
$smarty = null;
web_initialization();


/*------------------------------------
	access permission and rpc health
--------------------------------------*/
session_start();
// 未授权用户
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] == false) {
	header('Location: '."index.php?action=page_relogin&callback=".urlencode($_SERVER['REQUEST_URI'])."\n\n");
	exit;
}
// RPC身份注册
sendrequest($rpcpbx->base_clientlogin($_SESSION['res_admin']['adminid'],$_SESSION['res_admin']['passwd']),0);

/*------------------------------------
	incoming action switcher
--------------------------------------*/
if (!isset($_REQUEST['action'])) page_features_key();

switch($_REQUEST['action']) {
	// other function
	case "do_features_key":
		do_features_key();
		break;
	default:
		page_features_key();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_features_key() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出features
	$rpcres = sendrequest($rpcpbx->features_hotkey_get('pickupexten'),0);
	$smarty->assign("pickupexten",$rpcres['pickupexten']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('blindxfer'),0);
	$smarty->assign("blindxfer",$rpcres['blindxfer']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('atxfer'),0);
	$smarty->assign("atxfer",$rpcres['atxfer']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('parkcall'),0);
	$smarty->assign("parkcall",$rpcres['parkcall']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('parkpos'),0);
	$smarty->assign("parkpos",$rpcres['parkpos']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('fri2automon'),0);
	$smarty->assign("fri2automon",$rpcres['fri2automon']);

	$rpcres = sendrequest($rpcpbx->features_hotkey_get('nway-start'),0);
	$smarty->assign("nwaystart",$rpcres['nway-start']);

	//取出特殊featerus localnumber号码
	$rpcres = sendrequest($rpcpbx->base_readconf('freeiris','freeiris.conf'),0);
	$smarty->assign("voicemailmain",$rpcres['resdata']['voicemail']['voicemailmain']);

	//取出特殊features localnumber号码
	$rpcres = sendrequest($rpcpbx->base_dbquery("select number from localnumber where assign like '".'agi://127.0.0.1/originate_diagnosis?%'."'"),1);
	$smarty->assign("originate_diagnosis",$rpcres['result_array'][0]['number']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_features_key.tpl');
	exit;
}

function do_features_key()
{
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//取出特殊featerus localnumber号码
	$vmres = sendrequest($rpcpbx->base_readconf('freeiris','freeiris.conf'),0);
	if ($vmres['resdata']['voicemail']['voicemailmain'] != $_REQUEST['voicemailmain'])	{
		//检测这个名字是否已经使用过了
		$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['voicemailmain']),1);
		if ($rpcres['resdata'])
			error_popbox(161,null,null,null,null,'submit_failed');

		//写回特别features设置
		sendrequest($rpcpbx->base_dbquery("update localnumber set number = '".$_REQUEST['voicemailmain']."' where number = '".$vmres['resdata']['voicemail']['voicemailmain']."'"));
		sendrequest($rpcpbx->base_updateconf('freeiris','freeiris.conf','voicemail','voicemailmain',$_REQUEST['voicemailmain']),1);

	}

	//取出特殊features localnumber号码
	$rpcres = sendrequest($rpcpbx->base_dbquery("select number from localnumber where assign like '".'agi://127.0.0.1/originate_diagnosis?%'."'"),1);
	if ($rpcres['result_array'][0]['number'] != $_REQUEST['originate_diagnosis']) {
		$newnumber = $rpcres['result_array'][0]['number'];
		//检测这个名字是否已经使用过了
		$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['originate_diagnosis']),1);
		if ($rpcres['resdata'])
			error_popbox(161,null,null,null,null,'submit_failed');

		sendrequest($rpcpbx->base_dbquery("update localnumber set number = '".$_REQUEST['originate_diagnosis']."' where number = '".$newnumber."'"));
	}

	//编辑参数
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('pickupexten',$_REQUEST['pickupexten']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('blindxfer',$_REQUEST['blindxfer']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('atxfer',$_REQUEST['atxfer']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('parkcall',$_REQUEST['parkcall']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('parkpos',$_REQUEST['parkpos']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('fri2automon',$_REQUEST['fri2automon']),1);
	$rpcres = sendrequest($rpcpbx->features_hotkey_set('nway-start',$_REQUEST['nwaystart']),1);

	error_popbox(160,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('pbx_features.php'),'submit_confirm');
}
?>