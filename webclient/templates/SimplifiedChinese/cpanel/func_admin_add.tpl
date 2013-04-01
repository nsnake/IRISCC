<% include file="cpanel/func_header.inc.tpl" %>
<form name="do_admin_add" method="POST" action="?action=do_admin_add" target="takefire">

	<h4>创建新管理员</h4>

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" s style="padding-left: 10px;padding-right: 40px">帐户名</td>
			<td height="30"><input type="text" id="iptext1" name="adminid" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >权限</td>
			<td height="30"><select size="1" name="level" id="iptext1">
	<option selected value="1">admin</option>
	<option value="2">arch</option>
	<option value="3">wiz</option>
	<option value="4">user</option>
	<option value="5">nobody</option>
	</select></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >备注</td>
			<td height="30"><textarea rows="6" name="remark" cols="25" id="iptext1"></textarea></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >密码</td>
			<td height="30"><input type="password" id="iptext1" name="curpasswd" size="20"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >再次输入</td>
			<td height="30"><input type="password" id="iptext1" name="newpasswd" size="20"></td>
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
