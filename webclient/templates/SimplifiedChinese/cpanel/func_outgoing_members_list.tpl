<% include file="cpanel/func_header.inc.tpl" %>
	<h4>外呼成员号码处理状态</h4>
	<div align="left">
	<table border="0" width="100%">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">外呼号码</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">处理状态</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<%  $eachone.number %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.status eq '0' %>未呼叫<% elseif $eachone.status eq '1' %>已呼叫<%else%>已接起<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
