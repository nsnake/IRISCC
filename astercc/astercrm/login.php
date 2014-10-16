<?php
/*******************************************************************************
* login.php
* 用户登入界面文件
* user login page
* 功能描述
	 首先载入所有元素，然后调用javascripr的init函数初始化页面上的文字信息

* Function Desc
	first load all elements, and then call javascript init function to initialize words on this page

* Page elements
* Form:							
									loginForm
* input field:					
									username			->	 username
									password			->	 password
									locate				->	 language
* hidden field:				
									onclickMsg			-> save message when user click login button
* button:						
									loginButton		-> user login button
* div:							
									titleDiv				->	 login form title
									usernameDiv		-> username
									passwordDiv		-> password
									locateDiv			-> locate
* javascript function:		
									loginSignup	
									init					 


* Revision 0.0443  2007/10/8 17:55:00  last modified by solo
* Desc: add a div to display copyright



* Revision 0.044  2007/09/7 17:55:00  last modified by solo
* Desc: add some comments
* 描述: 增加了一些注释信息
********************************************************************************/

require_once('login.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>asterCC</title>
		<meta http-equiv="Content-Language" content="utf-8" />
		<?php $xajax->printJavascript('include/'); ?>
		<script type="text/javascript">
		/**
		*  login function, launched when user click login button
		*
		*  	@param null
		*	@return false
		*/
		function loginSignup()
		{
			//xajax.$('loginButton').disabled=true;
			//xajax.$('loginButton').value=xajax.$('onclickMsg').value;
			xajax_processForm(xajax.getFormValues("loginForm"));
			//return false;
		}
		
		function selectmode(msg)
		{
			if(confirm(msg)){
				window.location.href="portal.php";
				return true;
			}
			xajax_clearDynamicMode();
			return false;
		}

		/**
		*  init function, launched after page load
		*
		*  	@param null
		*	@return false
		*/
		function init(){
			xajax_init(xajax.getFormValues("loginForm"));
			return false;
		}
		function setlanguage(){
			xajax_setLang(xajax.getFormValues("loginForm"));			
			return false;
		}
		</script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<link href="skin/default/css/login.css" rel="stylesheet" type="text/css" />

</head>
	<body onload="init();">
		<div id="loginbody">
		<div id="astercclogo"><img src="skin/default/images/asterCC_logo.gif" /></div>		
		<div id="logininfo">
		<form id="loginForm" action="javascript:loginSignup();"><!--  onsubmit="loginSignup();" -->
		<div class="text01" id="logintip" id="logintip"></div>
		<table border="0" cellspacing="0" cellpadding="2">
			<tr>
			  <td width="110"><div name="usernameDiv" id="usernameDiv" align="right"></div></td>
			  <td><input name="username" type="text" id="username" class="input"/></td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div name="passwordDiv" id="passwordDiv" align="right"></div></td>
			  <td><input type="password" name="password" id="password" class="input" /></td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div name="remembermeboxDiv" id="remembermeboxDiv" align="right"><input type="checkbox" value="forever" id="rememberme" name="rememberme"></div></td>
			  <td><div name="remembermeDiv" id="remembermeDiv" align="left"></div></td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div name="languageDiv" id="languageDiv" align="right"></div></td>
			  <td><SELECT name="locate" id="locate" onchange="setlanguage();" class="select">
					 <OPTION value="en_US">English</OPTION>
					 <OPTION value="cn_ZH">简体中文</OPTION>
					 <OPTION value="de_GER">Germany</OPTION>
				   </SELECT></td>
			  <td><div id="loginDiv" name="loginDiv" ></div></td>
			</tr>
		  </table>
		<div id="copyright"><br><span style="font-size:20px;">&copy;</span> 2004-2010 astercc.org </div>
		</form>
		</div>
		<div id="sonicwelllogo">version: 0.076 in asterCC 0.21</div>
		<!--<div id="sonicwelllogo"><img src="skin/default/images/sonicwell_logo.gif" /></div>-->
		</div>
	</body>
</html>