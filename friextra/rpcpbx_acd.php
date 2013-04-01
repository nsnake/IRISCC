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
	@package ACD函数包
	@description
	&nbsp;&nbsp;ACD类函数接口

ENDPAPER
*/
/*
    注册函数
*/
//会议室
$server->add('conference_list');
$server->add('conference_get');
$server->add('conference_add');
$server->add('conference_edit');
$server->add('conference_delete');

// 呼叫队列
$server->add("queue_list");
$server->add('queue_get');
$server->add('queue_add');
$server->add('queue_edit');
$server->add('queue_delete');

// IVR菜单
$server->add('ivrmenu_list');
$server->add('ivrmenu_get_menu');
$server->add('ivrmenu_add_menu');
$server->add('ivrmenu_edit_menu');
$server->add('ivrmenu_delete_menu');
$server->add('ivrmenu_list_action');
$server->add('ivrmenu_get_action');
$server->add('ivrmenu_add_action');
$server->add('ivrmenu_edit_action');
$server->add('ivrmenu_delete_action');
$server->add('ivrmenu_recall_action');		#调整优先顺序
$server->add('ivrmenu_list_ivruserinput');
$server->add('ivrmenu_add_ivruserinput');
$server->add('ivrmenu_get_ivruserinput');
$server->add('ivrmenu_edit_ivruserinput');
$server->add('ivrmenu_delete_ivruserinput');

// 自动录音
$server->add('sysautomon_list_trigger');
$server->add('sysautomon_get_trigger');
$server->add('sysautomon_add_trigger');
$server->add('sysautomon_delete_trigger');
$server->add('sysautomon_edit_trigger');


// 自动外呼(下列函数作废)
$server->add("outgoing_list");
//$server->add('outgoing_get');
$server->add('outgoing_add');
//$server->add('outgoing_edit');
$server->add('outgoing_delete');


/*
    函数内容
*/
/*
FRIPAPER

	@name conference_list
	@synopsis
		列表电话会议室
		<code>	
  $retrun = conference_list($order,$limitfrom,$limitoffset)
		</code>
	@param $order
		排序方式,比如填写'order by '
	@param $limitfrom
		取得记录结果的开始位置
	@param $limitoffset
		取得记录结果的结束位置
	@return $retrun
		@item  array 'conferences' : 会议室信息结构

ENDPAPER
*/
function conference_list($order,$limitfrom,$limitoffset)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from conference $order limit $limitfrom,$limitoffset");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('conferences'=>$result_array)));
}

/*
FRIPAPER

	@name conference_get
	@synopsis
		获得指定会议室的信息
		<code>	
  $retrun = conference_list($confno)
		</code>
	@param $confno
		会议室编号
	@return $retrun
		@item  array 'resdata' : 该会议室的信息

ENDPAPER
*/
function conference_get($confno)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from conference where confno = '".$confno."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name conference_add
	@synopsis
		增加一个新会议室
		<code>	
  $retrun = conference_list($confdata)
		</code>
	@param $confdata
		会议室数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function conference_add($confdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$confdata['confno']."',typeof = 'conference',assign = ''");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------ created
	$sql = "insert into conference set ".
			"confno='".$confdata['confno']."',".
			"pincode='".$confdata['pincode']."',".
			"playwhenevent='".$confdata['playwhenevent']."',".
                        "mohwhenonlyone='".$confdata['mohwhenonlyone']."',".                        
			"cretime=now()";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name conference_edit
	@synopsis
		编辑现有的会议室
		<code>	
  $retrun = conference_edit($confno,$confdata)
		</code>
	@param $confno
		会议室的编号
	@param $confdata
		会议室数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function conference_edit($confno,$confdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------changed
	$sql = "update conference set ".
			"pincode='".$confdata['pincode']."',".
			"playwhenevent='".$confdata['playwhenevent']."',".
                        "mohwhenonlyone='".$confdata['mohwhenonlyone']."'".
			" where confno='".$confno."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name conference_delete
	@synopsis
		删除会议室
		<code>	
  $retrun = conference_delete($confno)
		</code>
	@param $confno
		会议室的编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function conference_delete($confno)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where number = '".$confno."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from conference where confno = '".$confno."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}


/*
FRIPAPER

	@name queue_list
	@synopsis
		显示呼叫队列
		<code>	
  $retrun = queue_list($order,$limitfrom,$limitoffset)
		</code>
	@param $order
		排序方式,比如填写'order by '
	@param $limitfrom
		取得记录结果的开始位置
	@param $limitoffset
		取得记录结果的结束位置
	@return $retrun
		@item  array 'queues' : 呼叫队列列表数据结构

ENDPAPER
*/
function queue_list($order,$limitfrom,$limitoffset)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from queue $order limit $limitfrom,$limitoffset");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
		array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('queues'=>$result_array)));
}

/*
FRIPAPER

	@name queue_get
	@synopsis
		获得指定呼叫队列的信息
		<code>	
  $retrun = queue_get($queuenumber)
		</code>
	@param $queuenumber
		呼叫队列编号
	@return $retrun
		@item  array 'resdata' : 该呼叫队列的数据库信息
		@item  array 'confdata' : 该呼叫队列的配置文件信息

ENDPAPER
*/
function queue_get($queuenumber)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from queue where queuenumber = '$queuenumber'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	//处理成员姓名
	$members = preg_split("/\&/",$resdata['members']);
	$members_res=array();
	foreach ($members as $accountcode) {
		if ($accountcode == "")
			continue;
		$result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
		if (!$result)
			return(rpcreturn(500,mysql_error(),100,null));
		$oneres = mysql_fetch_array($result);
		mysql_free_result($result);
		array_push($members_res,$oneres);
	}
	$resdata['members_res']=$members_res;


	//取得配置文件
	$queues_list_conf = new asteriskconf();
	if ($queues_list_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/queues_list.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/queues_list.conf',100,null));
	$confdata = $queues_list_conf->key_all($queuenumber);

    return(rpcreturn(200,null,null,array('resdata'=>$resdata,'confdata'=>$confdata)));
}


/*
FRIPAPER

	@name queue_add
	@synopsis
		增加一个新呼叫队列
		<code>	
  $retrun = queue_add($queuedata)
		</code>
	@param $queuedata
		呼叫队列数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function queue_add($queuedata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$queuedata['queuenumber']."',typeof = 'queue',assign = ''");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
                
	//------------------------------------------------------创建member数据
	foreach ($queuedata['members'] as $value) {
		$indbmembers = $indbmembers."&".$value;
                $tplmembers = $tplmembers."member=LOCAL/".$value."@sub-queuefindnumber/n,0,".$value."\n";
	}
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into queue set cretime=now(),".
						"queuenumber='".$queuedata['queuenumber']."',".
						"queuename='".$queuedata['queuename']."',".
						"announce='".$queuedata['announce']."',".
						"playring='".$queuedata['playring']."',".
						"saymember='".$queuedata['saymember']."',".
						"queuetimeout='".$queuedata['queuetimeout']."',".
						"failedon='".$queuedata['failedon']."',".                                                
						"members='".$indbmembers."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成template数据格式
        $tpldata=array();
        $tpldata['queuenumber']=$queuedata['queuenumber'];
        $tpldata['strategy']=$queuedata['strategy'];
        $tpldata['timeout']=$queuedata['timeout'];
        $tpldata['periodic-announce-frequency']=$queuedata['periodic-announce-frequency'];
        $tpldata['members']=$tplmembers;        
	$tplcontents = conftpl_replace('/etc/freeiris2/queues.conf.tpl', $tpldata);

	//存储到配置文件中
	$queues_list_conf = new asteriskconf();
	if ($queues_list_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/queues_list.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/queues_list.conf',100,null));

	$queues_list_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	if ($queues_list_conf->save_file() && $queues_list_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/queues_list.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name queue_edit
	@synopsis
		编辑呼叫队列
		<code>	
  $retrun = queue_edit($queuenumber,$queuedata)
		</code>
	@param $queuenumber
		呼叫队列号码
	@param $queuedata
		编辑用数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function queue_edit($queuenumber,$queuedata)
{
	global $freeiris_conf;
	global $dbcon;


	//------------------------------------------------------刷新member数据
	foreach ($queuedata['members'] as $value) {
		$indbmembers = $indbmembers."&".$value;
		$tplmembers = $tplmembers."member=LOCAL/".$value."@sub-queuefindnumber/n,0,".$value."\n";
	}
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("update queue set ".
						"queuename='".$queuedata['queuename']."',".
						"announce='".$queuedata['announce']."',".
						"playring='".$queuedata['playring']."',".
						"saymember='".$queuedata['saymember']."',".
						"queuetimeout='".$queuedata['queuetimeout']."',".
						"failedon='".$queuedata['failedon']."',".                                                
						"members='".$indbmembers."' ".
						"where queuenumber='".$queuenumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成template数据格式
        $tpldata=array();
        $tpldata['queuenumber']=$queuenumber;
        $tpldata['strategy']=$queuedata['strategy'];
        $tpldata['timeout']=$queuedata['timeout'];
        $tpldata['periodic-announce-frequency']=$queuedata['periodic-announce-frequency'];
        $tpldata['members']=$tplmembers;        
	$tplcontents = conftpl_replace('/etc/freeiris2/queues.conf.tpl', $tpldata);

	//存储到配置文件中
	$queues_list_conf = new asteriskconf();
	if ($queues_list_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/queues_list.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/queues_list.conf',100,null));

	$queues_list_conf->assign_delsection($queuenumber);
	$queues_list_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	if ($queues_list_conf->save_file() && $queues_list_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/queues_list.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name queue_delete
	@synopsis
		删除呼叫队列
		<code>	
  $retrun = queue_delete($queuenumber)
		</code>
	@param $queuenumber
		呼叫队列的编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function queue_delete($queuenumber)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where number = '".$queuenumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from queue where queuenumber = '".$queuenumber."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除配置数据
	$queues_list_conf = new asteriskconf();
	if ($queues_list_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/queues_list.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/queues_list.conf',100,null));

	$queues_list_conf->assign_delsection($queuenumber);

	//如果执行成功
	if ($queues_list_conf->save_file() && $queues_list_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/queues_list.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name ivrmenu_list
	@synopsis
		显示IVR菜单列表
		<code>	
  $retrun = ivrmenu_list()
		</code>
	@return $retrun
		@item  array 'ivrmenu' : IVR菜单数据结构

ENDPAPER
*/
function ivrmenu_list()
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from ivrmenu order by ivrnumber asc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('ivrmenu'=>$result_array)));
}

/*
FRIPAPER

	@name ivrmenu_get_menu
	@synopsis
		获得指定IVR菜单的信息
		<code>	
  $retrun = ivrmenu_get_menu($ivrnumber)
		</code>
	@param $queuenumber
		呼叫队列编号
	@return $retrun
		@item  array 'resdata' : 该IVR菜单的信息

ENDPAPER
*/
function ivrmenu_get_menu($ivrnumber)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from ivrmenu where ivrnumber = '".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name ivrmenu_add_menu
	@synopsis
		增加IVR菜单
		<code>	
  $retrun = ivrmenu_add_menu($ivrdata)
		</code>
	@param $ivrdata
		IVR菜单数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_add_menu($ivrdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$ivrdata['ivrnumber']."',typeof = 'ivr',assign = ''");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------产生主表
	$sql = "insert into ivrmenu set ".
			"ivrnumber='".$ivrdata['ivrnumber']."',".
			"ivrname='".$ivrdata['ivrname']."',".
			"description='".$ivrdata['description']."',".
			"readonly=0,".
			"cretime=now()";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------产生options表
	$sql = "insert into ivruserinput set ".
			"ivrnumber='".$ivrdata['ivrnumber']."',".
			"general='1',".
			"general_type='invalid',".
			"general_args='folder=freeiris&filename=ivr-invalid&',".
			"input='',".
			"gotoivrnumber='".$ivrdata['ivrnumber']."',".
			"gotoivractid=''";
	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$sql = "insert into ivruserinput set ".
			"ivrnumber='".$ivrdata['ivrnumber']."',".
			"general='1',".
			"general_type='timeout',".
			"general_args='folder=freeiris&filename=ivr-timeout&timeout=10&',".
			"input='',".
			"gotoivrnumber='".$ivrdata['ivrnumber']."',".
			"gotoivractid=''";
	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$sql = "insert into ivruserinput set ".
			"ivrnumber='".$ivrdata['ivrnumber']."',".
			"general='1',".
			"general_type='retry',".
			"general_args='numberofretry=6',".
			"input='',".
			"gotoivrnumber='9000000001',".
			"gotoivractid=''";
	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_edit_menu
	@synopsis
		编辑IVR菜单
		<code>	
  $retrun = ivrmenu_edit_menu($ivrnumber,$ivrdata)
		</code>
	@param $ivrnumber
		IVR菜单编号
	@param $ivrdata
		编辑用数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_edit_menu($ivrnumber,$ivrdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------update
	$result=mysql_query("update ivrmenu set ".
						"ivrname='".$ivrdata['ivrname']."',".
						"description='".$ivrdata['description']."' ".
						"where ivrnumber='".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null));
}


/*
FRIPAPER

	@name ivrmenu_delete_menu
	@synopsis
		删除IVR菜单
		<code>	
  $retrun = ivrmenu_delete_menu($ivrnumber)
		</code>
	@param $ivrnumber
		IVR菜单编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_delete_menu($ivrnumber)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where number = '".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from ivrmenu where ivrnumber = '".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$result=mysql_query("delete from ivraction where ivrnumber = '".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$result=mysql_query("delete from ivruserinput where ivrnumber = '".$ivrnumber."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_list_action
	@synopsis
		IVR菜单动作列表
		<code>	
  $retrun = ivrmenu_list_action($ivrnumber)
		</code>
	@param $ivrnumber
		IVR菜单编号
	@return $retrun
		@item  array 'actions' : 动作数据结构

ENDPAPER
*/
function ivrmenu_list_action($ivrnumber)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from ivraction where ivrnumber = '".$ivrnumber."'order by ordinal asc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('actions'=>$result_array)));
}

/*
FRIPAPER

	@name ivrmenu_get_action
	@synopsis
		获得IVR菜单的动作
		<code>	
  $retrun = ivrmenu_get_action($id)
		</code>
	@param $id
		 动作的编号
	@return $retrun
		@item  array 'resdata' : 该IVR菜单动作的信息

ENDPAPER
*/
function ivrmenu_get_action($id)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from ivraction where id = '".$id."'");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name ivrmenu_add_action
	@synopsis
		增加IVR菜单的动作
		<code>	
  $retrun = ivrmenu_add_action($ordinal,$ivrnumber,$actmode,$args)
		</code>
	@param $ordinal
		优先顺序编号
	@param $ivrnumber
		IVR菜单编号
	@param $actmode
		动作类型
	@param $args
		动作参数
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_add_action($ordinal,$ivrnumber,$actmode,$args)
{
	global $freeiris_conf;
	global $dbcon;

	foreach ($args as $key => $value) {
		$formated_args .= $key.'='.$value.'&';
	}

	//------------------------------------------------------router created
	$sql = "insert into ivraction set ordinal = '".$ordinal."',".
			"ivrnumber='".$ivrnumber."',".
			"actmode='".$actmode."',".
			"args='".$formated_args."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_edit_action
	@synopsis
		编辑IVR菜单的动作
		<code>	
  $retrun = ivrmenu_edit_action($actid,$args)
		</code>
	@param $actid
		动作编号
	@param $args
		参数
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_edit_action($actid,$args)
{
	global $freeiris_conf;
	global $dbcon;

	foreach ($args as $key => $value) {
		$formated_args .= $key.'='.$value.'&';
	}

	//------------------------------------------------------router created
	$sql = "update ivraction set ".
			"args='".$formated_args."'".
			" where id = '".$actid."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_delete_action
	@synopsis
		删除IVR菜单的动作
		<code>	
  $retrun = ivrmenu_delete_action($id)
		</code>
	@param $id
		动作编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_delete_action($id)
{
	global $freeiris_conf;
	global $dbcon;
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from ivraction where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_recall_action
	@synopsis
		更新IVR菜单动作的优先级别
		<code>	
  $retrun = ivrmenu_recall_action($actid,$ordinal)
		</code>
	@param $actid
		动作编号
	@param $ordinal
		新的优先级别编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_recall_action($actid,$ordinal)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------router changed
	$sql = "update ivraction set ordinal='".$ordinal."' where id = '".$actid."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_list_ivruserinput
	@synopsis
		IVR菜单用户输入列表
		<code>	
  $retrun = ivrmenu_list_ivruserinput($ivrnumber,$general)
		</code>
	@param $ivrnumber
		IVR菜单编号
	@param $general
		标准动作参数,如果没有应该是空
	@return $retrun
		@item  array 'ivruserinputs' : 动作数据结构

ENDPAPER
*/
function ivrmenu_list_ivruserinput($ivrnumber,$general)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from ivruserinput where ivrnumber = '".$ivrnumber."' and general = '".$general."' order by input asc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('ivruserinputs'=>$result_array)));
}

/*
FRIPAPER

	@name ivrmenu_get_ivruserinput
	@synopsis
		获得IVR菜单的用户输入
		<code>	
  $retrun = ivrmenu_get_ivruserinput($id)
		</code>
	@param $id
		 用户输入编号
	@return $retrun
		@item  array 'resdata' : 该IVR菜单用户输入的信息

ENDPAPER
*/
function ivrmenu_get_ivruserinput($id)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from ivruserinput where id = '".$id."'");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name ivrmenu_edit_ivruserinput
	@synopsis
		编辑IVR菜单的用户输入
		<code>	
  $retrun = ivrmenu_edit_ivruserinput($ivruserinputid,$iuidata)
		</code>
	@param $ivruserinputid
		用户输入编号
	@param $iuidata
		编辑数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_edit_ivruserinput($ivruserinputid,$iuidata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------changed
	$sql = "update ivruserinput set ".
			"general_args='".$iuidata['general_args']."',".
			"input='".$iuidata['input']."',".
			"gotoivrnumber='".$iuidata['gotoivrnumber']."',".
			"gotoivractid='".$iuidata['gotoivractid']."'".
			" where id='".$ivruserinputid."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_add_ivruserinput
	@synopsis
		增加IVR菜单的用户输入
		<code>	
  $retrun = ivrmenu_add_ivruserinput($ivrnumber,$data)
		</code>
	@param $ivrnumber
		IVR菜单编号
	@param $data
		数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_add_ivruserinput($ivrnumber,$data)
{
	global $freeiris_conf;
	global $dbcon;

	foreach ($args as $key => $value) {
		$formated_args .= $key.'='.$value.'&';
	}

	//------------------------------------------------------router created
	$sql = "insert into ivruserinput set ".
			"ivrnumber='".$ivrnumber."',".
			"general='0',".
			"general_type='',".
			"general_args='',".
			"input='".$data['input']."',".
			"gotoivrnumber='".$data['gotoivrnumber']."',".
			"gotoivractid='".$data['gotoivractid']."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name ivrmenu_delete_ivruserinput
	@synopsis
		删除IVR菜单的用户输入
		<code>	
  $retrun = ivrmenu_delete_ivruserinput($id)
		</code>
	@param $id
		用户输入编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function ivrmenu_delete_ivruserinput($id)
{
	global $freeiris_conf;
	global $dbcon;
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from ivruserinput where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name sysautomon_list_trigger
	@synopsis
		自动录音触发器列表
		<code>	
  $retrun = sysautomon_list_trigger($limitfrom,$limitoffset)
		</code>
	@param $limitfrom
		取得记录结果的开始位置
	@param $limitoffset
		取得记录结果的结束位置
	@return $retrun
		@item  array 'resdata' : 自动录音触发器列表数据结构

ENDPAPER
*/
function sysautomon_list_trigger($limitfrom,$limitoffset)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from sysautomontrigger order by cretime desc limit $limitfrom,$limitoffset");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$result_array)));
}

/*
FRIPAPER

	@name sysautomon_get_trigger
	@synopsis
		获得指定的自动录音触发器信息
		<code>	
  $retrun = sysautomon_get_trigger($id)
		</code>
	@param $id
		 触发器编号
	@return $retrun
		@item  array 'resdata' : 数据信息结构

ENDPAPER
*/
function sysautomon_get_trigger($id)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from sysautomontrigger where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name sysautomon_add_trigger
	@synopsis
		增加自动录音触发器信息
		<code>	
  $retrun = sysautomon_add_trigger($data)
		</code>
	@param $data
		数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function sysautomon_add_trigger($data)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------ created
	$sql = "insert into sysautomontrigger set ".
			"triggername='".$data['triggername']."',".
			"recordout='".$data['recordout']."',".
			"recordin='".$data['recordin']."',".
			"recordqueue='".$data['recordqueue']."',".
			"keepfortype='".$data['keepfortype']."',".
			"keepforargs='".$data['keepforargs']."',".
			"members='".$data['members']."',".
			"cretime=now()";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name sysautomon_delete_trigger
	@synopsis
		删除自动录音触发器信息
		<code>	
  $retrun = sysautomon_delete_trigger($id)
		</code>
	@param $id
		用户输入编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function sysautomon_delete_trigger($id)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------删除
	$result=mysql_query("delete from sysautomontrigger where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name sysautomon_edit_trigger
	@synopsis
		编辑自动录音触发器信息
		<code>	
  $retrun = sysautomon_edit_trigger($id,$data)
		</code>
	@param $id
		触发器编号
	@param $data
		编辑数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function sysautomon_edit_trigger($id,$data)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------ created
	$sql = "update sysautomontrigger set ".
			"triggername='".$data['triggername']."',".
			"recordout='".$data['recordout']."',".
			"recordin='".$data['recordin']."',".
			"recordqueue='".$data['recordqueue']."',".
			"keepfortype='".$data['keepfortype']."',".
			"keepforargs='".$data['keepforargs']."',".
			"members='".$data['members']."'".
			" where id = '".$id."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name outgoing_list
	@synopsis
		列表计划(已作废,请使用base_dbquery直接访问相关表)
		<code>	
  $retrun = outgoing_list()
		</code>
	@return $retrun
		@item  array 'outgoing' : 会议室信息结构

ENDPAPER
*/
function outgoing_list()
{
	return(rpcreturn(500,'function outgoing_list was deprecated and removed!',109,null));
	/*
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from outgoing order by cretime desc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('outgoing'=>$result_array)));
	*/
}




/*
FRIPAPER

	@name outgoing_add
	@synopsis
		增加一个新的外呼计划
		<code>	
  $retrun = outgoing_add($confdata)
		</code>
	@param $confdata
		外呼数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function outgoing_add($indata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------ created
	$sql = "insert into outgoing set ".
			"name='".$indata['name']."',".
			"concurrent='".$indata['concurrent']."',".
			"outgoing_callerid='".$indata['outgoing_callerid']."',".
			"outgoing_waittime='".$indata['outgoing_waittime']."',".
			"numbercount='".count($indata['members'])."',".
			"calledcount=0,".
			"startime='".$indata['startime']."',".
			"localnumber='".$indata['localnumber']."',".
			"cretime=now()";
	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	$result=mysql_query("select last_insert_id()");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$row_lastinsertid = mysql_fetch_array($result);
	mysql_free_result($result);

	foreach ($indata['members'] as $value) {
		$sql = "insert into outgoing_members set ".
				"outgoingid='".$row_lastinsertid[0]."',".
				"number='".$value."',".
				"status=0";
		$result=mysql_query($sql);
		if (!$result)
			return(rpcreturn(500,mysql_error(),100,null));
	}
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}


/*
FRIPAPER

	@name outgoing_delete
	@synopsis
		删除计划任务(已作废,请使用base_dbquery直接访问相关表)
		<code>	
  $retrun = outgoing_delete($id)
		</code>
	@param $id
		编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function outgoing_delete($id)
{
	return(rpcreturn(500,'function outgoing_delete was deprecated and removed!',109,null));
/*
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------删除
	$result=mysql_query("delete from outgoing where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
*/
}

?>