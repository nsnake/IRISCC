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
if (!isset($_REQUEST['action'])) page_option_general();

switch($_REQUEST['action']) {
	case "do_option_general_set";
		do_option_general_set();
		break;
	default:
		page_option_general();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_option_general() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出fastagi
	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','freeiris.conf','fastagi'),0);
	$smarty->assign("fastagi",$rpcres['fastagi']);
	//取出voicemail
	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','freeiris.conf','voicemail'),0);
	$smarty->assign("voicemail",$rpcres['voicemail']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_option_general.tpl');
	exit;
}

function do_option_general_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if ($_REQUEST['section'] == 'fastagi') {
		$section = 'fastagi';

		if (trim($_REQUEST['dial_ringtime']) == "" || preg_match("/[^0-9]/",$_REQUEST['dial_ringtime']))
			error_popbox(501,null,null,null,null,'submit_failed');

		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'dial_ringtime',$_REQUEST['dial_ringtime']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'dial_addional',$_REQUEST['dial_addional']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'diallocal_failed',$_REQUEST['diallocal_failed']),1);
        if ($_REQUEST['router_extenrule_default'] != 'enable') {
            sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'router_extenrule_default','disabled'),1);
        } else {
            sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'router_extenrule_default',$_REQUEST['router_extenrule_default']),1);
        }
        if ($_REQUEST['router_trunkrule_default'] != 'enable') {
            sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'router_trunkrule_default','disabled'),1);
        } else {
            sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'router_trunkrule_default',$_REQUEST['router_trunkrule_default']),1);
        }

		error_popbox(517,null,null,null,'option_general.php','submit_confirm');

	} elseif ($_REQUEST['section'] == 'voicemail') {
		$section = 'voicemail';

		if (trim($_REQUEST['usermax']) == "" || preg_match("/[^0-9]/",$_REQUEST['usermax']))
			error_popbox(502,null,null,null,null,'submit_failed');
		if (trim($_REQUEST['silence']) == "" || preg_match("/[^0-9]/",$_REQUEST['silence']))
			error_popbox(503,null,null,null,null,'submit_failed');
		if (trim($_REQUEST['maxduration']) == "" || preg_match("/[^0-9]/",$_REQUEST['maxduration']))
			error_popbox(504,null,null,null,null,'submit_failed');

		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'enable',$_REQUEST['enable']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'usermax',$_REQUEST['usermax']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'silence',$_REQUEST['silence']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'maxduration',$_REQUEST['maxduration']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'vmmainsayinbox',$_REQUEST['vmmainsayinbox']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'uvmainsayheader',$_REQUEST['uvmainsayheader']),1);

		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'mailer',$_REQUEST['mailer']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'mailer_attachvoice',$_REQUEST['mailer_attachvoice']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'mailer_from',$_REQUEST['mailer_from']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'smtp_host',$_REQUEST['smtp_host']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'smtp_port',$_REQUEST['smtp_port']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'smtp_auth',$_REQUEST['smtp_auth']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'smtp_username',$_REQUEST['smtp_username']),1);
		sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf',$section,'smtp_password',$_REQUEST['smtp_password']),1);

		error_popbox(null,null,null,null,'option_general.php','submit_successfuly');

	} else {
		error_popbox(113,null,null,null,null,'submit_failed');
	}

	exit;
}

?>
