<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除这条外线吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

});
</script>
<div id="body-body">
	<h3>中继管理</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%"><span class="tipmsg" title="没有关于这个的帮助主题" style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;?&nbsp;</b></span>&nbsp;如何使用这个功能</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加一条外线" name="button" id='btn1' class='showpopDialog' func="trunk_manage.php?action=func_trunk_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前所有外线&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;条</td>
			<td height="30" align="right" width="50%"><a href="?order=<% $order %>&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?order=<% $order %>&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?order=<% $order %>&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="750" align="left" cellspacing="0" cellpadding="0" >
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">中继名称</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=trunkproto">协议类型</a><% if $order eq 'trunkproto' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">设备名</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=cretime">创建时间</a><% if $order eq 'cretime' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">注释</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.trunkname %></span>
					</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class='tipmsg' title="<% if $eachone.trunkprototype eq 'ip' %>IP地址验证<% elseif $eachone.trunkprototype eq 'iad' %>语音网关验证<%elseif $eachone.trunkprototype eq 'reg'%>用户名和密码验证<%else%>其他验证<%/if%> : <% $eachone.reg_state %>">&nbsp;&nbsp;<% if $eachone.trunkproto eq 'dahdi' %><% $eachone.trunkprototype %><%else%><% $eachone.trunkproto %><%/if%>&nbsp;<% if $eachone.reg_state eq 'Registered' %><img src="../images/icon/16.png"><% elseif $eachone.reg_state ne '' %><img src="../images/icon/16gray.png"><%/if%></td>



					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.trunkdevice %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.cretime %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.trunkremark %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="trunk_manage.php?action=func_trunk_edit_<% $eachone.trunkproto %>&id=<% $eachone.id %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="trunk_manage.php?action=do_trunk_delete_<% $eachone.trunkproto %>&id=<% $eachone.id %>">&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>


<% include file="cpanel/page_footer.inc.tpl" %>
