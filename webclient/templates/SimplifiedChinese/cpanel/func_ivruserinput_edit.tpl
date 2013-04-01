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
<% if $eachfile.ivrnumber eq $userinput.gotoivrnumber %>
edit_selected_gotoivr = <% $array_number %>;
<%/if%>
<% if $eachfile.id eq $userinput.gotoivractid %>
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
	<h4>编辑用户输入选择</h4>
<form name="do_ivruserinput_edit" method="POST" action="?action=do_ivruserinput_edit&id=<% $userinput.id %>&ivrnumber=<% $ivrnumber %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >用户输入按键</td>
			<td height="30">
			<select size="1" name="input">
			<option value="1" <% if $userinput.input eq '1' %>selected<%/if%>>1</option>
			<option value="2" <% if $userinput.input eq '2' %>selected<%/if%>>2</option>
			<option value="3" <% if $userinput.input eq '3' %>selected<%/if%>>3</option>
			<option value="4" <% if $userinput.input eq '4' %>selected<%/if%>>4</option>
			<option value="5" <% if $userinput.input eq '5' %>selected<%/if%>>5</option>
			<option value="6" <% if $userinput.input eq '6' %>selected<%/if%>>6</option>
			<option value="7" <% if $userinput.input eq '7' %>selected<%/if%>>7</option>
			<option value="8" <% if $userinput.input eq '8' %>selected<%/if%>>8</option>
			<option value="9" <% if $userinput.input eq '9' %>selected<%/if%>>9</option>
			<option value="0" <% if $userinput.input eq '0' %>selected<%/if%>>0</option>
			<option value="*" <% if $userinput.input eq '*' %>selected<%/if%>>*</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >跳转到IVR菜单</td>
			<td height="30">
			<select size="1" name="gotoivrnumber" id='select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >位置在</td>
			<td height="30">
			<select size="1" name="gotoivractid" id='select_actpoint'>
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
