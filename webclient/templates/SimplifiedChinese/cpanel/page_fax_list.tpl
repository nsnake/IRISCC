<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".download").click(function(){
		window.location.href=$(this).attr("gotourl");
	});


});
</script>


<!-- body -->
<div id="body-body">
	<h3>数字传真</h3>

	<div align="left">
	<table border="0" width="90%" align="left" style="margin: 0px">
		<tr>
			<td height="30" align="left" width="100%">&nbsp;传真算法符合ITU-TSS标准传真机(G3 Group), 可工作于模拟语音卡及数字语音卡之上.</td>
		</tr>
<% if $release eq 'freeiris' %>
		<tr>
			<td align="left">
			<a href="http://cn.freeiris.org/store.php?action=list&category=other" target="_blank">商用传真算法购买请访问</a>
			<td>
		</tr>
<%/if%>
		<tr>
			<td align="left">
			<table border="0" align="left" style="margin-top: 20px">
				<tr>
					<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><input type="button" value="备份授权协议" name="button" id='btn1'  onclick="javascript:window.location.href('pbx_fax.php?action=do_bklicense_download');">&nbsp;&nbsp;<input type="button" value="下载传真打印驱动程序" name="button" id='btn1' class='download' gotourl="javascript:window.location.href('../download/faxdriver_setup.exe');">&nbsp;&nbsp;<input type="button" value="查看收到传真记录" name="button" id='btn1' class='showpopDialog' func="pbx_fax.php?action=func_faxstats_list"></td>
				</tr>
			</table>
			<td>
		</tr>
		<tr>
			<td align="left">
<form name="do_faxoption_set" method="POST" action="?action=do_faxoption_set" target="takefire">
	<table border="0" style="margin-top: 15px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">传真速率</td>
			<td height="30">最大&nbsp;<SELECT NAME="maxrate">
				<OPTION VALUE="14400" <% if $option.maxrate eq '14400' %>selected<%/if%>>14400</OPTION>
				<OPTION VALUE="12200" <% if $option.maxrate eq '12200' %>selected<%/if%>>12200</OPTION>
				<OPTION VALUE="9600" <% if $option.maxrate eq '9600' %>selected<%/if%>>9600</OPTION>
				<OPTION VALUE="7200" <% if $option.maxrate eq '7200' %>selected<%/if%>>7200</OPTION>
				<OPTION VALUE="4800" <% if $option.maxrate eq '4800' %>selected<%/if%>>4800</OPTION>
				<OPTION VALUE="2400" <% if $option.maxrate eq '2400' %>selected<%/if%>>2400</OPTION>
			</SELECT>&nbsp;bps<BR>最小&nbsp;<SELECT NAME="minrate">
				<OPTION VALUE="14400" <% if $option.minrate eq '14400' %>selected<%/if%>>14400</OPTION>
				<OPTION VALUE="12200" <% if $option.minrate eq '12200' %>selected<%/if%>>12200</OPTION>
				<OPTION VALUE="9600" <% if $option.minrate eq '9600' %>selected<%/if%>>9600</OPTION>
				<OPTION VALUE="7200" <% if $option.minrate eq '7200' %>selected<%/if%>>7200</OPTION>
				<OPTION VALUE="4800" <% if $option.minrate eq '4800' %>selected<%/if%>>4800</OPTION>
				<OPTION VALUE="2400" <% if $option.minrate eq '2400' %>selected<%/if%>>2400</OPTION>
			</SELECT>&nbsp;bps</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">传真协议支持</td>
			<td height="30"><INPUT TYPE="checkbox" NAME="modem[]" value='v17' <% if $modem.v17 eq '1' %>checked<%/if%>>V.17(14400bps)&nbsp;<INPUT TYPE="checkbox" NAME="modem[]" value='v27' <% if $modem.v27 eq '1' %>checked<%/if%>>V.27(4800bps)&nbsp;<INPUT TYPE="checkbox" NAME="modem[]" value='v29' <% if $modem.v29 eq '1' %>checked<%/if%>>V.29(9600bps)</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">错误校正(ECM)</td>
			<td height="30"><input type="radio" value="yes" name="ecm" <% if $option.ecm eq 'yes' %>checked<%/if%>>开启 <input type="radio" value="no" name="ecm" <% if $option.ecm eq 'no' %>checked<%/if%>>关闭</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">传真机抬头</td>
			<td height="30"><input type="text" id="iptext1" name="faxtitle" size="16" value="<% $option.faxtitle %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">传真显示号码</td>
			<td height="30"><input type="text" id="iptext1" name="localstationid" size="32" value="<% $option.localstationid %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">IVR提醒菜单 传真</td>
			<td height="30"><input type="radio" value="yes" name="enablefaxivr" <% if $option.enablefaxivr eq 'yes' %>checked<%/if%>>开启 <input type="radio" value="no" name="enablefaxivr" <% if $option.enablefaxivr eq 'no' %>checked<%/if%>>关闭</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">传真保存方式</td>
			<td height="30"><input type="radio" value="hold" name="deliver" <% if $option.deliver eq 'hold' %>checked<%/if%>>本地存储 <input type="radio" value="mail" name="deliver" <% if $option.deliver eq 'mail' %>checked<%/if%>>电子邮件</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
			</td>
		</tr>
		<tr>
			<td align="left">
<form name="do_fax_send" method="POST" action="?action=do_fax_send" target="takefire" ENCTYPE="multipart/form-data">
	<table border="0" cellspacing="0" style="margin-top: 15px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2">测试传真支持TIFF(G3传真格式)格式的传真文件</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >传真号码</td>
			<td height="30"><input type="text" id="iptext1" name="number" size="16" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" >上载文件</td>
			<td height="30"><INPUT NAME="faxfile" TYPE="File"></td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="发送测试传真" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"></td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
