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
	@package 中继函数包
	@description
	&nbsp;&nbsp;中继函数包

ENDPAPER
*/
/*
    注册函数
*/
// 中继
$server->add('trunk_list');		#分机列表
// sip
$server->add('trunk_get_sip');		#获得分机
$server->add('trunk_add_sip');	#新增SIP分机
$server->add('trunk_edit_sip');	#编辑
$server->add('trunk_delete_sip');	#删除
// iax2
$server->add('trunk_get_iax2');		#获得分机
$server->add('trunk_add_iax2');		#新增分机
$server->add('trunk_edit_iax2');	#编辑
$server->add('trunk_delete_iax2');	#删除
// custom
$server->add('trunk_get_custom');		#获得分机
$server->add('trunk_add_custom');		#新增分机
$server->add('trunk_edit_custom');		#编辑
$server->add('trunk_delete_custom');	#删除
// dahdi通用函数
$server->add('trunk_freegroup_dahdi');	#取得所有基于dahdi协议的硬件中继分组可用编号
// digital
$server->add('trunk_freechan_isdnpri');		#获得可用的ISDNPRI CHAN
$server->add('trunk_get_isdnpri');		#获得分机
$server->add('trunk_add_isdnpri');		#新增分机
$server->add('trunk_edit_isdnpri');		#编辑
$server->add('trunk_delete_isdnpri');	#删除
// fxo
$server->add('trunk_freechan_fxo');		#获得可用的ISDNPRI CHAN
$server->add('trunk_get_fxo');		#获得分机
$server->add('trunk_add_fxo');		#新增分机
$server->add('trunk_edit_fxo');		#编辑
$server->add('trunk_delete_fxo');	#删除

/*
    函数内容
*/
/*
FRIPAPER

	@name trunk_list
	@synopsis
		全部中继列表
		<code>	
  $retrun = trunk_list($order,$limitfrom,$limitoffset)
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
function trunk_list($order,$limitfrom,$limitoffset)
{
	global $freeiris_conf;
	global $dbcon;
	
	
	//先从AMI里取得匹配用数据
	$asm = new freeiris_ami('freeiris','freeiris','localhost',5038);
	if(!$asm->connect())
		return(rpcreturn(500,'ami connect failed',108,null));
	//get sip registry
	$amimessages = $asm->send_request('command',array('ActionID'=>'acs',command=>'sip show registry'));
	$peersarray = preg_split('/\n/',$amimessages['data']);
	$peersresult_sip=asunpacker($peersarray[2],$peersarray);
	//get iax2 registry
	$amimessages = $asm->send_request('command',array('ActionID'=>'acs',command=>'iax2 show registry'));
	$peersarray = preg_split('/\n/',$amimessages['data']);
	$peersresult_iax2=asunpacker($peersarray[2],$peersarray);	
	$asm->disconnect();	
	
	
	//从配置中取数据
	$sip_trunk_conf = new asteriskconf();
	if ($sip_trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_trunk.conf',100,null));
	$iax2_trunk_conf = new asteriskconf();
	if ($iax2_trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_trunk.conf',100,null));		
	

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from trunk $order limit $limitfrom,$limitoffset");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
		$match_host=null;
		$match_username=null;
		$peersresult=array();
		
		//如果是SIP注册模式
		if ($each['trunkproto'] == 'sip' && $each['trunkprototype'] == 'reg') {
			$match_host = $sip_trunk_conf->get($each['trunkdevice'],'host').':'.$sip_trunk_conf->get($each['trunkdevice'],'port');
			$match_username = $sip_trunk_conf->get($each['trunkdevice'],'username');
			$peersresult = $peersresult_sip;
		} elseif ($each['trunkproto'] == 'iax2' && $each['trunkprototype'] == 'reg') {
			$match_host = $iax2_trunk_conf->get($each['trunkdevice'],'host').':'.$iax2_trunk_conf->get($each['trunkdevice'],'port');
			$match_username = $iax2_trunk_conf->get($each['trunkdevice'],'username');
			$peersresult = $peersresult_iax2;
		}
		//如果需要进行查找
		if (count($peersresult) > 0) {
			//查找这个帐户是否已经注册上了
			for ($i=3;$i<=(count($peersresult)-2);$i++) {
				$oneres = $peersresult[$i];
				if (!array_key_exists('Host',$oneres))
					continue;
				if ($oneres['Host'] == $match_host &&
				    $oneres['Username'] == $match_username) {
					$each['reg_state']=$oneres['State'];
					break;
				}
			}
		}
		
		array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('trunks'=>$result_array)));
}

/*
FRIPAPER

	@name trunk_get_sip
	@synopsis
		取出SIP协议中继
		<code>	
  $retrun = trunk_get_sip($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		@item  array 'resdata' : 这个中继的数据结构

ENDPAPER
*/
function trunk_get_sip($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));


	//取得配置文件
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_trunk.conf',100,null));
	if ($trunk_conf->key_all($resdata['trunkdevice'])) {
		$resdata = array_merge($resdata,$trunk_conf->key_all($resdata['trunkdevice']));
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name trunk_add_sip
	@synopsis
		增加一个新的SIP中继
		<code>	
  $retrun = trunk_add_sip($devdata)
		</code>
	@param $devdata
		增加的中继信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_add_sip($devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建trunk记录
	$result=mysql_query("insert into trunk set trunkname = '".$devdata['trunkname']."',cretime=now(),".
			"trunkproto='".$devdata['trunkproto']."',".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkdevice='".$devdata['trunkdevice']."',".
			"trunkremark='".$devdata['trunkremark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建trunk记录的id
	$result=mysql_query("select last_insert_id()");
	$lastid = mysql_fetch_array($result);
	mysql_free_result($result);
	$lastid = $lastid[0];

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign = '".$lastid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成sipregistery
	if (trim($devdata['register']) != '') {
		$reg_conf = new asteriskconf();
		if ($reg_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf')==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_regfile.conf',100,null));
		$reg_conf->assign_append('foot',null,'register='.$devdata['register'],null);
		$reg_conf->save_file();
	}
	
	//------------------------------------------------------生成template数据格式
	$tplcontents = conftpl_replace('/etc/freeiris2/trunk.sip.conf.tpl', $devdata);

	//存储到配置文件中
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_trunk.conf',100,null));

	$trunk_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	if ($trunk_conf->save_file() && $trunk_conf->last_changed_file == $freeiris_conf->get('general','asterisketc').'/sip_trunk.conf') {
		return(rpcreturn(200,null,null,null,true));
	} else {
		return(rpcreturn(200,null,null,null));
	}
}

/*
FRIPAPER

	@name trunk_edit_sip
	@synopsis
		编辑SIP中继
		<code>	
  $retrun = trunk_edit_sip($trunkid,$devdata)
		</code>
	@param $trunkid
		中继编号
	@param $devdata
		中继编辑的数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_edit_sip($trunkid,$devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------修改trunk记录
	$result=mysql_query("update trunk set ".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkremark='".$devdata['trunkremark'].
			"' where id = '".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------取得旧siptrunk
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_trunk.conf',100,null));

	//------------------------------------------------------删除旧sipregistery
	if ($trunk_conf->get($resdata['trunkdevice'],'register') != '') {
		$regmatch = 'register='.$trunk_conf->get($resdata['trunkdevice'],'register');
		//打开regfile文件
		$regfile_array = file($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf');
		$regfile_string=null;
		foreach ($regfile_array as $line) {
			//如果在文件中找到对应的register
			if (trim($line) != $regmatch) {
				$regfile_string = $regfile_string.trim($line)."\n";
			}
		}
		//写文件
		$fhandle = fopen($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf','w');
		fwrite($fhandle,$regfile_string);
		fclose($fhandle);
	}
	//------------------------------------------------------增加新的sipregistery
	if (trim($devdata['register']) != '') {
		$reg_conf = new asteriskconf();
		if ($reg_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf')==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_regfile.conf',100,null));
		$reg_conf->assign_append('foot',null,'register='.$devdata['register'],null);
		$reg_conf->save_file();
	}
	
	//------------------------------------------------------编辑siptrunk
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'username',$devdata['username']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'secret',$devdata['secret']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'host',$devdata['host']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'port',$devdata['port']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'fromuser',$devdata['fromuser']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'fromdomain',$devdata['fromdomain']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'register',$devdata['register']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'callerid',$devdata['callerid']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'defaultexpiry',$devdata['defaultexpiry']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'call-limit',$devdata['call-limit']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'progressinband',$devdata['progressinband']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'insecure',$devdata['insecure']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'qualify',$devdata['qualify']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'allow',$devdata['allow']);
	
	//如果执行成功
	$trunk_conf->save_file();
	//必须执行sip reload
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_delete_sip
	@synopsis
		删除SIP中继
		<code>	
  $retrun = trunk_delete_sip($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_delete_sip($trunkid)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的trunkdevice
        $result=mysql_query("select * from trunk where id = '".$trunkid."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $trunkres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$trunkres['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------取得旧siptrunk
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/sip_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_trunk.conf',100,null));

	//------------------------------------------------------删除旧sipregistery
	if ($trunk_conf->get($resdata['trunkdevice'],'register') != '') {
		$regmatch = 'register='.$trunk_conf->get($resdata['trunkdevice'],'register');
		//打开regfile文件
		$regfile_array = file($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf');
		$regfile_string=null;
		foreach ($regfile_array as $line) {
			//如果在文件中找到对应的register
			if (trim($line) != $regmatch) {
				$regfile_string = $regfile_string.trim($line)."\n";
			}
		}
		//写文件
		$fhandle = fopen($freeiris_conf->get('general','asterisketc').'/sip_regfile.conf','w');
		fwrite($fhandle,$regfile_string);
		fclose($fhandle);
	}

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from trunk where id = '".$trunkid."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除配置数据
	$trunk_conf->assign_delsection($resdata['trunkdevice']);
	
	//执行删除
	$trunk_conf->save_file();
	//删除后必须执行sip reload
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_add_iax2
	@synopsis
		增加一个新的IAX2中继
		<code>	
  $retrun = trunk_add_iax2($devdata)
		</code>
	@param $devdata
		增加的中继信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_add_iax2($devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建trunk记录
	$result=mysql_query("insert into trunk set trunkname = '".$devdata['trunkname']."',cretime=now(),".
			"trunkproto='".$devdata['trunkproto']."',".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkdevice='".$devdata['trunkdevice']."',".
			"trunkremark='".$devdata['trunkremark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建trunk记录的id
	$result=mysql_query("select last_insert_id()");
	$lastid = mysql_fetch_array($result);
	mysql_free_result($result);
	$lastid = $lastid[0];
	
	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign = '".$lastid."'");	

	//------------------------------------------------------生成iax2registery
	if (trim($devdata['register']) != '') {
		$reg_conf = new asteriskconf();
		if ($reg_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf')==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_regfile.conf',100,null));
		$reg_conf->assign_append('foot',null,'register='.$devdata['register'],null);
		$reg_conf->save_file();
	}

	//------------------------------------------------------生成template数据格式
	$tplcontents = conftpl_replace('/etc/freeiris2/trunk.iax2.conf.tpl', $devdata);

	//存储到配置文件中
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_trunk.conf',100,null));

	$trunk_conf->assign_append('foot',null,$tplcontents,null);

	//如果执行成功
	$trunk_conf->save_file();
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_get_iax2
	@synopsis
		取出IAX2协议中继
		<code>	
  $retrun = trunk_get_iax2($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		@item  array 'resdata' : 这个中继的数据结构

ENDPAPER
*/
function trunk_get_iax2($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));


	//取得配置文件
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_trunk.conf',100,null));
	if ($trunk_conf->key_all($resdata['trunkdevice'])) {
		$resdata = array_merge($resdata,$trunk_conf->key_all($resdata['trunkdevice']));
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}


/*
FRIPAPER

	@name trunk_edit_iax2
	@synopsis
		编辑IAX2中继
		<code>	
  $retrun = trunk_edit_iax2($trunkid,$devdata)
		</code>
	@param $trunkid
		中继编号
	@param $devdata
		中继编辑的数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_edit_iax2($trunkid,$devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------修改trunk记录
	$result=mysql_query("update trunk set ".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkremark='".$devdata['trunkremark'].
			"' where id = '".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------取得旧trunk
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_trunk.conf',100,null));

	//------------------------------------------------------删除旧registery
	if ($trunk_conf->get($resdata['trunkdevice'],'register') != '') {
		$regmatch = 'register='.$trunk_conf->get($resdata['trunkdevice'],'register');
		//打开regfile文件
		$regfile_array = file($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf');
		$regfile_string=null;
		foreach ($regfile_array as $line) {
			//如果在文件中找到对应的register
			if (trim($line) != $regmatch) {
				$regfile_string = $regfile_string.trim($line)."\n";
			}
		}
		//写文件
		$fhandle = fopen($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf','w');
		fwrite($fhandle,$regfile_string);
		fclose($fhandle);
	}
	//------------------------------------------------------增加新的sipregistery
	if (trim($devdata['register']) != '') {
		$reg_conf = new asteriskconf();
		if ($reg_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf')==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/sip_regfile.conf',100,null));
		$reg_conf->assign_append('foot',null,'register='.$devdata['register'],null);
		$reg_conf->save_file();
	}

	//------------------------------------------------------编辑siptrunk
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'username',$devdata['username']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'secret',$devdata['secret']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'host',$devdata['host']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'port',$devdata['port']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'register',$devdata['register']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'callerid',$devdata['callerid']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'qualify',$devdata['qualify']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'transfer',$devdata['transfer']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'jitterbuffer',$devdata['jitterbuffer']);
	$trunk_conf->assign_editkey($resdata['trunkdevice'],'allow',$devdata['allow']);
	
	//如果执行成功
	$trunk_conf->save_file();
	//必须执行sip reload
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_delete_iax2
	@synopsis
		删除IAX2中继
		<code>	
  $retrun = trunk_delete_iax2($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_delete_iax2($trunkid)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的trunkdevice
        $result=mysql_query("select * from trunk where id = '".$trunkid."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $trunkres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$trunkres['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------取得旧siptrunk
	$trunk_conf = new asteriskconf();
	if ($trunk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/iax_trunk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/iax_trunk.conf',100,null));

	//------------------------------------------------------删除旧registery
	if ($trunk_conf->get($resdata['trunkdevice'],'register') != '') {
		$regmatch = 'register='.$trunk_conf->get($resdata['trunkdevice'],'register');
		//打开regfile文件
		$regfile_array = file($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf');
		$regfile_string=null;
		foreach ($regfile_array as $line) {
			//如果在文件中找到对应的register
			if (trim($line) != $regmatch) {
				$regfile_string = $regfile_string.trim($line)."\n";
			}
		}
		//写文件
		$fhandle = fopen($freeiris_conf->get('general','asterisketc').'/iax_regfile.conf','w');
		fwrite($fhandle,$regfile_string);
		fclose($fhandle);
	}

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from trunk where id = '".$trunkid."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除配置数据
	$trunk_conf->assign_delsection($resdata['trunkdevice']);
	
	//执行删除
	$trunk_conf->save_file();
	//删除后必须执行sip reload
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_add_custom
	@synopsis
		增加一个新的自定义中继
		<code>	
  $retrun = trunk_add_custom($devdata)
		</code>
	@param $devdata
		增加的中继信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_add_custom($devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建trunk记录
	$result=mysql_query("insert into trunk set trunkname = '".$devdata['trunkname']."',cretime=now(),".
			"trunkproto='".$devdata['trunkproto']."',".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkdevice='".$devdata['trunkdevice']."',".
			"trunkremark='".$devdata['trunkremark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------创建trunk记录的id
	$result=mysql_query("select last_insert_id()");
	$lastid = mysql_fetch_array($result);
	mysql_free_result($result);
	$lastid = $lastid[0];

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign = '".$lastid."'");

	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name trunk_get_custom
	@synopsis
		取出自定义中继
		<code>	
  $retrun = trunk_get_custom($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		@item  array 'resdata' : 这个中继的数据结构

ENDPAPER
*/
function trunk_get_custom($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}


/*
FRIPAPER

	@name trunk_edit_custom
	@synopsis
		编辑自定义中继
		<code>	
  $retrun = trunk_edit_custom($trunkid,$devdata)
		</code>
	@param $trunkid
		中继编号
	@param $devdata
		中继编辑的数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_edit_custom($trunkid,$devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------修改trunk记录
	$result=mysql_query("update trunk set ".
			"trunkremark='".$devdata['trunkremark']."',".
			"trunkdevice='".$devdata['trunkdevice'].
			"' where id = '".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除旧的localnumber
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$resdata['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign='".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));		

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name trunk_delete_custom
	@synopsis
		删除自定义中继
		<code>	
  $retrun = trunk_delete_custom($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_delete_custom($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//------------------------------------------------------先取出来找到真正的trunkdevice
        $result=mysql_query("select * from trunk where id = '".$trunkid."'");
        if (!$result)
                return(rpcreturn(500,mysql_error(),100,null));
        $trunkres = mysql_fetch_array($result);
        mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$trunkres['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from trunk where id = '".$trunkid."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name trunk_freegroup_dahdi
	@synopsis
		可用的chan_dahdi分组编号,整个chan_dahdi一共有0-63个分组
		DAHDI只能用0-59共60个分组
		60分组分配给FXS口
		61-63分组暂时未使用
		<code>	
  $retrun = trunk_freegroup_dahdi()
		</code>
	@return $retrun
		@item array 'freegroup' : 可用分组编号

ENDPAPER
*/
function trunk_freegroup_dahdi()
{
	global $freeiris_conf;
	//all group
	$allgroup=array();
	for ($i=0;$i<=63;$i++) {
		array_push($allgroup,$i);
	}
	//get used group
	$usedgroup=array();
	foreach (preg_split("/\n/",file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf')) as $line) {
		if (preg_match("/^group/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			array_push($usedgroup,$kv[1]);
		}
	}
	foreach (preg_split("/\n/",file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf')) as $line) {
		if (preg_match("/^group/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			array_push($usedgroup,$kv[1]);
		}
	}
	//get freegroup
	$freegroup = array_diff($allgroup,$usedgroup);
	sort($freegroup);

	return(rpcreturn(200,null,null,array('freegroup'=>$freegroup)));
}

/*
FRIPAPER

	@name trunk_freechan_isdnpri
	@synopsis
		取出可用的ISDNPRI数字中继 B信道
		<code>	
  $retrun = trunk_freechan_isdnpri()
		</code>
	@return $retrun
		@item array 'freechan' : b信道记录

ENDPAPER
*/
function trunk_freechan_isdnpri()
{
	global $freeiris_conf;

	//get all channels
	$bchanarray=array();
	$spancount=1;
	foreach (preg_split("/\n/",file_get_contents('/etc/dahdi/system.conf')) as $line) {
		if (preg_match("/^bchan/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$bchanarray[$spancount]=chanunpacker($kv[1]);
			$spancount++;
		}
	}

	//get used channels
	$bchanused=array();
	foreach (preg_split("/\n/",file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf')) as $line) {
		if (preg_match("/^channel/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$bchanused=array_merge($bchanused,chanunpacker($kv[1]));
		}
	}
	//get know how different
	foreach ($bchanarray as $key=>$one) {
		$thisfree = array_diff($one,$bchanused);
		$bchanarray[$key]=$thisfree;
	}

	return(rpcreturn(200,null,null,array('freechan'=>$bchanarray)));
}

/*
FRIPAPER

	@name trunk_get_isdnpri
	@synopsis
		取出ISDN-PRI数字中继信息
		<code>	
  $retrun = trunk_get_isdnpri($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		@item  array 'resdata' : 这个中继的数据结构

ENDPAPER
*/
function trunk_get_isdnpri($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));


	//取得配置文件
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf');
	foreach (preg_split("/\n/",$filebody) as $line) {
		trim($line);
		if ($line == '')
			continue;

		if (preg_match("/^group\=".$resdata['trunkdevice']."/",$line)) {
			$findset=true;
			continue;
		} elseif (preg_match("/^channel\=/",$line) && $findset==true) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$resdata['channel_array']=chanunpacker($kv[1]);
			break;
		}
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name trunk_add_isdnpri
	@synopsis
		增加一个新的ISDN-PRI数字中继
		<code>	
  $retrun = trunk_add_isdnpri($devdata)
		</code>
	@param $devdata
		增加的中继信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_add_isdnpri($devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建trunk记录
	$result=mysql_query("insert into trunk set trunkname = '".$devdata['trunkname']."',cretime=now(),".
			"trunkproto='".$devdata['trunkproto']."',".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkdevice='".$devdata['trunkdevice']."',".
			"trunkremark='".$devdata['trunkremark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建trunk记录的id
	$result=mysql_query("select last_insert_id()");
	$lastid = mysql_fetch_array($result);
	mysql_free_result($result);
	$lastid = $lastid[0];

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign = '".$lastid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成template数据格式
	$devdata['channel']=chanpacker($devdata['channel_array']);
	$tplcontents = conftpl_replace('/etc/freeiris2/chan_dahdi_digital.conf.tpl', $devdata);

	//存储到配置文件中
	$digital_conf = new asteriskconf();
	if ($digital_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf',100,null));

	$digital_conf->assign_append('foot',null,$tplcontents,null);

	$digital_conf->save_file();

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_edit_isdnpri
	@synopsis
		编辑ISDN-PRI 数字中继
		<code>	
  $retrun = trunk_edit_isdnpri($trunkid,$devdata)
		</code>
	@param $trunkid
		中继编号
	@param $devdata
		中继编辑的数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_edit_isdnpri($trunkid,$devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------修改trunk记录
	$result=mysql_query("update trunk set ".
			"trunkremark='".$devdata['trunkremark'].
			"' where id = '".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------编辑配置数据
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf');
	$chan_dahdi_digital = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf', 'w');
	if ($chan_dahdi_digital) {
		foreach (preg_split("/\n/",$filebody) as $line) {
			trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^channel\=/",$line) && $findset==true) {
				fwrite($chan_dahdi_digital, "channel=".chanpacker($devdata['channel_array'])."\n");
				$findset=false;
				continue;
			} else {
				fwrite($chan_dahdi_digital, $line."\n");
				if (preg_match("/^group\=".$resdata['trunkdevice']."/",$line)) {
					$findset=true;
				}
				continue;
			}
		}
		fclose($chan_dahdi_digital);
	}

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_delete_isdnpri
	@synopsis
		删除ISDN-PRI 数字中继的记录(同时释放了B信道的使用)
		<code>	
  $retrun = trunk_delete_isdnpri($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_delete_isdnpri($trunkid)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的trunkdevice
    $result=mysql_query("select * from trunk where id = '".$trunkid."'");
    if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$trunkres = mysql_fetch_array($result);
	mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$trunkres['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from trunk where id = '".$trunkid."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除配置数据
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf');
	$chan_dahdi_digital = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi_digital.conf', 'w');
	if ($chan_dahdi_digital) {
		foreach (preg_split("/\n/",$filebody) as $line) {
			trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^group\=".$trunkres['trunkdevice']."/",$line)) {
				$findset=true;
				continue;
			} elseif (preg_match("/^channel\=/",$line) && $findset==true) {
				$findset=false;
				continue;
			} elseif ($findset==true) {
				continue;
			} else {
				fwrite($chan_dahdi_digital, $line."\n");
				continue;
			}
		}
		fclose($chan_dahdi_digital);
	}

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_freechan_fxo
	@synopsis
		取出可用的FXO模拟中继 信道
		<code>	
  $retrun = trunk_freechan_fxo()
		</code>
	@return $retrun
		@item array 'freechan' : b信道记录

ENDPAPER
*/
function trunk_freechan_fxo()
{
	global $freeiris_conf;

	//get all channels
	$fxoarray=array();
	foreach (preg_split("/\n/",file_get_contents('/etc/dahdi/system.conf')) as $line) {
		if (preg_match("/^fxs/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$fxoarray=chanunpacker($kv[1]);
			break;
		}
	}

	//get used channels
	$fxoused=array();
	foreach (preg_split("/\n/",file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf')) as $line) {
		if (preg_match("/^channel/",$line)) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$fxoused=array_merge($fxoused,chanunpacker($kv[1]));
		}
	}
	//get know how different
	$freechan = array_diff($fxoarray,$fxoused);

	return(rpcreturn(200,null,null,array('freechan'=>$freechan)));
}

/*
FRIPAPER

	@name trunk_get_fxo
	@synopsis
		取出模拟中继续FXO的记录
		<code>	
  $retrun = trunk_get_fxo($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		@item  array 'resdata' : 这个中继的数据结构

ENDPAPER
*/
function trunk_get_fxo($trunkid)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//取得配置文件
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf');
	foreach (preg_split("/\n/",$filebody) as $line) {
		trim($line);
		if ($line == '')
			continue;

		if (preg_match("/^group\=".$resdata['trunkdevice']."/",$line)) {
			$findset=true;
			continue;
		} elseif (preg_match("/^channel\=/",$line) && $findset==true) {
			$line=trim($line);
			$kv = preg_split("/\=/",$line);
			$resdata['channel_array']=chanunpacker($kv[1]);
			break;
		}
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name trunk_add_fxo
	@synopsis
		增加一个新的模拟中继FXO
		<code>	
  $retrun = trunk_add_fxo($devdata)
		</code>
	@param $devdata
		增加的中继信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_add_fxo($devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------创建trunk记录
	$result=mysql_query("insert into trunk set trunkname = '".$devdata['trunkname']."',cretime=now(),".
			"trunkproto='".$devdata['trunkproto']."',".
			"trunkprototype='".$devdata['trunkprototype']."',".
			"trunkdevice='".$devdata['trunkdevice']."',".
			"trunkremark='".$devdata['trunkremark']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
		
	//------------------------------------------------------创建trunk记录的id
	$result=mysql_query("select last_insert_id()");
	$lastid = mysql_fetch_array($result);
	mysql_free_result($result);
	$lastid = $lastid[0];

	//------------------------------------------------------创建localnumber表记录
	$result=mysql_query("insert into localnumber set number = '".$devdata['trunkdevice']."',typeof = 'trunk',assign = '".$lastid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------生成template数据格式
	$devdata['channel']=chanpacker($devdata['channel_array']);
	$tplcontents = conftpl_replace('/etc/freeiris2/chan_dahdi_fxo.conf.tpl', $devdata);

	//存储到配置文件中
	$fxo_conf = new asteriskconf();
	if ($fxo_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf',100,null));

	$fxo_conf->assign_append('foot',null,$tplcontents,null);

	$fxo_conf->save_file();

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_edit_fxo
	@synopsis
		编辑模拟中继FXO的信息
		<code>	
  $retrun = trunk_edit_fxo($trunkid,$devdata)
		</code>
	@param $trunkid
		中继编号
	@param $devdata
		中继编辑的数据
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_edit_fxo($trunkid,$devdata)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------修改trunk记录
	$result=mysql_query("update trunk set ".
			"trunkremark='".$devdata['trunkremark'].
			"' where id = '".$trunkid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------取得trunk记录
	$result=mysql_query("select * from trunk where id = '$trunkid'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find trunk",143,null));

	//------------------------------------------------------编辑配置数据
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf');
	$chan_dahdi_fxo = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf', 'w');
	if ($chan_dahdi_fxo) {
		foreach (preg_split("/\n/",$filebody) as $line) {
			$line=trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^channel\=/",$line) && $findset==true) {
				fwrite($chan_dahdi_fxo, "channel=".chanpacker($devdata['channel_array'])."\n");
				$findset=false;
				continue;
			} else {
				fwrite($chan_dahdi_fxo, $line."\n");
				if (preg_match("/^accountcode\=".$resdata['trunkdevice']."/",$line)) {
					$findset=true;
				}
				continue;
			}
		}
		fclose($chan_dahdi_fxo);
	}

	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name trunk_delete_fxo
	@synopsis
		删除模拟中继FXO
		<code>	
  $retrun = trunk_delete_fxo($trunkid)
		</code>
	@param $trunkid
		中继编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function trunk_delete_fxo($trunkid)
{
	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------先取出来找到真正的trunkdevice
    $result=mysql_query("select * from trunk where id = '".$trunkid."'");
    if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$trunkres = mysql_fetch_array($result);
	mysql_free_result($result);

	//------------------------------------------------------删除localnumber表记录
	$result=mysql_query("delete from localnumber where assign = '".$trunkid."' and number = '".$trunkres['trunkdevice']."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	
	//------------------------------------------------------删除基本数据
	$dores=mysql_query("delete from trunk where id = '".$trunkid."'");
	if (!$dores)
		return(rpcreturn(500,mysql_error(),100,null));

	//------------------------------------------------------删除配置数据
	$findset=false;
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf');
	$chan_dahdi_fxo = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi_fxo.conf', 'w');
	if ($chan_dahdi_fxo) {
		foreach (preg_split("/\n/",$filebody) as $line) {
			$line=trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^accountcode\=".$trunkres['trunkdevice']."/",$line)) {
				$findset=true;
				continue;
			//} elseif (preg_match("/^channel\=/",$line) && $findset==true) {
			} elseif (preg_match("/^;friautocreate\=".$trunkres['trunkdevice']."/",$line) && $findset=true) {
				$findset=false;
				continue;
			} elseif ($findset==true) {
				continue;
			} else {
				fwrite($chan_dahdi_fxo, $line."\n");
				continue;
			}
		}
		fclose($chan_dahdi_fxo);
	}

	return(rpcreturn(200,null,null,null,true));
}

?>