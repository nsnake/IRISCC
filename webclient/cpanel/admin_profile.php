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
if (!isset($_REQUEST['action'])) page_admin_profile();

switch($_REQUEST['action']) {
	case "do_admin_profile_edit":
		do_admin_profile_edit();
		break;
	default:
		page_admin_profile();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_admin_profile() {
	global $smarty;

	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_admin_profile.tpl');
	exit;
}

function do_admin_profile_edit() {
	global $rpcpbx;
	global $smarty;

	$changelist = array();

	//忘记填写参数
	if (trim($_REQUEST['curpasswd']) == "" || md5($_REQUEST['curpasswd']) != $_SESSION['res_admin']['passwd'])	
		error_popbox(104,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['newpasswd']) != "" && $_REQUEST['newpasswd'] != $_REQUEST['renewpasswd'])
		error_popbox(105,null,null,null,null,'submit_failed');

	//准备数据
	if (trim($_REQUEST['newpasswd']) != "")
		$changelist['passwd']=md5($_REQUEST['newpasswd']);

	//更新密码
	$rpcres = sendrequest($rpcpbx->admin_profile_edit($_SESSION['res_admin']['adminid'],$changelist),1);

	error_popbox(106,null,null,null,'index.php?action=do_logout','submit_successfuly');

exit;
}

?>