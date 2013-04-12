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
	this file : system record

    $Id: acd_record.php 398 2010-05-13 04:28:22Z hoowa $
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
if (!isset($_REQUEST['action'])) page_record_list();

switch($_REQUEST['action']) {
	case "func_recordtrigger_add":
		func_recordtrigger_add();
		break;
	case "do_recordtrigger_add":
		do_recordtrigger_add();
		break;
	case "func_recordtrigger_edit":
		func_recordtrigger_edit();
		break;
	case "do_recordtrigger_edit":
		do_recordtrigger_edit();
		break;
	case "do_recordtrigger_delete":
		do_recordtrigger_delete();
		break;
	case "func_recordfiles_list":
		func_recordfiles_list();
		break;
	case "do_recordfiles_delete":
		do_recordfiles_delete();
		break;
	case "func_ivrfiles_list":
		func_ivrfiles_list();
		break;
	case "do_ivrfiles_delete":
		do_ivrfiles_delete();
		break;
	default:
		page_record_list();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_record_list() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//磁盘使用
	$rpcres = sendrequest($rpcpbx->voicefiles_diskfree('ivrmenu'),0);
	$smarty->assign("ivrmenu_diskfree_gigabyte",round($rpcres['diskfree']/1024/1024/1024,4));
	$diskfree_percent = round(($rpcres['diskfree']/$rpcres['disktotal'])*100);
	$smarty->assign("ivrmenu_diskfree_percent",$diskfree_percent);
	$smarty->assign("ivrmenu_diskused_percent",(100-$diskfree_percent));

	$rpcres = sendrequest($rpcpbx->voicefiles_diskfree('sysautomon'),0);
	$smarty->assign("sysautomon_diskfree_gigabyte",round($rpcres['diskfree']/1024/1024/1024,4));
	$diskfree_percent = round(($rpcres['diskfree']/$rpcres['disktotal'])*100);
	$smarty->assign("sysautomon_diskfree_percent",$diskfree_percent);
	$smarty->assign("sysautomon_diskused_percent",(100-$diskfree_percent));


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
	$rpcres = sendrequest($rpcpbx->sysautomon_list_trigger($limit_from,$friconf['cols_in_page']),0);

	//根据分页显示进行取得数据显示
	$smarty->assign("lists_array",$rpcres['resdata']);
	$smarty->assign("maxcount",count($rpcres['resdata']));

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_record_list.tpl');
	exit;
}


function func_recordtrigger_edit() {
	global $smarty;
	global $rpcpbx;

	//取出
	$rpcres = sendrequest($rpcpbx->sysautomon_get_trigger($_REQUEST['id']),0);
	if ($rpcres['resdata']['keepfortype'] == '0') {
		$rpcres['resdata']['keepfortype0']=$rpcres['resdata']['keepforargs'];
	} elseif ($rpcres['resdata']['keepfortype'] == '1') {
		$rpcres['resdata']['keepfortype1']=$rpcres['resdata']['keepforargs'];
	} elseif ($rpcres['resdata']['keepfortype'] == '2') {
		$rpcres['resdata']['keepfortype2']='checked';
	}

	$smarty->assign("resdata",$rpcres['resdata']);
	$smarty->assign("members",preg_replace("/\&/","\n",$rpcres['resdata']['members']));
//	$members = preg_split("/\&/",$rpcres['resdata']['members']);
//	$members_assign=array();
//
//	//取出所有备选分机
//	$rpcres = sendrequest($rpcpbx->extension_list('order by accountcode',0,65536),0);
//	$extensions=array();
//	//(排除已选)
//	foreach ($rpcres['extensions'] as $exten) {
//		$nosave=false;
//		foreach ($members as $member) {
//			if ($exten['accountcode'] == $member) {
//				$nosave=true;
//				array_push($members_assign,$exten);
//				break;
//			}
//		}
//		if ($nosave != true) {
//			array_push($extensions,$exten);
//		}
//	}
//	$smarty->assign("extensions",$extensions);
//	$smarty->assign("members",$members_assign);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_recordtrigger_edit.tpl');
exit;
}


function do_recordtrigger_edit()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$newrecord=array();
	$newrecord['triggername']=$_REQUEST['triggername'];

	if ($_REQUEST['recordout'] == 'true') {
		$newrecord['recordout'] = 1;
	} else {
		$newrecord['recordout'] = 0;
	}
	if ($_REQUEST['recordin'] == 'true') {
		$newrecord['recordin'] = 1;
	} else {
		$newrecord['recordin'] = 0;
	}
	if ($_REQUEST['recordqueue'] == 'true') {
		$newrecord['recordqueue'] = 1;
	} else {
		$newrecord['recordqueue'] = 0;
	}

	if ($_REQUEST['keepfortype2'] == 'true') {
		$newrecord['keepfortype'] = 2;
	} elseif (trim($_REQUEST['keepfortype1']) != '') {
		$newrecord['keepfortype'] = 1;
		$newrecord['keepforargs'] = $_REQUEST['keepfortype1'];
	} elseif (trim($_REQUEST['keepfortype0']) != '') {
		$newrecord['keepfortype'] = 0;
		$newrecord['keepforargs'] = $_REQUEST['keepfortype0'];
	}

//	foreach ($_REQUEST['members'] as $member) {
//		$newrecord['members'] .= $member.'&';
//	}

	$newrecord['members']=preg_replace("/\r/","",$_REQUEST['members']);
	$newrecord['members']=preg_replace("/\n/","&",$newrecord['members']);

	//创建信息
	$rpcres = sendrequest($rpcpbx->sysautomon_edit_trigger($_REQUEST['id'],$newrecord),0);

	//完成
	error_popbox(null,null,null,null,'acd_record.php','submit_successfuly');
exit;
}


function func_recordtrigger_add()
{
	global $smarty;
	global $rpcpbx;

	//取出所有备选分机
	//$rpcres = sendrequest($rpcpbx->extension_list('order by accountcode',0,65536),0);
	//$smarty->assign("extensions",$rpcres['extensions']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_recordtrigger_add.tpl');
exit;
}

function do_recordtrigger_add()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['triggername']) == "")
		error_popbox(230,null,null,null,null,'submit_failed');
	if (count($_REQUEST['members']) <= 0)
		error_popbox(231,null,null,null,null,'submit_failed');

	$newrecord=array();
	$newrecord['triggername']=$_REQUEST['triggername'];

	if ($_REQUEST['recordout'] == 'true') {
		$newrecord['recordout'] = 1;
	} else {
		$newrecord['recordout'] = 0;
	}
	if ($_REQUEST['recordin'] == 'true') {
		$newrecord['recordin'] = 1;
	} else {
		$newrecord['recordin'] = 0;
	}
	if ($_REQUEST['recordqueue'] == 'true') {
		$newrecord['recordqueue'] = 1;
	} else {
		$newrecord['recordqueue'] = 0;
	}

	if ($_REQUEST['keepfortype2'] == 'true') {
		$newrecord['keepfortype'] = 2;
	} elseif (trim($_REQUEST['keepfortype1']) != '') {
		$newrecord['keepfortype'] = 1;
		$newrecord['keepforargs'] = $_REQUEST['keepfortype1'];
	} elseif (trim($_REQUEST['keepfortype0']) != '') {
		$newrecord['keepfortype'] = 0;
		$newrecord['keepforargs'] = $_REQUEST['keepfortype0'];
	}

//	foreach ($_REQUEST['members'] as $member) {
//		$newrecord['members'] .= $member.'&';
//	}

	$newrecord['members']=preg_replace("/\r/","",$_REQUEST['members']);
	$newrecord['members']=preg_replace("/\n/","&",$newrecord['members']);

	//创建信息
	$rpcres = sendrequest($rpcpbx->sysautomon_add_trigger($newrecord),0);

	//完成
	error_popbox(null,null,null,null,'acd_record.php','submit_successfuly');

exit;
}

function do_recordtrigger_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(113,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->sysautomon_delete_trigger($_REQUEST['id']),1);

	error_popbox(null,null,null,null,'acd_record.php','submit_successfuly');
exit;
}

function func_recordfiles_list()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->sysautomon_list_trigger(0,65536),0);

	//根据分页显示进行取得数据显示
	$smarty->assign("trigger_lists",$rpcres['resdata']);

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
	$rpcres = sendrequest($rpcpbx->voicefiles_list('sysautomon',$limit_from,$friconf['cols_in_page'],$_REQUEST['trigger_filter']),0);

	//根据分页显示进行取得数据显示
	foreach ($rpcres['resdata'] as $key => $value) {
		//设置的变量
		$args_array=array();
		foreach (preg_split("/\&/",$value['args']) as $eachline) {
			if ($eachline == '')
				continue;
			$oneparam = preg_split("/\=/",$eachline);
			$args_array[$oneparam[0]]=$oneparam[1];
		}
		$rpcres['resdata'][$key]['args_res']=$args_array;
		//设置录音时间
		$cretime_date = explode(" ",$value['cretime']);
		$rpcres['resdata'][$key]['cretime_date']=$cretime_date;

		//取出CDR关联记录
        //
        // by hoowa 2010-4-11
        // 默认的like方式无法准确的匹配到当前录音所对应的CDR(尤其是队列被叫模式,第一个不接,第二个接的时候)
        // 新的匹配方法为:
        // 1. 通过voicefiles得到callsessionid
        // 2. sysautomon.dynamic生成的filename非常精准几乎不可能重复.
        // 2. 通过callsession_acts对应callsessionid和filename得到cdruniqueid
        // 3. 通过cdruniqueid和callsessionid直接精确找到cdr记录
        //
        $subrpcres = sendrequest($rpcpbx->base_dbquery("select cdruniqueid from callsession_acts where callsessionid ='".$value['associate']."' AND function = 'sysautomon' AND var1value= '".$value['filename']."'"),0);
        if ($subrpcres['result_array']['0'] && $subrpcres['result_array']['0']['cdruniqueid'] != "") {
            $subrpcres = sendrequest($rpcpbx->base_dbquery("select id,calldate,src,dst from cdr where userfield = '".$value['associate']."\,".$subrpcres['result_array']['0']['cdruniqueid']."' LIMIT 1"),0);
            $rpcres['resdata'][$key]['cdr']=$subrpcres['result_array']['0'];
        }
//		$subrpcres = sendrequest($rpcpbx->base_dbquery("select id,calldate,src,dst from cdr where userfield like '".$value['associate']."\,%' LIMIT 1"),0);
//		$rpcres['resdata'][$key]['cdr']=$subrpcres['result_array']['0'];
	}
	$smarty->assign("recordfiles_array",$rpcres['resdata']);
	$smarty->assign("maxcount",count($rpcres['resdata']));

	//过滤器
	$smarty->assign("trigger_filter",$_REQUEST['trigger_filter']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_recordfiles_list.tpl');
	exit;
}

function do_recordfiles_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) != "") {
		$rpcres = sendrequest($rpcpbx->voicefiles_delete($_REQUEST['id']),1);
	} elseif (trim($_REQUEST['area']) == "all") {

		$rpcres = sendrequest($rpcpbx->voicefiles_list('sysautomon',$limit_from,$friconf['cols_in_page'],$_REQUEST['trigger_filter']),0);
		foreach ($rpcres['resdata'] as $value) {
			sendrequest($rpcpbx->voicefiles_delete($value['id']),1);
		}

	}


	header('Location: acd_record.php?action=func_recordfiles_list&trigger_filter='.$_REQUEST['trigger_filter']."\n\n");

exit;
}

function func_ivrfiles_list()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);

	//根据分页显示进行取得数据显示
	$smarty->assign("ivrmenu_lists",$rpcres['ivrmenu']);

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
	$rpcres = sendrequest($rpcpbx->voicefiles_list('ivrmenu',$limit_from,$friconf['cols_in_page'],$_REQUEST['ivrmenu_filter']),0);
	foreach ($rpcres['resdata'] as $key => $value) {
		$array_filename = preg_split("/\_/",$value['filename']);
		$rpcres['resdata'][$key]['caller']=$array_filename[0];
	}
	$smarty->assign("ivrmenufiles_array",$rpcres['resdata']);
	$smarty->assign("maxcount",count($rpcres['resdata']));

	//过滤器
	$smarty->assign("ivrmenu_filter",$_REQUEST['ivrmenu_filter']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivrfiles_list.tpl');
	exit;
}

function do_ivrfiles_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) != "") {
		$rpcres = sendrequest($rpcpbx->voicefiles_delete($_REQUEST['id']),1);
	} elseif (trim($_REQUEST['area']) == "all") {

		$rpcres = sendrequest($rpcpbx->voicefiles_list('ivrmenu',$limit_from,$friconf['cols_in_page'],$_REQUEST['ivrmenu_filter']),0);
		foreach ($rpcres['resdata'] as $value) {
			sendrequest($rpcpbx->voicefiles_delete($value['id']),1);
		}

	}


	header('Location: acd_record.php?action=func_ivrfiles_list&trigger_filter='.$_REQUEST['ivrmenu_filter']."\n\n");

exit;
}
?>