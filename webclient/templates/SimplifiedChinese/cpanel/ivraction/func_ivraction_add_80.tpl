<% include file="cpanel/func_header.inc.tpl" %>
	<h4>新动作 等待几秒</h4>
<form name="do_ivraction_add" method="POST" action="?action=do_ivraction_add&ivrnumber=<% $ivrnumber %>&actmode=<% $actmode %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >等待时间(秒)</td>
			<td height="30"><input type="text" id="iptext1" name="wait" size="4" value=""></td>
		</tr>
		<tr>
			<td height="30" colspan='2'><input type="checkbox" name="interruptible" value="true">等待中, 接受用户输入选择.</td>
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
