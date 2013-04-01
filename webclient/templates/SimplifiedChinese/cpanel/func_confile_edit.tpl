<% include file="cpanel/func_header.inc.tpl" %>

	<h4><% if $folder eq 'freeiris' %>主配置文件<%else%>通信配置文件<%/if%> <% $filename %></h4>

<form name="do_confile_edit" method="POST" action="?action=do_confile_edit&folder=<% $folder %>&filename=<% $filename %>">
	<table border="0" cellspacing="0" style='margin-left:20px;margin-top:5px'>
		<tr>
			<td align='center'>
				<textarea rows="22" name="filestream" cols="67" id='iptext1'><% $filestream|escape:'html' %></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
