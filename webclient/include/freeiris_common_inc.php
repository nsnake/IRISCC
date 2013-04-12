<?php
/*
	Freeiris2 -- An Opensource telephony project.
	Copyright (C) 2005 - 2008, Sun bing.
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
	this file :
	freeiris2 common include file

    $Id$
*/


/*
	error output
*/

require "config_inc.php";


function error_page($errorcode,$rawerrstr,$line=null,$function=null)
{
	global $smarty;

	if ($line==null || $function==null) {
		$debug = debug_backtrace();
		$line = $debug[0]['line'];
		$function = $debug[0]['function'];
	}

	$smarty->assign("errorcode",$errorcode);
	$smarty->assign("line",$line);
	$smarty->assign("function",$function);
	$smarty->assign("rawerrstr",$rawerrstr);
	$smarty->assign("filename",$_SERVER['PHP_SELF']);

	smarty_output('error_page.tpl');
	exit;
}

function error_popbox($errorcode,$rawerrstr,$line=null,$function=null,$location,$type)
{
	global $smarty;

	if ($line==null || $function==null) {
		$debug = debug_backtrace();
		$line = $debug[0]['line'];
		$function = $debug[0]['function'];
	}

	$smarty->assign('errorcode',preg_replace("/\'|\n/","",$errorcode));
	$smarty->assign('rawerrstr',$rawerrstr);
	$smarty->assign('line',$line);
	$smarty->assign("function",$function);
	$smarty->assign('location',$location);
	$smarty->assign('type',$type);
	$smarty->assign("filename",$_SERVER['PHP_SELF']);

	smarty_output('error_popbox.tpl');
	exit;
}
/*
    发出RPC远程请求
	$rpcreturn为发送数据,$errortype为错误方式,0='exception_error',1='warnning_message_iframe'
*/
function sendrequest($rpcres,$errortype)
{
	$debug = debug_backtrace();
	$line = $debug[0]['line'];
	$function = $debug[0]['function'];

	if ($errortype == '0') {

		if (is_a($rpcres, "PHPRPC_Error"))
			error_page(101,$rpcres->Message,$line,$function);
		if ($rpcres['response']['statcode'] != 200)
			error_page($rpcres['response']['msgcode'],$rpcres['response']['message'],$line,$function);

	} elseif ($errortype == '1') {

		if (is_a($rpcres, "PHPRPC_Error"))
			error_popbox(101,$rpcres->Message,$line,$function,null,'submit_failed');
		if ($rpcres['response']['statcode'] != 200)
			error_popbox($rpcres['response']['msgcode'],$rpcres['response']['message'],$line,$function,null,'submit_failed');

	}

return($rpcres);
}

/*
	call this function to smarty output
*/
function smarty_output($template_file)
{
	global $smarty;
	global $friconf;
	global $rpcpbx;
	// add by anjing<yuzegao@163.com>
	global $_SESSION;

	//取得systemID信息
	if (isset($_SESSION["admin"]) || $_SESSION["admin"] == true) {
//		$rpcres=sendrequest($rpcpbx->base_registration_get(),0);
//		$smarty->assign("registration",$rpcres['registration']);
		$smarty->assign("menutable",$friconf['menutable']);
	}

	header("Cache-Control: no-cache\n");
	$smarty->assign("title",$friconf['title']);
	$smarty->display($template_file);
	return(true);
}


/*
	初始化数据
*/
function web_initialization()
{
	global $friconf;
	global $smarty;
////	global $rpcsysadm;
////	global $rpcpbx;

	// template enginnger
	$smarty = new Smarty;
	$smarty->template_dir = '../'.$smarty->template_dir.'/'.$friconf['language'];
	$smarty->compile_dir  = '../'.$smarty->compile_dir;
	$smarty->left_delimiter = '<%';
	$smarty->right_delimiter= '%>';

	// rpc enginnger
//	$rpcsysadm = new HproseHttpClient($friconf['friextra_urlbase'].'/rpcsysadm.php');
//	$rpcpbx = new HproseHttpClient($friconf['friextra_urlbase'].'/rpcpbx.php');

	return(true);
}

/**
*    由于此函数返回的是一个数组，因此要配合join函数来显示字符串:
*    join('',subString_UTF8($str, $start, $lenth));
*    在页面显示的时候还可以在此语句后面连一个"..."
* 
*    注:
*    编码 第一字节 第二字节
*    gb2312 0xa1-0xf7 0xa1-0xfe
*    gbk 0x81-0xfe 0x81-0xfe 0x40-0x7e
*    big5 0xa1-0xf7 0x81-0xfe 0x40-0x7e
*    
* 中文字符截取且支持utf-8
* 
*     @Param: char $str,
*    @Param: intege $start,
*    @Param: intege $start,
*    @return: $s.
*    time: Tue Aug 07 18:18:27 CST 2007
*    lastime: Tue Aug 07 18:18:27 CST 2007
*/
function subString_UTF8($str, $start, $lenth)
{
	$len = strlen($str);
	$r = array();
	$n = 0;
	$m = 0;
	for($i = 0; $i < $len; $i++) {
		$x = substr($str, $i, 1);
		$a = base_convert(ord($x), 10, 2);
		$a = substr('00000000'.$a, -8);
		if ($n < $start){
			if (substr($a, 0, 1) == 0) {
			}elseif (substr($a, 0, 3) == 110) {
				$i += 1;
			}elseif (substr($a, 0, 4) == 1110) {
				$i += 2;
			}
			$n++;
		}else{
			if (substr($a, 0, 1) == 0) {
				$r[ ] = substr($str, $i, 1);
			}elseif (substr($a, 0, 3) == 110) {
				$r[ ] = substr($str, $i, 2);
				$i += 1;
			}elseif (substr($a, 0, 4) == 1110) {
				$r[ ] = substr($str, $i, 3);
				$i += 2;
			}else{
				$r[ ] = '';
			}
			if (++$m >= $lenth){
				break;
			}
		}
	}
	return $r;
}

?>