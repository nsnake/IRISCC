<% include file="cpanel/func_header.inc.tpl" %>
	<h4>会话 <% $callsession.accountcode %> 打给 <% $callsession.extension %></h4>
<% $callsession.cretime %>&nbsp;<% $cdr.disposition %>&nbsp;<% $cdr.billsec %>秒
	<div align="left">
	<table border="0" width="100%" align="left" style="margin-top: 10px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">编号</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">操作功能</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">参数</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">参数</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">参数</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">参数</td>
				</tr>
		<%foreach from=$sessionflow item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class="tipmsg" title="<% $eachone.acttime %>">&nbsp;<% if $eachone.cdruniqueid eq $cdruniqueid %><font color='#BD0F25'><%/if%><% $eachone.actid %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class="tipmsg" title="<% $eachone.function %>">&nbsp;
<% if $eachone.function eq 'sysautomon' %>
自动录音
<% elseif $eachone.function eq 'outbound' %>
拨外线
<% elseif $eachone.function eq 'automon' %>
一键录音
<% elseif $eachone.function eq 'router' %>
路由处理
<% elseif $eachone.function eq 'sendfax' %>
发传真
<% elseif $eachone.function eq 'receivefax' %>
收传真
<% elseif $eachone.function eq 'voicemail' %>
语音信箱
<% elseif $eachone.function eq 'dial_local' %>
拨本地号码
<% elseif $eachone.function eq 'dial_localfailed' %>
本地未接通
<% elseif $eachone.function eq 'queue_answeragent' %>
坐席应答
<% elseif $eachone.function eq 'ivrmenu' %>
IVR菜单
<% elseif $eachone.function eq 'ivraction' %>
IVR动作
<% elseif $eachone.function eq 'ivrmenu_userinput' %>
IVR用户输入
<%else%>
<% $eachone.function %>
<%/if%>
					</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.var0key %>=<% $eachone.var0value %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.var1key %>=<% $eachone.var1value %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.var2key %>=<% $eachone.var2value %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-size: 8pt;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.var3key %>=<% $eachone.var3value %></td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
