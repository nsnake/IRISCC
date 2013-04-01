<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$("#select_ivrtreeview").change(function(){
		if ($("#select_ivrtreeview").val() != '')
		{
			$("#ivrtree").attr("src",'acd_ivrmenu.php?action=func_ivr_treeview&ivrnumber='+$("#select_ivrtreeview").val());
		}
	});

<% if $selected_ivrnumber ne '' %>
	$("#ivrtree").attr("src",'acd_ivrmenu.php?action=func_ivr_treeview&ivrnumber=<% $selected_ivrnumber %>');
<%/if%>

});
</script>
<!-- body -->
<div id="body-body">
	<div align="left">
	<table border="0" cellspacing="0" width='810'>
		<tr>
			<td align="left"><b>IVR菜单</b>&nbsp;&nbsp;&nbsp;&nbsp;选择已创建的&nbsp;&nbsp;
			<select size="1" name="select_ivrtreeview" id='select_ivrtreeview'>
			<option value="">　　　　　　　　</option>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>" <% if $selected_ivrnumber eq $eachone.ivrnumber %>selected<%/if%>><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			<option value="">----系统默认----</option>
		<%foreach from=$ivrmenu_readonly item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>" <% if $selected_ivrnumber eq $eachone.ivrnumber %>selected<%/if%>><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>
			</td>
			<td align="left">
			<input type="button" value="创建新的IVR菜单" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivrmenu_add">
			</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
	</table>
	<table border="0" cellspacing="0" cellpadding="0" width="810" id="ivrzone">
		<tr>
			<td align='left'><iframe id='ivrtree' name='ivrtree' src='#' width='350' height='400' frameborder="0" style="border-style: solid; border-width: 1px"></iframe></td>
			<td align='right'><iframe id='ivropt' name='ivropt' src='#' width='440' height='400' frameborder="0"></iframe></td>
		</tr>
	</table>
	</div>
</div>


<% include file="cpanel/page_footer.inc.tpl" %>
