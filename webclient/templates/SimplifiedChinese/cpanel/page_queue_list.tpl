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

});
</script>
<div id="body-body">
	<h3>呼叫队列</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加呼叫队列" name="button" id='btn1' class='showpopDialog' func="acd_queue.php?action=func_queue_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前呼叫队列&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;共&nbsp;<% $maxcount %>&nbsp;组</td>
			<td height="30" align="right" width="50%"><a href="?cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a><a href="?cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;<a href="?cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="650" align="left" cellspacing="0" cellpadding="0" >
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=queuenumber">队列号码</a><% if $order eq 'queuenumber' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">队列名称</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">坐席成员</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><a href="?order=cretime">创建时间</a><% if $order eq 'cretime' %>&nbsp;<img src='../images/icon/07.png'><%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<img src="../images/icon/pi110.png">&nbsp;&nbsp;<b><% $eachone.queuenumber %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.queuename %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" class="tipmsg" title="<% $eachone.members %>" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.members_count %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.cretime %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="acd_queue.php?action=func_queue_edit&queuenumber=<% $eachone.queuenumber %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_queue.php?action=do_queue_delete&queuenumber=<% $eachone.queuenumber %>">&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
