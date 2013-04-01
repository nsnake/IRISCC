<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">

	//----------------动作选择器
	action = new Array();

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
<%/foreach%>

<%/foreach%>
 

	$().ready(function() {
		//选择目录时
		$("#select_gotoivr").change(function(){
			add_filename();
		});
		//启动完成后
		add_filename();
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
	<h4>新动作 拨打号码</h4>
<form name="do_ivraction_add" method="POST" action="?action=do_ivraction_add&ivrnumber=<% $ivrnumber %>&actmode=<% $actmode %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >从字符变量中读</td>
			<td height="30"><input type="text" id="iptext1" name="dialvarname" size="20" value="" class="tipmsg" title="数字和英文字母组合."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >或是</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >拨打以下号码</td>
			<td height="30"><input type="text" id="iptext1" name="dialdigits" size="16" value="" class="tipmsg" title="数字."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >号码处理方式</td>
			<td height="30"><input type="radio" value="" name="typeof" checked>本地号码&nbsp;&nbsp;<input type="radio" value="extension" name="typeof">分机&nbsp;&nbsp;<input type="radio" value="queue" name="typeof">呼叫队列&nbsp;&nbsp;<input type="radio" value="conference" name="typeof">电话会议&nbsp;&nbsp;<input type="radio" value="extenrouter" name="typeof">拨出规则</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'>如果拨打的号码不存在</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" ></td>
			<td height="30"><input type="checkbox" name="playbackinvalid" value="true">播放提示号码不存在</td>
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
