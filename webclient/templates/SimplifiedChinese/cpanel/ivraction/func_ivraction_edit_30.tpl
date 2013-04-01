<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 读出日期时间</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >读出呼叫当时发生的日期时间</td>
			<td height="30"><input type="checkbox" value="true" name="saydatetime" <%if $args_array.saydatetime eq 'true'%>checked<%/if%>>好的</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >从录制的字符变量中读出日期</td>
			<td height="30"><input type="text" id="iptext1" name="saydatefromvar" size="20" value="<% $args_array.saydatefromvar %>"><span class="tipmsg" title="只有满足YYYYMMDD 或是 YYYYMM 或是 MMDD 的格式, 比如:'20090101'系统会读做 2009年1月1日" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>格式要求?</b></span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >从录制的字符变量中读出时间</td>
			<td height="30"><input type="text" id="iptext1" name="saytimefromvar" size="20" value="<% $args_array.saytimefromvar %>"><span class="tipmsg" title="只有满足HHMM 或是 HHMMSS的格式, 比如:'1711'系统会读做 17点11分" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>格式要求?</b></span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >读出指定日期</td>
			<td height="30"><input type="text" id="iptext1" name="saydatestring" size="20" value="<% $args_array.saydatestring %>"><span class="tipmsg" title="支持格式: YYYYMMDD 或是 YYYYMM 或是 MMDD，比如:'20090101'" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>格式要求?</b></span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >读出指定时间</td>
			<td height="30"><input type="text" id="iptext1" name="saytimestring" size="20" value="<% $args_array.saytimestring %>"><span class="tipmsg" title="支持格式: HHMM 或是 HHMMSS，比如:'1716'" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>格式要求?</b></span></td>
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
