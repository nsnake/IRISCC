<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {
	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

});
</script>


<!-- body -->
<div id="body-body">
	<h3>语音文件管理</h3>

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
			<table border="0" align="left" style="margin-top: 20px">
				<tr>
					<td height="30" align="left"><input type="button" value="上载新的语音" name="button" id='btn1' class='showpopDialog' func="pbx_soundmanager.php?action=func_soundmanager_add"></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td align="left">
			<table width="100%" border="0" cellspacing="0" style="margin-top: 20px">
			<tr>
				<td height="30" align="left" width="50%">有效语音文件&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;个</td>
				<td height="30" align="right" width="50%"><a href="?cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" style="margin-top: 0px">
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">目录</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">语音文件</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">说明</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">尺寸</td>
<!-- 					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">上载时间</td> -->
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$soundmusic_array item=eachone key=keyname %>
				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 11px" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.category %>/<% $eachone.folder %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 11px" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<b><% $eachone.filename %>.<% $eachone.extname %></b></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" class="tipmsg" title="<% $eachone.description %>" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.description_short %></td>
					<td height="30" align="right" style="border-bottom: #ACA8A1 1px solid;font-size: 11px" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.filesize %>KB&nbsp;&nbsp;</td>
<!-- 					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.cretime %></td> -->
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="试听 / 更新" name="button" id='btn1' class='showpopDialog' func="pbx_soundmanager.php?action=func_soundmanager_edit&id=<% $eachone.id %>">&nbsp;<% if $eachone.readonly eq '0' %><input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="pbx_soundmanager.php?action=do_soundmanager_delete&id=<% $eachone.id %>">&nbsp;<%/if%></td>
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
