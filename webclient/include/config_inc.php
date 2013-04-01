<?php
/*
    $Id$
	----------------------------------------------
	以下部分是freeiris web client的配置
*/

# freeiris2 extra interface的HTTP访问地址
$friconf['friextra_urlbase']='http://127.0.0.1:'. $_SERVER['SERVER_PORT'] .'/friextra';

# web部分的设置
$friconf['title']='Freeiris2 - 真正的中文开源通信平台';
$friconf['language']='SimplifiedChinese';
$friconf['session_expiry']=30;
$friconf['enable_mainalert']=true;

#分页显示每页有多少条记录
$friconf['cols_in_page']=60;


#MENU菜单
$friconf['menutable']['exten']=array('category'=>'分机功能','submenu'=>array());
array_push($friconf['menutable']['exten']['submenu'],array('url'=>'exten_manage.php','name'=>'分机管理'));
array_push($friconf['menutable']['exten']['submenu'],array('url'=>'exten_group.php','name'=>'分机分组'));
array_push($friconf['menutable']['exten']['submenu'],array('url'=>'exten_router.php','name'=>'拨出规则'));

$friconf['menutable']['trunk']=array('category'=>'外线功能','submenu'=>array());
array_push($friconf['menutable']['trunk']['submenu'],array('url'=>'trunk_manage.php','name'=>'中继管理'));
array_push($friconf['menutable']['trunk']['submenu'],array('url'=>'trunk_router.php','name'=>'拨入规则'));

$friconf['menutable']['pbx']=array('category'=>'PBX功能','submenu'=>array());
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_features.php','name'=>'功能热键'));
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_billing.php','name'=>'话费对帐单'));
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_soundmanager.php','name'=>'语音文件'));
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_musicmanager.php','name'=>'等待音乐'));
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_uservoice.php','name'=>'语音信箱'));
array_push($friconf['menutable']['pbx']['submenu'],array('url'=>'pbx_fax.php','name'=>'数字传真'));

$friconf['menutable']['acd']=array('category'=>'电脑话务','submenu'=>array());
array_push($friconf['menutable']['acd']['submenu'],array('url'=>'acd_conference.php','name'=>'电话会议'));
array_push($friconf['menutable']['acd']['submenu'],array('url'=>'acd_queue.php','name'=>'呼叫队列'));
array_push($friconf['menutable']['acd']['submenu'],array('url'=>'acd_ivrmenu.php','name'=>'IVR菜单'));
array_push($friconf['menutable']['acd']['submenu'],array('url'=>'acd_record.php','name'=>'自动录音'));
array_push($friconf['menutable']['acd']['submenu'],array('url'=>'acd_outgoing.php','name'=>'自动外呼'));

$friconf['menutable']['option']=array('category'=>'系统选项','submenu'=>array());
array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_general.php','name'=>'通话参数'));
array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_hardware.php','name'=>'硬件语音板'));
array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_voip.php','name'=>'VOIP协议'));
//array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_diagnosis.php','name'=>'诊断评估'));
array_push($friconf['menutable']['option']['submenu'],array('url'=>'analyze_callsession.php','name'=>'会话统计'));
array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_static.php','name'=>'通话清单'));
// disable change file
//array_push($friconf['menutable']['option']['submenu'],array('url'=>'option_advanced.php','name'=>'高级设置'));

$friconf['menutable']['other']=array('category'=>'当前帐户','submenu'=>array());
array_push($friconf['menutable']['other']['submenu'],array('url'=>'main.php','name'=>'返回首页'));
array_push($friconf['menutable']['other']['submenu'],array('url'=>'admin_profile.php','name'=>'个人信息'));
array_push($friconf['menutable']['other']['submenu'],array('url'=>'admin_manage.php','name'=>'管理帐户'));
array_push($friconf['menutable']['other']['submenu'],array('url'=>'pbx_reload.php','name'=>'重启系统'));
array_push($friconf['menutable']['other']['submenu'],array('url'=>'index.php?action=do_logout','name'=>'退出系统'));

?>
