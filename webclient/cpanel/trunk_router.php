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
	this file : trunk router settings

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
if (!isset($_REQUEST['action'])) page_trunkrouter_list();

switch($_REQUEST['action']) {
	//router recall
	case "do_router_recall":
		do_router_recall();
		break;
	case "do_router_delete":
		do_router_delete();
		break;
	// add exten function
	case "func_router_add":
		func_router_add();
		break;
	case "do_router_add":
		do_router_add();
		break;
	// edit exten function
	case "func_router_edit":
		func_router_edit();
		break;
	case "do_router_edit":
		do_router_edit();
		break;
	case "do_change_extenvar";
		do_change_extenvar();
		break;
	default:
		page_trunkrouter_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_trunkrouter_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//设置default
	$rpcres = sendrequest($rpcpbx->dialplan_globalvar_get('FRI2_TRUNK_DEFAULT_SREPLACE'),0);
	$smarty->assign("FRI2_TRUNK_DEFAULT_SREPLACE",$rpcres['FRI2_TRUNK_DEFAULT_SREPLACE']);


	//取出所有的router
	$rpcres = sendrequest($rpcpbx->router_list(2),0);

	//重新排列
	$allrule = array();
	$nodelrule = array();
	$proirety_aslevel = 1;
	foreach ($rpcres['rules'] as $each) {
		if ($each['createmode'] == '2') {
			array_push($nodelrule,$each);
		} else {
			$each['proirety_aslevel']=$proirety_aslevel;
			array_push($allrule,$each);
			$proirety_aslevel++;
		}
	}

	//总量
	$smarty->assign("maxcount",count($rpcres['rules']));
	//列表
	$smarty->assign("table_array",$allrule);
	$smarty->assign("nodelrule_array",$nodelrule);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_trunkrouter_list.tpl');
	exit;
}

function do_change_extenvar() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->dialplan_globalvar_set('FRI2_TRUNK_DEFAULT_SREPLACE',$_REQUEST['FRI2_TRUNK_DEFAULT_SREPLACE']),1);

	//完成
	header("Location: ".'pbx_reload.php?action=reload&area=dialplan&return='.urlencode('trunk_router.php')."\n\n");
exit;
}


function func_router_add() {
	global $smarty;
	global $rpcpbx;

	//取出所有的trunk
	$rpcres = sendrequest($rpcpbx->trunk_list('order by cretime desc',0,1000),0);

	//列表
	$smarty->assign("provider_array",$rpcres['trunks']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);

	// SIP TEMPlATE
	smarty_output('cpanel/func_trunkrouter_add.tpl');
exit;
}

function do_router_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$routerdata = array();

	//不填绝对不行的
	if (trim($_REQUEST['routername']) == "")
		error_popbox(150,null,null,null,null,'submit_failed');

	if (trim($_REQUEST['match_callergroup']) == "" && trim($_REQUEST['match_callerid']) == "" && trim($_REQUEST['match_callerlen']) == "" && trim($_REQUEST['match_callednum']) == "" && trim($_REQUEST['match_calledlen']) == "")
		error_popbox(151,null,null,null,null,'submit_failed');

	$routerdata['routername'] = $_REQUEST['routername'];
	$routerdata['lastwhendone'] = $_REQUEST['lastwhendone'];
//	$routerdata['match_callergroup_trunkname'] = $_REQUEST['match_callergroup_trunkname'];  选择菜单不需要替换
	$routerdata['match_callergroup'] = $_REQUEST['match_callergroup'];
	$routerdata['match_callerid'] = $_REQUEST['match_callerid'];
	$routerdata['match_callerlen'] = $_REQUEST['match_callerlen'];
	$routerdata['match_callednum'] = $_REQUEST['match_callednum'];
	$routerdata['match_calledlen'] = $_REQUEST['match_calledlen'];
	$routerdata['replace_callerid'] = $_REQUEST['replace_callerid'];
	$routerdata['replace_calledtrim'] = $_REQUEST['replace_calledtrim'];
	$routerdata['replace_calledappend'] = $_REQUEST['replace_calledappend'];
	$routerdata['process_mode'] = $_REQUEST['process_mode'];

	if ($_REQUEST['process_mode'] == 1)  {
		$routerdata['process_defined'] = $_REQUEST['process_defined_localnumber'];
	} elseif ($_REQUEST['process_mode'] == 2)  {
		$routerdata['process_defined'] = $_REQUEST['process_defined_trunk'];
	}

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from router where routername = '".$_REQUEST['routername']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(152,null,null,null,null,'submit_failed');

	//创建router
	$rpcres = sendrequest($rpcpbx->router_add(mktime(),1,2,$routerdata),1);

	//完成
	error_popbox(null,null,null,null,'trunk_router.php','submit_successfuly');

exit;
}


function do_router_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->router_delete($_REQUEST['id']),1);

	//完成
	error_popbox(null,null,null,null,'trunk_router.php','submit_successfuly');

exit;
}

function func_router_edit() {
	global $smarty;
	global $rpcpbx;

	//取得这个router
	$rpcres = sendrequest($rpcpbx->router_get($_REQUEST['id']),0);
	$smarty->assign("rule",$rpcres['resdata']);

	//取出所有的trunk
	$rpcres = sendrequest($rpcpbx->trunk_list('order by cretime desc',0,1000),0);

	//列表
	$smarty->assign("provider_array",$rpcres['trunks']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);

	// SIP TEMPlATE
	smarty_output('cpanel/func_trunkrouter_edit.tpl');

exit;
}

function do_router_edit() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$routerdata['routername'] = $_REQUEST['routername'];
	$routerdata['lastwhendone'] = $_REQUEST['lastwhendone'];
//	$routerdata['match_callergroup_trunkname'] = $_REQUEST['match_callergroup_trunkname'];  选择菜单不需要替换
	$routerdata['match_callergroup'] = $_REQUEST['match_callergroup'];
	$routerdata['match_callerid'] = $_REQUEST['match_callerid'];
	$routerdata['match_callerlen'] = $_REQUEST['match_callerlen'];
	$routerdata['match_callednum'] = $_REQUEST['match_callednum'];
	$routerdata['match_calledlen'] = $_REQUEST['match_calledlen'];
	$routerdata['replace_callerid'] = $_REQUEST['replace_callerid'];
	$routerdata['replace_calledtrim'] = $_REQUEST['replace_calledtrim'];
	$routerdata['replace_calledappend'] = $_REQUEST['replace_calledappend'];
	$routerdata['process_mode'] = $_REQUEST['process_mode'];

	if ($_REQUEST['process_mode'] == 1)  {
		$routerdata['process_defined'] = $_REQUEST['process_defined_localnumber'];
	} elseif ($_REQUEST['process_mode'] == 2)  {
		$routerdata['process_defined'] = $_REQUEST['process_defined_trunk'];
	}

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->router_get($_REQUEST['id']),1);

	//编辑
	$rpcres = sendrequest($rpcpbx->router_edit($_REQUEST['id'],$routerdata,2),1);

	error_popbox(null,null,null,null,'trunk_router.php','submit_successfuly');

exit;
}

function do_router_recall()
{
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//剔除调整顺序
	$source_all=array();
	foreach ($_REQUEST['router'] as $value) {
		if (trim($value) == "")
			continue;
		array_push($source_all,$value);
	}
	$routerproi=array();
	$maxdigit = count($source_all);
	foreach ($source_all as $value) {
		$routerproi[$maxdigit]=$value;
		$maxdigit--;
	}
	//根据新顺序调整优先关系
	foreach ($routerproi as $proi => $routerid) {
		$rpcres = sendrequest($rpcpbx->router_recall($routerid,$proi),1);
	}

	//完成
	error_popbox(null,null,null,null,'trunk_router.php','submit_successfuly');

exit;
}
?>