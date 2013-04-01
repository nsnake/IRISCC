<% include file="cpanel/func_header.inc.tpl" %>
	<h4>创建自定义分机</h4>

<form name="do_exten_add_custom" method="POST" action="?action=do_exten_add_custom" target="takefire">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;第二步 用户信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px">分机号码</td>
			<td height="30"><input type="text" id="iptext1" name="accountcode" size="8" value="" class="tipmsg" title="必填,3位以上数字"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">用户姓名</font></td>
			<td height="30"><input type="text" id="iptext1" name="info_name" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">电子邮箱</td>
			<td height="30"><input type="text" id="iptext1" name="info_email" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">数字密码</td>
			<td height="30"><input type="text" id="iptext1" name="password" size="8" value="" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">再次输入密码</td>
			<td height="30"><input type="text" id="iptext1" name="repassword" size="8" value="" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">详细资料</td>
			<td height="30"><input type="text" id="iptext1" name="info_detail" size="30" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">用户分组</td>
			<td height="30"><input type="text" id="iptext1" name="extengroup" size="30" value="" class="tipmsg" title="填写用户要分配的分组名称以逗号或空格区分两个分组"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">设备参数</td>
			<td height="30"><input type="text" id="iptext1" name="devicestring" size="20" value="" class="tipmsg" title="设备的参数名称,这个参数专门用于自定义设备"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">呼转号码</td>
			<td height="30"><input type="text" id="iptext1" name="transfernumber" size="16" value="" class="tipmsg" title="如果启用了呼叫转移功能,当拨打此分机失败以后将呼转次号码."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">无法接通处理&nbsp;&nbsp;</td>
			<td height="30">
<table border="0" width="100%">
<tr>
<td>&nbsp;&nbsp;<input type="radio" value="" name="diallocal_failed" checked>系统默认&nbsp;&nbsp;</td>
<td>&nbsp;&nbsp;<input type="radio" value="nothing" name="diallocal_failed">不处理&nbsp;&nbsp;</td>
<td>&nbsp;&nbsp;<input type="radio" value="failedtransfer" name="diallocal_failed">呼叫转移&nbsp;&nbsp;</td>
</tr>
<tr>
<td>&nbsp;&nbsp;<input type="radio" value="voicemail" name="diallocal_failed">语音信箱&nbsp;&nbsp;</td>
<td>&nbsp;&nbsp;<input type="radio" value="fax" name="diallocal_failed">数字传真&nbsp;&nbsp;</td>
<td>&nbsp;&nbsp;<input type="radio" value="ivr" name="diallocal_failed">IVR提醒菜单&nbsp;&nbsp;</td>
</tr>
</table>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<% include file="cpanel/func_footer.inc.tpl" %>
