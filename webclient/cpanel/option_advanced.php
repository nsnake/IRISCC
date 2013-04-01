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
	this file : option settings

    $Id: option_voip.php 186 2009-05-26 12:42:09Z hoowa $
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
if (!isset($_REQUEST['action'])) page_option_advanced();

switch($_REQUEST['action']) {
	case "do_confile_delete";
		do_confile_delete();
		break;
	case "func_confile_edit":
		func_confile_edit();
		break;
	case "do_confile_edit":
		do_confile_edit();
		break;
	case "func_confile_list":
		func_confile_list();
		break;
	default:
		page_option_voip();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_option_advanced() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->option_confsection_get('freeiris','freeiris.conf','database'),0);
	$smarty->assign("database",$rpcres['database']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_option_advanced.tpl');
	exit;
}

function do_option_advanced_database_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if (trim($_REQUEST['dbhost']) == "")
		error_popbox(520,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['dbuser']) == "")
		error_popbox(521,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['dbname']) == "")
		error_popbox(522,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['dbport']) == "" || preg_match("/[^0-9]/",$_REQUEST['dbport']))
		error_popbox(523,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbhost',$_REQUEST['dbhost']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbuser',$_REQUEST['dbuser']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbpasswd',$_REQUEST['dbpasswd']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbname',$_REQUEST['dbname']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbport',$_REQUEST['dbport']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','database','dbsock',$_REQUEST['dbsock']),1);

	error_popbox(524,null,null,null,'./index.php?action=do_logout','submit_confirm');
	exit;
}

function func_confile_list()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$smarty->assign("folder",$_REQUEST['folder']);

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->option_confile_list($_REQUEST['folder']),1);
	$smarty->assign("filelist",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_confile_list.tpl');

exit;
}

function do_confile_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['folder']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['filename']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->option_confile_delete($_REQUEST['folder'],$_REQUEST['filename']),1);

	//完成
	header('Location: '.'option_advanced.php?action=func_confile_list&folder='.$_REQUEST['folder']."\n\n");
exit;
}

function func_confile_edit()
{
	global $smarty;
	global $rpcpbx;

	//取得内容
	$rpcres = sendrequest($rpcpbx->option_confile_stream($_REQUEST['folder'],$_REQUEST['filename']),0);
	$smarty->assign("filestream",$rpcres['resdata']);


	$smarty->assign("folder",$_REQUEST['folder']);
	$smarty->assign("filename",$_REQUEST['filename']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_confile_edit.tpl');
	exit;
}

function do_confile_edit()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['folder']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['filename']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	//增加这个费率
	sendrequest($rpcpbx->option_confile_puts($_REQUEST['folder'],$_REQUEST['filename'],$_REQUEST['filestream']),1);

	header('Location: '.'option_advanced.php?action=func_confile_list&folder='.$_REQUEST['folder']."\n\n");

exit;
}

?>