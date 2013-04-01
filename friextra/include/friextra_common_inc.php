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
	freeiris2 extra interface common library

	$Id$
*/

require_once("./include/asteriskconf/asteriskconf.inc.php");
require_once("./include/phprpc/phprpc_server.php");
require_once('./include/freeiris_ami_inc.php');

/*
	初始化数据
*/
function initrpc()
{
	global $freeiris_conf;
//	global $asterisk_conf;
	global $manager_conf;
	global $dbcon;

	// config read enginnger
	$freeiris_conf = new asteriskconf();
	if ($freeiris_conf->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
		return(rpcreturn(500,"can't open freeiris.conf",100,null));

//	$asterisk_conf = new asteriskconf();
//	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
//		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	$manager_conf = new asteriskconf();
	if ($manager_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/manager.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/manager.conf',100,null));

	// 连接数据系统
	$dbcon = mysql_pconnect($freeiris_conf->get('database','dbhost'), $freeiris_conf->get('database','dbuser'), $freeiris_conf->get('database','dbpasswd'));
	if (!$dbcon)
		return(rpcreturn(500,mysql_error(),100,null));

	// 选择库
	$selectdb = mysql_select_db($freeiris_conf->get('database','dbname'),$dbcon);
	if (!$selectdb)	
		return(rpcreturn(500,mysql_error(),100,null));

	return(true);
}

//// sql结构产生 这个代码已经被淘汰掉不再使用
//function buildsql($array)
//{
//	foreach ($array as $key => $value) {
//		$sqlstruct = $sqlstruct.','.$key."='".$value."'";
//	}
//	$sqlstruct = trim($sqlstruct,',');
//	return($sqlstruct);
//}

// 生成返回数据体
function rpcreturn($statcode,$message,$msgcode,$customize,$reload = false)
{
	$rpcdata=array(
		'response'=>array(
			'statcode'=>$statcode,
			'message'=>$message,
			'msgcode'=>$msgcode,
			'reload'=>$reload,
		),
	);
	if ($customize)
		$rpcdata = array_merge($rpcdata,$customize);

	return($rpcdata);
}

// conf template获得key
function conftpl_keys($file)
{
	//打开文件
	$handle = fopen($file, "r");
	if (!$handle)
		return(rpcreturn(500,"failed to open template file : $file",100,null));
	$contents = null;
	while (!feof($handle)) {
	  $contents .= fread($handle, 8192);
	}
	fclose($handle);

	//取出所有key
	//preg_match_all('/(\$\w+)/',$contents,$tpl_key_array);
	preg_match_all('/(\$[\w|\-]+)/',$contents,$tpl_key_array);
	$tpl_key_array = $tpl_key_array[0];
	arsort($tpl_key_array);

	return(array($tpl_key_array,$contents));
}
// conf template数据替换
function conftpl_replace($file,$arraydata)
{
	list($tpl_key_array,$tplfilebody) = conftpl_keys($file);

	//处理template数据,找到被定义了的key并且进行替换
	foreach ($tpl_key_array as $key) {
		$match=ltrim($key,'$');
		//如果这个key存在就进行替换
		if (array_key_exists($match,$arraydata)) {
			$tplfilebody = str_replace($key,$arraydata[$match],$tplfilebody);
		}
	}

	return($tplfilebody);
}
// conf template产生assign_edit编辑
function conftpl_assignedit($file,$arraydata,$confobj,$accountcode)
{
	list($tpl_key_array,$tplfilebody) = conftpl_keys($file);

	//处理template数据,找到被定义了的key并且进行替换
	foreach ($tpl_key_array as $key) {
		$match=ltrim($key,'$');
		//如果这个key存在就进行替换
		if (array_key_exists($match,$arraydata) && is_scalar($confobj->get($accountcode,$match)) && $confobj->get($accountcode,$match) != $arraydata[$match]) {
			$confobj->assign_editkey($accountcode,$match,$arraydata[$match]);
		}
	}
	return(true);
}

// ami shows unpacker
function asunpacker($template,$fulldata)
{
	$template = $template.str_repeat(" ", 30);
	$lenstr=array();
	$minlen = 0;
	preg_match_all('/\S+\s+/',$template,$matches);
	foreach ($matches[0] as $line => $each) {
		array_push($lenstr,array('len'=>strlen($each),'key'=>trim($each,' ')));
		if (($line+1) < count($matches[0])) {
			$minlen = $minlen + strlen($each);
		}
	}
	
	$return = array();
	foreach ($fulldata as $one) {
		if (trim($one) == '' || strlen($one) < $minlen) {
			array_push($return,array($one));
			continue;
		}
		$start = 0;
		$eacharray = array();
		foreach ($lenstr as $len) {
			$data = substr($one,$start,$len['len']);
			$eacharray[$len['key']] = trim($data,' ');
			$start=$start+$len['len'];
		}
		array_push($return,$eacharray);
	}
	
	return($return);
}

// chan unpack
function chanunpacker($channels)
{
	$chanarray=array();

	foreach (preg_split("/\,/",$channels) as $one)
	{
		if (trim($one) == '')
			continue;

		#N-N mode
		if (preg_match("/\-/",$one)) {

			$kv = preg_split("/\-/",$one);
			for ($i=$kv[0];$i<=$kv[1];$i++) {
				array_push($chanarray,$i);
			}

		#N mode
		} else {
			array_push($chanarray,$one);
		}
	}

	return($chanarray);
}

function chanpacker($channel_array)
{

	$channel_string=null;
	sort($channel_array);

	$isnew=null;
	$start=null;
	$buffer=null;

	foreach  ($channel_array as $value) {

		if ($channel_array[(count($channel_array)-1)] == $value) {

			if ($buffer != ($value-1) && $buffer != null && $start == $buffer) {# not 5-5
				$channel_string .= "$start,$value";
			} elseif ($buffer != ($value-1) && $buffer != null) {# 1,2,4-->1-2,4
				$channel_string .= "$start-$buffer,$value";
			} elseif ($buffer != ($value-1) && $start != null) {# 1,2 ---> 1,2
				$channel_string .= "$start,$value";
			} elseif ($buffer != ($value-1)) {# 1,2 ---> 1,2
				$channel_string .= "$value";
			} elseif ($start == null) {#1--->1
				$channel_string .= "$value";
			} else {#1,2,3--->1-3
				$channel_string .= "$start-$value";
			}

			break;
		}

		if ($isnew == null) {#frist
			$isnew = 'no';
			$start=$value;
			$buffer=$value;
			continue;
		} elseif ($buffer == ($value-1)) {
			$buffer=$value;
			continue;
		} elseif ($buffer != ($value-1)) {
			if ($start == $buffer) {# not 5-5
				$channel_string .= "$start,";
			} else {#1,2-->1-2
				$channel_string .= "$start-$buffer,";
			}
			$start=$value;
			$buffer=$value;
			continue;
		}

	}

	return($channel_string);
}

/*
	Read file to string
	读取文件变为文件流
*/
function rfts( $strFileName, $intLines = 0, $intBytes = 4096, $booErrorRep = true ) {
	global $error;
	$strFile = "";
	$intCurLine = 1;
  
	if( file_exists( $strFileName ) ) {
		if( $fd = fopen( $strFileName, 'r' ) ) {
			while( !feof( $fd ) ) {
				$strFile .= fgets( $fd, $intBytes );
				if( $intLines <= $intCurLine && $intLines != 0 ) {
					break;
				} else {
					$intCurLine++;
				}
			}
			fclose( $fd );
		} else {
			return "ERROR";
		}
	} else {
		return "ERROR";
	}
	
	return $strFile;
}

?>
