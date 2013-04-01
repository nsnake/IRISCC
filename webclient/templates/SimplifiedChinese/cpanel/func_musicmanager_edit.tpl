<% include file="cpanel/func_header.inc.tpl" %>
	<h4>试听 / 编辑 音乐 <% $file.filename %>.<% $file.extname %></h4>
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" colspan="2"><a class='media {height:70,width:400}' href='pbx_musicmanager.php?action=do_musicmanager_download&id=<% $file.id %>#.<% $file.extname %>'></a></td>
		</tr>
	</table>

<form name="do_musicmanager_edit" method="POST" action="?action=do_musicmanager_edit&id=<% $file.id %>" target="_self" ENCTYPE="multipart/form-data">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >文件名</td>
			<td height="30"><% $file.filename %>.<% $file.extname %></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >文件说明</td>
			<td height="30"><input type="text" id="iptext1" name="description" size="60" value="<% $file.description %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >创建时间</td>
			<td height="30"><% $file.cretime %></td>
		</tr>
		<tr>
			<td colspan="3" bgcolor="#F3F3F3">
		<table border="0" cellspacing="0">
			<tr>
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">上载音乐文件</td>
				<td height="30">&nbsp;<INPUT NAME="soundfile" TYPE="File"></td>
			</tr>
		</table>
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
