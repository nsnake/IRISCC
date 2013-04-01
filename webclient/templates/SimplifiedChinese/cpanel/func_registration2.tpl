<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	$("#agree").click(function(){
		if ($("#agree").attr("checked")==true)
		{
			$(".submitbutton").attr("disabled",false);
		} else {
			$(".submitbutton").attr("disabled",true);
		}
	});

});
</script>
<form name="registration" method="POST" action="?action=func_registration3" target="_self">
<table border="0" width="100%" id="table1">
	<tr>
		<td align='center'><H5>版本更新说明</H5></td>
	</tr>
	<tr>
		<td><pre id ='code'><% $license.changes %></pre></td>
	</tr>
	<tr>
		<td align='center'><H5>用户授权协议</H5></td>
	</tr>
	<tr>
		<td><pre id ='code'><% $license.license %></pre></td>
	</tr>
	<tr>
		<td align='center'><H5>保护开源在中国的发展</H5></td>
	</tr>
	<tr>
		<td><pre id ='code'><% $license.protect %></pre></td>
	</tr>
	<tr>
		<td align='center'><H3><input type="checkbox" name="C1" value="ON" id ='agree'>我同意并且愿意遵守以上协议</H3></td>
	</tr>
	<tr>
		<td align='center'><input type="submit" value="继续" name="submit" id='btn1' class='submitbutton' disabled></td>
	</tr>
</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
