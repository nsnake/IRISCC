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
	this file : trunk manage

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
if (!isset($_REQUEST['action'])) page_trunk_list();

switch($_REQUEST['action']) {
	// edit trunk function
	case "func_trunk_edit_sip":
		func_trunk_edit_sip();
		break;
	case "do_trunk_edit_sip";
		do_trunk_edit_sip();
		break;
	case "func_trunk_edit_iax2":
		func_trunk_edit_iax2();
		break;
	case "do_trunk_edit_iax2";
		do_trunk_edit_iax2();
		break;
	case "func_trunk_edit_custom":
		func_trunk_edit_custom();
		break;
	case "do_trunk_edit_custom";
		do_trunk_edit_custom();
		break;
	// edit fxo or digital means dahdi
	case "func_trunk_edit_dahdi";
		func_trunk_edit_dahdi();
		break;
	case "do_trunk_edit_isdnpri";
		do_trunk_edit_isdnpri();
		break;
	case "do_trunk_edit_fxo";
		do_trunk_edit_fxo();
		break;
	// add trunk function
	case "func_trunk_add":
		func_trunk_add();
		break;
	case "do_trunk_add_sip";
		do_trunk_add_sip();
		break;
	case "do_trunk_add_iax2";
		do_trunk_add_iax2();
		break;
	case "do_trunk_add_custom";
		do_trunk_add_custom();
		break;
	case "do_trunk_add_isdnpri";
		do_trunk_add_isdnpri();
		break;
	case "do_trunk_add_fxo";
		do_trunk_add_fxo();
		break;
	// delete trunk funcion
	case "do_trunk_delete_sip";
		do_trunk_delete_sip();
		break;
	case "do_trunk_delete_iax2";
		do_trunk_delete_iax2();
		break;
	case "do_trunk_delete_custom";
		do_trunk_delete_custom();
		break;
	// delete fxo or delete digital means delete dahdi
	case "do_trunk_delete_dahdi";
		do_trunk_delete_dahdi();
		break;
	default:
		page_trunk_list();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_trunk_list() {
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
	if ($_REQUEST['order'] == 'trunkproto') {
		$order='order by trunkproto desc';
		$smarty->assign("order",$_REQUEST['order']);
	} else {
		$order='order by cretime desc';
		$smarty->assign("order",'cretime');
	}

	//取出所有的帐户
	$rpcres = sendrequest($rpcpbx->trunk_list($order,$limit_from,$friconf['cols_in_page']),0);

	//总量
	$smarty->assign("maxcount",count($rpcres['trunks']));
	//列表
	$smarty->assign("table_array",$rpcres['trunks']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_trunk_list.tpl');
	exit;
}

function func_trunk_add() {
	global $smarty;
	global $rpcpbx;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);

	// SIP TEMPlATE
	switch($_REQUEST['trunkproto']) {
		case "sip";
			smarty_output('cpanel/func_trunk_add_sip.tpl');
			break;
		case "iax2":
			smarty_output('cpanel/func_trunk_add_iax2.tpl');
			break;
		case "fxo":
			$rpcres=sendrequest($rpcpbx->trunk_freechan_fxo(),0);
			$smarty->assign("freechan",$rpcres['freechan']);
			smarty_output('cpanel/func_trunk_add_fxo.tpl');
			break;
		case "isdnpri":
			$rpcres=sendrequest($rpcpbx->trunk_freechan_isdnpri(),0);
			$smarty->assign("freechan",$rpcres['freechan']);
			smarty_output('cpanel/func_trunk_add_isdnpri.tpl');
			break;
		case "custom":
			smarty_output('cpanel/func_trunk_add_custom.tpl');
			break;
		default:
			smarty_output('cpanel/func_trunk_add.tpl');
			break;
	}
exit;
}


function do_trunk_add_sip() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkname'] = $_REQUEST['trunkname'];
	$trunk['trunkproto'] = 'sip';
	$trunk['trunkdevice'] = uniqid();
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];

	//验证模式不同的时候检测
	$trunk['trunkprototype'] = $_REQUEST['trunkprototype'];

	if (trim($_REQUEST['trunkprototype']) == 'reg') {
		$trunk['username'] = $_REQUEST['username'];
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = $_REQUEST['username'];
		$trunk['fromdomain'] = $_REQUEST['host'];
		$trunk['register'] = $_REQUEST['username'].':'.$_REQUEST['secret'].'@'.$_REQUEST['host'].':'.$_REQUEST['port'];
	} elseif (trim($_REQUEST['trunkprototype']) == 'ip') {
		$trunk['username'] = null;
		$trunk['secret'] = null;
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = null;
		$trunk['fromdomain'] = $_REQUEST['host'];
		$trunk['register'] = null;
	} elseif (trim($_REQUEST['trunkprototype']) == 'iad') {
		$trunk['username'] = null;
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = 'dynamic';
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = null;
		$trunk['fromdomain'] = null;
		$trunk['register'] = null;
	}

	if (trim($_REQUEST['callerid']) == '') {
		$trunk['callerid'] = '';
	} else {
		$trunk['callerid'] = "'".$_REQUEST['callerid']."' <".$_REQUEST['callerid'].">";
	}

	$trunk['defaultexpiry'] = $_REQUEST['defaultexpiry'];
	$trunk['call-limit'] = $_REQUEST['call-limit'];
	$trunk['progressinband'] = $_REQUEST['progressinband'];
	$trunk['insecure'] = $_REQUEST['insecure'];
	$trunk['qualify'] = $_REQUEST['qualify'];

	//codec
	$allow=null;
	foreach ($_REQUEST['codec'] as $value) {
		$allow = $allow.$value.',';
	}
	$trunk['allow']=rtrim($allow,',');

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk where trunkname = '".$_REQUEST['trunkname']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(141,null,null,null,null,'submit_failed');

	//创建中继
	$rpcres = sendrequest($rpcpbx->trunk_add_sip($trunk),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(142,null,null,null,'pbx_reload.php?action=reload&area=sip&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function func_trunk_edit_sip() {
	global $smarty;
	global $rpcpbx;

	//取得这个用户
	$rpcres = sendrequest($rpcpbx->trunk_get_sip($_REQUEST['id']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	//由于smarty不支持key名字中包含-
	$rpcres['resdata']['calllimit'] = $rpcres['resdata']['call-limit'];

	//产生codec表
	foreach (preg_split('/\,/',$rpcres['resdata']['allow']) as $one) {
		$rpcres['resdata']['codec_'.$one] = true;
	}
	//修改callerid显示
	preg_match('/\<(.+)\>/',$rpcres['resdata']['callerid'],$matches);
	$rpcres['resdata']['callerid'] = $matches[1];

	$smarty->assign("trunk",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_trunk_edit_sip.tpl');
exit;
}

function do_trunk_edit_sip() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkremark'] = $_REQUEST['trunkremark'];

	//验证模式不同的时候检测
	$trunk['trunkprototype'] = $_REQUEST['trunkprototype'];

	if (trim($_REQUEST['trunkprototype']) == 'reg') {
		$trunk['username'] = $_REQUEST['username'];
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = $_REQUEST['username'];
		$trunk['fromdomain'] = $_REQUEST['host'];
		$trunk['register'] = $_REQUEST['username'].':'.$_REQUEST['secret'].'@'.$_REQUEST['host'].':'.$_REQUEST['port'];
	} elseif (trim($_REQUEST['trunkprototype']) == 'ip') {
		$trunk['username'] = null;
		$trunk['secret'] = null;
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = null;
		$trunk['fromdomain'] = $_REQUEST['host'];
		$trunk['register'] = null;
	} elseif (trim($_REQUEST['trunkprototype']) == 'iad') {
		$trunk['username'] = null;
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = 'dynamic';
		$trunk['port'] = $_REQUEST['port'];
		$trunk['fromuser'] = null;
		$trunk['fromdomain'] = null;
		$trunk['register'] = null;
	}

	if (trim($_REQUEST['callerid']) == '') {
		$trunk['callerid'] = '';
	} else {
		$trunk['callerid'] = "'".$_REQUEST['callerid']."' <".$_REQUEST['callerid'].">";
	}

	$trunk['defaultexpiry'] = $_REQUEST['defaultexpiry'];
	$trunk['call-limit'] = $_REQUEST['call-limit'];
	$trunk['progressinband'] = $_REQUEST['progressinband'];
	$trunk['insecure'] = $_REQUEST['insecure'];
	$trunk['qualify'] = $_REQUEST['qualify'];

	//codec
	$allow=null;
	foreach ($_REQUEST['codec'] as $value) {
		$allow = $allow.$value.',';
	}
	$trunk['allow']=rtrim($allow,',');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_sip($_REQUEST['id']),1);

	//编辑
	$rpcres = sendrequest($rpcpbx->trunk_edit_sip($_REQUEST['id'],$trunk),1);

	if ($rpcres['response']['reload'] == true) {
		error_popbox(144,null,null,null,'pbx_reload.php?action=reload&area=sip&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function do_trunk_delete_sip() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_sip($_REQUEST['id']),1);
	$rpcres = sendrequest($rpcpbx->trunk_delete_sip($_REQUEST['id']),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(145,null,null,null,'pbx_reload.php?action=reload&area=sip&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function do_trunk_add_iax2() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkname'] = $_REQUEST['trunkname'];
	$trunk['trunkproto'] = 'iax2';
	$trunk['trunkdevice'] = uniqid();
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];

	//验证模式不同的时候检测
	$trunk['trunkprototype'] = $_REQUEST['trunkprototype'];

	if (trim($_REQUEST['trunkprototype']) == 'reg') {
		$trunk['username'] = $_REQUEST['username'];
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['register'] = $_REQUEST['username'].':'.$_REQUEST['secret'].'@'.$_REQUEST['host'].':'.$_REQUEST['port'];
	} elseif (trim($_REQUEST['trunkprototype']) == 'ip') {
		$trunk['username'] = null;
		$trunk['secret'] = null;
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['register'] = null;
	} elseif (trim($_REQUEST['trunkprototype']) == 'iad') {
		$trunk['username'] = null;
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = 'dynamic';
		$trunk['port'] = 4569;
		$trunk['register'] = null;
	}

	if (trim($_REQUEST['callerid']) == '') {
		$trunk['callerid'] = '';
	} else {
		$trunk['callerid'] = "'".$_REQUEST['callerid']."' <".$_REQUEST['callerid'].">";
	}
	$trunk['qualify'] = $_REQUEST['qualify'];
	$trunk['transfer'] = $_REQUEST['transfer'];
	$trunk['jitterbuffer'] = $_REQUEST['jitterbuffer'];

	//codec
	$allow=null;
	foreach ($_REQUEST['codec'] as $value) {
		$allow = $allow.$value.',';
	}
	$trunk['allow']=rtrim($allow,',');

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk where trunkname = '".$_REQUEST['trunkname']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(141,null,null,null,null,'submit_failed');

	//创建中继
	$rpcres = sendrequest($rpcpbx->trunk_add_iax2($trunk),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(142,null,null,null,'pbx_reload.php?action=reload&area=iax2&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function func_trunk_edit_iax2() {
	global $smarty;
	global $rpcpbx;

	//取得这个trunk
	$rpcres = sendrequest($rpcpbx->trunk_get_iax2($_REQUEST['id']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	//产生codec表
	foreach (preg_split('/\,/',$rpcres['resdata']['allow']) as $one) {
		$rpcres['resdata']['codec_'.$one] = true;
	}
	//修改callerid显示
	preg_match('/\<(.+)\>/',$rpcres['resdata']['callerid'],$matches);
	$rpcres['resdata']['callerid'] = $matches[1];

	$smarty->assign("trunk",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_trunk_edit_iax2.tpl');
exit;
}

function do_trunk_edit_iax2() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkremark'] = $_REQUEST['trunkremark'];

	//验证模式不同的时候检测
	$trunk['trunkprototype'] = $_REQUEST['trunkprototype'];

	if (trim($_REQUEST['trunkprototype']) == 'reg') {
		$trunk['username'] = $_REQUEST['username'];
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['register'] = $_REQUEST['username'].':'.$_REQUEST['secret'].'@'.$_REQUEST['host'].':'.$_REQUEST['port'];
	} elseif (trim($_REQUEST['trunkprototype']) == 'ip') {
		$trunk['username'] = null;
		$trunk['secret'] = null;
		$trunk['host'] = $_REQUEST['host'];
		$trunk['port'] = $_REQUEST['port'];
		$trunk['register'] = null;
	} elseif (trim($_REQUEST['trunkprototype']) == 'iad') {
		$trunk['username'] = null;
		$trunk['secret'] = $_REQUEST['secret'];
		$trunk['host'] = 'dynamic';
		$trunk['port'] = 4569;
		$trunk['register'] = null;
	}

	if (trim($_REQUEST['callerid']) == '') {
		$trunk['callerid'] = '';
	} else {
		$trunk['callerid'] = "'".$_REQUEST['callerid']."' <".$_REQUEST['callerid'].">";
	}

	$trunk['qualify'] = $_REQUEST['qualify'];
	$trunk['transfer'] = $_REQUEST['transfer'];
	$trunk['jitterbuffer'] = $_REQUEST['jitterbuffer'];

	//codec
	$allow=null;
	foreach ($_REQUEST['codec'] as $value) {
		$allow = $allow.$value.',';
	}
	$trunk['allow']=rtrim($allow,',');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_iax2($_REQUEST['id']),1);

	//编辑
	$rpcres = sendrequest($rpcpbx->trunk_edit_iax2($_REQUEST['id'],$trunk),1);

	if ($rpcres['response']['reload'] == true) {
		error_popbox(144,null,null,null,'pbx_reload.php?action=reload&area=iax2&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function do_trunk_delete_iax2() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_iax2($_REQUEST['id']),1);
	$rpcres = sendrequest($rpcpbx->trunk_delete_iax2($_REQUEST['id']),1);

	//完成
	if ($rpcres['response']['reload'] == true) {
		error_popbox(145,null,null,null,'pbx_reload.php?action=reload&area=iax2&return='.urlencode('trunk_manage.php'),'submit_confirm');
	} else {
		error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
	}

exit;
}


function do_trunk_add_custom() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkname'] = $_REQUEST['trunkname'];
	$trunk['trunkproto'] = 'custom';
	$trunk['trunkdevice'] = $_REQUEST['trunkdevice'];
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];
	$trunk['trunkprototype'] = 'custom';

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk where trunkname = '".$_REQUEST['trunkname']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(141,null,null,null,null,'submit_failed');

	//创建中继
	$rpcres = sendrequest($rpcpbx->trunk_add_custom($trunk),1);

	//完成
	error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
exit;
}


function func_trunk_edit_custom() {
	global $smarty;
	global $rpcpbx;

	//取得这个trunk
	$rpcres = sendrequest($rpcpbx->trunk_get_custom($_REQUEST['id']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(125,$rpcres['response']['message'],null,null);

	$smarty->assign("trunk",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_trunk_edit_custom.tpl');
exit;
}

function do_trunk_edit_custom() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');

	$trunk['trunkdevice'] = $_REQUEST['trunkdevice'];
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_custom($_REQUEST['id']),1);

	//编辑
	$rpcres = sendrequest($rpcpbx->trunk_edit_custom($_REQUEST['id'],$trunk),1);

	error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
exit;
}


function do_trunk_delete_custom() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_custom($_REQUEST['id']),1);
	$rpcres = sendrequest($rpcpbx->trunk_delete_custom($_REQUEST['id']),1);

	//完成
	error_popbox(null,null,null,null,'trunk_manage.php','submit_successfuly');
exit;
}


function do_trunk_add_isdnpri() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');
	if (count($_REQUEST['channel']) <= 0)
		error_popbox(147,null,null,null,null,'submit_failed');

	//取得可用的中继分组
	$rpcres = sendrequest($rpcpbx->trunk_freegroup_dahdi(),1);
	if (count($rpcres['freegroup']) <= 0)
		error_popbox(146,null,null,null,null,'submit_failed');

	#get one freegroupid
	$trunk['trunkdevice'] = $rpcres['freegroup'][0];
	$trunk['group'] = $rpcres['freegroup'][0];
	#other
	$trunk['trunkname'] = $_REQUEST['trunkname'];
	$trunk['trunkproto'] = 'dahdi';
	$trunk['trunkprototype'] = 'isdn-pri';
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];
	#array of channels selected
	$trunk['channel_array'] = $_REQUEST['channel'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk where trunkname = '".$_REQUEST['trunkname']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(141,null,null,null,null,'submit_failed');

	//创建中继
	sendrequest($rpcpbx->trunk_add_isdnpri($trunk),1);

	//完成
	error_popbox(148,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('trunk_manage.php'),'submit_confirm');

exit;
}


function func_trunk_edit_dahdi() {
	global $smarty;
	global $rpcpbx;

	// get data
	$rpcres = sendrequest($rpcpbx->base_dbquery("select trunkprototype from trunk where id = '".$_REQUEST['id']."'"),1);
	if ($rpcres['result_array'][0]['trunkprototype'] == "")
		error_popbox(143,null,null,null,null,'submit_failed');

	// isdn-pri mode
	if ($rpcres['result_array'][0]['trunkprototype'] == 'isdn-pri') {

		//取得这个
		$rpcres = sendrequest($rpcpbx->trunk_get_isdnpri($_REQUEST['id']),0);
		$smarty->assign("trunk",$rpcres['resdata']);

		$rpcres=sendrequest($rpcpbx->trunk_freechan_isdnpri(),0);
		$smarty->assign("freechan",$rpcres['freechan']);

		//基本
		$smarty->assign("res_admin",$_SESSION['res_admin']);
		smarty_output('cpanel/func_trunk_edit_isdnpri.tpl');

	} elseif ($rpcres['result_array'][0]['trunkprototype'] == 'fxo') {

		//取得这个
		$rpcres = sendrequest($rpcpbx->trunk_get_fxo($_REQUEST['id']),0);
		$smarty->assign("trunk",$rpcres['resdata']);

		$rpcres=sendrequest($rpcpbx->trunk_freechan_fxo(),0);
		$smarty->assign("freechan",$rpcres['freechan']);

		//基本
		$smarty->assign("res_admin",$_SESSION['res_admin']);
		smarty_output('cpanel/func_trunk_edit_fxo.tpl');

	}

exit;
}

function do_trunk_edit_isdnpri() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');
	if (count($_REQUEST['channel']) <= 0)
		error_popbox(147,null,null,null,null,'submit_failed');

	#other
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];
	#array of channels selected
	$trunk['channel_array'] = $_REQUEST['channel'];

	//检测这个名字是否存在
	$rpcres = sendrequest($rpcpbx->trunk_get_isdnpri($_REQUEST['id']),1);

	//编辑
	$rpcres = sendrequest($rpcpbx->trunk_edit_isdnpri($_REQUEST['id'],$trunk),1);

	//完成
	error_popbox(149,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('trunk_manage.php'),'submit_confirm');


exit;
}


function do_trunk_delete_dahdi() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	// get data
	$rpcres = sendrequest($rpcpbx->base_dbquery("select trunkprototype from trunk where id = '".$_REQUEST['id']."'"),1);
	if ($rpcres['result_array'][0]['trunkprototype'] == "")
		error_popbox(143,null,null,null,null,'submit_failed');

	// isdn-pri mode
	if ($rpcres['result_array'][0]['trunkprototype'] == 'isdn-pri') {
		sendrequest($rpcpbx->trunk_delete_isdnpri($_REQUEST['id']),1);

	} elseif ($rpcres['result_array'][0]['trunkprototype'] == 'fxo') {
		sendrequest($rpcpbx->trunk_delete_fxo($_REQUEST['id']),1);

	}

	//完成
	error_popbox(149,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('trunk_manage.php'),'submit_confirm');

exit;
}

function do_trunk_add_fxo() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');
	if (count($_REQUEST['channel']) <= 0)
		error_popbox(147,null,null,null,null,'submit_failed');

	//取得可用的中继分组
	$rpcres = sendrequest($rpcpbx->trunk_freegroup_dahdi(),1);
	if (count($rpcres['freegroup']) <= 0)
		error_popbox(146,null,null,null,null,'submit_failed');

	#get one freegroupid
	$trunk['trunkdevice'] = $rpcres['freegroup'][0];
	$trunk['group'] = $rpcres['freegroup'][0];
	#other
	$trunk['trunkname'] = $_REQUEST['trunkname'];
	$trunk['trunkproto'] = 'dahdi';
	$trunk['trunkprototype'] = 'fxo';
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];
	#array of channels selected
	$trunk['channel_array'] = $_REQUEST['channel'];

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->base_dbquery("select count(*) from trunk where trunkname = '".$_REQUEST['trunkname']."'"),1);
	if ($rpcres['result_array'][0]['count(*)'] > 0)
		error_popbox(141,null,null,null,null,'submit_failed');

	//创建中继
	sendrequest($rpcpbx->trunk_add_fxo($trunk),1);

	//完成
	error_popbox(148,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('trunk_manage.php'),'submit_confirm');

exit;
}

function do_trunk_edit_fxo() {
	global $rpcpbx;
	global $smarty;
	global $friconf;

	$trunk = array();

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(143,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['trunkname']) == "")
		error_popbox(140,null,null,null,null,'submit_failed');
	if (count($_REQUEST['channel']) <= 0)
		error_popbox(147,null,null,null,null,'submit_failed');

	#other
	$trunk['trunkremark'] = $_REQUEST['trunkremark'];
	#array of channels selected
	$trunk['channel_array'] = $_REQUEST['channel'];

	//检测这个名字是否存在
	sendrequest($rpcpbx->trunk_get_fxo($_REQUEST['id']),1);

	//编辑
	sendrequest($rpcpbx->trunk_edit_fxo($_REQUEST['id'],$trunk),1);

	//完成
	error_popbox(149,null,null,null,'pbx_reload.php?action=reload&area=chan_dahdi&return='.urlencode('trunk_manage.php'),'submit_confirm');


exit;
}

?>