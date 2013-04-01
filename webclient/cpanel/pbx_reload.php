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
	this file :

    $Id$
*/

/*------------------------------------
	include and initization of modules
--------------------------------------*/
require_once("../include/phprpc/phprpc_client.php");
require_once("../include/smarty/Smarty.class.php");
require_once("../include/asteriskconf/asteriskconf.inc.php");
require_once("../include/freeiris_common_inc.php");

// rpc url
$rpcpbx = new PHPRPC_Client($friconf['friextra_urlbase'].'/rpcpbx.php');

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
if (!isset($_REQUEST['action'])) page_reload();

switch($_REQUEST['action']) {
	case "reload";
		do_reload();
		break;
	case "restart";
		do_restart();
		break;
	default:
		page_reload();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_reload() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_reload.tpl');
	exit;
}

function do_reload() {
	global $smarty;
	global $rpcpbx;

	$actionid = uniqid();

	switch($_REQUEST['area']) {
		case "sip";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'sip reload'),0);
			break;
		case "iax2";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'iax2 reload'),0);
			break;
		case "dialplan";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'dialplan reload'),0);
			break;
		case "moh";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'moh reload'),0);
			break;
		case "queue";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'module reload app_queue.so'),0);
			break;
		case "rtp";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'module reload rtp'),0);
			break;
		case "chan_dahdi";# can't use reload
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'module load chan_dahdi.so'),0);
			sleep(1);
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'dahdi restart'),0);
			sleep(6);
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'dahdi restart'),0);
			sleep(1);
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'reload'),0);
			break;
		case "softrestart";
			$rpcpbx->ami_command($actionid,'restart now');
			break;
		case "all";
			$rpcres = sendrequest($rpcpbx->ami_command($actionid,'reload'),0);
			break;
	}

	header("location: ".$_REQUEST['return']."\n\n");
	exit;
}

function do_restart() {
	global $smarty;
	global $rpcpbx;

	switch($_REQUEST['area']) {
		case "fri2d";
			$rpcres = sendrequest($rpcpbx->system_restart('fri2d'),0);
			break;
		case "all";
			$rpcres = sendrequest($rpcpbx->system_restart('reboot'),0);
			break;
	}

	header("location: ".$_REQUEST['return']."\n\n");
	exit;
}

?>