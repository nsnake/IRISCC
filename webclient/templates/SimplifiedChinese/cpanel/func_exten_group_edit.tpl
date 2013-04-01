<% include file="cpanel/func_header.inc.tpl" %>
<form name="do_group_edit" method="POST" action="?action=do_group_edit&groupid=<% $this.groupid %>" target="takefire">

	<h4>修改分组 <% $this.groupname %> 的信息</h4>

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">分组名称</td>
			<td height="30"><input type="text" id="iptext1" name="groupname" size="20" value="<% $this.groupname %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">备注</td>
			<td height="30"><input type="text" id="iptext1" name="remark" size="20" value="<% $this.remark %>"></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">
			<p align="center"><input type="submit" value="保存" name="submit" id='btn1'></td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
