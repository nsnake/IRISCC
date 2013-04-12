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
if (!isset($_REQUEST['action'])) page_soundmanager_list();

switch($_REQUEST['action']) {
	case "func_soundmanager_add":
		func_soundmanager_add();
		break;
	case "do_soundmanager_add":
		do_soundmanager_add();
		break;
	case "func_soundmanager_edit":
		func_soundmanager_edit();
		break;
	case "do_soundmanager_download":
		do_soundmanager_download();
		break;
	case "do_soundmanager_edit":
		do_soundmanager_edit();
		break;
	case "do_soundmanager_recordoverphone":
		do_soundmanager_recordoverphone();
		break;
	case "do_soundmanager_listenoverphone":
		do_soundmanager_listenoverphone();
		break;
	case "do_soundmanager_delete":
		do_soundmanager_delete();
		break;
	default:
		page_soundmanager_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_soundmanager_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//磁盘使用
	$rpcres = sendrequest($rpcpbx->voicefiles_diskfree('sound'),0);
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
	$rpcres = sendrequest($rpcpbx->voicefiles_list('sound',$limit_from,$friconf['cols_in_page']),0);

	//根据分页显示进行取得数据显示
	foreach ($rpcres['resdata'] as $key=>$value) {
		$description_short = join('',subString_UTF8($value['description'], 0, 18));
		if (strlen($description_short) < strlen($value['description']))
			$description_short = $description_short.'...';
		$rpcres['resdata'][$key]['description_short']=$description_short;
		$rpcres['resdata'][$key]['filesize']=round($value['filesize']/1024);
	}
	$smarty->assign("soundmusic_array",$rpcres['resdata']);
	$smarty->assign("maxcount",count($rpcres['resdata']));

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_soundmanager_list.tpl');
	exit;
}


function func_soundmanager_edit() {
	global $smarty;
	global $rpcpbx;

	//取出
	$rpcres = sendrequest($rpcpbx->voicefiles_get($_REQUEST['id']),0);
	$smarty->assign("file",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_soundmanager_edit.tpl');
exit;
}


function do_soundmanager_download()
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

function do_soundmanager_edit()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$filedata=array();

	//检测是否上载文件了
	if ($_FILES['soundfile']['name']) {
		$filestream = file_get_contents($_FILES['soundfile']['tmp_name']);
		$filedata['filestream']=$filestream;
		list($filename,$extname) = preg_split("/\./",$_FILES['soundfile']['name']);
		$filedata['extname']=$extname;
	} else {
		$filedata['filestream']=null;
	}

	//更改普通信息
	$filedata['description']=$_REQUEST['description'];

	$rpcres = sendrequest($rpcpbx->voicefiles_edit('sound',$_REQUEST['id'],$filedata),1);

	//完成
	header('Location: pbx_soundmanager.php?action=func_soundmanager_edit&id='.$_REQUEST['id']."\n\n");
}


function do_soundmanager_recordoverphone()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['extension']) == "")
		error_popbox(180,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	//查找到这个分机的资源号码
	$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['extension']),1);

	//不是分机
	if (!$rpcres['resdata'] || $rpcres['resdata']['typeof'] != 'extension')
		error_popbox(181,null,null,null,null,'submit_failed');


	//查找
	$rpcres = sendrequest($rpcpbx->extension_get($rpcres['resdata']['assign']),1);

	//不是分机
	if (!$rpcres['extension'] || $rpcres['extension']['deviceproto'] == 'virtual')
		error_popbox(181,null,null,null,null,'submit_failed');

	if ($rpcres['extension']['deviceproto'] == 'custom') {
		$channel = $rpcres['extension']['devicestring'];
	} elseif ($rpcres['extension']['deviceproto'] == 'fxs') {
		$channel = 'DAHDI/'.$rpcres['extension']['devicestring'];
	} else {
		$channel = $rpcres['extension']['deviceproto'].'/'.$rpcres['extension']['devicestring'];
	}

	//发起录音
	$actionid = uniqid();
	$rpcres = $rpcpbx->ami_originate($actionid,array(
		'Channel'=>$channel,
		'CallerID'=>'Record Over Phone',
		'MaxRetries'=>0,
		'WaitTime'=>30,
		'RetryTime'=>15,
		'Application'=>'AGI',
		'Data'=>'agi://127.0.0.1/originate_recordoverphone?soundfileid='.$_REQUEST['id'].'&extension='.$_REQUEST['extension']));

	error_popbox(null,null,null,null,'pbx_soundmanager.php','submit_successfuly');

exit;
}

function do_soundmanager_listenoverphone()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['extension']) == "")
		error_popbox(180,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	//查找到这个分机的资源号码
	$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['extension']),1);

	//不是分机
	if (!$rpcres['resdata'] || $rpcres['resdata']['typeof'] != 'extension')
		error_popbox(181,null,null,null,null,'submit_failed');


	//查找
	$rpcres = sendrequest($rpcpbx->extension_get($rpcres['resdata']['assign']),1);

	//不是分机
	if (!$rpcres['extension'] || $rpcres['extension']['deviceproto'] == 'virtual')
		error_popbox(181,null,null,null,null,'submit_failed');

	if ($rpcres['extension']['deviceproto'] == 'custom') {
		$channel = $rpcres['extension']['devicestring'];
	} elseif ($rpcres['extension']['deviceproto'] == 'fxs') {
		$channel = 'DAHDI/'.$rpcres['extension']['devicestring'];
	} else {
		$channel = $rpcres['extension']['deviceproto'].'/'.$rpcres['extension']['devicestring'];
	}
	
	//发起试听
	$actionid = uniqid();
	$rpcres = $rpcpbx->ami_originate($actionid,array(
		'Channel'=>$channel,
		'CallerID'=>'Listen Over Phone',
		'MaxRetries'=>0,
		'WaitTime'=>30,
		'RetryTime'=>15,
		'Application'=>'AGI',
		'Data'=>'agi://127.0.0.1/originate_listenoverphone?soundfileid='.$_REQUEST['id'].'&extension='.$_REQUEST['extension']));

	error_popbox(null,null,null,null,'pbx_soundmanager.php','submit_successfuly');

exit;
}

function func_soundmanager_add()
{
	global $smarty;
	global $rpcpbx;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_soundmanager_add.tpl');
exit;
}

function do_soundmanager_add()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['filename']) == "")
		error_page(182,$rpcres['response']['message'],null,null);
	if (preg_match("/[^0-9a-zA-Z\-\_]/",$_REQUEST['filename']))
		error_page(182,$rpcres['response']['message'],null,null);

	$_REQUEST['filename']=preg_replace("/\./","",$_REQUEST['filename']);

	//检测这个名字是否已经使用过了
	$rpcres = sendrequest($rpcpbx->voicefiles_get(null,'sound',$_REQUEST['filename'],'user_custom'),0);
	if ($rpcres['resdata'])
		error_page(183,$rpcres['response']['message'],null,null);

	$newrecord=array();
	$newrecord['filename']=$_REQUEST['filename'];
	$newrecord['extname']='';
	$newrecord['folder']='user_custom';
	$newrecord['description']=$_REQUEST['description'];
	$newrecord['associate']='cn';
	$newrecord['args']='';
	$newrecord['readonly']='0';

	//创建信息
	$rpcres = sendrequest($rpcpbx->voicefiles_add('sound',$newrecord),0);

	//取出来其ID
	$rpcres = sendrequest($rpcpbx->voicefiles_get(null,'sound',$_REQUEST['filename'],'user_custom'),0);

	//完成
	header('Location: pbx_soundmanager.php?action=func_soundmanager_edit&id='.$rpcres['resdata']['id']."\n\n");

exit;
}

function do_soundmanager_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->voicefiles_delete($_REQUEST['id']),1);

	error_popbox(null,null,null,null,'pbx_soundmanager.php','submit_successfuly');
exit;
}
?>