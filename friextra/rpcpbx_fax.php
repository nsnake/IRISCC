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
	@package FAX传真包
	@description
	&nbsp;&nbsp;FAX传真函数集成包

ENDPAPER
*/
/*
    注册函数
*/
$server->add('fax_option_get');
$server->add('fax_faxfile_send');
$server->add('fax_faxfile_download');
$server->add('license_download');

/*
	函数内容
*/
/*
FRIPAPER

	@name fax_option_get
	@synopsis
		读出传真配置参数
		<code>	
  $retrun = fax_option_get()
		</code>
	@return $retrun
		@item  array 'resdata' : 配置参数数据结构

ENDPAPER
*/
function fax_option_get()
{
	global $freeiris_conf;
    return(rpcreturn(200,null,null,array('resdata'=>$freeiris_conf->key_all('fax'))));
}
/*
FRIPAPER

	@name license_download
	@synopsis
		备份授权协议
		<code>	
  $retrun = license_download()
		</code>
	@return $retrun
		@item  binarystring 'filebinary' : 二进制数据文件内容
		@item  string 'filetype' : 文件格式

ENDPAPER
*/
function license_download()
{
	global $freeiris_conf;

	$filename = uniqid();

	system("tar czf /tmp/".$filename.".tgz /var/lib/asterisk/licenses/");
	$handle = fopen("/tmp/".$filename.".tgz", "rb");
	$contents = fread($handle, filesize("/tmp/".$filename.".tgz"));
	fclose($handle);
	unlink("/tmp/".$filename.".tgz");

	return(rpcreturn(200,null,null,array('filebinary'=>$contents,'filetype'=>'tgz')));
}
/*
FRIPAPER

	@name fax_faxfile_send
	@synopsis
		发送传真文件
		<code>	
  $retrun = fax_faxfile_send($account,$number,$filetype,$filebinary,$sendannounce)
		</code>
	@param $account
		传真发送的帐户,如果不填写以匿名帐户方式发送.
	@param $number
		对方传真的号码,拨号流程将通过拨出路由进行控制.
	@param $filetype
		传真文件的类型,目前只支持填写tiff
	@param $filebinary
		传真文件的类型的内容,binary格式
	@param $sendannounce
		传真发送提示语音,建议默认3次
	@return $retrun
		@item  init 'faxid ' : 传真队列编号

ENDPAPER
*/
function fax_faxfile_send($account,$number,$filetype,$filebinary,$sendannounce)
{
	global $freeiris_conf;
	global $dbcon;

	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	if ($filetype != 'tiff')
		return(rpcreturn(500,"do not support file format",'sdf',null));

	if (is_dir($asterisk_conf->get('directories','astspooldir').'/fax/') == false) {
		$oldumask=umask(0);
		mkdir($asterisk_conf->get('directories','astspooldir').'/fax/',0777);
		umask($oldumask);
	}

	//检测这个帐户的fax目录是否存在,如果不存在就创建一个.
	if (trim($account) != "" && is_dir($asterisk_conf->get('directories','astspooldir').'/fax/'.$account) == false) {
		$oldumask=umask(0);
		mkdir($asterisk_conf->get('directories','astspooldir').'/fax/'.$account,0777);
		umask($oldumask);
	}

	//提示音默认
	if (trim($sendannounce) == "") {
		$sendannounce = 3;
	}

	//将fax文件存储到这个目录中,并且在队列表中创建队列.
	$folder = $asterisk_conf->get('directories','astspooldir').'/fax/'.$account;
	$filename = uniqid().'.tiff';
	file_put_contents($folder.'/'.$filename,$filebinary);

	//------------------------------------------------------更新一般数据库资料
	$sql = "insert into faxqueue set cretime = now(),mode = 0,status = 0,retry = 0, ".
			"accountcode = '".$account."',".
			"number = '".$number."',".
			"sendannounce = '".$sendannounce."',".
			"filename = '".$filename."'";
	$result=mysql_query($sql);
	if (!$result)
		{return(rpcreturn(500,mysql_error(),100,null));}
	//返回插入的数据id(方法一)
	$faxid=mysql_insert_id();
	
	//(方法二)
	//$rs=mysql_query('SELECT LAST_INSERT_ID()');
        //$faxid=mysql_fetch_array($rs);

	//结束
	return(rpcreturn(200,null,null,array('faxid'=>$faxid)));
}
/*
FRIPAPER

	@name fax_faxfile_download
	@synopsis
		传真文件下载
		<code>	
  $retrun = fax_faxfile_download($faxid)
		</code>
	@param $faxid
		传真队列编号
	@return $retrun
		@item  binarystring 'filestream' : 二进制数据文件内容
		@item  string 'filename' : 文件名称
ENDPAPER
*/
function fax_faxfile_download($faxid)
{
	global $freeiris_conf;
	global $dbcon;
        
	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	//执行sql
	$result=mysql_query("select * from faxqueue where id = '".$faxid."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
	    return(rpcreturn(500,"can't find file",113,null));

	$filepath = $asterisk_conf->get('directories','astspooldir').'/fax/'.$resdata['accountcode'].'/'.$resdata['filename'];

    //打开文件body
    if (file_exists($filepath)) {
	    $filestream = file_get_contents($filepath);
    }

	return(rpcreturn(200,null,null,array('filestream'=>$filestream,'filename'=>$resdata['filename'])));
}

?>