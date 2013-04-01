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
FRIPAPER

    $Id$
	@author <link src="mailto:hoowa.sun AT gmail DOT com">Sun bing</link>
	@version 1.0
	@package rpcpbx
	@description
	&nbsp;&nbsp;RPC服务的主文件,由这个文件实现RPC服务接口,以及提供了基本的函数功能.<br>
	其他文件由此文件动态加载,其他文件的文件格式需要遵守 rpcpbx_<i><b>xxx</b></i>.php 这种格式

ENDPAPER
*/


/*
	载入基本函数库
*/
require_once("./include/friextra_common_inc.php");

$rpc_name      = 'rpcpbx';
$rpc_version   = '1.2';
$freeiris_conf = null;
//$asterisk_conf=null;
$manager_conf  = null;
$dbcon         = null;

//PHP使用最大允许内存
ini_set("memory_limit","128M");

initrpc();


/*
	生成RPC对象
*/
$server = new PHPRPC_Server();


/*
	注册开放式RPC基本服务
*/
$server->add('base_clientlogin');// 注册登记


/*
	注册非开放式RPC基本服务
*/
//验证请求者权限
session_start();
if (!isset($_SESSION["client_authorized"]) || $_SESSION["client_authorized"] == false) {


	//身份验证失败,不再发布其他函数.


} else {
	/*
		载入所有服务模块文件
		文件格式rpcpbx_[服务名称].php
	*/
	if ($serviceloader_dir_handle = opendir(getcwd())) {
		while (($servicefile = readdir($serviceloader_dir_handle)) !== false) {
			if (preg_match("/^rpcpbx\_(.+)\.php/",$servicefile)) {
				require($servicefile);
			}
		}
		closedir($serviceloader_dir_handle);
	}

}



/*
    启动RPC SERVICE服务
*/
$server->start();



/*
    基本的RPC服务函数
*/
/*
FRIPAPER

	@name base_clientlogin
	@synopsis
		<code>	
  $retrun = base_clientlogin ($adminid,$passwd)
		</code>
	@param $adminid
		登陆的帐户名称
	@param $passwd
		该帐户密码(MD5加密格式)
	@return $retrun
		这个帐户的信息数据库对象格式,或是fri2标准格式

ENDPAPER
*/
function base_clientlogin($adminid,$passwd)
{
	global $freeiris_conf;
	global $dbcon;

	//判断用户帐户
	$result=mysql_query("select * from admin where adminid = '$adminid' and passwd = '$passwd'",$dbcon);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$queryres = mysql_fetch_array($result);
	mysql_free_result($result);

	//如果不存在
	if (!$queryres) {
		return(rpcreturn(401,'authorization failed',102,null));
	} else {
		//为这个远程呼叫产生session
		session_start();
		$_SESSION["client_authorized"] = true;

		return(rpcreturn(200,null,null,array('res_admin'=>$queryres)));
	}

	return($result);
}

?>
