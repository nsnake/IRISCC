<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 播放音调</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >选择音调类型</td>
			<td height="30">
			<select size="1" name="playtone">
			<option value="busy" <%if $args_array.playtone eq 'busy'%>selected<%/if%>>busy</option>
			<option value="congestion" <%if $args_array.playtone eq 'congestion'%>selected<%/if%>>congestion</option>
			<option value="info" <%if $args_array.playtone eq 'info'%>selected<%/if%>>info</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >播放时长(秒)</td>
			<td height="30"><input type="text" id="iptext1" name="sec" size="4" value="<% $args_array.sec %>"></td>
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
