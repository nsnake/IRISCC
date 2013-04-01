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
if (!isset($_REQUEST['action'])) page_group_list();

switch($_REQUEST['action']) {
	case "do_group_add";
		do_group_add();
		break;
	case "do_group_delete";
		do_group_delete();
		break;
	case "do_group_edit";
		do_group_edit();
		break;
	case "func_group_edit":
		func_group_edit();
		break;
	case "func_group_add":
		func_group_add();
		break;
	default:
		page_group_list();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_group_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//排序程序
	$order='';
	if ($_REQUEST['order'] == 'groupid') {
		$order='order by groupid desc';
		$smarty->assign("order",$_REQUEST['order']);
	} elseif ($_REQUEST['order'] == 'groupname') {
		$order='order by groupname desc';
		$smarty->assign("order",$_REQUEST['order']);
	} else {
		$order='order by cretime desc';
		$smarty->assign("order",'cretime');
	}

	//取出所有的帐户
	$rpcres = sendrequest($rpcpbx->extengroup_list($order),0);

	//列表
	$smarty->assign("table_array",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_exten_group_list.tpl');
	exit;
}

function func_group_add() {
	global $smarty;
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_group_add.tpl');
exit;
}

function do_group_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['groupname']) == "")
		error_popbox(131,null,null,null,null,'submit_failed');

	//操作分组
	$rpcres = sendrequest($rpcpbx->extengroup_add($_REQUEST['groupname'],$_REQUEST['remark']),1);

	//完成
	error_popbox(null,null,null,null,'exten_group.php','submit_successfuly');

exit;
}

function func_group_edit() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extengroup_get($_REQUEST['groupid']),0);

	//基本
	$smarty->assign("this",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_group_edit.tpl');
exit;
}

function do_group_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['groupid']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	//删除这个分组
	$rpcres = sendrequest($rpcpbx->extengroup_delete($_REQUEST['groupid']),1);

	//完成
	error_popbox(null,null,null,null,'exten_group.php','submit_successfuly');
exit;
}

function do_group_edit() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['groupname']) == "")
		error_popbox(131,null,null,null,null,'submit_failed');

	//编辑分组
	$rpcres = sendrequest($rpcpbx->extengroup_edit($_REQUEST['groupid'],array('groupname'=>$_REQUEST['groupname'],'remark'=>$_REQUEST['remark'])),1);

	error_popbox(null,null,null,null,'exten_group.php','submit_successfuly');

exit;
}
?>