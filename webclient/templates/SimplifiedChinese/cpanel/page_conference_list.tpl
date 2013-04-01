<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});
	$(".kickbtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('这个操作将清理掉会议室中所有人, 继续吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

});
</script>
<div id="body-body">
	<h3>电话会议</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;话机拨打会议室密码进入会议室之后可以一起参与讨论问题,会议室属于"本地处理"资源,系统会自动识别哪个号码是分机或是会议室.</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加会议室" name="button" id='btn1' class='showpopDialog' func="acd_conference.php?action=func_conference_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前会议室&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;间</td>
			<td height="30" align="right" width="50%"><a href="?order=<% $order %>&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?order=<% $order %>&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?order=<% $order %>&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="550" align="left" cellspacing="0" cellpadding="0" >
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=confno">会议室号码</a><% if $order eq 'confno' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">密码</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=cretime">创建时间</a><% if $order eq 'cretime' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">当前人数</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<img src="../images/icon/pi145.png"></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;&nbsp;<% $eachone.confno %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.pincode %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.cretime %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class='tipmsg' title="<% $eachone.listed_name %>">&nbsp;<input type="button" value="<% $eachone.list %>" name="button" class="kickbtn" id='btn2' gotourl="acd_conference.php?action=do_conference_kick&confno=<% $eachone.confno %>"></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="acd_conference.php?action=func_conference_edit&confno=<% $eachone.confno %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_conference.php?action=do_conference_delete&confno=<% $eachone.confno %>">&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
