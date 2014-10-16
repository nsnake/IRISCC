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
date_default_timezone_set('UTC');
require_once(dirname(__FILE__)."/../lib/class.phpmailer.php");
require_once(dirname(__FILE__)."/../lib/asteriskconf.inc.php");

$mail = new PHPMailer();
$mail->PluginDir=dirname(__FILE__)."/../lib/";

//version
$VERSION='1.0';

//debug??
if (array_key_exists(1,$_SERVER['argv']) == true && $_SERVER['argv'][1] == '--verbose') $verbose = true;

message('notice',"freeiris fax sender by hoowa sun ".$VERSION);

//读出基本配置文件
$freeiris_conf = new asteriskconf();
if ($freeiris_conf->parse_in_file('/etc/freeiris2/freeiris.conf')==false)
	exit;
$asterisk_conf = new asteriskconf();
if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
	exit;

$faxfullpath = $asterisk_conf->get('directories','astspooldir').'/fax/';

$lasttime = date("Y-m-d H:i:s",(time()-$freeiris_conf->get('voicemail','mailer_checktime')));

//是否发送
if ($freeiris_conf->get('fax','deliver')=='hold')
	message('error',"disabled mailer");

//先期检测通用参数
if ($freeiris_conf->get('voicemail','mailer_attachvoice')=='yes') {
	$mailer_attachvoice=true;
} else {
	$mailer_attachvoice=false;
}
message('notice',"Variable mailer_attachvoice = ".$mailer_attachvoice);

//本程序的基本mail参数设置遵从VoiceMail部分的设置
$mailer_from=null;
if ($freeiris_conf->get('voicemail','mailer_from')=='') {
	message('notice',"Error no mailer_from");
} else {
	$mailer_from=$freeiris_conf->get('voicemail','mailer_from');
}
message('notice',"Variable mailer_from = ".$mailer_from);

//通过sendmail发送
if ($freeiris_conf->get('voicemail','mailer')=='sendmail') {
	message('notice',"Mailer type sendmail");
	//以Sendmail模式执行
	$mail->IsSendmail();

//------------------------------------------------------------------------------------通过smtp发送
} elseif ($freeiris_conf->get('voicemail','mailer')=='smtp') {
	message('notice',"Mailer type smtp");

	if ($freeiris_conf->get('voicemail','smtp_host')=='') {
		message('notice',"Error no smtp_host");
	} else {
		$smtp_host=$freeiris_conf->get('voicemail','smtp_host');
	}
	message('notice',"Variable smtp_host = ".$smtp_host);

	if ($freeiris_conf->get('voicemail','smtp_port')=='') {
		message('notice',"Error no smtp_port");
	} else {
		$smtp_port=$freeiris_conf->get('voicemail','smtp_port');
	}
	message('notice',"Variable smtp_port = ".$smtp_port);

	if ($freeiris_conf->get('voicemail','smtp_auth')=='true') {
		$smtp_auth=true;
	} else {
		$smtp_auth=false;
	}
	message('notice',"Variable smtp_auth = ".$smtp_auth);

	if ($freeiris_conf->get('voicemail','smtp_username')=='' && $smtp_auth == true) {
		message('notice',"Error no smtp_username");
	} else {
		$smtp_username=$freeiris_conf->get('voicemail','smtp_username');
	}
	message('notice',"Variable smtp_username = ".$smtp_username);

	if ($freeiris_conf->get('voicemail','smtp_password')=='' && $smtp_auth == true) {
		message('notice',"Error no smtp_password");
	} else {
		$smtp_password=$freeiris_conf->get('voicemail','smtp_password');
	}
	message('notice',"Variable smtp_password = ".$smtp_password);

	$mail->IsSMTP();
	$mail->Host = $smtp_host;
	$mail->SMTPAuth = $smtp_auth;
	$mail->Username = $smtp_username;
	$mail->Password = $smtp_password;
}


//连接数据库
$db = mysql_connect($freeiris_conf->get('database','dbhost'),$freeiris_conf->get('database','dbuser'),$freeiris_conf->get('database','dbpasswd'));
if (!$db)
	message('error','Could not connect: ' . mysql_error());
mysql_select_db($freeiris_conf->get('database','dbname'),$db);


//测试有多少个news mail
$result = mysql_query("select count(*) from faxqueue where mode = 1 and status = 3 and mailprocessed = 0 and cretime <= '".$lasttime."'",$db);
$countnew = mysql_fetch_array($result);
if ($countnew[0] <= 0)
	message('error','NOT NEW MAIL');
message('notice',"NEW MAIL (".$countnew[0].")");

//connect to SMTP server
if ($verbose==true) {
	$mail->SMTPDebug = true;
	message('notice','===========SMTP DEBUG ENABLED===========');
}


//----------------------------------------------------------------------------------执行发送流程
//组织数据
for ($i=1;$i<=$countnew[0];$i++) {

	//取出一条
	$result = mysql_query("select * from faxqueue where mode = 1 and status = 3 and mailprocessed = 0 and cretime <= '".$lasttime."' order by cretime asc limit ".($i-1).",1",$db);
	$eachmail = mysql_fetch_array($result);
	if (!$eachmail)
		message('error','THE END OF NEW MAIL');
	message('notice',"[".$eachmail['id']."]");

	//产生文件名称和地址
	$filename = $faxfullpath.'/'.$eachmail['accountcode'].'/'.$eachmail['filename'];

	// 没有文件 更新记录为发送完成
	if (file_exists($filename)==false || filesize($filename) < 1) {
		message('notice',"[".$eachmail['id']."] NO FILE : $filename");
		mysql_query("update faxqueue set mailprocessed = 1 where id = '".$eachmail['id']."'",$db);
		continue;
	}

	// 取用户信息
	$result = mysql_query("select info_email from extension where accountcode = '".$eachmail['accountcode']."'",$db);
	$thisuser = mysql_fetch_array($result);

	// 找不到用户 更新记录为发送完成
	if (!$thisuser) {
		message('notice',"[".$eachmail['id']."] NO USER : ".$eachmail['accountcode']);
		mysql_query("update faxqueue set mailprocessed = 1 where id = '".$eachmail['id']."'",$db);
		continue;
	}

	// 用户没有Email 保留
	if (trim($thisuser['info_email']) == '' || !preg_match("/\@/",$thisuser['info_email'])) {
		message('notice',"[".$eachmail['id']."] NO EMAIL : ".$eachmail['accountcode']);
		continue;
	}

	// 有文件,找到用户,用户有Email
	message('notice',"[".$eachmail['id']."] Mailling : ".$thisuser['info_email']);
	
	// 邮件发送参数
	$mail->CharSet = 'UTF-8';
	$mail->Encoding = "base64";
	$mail->From = $mailer_from;
	$mail->FromName = "auto_voicemail";
	#$mail->IsHTML(true);
	$mail->AddAddress($thisuser['info_email']);
	$mail->Subject = preg_replace("/\\\$number/",$eachmail['accountcode'],$freeiris_conf->get('fax','mailer_subject'));
	#$mail->AltBody = "Please See in HTML style";

	// 加附件
	if ($mailer_attachvoice==true) {
		$mail->AddAttachment($filename,$eachmail['filename']);
	}

	#日期处理
	$datetime = preg_split("/\ /",$eachmail['cretime']);
	$date = preg_split("/\-/",$datetime[0]);
	$time = preg_split("/\:/",$datetime[1]);

	// 设置mail内容
	$mailbody = $freeiris_conf->get('fax','mailer_body');
	#设置主叫号码
	$callerid = $eachmail['number'];
	#设置
	$mailbody = preg_replace("/\\\\n/","\n",$mailbody);
	$mailbody = preg_replace("/\\\\t/","\t",$mailbody);
	$mailbody = preg_replace("/\\\$number/",$eachmail['accountcode'],$mailbody);
	$mailbody = preg_replace("/\\\$month/",$date[1],$mailbody);
	$mailbody = preg_replace("/\\\$day/",$date[2],$mailbody);
	$mailbody = preg_replace("/\\\$hour/",$time[0],$mailbody);
	$mailbody = preg_replace("/\\\$minute/",$time[1],$mailbody);
	$mailbody = preg_replace("/\\\$callerid/",$callerid,$mailbody);

	#设置
	$mail->Body = $mailbody;

	// 发送失败
	if(!$mail->Send())
	{
		message('notice',"Mailer Error: " . $mail->ErrorInfo);
		continue;

	// 发送成功
	} else {
		mysql_query("update faxqueue set mailprocessed = 1 where id = '".$eachmail['id']."'",$db);
		continue;
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
