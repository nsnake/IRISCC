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

		$("select[@name='process_mode']").change(function(){
			selectmode($(this).val());
		});

		selectmode($("select[@name='process_mode']").val());
	});

	function selectmode(mode) {
		if (mode == 0)
		{
			$('#process_defined_trunk_table').hide();
			$('#process_defined_localnumber_table').hide();
		} else if (mode == 1)
		{
			$('#process_defined_trunk_table').hide();
			$('#process_defined_localnumber_table').show();
		} else if (mode == 2)
		{
			$('#process_defined_trunk_table').show();
			$('#process_defined_localnumber_table').hide();
		}
	}
</script>
	<h4>增加拨入规则</h4>

<form name="do_router_add" method="POST" action="?action=do_router_add" target="takefire">

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;规则信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >规则名称</td>
			<td height="30"><input type="text" id="iptext1" name="routername" size="20" value="" class="tipmsg" title="必填,数字字母组合"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >呼叫来自</td>
			<td height="30">
			<select size="1" name="match_callergroup">
				<option value="">任意</option>
		<%foreach from=$provider_array item=eachone key=keyname %>
				<option value="<% $eachone.id %>">[<% $eachone.trunkproto %>] <% $eachone.trunkname %></option>
		<%/foreach%>
			</select>
			</td>
		</tr>
		<tr>
			<td align="left" style="padding-left: 10px;padding-right: 40px">匹配规则<br>&nbsp;<span class="tipmsg" title="最终规则表示一旦这条规则被匹配,那么就不再请求其他规则." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>说明</b></span></td>
			<td height="80">当外线送入的被叫号码以<input type="text" id="iptext1" name="match_callednum" size="8" value="">开头且长度为<input type="text" id="iptext1" name="match_calledlen" size="2" value="">位时.<br>从头开始删掉<input type="text" id="iptext1" name="replace_calledtrim" size="2" value="">位且补充信息<input type="text" id="iptext1" name="replace_calledappend" size="8" value="">到该号码上.</td>
		</tr>
		<tr>
			<td align="left" style="padding-left: 10px;padding-right: 40px">处理方式</td>
			<td height="30">
			<select size="1" name="process_mode">
			<option value="0">黑名单</option>
			<option value="1" selected>本地处理</option>
			<option value="2">拨打外线</option>
			</select>
			</td>
		</tr>
		<tr style="display: none" id="process_defined_trunk_table">
			<td align="left" style="padding-left: 10px;padding-right: 40px">选择外线</td>
			<td height="30">
			<select size="1" name="process_defined_trunk">
				<option value="">&nbsp;</option>
		<%foreach from=$provider_array item=eachone key=keyname %>
				<option value="<% $eachone.id %>">[<% $eachone.trunkproto %>] <% $eachone.trunkname %></option>
		<%/foreach%>
			</select>
			</td>
		</tr>
 		<tr style="display: none" id="process_defined_localnumber_table">
			<td align="left" style="padding-left: 10px;padding-right: 40px">本地类型</td>
			<td height="30">
			<select size="1" name="process_defined_localnumber">
				<option value="">全部</option>
				<option value="">————</option>
				<option value="extension">分机</option>
				<option value="ivr">IVR菜单</option>
				<option value="queue">呼叫队列</option>
				<option value="conference">电话会议</option>
				<option value="agi">其他</option>
			</select>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/83.png" border="0"><a href="###" id='display_advance'>&nbsp;更多可以设置的参数&nbsp;</span></a></td>
		</tr>
		<tr>
			<td colspan="3">
				<table border="0" cellspacing="0" id='advance' style='display:none'>
					<tr>
						<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">最终规则<br>&nbsp;<span class="tipmsg" title="最终规则表示一旦这条规则被匹配,那么就不再请求其他规则." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>什么是最终规则?</b></span></td>
						<td height="60"><input type="radio" value="0" checked name="lastwhendone">Yes <input type="radio" value="1" name="lastwhendone">No</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">匹配主叫号码<br>
						&nbsp;<span class="tipmsg" title="如果有多个主叫号码则以逗号隔开" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="match_callerid" size="16" value=""></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">匹配主叫长度<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="match_callerlen" size="2" value=""></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">主叫替换<br>
						&nbsp;<span class="tipmsg" title="如果这个号码是送到外线的,系统会将内线的分机号码直接送出去,可能会被拒绝掉.这个功能可以用来做透传设置" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这条的作用</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="replace_callerid" size="16" value=""></td>
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
