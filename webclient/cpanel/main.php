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
if (!isset($_REQUEST['action'])) page_main();

switch($_REQUEST['action']) {
	case "page_main_alert";
		page_main_alert();
		break;
//	case "func_registration";
//		func_registration();
//		break;
//	case "func_registration2"; //LICENSE
//		func_registration2();
//		break;
//	case "func_registration3"; //PROTECT
//		func_registration3();
//		break;
//	case "do_registration";
//		do_registration();
//		break;
	default:
		page_main();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_main() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//version
	$rpcres=sendrequest($rpcpbx->base_version(),0);
	$smarty->assign("rpcpbx_version",$rpcres['rpcpbx']);
	$smarty->assign("freeiris2_version",$rpcres['freeiris2']);
	$smarty->assign("buildver_version",$rpcres['buildver']);

	//cpu balance
	$rpcres=sendrequest($rpcpbx->stat_cpu_usage(),0);
	$smarty->assign("cpu_usage",$rpcres['usage']);
	$smarty->assign("cpu_loadavg",$rpcres['loadavg']);

	//uptime
	$rpcres=sendrequest($rpcpbx->stat_system_uptime(),0);
	$uptime=preg_split("/\./",round(($rpcres['uptime']/60/60),1));
	$smarty->assign("uptime_hour",$uptime[0]);
	$smarty->assign("uptime_min",round($uptime[1]*6));

	//memory
	$rpcres=sendrequest($rpcpbx->stat_memory_usage(),0);
	$smarty->assign("memory_usage",$rpcres['percent']);	

	// all extensions count
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension "),1);
	$smarty->assign("extensions_count",$rpcres['result_array'][0]['count(*)']);

	// all trunks count
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk "),1);
	$smarty->assign("trunks_count",$rpcres['result_array'][0]['count(*)']);

	// call count
	$timestamp = time();
	$timestamp -= 86400;
	$datetime = date('Y-m-d H:i:s',$timestamp);
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from cdr where calldate >= '".$datetime."'"),1);
	$smarty->assign("calls_in24",$rpcres['result_array'][0]['count(*)']);
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from cdr"),1);
	$smarty->assign("calls_all",$rpcres['result_array'][0]['count(*)']);

	$release = $rpcpbx->base_release();
	$smarty->assign("release",$release['release']);

	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_main.tpl');

	exit;
}

function page_main_alert() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//getall alert message
	//$rpcremote = new HproseHttpClient('http://www.fonoirs.com/usercenter/minable.php');
	//$rpcremote->setTimeout(10);
	//$alertres = $rpcremote->get_mainpageinfo();
	//if (!is_a($alertres, "PHPRPC_Error")) {
	//	$smarty->assign("alertres",$alertres);
	//}
	//smarty_output('cpanel/page_main_alert.tpl');

    $handle = @fopen("http://www.freeiris.org/cn/usercenter/minable.php?action=freeiris", "r");
    if (!$handle) {
        exit;
    }
    stream_set_timeout($handle, 3);
    $content = stream_get_contents($handle);
    fclose($handle);

    $smarty->assign("minable",$content);
    smarty_output('cpanel/page_main_alert.tpl');

	exit;
}

//function func_registration() {
//	global $smarty;
//	global $rpcpbx;
//	$smarty->assign("res_admin",$_SESSION['res_admin']);
//	smarty_output('cpanel/func_registration.tpl');
//	exit;
//}
//
//function func_registration2() {
//	global $smarty;
//	global $rpcpbx;
//
//	$rpcres = sendrequest($rpcpbx->base_license_get(),1);
//
//	$smarty->assign("license",$rpcres['license']);
//	
//	$smarty->assign("res_admin",$_SESSION['res_admin']);
//	smarty_output('cpanel/func_registration2.tpl');
//	exit;
//}
//
//function func_registration3() {
//	global $smarty;
//	global $rpcpbx;
//	$smarty->assign("res_admin",$_SESSION['res_admin']);
//	smarty_output('cpanel/func_registration3.tpl');
//	exit;
//}
//
//function do_registration() {
//	global $smarty;
//	global $rpcpbx;
//	global $friconf;
//
//	$data=array();
//
//	//不填绝对不行的
//	if (trim($_REQUEST['username']) == "")
//		error_popbox(103,null,null,null,null,'submit_failed');
//	if (trim($_REQUEST['password']) == "")
//		error_popbox(103,null,null,null,null,'submit_failed');
//
//	$data['frist_time']=$_REQUEST['frist_time'];
//	$data['where_download']=$_REQUEST['where_download'];
//	$data['request']=$_REQUEST['request'];
//	$data['usedfor']=$_REQUEST['usedfor'];
//	$data['company']=$_REQUEST['company'];
//	$data['company_size']=$_REQUEST['company_size'];
//	$data['company_contact']=$_REQUEST['company_contact'];
//
//	$rpcremote = new HproseHttpClient('http://cn.freeiris.org/usercenter/minable.php');
//	$rpcres = $rpcremote->add_machine($_REQUEST['username'],$_REQUEST['password'],$data);
//	if (!$rpcres)
//		error_popbox(901,null,null,null,null,'submit_failed');
//
//	//存储到本地数据结构RPC中
//	sendrequest($rpcpbx->base_registration_set($rpcres['systemid'],$_REQUEST['username']),1);
//
//	error_popbox(null,null,null,null,'index.php?action=do_logout','submit_successfuly');
//}

?>