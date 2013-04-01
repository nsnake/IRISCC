<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {
    // Initialise the table
    $('#router').tableDnD({
        onDrop: function(table, row) {
		$('.applyrules').show('slow');		
//            alert($('#router').tableDnDSerialize());
        }
    });

    $("#router tr").hover(function() {
          $(this.cells[0]).addClass('showDragHandle');
    }, function() {
          $(this.cells[0]).removeClass('showDragHandle');
    });

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".applyrules").click(function() {
		var gotourl = "exten_router.php?action=do_router_recall&"+$('#router').tableDnDSerialize();
		jConfirm('确认要保存吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
    });

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除这条规则吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

});
</script>


<!-- body -->
<div id="body-body">
	<h3>分机拨出规则</h3>

	<div align="left">
	<table border="0" width="90%" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%"><span class="tipmsg" title="没有关于这个的帮助主题" style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;!&nbsp;</b></span>&nbsp;当分机拨号时将按照以下规则进行处理，如果没有匹配将根据通话参数中的设置进行默认处理。</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加新的拨号规则" name="button" id='btn1' class='showpopDialog' func="exten_router.php?action=func_router_add"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="点击这里 保存规则的优先顺序!" name="button" id='btn3' class="applyrules" style="display:none"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">拨号规则&nbsp;共&nbsp;<% $maxcount %>&nbsp;条</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0" id='router'>
 				<tr class="nodrop nodrag">
					<td height="30" width="32" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">优先</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">名称</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">表达式</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">处理方式</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">外线中继</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr  id='<% $eachone.id %>'>
					<td height="30" width="32" align="center" style="border-bottom: #ACA8A1 1px solid;" <% if $eachone.createmode ne '1' %>bgcolor='#F3F3F3'<%/if%>>&nbsp;</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.proirety_aslevel %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.routername %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid; font-size: 12px"  class='tipmsg' title="UniqueID:    <% $eachone.id %><br>
					匹配主叫分组:    <% $eachone.match_callergroup %><br>
					匹配主叫号码:    <% $eachone.match_callerid %><br>
					匹配主叫长度:    <% $eachone.match_callerlen %><br>
					匹配被叫号码:    <% $eachone.match_callednum %><br>
					匹配被叫长度:    <% $eachone.match_calledlen %><br>
					替换主叫号码:    <% $eachone.replace_callerid %><br>
					删除被叫几位:    <% $eachone.replace_calledtrim %><br>
					补充被叫前缀:    <% $eachone.replace_calledappend %>">
					&nbsp;<a href="###">号码 <% if $eachone.match_callednum ne '' %>以 <% $eachone.match_callednum %> 开头<%/if%> <% if $eachone.match_calledlen ne '' %>长度为 <% $eachone.match_calledlen %> 位<%/if%></a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;"><% if $eachone.process_mode eq '0' %>黑名单<% elseif $eachone.process_mode eq '1' %>本地处理<% elseif $eachone.process_mode eq '2' %>出局外线<%/if%>&nbsp;<% if $eachone.process_defined eq 'extension' %>分机<% elseif $eachone.process_defined eq 'ivr' %>IVR菜单<% elseif $eachone.process_defined eq 'conference' %>电话会议<% elseif $eachone.process_defined eq 'queue' %>呼叫队列<% elseif $eachone.process_defined ne '' %>其他<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.process_defined_trunk_result.trunkname %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">
<% if $eachone.createmode eq '0' %>
&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="exten_router.php?action=do_router_delete&id=<% $eachone.id %>">&nbsp;
<%elseif $eachone.createmode eq '1' %>
&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="exten_router.php?action=func_router_edit&id=<% $eachone.id %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="exten_router.php?action=do_router_delete&id=<% $eachone.id %>">&nbsp;
<%/if%>
					</td>
				</tr>
		<%/foreach%>
		<%foreach from=$nodelrule_array item=eachone key=keyname %>
 				<tr id='' class="nodrop nodrag">
					<td height="30" width="32" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'>&nbsp;</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'>&nbsp;N</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'>&nbsp;<% $eachone.routername %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid; font-size: 11px"  class='tipmsg' title="UniqueID:    <% $eachone.id %><br>
					匹配主叫分组:    <% $eachone.match_callergroup %><br>
					匹配主叫号码:    <% $eachone.match_callerid %><br>
					匹配主叫长度:    <% $eachone.match_callerlen %><br>
					匹配被叫号码:    <% $eachone.match_callednum %><br>
					匹配被叫长度:    <% $eachone.match_calledlen %><br>
					替换主叫号码:    <% $eachone.replace_callerid %><br>
					删除被叫几位:    <% $eachone.replace_calledtrim %><br>
					补充被叫前缀:    <% $eachone.replace_calledappend %>" bgcolor='#F3F3F3'>&nbsp;<a href="###">号码 <% if $eachone.match_callednum ne '' %>以 <% $eachone.match_callednum %> 开头<%/if%> <% if $eachone.match_calledlen ne '' %>长度为 <% $eachone.match_calledlen %> 位<%/if%></a></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'><% if $eachone.process_mode eq '0' %>黑名单<% elseif $eachone.process_mode eq '1' %>本地处理<% elseif $eachone.process_mode eq '2' %>出局外线<%/if%>&nbsp;<% if $eachone.process_defined eq 'extension' %>分机<% elseif $eachone.process_defined eq 'ivr' %>IVR菜单<% elseif $eachone.process_defined eq 'conference' %>电话会议<% elseif $eachone.process_defined eq 'queue' %>呼叫队列<% elseif $eachone.process_defined ne '' %>其他<%/if%></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'>&nbsp;<% $eachone.process_defined_trunk_result.trunkname %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;" bgcolor='#F3F3F3'>&nbsp;
					</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
