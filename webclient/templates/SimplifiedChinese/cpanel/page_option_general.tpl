<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

});
</script>
<div id="body-body">
	<h3>通话参数</h3>
	<p>&nbsp;</p>

	<div align="left">
<form name="do_option_general_set" method="POST" action="?action=do_option_general_set&section=fastagi" target="takefire">
	<table border="0" style="margin: 15px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">默认震铃时长</td>
			<td height="30"><input type="text" id="iptext1" name="dial_ringtime" size="4" value="<% $fastagi.dial_ringtime %>" class="tipmsg" title="必填,数字"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">拨号附加参数</td>
			<td height="30"><input type="text" id="iptext1" name="dial_addional" size="16" value="<% $fastagi.dial_addional %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">分机无法接通处理方法</td>
			<td height="30"><input type="radio" value="nothing" name="diallocal_failed" <% if $fastagi.diallocal_failed eq 'nothing' %>checked<%/if%>>不处理 <input type="radio" value="failedtransfer" name="diallocal_failed" <% if $fastagi.diallocal_failed eq 'failedtransfer' %>checked<%/if%>>呼叫转移 <input type="radio" value="voicemail" name="diallocal_failed" <% if $fastagi.diallocal_failed eq 'voicemail' %>checked<%/if%>>语音信箱 <input type="radio" value="ivr" name="diallocal_failed" <% if $fastagi.diallocal_failed eq 'ivr' %>checked<%/if%>>IVR提醒菜单<br><input type="radio" value="fax" name="diallocal_failed" <% if $fastagi.diallocal_failed eq 'fax' %>checked<%/if%>>数字传真</td>
		</tr>
        <tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">拨出规则参数</td>
			<td height="30"><input type="checkbox" value="enable" name="router_extenrule_default" <% if $fastagi.router_extenrule_default eq 'enable' %>checked<%/if%>>缺省模式查找本地号码</td>
		</tr>
        <tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">拨入规则参数</td>
			<td height="30"><input type="checkbox" value="enable" name="router_trunkrule_default" <% if $fastagi.router_trunkrule_default eq 'enable' %>checked<%/if%>>缺省模式查找本地号码</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_option_general_set" method="POST" action="?action=do_option_general_set&section=voicemail" target="takefire">
	<table border="0" style="margin: 15px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">启动语音信箱</td>
			<td height="30"><input type="radio" value="true" name="enable" <% if $voicemail.enable eq 'true' %>checked<%/if%>>好的 <input type="radio" value="false" name="enable" <% if $voicemail.enable eq 'false' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">每个用户容量</td>
			<td height="30"><input type="text" id="iptext1" name="usermax" size="8" value="<% $voicemail.usermax %>"> 条记录</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">留言静音检测</td>
			<td height="30"><input type="text" id="iptext1" name="silence" size="4" value="<% $voicemail.silence %>" class="tipmsg" title="当系统发现用户常时间不说话达到检测最大值的时候会结束留言."> (秒)</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">最长留言时间</td>
			<td height="30"><input type="text" id="iptext1" name="maxduration" size="4" value="<% $voicemail.maxduration %>" class="tipmsg" title="当达到最长通话时间系统会强制结束."> (秒)</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">播放收听前言</td>
			<td height="30"><input type="radio" value="true" name="vmmainsayinbox" <% if $voicemail.vmmainsayinbox eq 'true' %>checked<%/if%>>好的 <input type="radio" value="false" name="vmmainsayinbox" <% if $voicemail.vmmainsayinbox eq 'false' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">播放留言时间</td>
			<td height="30"><input type="radio" value="true" name="uvmainsayheader" <% if $voicemail.uvmainsayheader eq 'true' %>checked<%/if%>>好的 <input type="radio" value="false" name="uvmainsayheader" <% if $voicemail.uvmainsayheader eq 'false' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">发送到电子信箱</td>
			<td height="30"><input type="radio" value="nothing" name="mailer" <% if $voicemail.mailer eq 'nothing' %>checked<%/if%>>关闭 <input type="radio" value="smtp" name="mailer" <% if $voicemail.mailer eq 'smtp' %>checked<%/if%>>通过SMTP <input type="radio" value="sendmail" name="mailer" <% if $voicemail.mailer eq 'sendmail' %>checked<%/if%>>通过Sendmail </td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">邮件包含语音文件</td>
			<td height="30"><input type="radio" value="yes" name="mailer_attachvoice" <% if $voicemail.mailer_attachvoice eq 'yes' %>checked<%/if%>>好的 <input type="radio" value="no" name="mailer_attachvoice" <% if $voicemail.mailer_attachvoice eq 'no' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">发件人地址</td>
			<td height="30"><input type="text" id="iptext1" name="mailer_from" size="24" value="<% $voicemail.mailer_from %>" class="tipmsg" title="比如 yourname@yourhost.com"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SMTP服务器地址</td>
			<td height="30"><input type="text" id="iptext1" name="smtp_host" size="24" value="<% $voicemail.smtp_host %>" class="tipmsg" title="只有通过SMTP时才需要填写."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SMTP服务器端口</td>
			<td height="30"><input type="text" id="iptext1" name="smtp_port" size="4" value="<% $voicemail.smtp_port %>" class="tipmsg" title="只有通过SMTP时才需要填写.通常是25"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SMTP是否验证</td>
			<td height="30"><input type="radio" value="true" name="smtp_auth" <% if $voicemail.smtp_auth eq 'true' %>checked<%/if%>>好的 <input type="radio" value="false" name="smtp_auth" <% if $voicemail.smtp_auth eq 'false' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SMTP验证帐户</td>
			<td height="30"><input type="text" id="iptext1" name="smtp_username" size="24" value="<% $voicemail.smtp_username %>" class="tipmsg" title="只有通过SMTP时才需要填写.请询问你的邮件服务商,比如yourname@163.com"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">SMTP验证密码</td>
			<td height="30"><input type="text" id="iptext1" name="smtp_password" size="24" value="<% $voicemail.smtp_password %>" class="tipmsg" title="只有通过SMTP时才需要填写.SMTP的验证密码"></td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
