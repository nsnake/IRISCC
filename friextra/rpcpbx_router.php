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
	@package ROUTER呼叫规则函数包
	@description
	&nbsp;&nbsp;ROUTER呼叫规则函数包

ENDPAPER
*/
/*
    注册函数
*/
// 路由部分Router
$server->add('router_list');		#路由列表
$server->add('router_get');		#编辑
$server->add('router_add');		#增加
$server->add('router_edit');		#编辑
$server->add('router_delete');		#删除
$server->add('router_recall');		#调整优先顺序

/*
    函数内容
*/
/*
FRIPAPER

	@name router_list
	@synopsis
		呼叫规则列表
		<code>	
  $retrun = router_list($routerline)
		</code>
	@param $routerline
		呼叫规则类型,1分机,2中继
	@return $retrun
		@item  array 'rules' : 呼叫规则列表结构

ENDPAPER
*/
function router_list($routerline)
{
	global $freeiris_conf;
	global $dbcon;
	

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from router where routerline = '".$routerline."'order by proirety desc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
            //取出process_defined的trunk的数据结构result
            if (trim($each['process_defined']) != '') {
		$extra_res=mysql_query("select * from trunk where id = '".$each['process_defined']."'");
		if (!$extra_res)
			return(rpcreturn(500,mysql_error(),100,null));
		$eachexten = mysql_fetch_array($extra_res);
		mysql_free_result($extra_res);		
		$each['process_defined_trunk_result']=$eachexten;
            }
            //取出match_callergroup的trunk的数据结构result
            if ($routerline == '2') {
		$extra_res=mysql_query("select * from trunk where id = '".$each['match_callergroup']."'");
		if (!$extra_res)
			return(rpcreturn(500,mysql_error(),100,null));
		$eachexten = mysql_fetch_array($extra_res);
		mysql_free_result($extra_res);		
		$each['match_callergroup_trunk_result']=$eachexten;		
            }
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('rules'=>$result_array)));
}

/*
FRIPAPER

	@name router_get
	@synopsis
		取得指定一条呼叫规则信息
		<code>	
  $retrun = router_get($routerid)
		</code>
	@param $routerid
		呼叫规则编号
	@return $retrun
		@item  array 'resdata' : 呼叫规则信息

ENDPAPER
*/
function router_get($routerid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from router where id = '".$routerid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find router",113,null));

        //取出对应的分组名称
        if ($resdata['match_callergroup'] != '') {	
            $exteningroup_res=mysql_query("select * from extengroup where groupid = '".$resdata['match_callergroup']."'");
            if (!$exteningroup_res)
                    return(rpcreturn(500,mysql_error(),100,null));
            $eachexten = mysql_fetch_array($exteningroup_res);
	    mysql_free_result($exteningroup_res);
            $resdata['match_callergroup_extengroup_result']=$eachexten;
        }

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name router_add
	@synopsis
		增加一条呼叫规则
		<code>	
  $retrun = router_add($proirety,$createmode,$routerline,$routerdata)
		</code>
	@param $proirety
		优先级别
	@param $createmode
		0表示自动创建,1表示人工创建,2表示无法手动删除(优先级别是最低的)
	@param $routerline
		0未分配,1内线路由,2外线路由
	@param $routerdata
		路由其他数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function router_add($proirety,$createmode,$routerline,$routerdata)
{
	global $freeiris_conf;
	global $dbcon;


	//------------------------------------------------------分组数据
        if ($routerdata['match_callergroup'] == "" && $routerline == '1') {
            $result=mysql_query("select * from extengroup where groupname = '".$routerdata['match_callergroup_groupname']."'");
            if (!$result)
                    return(rpcreturn(500,mysql_error(),100,null));
            $callergroup = mysql_fetch_array($result);
            mysql_free_result($result);
            $routerdata['match_callergroup'] = $callergroup['groupid'];
            
        } elseif ($routerdata['match_callergroup'] == "" && $routerline == '2') {
            $result=mysql_query("select * from trunk where trunkname = '".$routerdata['match_callergroup_trunkname']."'");
            if (!$result)
                    return(rpcreturn(500,mysql_error(),100,null));
            $callergroup = mysql_fetch_array($result);
            mysql_free_result($result);
            $routerdata['match_callergroup'] = $callergroup['id'];            
        }

	//------------------------------------------------------router created
	$sql = "insert into router set proirety = '".$proirety."',".
			"createmode='".$createmode."',".
			"routerline='".$routerline."',".
			"routername='".$routerdata['routername']."',".
			"optextra='".$routerdata['optextra']."',".
			"lastwhendone='".$routerdata['lastwhendone']."',".
			"match_callergroup='".$routerdata['match_callergroup']."',".
			"match_callerid='".$routerdata['match_callerid']."',".
			"match_callerlen='".$routerdata['match_callerlen']."',".
			"match_callednum='".$routerdata['match_callednum']."',".
			"match_calledlen='".$routerdata['match_calledlen']."',".
			"replace_callerid='".$routerdata['replace_callerid']."',".
			"replace_calledtrim='".$routerdata['replace_calledtrim']."',".
			"replace_calledappend='".$routerdata['replace_calledappend']."',".
			"process_mode='".$routerdata['process_mode']."',".
			"process_defined='".$routerdata['process_defined']."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name router_edit
	@synopsis
		编辑呼叫规则
		<code>	
  $retrun = router_edit($routerid,$routerdata,$routerline)
		</code>
	@param $routerid
		呼叫规则编号
	@param $routerdata
		路由被编辑数据结构
	@param $routerline
		0未分配,1内线路由,2外线路由
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function router_edit($routerid,$routerdata,$routerline)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------分组数据
	if ($routerdata['match_callergroup'] == "" && $routerline == '1') {//分机的
		$result=mysql_query("select * from extengroup where groupname = '".$routerdata['match_callergroup_groupname']."'");
		if (!$result)
				return(rpcreturn(500,mysql_error(),100,null));
		$callergroup = mysql_fetch_array($result);
		mysql_free_result($result);
		$routerdata['match_callergroup'] = $callergroup['groupid'];
		
	} elseif ($routerdata['match_callergroup'] == "" && $routerline == '2') {//中继的
		$result=mysql_query("select * from trunk where trunkname = '".$routerdata['match_callergroup_trunkname']."'");
		if (!$result)
				return(rpcreturn(500,mysql_error(),100,null));
		$callergroup = mysql_fetch_array($result);
		mysql_free_result($result);
		$routerdata['match_callergroup'] = $callergroup['id'];            
	}

	//------------------------------------------------------router changed
	$sql = "update router set ".
			"routername='".$routerdata['routername']."',".
			"optextra='".$routerdata['optextra']."',".
			"lastwhendone='".$routerdata['lastwhendone']."',".
			"match_callergroup='".$routerdata['match_callergroup']."',".
			"match_callerid='".$routerdata['match_callerid']."',".
			"match_callerlen='".$routerdata['match_callerlen']."',".
			"match_callednum='".$routerdata['match_callednum']."',".
			"match_calledlen='".$routerdata['match_calledlen']."',".
			"replace_callerid='".$routerdata['replace_callerid']."',".
			"replace_calledtrim='".$routerdata['replace_calledtrim']."',".
			"replace_calledappend='".$routerdata['replace_calledappend']."',".
			"process_mode='".$routerdata['process_mode']."',".
			"process_defined='".$routerdata['process_defined']."' where ".
                        "id = '".$routerid."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name router_delete
	@synopsis
		删除一条规则
		<code>	
  $retrun = router_delete($id)
		</code>
	@param $id
		规则编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function router_delete($id)
{
	global $freeiris_conf;
	global $dbcon;
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from router where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name router_recall
	@synopsis
		更新IVR菜单动作的优先级别
		<code>	
  $retrun = router_recall($routerid,$proirety)
		</code>
	@param $routerid
		路由规则编号
	@param $proirety
		新优先级别
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function router_recall($routerid,$proirety)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------router changed
	$sql = "update router set proirety='".$proirety."' where id = '".$routerid."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}
?>