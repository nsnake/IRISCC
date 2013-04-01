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
	incoming action switcher
--------------------------------------*/
if (!isset($_REQUEST['action'])) page_login();

switch($_REQUEST['action']) {
	case "do_login":
		do_login();
		break;
	case "do_logout":
		do_logout();
		break;
	case "page_relogin":
		page_relogin();
		break;
	default:
		page_login();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_login() {
	global $smarty;
	smarty_output('cpanel/page_login.tpl');
	exit;
}
function page_relogin() {
	global $smarty;
	$smarty->assign("callback",urlencode($_REQUEST['callback']));
	smarty_output('cpanel/page_relogin.tpl');
	exit;
}
function do_login() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['adminid']) == "")
		error_popbox(103,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['passwd']) == "")
		error_popbox(103,null,null,null,null,'submit_failed');

	//发送请求验证login
	$rpcres = sendrequest($rpcpbx->base_clientlogin($_REQUEST['adminid'],md5($_REQUEST['passwd'])),1);

	//成功(不会在这里出现失败)
	session_cache_expire($friconf['session_expiry']);
	session_start();
	$_SESSION["admin"] = true;
	$_SESSION["res_admin"] = $rpcres['res_admin'];

	//回调地址
	if (trim($_REQUEST['callback']) != "") {
		error_popbox(null,null,null,null,$_REQUEST['callback'],'submit_successfuly');
	} else {
		error_popbox(null,null,null,null,'main.php','submit_successfuly');
	}

	exit;
}

function do_logout() 
{
	session_start();
	session_destroy();
	header('Location: '."index.php\n\n");
	exit;
}
?>