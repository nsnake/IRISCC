<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 跳转信箱或传真</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >从录制的字符变量中读</td>
			<td height="30"><input type="text" id="iptext1" name="dialvarname" size="20" value="<% $args_array.dialvarname %>" class="tipmsg" title="数字和英文字母组合."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >指定以下号码的语音信箱</td>
			<td height="30"><input type="text" id="iptext1" name="dialdigits" size="16" value="<% $args_array.dialdigits %>" class="tipmsg" title="数字."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" ></td>
			<td height="30"><input type="radio" value="voicemail" name="typeof" <% if $args_array.typeof eq 'voicemail' %>checked<%/if%>>语音信箱&nbsp;&nbsp;<input type="radio" value="digitalfax" name="typeof" <% if $args_array.typeof eq 'digitalfax' %>checked<%/if%>>数字传真 </td>
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
