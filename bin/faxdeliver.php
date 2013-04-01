<?php
/*
#
#	Freeiris2 -- An Opensource telephony project.
#	Copyright (C) 2005 - 2009, Sun bing.
#	Sun bing <hoowa.sun@gmail.com>
#
#	See http://www.freeiris.org for more information about
#	the Freeiris project.
#
#	This program is free software, distributed under the terms of
#	the GNU General Public License Version 2. See the LICENSE file
#	at the top of the source tree.
#
#	Freeiris2 -- 开源通信系统
#	本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
#	请查阅LICENSE文件。
#
#
#   $Id: outgoing.php 253 2009-07-08 04:07:46Z hoowa $
#
*/
require_once(dirname(__FILE__)."/../lib/class.phpmailer.php");
require_once(dirname(__FILE__)."/../lib/asteriskconf.inc.php");
require_once(dirname(__FILE__)."/../lib/freeiris_ami_inc.php");

$mail = new PHPMailer();
$mail->PluginDir=dirname(__FILE__)."/../lib/";

//version
$VERSION='1.0';

//debug??
if (array_key_exists(1,$_SERVER['argv']) == true && $_SERVER['argv'][1] == '--verbose') $verbose = true;

message('notice',"fax deliver by hoowa sun ".$VERSION);

//读出基本配置文件
$freeiris_conf = new asteriskconf();
if ($freeiris_conf->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
	exit;
$asterisk_conf = new asteriskconf();
if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
	exit;

//查看自己是否已经在内存中了
if (check_processes($freeiris_conf->get('general','freeiris_root')."/logs/faxdeliver.pid",$_SERVER['SCRIPT_NAME']) == true) {
	message('error','processes alreadly in memory , exists!');
}
system("echo '".getmypid()."' > ".$freeiris_conf->get('general','freeiris_root')."/logs/faxdeliver.pid");


#删除掉所有三个月以上的旧数据
//mysql_query("DELETE FROM faxqueue WHERE cretime <= '".date("Y-m-d",strtotime('-3 month'))." 00:00:00'",$db);

//生成发送队列准备
$q_faxdeliver=array();
//取出一条记录进行处理
$db = mysql_connect($freeiris_conf->get('database','dbhost'),$freeiris_conf->get('database','dbuser'),$freeiris_conf->get('database','dbpasswd'));
if (!$db)
	message('error','Could not connect: ' . mysql_error());
mysql_select_db($freeiris_conf->get('database','dbname'),$db);
$result = mysql_query("select * from faxqueue where mode = 0 and status = 0 order by id asc",$db);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	message('notice',"NEW SENDFAX ID (".$row['id'].")");
	message('notice',"        NUMBER (".$row['number'].")");
	message('notice',"   ACCOUNTCODE (".$row['accountcode'].")");
	message('notice',"      FILENAME (".$row['filename'].")");
	message('notice',"       CRETIME (".$row['cretime'].")");
	message('notice',"        STATUS (".$row['status'].")");

	array_push($q_faxdeliver,$row);

}
mysql_free_result($result);

//connect ami with stream timeout 8 sec
$asm = new freeiris_ami('freeiris','freeiris','localhost',5038,8);
if(!$asm->connect()) {
	message('error','ami connect failed');
}
$asm->send_request('Events',array('EVENTMASK'=>'off'));

//发送队列处理
foreach ($q_faxdeliver as $key => $eachone) {

	//允许前一个发送X秒等待时间,防止超过传真授权许可数量
	if ($key > 0) {
		message('notice',"NEXT SEND WILL BE START AFTER 10sec");
		sleep(10);
	}

	//检测系统已有并发量和限制
	$amimessages = $asm->send_request('command',array('ActionID'=>uniqid(),'command'=>'fax show stats'));
	$currentfax=null;
	$licensedfax=null;
	foreach (preg_split("/\n/",$amimessages['data']) as $value) {
		if (preg_match("/Current\sSessions\s+:\s([0-9]+)/",$value,$matches)) {
			$currentfax=$matches[1];
		}
		if (preg_match("/Licensed\sChannels\s+:\s([0-9]+)/",$value,$matches)) {
			$licensedfax=$matches[1];
		}
	}
	if ($currentfax == null || $licensedfax = null) { //取出参数失败但是程序继续执行
		//再检测一下是否有sendfax指令
		$amimessages = $asm->send_request('command',array('ActionID'=>uniqid(),'command'=>'show application SendFax'));
		if (preg_match("/not\sregistered/",$amimessages['data'])) {
			message('error','not found fax application');
		}
		message('notice','get licensed failed ignore!');
	} elseif ($currentfax == $licensedfax) { //已经达到并发量,本程序退出
		message('error','fax licensed $currentfax of $licen');
	}

	message('notice',$eachone['accountcode']." SENDING ".$eachone['number']);

	//进行发送
	$amimessages = $asm->send_request('Originate',array('ActionID'=>uniqid(),
						'Channel'=>"LOCAL/".$eachone['number']."@sub-outgoing/n\n",
						'CallerID'=> $eachone['accountcode']." <".$eachone['accountcode'].">",
						'Timeout'=>($freeiris_conf->get('fastagi','dial_ringtime')*1000),
						'Context'=>'app-sendfax',
						'Exten'=>'inprocess',
						'Priority'=>'1',

						'Variable'=>"CHANNEL(language)=cn|FRI2_FAXQUEUEID=".$eachone['id'].
								"|FRI2_FAXSENDANNOUNCE=".$eachone['sendannounce'],
						'Async'=>'true',
					));
	//更新记录
	if ($eachone['status'] != 1) {
		mysql_query("update faxqueue set status = 1 where id = '".$eachone['id']."'",$db);
	}

}

$asm->disconnect();

mysql_close($db);


//-----------------------------------信息输出
function message($type,$message)
{
global $verbose;

	if ($verbose==true) {
		echo "$message\n";
	}
	if ($type == 'error') {
		exit;
	}

return(true);
}

function check_processes($pid,$keyname)
{

	$exists = false;

	if (is_file($pid)==true) {

		$pid_number = `cat $pid`;
		$pid_number = trim($pid_number);

		#如果存在这个进程
		if (is_file("/proc/".$pid_number."/cmdline")) {
			$pid_cmdline = `cat /proc/$pid_number/cmdline`;
			$pid_cmdline = trim($pid_cmdline);

			$scriptname = preg_replace("/\//","\\\/",$_SERVER['SCRIPT_NAME']);

			#如果这个进程的名字正好是指定的名字
			if (preg_match("/".$scriptname."/i",$pid_cmdline)) {
				$exists = true;
			#这个进程是其他进程(未启动)
			} else {
				$exists = false;
			}
		#不存在这个进程(未启动)
		} else {
			$exists = false;
		}
	}

return($exists);
}

?>
