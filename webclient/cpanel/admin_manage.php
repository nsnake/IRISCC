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
if (!isset($_REQUEST['action'])) page_admin_manage();

switch($_REQUEST['action']) {
#	case "do_admin_profile_edit":
#		do_admin_profile_edit();
#		break;
	case "do_admin_add";
		do_admin_add();
		break;
	case "do_admin_delete";
		do_admin_delete();
		break;
	case "do_admin_edit";
		do_admin_edit();
		break;
	case "func_admin_edit":
		func_admin_edit();
		break;
	case "func_admin_add":
		func_admin_add();
		break;
	default:
		page_admin_manage();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_admin_manage() {
	global $smarty;
	global $rpcpbx;
	global $friconf;


	//分页显示程序
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

	//排序程序
	$order='';
	if ($_REQUEST['order'] == 'adminid') {
		$order='order by adminid desc';
		$smarty->assign("order",$_REQUEST['order']);
	} elseif ($_REQUEST['order'] == 'level') {
		$order='order by level desc';
		$smarty->assign("order",$_REQUEST['order']);
	} elseif ($_REQUEST['order'] == 'cretime') {
		$order='order by cretime desc';
		$smarty->assign("order",$_REQUEST['order']);
	} else {
		$order='order by cretime desc';
		$smarty->assign("order",'cretime');
	}


	//取出所有的帐户
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from admin $order limit $limit_from,".$friconf['cols_in_page']),0);

	//总量
	$smarty->assign("admin_count",count($rpcres['result_array']));
	//列表
	$smarty->assign("admin_array",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_admin_manage.tpl');
	exit;
}

function func_admin_add() {
	global $smarty;
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_admin_add.tpl');
exit;
}

function do_admin_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['adminid']) == "")
		error_popbox(103,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['curpasswd']) == "")
		error_popbox(103,null,null,null,null,'submit_failed');
	if ($_REQUEST['curpasswd'] != $_REQUEST['newpasswd'])
		error_popbox(105,null,null,null,null,'submit_failed');

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from admin where adminid = '".$_REQUEST['adminid']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(112,null,null,null,null,'submit_failed');

	//注册管理员
	$rpcres = sendrequest($rpcpbx->base_dbquery("insert into admin set adminid='".$_REQUEST['adminid']."',passwd=md5('".$_REQUEST['curpasswd']."'),remark='".$_REQUEST['remark']."',level='".$_REQUEST['level']."',cretime=now()"),1);

	//完成
	error_popbox(null,null,null,null,'admin_manage.php','submit_successfuly');

exit;
}

function func_admin_edit() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from admin where adminid = '".$_REQUEST['adminid']."'"),0);
	$smarty->assign("thisadmin",$rpcres['result_array'][0]);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_admin_edit.tpl');

exit;
}

function do_admin_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//忘记填写参数
	if (trim($_REQUEST['adminid']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');
	//如果帐号错了
	if ($_SESSION['res_admin']['adminid'] == $_REQUEST['adminid'])
		error_popbox(114,null,null,null,null,'submit_failed');

	//删除这个帐号
	$rpcres = sendrequest($rpcpbx->base_dbquery("delete from admin where adminid = '".$_REQUEST['adminid']."'"),1);

	//完成
	error_popbox(null,null,null,null,'admin_manage.php','submit_successfuly');
exit;
}

function do_admin_edit() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$changelist = array();

	//忘记填写参数
	if (trim($_REQUEST['curpasswd']) == "" || md5($_REQUEST['curpasswd']) != $_SESSION['res_admin']['passwd']) {
		error_popbox(107,null,null,null,null,'submit_failed');
		exit;
	}
	if (trim($_REQUEST['newpasswd']) != "" && $_REQUEST['newpasswd'] != $_REQUEST['renewpasswd']) {
		error_popbox(105,null,null,null,null,'submit_failed');
		exit;
	}
	if (trim($_REQUEST['newpasswd']) == "") {
		error_popbox(111,null,null,null,null,'submit_failed');
		exit;
	}

	//更新密码
	$rpcres = sendrequest($rpcpbx->base_dbquery("update admin set passwd=md5('".$_REQUEST['newpasswd']."') where adminid = '".$_REQUEST['adminid']."'"),1);

	error_popbox(null,null,null,null,'admin_manage.php','submit_successfuly');

exit;
}
?>