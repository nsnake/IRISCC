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
	<h3>系统自动录音</h3>

	<div align="left">
	<table border="0" width="90%" style="margin: 0px">
		<tr>
			<td height="30" align="left" width="100%">&nbsp;设置和管理哪些分机需要进行自动录音.</td>
		</tr>
	</table>
	<table border="0" width="80%" style="margin-left: 15px">
		<tr>
			<td align="left">
			<table border="0" align="left" style="margin-top: 20px">
				<tr>
					<td height="30" align="left"><input type="button" value="创建自动录音触发器" name="button" id='btn1' class='showpopDialog' func="acd_record.php?action=func_recordtrigger_add"></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td align="left">
			<table width="100%" border="0" cellspacing="0" style="margin-top: 20px">
			<tr>
				<td height="30" align="left" width="50%">录音触发器&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;个</td>
				<td height="30" align="right" width="50%"><a href="?cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" style="margin-top: 0px">
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">名称</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">触发器</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">对象</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">保存方式</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$lists_array item=eachone key=keyname %>
				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.triggername %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><b>&nbsp;<% if $eachone.recordout eq '1' %>拨号, <%/if%><% if $eachone.recordin eq '1' %>接听,<%/if%><% if $eachone.recordqueue eq '1' %>队列接听<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<span class="tipmsg" title="号码:<% $eachone.members %>">分机</a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.keepfortype eq '0' %><% $eachone.keepforargs %>条<%/if%><% if $eachone.keepfortype eq '1' %><% $eachone.keepforargs %>天<%/if%><% if $eachone.keepfortype eq '2' %>永久保存<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="编辑" name="button" id='btn1' class='showpopDialog' func="acd_record.php?action=func_recordtrigger_edit&id=<% $eachone.id %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_record.php?action=do_recordtrigger_delete&id=<% $eachone.id %>">&nbsp;</td>
				</tr>
		<%/foreach%>

			</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"></td>
		</tr>
	</table>
	<table border="0" width="90%" style="margin-left: 15px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="60"align="left">
				<input type="button" value="管理自动录音文件" name="button" id='btn1' class='showpopDialog' func="acd_record.php?action=func_recordfiles_list">
			</td>
			<td height="60" align="left"><img src="../images/icon/microphone.png">&nbsp;<b>自动录音磁盘还剩 <% $sysautomon_diskfree_percent %>%&nbsp;&nbsp;可用&nbsp;<% $sysautomon_diskfree_gigabyte %>GB</b>
				<table border="0" height="5" width="300" cellspacing="0" cellpadding="0" style="border: 1px solid #000000">
					<tr>
						<td width='<% $sysautomon_diskused_percent %>%' bgcolor="#FFFF00"></td>
						<td width='<% $sysautomon_diskfree_percent %>%' bgcolor="#00FF00"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="60" align="left">
				<input type="button" value="管理IVR录音文件" name="button" id='btn1' class='showpopDialog' func="acd_record.php?action=func_ivrfiles_list">
			</td>
			<td height="60" align="left"><img src="../images/icon/microphone.png">&nbsp;<b>IVR录音磁盘还剩 <% $ivrmenu_diskfree_percent %>%&nbsp;&nbsp;可用&nbsp;<% $ivrmenu_diskfree_gigabyte %>GB</b>
				<table border="0" height="5" width="300" cellspacing="0" cellpadding="0" style="border: 1px solid #000000">
					<tr>
						<td width='<% $ivrmenu_diskused_percent %>%' bgcolor="#FFFF00"></td>
						<td width='<% $ivrmenu_diskfree_percent %>%' bgcolor="#00FF00"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
