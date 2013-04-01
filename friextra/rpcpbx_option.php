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
	@package OPTION设置函数包
	@description
	&nbsp;&nbsp;OPTION设置函数包

ENDPAPER
*/
/*
    注册函数
*/
$server->add('dialplan_globalvar_get');	#删除
$server->add('dialplan_globalvar_set');	#调整优先顺序

$server->add('option_confile_list');	#列表出所有文件
$server->add('option_confile_stream');	#读出内容
$server->add('option_confile_puts');	#写入内容,或创建文件 
$server->add('option_confile_delete');	#删除文件freeiris.conf不可删除

$server->add('option_confsection_get');	#读出section的所有key
$server->add('option_confkey_edit');	#写入配置文件中key的编辑配置

$server->add('statistical_record_list');	#读出通话记录

$server->add('hardware_card_stat');	#读出硬件板卡的状态
$server->add('hardware_chandahdi_signalling_set');	#读出硬件板卡的状态

/*
    函数内容
*/
/*
FRIPAPER

	@name dialplan_globalvar_get
	@synopsis
		取得extensions里的global变量
		<code>	
  $retrun = dialplan_globalvar_get($gvar)
		</code>
	@param $gvar
		global变量
	@return $retrun
		@item  $gvar : global变量

ENDPAPER
*/
function dialplan_globalvar_get($gvar)
{
	global $freeiris_conf;
	global $dbcon;

	//取得配置
	$extensions_conf = new asteriskconf();
	if ($extensions_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/extensions.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/extensions.conf',100,null));
                
	return(rpcreturn(200,null,null,array($gvar=>$extensions_conf->get('globals',$gvar))));
}

/*
FRIPAPER

	@name dialplan_globalvar_set
	@synopsis
		设置extensions里的global变量
		<code>	
  $retrun = dialplan_globalvar_set($gvar,$val)
		</code>
	@param $gvar
		global变量
	@param $val
		变量内容
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function dialplan_globalvar_set($gvar,$val)
{
	global $freeiris_conf;
	global $dbcon;

	//取得配置
	$extensions_conf = new asteriskconf();
	if ($extensions_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/extensions.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/extensions.conf',100,null));
                
	$extensions_conf->assign_editkey('globals',$gvar,$val);
	
	//如果执行成功
	$extensions_conf->save_file();
        
	return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name option_confile_list
	@synopsis
		取出配置文件列表
		<code>	
  $retrun = option_confile_list($folder)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@return $retrun
		@item array 'resdata' : 配置文件列表结构

ENDPAPER
*/
function option_confile_list($folder)
{
	global $freeiris_conf;
	global $dbcon;

	$resdata=array();

	if ($folder == 'asterisk') {
		if ($handle = opendir($freeiris_conf->get('general','asterisketc'))) {
			$filearray=array();
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && !preg_match("/^\./",basename($file)) && is_file($freeiris_conf->get('general','asterisketc').'/'.$file) == true) {
					array_push($filearray,$file);
				}
			}
			closedir($handle);
			asort($filearray);
			foreach ($filearray as $key=>$file) {
				$filestat=array();
				$filestat['filename']=basename($file);
				$filestat['filesize']=round(filesize($freeiris_conf->get('general','asterisketc').'/'.$file)/1024,2);
				$filestat['fileowner']=posix_getpwuid(fileowner($freeiris_conf->get('general','asterisketc').'/'.$file));
				$filestat['fileperms']=substr(sprintf('%o', fileperms($freeiris_conf->get('general','asterisketc').'/'.$file)), -4);
				$filestat['filemtime']=date ("Y-m-d H:i:s", filemtime($freeiris_conf->get('general','asterisketc').'/'.$file));
				if ($filestat['fileperms'] == '0777' || $filestat['fileperms'] == '0666') {
					$filestat['filecandel']=true;
				} else {
					$filestat['filecandel']=false;
				}
				array_push($resdata,$filestat);
			}
		}
	} elseif ($folder == 'freeiris') {
		if ($handle = opendir('/etc/freeiris2/')) {
			$filearray=array();
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && !preg_match("/^\./",basename($file)) && is_file('/etc/freeiris2/'.$file) == true) {
					array_push($filearray,$file);
				}
			}
			closedir($handle);
			asort($filearray);
			foreach ($filearray as $key=>$file) {
				$filestat=array();
				$filestat['filename']=basename($file);
				$filestat['filesize']=round(filesize('/etc/freeiris2/'.$file)/1024,2);
				$filestat['fileowner']=posix_getpwuid(fileowner('/etc/freeiris2/'.$file));
				$filestat['fileperms']=substr(sprintf('%o', fileperms('/etc/freeiris2/'.$file)), -4);
				$filestat['filemtime']=date ("Y-m-d H:i:s", filemtime('/etc/freeiris2/'.$file));
				if ($filestat['fileperms'] == '0777' || $filestat['fileperms'] == '0666') {
					$filestat['filecandel']=true;
				} else {
					$filestat['filecandel']=false;
				}

				array_push($resdata,$filestat);
			}
		}
	}


	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name option_confile_stream
	@synopsis
		配置文件数据流
		<code>	
  $retrun = option_confile_stream($folder,$filename)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $filename
		文件名称
	@return $retrun
		@item string 'resdata' : 配置文件数据流内容

ENDPAPER
*/
function option_confile_stream($folder,$filename)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder == 'asterisk') {
		if (is_file($freeiris_conf->get('general','asterisketc').'/'.$filename)) {
			$resdata = file_get_contents($freeiris_conf->get('general','asterisketc').'/'.$filename);
		}

	} elseif ($folder == 'freeiris') {
		if (is_file('/etc/freeiris2/'.$filename)) {
			$resdata = file_get_contents('/etc/freeiris2/'.$filename);
		}

	} elseif ($folder == 'tmp') {
		if ($filename == 'hardwaretel.map') {
			if (is_file('/tmp/'.$filename)) {
				$resdata = file_get_contents('/tmp/'.$filename);
			}
		}
	}

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));
}

/*
FRIPAPER

	@name option_confile_puts
	@synopsis
		配置文件数据流写回
		<code>	
  $retrun = option_confile_puts($folder,$filename,$filestream)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $filename
		文件名称
	@param $filestream
		写回内容
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function option_confile_puts($folder,$filename,$filestream)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder == 'asterisk') {
		if (!preg_match("/\//",$filename)) {
			file_put_contents($freeiris_conf->get('general','asterisketc').'/'.$filename,$filestream);
		}

	} elseif ($folder == 'freeiris') {
		if (!preg_match("/\//",$filename)) {
			file_put_contents('/etc/freeiris2/'.$filename,$filestream);
		}
	}

	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name option_confile_delete
	@synopsis
		删除配置文件
		<code>	
  $retrun = option_confile_delete($folder,$filename)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $filename
		文件名称
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function option_confile_delete($folder,$filename)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder == 'asterisk') {
		if (is_file($freeiris_conf->get('general','asterisketc').'/'.$filename)) {
			unlink($freeiris_conf->get('general','asterisketc').'/'.$filename);
		}

	} elseif ($folder == 'freeiris') {
		if (is_file('/etc/freeiris2/'.$filename) && $filename != 'freeiris.conf') {
			unlink('/etc/freeiris2/'.$filename);
		}
	}

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name option_confsection_get
	@synopsis
		取得配置文件中的section
		<code>	
  $retrun = option_confsection_get($folder,$type,$section)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $type
		文件名称
	@param $section
		section的名称
	@return $retrun
		@item array $section : section的数据结构

ENDPAPER
*/
function option_confsection_get($folder,$type,$section)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder == 'freeiris') {

		if ($type == 'freeiris.conf') {
			return(rpcreturn(200,null,null,array($section=>$freeiris_conf->key_all($section))));

		} else {
			$confile = new asteriskconf();
			if ($confile->parse_in_file('/etc/freeiris2/'.$type)==false)
				return(rpcreturn(500,"can't open ".'/etc/freeiris2/'.$type,100,null));
					
			return(rpcreturn(200,null,null,array($section=>$confile->key_all($section))));
		}


	} elseif ($folder == 'asterisk') {
		
		$confile = new asteriskconf();
		if ($confile->parse_in_file($freeiris_conf->get('general','asterisketc').'/'.$type)==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/'.$type,100,null));
				
		return(rpcreturn(200,null,null,array($section=>$confile->key_all($section))));

	}

}

/*
FRIPAPER

	@name option_confkey_edit
	@synopsis
		编辑配置文件中指定文件的section下的key值
		<code>	
  $retrun = option_confkey_edit($folder,$type,$section,$key,$newvalue)
		</code>
	@param $folder
		<li>'freeiris' 表示/etc/freeiris/目录下.</li>
		<li>'asterisk' 表示/etc/asterisk目录下.</li>
		<li>除了以上两个目录不能读取和编辑其他目录.</li>
	@param $type
		文件名称
	@param $section
		section的名称
	@param $key
		键
	@param $newvalue
		新值
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function option_confkey_edit($folder,$type,$section,$key,$newvalue)
{
	global $freeiris_conf;
	global $dbcon;

	if ($folder == 'freeiris') {

		if ($type == 'freeiris.conf') {

			//取得配置
			$confile = new asteriskconf();
			if ($confile->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
				return(rpcreturn(500,'/etc/freeiris2/freeiris.conf',100,null));
						
			$confile->assign_editkey($section,$key,$newvalue);
			
			//如果执行成功
			$confile->save_file();

		} else {
			$confile = new asteriskconf();
			if ($confile->parse_in_file('/etc/freeiris2/'.$type)==false)
				return(rpcreturn(500,"can't open ".'/etc/freeiris2/'.$type,100,null));

			$confile->assign_editkey($section,$key,$newvalue);
			
			//如果执行成功
			$confile->save_file();
		}


	} elseif ($folder == 'asterisk') {

		$confile = new asteriskconf();
		if ($confile->parse_in_file($freeiris_conf->get('general','asterisketc').'/'.$type)==false)
			return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/'.$type,100,null));

		$confile->assign_editkey($section,$key,$newvalue);
		
		//如果执行成功
		$confile->save_file();

	}
        
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name hardware_card_stat
	@synopsis
		硬件语音卡状态输出
		<code>	
  $retrun = hardware_card_stat()
		</code>
	@return $retrun
		@item array 'cardstat' : 语音卡状态输出数据结构

ENDPAPER
*/
function hardware_card_stat()
{
	$card_stat=array();
//	$card_stat['digital']=array();
//	$card_stat['analog']=array();
	$dir='/proc/dahdi/';

	#打开目录
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {

			#写入文件
			$spanname=array();
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..')
					continue;
				array_push($spanname,$file);
			}
			closedir($dh);
			sort($spanname);

			foreach ($spanname as $file) {
				#打开文件
				$filebody = file_get_contents($dir.'/'.$file);
				if ($filebody) {
					
					$span=array();
					$span['filename']=$file;

					$stat=preg_split("/\n/",$filebody);
					$span['span']=$stat[0];
					$span['channels']=array();
					for ($i=2;$i<=count($stat);$i++) {
						if (trim($stat[$i])=="")
							continue;
						array_push($span['channels'],$stat[$i]);
					}

					array_push($card_stat,$span);
//					if (preg_match("/Span ([0-9]+): (TE|WCT|D)/",$stat[0])) {
//						array_push($card_stat['digital'],$span);
//					} elseif (preg_match("/Span ([0-9]+): (WCTDM|OPVXA1200)/",$stat[0])) {
//						array_push($card_stat['analog'],$span);
//					} else {
//						array_push($card_stat['analog'],$span);
//					}
//					if (preg_match("/Span ([0-9]+): TE/",$stat[0]) || !preg_match("/Span ([0-9]+): WCTDM/",$stat[0])) {
//						array_push($card_stat['digital'],$span);
//					} else {
//						array_push($card_stat['analog'],$span);
//					}
				}
			}

		}
	}

	return(rpcreturn(200,null,null,array('cardstat'=>$card_stat)));
}

/*
FRIPAPER

	@name hardware_chandahdi_signalling_set
	@synopsis
		chan_dahdi驱动配置中信号设置部分
		<code>	
  $retrun = hardware_chandahdi_signalling_set($type,$signalling)
		</code>
	@param $type
		配置类型,目前只能填写'pri'
	@param $signalling
		信号类型,比如pri_net或pri_cpe
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function hardware_chandahdi_signalling_set($type,$signalling)
{
	global $freeiris_conf;

	$changetype=null;
	if ($type == 'pri') {
		$changetype=3;
	} else{
		return(rpcreturn(200,null,null,null,true));
	}

	#--------------------------------------------开始修改参数
	$filebody = file_get_contents($freeiris_conf->get('general','asterisketc').'/chan_dahdi.conf');
	$chan_dahdi = fopen($freeiris_conf->get('general','asterisketc').'/chan_dahdi.conf', 'w');
	if ($chan_dahdi) {
		$signalling_reshow=0;
		foreach (preg_split("/\n/",$filebody) as $line) {
			trim($line);
			if ($line == '')
				continue;

			if (preg_match("/^signalling/",$line)) {
				$signalling_reshow++;

				if ($signalling_reshow==$changetype) {
					fwrite($chan_dahdi, "signalling=".$signalling."\n");
					continue;
				} else {
					fwrite($chan_dahdi, $line."\n");
					continue;
				}

			} else {
				fwrite($chan_dahdi, $line."\n");
				continue;
			}
		}
		fclose($chan_dahdi);
	}

	return(rpcreturn(200,null,null,null,true));
}

?>