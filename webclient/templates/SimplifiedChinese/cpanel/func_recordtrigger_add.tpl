<% include file="cpanel/func_header.inc.tpl" %>
<!-- <script language='javascript'>
$().ready(function() {

	//增加成员
	$("#add2members").click(function(){
		  $("#extensions option:selected").clone().appendTo("#members");
		  $("#extensions option:selected").remove();
	});
	$("#extensions").dblclick(function(){
		$("option:selected",this).clone().appendTo("#members");
		$("option:selected",this).remove();
	});
	//减少成员
	$("#remove2members").click(function(){
		  $("#members option:selected").clone().appendTo("#extensions");
		  $("#members option:selected").remove();
	});
	$("#members").dblclick(function(){
		$("option:selected",this).clone().appendTo("#extensions");
		$("option:selected",this).remove();
	});
	//提交时全选
	$("#do_recordtrigger_add").submit(function(){
		$("#members option").each(function(){
			$(this).attr('selected',true);
		});
		return true;
	});

});

</script> -->
	<h4>创建自动录音触发器</h4>

<form id='do_recordtrigger_add' name="do_recordtrigger_add" method="POST" action="?action=do_recordtrigger_add" target="takefire">
	<table border="0" cellspacing="0" style="margin-left: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">触发器名称</td>
			<td height="30"><input type="text" id="iptext1" name="triggername" size="16" value="" class="tipmsg" title="必填."></td>
		</tr>
		<tr>
			<td height="25" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'>哪种呼叫需要被录音?</td>
		</tr>
		<tr>
			<td height="25" align="left" style="padding-left: 10px;padding-right: 40px"></td>
			<td height="25"><input type="checkbox" name="recordout" value="true">拨打电话&nbsp;&nbsp;<input type="checkbox" name="recordin" checked value="true">接听电话&nbsp;&nbsp;<input type="checkbox" name="recordqueue" value="true">作为呼叫队列成员接听</td>
		</tr>
		<tr>
			<td height="25" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'>录音文件保存多长时间?</td>
		</tr>
		<tr>
			<td height="25" align="left" style="padding-left: 10px;padding-right: 40px"></td>
			<td height="25"><input type="text" id="iptext1" name="keepfortype0" size="4" value="100" class="tipmsg" title="必填.">条&nbsp;&nbsp;或&nbsp;&nbsp;<input type="text" id="iptext1" name="keepfortype1" size="4" value="" class="tipmsg" title="必填.">天&nbsp;&nbsp;或&nbsp;&nbsp;<input type="checkbox" name="keepfortype2" value="true">永久保存</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" valign='top'>录音对象</td>
			<td height="30" >
				<textarea rows="6" id="iptext1" name="members" cols="30" title='请输入录音的对象号码,每行输入一个.' class="tipmsg"></textarea>
<!-- 			<table border="0" width="100%" id="table1">
				<tr>
					<td>备选分机</td>
					<td></td>
					<td>已选择成员</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<select size='10' name="extensions[]" multiple id="extensions" style='width: 185px'>
		<%foreach from=$extensions item=eachone key=keyname %>
							<option value="<% $eachone.accountcode %>"><% $eachone.accountcode %> "<% $eachone.info_name %>" </option>
		<%/foreach%>
						</select>
					</td>
					<td><a href="###" id='add2members'><img src="../images/icon/pi305.png" border='0'></a><br><br><a href="###" id='remove2members'><img src="../images/icon/pi306.png" border='0'></a></td>
					<td>
						<select size='10' name="members[]" multiple id="members" style='width: 185px'>
						</select>
					</td>
				</tr>
			</table> -->
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin-left: 20px">
		<tr>
			<td>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<% include file="cpanel/func_footer.inc.tpl" %>
