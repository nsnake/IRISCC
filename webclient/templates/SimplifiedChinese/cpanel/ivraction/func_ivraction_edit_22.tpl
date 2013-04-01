<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 数字方式读出</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >从录制的字符变量中读</td>
			<td height="30"><input type="text" id="iptext1" name="playbackvarname" size="20" value="<% $args_array.playbackvarname %>" class="tipmsg" title="数字和英文字母组合."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >读以下字符</td>
			<td height="30"><input type="text" id="iptext1" name="saydigits" size="16" value="<% $args_array.saydigits %>" class="tipmsg" title="数字."></td>
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
