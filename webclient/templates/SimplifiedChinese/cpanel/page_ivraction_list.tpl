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
		var gotourl = "acd_ivrmenu.php?action=do_ivraction_recall&ivrnumber=<% $ivrnumber %>&"+$('#router').tableDnDSerialize();
		jConfirm('确认要保存吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
    });

	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除这项动作吗?' ,'提示' , function(r) {
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
	<h3>IVR菜单(<% $ivrnumber %>)动作编辑器</h3>
	<div align="left">
	<table border="0" width="90%" style="margin: 15px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/32.png" border='0'>&nbsp;<a href='?action=page_ivr_main&ivrnumber=<% $ivrnumber %>'>返回 IVR菜单页</a></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
	</table>
	<table border="0" width="90%" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加新的动作" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_add&ivrnumber=<% $ivrnumber %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="点击这里 保存规则的优先顺序!" name="button" id='btn3' class="applyrules" style="display:none"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前动作&nbsp;共&nbsp;<% $maxcount %>&nbsp;条</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0" id='router'>
 				<tr class="nodrop nodrag">
					<td height="30" width="32" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">优先</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">类型</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">参数</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr  id='<% $eachone.id %>'>
					<td height="30" width="32" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.proirety_aslevel %></td>
<% if $eachone.actmode eq '10' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;播放语音</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xi66.png">&nbsp;'<% $eachone.args_array.folder %>/<% $eachone.args_array.filename %>'</td>

<% elseif $eachone.actmode eq '11' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;发起录音</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/microphone.png">&nbsp;$<% $eachone.args_array.recordvarname %></td>

<% elseif $eachone.actmode eq '12' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;播放录音</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xi66.png">&nbsp;$<% $eachone.args_array.playbackvarname %></td>

<% elseif $eachone.actmode eq '20' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;录制0-9字符</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi401.png">&nbsp;$<% $eachone.args_array.receivevarname %></td>

<% elseif $eachone.actmode eq '21' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;读出0-9字符</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/19.png">&nbsp;<% if $eachone.args_array.playbackvarname ne ''%>$<% $eachone.args_array.playbackvarname %><%else%>'<% $eachone.args_array.saydigits %>'<%/if%></td>

<% elseif $eachone.actmode eq '22' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;数字方式读出</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/19-2.png">&nbsp;<% if $eachone.args_array.playbackvarname ne ''%>$<% $eachone.args_array.playbackvarname %><%else%>'<% $eachone.args_array.saydigits %>'<%/if%></td>

<% elseif $eachone.actmode eq '30' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;读出日期时间</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi126.png">&nbsp;<% if $eachone.args_array.saydatetime eq 'true'%>当前时间<% elseif $eachone.args_array.saydatefromvar ne ''%>日期 $<% $eachone.args_array.saydatefromvar %> <% elseif $eachone.args_array.saytimefromvar ne ''%>时间 $<% $eachone.args_array.saytimefromvar %><% elseif $eachone.args_array.saydatestring ne ''%>日期 '<% $eachone.args_array.saydatestring %>' <% elseif $eachone.args_array.saytimestring ne ''%>时间 '<% $eachone.args_array.saytimestring %>'<%/if%></td>

<% elseif $eachone.actmode eq '31' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;检测日期</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi125.png">&nbsp;日期匹配后跳转 '<% $eachone.args_array.gotoivr %> - <% $eachone.args_array.gotoivr_name %>'</td>

<% elseif $eachone.actmode eq '40' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;主叫变换</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xa38.png">&nbsp;<% if $eachone.args_array.modify eq 'replace'%>替换<%elseif $eachone.args_array.modify eq 'append'%>增补<%elseif $eachone.args_array.modify eq '结尾增加'%><%/if%> '<% $eachone.args_array.altercallerid %>'</td>

<% elseif $eachone.actmode eq '41' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;拨打号码</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/11.png">&nbsp;<% if $eachone.args_array.typeof eq 'extension'%>分机<%elseif $eachone.args_array.typeof eq 'queue'%>队列<%elseif $eachone.args_array.typeof eq 'conference'%>会议室<%else%>任意 <%/if%> <% if $eachone.args_array.dialvarname ne ''%>$<% $eachone.args_array.dialvarname %><%else%>'<% $eachone.args_array.dialdigits %>'<%/if%></td>

<% elseif $eachone.actmode eq '42' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;跳转信箱或传真</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi447.png">&nbsp;<% if $eachone.args_array.dialvarname ne ''%>$<% $eachone.args_array.dialvarname %><%else%>'<% $eachone.args_array.dialdigits %>'<%/if%></td>

<% elseif $eachone.actmode eq '43' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;跳转到IVR菜单</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">&nbsp;跳转 '<% $eachone.args_array.gotoivr %> - <% $eachone.args_array.gotoivr_name %>'</td>

<% elseif $eachone.actmode eq '44' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;WEB交互接口</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">&nbsp;<% $eachone.args_array.urlvar|urldecode %></td>

<% elseif $eachone.actmode eq '45' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;AGI扩展接口</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">&nbsp;<% $eachone.args_array.agi|urldecode %></td>

<% elseif $eachone.actmode eq '80' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;等待几秒</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src='../images/icon/pi237.png'>&nbsp;等待 '<% $eachone.args_array.wait %>' 秒</td>

<% elseif $eachone.actmode eq '81' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;播放音调</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi144.png">&nbsp;'<% $eachone.args_array.playtone %>'</td>

<% elseif $eachone.actmode eq '99' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;挂机</td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/01.png">&nbsp;</td><%/if%>

					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_ivrmenu.php?action=do_ivraction_delete&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>">&nbsp;
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
