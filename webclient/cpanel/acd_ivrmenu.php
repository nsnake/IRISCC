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
	this file : ivr menu

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
if (!isset($_REQUEST['action'])) page_ivr_main();

switch($_REQUEST['action']) {
	case "func_ivr_treeview":
		func_ivr_treeview();
		break;
	case "func_ivr_optmenu";
		func_ivr_optmenu();
		break;
	case "func_ivrmenu_add";
		func_ivrmenu_add();
		break;
	case "do_ivrmenu_add";
		do_ivrmenu_add();
		break;
	case "func_ivrmenu_edit";
		func_ivrmenu_edit();
		break;
	case "do_ivrmenu_edit";
		do_ivrmenu_edit();
		break;
	case "do_ivrmenu_delete";
		do_ivrmenu_delete();
		break;
	case "page_ivraction_list";
		page_ivraction_list();
		break;
	case "func_ivraction_add";
		func_ivraction_add();
		break;
	case "func_ivraction_add_step2";
		func_ivraction_add_step2();
		break;
	case "do_ivraction_add";
		do_ivraction_add();
		break;
	case "func_ivraction_edit";
		func_ivraction_edit();
		break;
	case "do_ivraction_edit";
		do_ivraction_edit();
		break;
	case "do_ivraction_delete";
		do_ivraction_delete();
		break;
	case "do_ivraction_recall";
		do_ivraction_recall();
		break;
	case "page_ivruserinput_list";
		page_ivruserinput_list();
		break;
	case "do_ivruserinput_generalset";
		do_ivruserinput_generalset();
		break;
	case "func_ivruserinput_add";
		func_ivruserinput_add();
		break;
	case "do_ivruserinput_add";
		do_ivruserinput_add();
		break;
	case "func_ivruserinput_edit";
		func_ivruserinput_edit();
		break;
	case "do_ivruserinput_edit";
		do_ivruserinput_edit();
		break;
	case "do_ivruserinput_delete";
		do_ivruserinput_delete();
		break;
	default:
		page_ivr_main();
		break;
}


/*------------------------------------
	responser functions
--------------------------------------*/
function page_ivr_main() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//取出所有
	$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);

	$ivrmenu_readonly=array();
	$ivrmenu_array=array();
	foreach ($rpcres['ivrmenu'] as $value) {
		if ($value['readonly'] == '1') {
			array_push($ivrmenu_readonly,$value);
		} else {
			array_push($ivrmenu_array,$value);
		}
	}

	//列表
	$smarty->assign("ivrmenu_array",$ivrmenu_array);
	$smarty->assign("ivrmenu_readonly",$ivrmenu_readonly);

	//当前的
	$smarty->assign("selected_ivrnumber",$_REQUEST['ivrnumber']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_ivr_main.tpl');
	exit;
}


function func_ivr_treeview() {
	global $smarty;
	global $rpcpbx;

	//取得这个IVR本身的数据
	$rpcres = sendrequest($rpcpbx->ivrmenu_get_menu($_REQUEST['ivrnumber']),0);
	//指定不存在
	if (!$rpcres['resdata'])
		error_page(113,$rpcres['response']['message'],null,null);

	$smarty->assign("ivrmenu",$rpcres['resdata']);



	//列表显示ACTIONS
	$rpcres = sendrequest($rpcpbx->ivrmenu_list_action($_REQUEST['ivrnumber']),0);
	//重新排列
	$allrule = array();
	$proirety_aslevel = 0;
	foreach ($rpcres['actions'] as $each) {

		//设置参数表
		$args_array=array();
		foreach (preg_split("/\&/",$each['args']) as $eachline) {
			if ($eachline == '')
				continue;
			$oneparam = preg_split("/\=/",$eachline);
			$args_array[$oneparam[0]]=$oneparam[1];
		}
		//取出gotivr 的ivrname
		if (array_key_exists('gotoivr',$args_array)) {
			$gotoivrres = sendrequest($rpcpbx->ivrmenu_get_menu($args_array['gotoivr']),0);
			$args_array['gotoivr_name']=$gotoivrres['resdata']['ivrname'];
		}
		//取出actpoint的level
		if (array_key_exists('actpoint',$args_array) && $args_array['actpoint'] != '') {
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($args_array['gotoivr']),0);
			$level=0;
			foreach ($actres['actions'] as $eachact) {
				if ($eachact['id'] == $args_array['actpoint']) {
					$args_array['actpoint_level']=$level;
					break;
				}
				$level++;
			}
		}
		//设置args的array
		$each['args_array']=$args_array;

		//当前这条动作所在的ivr中的级别编号
		$each['proirety_aslevel']=$proirety_aslevel;

		array_push($allrule,$each);
		$proirety_aslevel++;
	}
	$smarty->assign("action_array",$allrule);



	//取出IVROPTIONS并且得到他们所对应的第二级IVRMENU和第二级IVRACTION和第二级IVROPT
	$rpcres = sendrequest($rpcpbx->ivrmenu_list_ivruserinput($_REQUEST['ivrnumber'],0),0);
	foreach ($rpcres['ivruserinputs'] as $key=>$value) {

		//取出L2 IVRMENU
		$ivrres = sendrequest($rpcpbx->ivrmenu_get_menu($value['gotoivrnumber']),0);
		$rpcres['ivruserinputs'][$key]['gotoivrnumber_ref']=$ivrres['resdata'];

		//取出L2 IVRACTIONS
		$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['gotoivrnumber']),0);
		$l2allactions = array();
		$l2proirety_aslevel = 0;
		foreach ($actres['actions'] as $l2each) {
			//设置参数表
			$l2args_array=array();
			foreach (preg_split("/\&/",$l2each['args']) as $l2eachline) {
				if ($l2eachline == '')
					continue;
				$l2oneparam = preg_split("/\=/",$l2eachline);
				$l2args_array[$l2oneparam[0]]=$l2oneparam[1];
			}
			//取出gotivr 的ivrname
			if (array_key_exists('gotoivr',$l2args_array)) {
				$l2gotoivrres = sendrequest($rpcpbx->ivrmenu_get_menu($l2args_array['gotoivr']),0);
				$l2args_array['gotoivr_name']=$l2gotoivrres['resdata']['ivrname'];
			}
			//取出actpoint的level
			if (array_key_exists('actpoint',$l2args_array) && $l2args_array['actpoint'] != '') {
				$l2actres = sendrequest($rpcpbx->ivrmenu_list_action($l2args_array['gotoivr']),0);
				$l2level=0;
				foreach ($l2actres['actions'] as $l2eachact) {
					if ($l2eachact['id'] == $l2args_array['actpoint']) {
						$l2args_array['actpoint_level']=$l2level;
						break;
					}
					$level++;
				}
			}
			//设置args的array
			$l2each['args_array']=$l2args_array;

			//当前这条动作所在的ivr中的级别编号
			$l2each['proirety_aslevel']=$l2proirety_aslevel;

			array_push($l2allactions,$l2each);
			$l2proirety_aslevel++;
		}
		$rpcres['ivruserinputs'][$key]['gotoivrnumber_ivractions_array']=$l2allactions;//取得所有二级的actions

		//取出当前选择的L2 IVRACTIONS
		if ($value['gotoivractid'] != '') {
			$level=0;
			foreach ($actres['actions'] as $eachact) {
				if ($eachact['id'] == $value['gotoivractid']) {
					$rpcres['ivruserinputs'][$key]['gotoivractid_level']=$level;
					break;
				}
				$level++;
			}
		}

		//取得二级的IVROPT
		$l2ivroptres = sendrequest($rpcpbx->ivrmenu_list_ivruserinput($value['gotoivrnumber'],0),0);
		$rpcres['ivruserinputs'][$key]['gotoivrnumber_ivropt_array']=$l2ivroptres['ivruserinputs'];//取得所有二级的actions

	}
	//列表
	$smarty->assign("ivropt_array",$rpcres['ivruserinputs']);



	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivr_treeview.tpl');
exit;
}

function func_ivr_optmenu() {
	global $smarty;
	global $rpcpbx;

	//取得
	$rpcres = sendrequest($rpcpbx->ivrmenu_get_menu($_REQUEST['ivrnumber']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(113,$rpcres['response']['message'],null,null);

	$smarty->assign("ivrmenu",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivr_optmenu.tpl');
exit;
}

function func_ivrmenu_add() {
	global $smarty;
	global $rpcpbx;

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivrmenu_add.tpl');
exit;
}

function do_ivrmenu_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;
	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['ivrname']) == "")
		error_popbox(211,null,null,null,null,'submit_failed');

	//检测这个号码是否已经使用过了
	$rpcres = sendrequest($rpcpbx->localnumber_get($_REQUEST['ivrnumber']),1);
	if ($rpcres['resdata'])
		error_popbox(212,null,null,null,null,'submit_failed');

	$insert['ivrnumber'] = $_REQUEST['ivrnumber'];
	$insert['ivrname'] = $_REQUEST['ivrname'];
	$insert['description'] = $_REQUEST['description'];

	//创建IVR菜单
	$rpcres = sendrequest($rpcpbx->ivrmenu_add_menu($insert),1);

	//完成
	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivr_main&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');

exit;
}


function func_ivrmenu_edit() {
	global $smarty;
	global $rpcpbx;

	//取得
	$rpcres = sendrequest($rpcpbx->ivrmenu_get_menu($_REQUEST['ivrnumber']),0);

	//指定不存在
	if (!$rpcres['resdata'])
		error_page(113,$rpcres['response']['message'],null,null);

	$smarty->assign("ivrmenu",$rpcres['resdata']);

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivrmenu_edit.tpl');
exit;
}

function do_ivrmenu_edit() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['ivrname']) == "")
		error_popbox(211,null,null,null,null,'submit_failed');

//	$insert['ivrnumber'] = $_REQUEST['ivrnumber'];
	$insert['ivrname'] = $_REQUEST['ivrname'];
	$insert['description'] = $_REQUEST['description'];

	//创建
	$rpcres = sendrequest($rpcpbx->ivrmenu_edit_menu($_REQUEST['ivrnumber'],$insert),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivr_main&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');

exit;
}

function do_ivrmenu_delete() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->ivrmenu_delete_menu($_REQUEST['ivrnumber']),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php','submit_successfuly');
exit;
}

function page_ivraction_list() {
	global $smarty;
	global $rpcpbx;

	//列表显示IVR
	$rpcres = sendrequest($rpcpbx->ivrmenu_list_action($_REQUEST['ivrnumber']),0);

	//重新排列
	$allrule = array();
	$proirety_aslevel = 1;
	foreach ($rpcres['actions'] as $each) {

		//设置参数表
		$args_array=array();
		foreach (preg_split("/\&/",$each['args']) as $eachline) {
			if ($eachline == '')
				continue;
			$oneparam = preg_split("/\=/",$eachline);
			$args_array[$oneparam[0]]=$oneparam[1];
		}
		//取出gotivr 的ivrname
		if (array_key_exists('gotoivr',$args_array)) {
			$gotoivrres = sendrequest($rpcpbx->ivrmenu_get_menu($args_array['gotoivr']),0);
			$args_array['gotoivr_name']=$gotoivrres['resdata']['ivrname'];
		}
		$each['args_array']=$args_array;

		$each['proirety_aslevel']=$proirety_aslevel;
		array_push($allrule,$each);
		$proirety_aslevel++;
	}

	//总量
	$smarty->assign("maxcount",count($rpcres['actions']));
	//列表
	$smarty->assign("table_array",$allrule);


	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_ivraction_list.tpl');
exit;
}

function func_ivraction_add() {
	global $smarty;
	global $rpcpbx;

	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivraction_add.tpl');
exit;
}

function func_ivraction_add_step2() {
	global $smarty;
	global $rpcpbx;

	//不填绝对不行的
	if (trim($_REQUEST['actmode']) == "" || preg_match("/[^0-9]/",$_REQUEST['actmode']))
		error_page(220,$rpcres['response']['message'],null,null);

	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	$smarty->assign("actmode",$_REQUEST['actmode']);

	if ($_REQUEST['actmode'] == '10') {//<option value="10">播放语音</option>

		//取得数据
		$rpcres = sendrequest($rpcpbx->base_dbquery("select DISTINCT(folder) from voicefiles where label = 'sound'"),0);
		$smarty->assign("folder_array",$rpcres['result_array']);

		$folder_file_array = array();
		foreach ($rpcres['result_array'] as $key => $value) {
			//取得数据
			$fileres = sendrequest($rpcpbx->base_dbquery("select * from voicefiles where folder = '".$value['folder']."' and label = 'sound'"),0);
			
			$folder_file_array[$key]=$fileres['result_array'];
		}

		$smarty->assign("folder_file_array",$folder_file_array);


	} if ($_REQUEST['actmode'] == '11') {//<option value="11">发起录音</option>
	} if ($_REQUEST['actmode'] == '12') {//<option value="12">播放录音</option>
	} if ($_REQUEST['actmode'] == '20') {//<option value="20">录制0-9字符</option>
	} if ($_REQUEST['actmode'] == '21') {//<option value="21">读出0-9字符</option>
	} if ($_REQUEST['actmode'] == '22') {//<option value="22">数字方式读出</option>
	} if ($_REQUEST['actmode'] == '30') {//<option value="30">读出日期时间</option>
	} if ($_REQUEST['actmode'] == '31') {//<option value="31">检测日期</option>

		
		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
			
			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);


	} if ($_REQUEST['actmode'] == '40') {//<option value="40">主叫变换</option>
	} if ($_REQUEST['actmode'] == '41') {//<option value="41">拨打号码</option>

		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);

			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);

	} if ($_REQUEST['actmode'] == '42') {//<option value="42">跳转到语音信箱</option>
	} if ($_REQUEST['actmode'] == '43') {//<option value="43">跳转到IVR菜单</option>


		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);

			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);

	} if ($_REQUEST['actmode'] == '44') {//<option value="44">WEB交互接口</option>


		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);

			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);


	} if ($_REQUEST['actmode'] == '80') {//<option value="80">等待几秒</option>
	} if ($_REQUEST['actmode'] == '81') {//<option value="81">播放音调</option>
	} if ($_REQUEST['actmode'] == '99') {//<option value="99">挂机</option>
	}

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/ivraction/func_ivraction_add_'.$_REQUEST['actmode'].'.tpl');

exit;
}

function do_ivraction_add() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['actmode']) == "" || preg_match("/[^0-9]/",$_REQUEST['actmode']))
		error_popbox(221,null,null,null,null,'submit_failed');

	if ($_REQUEST['actmode'] == '10') {//<option value="10">播放语音</option>
		$insert['folder'] = $_REQUEST['folder'];
		$insert['filename'] = $_REQUEST['filename'];
		$insert['interruptible'] = $_REQUEST['interruptible'];



	} if ($_REQUEST['actmode'] == '11') {//<option value="11">发起录音</option>
		if (trim($_REQUEST['recordvarname']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['recordvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		$insert['recordvarname'] = $_REQUEST['recordvarname'];



	} if ($_REQUEST['actmode'] == '12') {//<option value="12">播放录音</option>
		if (trim($_REQUEST['playbackvarname']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
	


	} if ($_REQUEST['actmode'] == '20') {//<option value="20">录制0-9字符</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['receivevarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['maxdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['beepbeforereceive'] = $_REQUEST['beepbeforereceive'];
		$insert['addbeforeuserinput'] = $_REQUEST['addbeforeuserinput'];
		$insert['maxdigits'] = $_REQUEST['maxdigits'];
		$insert['receivevarname'] = $_REQUEST['receivevarname'];



	} if ($_REQUEST['actmode'] == '21') {//<option value="21">读出0-9字符</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['saydigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
		$insert['saydigits'] = $_REQUEST['saydigits'];



	} if ($_REQUEST['actmode'] == '22') {//<option value="22">数字方式读出</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['saydigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
		$insert['saydigits'] = $_REQUEST['saydigits'];



	} if ($_REQUEST['actmode'] == '30') {//<option value="30">读出日期时间</option>
		$insert['saydatetime'] = $_REQUEST['saydatetime'];
		$insert['saydatefromvar'] = $_REQUEST['saydatefromvar'];
		$insert['saytimefromvar'] = $_REQUEST['saytimefromvar'];
		$insert['saydatestring'] = $_REQUEST['saydatestring'];
		$insert['saytimestring'] = $_REQUEST['saytimestring'];


	} if ($_REQUEST['actmode'] == '31') {//<option value="31">检测日期</option>
		$insert['from_hour'] = $_REQUEST['from_hour'];
		$insert['from_min'] = $_REQUEST['from_min'];
		$insert['to_hour'] = $_REQUEST['to_hour'];
		$insert['to_min'] = $_REQUEST['to_min'];
		$insert['timeall'] = $_REQUEST['timeall'];
		$insert['from_week'] = $_REQUEST['from_week'];
		$insert['to_week'] = $_REQUEST['to_week'];
		$insert['weekall'] = $_REQUEST['weekall'];
		$insert['from_day'] = $_REQUEST['from_day'];
		$insert['to_day'] = $_REQUEST['to_day'];
		$insert['dayall'] = $_REQUEST['dayall'];
		$insert['from_month'] = $_REQUEST['from_month'];
		$insert['to_month'] = $_REQUEST['to_month'];
		$insert['monthall'] = $_REQUEST['monthall'];
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];


	} if ($_REQUEST['actmode'] == '40') {//<option value="40">主叫变换</option>
		if (trim($_REQUEST['altercallerid']) == "" || preg_match("/[^0-9]/",$_REQUEST['altercallerid']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['modify'] = $_REQUEST['modify'];
		$insert['altercallerid'] = $_REQUEST['altercallerid'];


	} if ($_REQUEST['actmode'] == '41') {//<option value="41">拨打号码</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['dialvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['dialdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['typeof'] = $_REQUEST['typeof'];
		$insert['dialvarname'] = $_REQUEST['dialvarname'];
		$insert['dialdigits'] = $_REQUEST['dialdigits'];
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];
		$insert['playbackinvalid'] = $_REQUEST['playbackinvalid'];

	} if ($_REQUEST['actmode'] == '42') {//<option value="42">跳转到语音信箱</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['dialvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['dialdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['dialvarname'] = $_REQUEST['dialvarname'];
		$insert['dialdigits'] = $_REQUEST['dialdigits'];
		$insert['typeof'] = $_REQUEST['typeof'];

	} if ($_REQUEST['actmode'] == '43') {//<option value="43">跳转到IVR菜单</option>
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];

	} if ($_REQUEST['actmode'] == '44') {//<option value="44">WEB交互接口</option>
		$insert['urlvar'] = urlencode($_REQUEST['urlvar']);
		$insert['urlargs'] = urlencode($_REQUEST['urlargs']);
		$insert['urltimeout'] = urlencode($_REQUEST['urltimeout']);
		$insert['done_gotoivr'] = $_REQUEST['done_gotoivr'];
		$insert['done_actpoint'] = $_REQUEST['done_actpoint'];
		$insert['failed_gotoivr'] = $_REQUEST['failed_gotoivr'];
		$insert['failed_actpoint'] = $_REQUEST['failed_actpoint'];
		$insert['other_gotoivr'] = $_REQUEST['other_gotoivr'];
		$insert['other_actpoint'] = $_REQUEST['other_actpoint'];

	} if ($_REQUEST['actmode'] == '45') {//<option value="45">AGI扩展接口</option>
		$insert['agi'] = urlencode($_REQUEST['agi']);

	} if ($_REQUEST['actmode'] == '80') {//<option value="80">等待几秒</option>
		if (trim($_REQUEST['wait']) == "" || preg_match("/[^0-9]/",$_REQUEST['wait']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['wait'] = $_REQUEST['wait'];
		$insert['interruptible'] = $_REQUEST['interruptible'];


	} if ($_REQUEST['actmode'] == '81') {//<option value="81">播放音调</option>
		if (trim($_REQUEST['sec']) == "" || preg_match("/[^0-9]/",$_REQUEST['sec']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['sec'] = $_REQUEST['sec'];
		$insert['playtone'] = $_REQUEST['playtone'];

	} if ($_REQUEST['actmode'] == '99') {//<option value="99">挂机</option>
	}

	//创建IVR菜单
	$rpcres = sendrequest($rpcpbx->ivrmenu_add_action(mktime(),$_REQUEST['ivrnumber'],$_REQUEST['actmode'],$insert),1);

	//完成
	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivraction_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
exit;
}

function do_ivraction_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(221,null,null,null,null,'submit_failed');

	$rpcres = sendrequest($rpcpbx->ivrmenu_delete_action($_REQUEST['id']),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivraction_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
exit;
}

function func_ivraction_edit()
{
	global $smarty;
	global $rpcpbx;

	//不填绝对不行的
	if (trim($_REQUEST['actmode']) == "" || preg_match("/[^0-9]/",$_REQUEST['actmode']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(221,null,null,null,null,'submit_failed');

	$smarty->assign("actmode",$_REQUEST['actmode']);
	$smarty->assign("id",$_REQUEST['id']);
	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	$smarty->assign("return",$_REQUEST['return']);

	//取出来数据
	$actres = sendrequest($rpcpbx->ivrmenu_get_action($_REQUEST['id']),0);

	//设置参数表
	$args_array=array();
	foreach (preg_split("/\&/",$actres['resdata']['args']) as $eachline) {
		if ($eachline == '')
			continue;
		$oneparam = preg_split("/\=/",$eachline);
		$args_array[$oneparam[0]]=$oneparam[1];
	}
	$smarty->assign("resdata",$actres['resdata']);
	$smarty->assign("args_array",$args_array);


	//不同类型
	if ($_REQUEST['actmode'] == '10') {//<option value="10">播放语音</option>

		//取得数据
		$rpcres = sendrequest($rpcpbx->base_dbquery("select DISTINCT(folder) from voicefiles where label = 'sound'"),0);

		$smarty->assign("folder_array",$rpcres['result_array']);

		$folder_file_array = array();
		foreach ($rpcres['result_array'] as $key => $value) {
			//取得数据
			$fileres = sendrequest($rpcpbx->base_dbquery("select * from voicefiles where folder = '".$value['folder']."' and label = 'sound'"),0);
			
			$folder_file_array[$key]=$fileres['result_array'];
		}

		$smarty->assign("folder_file_array",$folder_file_array);


	} if ($_REQUEST['actmode'] == '11') {//<option value="11">发起录音</option>
	} if ($_REQUEST['actmode'] == '12') {//<option value="12">播放录音</option>
	} if ($_REQUEST['actmode'] == '20') {//<option value="20">录制0-9字符</option>
	} if ($_REQUEST['actmode'] == '21') {//<option value="21">读出0-9字符</option>
	} if ($_REQUEST['actmode'] == '22') {//<option value="22">数字方式读出</option>
	} if ($_REQUEST['actmode'] == '30') {//<option value="30">读出日期时间</option>
	} if ($_REQUEST['actmode'] == '31') {//<option value="31">检测日期</option>

		
		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
			
			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);


	} if ($_REQUEST['actmode'] == '40') {//<option value="40">主叫变换</option>
	} if ($_REQUEST['actmode'] == '41') {//<option value="41">拨打号码</option>

		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		
			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);

	} if ($_REQUEST['actmode'] == '42') {//<option value="42">跳转到语音信箱</option>
	} if ($_REQUEST['actmode'] == '43') {//<option value="43">跳转到IVR菜单</option>


		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		
			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);

	} if ($_REQUEST['actmode'] == '44') {//<option value="44">WEB交互接口</option>


		//取得数据
		$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
		$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

		$ivrmenu_action_array = array();
		foreach ($rpcres['ivrmenu'] as $key => $value) {
			//取得数据
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		
			$ivrmenu_action_array[$key]=$actres['actions'];
		}

		$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);

	} if ($_REQUEST['actmode'] == '80') {//<option value="80">等待几秒</option>
	} if ($_REQUEST['actmode'] == '81') {//<option value="81">播放音调</option>
	} if ($_REQUEST['actmode'] == '99') {//<option value="99">挂机</option>
	}

	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/ivraction/func_ivraction_edit_'.$_REQUEST['actmode'].'.tpl');

exit;
}

function do_ivraction_edit() {
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['actmode']) == "" || preg_match("/[^0-9]/",$_REQUEST['actmode']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(221,null,null,null,null,'submit_failed');

	if ($_REQUEST['actmode'] == '10') {//<option value="10">播放语音</option>
		$insert['folder'] = $_REQUEST['folder'];
		$insert['filename'] = $_REQUEST['filename'];
		$insert['interruptible'] = $_REQUEST['interruptible'];


	} if ($_REQUEST['actmode'] == '11') {//<option value="11">发起录音</option>
		if (trim($_REQUEST['recordvarname']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['recordvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		$insert['recordvarname'] = $_REQUEST['recordvarname'];



	} if ($_REQUEST['actmode'] == '12') {//<option value="12">播放录音</option>
		if (trim($_REQUEST['playbackvarname']) == "" || preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
	


	} if ($_REQUEST['actmode'] == '20') {//<option value="20">录制0-9字符</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['receivevarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['maxdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['beepbeforereceive'] = $_REQUEST['beepbeforereceive'];
		$insert['addbeforeuserinput'] = $_REQUEST['addbeforeuserinput'];
		$insert['maxdigits'] = $_REQUEST['maxdigits'];
		$insert['receivevarname'] = $_REQUEST['receivevarname'];



	} if ($_REQUEST['actmode'] == '21') {//<option value="21">读出0-9字符</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['saydigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
		$insert['saydigits'] = $_REQUEST['saydigits'];



	} if ($_REQUEST['actmode'] == '22') {//<option value="22">数字方式读出</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['playbackvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['saydigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['playbackvarname'] = $_REQUEST['playbackvarname'];
		$insert['saydigits'] = $_REQUEST['saydigits'];



	} if ($_REQUEST['actmode'] == '30') {//<option value="30">读出日期时间</option>
		$insert['saydatetime'] = $_REQUEST['saydatetime'];
		$insert['saydatefromvar'] = $_REQUEST['saydatefromvar'];
		$insert['saytimefromvar'] = $_REQUEST['saytimefromvar'];
		$insert['saydatestring'] = $_REQUEST['saydatestring'];
		$insert['saytimestring'] = $_REQUEST['saytimestring'];


	} if ($_REQUEST['actmode'] == '31') {//<option value="31">检测日期</option>
		$insert['from_hour'] = $_REQUEST['from_hour'];
		$insert['from_min'] = $_REQUEST['from_min'];
		$insert['to_hour'] = $_REQUEST['to_hour'];
		$insert['to_min'] = $_REQUEST['to_min'];
		$insert['timeall'] = $_REQUEST['timeall'];
		$insert['from_week'] = $_REQUEST['from_week'];
		$insert['to_week'] = $_REQUEST['to_week'];
		$insert['weekall'] = $_REQUEST['weekall'];
		$insert['from_day'] = $_REQUEST['from_day'];
		$insert['to_day'] = $_REQUEST['to_day'];
		$insert['dayall'] = $_REQUEST['dayall'];
		$insert['from_month'] = $_REQUEST['from_month'];
		$insert['to_month'] = $_REQUEST['to_month'];
		$insert['monthall'] = $_REQUEST['monthall'];
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];


	} if ($_REQUEST['actmode'] == '40') {//<option value="40">主叫变换</option>
		if (trim($_REQUEST['altercallerid']) == "" || preg_match("/[^0-9]/",$_REQUEST['altercallerid']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['modify'] = $_REQUEST['modify'];
		$insert['altercallerid'] = $_REQUEST['altercallerid'];


	} if ($_REQUEST['actmode'] == '41') {//<option value="41">拨打号码</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['dialvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['dialdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['typeof'] = $_REQUEST['typeof'];
		$insert['dialvarname'] = $_REQUEST['dialvarname'];
		$insert['dialdigits'] = $_REQUEST['dialdigits'];
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];
		$insert['playbackinvalid'] = $_REQUEST['playbackinvalid'];


	} if ($_REQUEST['actmode'] == '42') {//<option value="42">跳转到语音信箱</option>
		if (preg_match("/[^a-zA-Z0-9]/",$_REQUEST['dialvarname']))
			error_popbox(222,null,null,null,null,'submit_failed');
		if (preg_match("/[^0-9]/",$_REQUEST['dialdigits']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['dialvarname'] = $_REQUEST['dialvarname'];
		$insert['dialdigits'] = $_REQUEST['dialdigits'];
		$insert['typeof'] = $_REQUEST['typeof'];

	} if ($_REQUEST['actmode'] == '43') {//<option value="43">跳转到IVR菜单</option>
		$insert['gotoivr'] = $_REQUEST['gotoivr'];
		$insert['actpoint'] = $_REQUEST['actpoint'];

	} if ($_REQUEST['actmode'] == '44') {//<option value="44">WEB交互接口</option>
		$insert['urlvar'] = urlencode($_REQUEST['urlvar']);
		$insert['urlargs'] = urlencode($_REQUEST['urlargs']);
		$insert['urltimeout'] = urlencode($_REQUEST['urltimeout']);
		$insert['done_gotoivr'] = $_REQUEST['done_gotoivr'];
		$insert['done_actpoint'] = $_REQUEST['done_actpoint'];
		$insert['failed_gotoivr'] = $_REQUEST['failed_gotoivr'];
		$insert['failed_actpoint'] = $_REQUEST['failed_actpoint'];
		$insert['other_gotoivr'] = $_REQUEST['other_gotoivr'];
		$insert['other_actpoint'] = $_REQUEST['other_actpoint'];

	} if ($_REQUEST['actmode'] == '45') {//<option value="45">AGI扩展接口</option>
		$insert['agi'] = urlencode($_REQUEST['agi']);

	} if ($_REQUEST['actmode'] == '80') {//<option value="80">等待几秒</option>
		if (trim($_REQUEST['wait']) == "" || preg_match("/[^0-9]/",$_REQUEST['wait']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['wait'] = $_REQUEST['wait'];
		$insert['interruptible'] = $_REQUEST['interruptible'];


	} if ($_REQUEST['actmode'] == '81') {//<option value="81">播放音调</option>
		if (trim($_REQUEST['sec']) == "" || preg_match("/[^0-9]/",$_REQUEST['sec']))
			error_popbox(223,null,null,null,null,'submit_failed');
		$insert['sec'] = $_REQUEST['sec'];
		$insert['playtone'] = $_REQUEST['playtone'];

	} if ($_REQUEST['actmode'] == '99') {//<option value="99">挂机</option>
	}

	//创建IVR菜单
	$rpcres = sendrequest($rpcpbx->ivrmenu_edit_action($_REQUEST['id'],$insert),1);

	//完成
	if ($_REQUEST['return']=='treeview') {
		error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivr_main&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
	} else {
		error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivraction_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
	}
exit;
}

function do_ivraction_recall()
{
	global $rpcpbx;
	global $smarty;
	global $friconf;

	//剔除调整顺序
	$source_all=array();
	foreach ($_REQUEST['router'] as $value) {
		if (trim($value) == "")
			continue;
		array_push($source_all,$value);
	}
	$routerproi=array();
	$maxdigit = 1;
	foreach ($source_all as $value) {
		$routerproi[$maxdigit]=$value;
		$maxdigit++;
	}
	//根据新顺序调整优先关系
	foreach ($routerproi as $proi => $actid) {
		$rpcres = sendrequest($rpcpbx->ivrmenu_recall_action($actid,$proi),1);
	}

	//完成
	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivraction_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
exit;
}

function page_ivruserinput_list() {
	global $smarty;
	global $rpcpbx;

	//取得数据--------------语音文件
	$rpcres = sendrequest($rpcpbx->base_dbquery("select DISTINCT(folder) from voicefiles where label = 'sound'"),0);
	$smarty->assign("folder_array",$rpcres['result_array']);

	$folder_file_array = array();
	foreach ($rpcres['result_array'] as $key => $value) {
		//取得数据
		$fileres = sendrequest($rpcpbx->base_dbquery("select * from voicefiles where folder = '".$value['folder']."' and label = 'sound'"),0);
		
		$folder_file_array[$key]=$fileres['result_array'];
	}
	$smarty->assign("folder_file_array",$folder_file_array);


	//取得动作数据------------IVR菜单选择
	$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
	$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

	$ivrmenu_action_array = array();
	foreach ($rpcres['ivrmenu'] as $key => $value) {
		//取得数据
		$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		
		$ivrmenu_action_array[$key]=$actres['actions'];
	}

	$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);


	//读出general设置
	$rpcres = sendrequest($rpcpbx->ivrmenu_list_ivruserinput($_REQUEST['ivrnumber'],1),0);
	$invalid_optmenu=array();
	$timeout_optmenu=array();
	$retry_optmenu=array();
	foreach ($rpcres['ivruserinputs'] as $value) {
		$general_args_array=array();
		foreach (preg_split("/\&/",$value['general_args']) as $eachline) {
			if ($eachline == '')
				continue;
			$oneparam = preg_split("/\=/",$eachline);
			$general_args_array[$oneparam[0]]=$oneparam[1];
		}
		if ($value['general_type'] == 'invalid') {
			$invalid_optmenu=$value;
			$invalid_optmenu['args_as_ref']=$general_args_array;
		} elseif ($value['general_type'] == 'timeout') {
			$timeout_optmenu=$value;
			$timeout_optmenu['args_as_ref']=$general_args_array;
		} elseif ($value['general_type'] == 'retry') {
			$retry_optmenu=$value;
			$retry_optmenu['args_as_ref']=$general_args_array;
		}
	}
	$smarty->assign("invalid_optmenu",$invalid_optmenu);
	$smarty->assign("timeout_optmenu",$timeout_optmenu);
	$smarty->assign("retry_optmenu",$retry_optmenu);


	//列表显示IVROPTIONS
	$rpcres = sendrequest($rpcpbx->ivrmenu_list_ivruserinput($_REQUEST['ivrnumber'],0),0);
	foreach ($rpcres['ivruserinputs'] as $key=>$value) {

		$ivrres = sendrequest($rpcpbx->ivrmenu_get_menu($value['gotoivrnumber']),0);
		$rpcres['ivruserinputs'][$key]['gotoivrnumber_ref']=$ivrres['resdata'];

		//find out act
		if ($value['gotoivractid'] != '') {
			$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['gotoivrnumber']),0);
			$level=0;
			foreach ($actres['actions'] as $eachact) {
				if ($eachact['id'] == $value['gotoivractid']) {
					$rpcres['ivruserinputs'][$key]['gotoivractid_ref']=$eachact;
					$rpcres['ivruserinputs'][$key]['gotoivractid_level']=$level;
					break;
				}
				$level++;
			}
		}
	}


	//总量
	$smarty->assign("maxcount",count($rpcres['ivruserinputs']));
	//列表
	$smarty->assign("table_array",$rpcres['ivruserinputs']);


	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);
	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/page_ivruserinput_list.tpl');

exit;
}

function do_ivruserinput_generalset() 
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');

	if ($_REQUEST['general_type'] == 'invalid') {
		$insert['general_args']= 'folder='.$_REQUEST['folder'].'&filename='.$_REQUEST['filename'];
		$insert['gotoivrnumber'] = $_REQUEST['gotoivrnumber'];
		$insert['gotoivractid'] = $_REQUEST['gotoivractid'];
		$rpcres = sendrequest($rpcpbx->ivrmenu_edit_ivruserinput($_REQUEST['id'],$insert),1);

	} elseif ($_REQUEST['general_type'] == 'timeout') {
		$insert['general_args']= 'folder='.$_REQUEST['folder'].'&filename='.$_REQUEST['filename'].'&timeout='.$_REQUEST['timeout'];
		$insert['gotoivrnumber'] = $_REQUEST['gotoivrnumber'];
		$insert['gotoivractid'] = $_REQUEST['gotoivractid'];
		$rpcres = sendrequest($rpcpbx->ivrmenu_edit_ivruserinput($_REQUEST['id'],$insert),1);

	} elseif ($_REQUEST['general_type'] == 'retry') {
		$insert['general_args']= 'numberofretry='.$_REQUEST['numberofretry'];
		$insert['gotoivrnumber'] = $_REQUEST['gotoivrnumber'];
		$insert['gotoivractid'] = $_REQUEST['gotoivractid'];
		$rpcres = sendrequest($rpcpbx->ivrmenu_edit_ivruserinput($_REQUEST['id'],$insert),1);
	}

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');

exit;
}

function func_ivruserinput_add()
{
	global $smarty;
	global $rpcpbx;

	//不填绝对不行的
	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);

	//取得数据
	$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
	$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);

	$ivrmenu_action_array = array();
	foreach ($rpcres['ivrmenu'] as $key => $value) {
		//取得数据
		$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		
		$ivrmenu_action_array[$key]=$actres['actions'];
	}

	$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);


	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivruserinput_add.tpl');

exit;
}

function do_ivruserinput_add()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');

	$insert['input'] = $_REQUEST['input'];
	$insert['gotoivrnumber'] = $_REQUEST['gotoivrnumber'];
	$insert['gotoivractid'] = $_REQUEST['gotoivractid'];
	$rpcres = sendrequest($rpcpbx->ivrmenu_add_ivruserinput($_REQUEST['ivrnumber'],$insert),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
exit;
}

function do_ivruserinput_delete()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	//不填绝对不行的
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(210,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(210,null,null,null,null,'submit_failed');

	sendrequest($rpcpbx->ivrmenu_delete_ivruserinput($_REQUEST['id']),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');
exit;
}

function func_ivruserinput_edit()
{
	global $smarty;
	global $rpcpbx;

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(221,null,null,null,null,'submit_failed');
	if (trim($_REQUEST['ivrnumber']) == "" || preg_match("/[^0-9]/",$_REQUEST['ivrnumber']))
		error_popbox(221,null,null,null,null,'submit_failed');

	$smarty->assign("id",$_REQUEST['id']);
	$smarty->assign("ivrnumber",$_REQUEST['ivrnumber']);

	//取出来数据
	$rpcres = sendrequest($rpcpbx->ivrmenu_get_ivruserinput($_REQUEST['id']),0);
	$smarty->assign("userinput",$rpcres['resdata']);


	//取得数据产生IVR菜单表
	$rpcres = sendrequest($rpcpbx->ivrmenu_list(),0);
	$smarty->assign("ivrmenu_array",$rpcres['ivrmenu']);
	$ivrmenu_action_array = array();
	foreach ($rpcres['ivrmenu'] as $key => $value) {
		//取得数据
		$actres = sendrequest($rpcpbx->ivrmenu_list_action($value['ivrnumber']),0);
		$ivrmenu_action_array[$key]=$actres['actions'];
	}
	$smarty->assign("ivrmenu_action_array",$ivrmenu_action_array);



	//基本
	$smarty->assign("res_admin",$_SESSION['res_admin']);
	smarty_output('cpanel/func_ivruserinput_edit.tpl');
}

function do_ivruserinput_edit()
{
	global $smarty;
	global $rpcpbx;
	global $friconf;

	$insert = array();

	//不填绝对不行的
	if (trim($_REQUEST['id']) == "" || preg_match("/[^0-9]/",$_REQUEST['id']))
		error_popbox(210,null,null,null,null,'submit_failed');

	$insert['input'] = $_REQUEST['input'];
	$insert['gotoivrnumber'] = $_REQUEST['gotoivrnumber'];
	$insert['gotoivractid'] = $_REQUEST['gotoivractid'];
	$rpcres = sendrequest($rpcpbx->ivrmenu_edit_ivruserinput($_REQUEST['id'],$insert),1);

	error_popbox(null,null,null,null,'acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber='.$_REQUEST['ivrnumber'],'submit_successfuly');

exit;
}
?>
