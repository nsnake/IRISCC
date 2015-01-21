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
	@package Base基本函数包
	@description
	&nbsp;&nbsp;基本函数

ENDPAPER
*/
/*
    注册函数
*/
$server->add('base_version');// 简单的返回系统版本
$server->add('base_release');// 发行版本
$server->add('base_dbquery');// 原形数据库接口
$server->add('admin_profile_edit');

$server->add('base_readconf');// 取出配置文件
$server->add('base_updateconf');// 编辑配置文件

//号码类型
$server->add('localnumber_get');

// AMI
$server->add('ami');	#执行ami标准指令
$server->add('ami_command');	#执行ami的command指令
$server->add('ami_originate');	#执行ami的originate指令
$server->add('ami_event');		#通过ami_event的数据库记录获得event

//系统状态指令
$server->add('stat_cpu_usage'); //获得CPU的使用率
$server->add('stat_system_uptime'); //获得工作时间
$server->add('stat_memory_usage'); //获得物理内存占用率

// 系统启动指令
$server->add('system_restart');	#执行让fri2d进程重新启动自己

// 注册信息
$server->add('base_registration_get'); #获得注册信息
$server->add('base_registration_set'); #申请注册
$server->add('base_license_get'); #获得注册信息的license内容

/*
    函数内容
*/
/*
FRIPAPER

	@name base_version
	@synopsis
		获得系统的版本号
		<code>	
  $retrun = base_version()
		</code>
	@return $retrun
		@item string 'rpcpbx' : rpcpbx的版本
		@item string 'freeiris2' : freeiris2的版本
		@item string 'buildver' : buildver的版本

ENDPAPER
*/
function base_version()
{
	global $freeiris_conf;
	global $rpc_version;

	return(rpcreturn(200,null,null,array('rpcpbx'=>$rpc_version,'freeiris2'=>$freeiris_conf->get('general','version'),'buildver'=>$freeiris_conf->get('general','buildver'))));
}
/*
FRIPAPER

	@name base_release
	@synopsis
		发行版本
		<code>	
  $retrun = base_release()
		</code>
	@return $retrun
		@item string 'release' : 版本类型

ENDPAPER
*/
function base_release()
{
	global $freeiris_conf;
	global $rpc_version;

	$release = $freeiris_conf->get('general','freeiris_root').'/bin/hardware --release';
	$release = `$release`;
	$release = trim($release);

	return(rpcreturn(200,null,null,array('release'=>$release)));
}
/*
FRIPAPER

	@name base_dbquery
	@synopsis
		数据库原始查询接口,该接口直接连接数据库.
		<code>	
  $retrun = base_dbquery($sql)
		</code>
	@return $retrun
		@item array 'result_array' : 执行数据结构

ENDPAPER
*/
function base_dbquery($sql)
{
	global $freeiris_conf;
	global $dbcon;

	//执行sql
	$result_array=array();
	$result=mysql_query($sql,$dbcon);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	if ( is_resource($result) )
	{
		while ($each = mysql_fetch_array($result))
		{
			array_push($result_array,$each);
		}
		mysql_free_result($result);
	}
	return(rpcreturn(200,null,null,array('result_array'=>$result_array)));
}

/*
FRIPAPER

	@name admin_profile_edit
	@synopsis
		编辑管理员的信息
		<code>	
  $retrun = admin_profile_edit($adminid,$newprofile)
		</code>
	@param $adminid
		管理员的ID号
	@param $newprofile
		编辑的数据信息
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function admin_profile_edit($adminid,$newprofile)
{
	global $freeiris_conf;
	global $dbcon;

	//执行
	if (count($newprofile) > 0) {
		
		//生成数据结构
		foreach ($newprofile as $key => $value) {
			$sqlstruct = $sqlstruct.','.$key."='".$value."'";
		}
		$sqlstruct = trim($sqlstruct,',');
		$sql = $sqlstruct;


		if (!mysql_query("update admin set $sql where adminid = '$adminid'",$dbcon))
			return(rpcreturn(500,mysql_error(),100,null));
		mysql_free_result($result);
	}

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name base_readconf
	@synopsis
		读取指定的配置文件信息结构
		<code>	
  $retrun = base_readconf($folder,$filename)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $filename
		文件名称
	@return $retrun
		@item  array 'resdata' : 该文件经过asteriskconf处理之后的数据结构

ENDPAPER
*/
function base_readconf($folder,$filename)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder != 'freeiris' && $folder != 'asterisk') {
		return(rpcreturn(500,"read conf only support freeiris and asterisk etc folder",100,null));
	} elseif($folder == 'freeiris') {
		$folder='/etc/freeiris2';
	} elseif($folder == 'asterisk') {
		$folder=$freeiris_conf->get('general','asterisketc');
	}

	//打开
	$thisconf = new asteriskconf();
	if ($thisconf->parse_in_file($folder.'/'.$filename)==false)
		return(rpcreturn(500,"can't open ".$folder.'/'.$filename,100,null));

    return(rpcreturn(200,null,null,array('resdata'=>$thisconf->section_all())));
}

/*
FRIPAPER

	@name base_updateconf
	@synopsis
		编辑配置文件的value
		<code>	
  $retrun = base_updateconf($folder,$filename,$section,$key,$newvalue)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $filename
		文件名称
	@param $section
		section块名称,比如[general]就是general
	@param $key
		键名
	@param $newvalue
		新的值名
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function base_updateconf($folder,$filename,$section,$key,$newvalue)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder != 'freeiris' && $folder != 'asterisk') {
		return(rpcreturn(500,"read conf only support freeiris and asterisk etc folder",100,null));
	} elseif($folder == 'freeiris') {
		$folder='/etc/freeiris2';
	} elseif($folder == 'asterisk') {
		$folder=$freeiris_conf->get('general','asterisketc');
	}

	//如果允许操作
	if ($newvalue != '' && $key != '' && $section != '' && $filename != '' && file_exists($folder.'/'.$filename)) {

		//打开
		$thisconf = new asteriskconf();
		if ($thisconf->parse_in_file($folder.'/'.$filename)==false)
			return(rpcreturn(500,"can't open ".$folder.'/'.$filename,100,null));

		//编辑
		$thisconf->assign_editkey($section,$key,$newvalue);

		//保存
		$thisconf->save_file();

	}

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name localnumber_get
	@synopsis
		获得本地号码信息,如果返回为空表示不是本地号码
		<code>	
  $retrun = localnumber_get($number)
		</code>
	@param $number
		号码
	@return $retrun
		@item  array 'resdata' : 该号码的信息

ENDPAPER
*/
function localnumber_get($number)
{
	global $freeiris_conf;
	global $dbcon;
	
	//执行sql
	$result=mysql_query("select * from localnumber where number = '$number'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

    return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name ami
	@synopsis
		执行任何AMI的标准指令.
		指令请参考http://www.voip-info.org/wiki/view/Asterisk+manager+API
		<code>	
  $retrun = ami($actionid,$action,$parameter)
		</code>
	@param $actionid
		AMI指令的编号
	@param $action
		AMI指令动作
	@param $parameter
		指令参数,数据采用'key'=>'value'格式
	@return $retrun
		@item string 'ami' : AMI返回信息

ENDPAPER
*/
function ami($actionid,$action,$parameter)
{
	global $freeiris_conf;
	global $manager_conf;
	global $dbcon;

	//连接AMI
	$asm = new freeiris_ami('freeiris','freeiris','localhost',5038);
	if(!$asm->connect()) {
		return(rpcreturn(500,'ami connect failed',108,null));
	} else{
		$amimessages = $asm->send_request($action,array_merge(array('ActionID'=>$actionid),$parameter));
		$asm->disconnect();
		return(rpcreturn(200,null,null,array('ami'=>$amimessages)));
	}
}


/*
FRIPAPER

	@name ami_command
	@synopsis
		获得本地号码信息,如果返回为空表示不是本地号码
		<code>	
  $retrun = ami_command($actionid,$command)
		</code>
	@param $actionid
		AMI指令的编号
	@param $command
		AMI指令内容
	@return $retrun
		@item string 'ami' : AMI返回信息

ENDPAPER
*/
function ami_command($actionid,$command)
{
	global $freeiris_conf;
	global $manager_conf;
	global $dbcon;

	//取得配置
//	$manager_conf = new asteriskconf();
//	if ($manager_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/manager.conf')==false)
//		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/manager.conf',100,null));

	//连接AMI
	$asm = new freeiris_ami('freeiris','freeiris','localhost',5038);
	if(!$asm->connect()) {
		return(rpcreturn(500,'ami connect failed',108,null));
	} else{
		$amimessages = $asm->send_request('command',array('ActionID'=>$actionid,command=>$command));
		$asm->disconnect();
		return(rpcreturn(200,null,null,array('ami'=>$amimessages)));
	}
}

/*
FRIPAPER

	@name ami_originate
	@synopsis
		ami发起asterisk动作
		<code>	
  $retrun = ami_originate($actionid,$parameter)
		</code>
	@param $actionid
		AMI指令的编号
	@param $parameter
		动作内容参数
	@return $retrun
		@item string 'ami' : AMI返回信息

ENDPAPER
*/
function ami_originate($actionid,$parameter)
{
	global $freeiris_conf;
	global $manager_conf;
	global $dbcon;

	//连接AMI
	$asm = new freeiris_ami('freeiris','freeiris','localhost',5038);
	if(!$asm->connect()) {
		return(rpcreturn(500,'ami connect failed',108,null));
	} else{
		$amimessages = $asm->send_request('Originate',array_merge(array('ActionID'=>$actionid),$parameter));
		$asm->disconnect();
		return(rpcreturn(200,null,null,array('ami'=>$amimessages)));
	}    
}

/*
FRIPAPER

	@name ami_event
	@synopsis
		Freeiris2的基于Proxy模式的AMI EVENT接口,该接口可以提高EVENT数据输出可靠性.
		<code>	
  $retrun = ami_event($limit=100,$fromid=null)
		</code>
	@param $limit
		数据量,系统只保留3分钟内的数据.每次默认取100条
	@param $fromid
		从编号id之后开始,可选参数
	@return $retrun
		@item string 'event' : EVENT数据列表,其中包括event1..event4每个保存240个字符

ENDPAPER
*/
function ami_event($limit=100,$fromid=null)
{
	global $dbcon;

	if (trim($limit)=="" || $limit <= 0) {
		$limit=100;
	}

	if (trim($fromid)!="") {
		$where = "where id > '".$fromid."'";
	}

	//执行sql
	$event=array();
	$result=mysql_query("select * from ami_event $where order by id asc limit $limit");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
#	
	while ($resdata = mysql_fetch_assoc($result)) {
		array_push($event,$resdata);
	}
	mysql_free_result($result);

    return(rpcreturn(200,null,null,array('event'=>$event)));
}

/*
FRIPAPER

	@name stat_cpu_usage
	@synopsis
		CPU处理器的负载状况
		<code>	
  $retrun = stat_cpu_usage()
		</code>
	@return $retrun
		@item string 'usage' : 负载比率
		@item string 'loadavg' : loadavage

ENDPAPER
*/
function stat_cpu_usage()
{
	$cpu = preg_split("/\n/",`cat /proc/stat`);
	$cpu = trim($cpu[0]);
	$cpuinfo1 = preg_split("/ /",$cpu);
//	sleep(1);
	usleep(100000);
	$cpu = preg_split("/\n/",`cat /proc/stat`);
	$cpu = trim($cpu[0]);
	$cpuinfo2 = preg_split("/ /",$cpu);
	$Total_1=$cpuinfo1[1]+$cpuinfo1[3]+$cpuinfo1[2]+$cpuinfo1[4];
	$Total_2=$cpuinfo2[1]+$cpuinfo2[3]+$cpuinfo2[2]+$cpuinfo2[4];
	
	// 修复两次检测中，值完全相等，造成下面的计算中变成为0的计算错误。 2015-01-10 23:21:08 By Coco老爸
	if ( $Total_2 === $Total_1 || $cpuinfo1[2] === $cpuinfo2[2] )
		$Rate = 0;
	else
		$Rate=((($cpuinfo2[1]+$cpuinfo2[2])-($cpuinfo1[1]+$cpuinfo1[2]))/($Total_2-$Total_1))*100;

	$loadavg = `cat /proc/loadavg`;
	trim($loadavg);

    return(rpcreturn(200,null,null,array('usage'=>round($Rate),'loadavg'=>$loadavg)));
}

/*
FRIPAPER

	@name stat_system_uptime
	@synopsis
		系统工作时长
		<code>	
  $retrun = stat_system_uptime()
		</code>
	@return $retrun
		@item string 'uptime' : 工作时长秒

ENDPAPER
*/
function stat_system_uptime()
{
	$uptime = `cat /proc/uptime`;
	$uptime = trim($uptime);
	$uptime = preg_split("/ /",$uptime);

    return(rpcreturn(200,null,null,array('uptime'=>$uptime[0])));
}

/*
FRIPAPER

	@name system_restart
	@synopsis
		系统重新启动
		<code>	
  $retrun = system_restart($area)
		</code>
	@param $area
		<li>'fri2d' 重新启动fri2d主管理进程</li>
		<li>'reboot' 将服务器设备重新启动</li>
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function system_restart($area)
{
	global $freeiris_conf;
	global $dbcon;

	//写执行命令
	if ($area == 'fri2d') {
		shell_exec("/usr/bin/sudo /etc/init.d/fri2d restart");
	} elseif ($area == 'reboot') {
		shell_exec("/usr/bin/sudo /sbin/reboot");
	}
	
	return(rpcreturn(200,null,null,null,null));        
}

/*
FRIPAPER

	@name base_registration_get
	@synopsis
		向服务端获得注册信息
		<code>	
  $retrun = base_registration_get()
		</code>
	@return $retrun
		@item array 'registration' : 信息

ENDPAPER
*/
function base_registration_get()
{
	global $freeiris_conf;
	global $dbcon;

	return(rpcreturn(200,null,null,array('registration'=>$freeiris_conf->key_all('registration'))));
}

/*
FRIPAPER

	@name base_license_get
	@synopsis
		获得注册信息
		<code>	
  $retrun = base_license_get()
		</code>
	@return $retrun
		@item array 'license' : 工作时长秒

ENDPAPER
*/
function base_license_get()
{
	global $freeiris_conf;
	global $dbcon;

	$document=array();
	$document['license'] = file_get_contents($freeiris_conf->get('general','freeiris_root').'/LICENSE');
	$document['protect'] = file_get_contents($freeiris_conf->get('general','freeiris_root').'/PROTECT');
	$document['changes'] = file_get_contents($freeiris_conf->get('general','freeiris_root').'/CHANGES');

	return(rpcreturn(200,null,null,array('license'=>$document)));
}

/*
FRIPAPER

	@name base_registration_set
	@synopsis
		注册信息添写
		<code>	
  $retrun = base_registration_set($systemid,$register_name)
		</code>
	@param $area
		<li>'systemid' 序列号</li>
		<li>'register_name' 注册人姓名</li>
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function base_registration_set($systemid,$register_name)
{
	global $freeiris_conf;
	global $dbcon;

	$confile = new asteriskconf();
	if ($confile->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
		return(rpcreturn(500,"can't open ".'/etc/freeiris2/freeiris.conf',100,null));

	$confile->assign_editkey('registration','register_name',$register_name);
	$confile->assign_editkey('registration','systemid',$systemid);

	//如果执行成功
	$confile->save_file();

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name stat_memory_usage
	@synopsis
		物理内存负载状况
		<code>	
  $retrun = stat_memory_usage()
		</code>
	@return $retrun
		@item string 'percent' : 物理内存负载数(百分比)
		@item string 'total'   : 物理内存总计(KB)
		@item string 'used'    : 物理内存使用(KB)
		@item string 'free'    : 物理内存空余(KB)
		@item string 'cached'  : 已Cached的数量(KB)
		@item string 'buffers' : 已buffers的数量(KB)

ENDPAPER
*/
function stat_memory_usage()
{
    $results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['swap'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);
    $results['devswap'] = array();

    $bufr = rfts( '/proc/meminfo' );
    if ( $bufr != "ERROR" ) {
      $bufe = explode("\n", $bufr);
      foreach( $bufe as $buf ) {
        if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['total'] = $ar_buf[1];
        } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['free'] = $ar_buf[1];
        } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['cached'] = $ar_buf[1];
        } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['buffers'] = $ar_buf[1];
        } 
      } 

      $results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
      $results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);

      // values for splitting memory usage这里可以获取更多的信息,但目前不需要
      //if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
      //  $results['ram']['app'] = $results['ram']['used'] - $results['ram']['cached'] - $results['ram']['buffers'];
	  //  $results['ram']['app_percent'] = round(($results['ram']['app'] * 100) / $results['ram']['total']);
	  //  $results['ram']['buffers_percent'] = round(($results['ram']['buffers'] * 100) / $results['ram']['total']);
	  //  $results['ram']['cached_percent'] = round(($results['ram']['cached'] * 100) / $results['ram']['total']);
      //}
    }
    return(rpcreturn(200,null,null,array('percent'=>$results['ram']['percent'],'total'=>$results['ram']['total'],
		                                 'free'=>$results['ram']['free'],'cached'=>$results['ram']['cached'],'buffers'=>$results['ram']['buffers'],'used'=>$results['ram']['used'])));
}

?>