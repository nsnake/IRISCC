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
	this file : voicemail system

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
if (!isset($_REQUEST['action'])) page_uservoice_list();

switch($_REQUEST['action']) {
	case "func_voicemail_list":
		func_voicemail_list();
		break;
	case "func_onetouch_list":
		func_onetouch_list();
		break;
	case "do_uservoice_download":
		do_uservoice_download();
		break;
	case "do_uservoice_delete":
		do_uservoice_delete();
		break;
	default:
		page_voicemail_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_uservoice_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//磁盘使用
	$rpcres = sendrequest($rpcpbx->voicefiles_diskfree('voicemail'),0);
	$smarty->assign("diskfree_gigabyte",round($rpcres['diskfree']/1024/1024/1024,4));
	$diskfree_percent = round(($rpcres['diskfree']/$rpcres['disktotal'])*100);
	$smarty->assign("diskfree_percent",$diskfree_percent);
	$smarty->assign("diskused_percent",(100-$diskfree_percent));

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
	$rpcres = sendrequest($rpcpbx->extension_list('order by accountcode asc',$limit_from,$friconf['cols_in_page']),0);
	foreach ($rpcres['extensions'] as $key=>$value) {

		$vmres = sendrequest($rpcpbx->base_dbquery("select count(*) from voicefiles where folder = '".$value['accountcode']."' and label = 'voicemail'"),0);
		$otres = sendrequest($rpcpbx->base_dbquery("select count(*) from voicefiles where folder = '".$value['accountcode']."' and label = 'onetouch'"),0);

		$rpcres['extensions'][$key]['voicemail_count']=$vmres['result_array'][0]['count(*)'];
		$rpcres['extensions'][$key]['onetouch_count']=$otres['result_array'][0]['count(*)'];
	}
	$smarty->assign("result_array",$rpcres['extensions']);
	$smarty->assign("maxcount",count($rpcres['extensions']));

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_uservoice_list.tpl');
	exit;
}


function func_voicemail_list() {
	global $smarty;
	global $rpcpbx;

	//取出
	$rpcres = sendrequest($rpcpbx->voicefiles_list('voicemail',null,null,$_REQUEST['accountcode']),0);
	$smarty->assign("voicemail",$rpcres['resdata']);

	$smarty->assign("accountcode",$_REQUEST['accountcode']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_voicemail_list.tpl');
exit;
}

function func_onetouch_list() {
	global $smarty;
	global $rpcpbx;

	//取出
	$rpcres = sendrequest($rpcpbx->voicefiles_list('onetouch',null,null,$_REQUEST['accountcode']),0);
	$smarty->assign("onetouch",$rpcres['resdata']);

	$smarty->assign("accountcode",$_REQUEST['accountcode']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_onetouch_list.tpl');
exit;
}


function do_uservoice_download()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->voicefiles_getstream($_REQUEST['id']),1);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	Header("Content-Type: application/octet-stream");
	Header("Content-Disposition: attachment; filename=".$rpcres['filename'].'.'.$rpcres['extname']);
	header("Content-Transfer-Encoding: binary ");

	echo $rpcres['filestream'];

exit;
}

function do_uservoice_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->voicefiles_delete($_REQUEST['id']),1);

	//完成
	if ($_REQUEST['type'] == 'vm') {
		header('Location: pbx_uservoice.php?action=func_voicemail_list&accountcode='.$_REQUEST['accountcode']."\n\n");
	} elseif ($_REQUEST['type'] == 'ot') {
		header('Location: pbx_uservoice.php?action=func_onetouch_list&accountcode='.$_REQUEST['accountcode']."\n\n");
	}
exit;
}
?>