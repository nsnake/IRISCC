<% include file="cpanel/page_header.inc.tpl" %>
<!-- body -->
<div id="body-body">
	<h3></h3>
	<p>&nbsp;</p>



<form id="do_login" name="do_login" method="POST" action="?action=do_login" target="takefire">
	<div align="center">
	<table border="0" width="300">
		<tr>
			<td height="30" width="100%" colspan="2" align="center"><b>验证身份登入管理界面</b></td>
		</tr>
		<tr>
			<td height="30" width="30%" align="center">帐户</td>
			<td height="30" width="70%" align="left">
			<input type="text" name="adminid" size="20" fricheck='notnull' id="iptext1"></td>
		</tr>
		<tr>
			<td height="30" width="30%" align="center">密码</td>
			<td height="30" width="70%" align="left">
			<input type="password" name="passwd" size="20" fricheck='notnull' id="iptext1"></td>
		</tr>
		<tr>
			<td height="30" width="382" colspan="2">
			<p align="center"><input type="submit" value="确认输入" name="submit" id="btn1"></td>
		</tr>
	</table>
	</div>
</form>



</div>
<% include file="cpanel/page_footer.inc.tpl" %>
