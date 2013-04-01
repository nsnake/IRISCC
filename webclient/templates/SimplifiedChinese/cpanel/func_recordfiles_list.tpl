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

	$("#trigger_filter").change(function(){	
		window.location.href="acd_record.php?action=func_recordfiles_list&trigger_filter="+$(this).val();
	});

});
</script>
	<h4>自动录音文件</h4>
	<div align="left">
<form name="filter" method="POST" action="?action=do_recordfiles_delete&area=all" target="_self">
	<table border="0" width="90%" style="margin-top: 10px">
		<tr>
			<td height="30">
			<select size="1" name="trigger_filter" id='trigger_filter'>
				<option value="">全部触发器</option>
		<%foreach from=$trigger_lists item=eachone key=keyname %>
				<option value="<% $eachone.id %>" <% if $trigger_filter eq $eachone.id %>selected<%/if%>><% $eachone.triggername %></option>
		<%/foreach%>
			</select>
			</td>
			<td height="30">录音文件&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;个</td>
			<td height="30" align='right'><a href="?action=func_recordfiles_list&trigger_filter=<% $trigger_filter %>&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?action=func_recordfiles_list&trigger_filter=<% $trigger_filter %>&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?action=func_recordfiles_list&trigger_filter=<% $trigger_filter %>&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" colspan='3'>
			<input type="submit" value="清理掉所选类型全部录音" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
	<table border="0" width="100%">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">呼叫起始</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">主叫</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">被叫</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">方式</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">起始</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">容量</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">播放</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$recordfiles_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="option_static.php?action=page_statistical_main&id=<% $eachone.cdr.id %>" target="_blank"><% $eachone.cdr.calldate %></a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 9pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.cdr.src eq $eachone.args_res.number %><font color='#BD0F25'><%/if%><%  $eachone.cdr.src %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 9pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.cdr.dst eq $eachone.args_res.number %><font color='#BD0F25'><%/if%><%  $eachone.cdr.dst %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 9pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.args_res.type eq 'caller' %>主叫<%/if%><% if $eachone.args_res.type eq 'callee' %>被叫<%/if%><% if $eachone.args_res.type eq 'queue' %>队列被叫<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<%  $eachone.cretime_date.1 %></td>
					<td height="30" align="right" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.filesize %>KB&nbsp;&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="pbx_uservoice.php?action=do_uservoice_download&id=<% $eachone.id %>" target="_blank" title="试听或下载"><img src="../images/icon/xi66.png" border='0'></a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_record.php?action=do_recordfiles_delete&id=<% $eachone.id %>&trigger_filter=<% $trigger_filter %>"></td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
