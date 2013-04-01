<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	//增加成员
	$("#add2members").click(function(){
		  $("#freechans option:selected").clone().appendTo("#channel");
		  $("#freechans option:selected").remove();
	});
	$("#freechans").dblclick(function(){
		$("option:selected",this).clone().appendTo("#channel");
		$("option:selected",this).remove();
	});
	//减少成员
	$("#remove2members").click(function(){
		  $("#channel option:selected").clone().appendTo("#freechans");
		  $("#channel option:selected").remove();
	});
	$("#channel").dblclick(function(){
		$("option:selected",this).clone().appendTo("#freechans");
		$("option:selected",this).remove();
	});
	//提交时全选
	$("#do_trunk_add_isdnpri").submit(function(){
		$("#channel option").each(function(){
			$(this).attr('selected',true);
		});
		return true;
	});

});

</script>
	<h4>增加 E1 ISDNPRI 30B+D 数字中继</h4>

<form id="do_trunk_add_isdnpri" name="do_trunk_add_isdnpri" method="POST" action="?action=do_trunk_add_isdnpri" target="takefire">

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;中继信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >中继名称</td>
			<td height="30"><input type="text" id="iptext1" name="trunkname" size="8" value="" class="tipmsg" title="必填,数字字母组合"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><font color="#ACA8A1">备注</font></td>
			<td height="30"><input type="text" id="iptext1" name="trunkremark" size="30" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">选择E1信道组</font></td>
			<td height="30">
			<table border="0" width="100%" id="table1">
				<tr>
					<td>备选B信道</td>
					<td></td>
					<td>已选B信道</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<select size='10' name="freechans[]" multiple id="freechans" style='width: 120px'>
		<%foreach from=$freechan item=eachone key=keyname %>
		<%foreach from=$eachone item=chan %><option value="<% $chan %>"> PRI <% $keyname %> / <% $chan %> </option><%/foreach%>
		<%/foreach%>
						</select>
					</td>
					<td><a href="###" id='add2members'><img src="../images/icon/pi305.png" border='0'></a><br><br><a href="###" id='remove2members'><img src="../images/icon/pi306.png" border='0'></a></td>
					<td>
						<select size='10' name="channel[]" multiple id="channel" style='width: 120px'>
						</select>
					</td>
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
