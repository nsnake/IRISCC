<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">

	//----------------动作选择器
	action = new Array();
	var edit_selected_actpoint;
	var edit_selected_gotoivr;

<%foreach from=$ivrmenu_action_array item=eachone key=array_number %>
	action[<% $array_number %>] = new Array();
<%foreach from=$eachone item=eachfile key=keyname %>

	action[<% $array_number %>][<% $keyname %>] = new Array();
	action[<% $array_number %>][<% $keyname %>][0] = '<% $keyname %>';
	action[<% $array_number %>][<% $keyname %>][1] = '<% $eachfile.id %>';
	action[<% $array_number %>][<% $keyname %>][2] = '<% $eachfile.ivrnumber %>';
	action[<% $array_number %>][<% $keyname %>][3] = '<% $eachfile.proirety %>';
	action[<% $array_number %>][<% $keyname %>][4] = '<% $eachfile.actmode %>';
	<% if $eachfile.actmode eq '10' %>action[<% $array_number %>][<% $keyname %>][5] = '播放语音';
	<% elseif $eachfile.actmode eq '11' %>action[<% $array_number %>][<% $keyname %>][5] = '发起录音';
	<% elseif $eachfile.actmode eq '12' %>action[<% $array_number %>][<% $keyname %>][5] = '播放录音';
	<% elseif $eachfile.actmode eq '20' %>action[<% $array_number %>][<% $keyname %>][5] = '录制0-9字符';
	<% elseif $eachfile.actmode eq '21' %>action[<% $array_number %>][<% $keyname %>][5] = '读出0-9字符';
	<% elseif $eachfile.actmode eq '22' %>action[<% $array_number %>][<% $keyname %>][5] = '数字方式读出';
	<% elseif $eachfile.actmode eq '30' %>action[<% $array_number %>][<% $keyname %>][5] = '读出日期时间';
	<% elseif $eachfile.actmode eq '31' %>action[<% $array_number %>][<% $keyname %>][5] = '检测日期';
	<% elseif $eachfile.actmode eq '40' %>action[<% $array_number %>][<% $keyname %>][5] = '主叫变换';
	<% elseif $eachfile.actmode eq '41' %>action[<% $array_number %>][<% $keyname %>][5] = '拨打号码';
	<% elseif $eachfile.actmode eq '42' %>action[<% $array_number %>][<% $keyname %>][5] = '跳转信箱或传真';
	<% elseif $eachfile.actmode eq '43' %>action[<% $array_number %>][<% $keyname %>][5] = '跳转到IVR菜单';
	<% elseif $eachfile.actmode eq '44' %>action[<% $array_number %>][<% $keyname %>][5] = 'WEB交互接口';
	<% elseif $eachfile.actmode eq '45' %>action[<% $array_number %>][<% $keyname %>][5] = 'AGI扩展接口';
	<% elseif $eachfile.actmode eq '80' %>action[<% $array_number %>][<% $keyname %>][5] = '等待几秒';
	<% elseif $eachfile.actmode eq '81' %>action[<% $array_number %>][<% $keyname %>][5] = '播放音调';
	<% elseif $eachfile.actmode eq '99' %>action[<% $array_number %>][<% $keyname %>][5] = '挂机';<%/if%>
<% if $eachfile.ivrnumber eq $args_array.gotoivr %>
edit_selected_gotoivr = <% $array_number %>;
<%/if%>
<% if $eachfile.id eq $args_array.actpoint %>
edit_selected_actpoint = <% $keyname %>;
<%/if%>
<%/foreach%>

<%/foreach%>
 

	$().ready(function() {
		//选择目录时
		$("#select_gotoivr").change(function(){
			add_filename();
		});
		//启动完成后
		$("#select_gotoivr")[0].selectedIndex = edit_selected_gotoivr;//选择folderid
		add_filename();
		$("#select_actpoint")[0].selectedIndex = edit_selected_actpoint+1;//选择folderid
	});
	function remove_filename() {
		$("#select_actpoint option").each(function(){
			$(this).remove();
		});
	}
	function add_filename() {
		remove_filename();
		selectedId = $("#select_gotoivr")[0].selectedIndex;
		$("<option value=''>IVR菜单起点</option>").appendTo($("#select_actpoint"));
		for (i=0;i<=(action[selectedId].length-1) ;i++ )
		{
			$("<option value='"+action[selectedId][i][1]+"'>"+action[selectedId][i][0]+"."+action[selectedId][i][5]+"</option>").appendTo($("#select_actpoint"));
		}
	}

</script>
	<h4>编辑动作 检测日期</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >时间范围</td>
			<td height="30">从&nbsp;<input type="text" id="iptext1" name="from_hour" size="2" value="<% $args_array.from_hour %>">点<input type="text" id="iptext1" name="from_min" size="2" value="<% $args_array.from_min %>">分 到 <input type="text" id="iptext1" name="to_hour" size="2" value="<% $args_array.to_hour %>">点<input type="text" id="iptext1" name="to_min" size="2" value="<% $args_array.to_min %>">分&nbsp;&nbsp;<input type="checkbox" value="true" name="timeall" <%if $args_array.timeall eq 'true'%>checked<%/if%>>全部时间段</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >周期范围</td>
			<td height="30">从&nbsp;<select size="1" name="from_week">
			<option value="0" <% if $args_array.from_week eq '0' %>selected<%/if%>>礼拜日</option>
			<option value="1" <% if $args_array.from_week eq '1' %>selected<%/if%>>礼拜一</option>
			<option value="2" <% if $args_array.from_week eq '2' %>selected<%/if%>>礼拜二</option>
			<option value="3" <% if $args_array.from_week eq '3' %>selected<%/if%>>礼拜三</option>
			<option value="4" <% if $args_array.from_week eq '4' %>selected<%/if%>>礼拜四</option>
			<option value="5" <% if $args_array.from_week eq '5' %>selected<%/if%>>礼拜五</option>
			<option value="6" <% if $args_array.from_week eq '6' %>selected<%/if%>>礼拜六</option>
			</select> 到 <select size="1" name="to_week">
			<option value="0" <% if $args_array.to_week eq '0' %>selected<%/if%>>礼拜日</option>
			<option value="1" <% if $args_array.to_week eq '1' %>selected<%/if%>>礼拜一</option>
			<option value="2" <% if $args_array.to_week eq '2' %>selected<%/if%>>礼拜二</option>
			<option value="3" <% if $args_array.to_week eq '3' %>selected<%/if%>>礼拜三</option>
			<option value="4" <% if $args_array.to_week eq '4' %>selected<%/if%>>礼拜四</option>
			<option value="5" <% if $args_array.to_week eq '5' %>selected<%/if%>>礼拜五</option>
			<option value="6" <% if $args_array.to_week eq '6' %>selected<%/if%>>礼拜六</option>
			</select>&nbsp;&nbsp;<input type="checkbox" value="true" name="weekall" <%if $args_array.weekall eq 'true'%>checked<%/if%>>整周</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >日期范围</td>
			<td height="30">从&nbsp;<input type="text" id="iptext1" name="from_day" size="2" value="<% $args_array.from_day %>">号 到 <input type="text" id="iptext1" name="to_day" size="2" value="<% $args_array.to_day %>">号 &nbsp;&nbsp;<input type="checkbox" value="true" name="dayall" <%if $args_array.dayall eq 'true'%>checked<%/if%>>整月</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >月份范围</td>
			<td height="30">从&nbsp;<input type="text" id="iptext1" name="from_month" size="2" value="<% $args_array.from_month %>">月 到 <input type="text" id="iptext1" name="to_month" size="2" value="<% $args_array.to_month %>">月 &nbsp;&nbsp;<input type="checkbox" value="true" name="monthall" <%if $args_array.monthall eq 'true'%>checked<%/if%>>任何月份</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'>满足以上条件后将如下执行</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >跳转到IVR菜单</td>
			<td height="30">
			<select size="1" name="gotoivr" id='select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >位置在</td>
			<td height="30">
			<select size="1" name="actpoint" id='select_actpoint'>
			</select>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
