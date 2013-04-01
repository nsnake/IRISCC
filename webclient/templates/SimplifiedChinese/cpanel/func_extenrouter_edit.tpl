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
	<h4>编辑分机拨号规则</h4>

<form name="do_router_add" method="POST" action="?action=do_router_edit&id=<% $rule.id %>" target="takefire">

	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/24.png">&nbsp;规则信息</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >规则名称</td>
			<td height="30"><input type="text" id="iptext1" name="routername" size="20" value="<% $rule.routername %>" class="tipmsg" title="必填,数字字母组合"></td>
		</tr>
		<tr>
			<td align="left" style="padding-left: 10px;padding-right: 40px">匹配表达式<br>&nbsp;<span class="tipmsg" title="最终规则表示一旦这条规则被匹配,那么就不再请求其他规则." style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>说明</b></span></td>
			<td height="80">用户拨打的号码是以 <input type="text" id="iptext1" name="match_callednum" size="8" value="<% $rule.match_callednum %>"> 开头,长度为 <input type="text" id="iptext1" name="match_calledlen" size="2" value="<% $rule.match_calledlen %>"> 位.<br>从头删除掉 <input type="text" id="iptext1" name="replace_calledtrim" size="2" value="<% $rule.replace_calledtrim %>"> 位数字,同时补充 <input type="text" id="iptext1" name="replace_calledappend" size="8" value="<% $rule.replace_calledappend %>"> 到这个号码上.</td>
		</tr>
		<tr>
			<td align="left" style="padding-left: 10px;padding-right: 40px">处理方式</td>
			<td height="30">
			<select size="1" name="process_mode">
			<option value="0" <% if $rule.process_mode eq '0' %>selected<%/if%>>黑名单</option>
			<option value="1" <% if $rule.process_mode eq '1' %>selected<%/if%>>本地处理</option>
			<option value="2" <% if $rule.process_mode eq '2' %>selected<%/if%>>拨打外线</option>
			</select>
			</td>
		</tr>
		<tr style="display: none" id="process_defined_trunk_table">
			<td align="left" style="padding-left: 10px;padding-right: 40px">选择外线</td>
			<td height="30">
			<select size="1" name="process_defined_trunk">
				<option value="">&nbsp;</option>
		<%foreach from=$provider_array item=eachone key=keyname %>
				<option value="<% $eachone.id %>" <% if $eachone.id eq $rule.process_defined %>selected<%/if%>>[<% $eachone.trunkproto %>] <% $eachone.trunkname %></option>
		<%/foreach%>
			</select>
			</td>
		</tr>
 		<tr style="display: none" id="process_defined_localnumber_table">
			<td align="left" style="padding-left: 10px;padding-right: 40px">本地类型</td>
			<td height="30">
			<select size="1" name="process_defined_localnumber">
				<option value="" <% if $rule.process_defined eq '' %>selected<%/if%>>全部</option>
				<option value="">————</option>
				<option value="extension" <% if $rule.process_defined eq 'extension' %>selected<%/if%>>分机</option>
				<option value="ivr" <% if $rule.process_defined eq 'ivr' %>selected<%/if%>>IVR菜单</option>
				<option value="queue" <% if $rule.process_defined eq 'queue' %>selected<%/if%>>呼叫队列</option>
				<option value="conference" <% if $rule.process_defined eq 'conference' %>selected<%/if%>>电话会议</option>
				<option value="agi" <% if $rule.process_defined eq 'agi' %>selected<%/if%>>其他</option>
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
						<td height="60"><input type="radio" value="0" <% if $rule.lastwhendone eq '0' %>checked<%/if%> name="lastwhendone">Yes <input type="radio" value="1" <% if $rule.lastwhendone eq '1' %>checked<%/if%> name="lastwhendone">No</td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">匹配的用户组<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="match_callergroup_groupname" size="8" value="<% $rule.match_callergroup_extengroup_result.groupname %>"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">匹配主叫号码<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="match_callerid" size="16" value="<% $rule.match_callerid %>"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">匹配主叫长度<br>
						&nbsp;<span class="tipmsg" title="没有这个帮助主题" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这是什么?</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="match_callerlen" size="2" value="<% $rule.match_callerlen %>"></td>
					</tr>
					<tr>
						<td align="left" style="padding-left: 10px;padding-right: 40px">主叫替换<br>
						&nbsp;<span class="tipmsg" title="特殊变量{N}表示当前主叫号码,如果想实现格式添加就是: 数字{N}组合填写. 比如: 5165{N}" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>这条的作用</b></span></td>
						<td height="60"><input type="text" id="iptext1" name="replace_callerid" size="16" value="<% $rule.replace_callerid %>"></td>
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
