<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

});
</script>
<div id="body-body">
	<h3>用户语音信箱&nbsp;录音文件</h3>

	<div align="left">
	<table border="0" width="95%" align="left" style="margin: 15px">
		<tr>
			<td align="left">
			<table border="0" align="left" style="margin-top: 20px">
				<tr>
					<td height="30" align="left" colspan='2'><img src="../images/icon/microphone.png">&nbsp;<b>磁盘还剩 <% $diskfree_percent %>%&nbsp;/&nbsp;<% $diskfree_gigabyte %>GB&nbsp;可用</b>
						<table border="0" height="5" width="300" cellspacing="0" cellpadding="0" style="border: 1px solid #000000">
							<tr>
								<td width='<% $diskused_percent %>%' bgcolor="#FFFF00"></td>
								<td width='<% $diskfree_percent %>%' bgcolor="#00FF00"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td align="left">
			<table width="100%" border="0" cellspacing="0" style="margin-top: 20px">
			<tr>
				<td height="30" align="left" width="50%">已用语音信箱帐号&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;个</td>
				<td height="30" align="right" width="50%"><a href="?cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" style="margin-top: 0px">
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">分机号码</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">真实姓名</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">语音邮件</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">一键录音</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$result_array item=eachone key=keyname %>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<b><% $eachone.accountcode %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.info_name %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><img src="../images/icon/pi447.png">&nbsp;<% $eachone.voicemail_count %>&nbsp;封</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><img src="../images/icon/pi401.png">&nbsp;<% $eachone.onetouch_count %>&nbsp;条</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="查看邮件" name="button" id='btn1' class='showpopDialog' func="pbx_uservoice.php?action=func_voicemail_list&accountcode=<% $eachone.accountcode %>">&nbsp;<input type="button" value="查看录音" name="button" id='btn1' class='showpopDialog' func="pbx_uservoice.php?action=func_onetouch_list&accountcode=<% $eachone.accountcode %>"></td>
				</tr>
		<%/foreach%>

			</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"></td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
