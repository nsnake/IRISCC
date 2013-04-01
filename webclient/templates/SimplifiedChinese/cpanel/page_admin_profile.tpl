<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<div id="body-body">
	<h3>个人信息修改</h3>
	<p>&nbsp;</p>

<form name="do_admin_profile_edit" method="POST" action="?action=do_admin_profile_edit" target="takefire">
	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="30%"><b>固定信息</b><span class="tipmsg" title="没有关于这个的帮助主题" style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;?&nbsp;</b></span></td>
			<td height="30" width="70%">　</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="30%">帐户名</td>
			<td height="30" width="70%"><input type="text" id="iptext1" name="adminid" size="20" value="<% $res_admin.adminid %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" width="30%">权限</td>
			<td height="30" width="70%"><input type="text" id="iptext1" name="level" size="20" value="<% $res_admin.level %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" width="30%">创建时间</td>
			<td height="30" width="70%"><input type="text" id="iptext1" name="cretime" size="20" value="<% $res_admin.cretime %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan="2">
				<table border="0" cellspacing="0" width="300" style="margin: 20px">
					<tr>
						<td height="30" align="left" width="30%"><b>修改密码</b></td>
						<td height="30" width="70%">　</td>
					</tr>
					<tr>
						<td height="30" align="left" width="30%" bgcolor="#EFEFEF"></td>
						<td height="30" width="70%" valign="top" bgcolor="#EFEFEF">新密码&nbsp;&nbsp;<input type="password" id="iptext1" name="newpasswd" size="20"></td>
					</tr>
					<tr>
						<td height="30" align="left" width="30%" bgcolor="#EFEFEF"></td>
						<td height="30" width="70%" valign="top" bgcolor="#EFEFEF">重复输入&nbsp;<input type="password" id="iptext1" name="renewpasswd" size="20"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" width="30%">当前密码</td>
			<td height="30" width="70%"><input type="password" id="iptext1" name="curpasswd" size="20" fricheck='notnull'></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">
			<p align="center"><input type="submit" value="保存这个更改" name="submit" id='btn1'></td>
		</tr>
	</table>
	</div>
</form>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>

