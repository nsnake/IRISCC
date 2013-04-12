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
	this file : analyze callsession watch

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
if (!isset($_REQUEST['action'])) page_callsession_main();

switch($_REQUEST['action']) {
	case "func_sessionflow_list":
		func_sessionflow_list();
		break;
	default:
		page_callsession_main();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_callsession_main() {
	global $smarty;
	global $rpcpbx;
	global $friconf;


	//分页显示计算
	$cols_in_page=30;
	$limit_from=0;
	if (!$_REQUEST['cols_in_page'] || $_REQUEST['cols_in_page'] == 'frist' || $_REQUEST['cols_in_page'] < $cols_in_page) {
		$limit_from=0;
		$smarty->assign("pre_cols",0);
		$smarty->assign("next_cols",$cols_in_page);
	} else {
		$limit_from=$_REQUEST['cols_in_page'];
		$smarty->assign("pre_cols",$_REQUEST['cols_in_page']-$cols_in_page);
		$smarty->assign("next_cols",($_REQUEST['cols_in_page']+$cols_in_page));
	}
	$smarty->assign("from_cols",($limit_from+1));
	$smarty->assign("to_cols",($limit_from+$cols_in_page));

	//格式化条件
	$sqlwhere = '';

	//取出当前要显示的类型
	if (trim($_REQUEST['routerline']) != "") {
		$sqlwhere .= " and routerline = '".$_REQUEST['routerline']."'";
	}
	$smarty->assign("routerline",$_REQUEST['routerline']);

	//日期时间范围
	if (trim($_REQUEST['start_date']) != '' && trim($_REQUEST['end_date']) != '') {
		$sqlwhere.=" and cretime >= '".$_REQUEST['start_date']." 00:00:00' and cretime <= '".$_REQUEST['end_date']." 23:59:59'";
		$smarty->assign("start_date",$_REQUEST['start_date']);
		$smarty->assign("end_date",$_REQUEST['end_date']);
	}
	//号码统配
	if (trim($_REQUEST['src']) != '' && preg_match("/\*/",$_REQUEST['src'])) {
		$sqlwhere.=" and callernumber like '".preg_replace("/\*/","%",$_REQUEST['src'])."'";
		$smarty->assign("src",$_REQUEST['src']);
	} elseif (trim($_REQUEST['src']) != '') {
		$sqlwhere.=" and callernumber = '".$_REQUEST['src']."'";
		$smarty->assign("src",$_REQUEST['src']);
	}
	if (trim($_REQUEST['dst']) != '' && preg_match("/\*/",$_REQUEST['dst'])) {
		$sqlwhere.=" and extension like '".preg_replace("/\*/","%",$_REQUEST['dst'])."'";
		$smarty->assign("dst",$_REQUEST['dst']);
	} elseif (trim($_REQUEST['dst']) != '') {
		$sqlwhere.=" and extension = '".$_REQUEST['dst']."'";
		$smarty->assign("dst",$_REQUEST['dst']);
	}

	//最后生成
	$sqlwhere = preg_replace("/^ and/","",$sqlwhere);
	if (trim($sqlwhere) != '')
		$sqlwhere = 'where '.$sqlwhere;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from callsession ".$sqlwhere." order by cretime desc limit ".$limit_from.','.$cols_in_page),0);
	//处理详细,由于速度太慢,所以只显示5条
	$i=0;
	foreach ($rpcres['result_array'] as $key => $value) {
		if ($i==5)
			break;
		$subrpcres = sendrequest($rpcpbx->base_dbquery("select disposition,duration,billsec from cdr where userfield = '".$value['id'].",".$value['frist_cdruniqueid']."' LIMIT 1"),0);
		$rpcres['result_array'][$key]['cdr']=$subrpcres['result_array']['0'];
		$i++;
	}
	$smarty->assign("recordlist",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_callsession_main.tpl');
	exit;
}

function func_sessionflow_list() {
	global $smarty;
	global $rpcpbx;

	$smarty->assign("cdruniqueid",$_REQUEST['cdruniqueid']);

	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from callsession where id = '".$_REQUEST['callsessionid']."' LIMIT 1"),0);
	$smarty->assign("callsession",$rpcres['result_array'][0]);

	$rpcres = sendrequest($rpcpbx->base_dbquery("select disposition,duration,billsec from cdr where userfield = '".$rpcres['result_array'][0]['id'].",".$rpcres['result_array'][0]['frist_cdruniqueid']."' LIMIT 1"),0);
	$smarty->assign("cdr",$rpcres['result_array'][0]);

	//取出
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from callsession_acts where callsessionid = '".$_REQUEST['callsessionid']."' order by actid asc"),0);
	$smarty->assign("sessionflow",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_sessionflow_list.tpl');
exit;
}

?>