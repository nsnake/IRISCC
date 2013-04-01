<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});


});
</script>


<!-- body -->
<div id="body-body">
	<h3>话费对帐单</h3>

	<div align="left">
	<table border="0" width="90%" align="left" style="margin: 0px">
		<tr>
			<td height="30" align="left" width="100%">&nbsp;每月1号以后将可以下载上一月份的对帐单,系统保留12个月的对帐单数据. 不同于计费本功能不进行呼叫检测.</td>
		</tr>
		<tr>
			<td align="left">
		<form name="do_ratetable_add" method="POST" action="?action=do_ratetable_add" target="takefire">
			<table width="80%" border="0" cellspacing="0" style="margin-top: 20px">
				<tr>
					<td height="30" align="left" colspan='5'><b>费率设置</b></td>
				</tr>
				<tr>
					<td height="30" align="center" width="100%" colspan="5"><hr size="1"></td>
				</tr>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">号码开头</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">说明</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">计费单位</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">计费单价</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$rules item=eachone key=keyname %>
				<tr>
					<td  align="right" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.dst_prefix %>&nbsp;&nbsp;&nbsp;</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.destnation %></td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.persecond %>&nbsp;秒</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.percost %></td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="pbx_billing.php?action=do_ratetable_delete&id=<% $eachone.id %>" target="takefire">删除</a>&nbsp;</td>
				</tr>
		<%/foreach%>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><input type="text" id="iptext1" name="dst_prefix" size="8" value="" class="tipmsg" title="被叫号码的前缀"></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><input type="text" id="iptext1" name="destnation" size="16" value="" class="tipmsg" title="说明"></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><input type="text" id="iptext1" name="persecond" size="4" value="60" class="tipmsg" title="计费单位"></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><input type="text" id="iptext1" name="percost" size="6" value="0.3" class="tipmsg" title="计费单价"></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><input type="submit" value="增加新费率" name="submit" id='btn1'></td>
				</tr>
			</table>
		</form>
			</td>
		</tr>


		<tr>
			<td>
			<table width="400" border="0" cellspacing="0" style="margin-top: 20px">
				<tr>
					<td height="30" align="left" colspan='3'><b>对帐单下载</b></td>
				</tr>
				<tr>
					<td height="30" align="center" width="100%" colspan="3"><hr size="1"></td>
				</tr>
				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">对帐单日期</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">CSV格式</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">XLS格式</td>
				</tr>
		<%foreach from=$invoice_date item=eachone key=keyname %>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.year %> 年 <% $eachone.month %> 月</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="pbx_billing.php?action=do_invoice_download&year=<% $eachone.year %>&month=<% $eachone.month %>&format=csv">CSV</a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<a href="pbx_billing.php?action=do_invoice_download&year=<% $eachone.year %>&month=<% $eachone.month %>&format=xls">XLS</a></td>
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
