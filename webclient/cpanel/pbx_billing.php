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
	this file : billing lite

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
if (!isset($_REQUEST['action'])) page_billing_stat();

switch($_REQUEST['action']) {
//	case "func_billinvoice_search":
//		func_billinvoice_search();
//		break;
//	case "func_billinvoice_list":
//		func_billinvoice_list();
//		break;
	case "do_invoice_download":
		do_invoice_download();
		break;
	case "do_ratetable_delete":
		do_ratetable_delete();
		break;
	case "do_ratetable_add":
		do_ratetable_add();
		break;
//	case "do_billing_enable":
//		do_billing_enable();
//		break;
//	case "do_billing_per":
//		do_billing_per();
//		break;
	default:
		page_billing_stat();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_billing_stat() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出费率
	$rpcres = sendrequest($rpcpbx->billing_rule_list($_REQUEST['accountcode']),0);
	$smarty->assign("rules",$rpcres['rules']);

	//测试找出可用月份
	$avaliable_date=array();
	for ($i=1;$i<=12;$i++) {
		$timestamp = strtotime("-".$i." month");
		$rpcres = sendrequest($rpcpbx->base_dbquery("select id from billinginvoice where calldate >= '".Date('Y-m',$timestamp)."-01 00:00:00' and calldate <= '".Date('Y-m',$timestamp)."-31 23:59:59' limit 1"),0);
		if ($rpcres['result_array'][0]) {
			array_push($avaliable_date,array('year'=>Date('Y',$timestamp),'month'=>Date('m',$timestamp)));
		} else {#如果这个月没记录那之前一个月也会没记录,所以就不再检索(提高速度)
			break;
		}
	}
	$smarty->assign("invoice_date",$avaliable_date);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_billing_stat.tpl');
	exit;
}
//function do_billing_enable() {
//	global $rpcpbx;
//	global $smarty;
//	global $friconf;
//
//	$rpcres = sendrequest($rpcpbx->billing_var_set('enable',$_REQUEST['enable']),1);
//
//	//完成
//	error_popbox(null,null,null,null,'pbx_reload.php?action=restart&area=fri2d&return='.urlencode('pbx_billing.php'),'submit_successfuly');
//
//exit;
//}
//function do_billing_per() {
//	global $rpcpbx;
//	global $smarty;
//	global $friconf;
//
//	$rpcres = sendrequest($rpcpbx->billing_var_set('per',$_REQUEST['per']),1);
//
//	//完成
//	error_popbox(null,null,null,null,'pbx_reload.php?action=restart&area=fri2d&return='.urlencode('pbx_billing.php'),'submit_successfuly');
//
//exit;
//}

function do_ratetable_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['dst_prefix']) == "")
		error_popbox(170,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['persecond']) == "")
		error_popbox(171,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['percost']) == "")
		error_popbox(172,null,null,null,null,'submit_failed');

	$rule=array();

	$rule['dst_prefix']=$_REQUEST['dst_prefix'];
	$rule['destnation']=$_REQUEST['destnation'];
	$rule['persecond']=$_REQUEST['persecond'];
	$rule['percost']=$_REQUEST['percost'];
	$rule['secret']=$_REQUEST['password'];

	//增加这个费率
	$rpcres = sendrequest($rpcpbx->billing_rule_add($rule),1);

	error_popbox(null,null,null,null,'pbx_billing.php','submit_successfuly');

exit;
}

function do_ratetable_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "")
		error_popbox(120,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->billing_rule_delete($_REQUEST['id']),1);

	//完成
	error_popbox(null,null,null,null,'pbx_billing.php','submit_successfuly');
}

function do_invoice_download()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$rpcres = sendrequest($rpcpbx->base_dbquery("select src,dst,calldate,billsec,cost from billinginvoice where calldate >= '".$_REQUEST['year']."-".$_REQUEST['month']."-01 00:00:00' and calldate <= '".$_REQUEST['year']."-".$_REQUEST['month']."-31 23:59:59' order by calldate desc"),0);

	#CSV FORMAT
	if ($_REQUEST['format'] == 'csv') {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		Header("Content-Type: application/vnd.ms-excel");
		Header("Content-Disposition: attachment; filename=billing_invoice.csv");
		header("Content-Transfer-Encoding: binary ");
		echo "caller,called,calldate,billsec,cost\n";
		foreach ($rpcres['result_array'] as $each) {
			echo $each['src'].','.$each['dst'].','.$each['calldate'].','.$each['billsec'].','.$each['cost'].','."\n";
		}
		exit;

	} else {
		include("../include/exportxls.php");

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		Header("Content-Type: application/vnd.ms-excel");
		Header("Content-Disposition: attachment; filename=billing_invoice.xls");
		header("Content-Transfer-Encoding: binary ");

		xlsBOF(); 
		xlsWriteLabel(0,0,"src");
		xlsWriteLabel(0,1,"dst");
		xlsWriteLabel(0,2,"calldate");
		xlsWriteLabel(0,3,"billsec");
		xlsWriteLabel(0,4,"cost");
		$xlsRow = 1;
		foreach ($rpcres['result_array'] as $each) {
			xlsWriteLabel($xlsRow,0,$each['src']);
			xlsWriteLabel($xlsRow,1,$each['dst']);
			xlsWriteLabel($xlsRow,2,$each['calldate']);
			xlsWriteLabel($xlsRow,3,$each['billsec']);
			xlsWriteLabel($xlsRow,4,$each['cost']);
			$xlsRow++;
		}
		xlsEOF();
		exit;
	}

exit;
}

//function func_billinvoice_search()
//{
//	global $smarty;
//	//基本
//	$smarty->assign("res_admin",$_SESSION['res_admin']);
//	smarty_output('cpanel/func_billinvoice_search.tpl');
//
//exit;
//}
//
//function func_billinvoice_list()
//{
//	global $smarty;
//	global $rpcpbx;
//	global $friconf;
//
//	//计算应该规定的时间
//	$startdate=null;
//	$enddate=null;
//	$accountcode=$_REQUEST['accountcode'];
//	if ($_REQUEST['dateround'] == "current_month") {
//		$startdate = date("Y-m-01 00:00:00");
//		$enddate = date("Y-m-31 23:59:59");
//	} elseif ($_REQUEST['dateround'] == "last_month") {
//		$startdate = date("Y-m-01 00:00:00",strtotime("last month"));
//		$enddate = date("Y-m-31 23:59:59",strtotime("last month"));
//	} elseif ($_REQUEST['dateround'] == "all") {
//		$startdate = null;
//		$enddate = null;
//	} else {
//		$startdate = $_REQUEST['startdate'];
//		$enddate = $_REQUEST['enddate'];
//	}
//	$smarty->assign("accountcode",$_REQUEST['accountcode']);
//	$smarty->assign("startdate",$startdate);
//	$smarty->assign("enddate",$enddate);
//
//	//分页显示计算
//	$limit_from=0;
//	if (!$_REQUEST['cols_in_page'] || $_REQUEST['cols_in_page'] == 'frist' || $_REQUEST['cols_in_page'] < $friconf['cols_in_page']) {
//		$limit_from=0;
//		$smarty->assign("pre_cols",0);
//		$smarty->assign("next_cols",$friconf['cols_in_page']);
//	} else {
//		$limit_from=$_REQUEST['cols_in_page'];
//		$smarty->assign("pre_cols",$_REQUEST['cols_in_page']-$friconf['cols_in_page']);
//		$smarty->assign("next_cols",($_REQUEST['cols_in_page']+$friconf['cols_in_page']));
//	}
//	$smarty->assign("from_cols",($limit_from+1));
//	$smarty->assign("to_cols",($limit_from+$friconf['cols_in_page']));
//
//	//取出所有数据
//	$rpcres = sendrequest($rpcpbx->billing_invoice_list($startdate,$enddate,$accountcode),1);
//
//	//统计总数
//	$totalbillsec = 0;
//	$totalcost = 0;
//	foreach ($rpcres['invoice'] as $value) {
//		$totalbillsec = $totalbillsec + $value['billsec'];
//		$totalcost = $totalcost + $value['cost'];
//	}
//	$smarty->assign("totalbillsec",$totalbillsec);
//	$smarty->assign("totalcost",$totalcost);
//
//	//根据分页显示进行取得数据显示
//	$invoice_array=array();
//	for ($i=$limit_from;$i<=($limit_from+$friconf['cols_in_page']-1);$i++) {
//		if (!$rpcres['invoice'][$i])
//			break;
//		array_push($invoice_array,$rpcres['invoice'][$i]);
//	}
//	$smarty->assign("invoice",$invoice_array);
//
//	//基本
//	$smarty->assign("res_admin",$_SESSION['res_admin']);
//	smarty_output('cpanel/func_billinvoice_list.tpl');
//
//exit;
//}
?>