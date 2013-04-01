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
if (!isset($_REQUEST['action'])) page_queue_list();

switch($_REQUEST['action']) {
	case "func_queue_edit":
		func_queue_edit();
		break;
	case "do_queue_edit";
		do_queue_edit();
		break;
	case "func_queue_add";
		func_queue_add();
		break;
	case "do_queue_add";
		do_queue_add();
		break;
	case "do_queue_delete";
		do_queue_delete();
		break;
	default:
		page_queue_list();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_queue_list() {
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
		$order='order by queuenumber asc';
		$smarty->assign("order",'queuenumber');
	}

	//取出所有
	$rpcres = sendrequest($rpcpbx->queue_list($order,$limit_from,$friconf['cols_in_page']),0);
	foreach ($rpcres['queues'] as $key=>$value) {
		$membercount=0;
		foreach (preg_split("/\&/",$value['members']) as $member) {
			if ($member == '')
				continue;
			$membercount++;
		}
		$rpcres['queues'][$key]['members_count']=$membercount;
	}

	//总量
	$smarty->assign("maxcount",count($rpcres['queues']));
	//列表
	$smarty->assign("table_array",$rpcres['queues']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_queue_list.tpl');
	exit;
}

function func_queue_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出所有备选分机
	$rpcres = sendrequest($rpcpbx->extension_list('order by accountcode',0,65536),0);
	$smarty->assign("extensions",$rpcres['extensions']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_queue_add.tpl');

exit;
}

function do_queue_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['queuenumber']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['queuenumber']))
		error_popbox(200,null,null,null,null,'submit_failed');

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['queuenumber']),1);
	if ($rpcres['resdata'])
		error_popbox(201,null,null,null,null,'submit_failed');

	$insert['queuenumber'] = $_REQUEST['queuenumber'];
	$insert['queuename'] = $_REQUEST['queuename'];
	$insert['strategy'] = $_REQUEST['strategy'];
	$insert['timeout'] = $_REQUEST['timeout'];
	$insert['announce'] = $_REQUEST['announce'];
	$insert['members'] = $_REQUEST['members'];
	$insert['playring'] = $_REQUEST['playring'];
	$insert['saymember'] = $_REQUEST['saymember'];
	$insert['periodic-announce-frequency'] = $_REQUEST['periodic-announce-frequency'];
	$insert['queuetimeout'] = $_REQUEST['queuetimeout'];
	$insert['failedon'] = $_REQUEST['failedon'];

	//创建队列
	$rpcres = sendrequest($rpcpbx->queue_add($insert),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(202,null,null,null,'pbx_reload.php?action=reload&area=queue&return='.urlencode('acd_queue.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'acd_queue.php','submit_successfuly');
	}

exit;
}


function func_queue_edit() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->queue_get($_REQUEST['queuenumber']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(113,$rpcres['response']['message'],null,null);

	$smarty->assign("queue",$rpcres['resdata']);

	$rpcres['confdata']['periodicannouncefrequency'] = $rpcres['confdata']['periodic-announce-frequency'];
	$smarty->assign("queueconf",$rpcres['confdata']);
	$members_res = $rpcres['resdata']['members_res'];

	//取出所有备选分机
	$rpcres = sendrequest($rpcpbx->extension_list('order by accountcode',0,65536),0);
	$extensions=array();
	//(排除已选)
	foreach ($rpcres['extensions'] as $exten) {
		$nosave=false;
		foreach ($members_res as $member) {
			if ($exten['accountcode'] == $member['accountcode']) {
				$nosave=true;
				break;
			}
		}
		if ($nosave != true) {
			array_push($extensions,$exten);
		}
	}
	$smarty->assign("extensions",$extensions);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_queue_edit.tpl');
exit;
}

function do_queue_edit() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['queuenumber']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['queuenumber']))
		error_popbox(113,null,null,null,null,'submit_failed');

	$insert['queuename'] = $_REQUEST['queuename'];
	$insert['strategy'] = $_REQUEST['strategy'];
	$insert['timeout'] = $_REQUEST['timeout'];
	$insert['announce'] = $_REQUEST['announce'];
	$insert['members'] = $_REQUEST['members'];
	$insert['playring'] = $_REQUEST['playring'];
	$insert['saymember'] = $_REQUEST['saymember'];
	$insert['periodic-announce-frequency'] = $_REQUEST['periodic-announce-frequency'];
	$insert['queuetimeout'] = $_REQUEST['queuetimeout'];
	$insert['failedon'] = $_REQUEST['failedon'];

	//创建队列
	$rpcres = sendrequest($rpcpbx->queue_edit($_REQUEST['queuenumber'],$insert),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(203,null,null,null,'pbx_reload.php?action=reload&area=queue&return='.urlencode('acd_queue.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'acd_queue.php','submit_successfuly');

	}

exit;
}

function do_queue_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['queuenumber']) == "")
		error_popbox(190,null,null,null,null,'submit_failed');

	//
	$rpcres = sendrequest($rpcpbx->queue_delete($_REQUEST['queuenumber']),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(202,null,null,null,'pbx_reload.php?action=reload&area=queue&return='.urlencode('acd_queue.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'acd_queue.php','submit_successfuly');
	}

exit;
}


?>