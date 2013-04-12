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

    $Id: option_voip.php 186 2009-05-26 12:42:09Z hoowa $
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
if (!isset($_REQUEST['action'])) page_option_voip();

switch($_REQUEST['action']) {
	case "do_option_voip_sip_set";
		do_option_voip_sip_set();
		break;
	case "do_option_voip_iax_set";
		do_option_voip_iax_set();
		break;
	case "do_option_voip_rtp_set";
		do_option_voip_rtp_set();
		break;
	default:
		page_option_voip();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_option_voip() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','sip.conf','general'),0);
	$smarty->assign("sip",$rpcres['general']);
	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','iax.conf','general'),0);
	$smarty->assign("iax",$rpcres['general']);
	$rpcres = sendrequest($rpcpbx->option_confsection_get('asterisk','rtp.conf','general'),0);
	$smarty->assign("rtp",$rpcres['general']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_option_voip.tpl');
	exit;
}

function do_option_voip_sip_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if (trim($_REQUEST['bindport']) == "" || preg_match("/[^0-9]/",$_REQUEST['bindport']))
		error_popbox(510,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['maxexpiry']) == "" || preg_match("/[^0-9]/",$_REQUEST['maxexpiry']))
		error_popbox(511,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['minexpiry']) == "" || preg_match("/[^0-9]/",$_REQUEST['minexpiry']))
		error_popbox(511,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['defaultexpiry']) == "" || preg_match("/[^0-9]/",$_REQUEST['defaultexpiry']))
		error_popbox(511,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['allow']) == "")
		error_popbox(512,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['maxcallbitrate']) == "" || preg_match("/[^0-9]/",$_REQUEST['maxcallbitrate']))
		error_popbox(513,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','allowguest',$_REQUEST['allowguest']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','bindport',$_REQUEST['bindport']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','maxexpiry',$_REQUEST['maxexpiry']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','minexpiry',$_REQUEST['minexpiry']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','defaultexpiry',$_REQUEST['defaultexpiry']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','allow',$_REQUEST['allow']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','progressinband',$_REQUEST['progressinband']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','videosupport',$_REQUEST['videosupport']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','sip.conf','general','maxcallbitrate',$_REQUEST['maxcallbitrate']),1);

	error_popbox(514,null,null,null,'pbx_reload.php?action=reload&area=sip&return='.urlencode('option_voip.php'),'submit_confirm');
	exit;
}

function do_option_voip_iax_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if (trim($_REQUEST['bindport']) == "" || preg_match("/[^0-9]/",$_REQUEST['bindport']))
		error_popbox(510,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['maxregexpire']) == "" || preg_match("/[^0-9]/",$_REQUEST['maxregexpire']))
		error_popbox(511,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['minregexpire']) == "" || preg_match("/[^0-9]/",$_REQUEST['minregexpire']))
		error_popbox(511,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['allow']) == "")
		error_popbox(512,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->option_confkey_edit('asterisk','iax.conf','general','bindport',$_REQUEST['bindport']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','iax.conf','general','maxregexpire',$_REQUEST['maxregexpire']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','iax.conf','general','minregexpire',$_REQUEST['minregexpire']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','iax.conf','general','allow',$_REQUEST['allow']),1);

	error_popbox(515,null,null,null,'pbx_reload.php?action=reload&area=iax2&return='.urlencode('option_voip.php'),'submit_confirm');
	exit;
}

function do_option_voip_rtp_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if (trim($_REQUEST['rtpstart']) == "" || preg_match("/[^0-9]/",$_REQUEST['rtpstart']))
		error_popbox(510,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['rtpend']) == "" || preg_match("/[^0-9]/",$_REQUEST['rtpend']))
		error_popbox(511,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->option_confkey_edit('asterisk','rtp.conf','general','rtpstart',$_REQUEST['rtpstart']),1);
	sendrequest($rpcpbx->option_confkey_edit('asterisk','rtp.conf','general','rtpend',$_REQUEST['rtpend']),1);

	error_popbox(516,null,null,null,'pbx_reload.php?action=reload&area=rtp&return='.urlencode('option_voip.php'),'submit_confirm');
	exit;
}

?>