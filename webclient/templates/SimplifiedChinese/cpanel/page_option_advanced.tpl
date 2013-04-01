<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

});
</script>
<div id="body-body">
	<h3>高级设置</h3>
	<p>&nbsp;</p>

	<div align="left">
	<table border="0" width="500" style="margin: 15px">
		<tr>
			<td height="30" width="100%" colspan='2'>&nbsp;高级设置将配置修改系统核心部分,请不要在不了解的情况下对其做修改,将可能引起系统严重错误.</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'><input type="button" value="检索主配置文件" name="button" id='btn1' class='showpopDialog' func="option_advanced.php?action=func_confile_list&folder=freeiris">&nbsp;&nbsp;&nbsp;<input type="button" value="检索主通信文件" name="button" id='btn1' class='showpopDialog' func="option_advanced.php?action=func_confile_list&folder=asterisk"></td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>&nbsp;</td>
		</tr>
	</table>
<!-- <form name="do_option_advanced_database_set" method="POST" action="?action=do_option_advanced_database_set" target="takefire">
	<table border="0" style="margin: 15px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>数据库配置参数</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">数据库地址</td>
			<td height="30"><input type="text" id="iptext1" name="dbhost" size="30" value="<% $database.dbhost %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">用户名</td>
			<td height="30"><input type="text" id="iptext1" name="dbuser" size="16" value="<% $database.dbuser %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">密码</td>
			<td height="30"><input type="text" id="iptext1" name="dbpasswd" size="16" value="<% $database.dbpasswd %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">数据库名</td>
			<td height="30"><input type="text" id="iptext1" name="dbname" size="16" value="<% $database.dbname %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">数据库端口</td>
			<td height="30"><input type="text" id="iptext1" name="dbport" size="8" value="<% $database.dbport %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">sock文件</td>
			<td height="30"><input type="text" id="iptext1" name="dbsock" size="30" value="<% $database.dbsock %>"></td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form> -->
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
