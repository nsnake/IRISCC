<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var groupname = $(this).attr("groupname");
		var groupid = $(this).attr("groupid");
		jConfirm('确认要删除编号为 '+groupid+' 的分组 '+groupname+'  吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',"exten_group.php?action=do_group_delete&groupid="+groupid);
			}
		});
	});

});
</script>
<div id="body-body">
	<h3>分组管理</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%"><span class="tipmsg" title="没有关于这个的帮助主题" style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;?&nbsp;</b></span>&nbsp;如何使用这个功能</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="创建新的分组" name="button" id='btn1' class='showpopDialog' func="exten_group.php?action=func_group_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">系统限制最多允许有64个分组</td>
			<td height="30" align="right" width="50%"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="600" align="left" cellspacing="0" cellpadding="0" >
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=groupid">分组编号</a><% if $order eq 'groupid' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=groupname">分组名称</a><% if $order eq 'groupname' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">备注</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=cretime">创建时间</a><% if $order eq 'cretime' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">成员数量</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.groupid %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.groupname %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.remark %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.cretime %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class="tipmsg" title="<%foreach from=$eachone.exteningroup_array item=subeachone key=subkeyname %><% $subeachone|escape:'html' %>, <%/foreach%>">&nbsp;<% $eachone.exteningroup_array|@count %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="exten_group.php?action=func_group_edit&groupid=<% $eachone.groupid %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' groupid=<% $eachone.groupid %> groupname=<% $eachone.groupname %>>&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
