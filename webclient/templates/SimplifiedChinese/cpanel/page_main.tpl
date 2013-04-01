<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<!-- body -->
<div id="body-body">

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">系 统 负 载</td>
			<td height="30"><table border="0" height="6" width="<% $cpu_usage %>" id="table1" cellspacing="0" cellpadding="0" bgcolor="#FFFF00" style="border: 1px solid #CCCC00"><tr><td></td></tr></table><% $cpu_usage %>%</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">内 存 负 载</td>
			<td height="30"><table border="0" height="6" width="<% $memory_usage %>" id="table2" cellspacing="0" cellpadding="0" bgcolor="#FFFF00" style="border: 1px solid #CCCC00"><tr><td></td></tr></table><% $memory_usage %>%</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">工 作 时 间</td>
			<td height="30"><% $uptime_hour %> 小时 <% $uptime_min %> 分</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">分 机 总 数</td>
			<td height="30"><% $extensions_count %> 部</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">中 继 总 数</td>
			<td height="30"><% $trunks_count %> 组</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">24小时通话</td>
			<td height="30"><% $calls_in24 %> 次 [总 <% $calls_all %> 次]</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">MFS Ver.</td>
			<td height="30"><% $freeiris2_version %></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 12px;padding-right: 40px">RPCPBX Ver.</td>
			<td height="30"><% $rpcpbx_version %></td>
		</tr>
	</table>
<% if $release eq 'freeiris' %>
	<div style = 'float: center;'>
    <iframe name='alert' src='?action=page_main_alert' width='780' height='140' frameborder="0" scrolling="no"></iframe>
    </div>
<%/if%>
</div>

<% include file="cpanel/page_footer.inc.tpl" %>

