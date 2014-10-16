<?php
/*******************************************************************************
* login.common.php
* login参数信息文件
* login parameter file
* 功能描述
	根据用户的原则初始化SESSION, 初始化语言类, 默认使用 en_US
* Function Desc
	set language SESSION, initialize language class, use en_US by default

* Revision 0.0442  2007/09/14 07:55:00  last modified by solo
* Desc: modify session scripts to be compatible with trixbox
* 描述: 改进了对session的处理以兼容trixbox2.0, php5

* Revision 0.044  2007/09/7 17:55:00  last modified by solo
* Desc: add some comments
* 描述: 增加了一些注释信息

********************************************************************************/

header('Content-Type: text/html; charset=utf-8');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());

require_once ("include/xajax.inc.php");
require_once ('include/localization.class.php');

//session_start();

if (isset($_SESSION['curuser']['country']) )
	$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');
else
	$GLOBALS['locate']=new Localization('en','US','login');


$xajax = new xajax("login.server.php");
$xajax->registerFunction("processForm");	 //register xajax_processForm
$xajax->registerFunction("init");				//register xajax_init
$xajax->registerFunction("setLang");
$xajax->registerFunction("clearDynamicMode");
$xajax->registerFunction("calculateOntime");
?>