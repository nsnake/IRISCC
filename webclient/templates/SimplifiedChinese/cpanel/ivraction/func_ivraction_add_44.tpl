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
		$("#done_select_gotoivr").change(function(){
			add_filename('done_select_gotoivr','done_select_actpoint');
		});
		$("#failed_select_gotoivr").change(function(){
			add_filename('failed_select_gotoivr','failed_select_actpoint');
		});
		$("#other_select_gotoivr").change(function(){
			add_filename('other_select_gotoivr','other_select_actpoint');
		});
		//启动完成后
		add_filename('done_select_gotoivr','done_select_actpoint');
		add_filename('failed_select_gotoivr','failed_select_actpoint');
		add_filename('other_select_gotoivr','other_select_actpoint');
	});
	function remove_filename(curact) {
		$("#"+curact+" option").each(function(){
			$(this).remove();
		});
	}
	function add_filename(curivr,curact) {
		remove_filename(curact);
		selectedId = $("#"+curivr)[0].selectedIndex;
		$("<option value=''>IVR菜单起点</option>").appendTo($("#"+curact));
		for (i=0;i<=(action[selectedId].length-1) ;i++ )
		{
			$("<option value='"+action[selectedId][i][1]+"'>"+action[selectedId][i][0]+"."+action[selectedId][i][5]+"</option>").appendTo($("#"+curact));
		}
	}

</script>
	<h4>新动作 WEB交互接口</h4>
<form name="do_ivraction_add" method="POST" action="?action=do_ivraction_add&ivrnumber=<% $ivrnumber %>&actmode=<% $actmode %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >HTTP地址</td>
			<td height="30"><input type="text" id="iptext1" name="urlvar" size="30" value="http://">&nbsp;&nbsp;超时<input type="text" id="iptext1" name="urltimeout" size="4" value="10">秒</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><font color="#ACA8A1">以HTTP GET形式访问,例如: http://www.baidu.com/s</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >访问参数</td>
			<td height="30"><input type="text" id="iptext1" name="urlargs" size="40" value=""></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">
			<span class="tipmsg" title="$network $network_script $request $channel $language $type $uniqueid $callerid $calleridname $callingpres $callingani2 $callington $callingtns $dnid $rdnis $context $extension $priority $enhanced $accountcode $callsessionid $[IVR自定义变量]" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png">支持参数列表,附加到地址上的参数,例如: wd=$callerid+$extension</span>			
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><font color="#ACA8A1">返回数据结构: status=[failed|done]&传入变量=传入值&.....</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >返回成功时</td>
			<td height="30">
			<select size="1" name="done_gotoivr" id='done_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;位置&nbsp;<select size="1" name="done_actpoint" id='done_select_actpoint'>			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >返回失败时</td>
			<td height="30">
			<select size="1" name="failed_gotoivr" id='failed_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;位置&nbsp;<select size="1" name="failed_actpoint" id='failed_select_actpoint'>			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >超时或其他返回</td>
			<td height="30">
			<select size="1" name="other_gotoivr" id='other_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;位置&nbsp;<select size="1" name="other_actpoint" id='other_select_actpoint'>			</select>
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
