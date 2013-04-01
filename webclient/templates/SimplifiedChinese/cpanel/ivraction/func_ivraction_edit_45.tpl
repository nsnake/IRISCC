<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 AGI扩展接口</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >AGI资源</td>
			<td height="30"><input type="text" id="iptext1" name="agi" size="60" value="<% $args_array.agi|urldecode %>"></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><font color="#ACA8A1">访问会自动添加AGI参数以显示当前所在IVR的位置.</td>
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
