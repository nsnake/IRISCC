<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

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

	$("#colorselected tr").mouseover(function(){
		if (!$(this).hasClass("selected_color_callsession_disabled"))
		{
			for (c = 0; c < $(this.cells).length; c++) { $(this.cells)[c].style.backgroundColor = '#0C8AD6'; }
			$(this).css({ cursor: 'pointer' });
		}
	});
	$("#colorselected tr").mouseout(function(){
		if (!$(this).hasClass("selected_color_callsession_disabled"))
		{
			for (c = 0; c < $(this.cells).length; c++) { $(this.cells)[c].style.backgroundColor = '#FFFFFF'; }
		}
	});

});
</script>
<div id="body-body">
	<h3>自动外呼</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;自动外呼在完成一条之后才执行其他的外呼,呼叫是每10秒一次,呼叫实际量是 呼叫频率(成员-每次并发).</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加外呼计划" name="button" id='btn1' class='showpopDialog' func="acd_outgoing.php?action=func_outgoing_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left">当前&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)</td>
			<td height="30" align="right">
			<a href="?action=page_outgoing_list&cols_in_page=frist"><img src='../images/icon/38.png' border='0'></a>
			<a href="?action=page_outgoing_list&cols_in_page=<% $pre_cols %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;
			<a href="?action=page_outgoing_list&cols_in_page=<% $next_cols %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="750" align="left" cellspacing="0" cellpadding="0" id='colorselected'>
 				<tr class='selected_color_callsession_disabled'>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">说明</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">状态</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">并发限制</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">成员->完成</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">创建时间</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">外呼开始时间</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">本地号码</td>
 					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr class='showpopDialog' func="?action=func_outgoing_members_list&outgoingid=<% $eachone.id %>">
					<td height="30" id='colorline' align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.name %></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% if $eachone.tune eq '0' %>新外呼<% elseif $eachone.tune eq '1' %>已完成<%else%>处理中<%/if%></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" >&nbsp;<% $eachone.concurrent %></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" ><% $eachone.numbercount %>--><% $eachone.calledcount %></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" >&nbsp;<% $eachone.cretime %></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" >&nbsp;<% $eachone.startime %></td>
					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" >&nbsp;<% $eachone.localnumber %></td>
 					<td height="30" id='colorline' align="center" style="border-bottom: #ACA8A1 1px solid;" >&nbsp;<!-- <input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_outgoing.php?action=do_outgoing_delete&id=<% $eachone.id %>"> -->&nbsp;详&nbsp;细</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
