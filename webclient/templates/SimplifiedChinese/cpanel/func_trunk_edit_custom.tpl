<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">
	$().ready(function() {

		$("#display_advance").click(function(){
			if ($('#advance').is(':visible') == false)
			{
				$('#advance').show();
				$('#advance').focus();
			} else {
				$('#advance').hide();
			}
		});
	});
</script>
	<h4>增加自定义外线</h4>

<form name="do_trunk_edit_custom" method="POST" action="?action=do_trunk_edit_custom&id=<% $trunk.id %>" target="takefire">
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;中继信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" >&nbsp;中继名称&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td height="30"><input type="text" id="iptext1" name="trunkname" size="8" value="<% $trunk.trunkname %>" readonly class="tipmsg" title="必填,数字字母组合"></td>
		</tr>
		<tr>
			<td height="30" align="left">&nbsp;<font color="#ACA8A1">备注</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td height="30"><input type="text" id="iptext1" name="trunkremark" size="30" value="<% $trunk.trunkremark %>"></td>
		</tr>
		<tr>
			<td height="30" align="left">&nbsp;自定义设备</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td height="30"><input type="text" id="iptext1" name="trunkdevice" size="30" value="<% $trunk.trunkdevice %>" class="tipmsg" title="定义设备用于高级用户."></td>
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
