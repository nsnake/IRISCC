<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">

	//-------------------------------------------------------------------动作选择器
	action = new Array();
	var invalid_selectedindex_actpoint;
	var invalid_selectedindex_gotoivr;
	var timeout_selectedindex_actpoint;
	var timeout_selectedindex_gotoivr;
	var retry_selectedindex_actpoint;
	var retry_selectedindex_gotoivr;

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
<% if $eachfile.ivrnumber eq $invalid_optmenu.gotoivrnumber %>
invalid_selectedindex_gotoivr = <% $array_number %>;
<%/if%>
<% if $eachfile.id eq $invalid_optmenu.gotoivractid %>
invalid_selectedindex_actpoint = <% $keyname %>;
<%/if%>
<% if $eachfile.ivrnumber eq $timeout_optmenu.gotoivrnumber %>
timeout_selectedindex_gotoivr = <% $array_number %>;
<%/if%>
<% if $eachfile.id eq $invalid_optmenu.gotoivractid %>
timeout_selectedindex_actpoint = <% $keyname %>;
<%/if%>
<% if $eachfile.ivrnumber eq $retry_optmenu.gotoivrnumber %>
retry_selectedindex_gotoivr = <% $array_number %>;
<%/if%>
<% if $eachfile.id eq $retry_optmenu.gotoivractid %>
retry_selectedindex_actpoint = <% $keyname %>;
<%/if%>
<%/foreach%>

<%/foreach%>

	//-------------------------------------------------------------------动作选择器
	function remove_actionpoint(selector_name) {
		$("#"+selector_name+" option").each(function(){
			$(this).remove();
		});
	}
	function add_actionpoint(gotoivr_selector_name,action_selector_name) {
		remove_filename(action_selector_name);
		selectedId = $("#"+gotoivr_selector_name)[0].selectedIndex;
		$("<option value=''>该菜单起点</option>").appendTo($("#"+action_selector_name));
		for (i=0;i<=(action[selectedId].length-1) ;i++ )
		{
			$("<option value='"+action[selectedId][i][1]+"'>"+action[selectedId][i][0]+"."+action[selectedId][i][5]+"</option>").appendTo($("#"+action_selector_name));
		}
	}

	//-------------------------------------------------------------------声音文件选择和列表
	folder = new Array();
	folder_desc = new Array();
	var invalid_selectedindex_fileid;
	var invalid_selectedindex_folderid;
	var timeout_selectedindex_fileid;
	var timeout_selectedindex_folderid;
<%foreach from=$folder_file_array item=eachone key=folder_array_number %>

	folder[<% $folder_array_number %>] = new Array();
	folder_desc[<% $folder_array_number %>] = new Array();
<%foreach from=$eachone item=eachfile key=keyname %>
	folder[<% $folder_array_number %>][<% $keyname %>] = '<% $eachfile.filename %>';
	folder_desc[<% $folder_array_number %>][<% $keyname %>] = '<% $eachfile.description %>';
<% if $eachfile.filename eq $invalid_optmenu.args_as_ref.filename %>
	invalid_selectedindex_fileid = <% $keyname %>;
	invalid_selectedindex_folderid = <% $folder_array_number %>;
<%/if%>
<% if $eachfile.filename eq $timeout_optmenu.args_as_ref.filename %>
	timeout_selectedindex_fileid = <% $keyname %>;
	timeout_selectedindex_folderid = <% $folder_array_number %>;
<%/if%>
<%/foreach%>
<%/foreach%>
	//-------------------------------------------------------------------声音文件选择和列表
	function remove_filename(selector_name) {
		$("#"+selector_name+" option").each(function(){
			$(this).remove();
		});
	}
	function selected_filename_option(folder_selector_name,filename_selector_name) {
		folderid = $("#"+folder_selector_name)[0].selectedIndex;
		fileid = $("#"+filename_selector_name)[0].selectedIndex;
		$("#info_"+filename_selector_name).attr('innerHTML',folder_desc[folderid][fileid]);
	}
	function add_filename(folder_selector_name,filename_selector_name) {
		remove_filename(filename_selector_name);
		folderid = $("#"+folder_selector_name)[0].selectedIndex;
		for (i=0;i<=(folder[folderid].length-1) ;i++ )
		{
			$("<option value='"+folder[folderid][i]+"'>"+folder[folderid][i]+"</option>").appendTo($("#"+filename_selector_name));
		}
		selected_filename_option(folder_selector_name,filename_selector_name);
	}


$(document).ready(function() {
	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});
	$(".deletebtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确认要删除这项动作吗?' ,'提示' , function(r) {
			if (r == true)
			{
				$('#takefire').attr('src',gotourl);
			}
		});
	});

	//--------------INVALID声音文件管理
	//按键的时候
	$("#invalid_select_folder").change(function(){	add_filename('invalid_select_folder','invalid_select_filename');});	//选择目录时
	$("#invalid_select_filename").change(function(){selected_filename_option('invalid_select_folder','invalid_select_filename');});	//选择文件时
	//启动后
	$("#invalid_select_folder")[0].selectedIndex = invalid_selectedindex_folderid;//选择folderid
	add_filename('invalid_select_folder','invalid_select_filename');
	$("#invalid_select_filename")[0].selectedIndex = invalid_selectedindex_fileid;//选择文件id
	selected_filename_option('invalid_select_folder','invalid_select_filename');
	//--------------TIMEOUT声音文件管理
	//按键的时候
	$("#timeout_select_folder").change(function(){	add_filename('timeout_select_folder','timeout_select_filename');});	//选择目录时
	$("#timeout_select_filename").change(function(){selected_filename_option('timeout_select_folder','timeout_select_filename');});	//选择文件时
	$("#timeout_select_folder")[0].selectedIndex = timeout_selectedindex_folderid;//选择folderid
	add_filename('timeout_select_folder','timeout_select_filename');
	$("#timeout_select_filename")[0].selectedIndex = timeout_selectedindex_fileid;//选择文件id
	selected_filename_option('timeout_select_folder','timeout_select_filename');

	//--------------INVALID IVR菜单管理
	//按键的时候
	$("#invalid_select_gotoivr").change(function(){	add_actionpoint('invalid_select_gotoivr','invalid_select_actpoint');});
	//启动完成后
	$("#invalid_select_gotoivr")[0].selectedIndex = invalid_selectedindex_gotoivr;//选择folderid
	add_actionpoint('invalid_select_gotoivr','invalid_select_actpoint');
	$("#invalid_select_actpoint")[0].selectedIndex = invalid_selectedindex_actpoint+1;//选择folderid

	//按键的时候
	$("#timeout_select_gotoivr").change(function(){	add_actionpoint('timeout_select_gotoivr','timeout_select_actpoint');});
	//启动完成后
	$("#timeout_select_gotoivr")[0].selectedIndex = timeout_selectedindex_gotoivr;//选择folderid
	add_actionpoint('timeout_select_gotoivr','timeout_select_actpoint');
	$("#timeout_select_actpoint")[0].selectedIndex = timeout_selectedindex_actpoint+1;//选择folderid

	//按键的时候
	$("#retry_select_gotoivr").change(function(){	add_actionpoint('retry_select_gotoivr','retry_select_actpoint');});
	//启动完成后
	$("#retry_select_gotoivr")[0].selectedIndex = retry_selectedindex_gotoivr;//选择folderid
	add_actionpoint('retry_select_gotoivr','retry_select_actpoint');
	$("#retry_select_actpoint")[0].selectedIndex = retry_selectedindex_actpoint+1;//选择folderid

});


</script>


<!-- body -->
<div id="body-body">
	<h3>IVR菜单(<% $ivrnumber %>)用户输入编辑器</h3>
	<div align="left">
	<table border="0" width="90%" style="margin: 15px">
		<tr>
			<td height="30" align="left"><img src="../images/icon/32.png" border='0'>&nbsp;<a href='?action=page_ivr_main&ivrnumber=<% $ivrnumber %>'>返回 IVR菜单页</a></td>
		</tr>
	</table>
	<table border="0" width="90%" style="margin: 15px">
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="增加新的用户输入选择" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivruserinput_add&ivrnumber=<% $ivrnumber %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前输入选择&nbsp;共&nbsp;<% $maxcount %>&nbsp;条</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">用户按键</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">菜单名称</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">动作位置</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">其它选项</td>
				</tr>
		<%foreach from=$table_array item=eachone key=keyname %>
 				<tr  id='<% $eachone.id %>'>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.input %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $eachone.gotoivrnumber_ref.ivrname %></td>
<% if $eachone.gotoivractid_ref.actmode eq '10' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xi66.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.播放语音)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '11' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/microphone.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.发起录音)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '12' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xi66.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.播放录音)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '20' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi401.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.录制0-9字符)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '21' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/19.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.读出0-9字符)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '22' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/19-2.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.数字方式读出)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '30' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi126.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.读出日期时间)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '31' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi125.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.检测日期)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '40' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/xa38.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.主叫变换)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '41' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/11.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.拨打号码)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '42' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi447.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.跳转信箱或传真)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '43' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.跳转到IVR菜单)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '44' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.WEB交互接口)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '45' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/43.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.AGI扩展接口)</td>


<% elseif $eachone.gotoivractid_ref.actmode eq '80' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src='../images/icon/pi237.png'>执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.等待几秒)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '81' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/pi144.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.播放音调)</td>

<% elseif $eachone.gotoivractid_ref.actmode eq '99' %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic"><img src="../images/icon/01.png">执行菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)动作(<% $eachone.gotoivractid_level %>.挂机)</td>
<% else %><td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;font-style: italic">跳转到IVR菜单(<% $eachone.gotoivrnumber_ref.ivrnumber %>)</td><%/if%>

					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<input type="button" value="修改" name="button" id='btn1' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivruserinput_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>">&nbsp;<input type="button" value="删除" name="button" class="deletebtn" id='btn2' gotourl="acd_ivrmenu.php?action=do_ivruserinput_delete&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>">&nbsp;
					</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
<form name="invalid_form" method="POST" action="?action=do_ivruserinput_generalset&general_type=invalid&ivrnumber=<% $ivrnumber %>&id=<% $invalid_optmenu.id %>" target="takefire">
	<table border="0" width="90%" cellspacing="0" id='recordoverphone' style='margin:15px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2'><b>无效选择设置</td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2' style="padding-left: 10px;padding-right: 40px" >&nbsp;&nbsp;当用户输入了一个没有被设置的选择时应该如何处理?</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >文件目录&nbsp;&nbsp;
			<select size="1" name="folder" id='invalid_select_folder'>
		<%foreach from=$folder_array item=eachone key=keyname %>
			<option value="<% $eachone.folder %>"><% $eachone.folder %>/</option>
		<%/foreach%>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;播放声音&nbsp;&nbsp;
			<select size="1" name="filename" id='invalid_select_filename'>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2' style="padding-left: 10px;padding-right: 40px" >文件说明 : <b><span id='info_invalid_select_filename'></span>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >跳转到IVR菜单&nbsp;&nbsp;
			<select size="1" name="gotoivrnumber" id='invalid_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;并且位置在&nbsp;&nbsp;<select size="1" name="gotoivractid" id='invalid_select_actpoint'>
			</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="保存无效选择设置" name="submit" id='btn1'>
			</td>
		</tr>
		</table>
</form>
<form name="invalid_form" method="POST" action="?action=do_ivruserinput_generalset&general_type=timeout&ivrnumber=<% $ivrnumber %>&id=<% $timeout_optmenu.id %>" target="takefire">
	<table border="0" width="90%" cellspacing="0" id='recordoverphone' style='margin:15px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2'><b>选择输入超时设置</td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2' style="padding-left: 10px;padding-right: 40px" >&nbsp;&nbsp;如果用户在超过<input type="text" id="iptext1" name="timeout" size="2" value="<% $timeout_optmenu.args_as_ref.timeout %>" class="tipmsg" title="必填,纯数字填写">秒之内还没有选择如何处理?</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >文件目录&nbsp;&nbsp;
			<select size="1" name="folder" id='timeout_select_folder'>
		<%foreach from=$folder_array item=eachone key=keyname %>
			<option value="<% $eachone.folder %>"><% $eachone.folder %>/</option>
		<%/foreach%>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;播放声音&nbsp;&nbsp;
			<select size="1" name="filename" id='timeout_select_filename'>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2' style="padding-left: 10px;padding-right: 40px" >文件说明 : <b><span id='info_timeout_select_filename'></span>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >跳转到IVR菜单&nbsp;&nbsp;
			<select size="1" name="gotoivrnumber" id='timeout_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;并且位置在&nbsp;&nbsp;<select size="1" name="gotoivractid" id='timeout_select_actpoint'>
			</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="保存输入超时设置" name="submit" id='btn1'>
			</td>
		</tr>
		</table>
</form>
<form name="invalid_form" method="POST" action="?action=do_ivruserinput_generalset&general_type=retry&ivrnumber=<% $ivrnumber %>&id=<% $retry_optmenu.id %>" target="takefire">
	<table border="0" width="90%" cellspacing="0" id='recordoverphone' style='margin:15px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2'><b>重试次数设置</td>
		</tr>
		<tr>
			<td height="30" align="left" colspan='2' style="padding-left: 10px;padding-right: 40px" >&nbsp;&nbsp;在用户重试多少次后让其进入无效或输入超时设置?</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >尝试次数&nbsp;&nbsp;
			<select size="1" name="numberofretry">
			<option value="100" <% if $retry_optmenu.args_as_ref.numberofretry >= 9 %>selected<%/if%>>无限制</option>
			<option value="1" <% if $retry_optmenu.args_as_ref.numberofretry eq '1' %>selected<%/if%>>1</option>
			<option value="2" <% if $retry_optmenu.args_as_ref.numberofretry eq '2' %>selected<%/if%>>2</option>
			<option value="3" <% if $retry_optmenu.args_as_ref.numberofretry eq '3' %>selected<%/if%>>3</option>
			<option value="4" <% if $retry_optmenu.args_as_ref.numberofretry eq '4' %>selected<%/if%>>4</option>
			<option value="5" <% if $retry_optmenu.args_as_ref.numberofretry eq '5' %>selected<%/if%>>5</option>
			<option value="6" <% if $retry_optmenu.args_as_ref.numberofretry eq '6' %>selected<%/if%>>6</option>
			<option value="7" <% if $retry_optmenu.args_as_ref.numberofretry eq '7' %>selected<%/if%>>7</option>
			<option value="8" <% if $retry_optmenu.args_as_ref.numberofretry eq '8' %>selected<%/if%>>8</option>
			<option value="9" <% if $retry_optmenu.args_as_ref.numberofretry eq '9' %>selected<%/if%>>9</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >跳转到IVR菜单&nbsp;&nbsp;
			<select size="1" name="gotoivrnumber" id='retry_select_gotoivr'>
		<%foreach from=$ivrmenu_array item=eachone key=keyname %>
			<option value="<% $eachone.ivrnumber %>"><% $eachone.ivrnumber %> - <% $eachone.ivrname %></option>
		<%/foreach%>
			</select>&nbsp;&nbsp;并且位置在&nbsp;&nbsp;<select size="1" name="gotoivractid" id='retry_select_actpoint'>
			</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="保存无效选择设置" name="submit" id='btn1'>
			</td>
		</tr>
		</table>
</form>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
