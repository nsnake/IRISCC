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
	this file : conference

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
if (!isset($_REQUEST['action'])) page_conference_list();

switch($_REQUEST['action']) {
	case "func_conference_edit":
		func_conference_edit();
		break;
	case "do_conference_edit";
		do_conference_edit();
		break;
	case "func_conference_add";
		func_conference_add();
		break;
	case "do_conference_add";
		do_conference_add();
		break;
	case "do_conference_delete";
		do_conference_delete();
		break;
	case "do_conference_kick";
		do_conference_kick();
		break;
	default:
		page_conference_list();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_conference_list() {
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
	if ($_REQUEST['order'] == 'cretime') {
		$order='order by cretime desc';
		$smarty->assign("order",$_REQUEST['order']);
	} else {
		$order='order by confno asc';
		$smarty->assign("order",'confno');
	}

	//取出所有
	$rpcres = sendrequest($rpcpbx->conference_list($order,$limit_from,$friconf['cols_in_page']),0);
	$conferences_ref=$rpcres['conferences'];

	//取出会议室里的人员
	foreach ($conferences_ref as $key => $value) {
		//增加第二个参数0，因为sendrequest的第二个参数不是可选。2015-01-11 0:21:13 By Coco老爸
		$rpcres = sendrequest($rpcpbx->ami_command(uniqid(),'meetme list '.$value['confno'].' concise'),0);

		$list_count=0;
		$listed_name=null;
		foreach (preg_split("/\n/",$rpcres['ami']['data']) as $value) {
			if (preg_match("/^[0-9]\!/",$value) == false)
				continue;
			$meetdata = preg_split("/\!/",$value);
			$listed_name.=$meetdata[1]."&";
			$list_count++;
		}
		$conferences_ref[$key]['list']=$list_count;
		$conferences_ref[$key]['listed_name']=$listed_name;
	}

	//总量
	$smarty->assign("maxcount",count($conferences_ref));
	//列表
	$smarty->assign("table_array",$conferences_ref);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_conference_list.tpl');
	exit;
}

function func_conference_add() {
	global $smarty;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_conference_add.tpl');

exit;
}

function do_conference_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['confno']) == "")
		error_popbox(190,null,null,null,null,'submit_failed');

	$insert['confno'] = $_REQUEST['confno'];
	$insert['pincode'] = $_REQUEST['pincode'];
	$insert['playwhenevent'] = $_REQUEST['playwhenevent'];
	$insert['mohwhenonlyone'] = $_REQUEST['mohwhenonlyone'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['confno']),1);
	if ($rpcres['resdata'])
		error_popbox(191,null,null,null,null,'submit_failed');

	//创建中继
	$rpcres = sendrequest($rpcpbx->conference_add($insert),1);

	//完成
	error_popbox(null,null,null,null,'acd_conference.php','submit_successfuly');

exit;
}


function func_conference_edit() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->conference_get($_REQUEST['confno']),1);
	//指定不存在
	if (!$rpcres['resdata'])
		error_page(192,$rpcres['response']['message'],null,null);

	$smarty->assign("conf",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_conference_edit.tpl');
exit;
}

function do_conference_edit() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['confno']) == "")
		error_popbox(190,null,null,null,null,'submit_failed');

	$insert['pincode'] = $_REQUEST['pincode'];
	$insert['playwhenevent'] = $_REQUEST['playwhenevent'];
	$insert['mohwhenonlyone'] = $_REQUEST['mohwhenonlyone'];

	//编辑
	$rpcres = sendrequest($rpcpbx->conference_edit($_REQUEST['confno'],$insert),1);

	//完成
	error_popbox(null,null,null,null,'acd_conference.php','submit_successfuly');

exit;
}


function do_conference_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['confno']) == "")
		error_popbox(190,null,null,null,null,'submit_failed');

	//删除
	$rpcres = sendrequest($rpcpbx->conference_delete($_REQUEST['confno']),1);

	//完成
	error_popbox(null,null,null,null,'acd_conference.php','submit_successfuly');

exit;
}

function do_conference_kick() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['confno']) == "")
		error_popbox(190,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->ami_command(uniqid(),'meetme kick '.$_REQUEST['confno'].' all'));

	//完成
	error_popbox(null,null,null,null,'acd_conference.php','submit_successfuly');

exit;
}



?>