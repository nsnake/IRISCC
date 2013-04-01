<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

});
</script>
<div id="body-body">
	<h3>VOIP协议</h3>
	<p>&nbsp;</p>

	<div align="left">
<form name="do_option_voip_sip_set" method="POST" action="?action=do_option_voip_sip_set" target="takefire">
	<table border="0" style="margin: 15px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>SIP协议</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">允许匿名呼叫</td>
			<td height="30"><input type="radio" value="yes" name="allowguest" <% if $sip.allowguest eq 'yes' %>checked<%/if%>>好的 <input type="radio" value="no" name="allowguest" <% if $sip.allowguest eq 'no' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">协议端口</td>
			<td height="30"><input type="text" id="iptext1" name="bindport" size="6" value="<% $sip.bindport %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">注册时间</td>
			<td height="30">最大&nbsp;<input type="text" id="iptext1" name="maxexpiry" size="4" value="<% $sip.maxexpiry %>">&nbsp;秒&nbsp;&nbsp;&nbsp;最小&nbsp;<input type="text" id="iptext1" name="minexpiry" size="4" value="<% $sip.minexpiry %>">&nbsp;秒&nbsp;&nbsp;&nbsp;默认&nbsp;<input type="text" id="iptext1" name="defaultexpiry" size="4" value="<% $sip.defaultexpiry %>">&nbsp;秒</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">语音和视频编码顺序</td>
			<td height="30"><input type="text" id="iptext1" name="allow" size="40" value="<% $sip.allow %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">内带震铃</td>
			<td height="30"><input type="radio" value="never" name="progressinband" <% if $sip.progressinband eq 'never' %>checked<%/if%>>绝不 <input type="radio" value="yes" name="progressinband" <% if $sip.progressinband eq 'yes' %>checked<%/if%>>好的 <input type="radio" value="no" name="progressinband" <% if $sip.progressinband eq 'no' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">视频支持</td>
			<td height="30"><input type="radio" value="yes" name="videosupport" <% if $sip.videosupport eq 'yes' %>checked<%/if%>>好的 <input type="radio" value="no" name="videosupport" <% if $sip.videosupport eq 'no' %>checked<%/if%>>不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">视频码宽</td>
			<td height="30"><input type="text" id="iptext1" name="maxcallbitrate" size="4" value="<% $sip.maxcallbitrate %>">Kb</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_option_voip_iax_set" method="POST" action="?action=do_option_voip_iax_set" target="takefire">
	<table border="0" style="margin: 15px;margin-top: 50px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>IAX2协议</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">协议端口</td>
			<td height="30"><input type="text" id="iptext1" name="bindport" size="6" value="<% $iax.bindport %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">语音和视频编码顺序</td>
			<td height="30"><input type="text" id="iptext1" name="allow" size="40" value="<% $iax.allow %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">注册时间</td>
			<td height="30">最大&nbsp;<input type="text" id="iptext1" name="maxregexpire" size="4" value="<% $iax.maxregexpire %>">&nbsp;秒&nbsp;&nbsp;&nbsp;最小&nbsp;<input type="text" id="iptext1" name="minregexpire" size="4" value="<% $iax.minregexpire %>">&nbsp;秒</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_option_voip_rtp_set" method="POST" action="?action=do_option_voip_rtp_set" target="takefire">
	<table border="0" style="margin: 15px;margin-top: 50px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>RTP协议</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">RTP媒体端口开始范围</td>
			<td height="30"><input type="text" id="iptext1" name="rtpstart" size="6" value="<% $rtp.rtpstart %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">RTP媒体端口结束范围</td>
			<td height="30"><input type="text" id="iptext1" name="rtpend" size="6" value="<% $rtp.rtpend %>"></td>
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
