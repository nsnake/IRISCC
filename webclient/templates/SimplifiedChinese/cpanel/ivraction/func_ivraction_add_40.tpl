<% include file="cpanel/func_header.inc.tpl" %>
	<h4>新动作 主叫变换</h4>
<form name="do_ivraction_add" method="POST" action="?action=do_ivraction_add&ivrnumber=<% $ivrnumber %>&actmode=<% $actmode %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >你想如何修改主叫号码</td>
			<td height="30"><input type="radio" value="replace" name="modify" checked>直接替换&nbsp;&nbsp;<input type="radio" value="append" name="modify">结尾增加&nbsp;&nbsp;<input type="radio" value="prepend" name="modify">增补前缀&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >使用这个内容作为变换内容</td>
			<td height="30"><input type="text" id="iptext1" name="altercallerid" size="16" value="" class="tipmsg" title="必填."></td>
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
