<?php
include('asteriskconf.inc.php');

$sip = new asteriskconf();
	
	$sip->parse_in_file('t1.conf');

// $sip->assign_cleanfile();
// $sip->assign_matchreplace('secret=1234','secret=5678');
// $sip->assign_editkey('2000','type','peer');
// $sip->assign_delkey('pick','deleteme');
// $sip->assign_replacesection('replaceme',"type=friend\nport=5060\n");
//$sip->assign_replacesection('replaceme',array('type=friend','port=5060'));
// $sip->assign_replacesection('replaceme',array('type'=>'friend','port'=>'5060'));
// $sip->assign_delsection('1000');
//	$sip->assign_addsection('1000');
//	$sip->assign_addsection('9000');
//$sip->assign_append('down',NULL,'#tophead',NULL);
//$sip->assign_append('up','general','language=en',NULL);
//$sip->assign_append('down','general','language=en',NULL);
//$sip->assign_append('foot','general','language=en',NULL);
//$sip->assign_append('foot',NULL,'language=en',NULL);
//$sip->assign_append('up','general','language=cn',array('useragent','as5300'));
//$sip->assign_append('down','general','language=cn',array('useragent','as5300'));
//$sip->assign_append('over','general','language=cn',array('useragent','as5300'));

//	$sip->debug();

//	$sip->keep_resource_array=false;
	$sip->save_file('output.conf');
//	print $sip->errstr;
?>