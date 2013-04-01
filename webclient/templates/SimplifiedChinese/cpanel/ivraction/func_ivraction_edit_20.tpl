<% include file="cpanel/func_header.inc.tpl" %>
	<h4>编辑动作 录制0-9字符</h4>
<form name="do_ivraction_edit" method="POST" action="?action=do_ivraction_edit&ivrnumber=<% $ivrnumber %>&id=<% $id %>&actmode=<% $actmode %>&return=<% $return %>" target='takefire'>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >是否在录制前播放 "BEEP" 声</td>
			<td height="30"><input type="radio" value="true" name="beepbeforereceive" <%if $args_array.beepbeforereceive eq 'true'%>checked<%/if%>>好的&nbsp;&nbsp;<input type="radio" value="false" name="beepbeforereceive" <%if $args_array.beepbeforereceive eq 'false'%>checked<%/if%>>不嘛&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >是否包含前一个用户输入</td>
			<td height="30"><input type="radio" value="true" name="addbeforeuserinput" <%if $args_array.addbeforeuserinput eq 'true'%>checked<%/if%>>好的&nbsp;&nbsp;<input type="radio" value="false" name="addbeforeuserinput" <%if $args_array.addbeforeuserinput eq 'false'%>checked<%/if%>>不嘛&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >录制的字符长度</td>
			<td height="30"><input type="text" id="iptext1" name="maxdigits" size="4" value="<% $args_array.maxdigits %>" class="tipmsg" title="必填."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >录制文件变量名称</td>
			<td height="30"><input type="text" id="iptext1" name="receivevarname" size="20" value="<% $args_array.receivevarname %>" class="tipmsg" title="必填,数字和英文字母组合."></td>
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
