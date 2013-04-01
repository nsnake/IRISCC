<% include file="cpanel/func_header.inc.tpl" %>
	<div align="left">
<form name="filter" method="POST" action="?action=func_faxstats_list" target="_self">
	<table border="0" width="90%" style="margin-top: 10px">
		<tr>
			<td height="30">传真状态&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;个</td>
			<td height="30">帐户&nbsp;<INPUT TYPE="text" NAME="accountcode" size="8" value="<% $accountcode %>">&nbsp;<INPUT TYPE="submit" value="筛选"></td>
			<td height="30" align='right'><a href="?action=func_faxstats_list&accountcode=<% $accountcode %>&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?action=func_faxstats_list&accountcode=<% $accountcode %>&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?action=func_faxstats_list&accountcode=<% $accountcode %>&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
	</table>
</form>
	<table border="0" width="100%">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">时间</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">号码</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">处理状态</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">速率</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">解析度</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">页数</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">下载</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class="tipmsg" title="DEBUG: <% $eachone.id %><BR>mailprocessed: <% $eachone.mailprocessed %><BR>fax_status: <% $eachone.fax_status %><BR>fax_statusstr: <% $eachone.fax_statusstr %><BR>fax_error: <% $eachone.fax_error %><BR>fax_pages: <% $eachone.fax_pages %><BR>fax_bitrate: <% $eachone.fax_bitrate %><BR>fax_remotestationid: <% $eachone.fax_remotestationid %><BR>fax_resolution: <% $eachone.fax_resolution %><BR>fax_ecm: <% $eachone.fax_ecm %>">&nbsp;<% $eachone.cretime %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.mode eq '0' %><% $eachone.accountcode %>--&gt;<% $eachone.number %><%else%><% $eachone.accountcode %>&lt;--<% $eachone.number %><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.mode eq '0' %>发送<%else%>接收<%/if%>&nbsp;
					<% if $eachone.mode eq '0' %><% if $eachone.status eq '0' %>处理中<% elseif $eachone.status eq '1' %>处理中<% elseif $eachone.status eq '2' %>正在发送<% elseif $eachone.status eq '3' %>完成<% elseif $eachone.status eq '4' %>失败<%/if%><%/if%>
					<% if $eachone.mode eq '1' %><% if $eachone.status eq '0' %>接收中<% elseif $eachone.status eq '3' %>完成<% elseif $eachone.status eq '4' %>失败<%/if%><%/if%>
					</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.fax_bitrate %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.fax_resolution %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.fax_pages %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.mode eq '1' %><a href="?action=do_faxfile_download&faxid=<% $eachone.id %>"><img src='../images/icon/pi246.png' border='0'></a><%/if%></td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
