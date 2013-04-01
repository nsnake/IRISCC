<% include file="cpanel/func_header.inc.tpl" %>
	<h4>创建分机</h4>
	<p>&nbsp;</p>

	<table border="0" align="left" style="margin: 20px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;第一步 选择设备类型</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span id='sipblock' class="tipmsg" title="SIP协议类型的分机器，SIP协议是目前使用最广泛的IP通信协议" style="text-decoration: none;">&nbsp;<a href="?action=func_exten_add&deviceproto=sip">SIP协议 IP分机</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="IAX2协议的优势是更优良的防火墙穿越性能" style="text-decoration: none;">&nbsp;<a href="?action=func_exten_add&deviceproto=iax2">IAX2协议 IP分机</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="如果你的设备接有一块模拟分机板(FXS)你希望用传统电话作为分机,请选择这个" style="text-decoration: none;">&nbsp;<a href="?action=func_exten_add&deviceproto=fxs">FXS信令 模拟分机</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/44.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="如果你有一个外地员工并且他不方便接听电话创建这个分机可以作为接收语音信箱的方案" style="text-decoration: none;">&nbsp;<a href="?action=func_exten_add&deviceproto=virtual">虚拟分机</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left"  style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/44.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="如果你正在使用一种我们无法识别的新型设备请选择本功能进行调试,这个选项适合高级用户" style="text-decoration: none;">&nbsp;<a href="?action=func_exten_add&deviceproto=custom">自定义分机</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"></td>
		</tr>
	</table>

<% include file="cpanel/func_footer.inc.tpl" %>
