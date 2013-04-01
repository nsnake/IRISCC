<% include file="cpanel/page_header.inc.tpl" %>
<!-- body -->
<div id="body-body">
	<h3></h3>
	<p>&nbsp;</p>

<form name="do_login" method="POST" action="?action=do_login&callback=<% $callback %>" target="takefire" onsubmit="return this.submit.disabled=true;">
	<div align="center">
	<table border="0" width="300">
		<tr>
			<td height="30" width="100%" colspan="2" align="center"><b>暂时无法验证您的身份,请重新输入帐户信息才可以使用这个功能</b></td>
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

