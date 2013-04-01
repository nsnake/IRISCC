<% include file="cpanel/func_header.inc.tpl" %>
	<h4>试听 / 编辑 <% $file.filename %>.<% $file.extname %></h4>
<form name="do_soundmanager_edit" method="POST" action="?action=do_soundmanager_recordoverphone&id=<% $file.id %>" target="takefire">
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" colspan="2"><a class='media {height:70,width:400}' href='pbx_soundmanager.php?action=do_soundmanager_download&id=<% $file.id %>#.<% $file.extname %>'></a></td>
		</tr>
		<tr>
			<td height="40" align="left" style="padding-left: 10px;padding-right: 40px" >&nbsp;<img src="../images/icon/pi246.png">&nbsp;<a href='pbx_soundmanager.php?action=do_soundmanager_download&id=<% $file.id %>'>下载</a>&nbsp;&nbsp;<img src="../images/icon/pi401.png">&nbsp;&nbsp;通过话机录音&nbsp;&nbsp;<input type="text" id="iptext1" name="extension" size="6" value="">&nbsp;<input type="submit" value="录音" name="submit" id='btn1'></td>
		</tr>
	</table>
</form>

<form name="do_soundmanager_edit" method="POST" action="?action=do_soundmanager_listenoverphone&id=<% $file.id %>" target="takefire">
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="40" align="left" style="padding-left: 10px;padding-right: 40px" >
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../images/icon/pi401.png">&nbsp;&nbsp;通过话机试听&nbsp;&nbsp;<input type="text" id="iptext1" name="extension" size="6" value="">&nbsp;<input type="submit" value="试听" name="submit" id='btn1'></td>
		</tr>
	</table>
</form>

<form name="do_soundmanager_edit" method="POST" action="?action=do_soundmanager_edit&id=<% $file.id %>" target="_self" ENCTYPE="multipart/form-data">
	<table border="0" cellspacing="0" style="margin: 20px;">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >文件名</td>
			<td height="30"><% $file.category %>/<% $file.folder %>/<% $file.filename %>.<% $file.extname %></td>
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
				<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">上载录音文件</td>
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
