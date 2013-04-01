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

		$("input[@name='trunkprototype']").click(function(){
			if ($("input[@name='trunkprototype']:checked").val() == 'reg') {
				$('#autharea_server').show();
				$('#autharea_user').show();
				$('#autharea_secret').show();
			} else if ($("input[@name='trunkprototype']:checked").val() == 'ip') {
				$('#autharea_server').show();
				$('#autharea_user').hide();
				$('#autharea_secret').hide();
			} else if ($("input[@name='trunkprototype']:checked").val() == 'iad') {
				$('#autharea_server').hide();
				$('#autharea_user').hide();
				$('#autharea_secret').show();
			}
		});

		if ($("input[@name='trunkprototype']:checked").val() == 'reg') {
			$('#autharea_server').show();
			$('#autharea_user').show();
			$('#autharea_secret').show();
		} else if ($("input[@name='trunkprototype']:checked").val() == 'ip') {
			$('#autharea_server').show();
			$('#autharea_user').hide();
			$('#autharea_secret').hide();
		} else if ($("input[@name='trunkprototype']:checked").val() == 'iad') {
			$('#autharea_server').hide();
			$('#autharea_user').hide();
			$('#autharea_secret').show();
		}

	});
</script>
	<h4>编辑SIP外线</h4>

<form name="do_exten_edit_sip" method="POST" action="?action=do_trunk_edit_sip&id=<% $trunk.id %>" target="takefire">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;中继信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >中继名称</td>
			<td height="30"><input type="text" id="iptext1" name="trunkname" size="8" value="<% $trunk.trunkname %>" readonly class="tipmsg" title="必填,数字字母组合"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">备注</font></td>
			<td height="30"><input type="text" id="iptext1" name="trunkremark" size="30" value="<% $trunk.trunkremark %>"></td>
		</tr>
	</table>

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;验证方式</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="3"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" class="tipmsg" title="互联网电话服务商一般为用户提供的VOIP服务都是基于这种验证模式.">&nbsp;&nbsp;<input type="radio" value="reg" <% if $trunk.trunkprototype eq 'reg' %>checked<%/if%> name="trunkprototype">用户名和密码&nbsp;&nbsp;</td>
			<td height="30" class="tipmsg" title="用于跟同级的设备进行对接,适合高级用户.">&nbsp;&nbsp;<input type="radio" value="ip" <% if $trunk.trunkprototype eq 'ip' %>checked<%/if%> name="trunkprototype">IP地址&nbsp;&nbsp;</td>
			<td height="30" class="tipmsg" title="如果你有一台FXO口的语音网关可以使用这个设置让你的语音网关注册到系统来作为中继.">&nbsp;&nbsp;<input type="radio" value="iad" <% if $trunk.trunkprototype eq 'iad' %>checked<%/if%> name="trunkprototype">语音网关&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" bgcolor="#F3F3F3">
		<table border="0" cellspacing="0" id='autharea_server' style='display:block'>
			<tr>
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">服务器地址</td>
				<td height="30"><input type="text" id="iptext1" name="host" size="20" value="<% $trunk.host %>"></td>
			</tr>
			<tr>
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">服务器端口</td>
				<td height="30"><input type="text" id="iptext1" name="port" size="4" value="<% $trunk.port %>"></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" id='autharea_user' style='display:block'>
			<tr>
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">验证用户</td>
				<td height="30"><input type="text" id="iptext1" name="username" size="10" value="<% $trunk.username %>"></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" id='autharea_secret' style='display:block'>
			<tr>
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">验证密码</td>
				<td height="30"><input type="text" id="iptext1" name="secret" size="10" value="<% $trunk.secret %>"></td>
			</tr>
		</table>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/83.png" border="0"><a href="###" id='display_advance'>&nbsp;点击显示高级选项&nbsp;</span></a></td>
		</tr>
		<tr>
			<td colspan="3">
				<table border="0" cellspacing="0" id='advance' style='display:none'>
					<tr>
						<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">强制主叫号码<br>
						&nbsp;<span class="tipmsg" title="如果填写,表示强制以这个主叫号码送出通话" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="callerid" size="8" value="<% $trunk.callerid %>"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">SIP注册时间<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="defaultexpiry" size="4" value="<% $trunk.defaultexpiry %>">&nbsp;秒</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">最大并发量<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="call-limit" size="4" value="<% $trunk.calllimit %>"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">允许这条线路呼入电话</td>
						<td height="30"><input type="radio" value="port,invite" <% if $trunk.insecure eq 'port,invite' %>checked<%/if%> name="insecure">Yes <input type="radio" value="no" <% if $trunk.insecure eq 'no' %>checked<%/if%> name="insecure">No</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">允许彩铃透传</td>
						<td height="30"><input type="radio" value="yes" <% if $trunk.progressinband eq 'yes' %>checked<%/if%> name="progressinband">Yes <input type="radio" value="no" <% if $trunk.progressinband eq 'no' %>checked<%/if%> name="progressinband">No</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">服务器保活<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="qualify" size="8" value="<% $trunk.qualify %>">&nbsp;毫秒</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">编码支持<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>如何选择适合的编码?</b></span></td>
						<td height="60">
<table border="0" width="100%">
	<tr>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_g723 eq '1' %>checked<%/if%> value="g723">G.723&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_g729 eq '1' %>checked<%/if%> value="g729">G.729&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_gsm eq '1' %>checked<%/if%> value="gsm">GSM&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_ulaw eq '1' %>checked<%/if%> value="ulaw">ULAW&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_alaw eq '1' %>checked<%/if%> value="alaw">ALAW&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_h263 eq '1' %>checked<%/if%> value="h263">H.263&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_h264 eq '1' %>checked<%/if%> value="h264">H.264&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_g722 eq '1' %>checked<%/if%> value="g722"><font color='gray'>G.722&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_g726 eq '1' %>checked<%/if%> value="g726"><font color='gray'>G.726&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_ilbc eq '1' %>checked<%/if%> value="ilbc"><font color='gray'>ILBC&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;<input type="checkbox" name="codec[]" <% if $trunk.codec_speex eq '1' %>checked<%/if%>value="speex"><font color='gray'>SPEEX&nbsp;&nbsp;</td>
	</tr>
</table>
						</td>
					</tr>
				</table>
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
