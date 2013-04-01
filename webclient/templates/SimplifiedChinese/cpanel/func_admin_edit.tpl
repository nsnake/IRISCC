<% include file="cpanel/func_header.inc.tpl" %>
<form name="do_admin_edit" method="POST" action="?action=do_admin_edit&adminid=<% $thisadmin.adminid %>" target="takefire">

	<h4>修改管理员 <% $thisadmin.adminid %> 的信息</h4>

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >帐户名</td>
			<td height="30"><input type="text" id="iptext1" name="adminid" size="20" value="<% $thisadmin.adminid %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >权限</td>
			<td height="30"><input type="text" id="iptext1" name="level" size="20" value="<% $thisadmin.level %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >创建时间</td>
			<td height="30"><input type="text" id="iptext1" name="cretime" size="20" value="<% $thisadmin.cretime %>" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">
				<table border="0" cellspacing="0" width="300" style="margin: 20px">
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" ><b>修改密码</b></td>
						<td height="30">　</td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"  bgcolor="#EFEFEF"></td>
						<td height="30" valign="top" bgcolor="#EFEFEF">新密码&nbsp;&nbsp;<input type="password" id="iptext1" name="newpasswd" size="20"></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"  bgcolor="#EFEFEF"></td>
						<td height="30" valign="top" bgcolor="#EFEFEF">重复输入&nbsp;<input type="password" id="iptext1" name="renewpasswd" size="20"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >您的管理员密码</td>
			<td height="30"><input type="password" id="iptext1" name="curpasswd" size="20" fricheck='notnull'></td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
