<% include file="cpanel/func_header.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除吗?' ,'提示' , function(r) {
			if (r == true)
			{
				window.location.href=gotourl;
//				$(this).attr('src',gotourl);
			}
		});
	});

});
</script>
	<h4>分机 <% $accountcode %> 的语音邮件</h4>

	<div align="left">
	<table border="0" width="90%" align="left" style="margin-top: 20px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">录音时间</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">来电号码</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">录音文件</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">播放</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$voicemail item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.cretime %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;&nbsp;<% $eachone.caller %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.filesize %>KB</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="pbx_uservoice.php?action=do_uservoice_download&id=<% $eachone.id %>" target="_blank" title="试听或下载"><img src="../images/icon/xi66.png" border='0'></a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="pbx_uservoice.php?action=do_uservoice_delete&id=<% $eachone.id %>&accountcode=<% $accountcode %>&type=vm"></td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
