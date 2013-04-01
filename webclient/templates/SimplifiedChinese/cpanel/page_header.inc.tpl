<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<HTML>
 <HEAD>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <TITLE> <% $title %> </TITLE>
	<link href="../css/common.css" rel="stylesheet" type="text/css" media="screen, projection" />
	<link href="../css/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen, projection" />
	<link href="../css/ddsmoothmenu.css" rel="stylesheet" type="text/css" media="screen, projection" />

	<script type="text/javascript" src="../js/msgcode.js"></script>
	<script type="text/javascript" src="../js/jquery.js"></script>
	<!-- JAlert Dependencies -->
	<script type="text/javascript" src="../js/jquery-ui-personalized.js"></script>
	<!-- JAlert Core files -->
	<script type="text/javascript" src="../js/jquery.alerts.js"></script>
	
	<script type="text/javascript" src="../js/ddsmoothmenu.js"></script>
	<script type="text/javascript" src="../js/jquery.media.js"></script>
	<script type="text/javascript" src="../js/jquery.metadata.js"></script> 
	<script type="text/javascript" src="../js/common.js"></script>

	<script type="text/javascript" src="../js/jquery.tooltip.js"></script>
	<script type="text/javascript" src="../js/jquery.tablednd_0_5.js"></script>

 </HEAD>

 <BODY>
<div id="wrap">
	<div id="wrap-header">
		<span id="wrap-header-left"></span>
		<div id="wrap-header-body" style="position: relative;">

			<div id="tabfull">
				<div id="left"></div>
 				<div id="logo"></div>
				<div id="right"></div>
 				<div id="header_leftbutton" align='right'><% if $res_admin ne '' %><b><font color=white><% $res_admin.adminid %></font></b><br><a href="index.php?action=do_logout" id="header_link">退出系统</a><%/if%><!-- <br><b><% if $registration.display_noreg eq 'yes' && $registration.systemid eq '' %><a href="###" class='showRegistration' func="main.php?action=func_registration"><font color=red>点击这里进行新用户向导.</font></a><%else%><font color=white><% $registration.systemid %></font><%/if%></b> --></div>
			</div>
		</div>
		<span id="wrap-header-right"></span>
	</div>
