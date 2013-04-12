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
	this file : exten_manage

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
if (!isset($_REQUEST['action'])) page_exten_list();

switch($_REQUEST['action']) {
	// edit exten function
	case "func_exten_edit_sip":
		func_exten_edit_sip();
		break;
	case "do_exten_edit_sip";
		do_exten_edit_sip();
		break;
	case "func_exten_edit_iax2":
		func_exten_edit_iax2();
		break;
	case "do_exten_edit_iax2";
		do_exten_edit_iax2();
		break;
	case "func_exten_edit_virtual":
		func_exten_edit_virtual();
		break;
	case "do_exten_edit_virtual";
		do_exten_edit_virtual();
		break;
	case "func_exten_edit_custom":
		func_exten_edit_custom();
		break;
	case "do_exten_edit_custom";
		do_exten_edit_custom();
		break;
	case "func_exten_edit_fxs":
		func_exten_edit_fxs();
		break;
	case "do_exten_edit_fxs";
		do_exten_edit_fxs();
		break;
	// add exten function
	case "func_exten_add":
		func_exten_add();
		break;
	case "do_exten_add_sip";
		do_exten_add_sip();
		break;
	case "do_exten_add_iax2";
		do_exten_add_iax2();
		break;
	case "do_exten_add_virtual";
		do_exten_add_virtual();
		break;
	case "do_exten_add_custom";
		do_exten_add_custom();
		break;
	case "do_exten_add_fxs";
		do_exten_add_fxs();
		break;
	// delete exten funcion
	case "do_exten_delete_sip";
		do_exten_delete_sip();
		break;
	case "do_exten_delete_iax2";
		do_exten_delete_iax2();
		break;
	case "do_exten_delete_virtual";
		do_exten_delete_virtual();
		break;
	case "do_exten_delete_custom";
		do_exten_delete_custom();
		break;
	case "do_exten_delete_fxs";
		do_exten_delete_fxs();
		break;
	// other function
	case "do_exten_diagnosis":
		do_exten_diagnosis();
		break;
	default:
		page_exten_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_exten_list() {
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
	if ($_REQUEST['order'] == 'accountcode') {
		$order='order by accountcode desc';
		$smarty->assign("order",$_REQUEST['order']);
	} elseif ($_REQUEST['order'] == 'deviceproto') {
		$order='order by deviceproto desc';
		$smarty->assign("order",$_REQUEST['order']);
	} elseif ($_REQUEST['order'] == 'cretime') {
		$order='order by cretime desc';
		$smarty->assign("order",$_REQUEST['order']);
	} else {
		$order='order by cretime desc';
		$smarty->assign("order",'cretime');
	}

	//取出所有的帐户
	$rpcres = sendrequest($rpcpbx->extension_list($order,$limit_from,$friconf['cols_in_page']),0);

	//总量
	$smarty->assign("maxcount",count($rpcres['extensions']));
	//列表
	$smarty->assign("table_array",$rpcres['extensions']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_exten_list.tpl');
	exit;
}

function func_exten_add() {
	global $smarty;
	global $rpcpbx;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);

	// SIP TEMPlATE
	switch($_REQUEST['deviceproto']) {
		case "sip";
			smarty_output('cpanel/func_exten_add_sip.tpl');
			break;
		case "iax2":
			smarty_output('cpanel/func_exten_add_iax2.tpl');
			break;
		case "fxs":
			$rpcres=sendrequest($rpcpbx->extension_freechan_fxs(),0);
			$smarty->assign("freechan",$rpcres['freechan']);
			smarty_output('cpanel/func_exten_add_fxs.tpl');
			break;
		case "virtual":
			smarty_output('cpanel/func_exten_add_virtual.tpl');
			break;
		case "custom":
			smarty_output('cpanel/func_exten_add_custom.tpl');
			break;
		default:
			smarty_output('cpanel/func_exten_add.tpl');
			break;
	}
exit;
}

function do_exten_add_sip() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['accountcode']=$_REQUEST['accountcode'];
	$exten['devicenumber']=$_REQUEST['accountcode'];
	$exten['username']=$_REQUEST['accountcode'];

	$exten['password']=$_REQUEST['password'];
	$exten['secret']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];
	$exten['host']=$_REQUEST['host'];
	$exten['nat']=$_REQUEST['nat'];
	$exten['qualify']=$_REQUEST['qualify'];
	$exten['canreinvite']=$_REQUEST['canreinvite'];
	$exten['setvar']=$_REQUEST['setvar'];
	$exten['call-limit']=$_REQUEST['call-limit'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension where accountcode = '".$_REQUEST['accountcode']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(123,null,null,null,null,'submit_failed');

	//创建分机
	$rpcres = sendrequest($rpcpbx->extension_add_sip($exten),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(124,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}

function func_exten_edit_sip() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extension_get_sip($_REQUEST['accountcode']),0);

	//指定分机不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	//由于smarty不支持key名字中包含-
	$rpcres['resdata']['calllimit'] = $rpcres['resdata']['call-limit'];
	$smarty->assign("extension",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_edit_sip.tpl');
exit;
}

function do_exten_edit_sip() {
	global $rpcpbx;
	global $smarty;
	global $friconf;
	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['password']=$_REQUEST['password'];
	$exten['secret']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];
	$exten['host']=$_REQUEST['host'];
	$exten['nat']=$_REQUEST['nat'];
	$exten['qualify']=$_REQUEST['qualify'];
	$exten['canreinvite']=$_REQUEST['canreinvite'];
	$exten['setvar']=$_REQUEST['setvar'];
	$exten['call-limit']=$_REQUEST['call-limit'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_sip($_REQUEST['accountcode']),1);

	//编辑分机
	$rpcres = sendrequest($rpcpbx->extension_edit_sip($_REQUEST['accountcode'],$exten),1);

	if ($rpcres['response']['reload'] == true) {
		error_popbox(127,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}

function do_exten_delete_sip() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_sip($_REQUEST['accountcode']),1);

	$rpcres = sendrequest($rpcpbx->extension_delete_sip($_REQUEST['accountcode']),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(128,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}


function do_exten_add_iax2() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['accountcode']=$_REQUEST['accountcode'];
	$exten['devicenumber']=$_REQUEST['accountcode'];

	$exten['password']=$_REQUEST['password'];
	$exten['secret']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];
	$exten['host']=$_REQUEST['host'];
	$exten['port']=$_REQUEST['port'];
	$exten['transfer']=$_REQUEST['transfer'];
	$exten['setvar']=$_REQUEST['setvar'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension where accountcode = '".$_REQUEST['accountcode']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(123,null,null,null,null,'submit_failed');

	//创建分机
	$rpcres = sendrequest($rpcpbx->extension_add_iax2($exten),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(124,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}

function func_exten_edit_iax2() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extension_get_iax2($_REQUEST['accountcode']),0);

	//指定分机不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	$smarty->assign("extension",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_edit_iax2.tpl');
exit;
}

function do_exten_edit_iax2() {
	global $rpcpbx;
	global $smarty;
	global $friconf;
	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['password']=$_REQUEST['password'];
	$exten['secret']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];
	$exten['host']=$_REQUEST['host'];
	$exten['port']=$_REQUEST['port'];
	$exten['transfer']=$_REQUEST['transfer'];
	$exten['setvar']=$_REQUEST['setvar'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_iax2($_REQUEST['accountcode']),1);

	//编辑分机
	$rpcres = sendrequest($rpcpbx->extension_edit_iax2($_REQUEST['accountcode'],$exten),1);

	if ($rpcres['response']['reload'] == true) {
		error_popbox(127,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}

function do_exten_delete_iax2() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_iax2($_REQUEST['accountcode']),1);

	$rpcres = sendrequest($rpcpbx->extension_delete_iax2($_REQUEST['accountcode']),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(128,null,null,null,'pbx_reload.php?action=reload&area=all&return='.urlencode('exten_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
	}

exit;
}


function do_exten_add_virtual() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['accountcode']=$_REQUEST['accountcode'];
	$exten['devicenumber']=$_REQUEST['accountcode'];

	$exten['password']=$_REQUEST['password'];


	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension where accountcode = '".$_REQUEST['accountcode']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(123,null,null,null,null,'submit_failed');

	//创建分机
	$rpcres = sendrequest($rpcpbx->extension_add_virtual($exten),1);

	//完成
	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');

exit;
}

function func_exten_edit_virtual() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extension_get_virtual($_REQUEST['accountcode']),0);

	//指定分机不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	$smarty->assign("extension",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_edit_virtual.tpl');
exit;
}

function do_exten_edit_virtual() {
	global $rpcpbx;
	global $smarty;
	global $friconf;
	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

	$exten['password']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_virtual($_REQUEST['accountcode']),1);

	//编辑分机
	$rpcres = sendrequest($rpcpbx->extension_edit_virtual($_REQUEST['accountcode'],$exten),1);

	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
exit;
}

function do_exten_delete_virtual() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_virtual($_REQUEST['accountcode']),1);

	$rpcres = sendrequest($rpcpbx->extension_delete_virtual($_REQUEST['accountcode']),1);

	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
exit;
}


function do_exten_add_custom() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['devicestring']) == "")
		error_popbox(126,null,null,null,null,'submit_failed');

	$exten['accountcode']=$_REQUEST['accountcode'];
	$exten['devicenumber']=$_REQUEST['accountcode'];
	$exten['devicestring']=$_REQUEST['devicestring'];

	$exten['password']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension where accountcode = '".$_REQUEST['accountcode']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(123,null,null,null,null,'submit_failed');

	//创建分机
	$rpcres = sendrequest($rpcpbx->extension_add_custom($exten),1);

	//完成
	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
exit;
}

function func_exten_edit_custom() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extension_get_custom($_REQUEST['accountcode']),0);

	//指定分机不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	$smarty->assign("extension",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_edit_custom.tpl');
exit;
}

function do_exten_edit_custom() {
	global $rpcpbx;
	global $smarty;
	global $friconf;
	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['devicestring']) == "")
		error_popbox(126,null,null,null,null,'submit_failed');

	$exten['devicestring']=$_REQUEST['devicestring'];
	$exten['password']=$_REQUEST['password'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_custom($_REQUEST['accountcode']),1);

	//编辑分机
	$rpcres = sendrequest($rpcpbx->extension_edit_custom($_REQUEST['accountcode'],$exten),1);

	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');
exit;
}

function do_exten_delete_custom() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_custom($_REQUEST['accountcode']),1);

	$rpcres = sendrequest($rpcpbx->extension_delete_custom($_REQUEST['accountcode']),1);

	//完成
	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');

exit;
}


function do_exten_add_fxs()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['devicestring']) == "" || preg_match('/[^0-9]/',$_REQUEST['devicestring']))
		error_popbox(136,null,null,null,null,'submit_failed');

	$exten['accountcode']=$_REQUEST['accountcode'];
	$exten['devicenumber']=$_REQUEST['accountcode'];
	$exten['callerid']=$_REQUEST['accountcode'];

	$exten['password']=$_REQUEST['password'];

	$exten['devicestring']=$_REQUEST['devicestring'];
	$exten['channel']=$_REQUEST['devicestring'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from extension where accountcode = '".$_REQUEST['accountcode']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(123,null,null,null,null,'submit_failed');


	//创建分机
	sendrequest($rpcpbx->extension_add_fxs($exten),1);

	//完成
	error_popbox(135,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('exten_manage.php'),'submit_confirm');

exit;
}


function func_exten_edit_fxs() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->extension_get_sip($_REQUEST['accountcode']),0);

	//指定分机不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);
	$smarty->assign("extension",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_exten_edit_fxs.tpl');

exit;
}

function do_exten_edit_fxs() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$exten = array();

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['password']) == "" || preg_match('/[^0-9]/',$_REQUEST['password']))
		error_popbox(121,null,null,null,null,'submit_failed');
	if ($_REQUEST['repassword'] != $_REQUEST['password'])
		error_popbox(122,null,null,null,null,'submit_failed');

    //满足新增所需要的数据
    $exten['accountcode'] = $_REQUEST['accountcode'];
    $exten['devicenumber'] = $_REQUEST['accountcode'];
    $exten['callerid'] = $_REQUEST['accountcode'];

	$exten['password']=$_REQUEST['password'];

	$exten['devicestring']=$_REQUEST['devicestring'];
    $exten['channel'] = $exten['devicestring'];

	//不管写没写都默认处理的
	$exten['transfernumber']=$_REQUEST['transfernumber'];
	$exten['diallocal_failed']=$_REQUEST['diallocal_failed'];
	$exten['info_name']=$_REQUEST['info_name'];
	$exten['info_email']=$_REQUEST['info_email'];
	$exten['info_detail']=$_REQUEST['info_detail'];
	$exten['extengroup']=$_REQUEST['extengroup'];


	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_get_fxs($_REQUEST['accountcode']),1);

	//编辑分机
	$rpcres = sendrequest($rpcpbx->extension_edit_fxs($_REQUEST['accountcode'],$exten),1);

	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');

exit;
}

function do_exten_delete_fxs() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['accountcode']) == "" || strlen(trim($_REQUEST['accountcode'])) < 3 || preg_match('/[^0-9]/',$_REQUEST['accountcode']) || substr($_REQUEST['accountcode'],0,1) == '0')
		error_popbox(120,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->extension_delete_fxs($_REQUEST['accountcode']),1);

	//完成
	error_popbox(137,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('exten_manage.php'),'submit_confirm');

exit;
}


// diagnosis 分析诊断
function do_exten_diagnosis() {
	global $smarty;
	global $rpcpbx;

	//取出
	$rpcres = sendrequest($rpcpbx->extension_get($_REQUEST['accountcode']),1);
	if (!$rpcres['extension'])
		error_popbox(125,null,null,null,null,'submit_failed');

	//设置目前为 ! 状态
	sendrequest($rpcpbx->base_dbquery("update extension set fristchecked=1 where accountcode = '".$_REQUEST['accountcode']."'"),1);

	//设置好分机
	if ($rpcres['extension']['deviceproto'] == 'custom') {
		$channel=$rpcres['extension']['devicestring'];
	} elseif ($rpcres['extension']['deviceproto'] == 'fxs') {
		$channel='DAHDI/'.$rpcres['extension']['devicestring'];
	} else {
		$channel=$rpcres['extension']['deviceproto'].'/'.$rpcres['extension']['devicestring'];
	}

	//发起呼叫
	$actionid = uniqid();
	$rpcres = $rpcpbx->ami_originate($actionid,array(
		'Channel'=>$channel,
		'CallerID'=>'Diagnosis Phone',
		'MaxRetries'=>0,
		'WaitTime'=>30,
		'RetryTime'=>15,
		'Application'=>'AGI',
		'Data'=>'agi://127.0.0.1/originate_diagnosis?accountcode='.$_REQUEST['accountcode']));

	error_popbox(null,null,null,null,'exten_manage.php','submit_successfuly');

exit;
}
?>
