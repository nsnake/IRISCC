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
	@package 分机函数包
	@description
	&nbsp;&nbsp;分机函数包

ENDPAPER
*/
/*
    注册函数
*/
// 分机
$server->add('extension_list');		#分机列表
$server->add('extension_get');		#分机列表
// sip
$server->add('extension_get_sip');		#获得分机
$server->add('extension_add_sip');	#新增SIP分机
$server->add('extension_edit_sip');	#编辑
$server->add('extension_delete_sip');	#删除
// iax2
$server->add('extension_get_iax2');		#获得分机
$server->add('extension_add_iax2');		#新增分机
$server->add('extension_edit_iax2');	#编辑
$server->add('extension_delete_iax2');	#删除
// virtual
$server->add('extension_get_virtual');		#获得分机
$server->add('extension_add_virtual');		#新增分机
$server->add('extension_edit_virtual');		#编辑
$server->add('extension_delete_virtual');	#删除
// custom
$server->add('extension_get_custom');		#获得分机
$server->add('extension_add_custom');		#新增分机
$server->add('extension_edit_custom');		#编辑
$server->add('extension_delete_custom');	#删除
// fxs
$server->add('extension_freechan_fxs');		#获得分机
$server->add('extension_get_fxs');		#获得分机
$server->add('extension_add_fxs');		#新增分机
$server->add('extension_edit_fxs');		#编辑
$server->add('extension_delete_fxs');	#删除

// 分组
$server->add('extengroup_list');	#分组列表
$server->add('extengroup_get');		#查看分组
$server->add('extengroup_add');		#新增分组
$server->add('extengroup_edit');	#编辑分组
$server->add('extengroup_delete');	#删除分组


/*
    函数内容
*/
/*
FRIPAPER

	@name extension_hints_add
	@synopsis
		非公开内部函数,增加分机的subscribe hints订阅服务
		<code>	
  $retrun = extension_hints_add($number,$protocol,$numberstring)
		</code>
	@param $number
		号码,唯一的
	@param $protocol
		协议类型字符
	@param $numberstring
		号码设备字符串
	@return $retrun
		无

ENDPAPER
*/
function extension_hints_add($number,$protocol,$numberstring)
{
	global $freeiris_conf;
	$extensions_hints_handle = fopen($freeiris_conf->get('general','asterisketc').'/extensions_hints.conf',"a");
	if ($extensions_hints_handle) {
		fwrite($extensions_hints_handle,"exten=".$number.',hint,'.$protocol.'/'.$numberstring."\n");
		fclose($extensions_hints_handle);
	}

return(true);
}
/*
FRIPAPER

	@name extension_hints_add
	@synopsis
		非公开内部函数,删除指定分机的subscribe hints订阅服务
		<code>	
  $retrun = extension_hints_add($number)
		</code>
	@param $number
		号码,唯一的
	@return $retrun
		无

ENDPAPER
*/
function extension_hints_del($number)
{
	global $freeiris_conf;
	$extensions_hints_full = file_get_contents($freeiris_conf->get('general','asterisketc').'/extensions_hints.conf');
	$extensions_hints_new = null;
	foreach (preg_split("/\n/",$extensions_hints_full) as $line) {
		if (preg_match("/^exten\=".$number."/",$line)) {
		} elseif (trim($line) != null) {
			$extensions_hints_new .= $line."\n";
		}
	}
	$extensions_hints_handle = fopen($freeiris_conf->get('general','asterisketc').'/extensions_hints.conf',"w");
	if ($extensions_hints_handle) {
		fwrite($extensions_hints_handle,$extensions_hints_new);
		fclose($extensions_hints_handle);
	}

return(true);
}
/*
FRIPAPER

	@name extension_list
	@synopsis
		全部中继列表
		<code>	
  $retrun = extension_list($order,$limitfrom,$limitoffset)
		</code>
	@param $order
		排序方式,比如填写'order by '
	@param $limitfrom
		取得记录结果的开始位置
	@param $limitoffset
		取得记录结果的结束位置
	@return $retrun
		@item array 'trunks' : 中继列表显示所有中继的数据结构

ENDPAPER
*/
function extension_list($order,$limitfrom,$limitoffset)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from extension $order limit $limitfrom,$limitoffset");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
		//取出对应的分组名称
		$group_array =array();
		$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$each['accountcode']."'");
		if (!$exteningroup_res)
			return(rpcreturn(500,mysql_error(),100,null));
		while ($eachexten = mysql_fetch_array($exteningroup_res))
		{
			array_push($group_array,$eachexten['groupname']);
		}
		$each['group_array']=$group_array;

		array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('extensions'=>$result_array)));
}

/*
FRIPAPER

	@name extension_get
	@synopsis
		取得分机不管他是什么协议的
		<code>	
  $retrun = extension_get($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'extension' : 分机数据结构

ENDPAPER
*/
function extension_get($accountcode)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

    return(rpcreturn(200,null,null,array('extension'=>$resdata)));
}

/*
FRIPAPER

	@name extension_get_sip
	@synopsis
		取出SIP协议分机
		<code>	
  $retrun = extension_get_sip($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'resdata' : 这个分机的数据结构

ENDPAPER
*/
function extension_get_sip($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find extension",125,null));

	//取出对应的分组名称
	$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$resdata['accountcode']."'");
	if (!$exteningroup_res)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($eachexten = mysql_fetch_array($exteningroup_res))
	{
		$extengroup = $extengroup.$eachexten['groupname'].',';
	}
	$resdata['extengroup']=$extengroup;

	//取得配置文件
	$sip_exten_conf = new asteriskconf();
	if ($sip_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_exten.conf',100,null));
	if ($sip_exten_conf->key_all($resdata['accountcode'])) {
		$resdata = array_merge($resdata,$sip_exten_conf->key_all($resdata['accountcode']));
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extension_add_sip
	@synopsis
		增加一个新的SIP分机
		<code>	
  $retrun = extension_add_sip($devdata)
		</code>
	@param $devdata
		增加的分机信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_add_sip($exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$exten['devicenumber']."',typeof = 'extension',assign = '".$exten['accountcode']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into extension set accountcode = '".$exten['accountcode']."',cretime=now(),".
						"password='".$exten['password']."',".
						"deviceproto='sip',".
						"devicenumber='".$exten['devicenumber']."',".
						"devicestring='".$exten['devicenumber']."',".
						"fristchecked=0,".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------创建分组数据
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$exten['accountcode']."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

		//补充sip template所需数据
		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
	}
	mysql_free_result($result);

	//------------------------------------------------------hints的数据处理
	extension_hints_add($exten['devicenumber'],'SIP',$exten['devicenumber']);

	//------------------------------------------------------生成template数据格式
	$tplcontents = conftpl_replace('/etc/freeiris2/exten.sip.conf.tpl', $exten);

	//存储到配置文件中
	$sip_exten_conf = new asteriskconf();
	if ($sip_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_exten.conf',100,null));

	$sip_exten_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	if ($sip_exten_conf->save_file() && $sip_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/sip_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_edit_sip
	@synopsis
		编辑SIP分机
		<code>	
  $retrun = extension_edit_sip($accountcode,$exten)
		</code>
	@param $accountcode
		分机帐户
	@param $exten
		分机的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_edit_sip($accountcode,$exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------编辑extension表
	$result=mysql_query("update extension set ".
						"password='".$exten['password']."',".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."' where accountcode = '".$accountcode."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------修改分组
	//删除旧分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));
	//产生新分组
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$accountcode."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

		//补充sip template所需数据
		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
	}
	mysql_free_result($result);

	//------------------------------------------------------编辑配置数据
	$sip_exten_conf = new asteriskconf();
	if ($sip_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_exten.conf',100,null));
	if (!$sip_exten_conf->key_list($accountcode))
		return(rpcreturn(500,"can't find extension in sip_exten.conf!",100,null));

	//产生编辑数据结构
	if (!conftpl_assignedit('/etc/freeiris2/exten.sip.conf.tpl',$exten,$sip_exten_conf,$accountcode))
			return(rpcreturn(500,"unknow error",100,null));

	//执行
	if ($sip_exten_conf->save_file() && $sip_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/sip_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_delete_sip
	@synopsis
		删除SIP分机
		<code>	
  $retrun = extension_delete_sip($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_delete_sip($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的number
        $result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $extenres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$accountcode."' and number = '".$extenres['devicenumber']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from extension where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------hints的数据处理
	extension_hints_del($extenres['devicenumber']);

	//------------------------------------------------------删除配置数据
	$sip_exten_conf = new asteriskconf();
	if ($sip_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_exten.conf',100,null));

	$sip_exten_conf->assign_delsection($accountcode);

	if ($sip_exten_conf->save_file() && $sip_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/sip_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_get_iax2
	@synopsis
		取出IAX2分机的信息
		<code>	
  $retrun = extension_get_iax2($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'resdata' : 分机数据结构

ENDPAPER
*/
function extension_get_iax2($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find extension",125,null));

	//取出对应的分组名称
	$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$resdata['accountcode']."'");
	if (!$exteningroup_res)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($eachexten = mysql_fetch_array($exteningroup_res))
	{
		$extengroup = $extengroup.$eachexten['groupname'].',';
	}
	$resdata['extengroup']=$extengroup;

	//取得配置文件
	$iax2_exten_conf = new asteriskconf();
	if ($iax2_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_exten.conf',100,null));
	if ($iax2_exten_conf->key_all($resdata['accountcode'])) {
		$resdata = array_merge($resdata,$iax2_exten_conf->key_all($resdata['accountcode']));
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extension_add_iax2
	@synopsis
		增加一个新的IAX2分机
		<code>	
  $retrun = extension_add_iax2($exten)
		</code>
	@param $exten
		增加的分机信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_add_iax2($exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$exten['devicenumber']."',typeof = 'extension',assign = '".$exten['accountcode']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into extension set accountcode = '".$exten['accountcode']."',cretime=now(),".
						"password='".$exten['password']."',".
						"deviceproto='iax2',".
						"devicenumber='".$exten['devicenumber']."',".
						"devicestring='".$exten['devicenumber']."',".
						"fristchecked=0,".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------创建分组数据
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$exten['accountcode']."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

		//补充sip template所需数据
		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
	}
	mysql_free_result($result);

	//------------------------------------------------------hints的数据处理
	extension_hints_add($exten['devicenumber'],'IAX2',$exten['devicenumber']);

	//------------------------------------------------------生成template数据格式
	$tplcontents = conftpl_replace('/etc/freeiris2/exten.iax2.conf.tpl', $exten);

	//存储到配置文件中
	$iax2_exten_conf = new asteriskconf();
	if ($iax2_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_exten.conf',100,null));

	$iax2_exten_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	if ($iax2_exten_conf->save_file() && $iax2_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/iax_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_edit_iax2
	@synopsis
		编辑IAX2分机
		<code>	
  $retrun = extension_edit_iax2($accountcode,$exten)
		</code>
	@param $accountcode
		分机帐户
	@param $exten
		分机的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_edit_iax2($accountcode,$exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------编辑extension表
	$result=mysql_query("update extension set ".
						"password='".$exten['password']."',".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."' where accountcode = '".$accountcode."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------修改分组
	//删除旧分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));
	//产生新分组
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$accountcode."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

		//补充sip template所需数据
		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
	}
	mysql_free_result($result);

	//------------------------------------------------------编辑配置数据
	$iax2_exten_conf = new asteriskconf();
	if ($iax2_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_exten.conf',100,null));
	if (!$iax2_exten_conf->key_list($accountcode))
		return(rpcreturn(500,"can't find extension in iax_exten.conf!",100,null));

	//产生编辑数据结构
	if (!conftpl_assignedit('/etc/freeiris2/exten.iax2.conf.tpl',$exten,$iax2_exten_conf,$accountcode))
			return(rpcreturn(500,"unknow error",100,null));

	//执行
	if ($iax2_exten_conf->save_file() && $iax2_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/iax_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_delete_iax2
	@synopsis
		删除IAX2分机
		<code>	
  $retrun = extension_delete_iax2($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_delete_iax2($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的number
        $result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $extenres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$accountcode."' and number = '".$extenres['devicenumber']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from extension where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------hints的数据处理
	extension_hints_del($extenres['devicenumber']);

	//------------------------------------------------------删除配置数据
	$iax2_exten_conf = new asteriskconf();
	if ($iax2_exten_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_exten.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_exten.conf',100,null));

	$iax2_exten_conf->assign_delsection($accountcode);

	if ($iax2_exten_conf->save_file() && $iax2_exten_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/iax_exten.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name extension_get_virtual
	@synopsis
		取出虚拟分机
		<code>	
  $retrun = extension_get_virtual($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'resdata' : 这个分机的数据结构

ENDPAPER
*/
function extension_get_virtual($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find extension",125,null));

	//取出对应的分组名称
	$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$resdata['accountcode']."'");
	if (!$exteningroup_res)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($eachexten = mysql_fetch_array($exteningroup_res))
	{
		$extengroup = $extengroup.$eachexten['groupname'].',';
	}
	$resdata['extengroup']=$extengroup;

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extension_add_virtual
	@synopsis
		增加一个新的虚拟分机
		<code>	
  $retrun = extension_add_virtual($exten)
		</code>
	@param $exten
		增加的分机信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_add_virtual($exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$exten['devicenumber']."',typeof = 'extension',assign = '".$exten['accountcode']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into extension set accountcode = '".$exten['accountcode']."',cretime=now(),".
						"password='".$exten['password']."',".
						"deviceproto='virtual',".
						"devicenumber='".$exten['devicenumber']."',".
						"devicestring='',".
						"fristchecked=2,".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------创建分组数据
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$exten['accountcode']."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));
	}
	mysql_free_result($result);

	//完成
	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_edit_virtual
	@synopsis
		编辑虚拟分机
		<code>	
  $retrun = extension_edit_virtual($accountcode,$exten)
		</code>
	@param $accountcode
		分机帐户
	@param $exten
		分机的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_edit_virtual($accountcode,$exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------编辑extension表
	$result=mysql_query("update extension set ".
						"password='".$exten['password']."',".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."' where accountcode = '".$accountcode."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------修改分组
	//删除旧分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));
	//产生新分组
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$accountcode."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_delete_virtual
	@synopsis
		删除虚拟分机
		<code>	
  $retrun = extension_delete_virtual($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_delete_virtual($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的number
        $result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $extenres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$accountcode."' and number = '".$extenres['devicenumber']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from extension where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null));
}


/*
FRIPAPER

	@name extension_get_custom
	@synopsis
		取出自定义分机
		<code>	
  $retrun = extension_get_custom($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'resdata' : 这个分机的数据结构

ENDPAPER
*/
function extension_get_custom($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find extension",125,null));

	//取出对应的分组名称
	$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$resdata['accountcode']."'");
	if (!$exteningroup_res)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($eachexten = mysql_fetch_array($exteningroup_res))
	{
		$extengroup = $extengroup.$eachexten['groupname'].',';
	}
	$resdata['extengroup']=$extengroup;

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extension_add_custom
	@synopsis
		增加一个新的自定义分机
		<code>	
  $retrun = extension_add_custom($exten)
		</code>
	@param $exten
		增加的分机信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_add_custom($exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$exten['devicenumber']."',typeof = 'extension',assign = '".$exten['accountcode']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into extension set accountcode = '".$exten['accountcode']."',cretime=now(),".
						"password='".$exten['password']."',".
						"deviceproto='custom',".
						"devicenumber='".$exten['devicenumber']."',".
						"devicestring='".$exten['devicestring']."',".
						"fristchecked=0,".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------创建分组数据
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$exten['accountcode']."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_edit_custom
	@synopsis
		编辑自定义分机
		<code>	
  $retrun = extension_edit_custom($accountcode,$exten)
		</code>
	@param $accountcode
		分机帐户
	@param $exten
		分机的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_edit_custom($accountcode,$exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------编辑extension表
	$result=mysql_query("update extension set ".
						"password='".$exten['password']."',".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"devicestring='".$exten['devicestring']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."' where accountcode = '".$accountcode."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------修改分组
	//删除旧分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));
	//产生新分组
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$accountcode."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_delete_custom
	@synopsis
		删除自定义分机
		<code>	
  $retrun = extension_delete_custom($accountcode)
		</code>
	@param $trunkid
		分机帐户
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_delete_custom($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的number
        $result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $extenres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$accountcode."' and number = '".$extenres['devicenumber']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
                
	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from extension where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_freechan_fxs
	@synopsis
		取出可用的FXS模拟分机信道
		<code>	
  $retrun = extension_freechan_fxs()
		</code>
	@return $retrun
		@item array 'freechan' : 分机信道记录

ENDPAPER
*/
function extension_freechan_fxs()
{
	global $freeiris_conf;

	//get all channels
	$fxsline=null;
	foreach (preg_split("/\n/",file_get_contents('/etc/dahdi/system.conf')) as $line) {
		if (preg_match("/^fxo/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$fxsline=$kv[1];
			break;
		}
	}
	#array it
	$fxsarray=array();
	foreach (preg_split("/\,/",$fxsline) as $one) {
		if (trim($one) == '')
			continue;

		#N-N mode
		if (preg_match("/\-/",$one)) {

			$kv = preg_split("/\-/",$one);
			for ($i=$kv[0];$i<=$kv[1];$i++) {
				array_push($fxsarray,$i);
			}

		#N mode
		} else {
			array_push($fxsarray,$one);
		}
	}

	//get used channels
	$fxsused=array();
	foreach (preg_split("/\n/",file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf')) as $line) {
		if (preg_match("/^channel/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			array_push($fxsused,$kv[1]);
		}
	}
	//get freechan id
	$freechan = array_diff($fxsarray,$fxsused);

	return(rpcreturn(200,null,null,array('freechan'=>$freechan)));
}

/*
FRIPAPER

	@name extension_get_fxs
	@synopsis
		取出FXS模拟分机
		<code>	
  $retrun = extension_get_fxs($accountcode)
		</code>
	@param $accountcode
		分机帐户
	@return $retrun
		@item  array 'resdata' : 这个分机的数据结构

ENDPAPER
*/
function extension_get_fxs($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extension where accountcode = '$accountcode'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find extension",125,null));

	//取出对应的分组名称
	$exteningroup_res=mysql_query("select extengroup.* from extengroup_assign left join extengroup on extengroup_assign.groupid = extengroup.groupid where accountcode = '".$resdata['accountcode']."'");
	if (!$exteningroup_res)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($eachexten = mysql_fetch_array($exteningroup_res))
	{
		$extengroup = $extengroup.$eachexten['groupname'].',';
	}
	$resdata['extengroup']=$extengroup;

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extension_add_fxs
	@synopsis
		增加一个新的FXS模拟分机
		<code>	
  $retrun = extension_add_fxs($exten)
		</code>
	@param $exten
		增加的分机信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_add_fxs($exten)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$exten['devicenumber']."',typeof = 'extension',assign = '".$exten['accountcode']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建extension表记录
	$result=mysql_query("insert into extension set accountcode = '".$exten['accountcode']."',cretime=now(),".
						"password='".$exten['password']."',".
						"deviceproto='fxs',".
						"devicenumber='".$exten['devicenumber']."',".
						"devicestring='".$exten['devicestring']."',".
						"fristchecked=0,".
						"transfernumber='".$exten['transfernumber']."',".
						"diallocal_failed='".$exten['diallocal_failed']."',".
						"info_name='".$exten['info_name']."',".
						"info_email='".$exten['info_email']."',".
						"info_detail='".$exten['info_detail']."',".
						"info_remark='".$exten['info_remark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成分组数据
	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
		$groupnames = $groupnames." groupname = '$value' OR ";
	}
	$groupnames = rtrim($groupnames,'OR ');
	$exten['callgroup']=null;
	$exten['pickupgroup']=null;
	$result=mysql_query("select * from extengroup where $groupnames");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//创建分组
		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$exten['accountcode']."'");
		if (!$dores)
			return(rpcreturn(500,mysql_error(),100,null));

		//补充sip template所需数据
		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
	}
	mysql_free_result($result);

	//------------------------------------------------------hints的数据处理
	extension_hints_add($exten['devicenumber'],'DAHDI',$exten['devicestring']);

	//------------------------------------------------------生成template数据格式
	$tplcontents = conftpl_replace('/etc/freeiris2/chan_dahdi_fxs.conf.tpl', $exten);

	//存储到配置文件中
	$fxs_conf = new asteriskconf();
	if ($fxs_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf',100,null));

	$fxs_conf->assign_append('foot',null,$tplcontents,null);

	$fxs_conf->save_file();

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name extension_edit_fxs
	@synopsis
		编辑FXS模拟分机
		<code>	
  $retrun = extension_edit_fxs($accountcode,$exten)
		</code>
	@param $accountcode
		分机帐户
	@param $exten
		分机的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_edit_fxs($accountcode,$exten)
{
	global $freeiris_conf;
	global $dbcon;

//	//------------------------------------------------------编辑extension表
//	$result=mysql_query("update extension set ".
//						"password='".$exten['password']."',".
//						"transfernumber='".$exten['transfernumber']."',".
//						"diallocal_failed='".$exten['diallocal_failed']."',".
//						"info_name='".$exten['info_name']."',".
//						"info_email='".$exten['info_email']."',".
//						"info_detail='".$exten['info_detail']."',".
//						"info_remark='".$exten['info_remark']."' where accountcode = '".$accountcode."'");
//	if (!$result)
//		return(rpcreturn(500,mysql_error(),100,null));
//
//	//------------------------------------------------------修改分组
//	//删除旧分组
//	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
//	if (!$dores)
//		return(rpcreturn(500,mysql_error(),100,null));
//	//产生新分组
//	foreach (split('[\,|\s|\ ]',$exten['extengroup']) as $value) {
//		$groupnames = $groupnames." groupname = '$value' OR ";
//	}
//	$groupnames = rtrim($groupnames,'OR ');
//	$result=mysql_query("select * from extengroup where $groupnames");
//	if (!$result)
//		return(rpcreturn(500,mysql_error(),100,null));
//	while ($each = mysql_fetch_array($result))
//	{
//		//创建分组
//		$dores=mysql_query("insert into extengroup_assign set groupid = '".$each['groupid']."' , accountcode = '".$accountcode."'");
//		if (!$dores)
//			return(rpcreturn(500,mysql_error(),100,null));
//
//		//补充sip template所需数据
//		$exten['callgroup'] = $exten['callgroup'] . $each['groupid'].',';
//		$exten['pickupgroup'] = $exten['pickupgroup'] . $each['groupid'].',';
//	}
//	mysql_free_result($result);

	//------------------------------------------------------修改分组编辑配置数据
	extension_delete_fxs($accountcode); //删除旧的FXS配置信息
    extension_add_fxs($exten); //新增分机的数据

	//重建新配置信息
//	$tplcontents = conftpl_replace('/etc/freeiris2/chan_dahdi_fxs.conf.tpl', $exten);

	//存储到配置文件中
//	$fxs_conf = new asteriskconf();
//	if ($fxs_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf')==false)
//		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf',100,null));

//	$fxs_conf->assign_append('foot',null,$tplcontents,null);

//	$fxs_conf->save_file();

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extension_delete_fxs
	@synopsis
		删除FXS模拟分机
		<code>	
  $retrun = extension_delete_fxs($accountcode)
		</code>
	@param $trunkid
		分机帐户
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extension_delete_fxs($accountcode)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的number
	$result=mysql_query("select * from extension where accountcode = '".$accountcode."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$extenres = mysql_fetch_array($result);
	mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$accountcode."' and number = '".$extenres['devicenumber']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
                
	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from extension where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除分组
	$dores=mysql_query("delete from extengroup_assign where accountcode = '".$accountcode."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------hints的数据处理
	extension_hints_del($extenres['devicenumber']);

	//------------------------------------------------------删除配置数据
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf');
	$chan_dahdi_fxs = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxs.conf', 'w');
	if ($chan_dahdi_fxs) {
		foreach (preg_split("/\n/",$filebody) as $line) {
			$line = trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^accountcode\=".$accountcode."/",$line)) {
				$findset=true;
				continue;
			//} elseif (preg_match("/^channel\=".$extenres['devicestring']."/",$line)) {
			} elseif (preg_match("/^;friautocreate\=".$accountcode."/",$line)) {
				$findset=false;
				continue;
			} elseif ($findset==true) {
				continue;
			} else {
				fwrite($chan_dahdi_fxs, $line."\n");
				continue;
			}
		}
		fclose($chan_dahdi_fxs);
	}

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name extengroup_list
	@synopsis
		取得分机分组列表
		<code>	
  $retrun = extengroup_list($order)
		</code>
	@param $order
		排序方式,比如填写'order by '
	@return $retrun
		@item array 'result_array' : 分机分组列表数据结构

ENDPAPER
*/
function extengroup_list($order)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from extengroup $order");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		//取出全部成员
		$exten_array =array();
		$exteningroup_res=mysql_query("select * from extengroup_assign where groupid = '".$each['groupid']."'");
		if (!$exteningroup_res)
			return(rpcreturn(500,mysql_error(),100,null));
		while ($eachexten = mysql_fetch_array($exteningroup_res))
		{
			array_push($exten_array,$eachexten['accountcode']);
		}
		$each['exteningroup_array']=$exten_array;

		array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('result_array'=>$result_array)));
}

/*
FRIPAPER

	@name extengroup_delete
	@synopsis
		删除分机分组
		<code>	
  $retrun = extension_delete_sip($groupid)
		</code>
	@param $groupid
		分组编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extengroup_delete($groupid)
{
	global $freeiris_conf;
	global $dbcon;

	//查找是否有人在用
	$result_array=array();
	$result=mysql_query("select count(*) from extengroup_assign where groupid = $groupid");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	$count = mysql_fetch_array($result);
	if ($count['count(*)'] > 0)
		return(rpcreturn(403,'data operation not allow',130,null));

	//删除
	$result=mysql_query("delete from extengroup where groupid = $groupid");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extengroup_add
	@synopsis
		增加一个新的分机分组
		<code>	
  $retrun = extengroup_add($groupname,$remark)
		</code>
	@param $groupname
		分机分组名称
	@param $remark
		分机分组注释
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extengroup_add($groupname,$remark)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query("select groupid from extengroup");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	while ($each = mysql_fetch_array($result))
	{
		array_push($result_array,$each['groupid']);
	}
	mysql_free_result($result);
	//找到可用分组id
	$freenumber=-1;
	for ($i=0;$i<=63;$i++) {
		$free = true;
		foreach ($result_array as $value) {
			if ($value == $i) {
				$free = false;
				continue;
			}
		}
		if ($free == true) {
			$freenumber = $i;
			break;
		}
	}
	if ($freenumber < 0)
		return(rpcreturn(403,'data operation not allow',132,null));

	//创建
	$result=mysql_query("INSERT INTO extengroup (groupid, groupname, remark, cretime) VALUES ($freenumber, '$groupname', '$remark', now())");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name extengroup_get
	@synopsis
		取出分机分组信息
		<code>	
  $retrun = extengroup_get($groupid)
		</code>
	@param $groupid
		分组帐户
	@return $retrun
		@item  array 'resdata' : 这个分机的数据结构

ENDPAPER
*/
function extengroup_get($groupid)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result=mysql_query("select * from extengroup where groupid = '$groupid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name extengroup_edit
	@synopsis
		编辑分机分组
		<code>	
  $retrun = extengroup_edit($groupid,$putdata)
		</code>
	@param $groupid
		分组编号
	@param $putdata
		分组的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function extengroup_edit($groupid,$putdata)
{
	global $freeiris_conf;
	global $dbcon;

	//执行
	if (count($putdata) > 0) {
		//生成数据结构
		foreach ($putdata as $key => $value) {
			$sqlstruct = $sqlstruct.','.$key."='".$value."'";
		}
		$sqlstruct = trim($sqlstruct,',');
		$sql = $sqlstruct;

		if (!mysql_query("update extengroup set $sql where groupid = '$groupid'",$dbcon))
			return(rpcreturn(500,mysql_error(),100,null));
		mysql_free_result($result);
	}

	return(rpcreturn(200,null,null,null));
}

?>