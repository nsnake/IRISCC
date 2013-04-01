<% include file="cpanel/func_header.inc.tpl" %>
	<h4>上载新的音乐</h4>

<form name="do_musicmanager_add" method="POST" action="?action=do_musicmanager_add" target="_self">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;首先 填写文件信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">文件名称</td>
			<td height="30"><input type="text" id="iptext1" name="filename" size="16" value="" class="tipmsg" title="必填,请不要包含扩展名."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">文件说明</td>
			<td height="30"><input type="text" id="iptext1" name="description" size="60" value=""></td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td>
				<input type="submit" value="确认进入语音文件上载页" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
