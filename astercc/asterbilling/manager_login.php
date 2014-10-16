<?php
/*******************************************************************************
********************************************************************************/

require_once('manager_login.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
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
			xajax.$('loginButton').disabled=true;
			xajax.$('loginButton').value=xajax.$('onclickMsg').value;
			xajax_processForm(xajax.getFormValues("loginForm"));
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

	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

</head>
	<body onload="init();" style="margin-top: 80px;">
	 <div align="center">
	 		<div id="formDiv">
			<form id="loginForm" action="javascript:void(null);" onsubmit="loginSignup();">
		  <div class="login_in">
				<div id="titleDiv"></div>
				<div class="left">
					<table width="410" height="143" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<th width="70" height="58" scope="col">&nbsp;</th>
							<th width="" valign="bottom" scope="col">
								<div name="usernameDiv" id="usernameDiv" align="right"></div>
							</th>
							<th width="" valign="bottom" scope="col">
								<div align="left">
								<input name="username" type="text" id="username" style="width:150px;height:14px" />
								</div>
							</th>
						</tr>
						<tr>
							<td height="49">&nbsp;</td>
							<th><div name="passwordDiv" id="passwordDiv" align="right"></div></th>
							<td>
								<div align="left">
									<input type="password" name="password" id="password" style="width:150px;height:14px" />
								</div>
							</td>
						</tr>
						<tr>
							<td height="36">&nbsp;</td>
							<th><div name="validcodeDiv" id="validcodeDiv" align="right"></div></th>
							<td>
								<div align="left">
									<input type="text" name="code" id="code" style="width:50px;height:14px" />
								</div>
							</td>
						</tr>
						<tr>
							<td height="">&nbsp;</td>
							<th></th>
							<td><div align="left"><img id="imgCode" name="imgCode" src=""></div></td>
						</tr>
						<tr>
							<td height="36">&nbsp;</td>
							<th><div name="pagestyleDiv" id="pagestyleDiv" align="right"></div></th>
							<td><div align="left" id="pagestyleSelectDiv">
									<SELECT name="pagestyle" id="pagestyle" style="width:120px;">
										<OPTION value="classic">Classic</OPTION>
										<OPTION value="simple">Simple</OPTION>
									</SELECT>
								</div>
							</td>
						</tr>
						<tr>
							<td height="36">&nbsp;</td>
							<th><div name="locateDiv" id="locateDiv" align="right"></div></th>
							<td>
								<div align="left">
									<SELECT name="locate" id="locate" onchange="setlanguage();" style="width:120px;">
										<OPTION value="en_US">English</OPTION>
										<OPTION value="cn_ZH">简体中文</OPTION>
										<OPTION value="ch_FR">Français</OPTION>
										<OPTION value="es_MX">Spanish</OPTION>
										<OPTION value="de_GER">Deutsch</OPTION>
										<OPTION value="pt_BR">Portugês Brasileiro</OPTION>
									</SELECT>
								</div>
							</td>
						</tr>
						<tr>
							<td></td>
							<th></th>
							<td>
								<div align="left">
									<input type="checkbox" value="forever" id="rememberme" name="rememberme">&nbsp;&nbsp;
									<span name="remembermeDiv" id="remembermeDiv"></span>
									<input id="loginButton" name="loginButton" type="submit" value=""/>
									<input id="onclickMsg" name="onclickMsg" type="hidden" value=""/>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div class="right">&nbsp;</div>
				<div class="right">&copy; 2004-2011 <a href="http://astercc.org" target="_blank">astercc.org</a></div>
				<div class="right">version: 0.16 in asterCC 0.21</div>
		  </div></form></div>
	    </div>
	</body>
</html>
