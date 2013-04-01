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
	this file : queue

    $Id: acd_outgoing.php 361 2010-01-28 12:52:06Z hoowa $
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
if (!isset($_REQUEST['action'])) page_outgoing_list();

switch($_REQUEST['action']) {
	case "func_outgoing_add";
		func_outgoing_add();
		break;
	case "do_outgoing_add";
		do_outgoing_add();
		break;
//	case "do_outgoing_delete";
//		do_outgoing_delete();
//		break;
	case "func_outgoing_members_list";
		func_outgoing_members_list();
		break;
	default:
		page_outgoing_list();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_outgoing_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//分页显示计算
	$limit_from=0;
	if (!$_REQUEST['cols_in_page'] || $_REQUEST['cols_in_page'] == 'frist' || $_REQUEST['cols_in_page'] < $friconf['cols_in_page']) {
		$limit_from=0;
		$smarty->assign("pre_cols",0);
		$smarty->assign("next_cols",$friconf['cols_in_page']);
	} else {
		$limit_from=$_REQUEST['cols_in_page'];
		$smarty->assign("pre_cols",$_REQUEST['cols_in_page']-$friconf['cols_in_page']);
		$smarty->assign("next_cols",($_REQUEST['cols_in_page']+$friconf['cols_in_page']));
	}
	$smarty->assign("from_cols",($limit_from+1));
	$smarty->assign("to_cols",($limit_from+$friconf['cols_in_page']));

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from outgoing order by cretime desc limit ".$limit_from.','.$friconf['cols_in_page']),0);

	//列表
	$smarty->assign("table_array",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_outgoing_list.tpl');
	exit;
}

function func_outgoing_members_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from outgoing_members where outgoingid = '".$_REQUEST['outgoingid']."' order by id asc"),0);
	//列表
	$smarty->assign("table_array",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_outgoing_members_list.tpl');
	exit;
}

function func_outgoing_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_outgoing_add.tpl');

exit;
}

function do_outgoing_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['name']) == "")
		error_popbox(401,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['concurrent']) == "" || preg_match("/[^0-9]/",$_REQUEST['concurrent']))
		error_popbox(402,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['startime_date']) == "")
		error_popbox(403,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['members']) == "")
		error_popbox(404,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['localnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['localnumber']))
		error_popbox(405,null,null,null,null,'submit_failed');

	$insert['name'] = $_REQUEST['name'];
	$insert['concurrent'] = $_REQUEST['concurrent'];
	$insert['startime'] = $_REQUEST['startime_date'].' '.$_REQUEST['startime_hour']. ':' . $_REQUEST['startime_minute'] . ':00';
	$insert['localnumber'] = $_REQUEST['localnumber'];
	$insert['outgoing_callerid'] = $_REQUEST['outgoing_callerid'];
	$insert['outgoing_waittime'] = $_REQUEST['outgoing_waittime'];

	$_REQUEST['members'] = preg_replace("/\r/","",$_REQUEST['members']);
	$insert['members']=array();
	foreach (explode("\n",$_REQUEST['members']) as $value) {
		trim($value);
		if ($value != "")
			array_push($insert['members'],$value);
	}

	//创建计划
	$rpcres = sendrequest($rpcpbx->outgoing_add($insert),1);

	//完成
	error_popbox(null,null,null,null,'acd_outgoing.php','submit_successfuly');

exit;
}


//function do_outgoing_delete() {
//	global $smarty;
//	global $rpcpbx;
//	global $friconf;
//
//	//不填绝对不行的
//	if (trim($_REQUEST['id']) == "")
//		error_popbox(190,null,null,null,null,'submit_failed');
//
//	//
//	$rpcres = sendrequest($rpcpbx->outgoing_delete($_REQUEST['id']),1);
//
//	//完成
//	error_popbox(null,null,null,null,'acd_outgoing.php','submit_successfuly');
//
//exit;
//}


?>