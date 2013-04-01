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
#   $Id: outgoing.php 484 2010-08-17 08:37:26Z hoowa $
#
*/
require_once(dirname(__FILE__)."/../lib/class.phpmailer.php");
require_once(dirname(__FILE__)."/../lib/asteriskconf.inc.php");
require_once(dirname(__FILE__)."/../lib/freeiris_ami_inc.php");

//version
$VERSION='1.0';

//debug??
if (array_key_exists(1,$_SERVER['argv']) == true && $_SERVER['argv'][1] == '--verbose') $verbose = true;

message('notice',"freeiris outgoing sender by hoowa sun ".$VERSION);

//读出基本配置文件
$freeiris_conf = new asteriskconf();
if ($freeiris_conf->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
	exit;
$asterisk_conf = new asteriskconf();
if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
	exit;

//查看自己是否已经在内存中了
if (check_processes($freeiris_conf->get('general','freeiris_root')."/logs/outgoing.pid",$_SERVER['SCRIPT_NAME']) == true) {
	message('error','processes alreadly in memory , exists!');
}
system("echo '".getmypid()."' > ".$freeiris_conf->get('general','freeiris_root')."/logs/outgoing.pid");

//连接数据库
$db = mysql_connect($freeiris_conf->get('database','dbhost'),$freeiris_conf->get('database','dbuser'),$freeiris_conf->get('database','dbpasswd'));
if (!$db)
	message('error','Could not connect: ' . mysql_error());
mysql_select_db($freeiris_conf->get('database','dbname'),$db);

#删除掉所有旧数据
#mysql_query("DELETE FROM outgoing WHERE cretime <= '".date("Y-m-d",strtotime('-1 month'))." 00:00:00'",$db);

//找出适合的发送分组
$q_outgoing_number = array();
$result = mysql_query("select * from outgoing where (tune=0 or tune=2) and startime <= now() order by startime asc",$db);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	//如果这个记录calledcount=numbercount则设置tune为1处理下一个
	if ($row['calledcount']==$row['numbercount']) {
		mysql_query("update outgoing set tune=1 where id = '".$row['id']."'");
		continue;
	}
	//如果这个表tune为0则修改为2
	if ($row['tune']=='0') {
		mysql_query("update outgoing set tune=2 where id = '".$row['id']."'");
	}

	#取出这个队列的被呼叫号码
	$row['members']=array();
	$result_members = mysql_query("select * from outgoing_members where outgoingid = '".$row['id']."' and status = 0 limit ".$row['concurrent'],$db);
	while ($row_members = mysql_fetch_array($result_members, MYSQL_ASSOC)) {
		array_push($row['members'],$row_members);
	}
	mysql_free_result($result_members);
	//如果存在被叫队列就记录
	if (count($row['members']) > 0) {
		array_push($q_outgoing_number,$row);

		message('notice',"NEW OUTGOING ID (".$row['id'].")");
		message('notice',"       CALLBACK (".$row['localnumber'].")");
		message('notice',"       STARTIME (".$row['startime'].")");
		message('notice',"        MEMBERS (".$row['numbercount'].")");
		message('notice',"         CALLED (".$row['calledcount'].")");

	//如果不存在或其他原因直接设置此队列为完成状态
	} else {
		mysql_query("update outgoing set tune=1 where id = '".$row['id']."'");
	}
}
mysql_free_result($result);

//connect ami with stream timeout 8 sec
$asm = new freeiris_ami('freeiris','freeiris','localhost',5038,8);
if(!$asm->connect()) {
	message('error','ami connect failed');
}
$asm->send_request('Events',array('EVENTMASK'=>'off'));

//开始处理
foreach ($q_outgoing_number as $eachone) {

	//读出每个member
	$i=0;
	foreach ($eachone['members'] as $member) {
		message('notice',$eachone['name']." CALLING ".$member['number']);
		$amimessages = $asm->send_request('Originate',array('ActionID'=>uniqid(),
							'Channel'=>"LOCAL/".$member['number']."@sub-outgoing/n\n",
							'Context'=>'sub-outgoing-callback',
							'Exten'=>$eachone['localnumber'],
							'Priority'=>'1',
							'Timeout'=>($eachone['outgoing_waittime']*1000),
							'CallerID'=> $eachone['outgoing_callerid']." <".$eachone['outgoing_callerid'].">",
							'Variable'=>"CHANNEL(language)=cn|FRI2_OUTGOING_MEMBERID=".$member['id'].
									"|FRI2_OUTGOING_ID=".$eachone['id'].
									"|FRI2_OUTGOING_NUMBER=".$member['number'],
							'Async'=>'true',
						));

		//更新members记录
		mysql_query("update outgoing_members set status=1 where id = '".$member['id']."'",$db);
		$i++;
	}

	//是否彻底完成
	$tune = 2;
	if (($i+$eachone['calledcount'])==$eachone['numbercount']) {
		$tune = 1;
	}
	//更新主表记录
	mysql_query("update outgoing set calledcount=calledcount+".$i.",tune='".$tune."' where id = '".$eachone['id']."'",$db);

}
$asm->disconnect();

//执行完成
message('notice',"THE END");

exit;

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
