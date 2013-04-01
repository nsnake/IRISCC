<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">

	//----------------声音文件选择和列表
	folder = new Array();
	folder_desc = new Array();
<%foreach from=$folder_file_array item=eachone key=folder_array_number %>

	folder[<% $folder_array_number %>] = new Array();
	folder_desc[<% $folder_array_number %>] = new Array();
<%foreach from=$eachone item=eachfile key=keyname %>
	folder[<% $folder_array_number %>][<% $keyname %>] = '<% $eachfile.filename %>';
	folder_desc[<% $folder_array_number %>][<% $keyname %>] = '<% $eachfile.description %>';
<% if $eachfile.filename eq $args_array.filename %>
var edit_selected_fileid = <% $keyname %>;
var edit_selected_folderid = <% $folder_array_number %>;
<%/if%>
<%/foreach%>
<%/foreach%>

	$().ready(function() {
		//选择目录时
		$("#select_folder").change(function(){
			add_filename();
		});
		//选择文件时
		$("#select_filename").change(function(){
			selected_filename_option();
		});
		//启动完成后
		$("#select_folder")[0].selectedIndex = edit_selected_folderid;//选择folderid
		add_filename();
		$("#select_filename")[0].selectedIndex = edit_selected_fileid;//选择文件id
		selected_filename_option();
	});

	function remove_filename() {
		$("#select_filename option").each(function(){
			$(this).remove();
		});
	}
	function selected_filename_option() {
		folderid = $("#select_folder")[0].selectedIndex;
		fileid = $("#select_filename")[0].selectedIndex;
		$("#info").attr('innerHTML',folder_desc[folderid][fileid]);
	}
	function add_filename() {
		remove_filename();
		folderid = $("#select_folder")[0].selectedIndex;
		for (i=0;i<=(folder[folderid].length-1) ;i++ )
		{
			$("<option value='"+folder[folderid][i]+"'>"+folder[folderid][i]+"</option>").appendTo($("#select_filename"));
		}
		selected_filename_option();
	}

</script>
	<h4>编辑动作 播放语音</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" width='550' style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left">文件目录&nbsp;&nbsp;
			<select size="1" name="folder" id='select_folder'>
		<%foreach from=$folder_array item=eachone key=keyname %>
			<option value="<% $eachone.folder %>"><% $eachone.folder %>/</option>
		<%/foreach%>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left">播放声音&nbsp;&nbsp;
			<select size="1" name="filename" id='select_filename'>
			</select>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" width='550' style="margin: 20px">
		<tr>
			<td height="30" align="left" colspan='2'>&nbsp;声音文件说明 : <b><span id='info'></span>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" width='550' style="margin: 20px">
		<tr>
			<td height="30"><input type="checkbox" name="interruptible" value="true" <%if $args_array.interruptible eq 'true'%>checked<%/if%>>播放中, 接受用户输入选择.</td>
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
