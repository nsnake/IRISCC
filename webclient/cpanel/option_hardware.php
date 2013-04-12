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
	this file : option settings

    $Id: option_hardware.php 312 2009-11-09 07:21:44Z hoowa $
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
if (!isset($_REQUEST['action'])) page_option_hardware();

switch($_REQUEST['action']) {
	case "func_analog_option";
		func_analog_option();
		break;
	case "do_analog_option";
		do_analog_option();
		break;
	case "func_digital_option";
		func_digital_option();
		break;
	case "do_digital_option";
		do_digital_option();
		break;
	case "do_hardware_set";
		do_hardware_set();
		break;
	case "do_chan_dahdi_set";
		do_chan_dahdi_set();
		break;
	default:
		page_option_hardware();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_option_hardware() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','hardwaretel_info.conf','common'),0);
	$smarty->assign("common",$rpcres['common']);
	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','chan_dahdi.conf','channels'),0);
	$smarty->assign("chan_dahdi",$rpcres['channels']);

	#getout map files
	$rpcres = sendrequest($rpcpbx->option_confile_stream('tmp','hardwaretel.map','analog'),0);
	$hardware_map=array();
	$analog_spans=array();
	$digital_spans=array();
	$current='';
	foreach (preg_split("/\n/",$rpcres['resdata']) as $value) {
		if (preg_match("[analog]",$value)) {
			$current='analog';
			$hardware_map[$current]=array();
			continue;
		}
		if (preg_match("[digital]",$value)) {
			$current='digital';
			$hardware_map[$current]=array();
			continue;
		}
		if (trim($value)=="")
			continue;
		$keyvalue=preg_split("/\=/",$value);
		if ($keyvalue[0] == 'span') {
			if ($current == 'analog')
				array_push($analog_spans,$keyvalue[1]);
			if ($current == 'digital')
				array_push($digital_spans,$keyvalue[1]);
		} else {
			$hardware_map[$current][$keyvalue[0]]=$keyvalue[1];
		}
	}
	$hardware_map['analog']['spans']=$analog_spans;
	$hardware_map['digital']['spans']=$digital_spans;
	$smarty->assign("hardware_map",$hardware_map);

	$rpcres=sendrequest($rpcpbx->hardware_card_stat(),0);
	$smarty->assign("cardstat",$rpcres['cardstat']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_option_hardware.tpl');
	exit;
}

function func_analog_option() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','hardwaretel_info.conf','analog'),0);
	$smarty->assign("analog",$rpcres['analog']);
	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','chan_dahdi.conf','channels'),0);
	$smarty->assign("chan_dahdi",$rpcres['channels']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_analog_option.tpl');
	exit;
}

function do_analog_option() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if ($_REQUEST['type'] == 'hardware') {
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','analog','fxo_protocol',$_REQUEST['fxo_protocol']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','analog','fxs_protocol',$_REQUEST['fxs_protocol']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','general','writenewdriverconf','yes'),1);
		error_popbox(530,null,null,null,'option_hardware.php','submit_confirm');

	} elseif ($_REQUEST['type'] == 'fxo') {
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','busydetect',$_REQUEST['busydetect']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','busycount',$_REQUEST['busycount']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','busypattern',$_REQUEST['busypattern']),1);
		
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','pulsedial',$_REQUEST['pulsedial']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','answeronpolarityswitch',$_REQUEST['answeronpolarityswitch']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','hanguponpolarityswitch',$_REQUEST['hanguponpolarityswitch']),1);

		error_popbox(531,null,null,null,'pbx_reload.php?action=reload&area=softrestart&return='.urlencode('option_hardware.php'),'submit_confirm');
	}

	exit;
}

function func_digital_option() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','hardwaretel_info.conf','digital'),0);
	$smarty->assign("digital",$rpcres['digital']);
	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','chan_dahdi.conf','channels'),0);
	$smarty->assign("chan_dahdi",$rpcres['channels']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_digital_option.tpl');
	exit;
}

function do_digital_option() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if ($_REQUEST['type'] == 'hardware') {
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','span_timesource',$_REQUEST['span_timesource']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','span_lbo',$_REQUEST['span_lbo']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','span_framing',$_REQUEST['span_framing']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','span_coding',$_REQUEST['span_coding']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','span_option',$_REQUEST['span_option']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','digital','dchan_num',$_REQUEST['dchan_num']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','general','writenewdriverconf','yes'),1);
		error_popbox(534,null,null,null,'option_hardware.php','submit_confirm');

	} elseif ($_REQUEST['type'] == 'chan_dahdi') {
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','switchtype',$_REQUEST['switchtype']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','pridialplan',$_REQUEST['pridialplan']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','prilocaldialplan',$_REQUEST['prilocaldialplan']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','resetinterval',$_REQUEST['resetinterval']),1);
		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','overlapdial',$_REQUEST['overlapdial']),1);

//		sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','signalling',$_REQUEST['signalling']),1);
		sendrequest($rpcpbx->hardware_chandahdi_signalling_set('pri',$_REQUEST['signalling']),1);

		error_popbox(535,null,null,null,'pbx_reload.php?action=reload&area=softrestart&return='.urlencode('option_hardware.php'),'submit_confirm');
	}

	exit;
}


function do_hardware_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','common','loadzone',$_REQUEST['loadzone']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','common','defaultzone',$_REQUEST['loadzone']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','indications.conf','general','country',$_REQUEST['loadzone']),1);

	sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','common','echocanceller',$_REQUEST['echocanceller']),1);

	sendrequest($rpcpbx->option_confkey_edit('freeiris','hardwaretel_info.conf','general','writenewdriverconf','yes'),1);

	error_popbox(532,null,null,null,'option_hardware.php','submit_confirm');
	exit;
}

function do_chan_dahdi_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','echocancel',$_REQUEST['echocancel']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','cidsignalling',$_REQUEST['cidsignalling']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','cidstart',$_REQUEST['cidstart']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','rxgain',$_REQUEST['rxgain']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','chan_dahdi.conf','channels','txgain',$_REQUEST['txgain']),1);

	error_popbox(533,null,null,null,'pbx_reload.php?action=reload&area=softrestart&return='.urlencode('option_hardware.php'),'submit_confirm');
	exit;
}

?>