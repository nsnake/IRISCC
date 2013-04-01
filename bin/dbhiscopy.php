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
#   $Id: vmsender.php 247 2009-07-02 08:53:24Z hoowa $
#
*/
require_once(dirname(__FILE__)."/../lib/asteriskconf.inc.php");

//version
$VERSION='1.0';

$dblist = array(
				array('from'=>'billinginvoice','to'=>'billinginvoice_history','datefield'=>'cretime','deletewhenmoved'=>true),
				array('from'=>'cdr','to'=>'cdr_history','datefield'=>'calldate','deletewhenmoved'=>true),
				array('from'=>'callsession','to'=>'callsession_history','datefield'=>'cretime','deletewhenmoved'=>true),
				array('from'=>'callsession_acts','to'=>'callsession_acts_history','datefield'=>'acttime','deletewhenmoved'=>true),
			);

//debug??
if (array_key_exists(1,$_SERVER['argv']) == true && $_SERVER['argv'][1] == '--verbose') $verbose = true;

message('notice',"freeiris database history copy by hoowa sun ".$VERSION);

//读出基本配置文件
$freeiris_conf = new asteriskconf();
if ($freeiris_conf->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
	exit;
$asterisk_conf = new asteriskconf();
if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
	exit;

//连接数据库
$db = mysql_connect($freeiris_conf->get('database','dbhost'),$freeiris_conf->get('database','dbuser'),$freeiris_conf->get('database','dbpasswd'));
if (!$db)
	message('error','Could not connect: ' . mysql_error());
mysql_select_db($freeiris_conf->get('database','dbname'),$db);



//---------------------------------------------------------------历史数据转存程序
$beforeyear = (Date('Y')-1);

// 转存12个月前的数据
foreach ($dblist as $eachone) {
	message('notice',"Moving to History From Table '".$eachone['from']."' To '".$eachone['to']."'");
	$result = mysql_query("INSERT INTO ".$eachone['to']." SELECT * FROM ".$eachone['from']." where ".$eachone['datefield']." < '".$beforeyear."-".Date("m")."-01 00:00:00'");
	if ($result && $eachone['deletewhenmoved']==true) {
		mysql_query("DELETE FROM ".$eachone['from']." where ".$eachone['datefield']." < '".$beforeyear."-".Date("m")."-01 00:00:00'");
	}
}


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

?>
