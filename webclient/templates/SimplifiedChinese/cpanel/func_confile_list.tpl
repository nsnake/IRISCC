<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除配置文件吗?一旦删除就不可恢复.' ,'提示' , function(r) {
			if (r == true)
			{
				window.location.href=gotourl;
//				$(this).attr('src',gotourl);
			}
		});
	});

});
</script>
	<h4><% if $folder eq 'freeiris' %>主配置文件<%else%>通信配置文件<%/if%></h4>

	<div align="left">
	<table border="0" width="100%" align="left" style="margin-top: 20px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;">文件名称</td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;">尺寸</td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;">权限</td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;">修改时间</td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$filelist item=eachone key=keyname %>
 				<tr>
					<td height='30' align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;&nbsp;<% if $eachone.filecandel eq '1' %><a href="?action=func_confile_edit&folder=<% $folder|escape:'url' %>&filename=<% $eachone.filename|escape:'url' %>"><%/if%><% $eachone.filename %></a></td>
					<td height='30' align="right" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.filesize %>K&nbsp;</td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.fileperms %></td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.filemtime %></td>
					<td height='30' align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% if $eachone.filecandel eq '1' %><input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="option_advanced.php?action=do_confile_delete&folder=<% $folder|escape:'url' %>&filename=<% $eachone.filename|escape:'url' %>"><%/if%></td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

<% include file="cpanel/func_footer.inc.tpl" %>
