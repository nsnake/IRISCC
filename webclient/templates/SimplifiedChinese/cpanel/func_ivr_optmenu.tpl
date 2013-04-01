<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){top.loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除IVR菜单吗?' ,'提示' , function(r) {
			if (r == true)
			{
				top.$('#takefire').attr('src',gotourl);
			}
		});
	});

	$(".jumpto").click(function(){
		top.document.location.href=$(this).attr("func");
	});

});
</script>
	<table border="0" cellspacing="0" style='letter-spacing: 1px;margin-top:10px'>
		<tr>
			<td height="30" align="left">IVR菜单:&nbsp;<b><% $ivrmenu.ivrnumber %></b> - <% $ivrmenu.ivrname %></td>
		</tr>
		<tr>
			<td height="30" align="left"valign='top'>IVR菜单描述:&nbsp;<% $ivrmenu.description %></td>
		</tr>
<% if $ivrmenu.readonly ne '1'%>
		<tr>
			<td height="30" align="center">&nbsp;<input type="button" value="编辑IVR" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivrmenu_edit&ivrnumber=<% $ivrmenu.ivrnumber %>">&nbsp;<input type="button" value="删除IVR" name="button" class="deletebtn" id='btn2' gotourl="acd_ivrmenu.php?action=do_ivrmenu_delete&ivrnumber=<% $ivrmenu.ivrnumber %>">&nbsp;</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style='letter-spacing: 1px;margin-top:10px'>
		<tr>
			<td height="30" align="left">动作选项:</td>
		</tr>
		<tr>
			<td height="30" align="left">&nbsp;<input type="button" value="设置动作" name="button" id='btn1' class='jumpto' func="acd_ivrmenu.php?action=page_ivraction_list&ivrnumber=<% $ivrmenu.ivrnumber %>">&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left"><font color="#ACA8A1">IVR菜单中每一个环节都属于一个动作,您可以创建新的动作或修改已有动作的优先关系.</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style='letter-spacing: 1px;margin-top:10px'>
		<tr>
			<td height="30" align="left">用户输入选项:</td>
		</tr>
		<tr>
			<td height="30" align="left">&nbsp;<input type="button" value="设置用户输入" name="button" id='btn1' class='jumpto' func="acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber=<% $ivrmenu.ivrnumber %>">&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left"><font color="#ACA8A1">在所有动作都完成后,系统将接受一定按键选择,可以帮助你的用户跳转到你希望的其他IVR菜单中.</td>
		</tr>
<%/if%>
	</table>
<% include file="cpanel/func_footer.inc.tpl" %>
