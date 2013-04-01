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

FRIPAPER

	@author Sun bing <hoowa.sun@gmail.com>
	@version $Id: demo1.php 88 2009-03-20 02:07:44Z hoowa $
	@package Freeiris2 Extra Interface
	@filename test.php
	@description

	<h3>你已经在测试了</h3>

	当前为一个说明,<key>关键字</key>和<b>粗体</b><b>斜体</b><u>下线</u>
	已经被支持了.官方网站<link src="http://www.freeiris.org">fri2</link>

ENDPAPER
*/
/* 
	this file :
	rpc pbx -- Freeiris2 Extra Interface

    $Id: demo1.php 88 2009-03-20 02:07:44Z hoowa $
*/
require_once("./include/friextra_common_inc.php");

/*
	注册RPC函数
*/
$server = new PHPRPC_Server();
// 分机
$server->add('extension_list');		#分机列表
$server->add('extension_get');		#查看分组
$server->add('extension_add_sip');	#新增SIP分机
$server->add('extension_edit_sip');	#编辑分机
$server->add('extension_delete_sip');	#删除分机

// 分组(未开放功能暂时可以使用原形接口)
$server->add('extengroup_list');	#分组列表
$server->add('extengroup_get');		#查看分组
$server->add('extengroup_add');		#新增分组
$server->add('extengroup_edit');	#编辑分组
$server->add('extengroup_delete');	#删除分组

// 原形接口
$server->add('dbquery');	#数据库函数原形接口
$server->add('client_login');	#身份验证基本函数

$server->start();

/*
FRIPAPER

	@name extension_list

	@synopsis
	
	$fri2struct extension_list (string $order,int $limitfrom,int $limitoffset)

	@param $order

		 排序方式设置

		@item 'order by xxxx'
		
			其中xxx部分表示为数据库中extension表的字段
		@item 'XXXX by xxxx'
		
			其中xxx部分表示为数据库中extension表的字段

	@param $limitfrom

		 数据开始的位置

	@param $limitoffset

		 数据数量

	@return $fri2struct
	
	freeiris2的字符串数组标准返回数据格式

		@item array 'extensions'

			自定义部分,数据结构体

			<code>
extensions = array(
	'hello'=>'abc',		#XX数据
);
			</code>

			<li>hello 名称</li>
			<li>xxx 密码</li>

ENDPAPER
*/
function extension_list($order,$limitfrom,$limitoffset)
{
	//验证请求者权限
	session_start();
	if (!isset($_SESSION["client_authorized"]) || $_SESSION["client_authorized"] == false)
		return(rpcreturn(401,'authorization failed',102,null));

}
/*
FRIPAPER

	@name test2

	@synopsis salkfd
	
	$fri2struct test2

ENDPAPER
*/
function test2() 
{
}
