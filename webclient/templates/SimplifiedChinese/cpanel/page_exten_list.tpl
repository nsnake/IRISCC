<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除这个分机吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

	$(".diagnosis0").click(function(){
		var accountcode = $(this).attr("accountcode");
		jConfirm('是否向分机 '+accountcode+' 发起测试呼叫(如果呼叫失败将标识为警告)' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',"exten_manage.php?action=do_exten_diagnosis&accountcode="+accountcode);
			}
		});
	});
	$(".diagnosis1").click(function(){
		var accountcode = $(this).attr("accountcode");
		jConfirm('是否向分机 '+accountcode+' 发起测试呼叫(如果呼叫失败将标识为警告)' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',"exten_manage.php?action=do_exten_diagnosis&accountcode="+accountcode);
			}
		});
	});
	$(".diagnosis2").click(function(){
		var accountcode = $(this).attr("accountcode");
		jConfirm('这个分机现在标识为正常, 是否向分机 '+accountcode+' 发启测试呼叫(如果呼叫失败将标识为警告)' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',"exten_manage.php?action=do_exten_diagnosis&accountcode="+accountcode);
			}
		});
	});

});
</script>
<div id="body-body">
	<h3>分机管理</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%"><span class="tipmsg" title="没有关于这个的帮助主题" style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;?&nbsp;</b></span>&nbsp;如何使用这个功能</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="创建新的分机" name="button" id='btn1' class='showpopDialog' func="exten_manage.php?action=func_exten_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前所有分机&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;部</td>
			<td height="30" align="right" width="50%"><a href="?order=<% $order %>&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?order=<% $order %>&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?order=<% $order %>&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="750" align="left" cellspacing="0" cellpadding="0" >
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=accountcode">分机帐号</a><% if $order eq 'accountcode' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=deviceproto">号码类型</a><% if $order eq 'deviceproto' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">分组</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">真实姓名</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=cretime">创建时间</a><% if $order eq 'cretime' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">注释</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;
						<% if $eachone.fristchecked eq '0' %>
							<span class="tipmsg" title="未进行检测,点击进行呼叫检测."><a href="###" class="diagnosis0" accountcode=<% $eachone.accountcode %>><img src="../images/icon/52.png" border='0'></a></span>
						<%elseif $eachone.fristchecked eq '1' %>
							<span class="tipmsg" title="最近一次这个分机检测结果为失败,点击这里再次检测."><a href="###" class="diagnosis1" accountcode=<% $eachone.accountcode %>><img src="../images/icon/73.png" border='0'></a></span>
						<%else%>
							<a href="###" class="diagnosis2" accountcode=<% $eachone.accountcode %>><img src="../images/icon/xa38.png" border='0'></a>
						<%/if%>
						&nbsp;<span class="tipmsg" title="帐号: <% $eachone.accountcode|escape:'html' %><br>分机号: <% $eachone.devicenumber %><br>设备:  <% $eachone.devicestring %>" ><% $eachone.accountcode %></span>
					</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% if $eachone.deviceproto eq 'virtual' %>虚拟<% elseif $eachone.deviceproto eq 'custom' %>自定义<%else%><% $eachone.deviceproto %><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><span class="tipmsg" title="<%foreach from=$eachone.group_array item=subeachone key=subkeyname %><% $subeachone|escape:'html' %>, <%/foreach%>" ><% if $eachone.group_array|@count gt 0 %><img src='../images/icon/91.png'><%/if%></span></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><span class="tipmsg" title="姓名: <% $eachone.info_name|escape:'html' %><br>Email: <% $eachone.info_email|escape:'html' %><br>说明: <% $eachone.info_detail|escape:'html' %>" ><% $eachone.info_name %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% $eachone.cretime %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.info_remark %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="exten_manage.php?action=func_exten_edit_<% $eachone.deviceproto %>&accountcode=<% $eachone.accountcode %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="exten_manage.php?action=do_exten_delete_<% $eachone.deviceproto %>&accountcode=<% $eachone.accountcode %>">&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
