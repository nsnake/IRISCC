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
	this file : statistical

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
if (!isset($_REQUEST['action'])) page_statistical_main();

switch($_REQUEST['action']) {
	case "do_record_download":
		do_record_download();
		break;
	default:
		page_statistical_main();
		break;
}

/*------------------------------------
	responser functions
--------------------------------------*/
function page_statistical_main() {
	global $smarty;
	global $rpcpbx;
	global $friconf;


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

	//格式化条件
	$sqlwhere = '';

	//取出当前要显示的类型
	if ($_REQUEST['dcontext'] == 'trunk') {
		$sqlwhere.=" and dcontext like 'from-trunk%'";
	} elseif ($_REQUEST['dcontext'] == 'exten') {
		$sqlwhere.=" and dcontext like 'from-exten%'";
	}
	$smarty->assign("dcontext",$_REQUEST['dcontext']);

	//取出
	if (trim($_REQUEST['start_date']) != '' && trim($_REQUEST['end_date']) != '') {
		$sqlwhere.=" and calldate >= '".$_REQUEST['start_date']." 00:00:00' and calldate <= '".$_REQUEST['end_date']." 23:59:59'";
		$smarty->assign("start_date",$_REQUEST['start_date']);
		$smarty->assign("end_date",$_REQUEST['end_date']);
	}
	if (trim($_REQUEST['src']) != '') {
		$sqlwhere.=" and src like '".preg_replace("/\*/","%",$_REQUEST['src'])."'";
		$smarty->assign("src",$_REQUEST['src']);
	}
	if (trim($_REQUEST['dst']) != '') {
		$sqlwhere.=" and dst like '".preg_replace("/\*/","%",$_REQUEST['dst'])."'";
		$smarty->assign("dst",$_REQUEST['dst']);
	}
	//如果发现了ID覆盖其他参数
	if (trim($_REQUEST['id']) != "") {
		$sqlwhere = "id = '".$_REQUEST['id']."'";
		$smarty->assign("id",$_REQUEST['id']);
	}

	//最后生成
	$sqlwhere = preg_replace("/^ and/","",$sqlwhere);
	if (trim($sqlwhere) != '')
		$sqlwhere = 'where '.$sqlwhere;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from cdr ".$sqlwhere." order by calldate desc limit ".$limit_from.','.$friconf['cols_in_page']),0);

	//
	foreach ($rpcres['result_array'] as $key => $value) {
		if (trim($value['userfield']) != "") {
			$session = explode(",",$value['userfield']);
			$rpcres['result_array'][$key]['callsessionid']=$session[0];
			if (trim($value['uniqueid']) == "")
				$rpcres['result_array'][$key]['uniqueid']=$session[1];
		}
	}
	$smarty->assign("recordlist",$rpcres['result_array']);


	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_statistical_main.tpl');
	exit;
}

function do_record_download()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//禁止使用因为某些条件
	if (trim($_REQUEST['start_date']) == '' && trim($_REQUEST['end_date']) == '' && trim($_REQUEST['id']) == "")
		error_popbox(601,null,null,null,null,'submit_failed');

	//格式化条件
	$sqlwhere = '';

	//取出当前要显示的类型
	if ($_REQUEST['dcontext'] == 'trunk') {
		$sqlwhere.=" and dcontext like 'from-trunk%'";
	} elseif ($_REQUEST['dcontext'] == 'exten') {
		$sqlwhere.=" and dcontext like 'from-exten%'";
	}

	//取出
	if (trim($_REQUEST['start_date']) != '' && trim($_REQUEST['end_date']) != '') {
		$sqlwhere.=" and calldate >= '".$_REQUEST['start_date']." 00:00:00' and calldate <= '".$_REQUEST['end_date']." 23:59:59'";
	}
	if (trim($_REQUEST['src']) != '') {
		$sqlwhere.=" and src like '".preg_replace("/\*/","%",$_REQUEST['src'])."'";
		$smarty->assign("src",$_REQUEST['src']);
	}
	if (trim($_REQUEST['dst']) != '') {
		$sqlwhere.=" and dst like '".preg_replace("/\*/","%",$_REQUEST['dst'])."'";
		$smarty->assign("dst",$_REQUEST['dst']);
	}
	//如果发现了ID覆盖其他参数
	if (trim($_REQUEST['id']) != "") {
		$sqlwhere = "id = '".$_REQUEST['id']."'";
		$smarty->assign("id",$_REQUEST['id']);
	}

	//最后生成
	$sqlwhere = preg_replace("/^ and/","",$sqlwhere);
	if (trim($sqlwhere) != '')
		$sqlwhere = 'where '.$sqlwhere;

	//取出所有数据
	$rpcres = sendrequest($rpcpbx->base_dbquery("select * from cdr ".$sqlwhere." order by calldate desc"),0);


	//输出
	include("../include/exportxls.php");

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	Header("Content-Type: application/vnd.ms-excel");
	Header("Content-Disposition: attachment; filename=record.xls");
	header("Content-Transfer-Encoding: binary ");

	xlsBOF(); 
	xlsWriteLabel(0,0,"Calling Record Statistical");
	xlsWriteLabel(1,1,"calltype ".$_REQUEST['dcontext']);
	xlsWriteLabel(2,0,"Date Range ".$_REQUEST['start_date']." To ".$_REQUEST['end_date']);
	xlsWriteLabel(3,0,"Call From ".$_REQUEST['src']." To ".$_REQUEST['dst']);
//	xlsWriteLabel(4,0,"calltype");
	xlsWriteLabel(4,0,"");
	xlsWriteLabel(4,1,"src");
	xlsWriteLabel(4,2,"dst");
	xlsWriteLabel(4,3,"disposition");
	xlsWriteLabel(4,4,"duration");
	xlsWriteLabel(4,5,"billsec");
	xlsWriteLabel(4,6,"calldate");
	$xlsRow = 5;
	foreach ($rpcres['result_array'] as $each) {
//		if (preg_match("/trunk/",$each['dcontext'])) {
//			xlsWriteLabel($xlsRow,0,'trunk');
//		} elseif (preg_match("/exten/",$each['dcontext'])) {
//			xlsWriteLabel($xlsRow,0,'exten');
//		} else {
			xlsWriteLabel($xlsRow,0,'');
//		}
		xlsWriteLabel($xlsRow,1,$each['src']);
		xlsWriteLabel($xlsRow,2,$each['dst']);
		xlsWriteLabel($xlsRow,3,$each['disposition']);
		xlsWriteLabel($xlsRow,4,$each['duration']);
		xlsWriteLabel($xlsRow,5,$each['billsec']);
		xlsWriteLabel($xlsRow,6,$each['calldate']);
		$xlsRow++;
	}
	xlsEOF();

	exit;
}
?>