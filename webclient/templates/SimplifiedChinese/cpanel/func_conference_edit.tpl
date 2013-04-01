<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑电话会议室</h4>

<form name="do_conference_edit" method="POST" action="?action=do_conference_edit&confno=<% $conf.confno %>" target="takefire">

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/pi145.png">&nbsp;会议室</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >会议室号码</td>
			<td height="30"><input type="text" id="iptext1" name="confno" size="16" value="<% $conf.confno %>" class="tipmsg" title="必填,纯数字" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >会议室密码</td>
			<td height="30"><input type="text" id="iptext1" name="pincode" size="8" value="<% $conf.pincode %>" class="tipmsg" title="必填,纯数字"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" ></td>
			<td height="30"><input type="checkbox" name="playwhenevent" <% if $conf.playwhenevent eq '1' %>checked<%/if%> value="1">有人 进入/离开 会议室的时候播放语音</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" ></td>
			<td height="30"><input type="checkbox" name="mohwhenonlyone" <% if $conf.mohwhenonlyone eq '1' %>checked<%/if%> value="1">会议室只有一个人的时候播放等待音乐</td>
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
