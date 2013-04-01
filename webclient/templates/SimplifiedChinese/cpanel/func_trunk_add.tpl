<% include file="cpanel/func_header.inc.tpl" %>
	<h4>增加外线</h4>

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;选择设备类型</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
 		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span id='sipblock' class="tipmsg" title="SIP协议是目前使用最广泛的IP通信协议" style="text-decoration: none;">&nbsp;<a href="?action=func_trunk_add&trunkproto=sip">SIP&nbsp;&nbsp;&nbsp;&nbsp;网络外线</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="IAX2协议的优势是更优良的防火墙穿越性能" style="text-decoration: none;">&nbsp;<a href="?action=func_trunk_add&trunkproto=iax2">IAX2&nbsp;&nbsp;网络外线</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="如果你有一条电话线接到了机器的模拟板上,请选择这个功能进行设置" style="text-decoration: none;">&nbsp;<a href="?action=func_trunk_add&trunkproto=fxo">PSTN&nbsp;FXO&nbsp;模拟外线</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/43.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="ISDN-PRI协议的数字中继线,一般称为E1" style="text-decoration: none;">&nbsp;<a href="?action=func_trunk_add&trunkproto=isdnpri">E1&nbsp;&nbsp;ISDN-PRI&nbsp;数字中继</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" width="100%" colspan="2">&nbsp;&nbsp;<img src="../images/icon/44.png" border="0">&nbsp;&nbsp;<span class="tipmsg" title="如果你正在使用一种我们无法识别的新型设备请选择本功能进行调试,这个选项适合高级用户" style="text-decoration: none;">&nbsp;<a href="?action=func_trunk_add&trunkproto=custom">自定义外线</a>&nbsp;</span></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"></td>
		</tr>
	</table>

<% include file="cpanel/func_footer.inc.tpl" %>
