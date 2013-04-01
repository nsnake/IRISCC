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
	this file : fax system

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
if (!isset($_REQUEST['action'])) page_fax_list();

switch($_REQUEST['action']) {
	case "func_faxstats_list":
		func_faxstats_list();
		break;
	case "do_faxfile_download":
		do_faxfile_download();
		break;
	case "do_fax_send":
		do_fax_send();
		break;
	case "do_bklicense_download":
		do_bklicense_download();
		break;
	case "do_faxoption_set":
		do_faxoption_set();
		break;
	default:
		page_fax_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_fax_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->fax_option_get(),0);
	$smarty->assign("option",$rpcres['resdata']);

	$modem=array();
	foreach (preg_split("/\|/",$rpcres['resdata']['modem']) as $value) {
		$modem[$value]=true;
	}
	$smarty->assign("modem",$modem);

	$release = $rpcpbx->base_release();
	$smarty->assign("release",$release['release']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_fax_list.tpl');
	exit;
}

function do_faxoption_set() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$modem=null;
	foreach ($_REQUEST['modem'] as $value) {
		$modem .= $value.'|';
	}
	if (trim($modem) == "") {
		$modem = 'v27';
	}
	$modem = rtrim($modem,'|');

	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','maxrate',$_REQUEST['maxrate']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','minrate',$_REQUEST['minrate']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','modem',$modem),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','ecm',$_REQUEST['ecm']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','faxtitle',$_REQUEST['faxtitle']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','localstationid',$_REQUEST['localstationid']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','enablefaxivr',$_REQUEST['enablefaxivr']),1);
	sendrequest($rpcpbx->option_confkey_edit('freeiris','freeiris.conf','fax','deliver',$_REQUEST['deliver']),1);

	error_popbox(303,null,null,null,'pbx_fax.php','submit_confirm');
	exit;
}

function do_bklicense_download() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres=sendrequest($rpcpbx->license_download(),1);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	Header("Content-Type: application/octet-stream");
	Header("Content-Disposition: attachment; filename=backup_license_".Date("Y-m-d").".".$rpcres['filetype']);
	header("Content-Transfer-Encoding: binary");
	echo $rpcres['filebinary'];

	exit;
}

function do_fax_send() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['number']) == "")
		error_popbox(300,null,null,null,null,'submit_failed');
	if (!$_FILES['faxfile'])	//检测是否上载文件了
		error_popbox(301,null,null,null,null,'submit_failed');

	$fileinfo = pathinfo($_FILES['faxfile']['name']);
	if (strtolower($fileinfo['extension']) != "tif" && strtolower($fileinfo['extension']) != "tiff")	//检测是否上载文件了
		error_popbox(301,null,null,null,null,'submit_failed');

	$filestream = file_get_contents($_FILES['faxfile']['tmp_name']);

	$rpcres = sendrequest($rpcpbx->fax_faxfile_send(null,$_REQUEST['number'],'tiff',$filestream,null),1);

	//完成
	error_popbox(302,null,null,null,'pbx_fax.php','submit_successfuly');
	exit;
}

function func_faxstats_list() {
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

	//筛选
	$filter = null;
	if (trim($_REQUEST['accountcode']) != "") {
		$filter = " where accountcode = '".$_REQUEST['accountcode']."'";
		$smarty->assign("accountcode",$_REQUEST['accountcode']);
	}

	//取出总量
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from faxqueue ".$filter),1);
	$smarty->assign("maxcount",$rpcres['result_array'][0][0]);

	//列表
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from faxqueue ".$filter." ORDER BY id desc LIMIT ".$limit_from.",".$friconf['cols_in_page']),1);
	$smarty->assign("table_array",$rpcres['result_array']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_faxstats_list.tpl');
	exit;
}

function do_faxfile_download() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	if ($_REQUEST['faxid'] == "")
		exit;
	$rpcres=sendrequest($rpcpbx->fax_faxfile_download($_REQUEST['faxid']),1);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	Header("Content-Type: application/octet-stream");
	Header("Content-Disposition: attachment; filename=".$rpcres['filename']);
	header("Content-Transfer-Encoding: binary");
	echo $rpcres['filestream'];

	exit;
}

?>