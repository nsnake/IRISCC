<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">
	$().ready(function() {
		$("#display_advance").click(function(){
			if ($('#advance').is(':visible') == false)
			{
				$('#advance').show();
				$('#advance').focus();
			} else {
				$('#advance').hide();
			}
		});
	});
</script>
	<h4>编辑SIP分机 <% $extension.accountcode %> 信息</h4>

<form name="do_exten_edit_sip" method="POST" action="?action=do_exten_edit_sip&accountcode=<% $extension.accountcode %>" target="takefire">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;第二步 用户信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">分机号码</td>
			<td height="30"><input type="text" id="iptext1" name="accountcode" size="8" value="<% $extension.accountcode %>" class="tipmsg" title="必填,3位以上数字" disabled></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">用户姓名</font></td>
			<td height="30"><input type="text" id="iptext1" name="info_name" size="20" value="<% $extension.info_name %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">电子邮箱</td>
			<td height="30"><input type="text" id="iptext1" name="info_email" size="20" value="<% $extension.info_email %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">数字密码</td>
			<td height="30"><input type="text" id="iptext1" name="password" size="8" value="<% $extension.password %>" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">再次输入密码</td>
			<td height="30"><input type="text" id="iptext1" name="repassword" size="8" value="<% $extension.password %>" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">详细资料</td>
			<td height="30"><input type="text" id="iptext1" name="info_detail" size="30" value="<% $extension.info_detail %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">用户分组</td>
			<td height="30"><input type="text" id="iptext1" name="extengroup" size="30" value="<% $extension.extengroup %>" class="tipmsg" title="填写用户要分配的分组名称以逗号或空格区分两个分组"></td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px;margin-top: 5px">
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">&nbsp;&nbsp;<img src="../images/icon/83.png" border="0"><a href="###" id='display_advance'>&nbsp;点击显示高级选项&nbsp;</span></a></td>
		</tr>
		<tr>
			<td colspan="3">
				<table border="0" cellspacing="0" id='advance' style='display:none'>
					<tr>
						<td height="30" align="left"><img src="../images/icon/24.png">&nbsp;协议参数</td>
					</tr>
					<tr>
						<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">主机地址</td>
						<td height="30"><input type="text" id="iptext1" name="host" size="16" value="<% $extension.host %>"></td>
					</tr>
					<tr>
						<td height="30" valign='top' style="padding-left: 10px;padding-right: 40px"><span class="tipmsg" title="分机的IP地址,如果分机是固定IP请填写地址,否则请保持dynamic." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>关于地址!</b></span></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">私网穿透</td>
						<td height="30"><input type="radio" value="yes" <% if $extension.nat eq 'yes' %>checked<%/if%> name="nat">Yes <input type="radio" value="no" <% if $extension.nat eq 'no' %>checked<%/if%> name="nat">No</td>
					</tr>
					<tr>
						<td height="30" align='left' style="padding-left: 10px;padding-right: 40px">保活时长</td>
						<td height="30"><input type="text" id="iptext1" name="qualify" size="8" value="<% $extension.qualify %>">毫秒</td>
					</tr>
					<tr>
						<td height="30" valign='top' style="padding-left: 10px;padding-right: 40px"><span class="tipmsg" title="NAT设置可以确保即使分机在某个Firewall后面也能轻松的穿越Firewall." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>NAT 的作用?</b></span></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SIP协议转发</td>
						<td height="30"><input type="radio" value="yes" <% if $extension.canreinvite eq 'yes' %>checked<%/if%> name="canreinvite">Yes <input type="radio" value="no" <% if $extension.canreinvite eq 'no' %>checked<%/if%> name="canreinvite">No</td>
					</tr>
					<tr>
						<td height="30" valign='top' style="padding-left: 10px;padding-right: 40px"><span class="tipmsg" title="SIP协议转发将极大的提高系统性能,主要用于交换系统.由于我们的系统具有很强的PBX功能如果启用这个功能可能导致莫名其妙的错误,所以请慎重." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>请慎用转发?</b></span></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">自定义变量</td>
						<td height="30"><input type="text" id="iptext1" name="setvar" size="20" value="<% $extension.setvar %>"></td>
					</tr>
					<tr>
						<td height="30" valign='top' style="padding-left: 10px;padding-right: 40px"><span class="tipmsg" title="这个参数是专门用于将指定的变量传递到呼叫流程的方法,适用用二次开发情况." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>填写格式?</b></span></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">最大并发</td>
						<td height="30"><input type="text" id="iptext1" name="call-limit" size="4" value="<% $extension.calllimit %>"> 个同时呼叫
						</td>
					</tr>
					<tr>
						<td height="30" valign='top' style="padding-left: 10px;padding-right: 40px"><span class="tipmsg" title="允许这个分机同时产生的呼叫数量,一般分机只需要一线就可以了.如果使用的是支持多线呼叫的专业话机可能需要设置这个,比如潮流的GXP2000同时支持4路呼叫." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>什么效果?</b></span></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">呼转号码</td>
						<td height="30"><input type="text" id="iptext1" name="transfernumber" size="16" value="<% $extension.transfernumber %>" class="tipmsg" title="如果启用了呼叫转移功能,当拨打此分机失败以后将呼转次号码."></td>
					</tr>
					<tr>
						<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">无法接通处理</td>
						<td height="30">
<table border="0" width="100%">
	<tr>
		<td>&nbsp;&nbsp;<input type="radio" value="" name="diallocal_failed" <% if $extension.diallocal_failed eq '' %>checked<%/if%>>系统默认&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="radio" value="nothing" name="diallocal_failed" <% if $extension.diallocal_failed eq 'nothing' %>checked<%/if%>>不处理&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="radio" value="failedtransfer" name="diallocal_failed" <% if $extension.diallocal_failed eq 'failedtransfer' %>checked<%/if%>>呼叫转移&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<input type="radio" value="voicemail" name="diallocal_failed" <% if $extension.diallocal_failed eq 'voicemail' %>checked<%/if%>>语音信箱&nbsp;&nbsp;</td>
	<td>&nbsp;&nbsp;<input type="radio" value="fax" name="diallocal_failed" <% if $extension.diallocal_failed eq 'fax' %>checked<%/if%>>数字传真&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="radio" value="ivr" name="diallocal_failed" <% if $extension.diallocal_failed eq 'ivr' %>checked<%/if%>>IVR提醒菜单&nbsp;&nbsp;</td>
	</tr>
</table>
						</td>
					</tr>
				</table>
				</div>
			</td>
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
