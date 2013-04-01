<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

});
</script>
<div id="body-body">
	<h3>语音板设置</h3>
	<p>&nbsp;</p>

	<div align="left">
<form name="do_hardware_set" method="POST" action="?action=do_hardware_set" target="takefire">
	<table border="0" style="margin: 15px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>通用参数</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">硬件音调区</td>
			<td height="30">
			<select size="1" name="loadzone">
				<option value="us" <% if $common.loadzone eq 'us' %>selected<%/if%>>United States / North America</option>
				<option value="au" <% if $common.loadzone eq 'au' %>selected<%/if%>>Australia</option>
				<option value="fr" <% if $common.loadzone eq 'fr' %>selected<%/if%>>France</option>
				<option value="nl" <% if $common.loadzone eq 'nl' %>selected<%/if%>>Netherlands</option>
				<option value="uk" <% if $common.loadzone eq 'uk' %>selected<%/if%>>United Kingdom</option>
				<option value="fi" <% if $common.loadzone eq 'fi' %>selected<%/if%>>Finland</option>
				<option value="es" <% if $common.loadzone eq 'es' %>selected<%/if%>>Spain</option>
				<option value="jp" <% if $common.loadzone eq 'jp' %>selected<%/if%>>Japan</option>
				<option value="no" <% if $common.loadzone eq 'no' %>selected<%/if%>>Norway</option>
				<option value="at" <% if $common.loadzone eq 'at' %>selected<%/if%>>Austria</option>
				<option value="nz" <% if $common.loadzone eq 'nz' %>selected<%/if%>>New Zealand</option>
				<option value="it" <% if $common.loadzone eq 'it' %>selected<%/if%>>Italy</option>
				<option value="gr" <% if $common.loadzone eq 'gr' %>selected<%/if%>>Greece</option>
				<option value="tw" <% if $common.loadzone eq 'tw' %>selected<%/if%>>Taiwan</option>
				<option value="cl" <% if $common.loadzone eq 'cl' %>selected<%/if%>>Chile</option>
				<option value="se" <% if $common.loadzone eq 'se' %>selected<%/if%>>Sweden</option>
				<option value="be" <% if $common.loadzone eq 'be' %>selected<%/if%>>Belgium</option>
				<option value="sg" <% if $common.loadzone eq 'sg' %>selected<%/if%>>Singapore</option>
				<option value="il" <% if $common.loadzone eq 'il' %>selected<%/if%>>Israel</option>
				<option value="br" <% if $common.loadzone eq 'br' %>selected<%/if%>>Brazil</option>
				<option value="hu" <% if $common.loadzone eq 'hu' %>selected<%/if%>>Hungary</option>
				<option value="lt" <% if $common.loadzone eq 'lt' %>selected<%/if%>>Lithuania</option>
				<option value="pl" <% if $common.loadzone eq 'pl' %>selected<%/if%>>Poland</option>
				<option value="za" <% if $common.loadzone eq 'za' %>selected<%/if%>>South Africa</option>
				<option value="pt" <% if $common.loadzone eq 'pt' %>selected<%/if%>>Portugal</option>
				<option value="ee" <% if $common.loadzone eq 'ee' %>selected<%/if%>>Estonia</option>
				<option value="mx" <% if $common.loadzone eq 'mx' %>selected<%/if%>>Mexico</option>
				<option value="in" <% if $common.loadzone eq 'in' %>selected<%/if%>>India</option>
				<option value="de" <% if $common.loadzone eq 'de' %>selected<%/if%>>Germany</option>
				<option value="ch" <% if $common.loadzone eq 'ch' %>selected<%/if%>>Switzerland</option>
				<option value="dk" <% if $common.loadzone eq 'dk' %>selected<%/if%>>Denmark</option>
				<option value="cz" <% if $common.loadzone eq 'cz' %>selected<%/if%>>Czech Republic</option>
 				<option value="cn" <% if $common.loadzone eq 'cn' %>selected<%/if%>>China</option>
				<option value="ar" <% if $common.loadzone eq 'ar' %>selected<%/if%>>Argentina</option>
				<option value="my" <% if $common.loadzone eq 'my' %>selected<%/if%>>Malaysia</option>
				<option value="th" <% if $common.loadzone eq 'th' %>selected<%/if%>>Thailand</option>
				<option value="bg" <% if $common.loadzone eq 'bg' %>selected<%/if%>>Bulgaria</option>
				<option value="ve" <% if $common.loadzone eq 've' %>selected<%/if%>>Venezuela</option>
				<option value="ph" <% if $common.loadzone eq 'ph' %>selected<%/if%>>Philippines</option>
				<option value="ru" <% if $common.loadzone eq 'ru' %>selected<%/if%>>Russian Federation</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">模拟口软回音算法</td>
			<td height="30">
			<select size="1" name="echocanceller">
				<option value="" <% if $common.echocanceller eq '' %>selected<%/if%>>无</option>
				<option value="mg2" <% if $common.echocanceller eq 'mg2' %>selected<%/if%>>mg2</option>
				<option value="kb1" <% if $common.echocanceller eq 'kb1' %>selected<%/if%>>kb1</option>
				<option value="sec2" <% if $common.echocanceller eq 'sec2' %>selected<%/if%>>sec2</option>
				<option value="oslec" <% if $common.echocanceller eq 'oslec' %>selected<%/if%>>oslec</option>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_chan_dahdi_set" method="POST" action="?action=do_chan_dahdi_set" target="takefire">
	<table border="0" style="margin: 20px;margin-top: 40px">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>通信功能</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">启用回音消除</td>
			<td height="30"><input type="radio" value="yes" <% if $chan_dahdi.echocancel eq 'yes' %>checked<%/if%> name="echocancel">好的&nbsp;<input type="radio" value="no" <% if $chan_dahdi.echocancel eq 'no' %>checked<%/if%> name="echocancel">不嘛
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">来电显示协议</td>
			<td height="30">
			<select size="1" name="cidsignalling">
				<option value="bell" <% if $chan_dahdi.cidsignalling eq 'bell' %>selected<%/if%>>bell</option>
				<option value="v23" <% if $chan_dahdi.cidsignalling eq 'v23' %>selected<%/if%>>v23</option>
				<option value="v23_jp" <% if $chan_dahdi.cidsignalling eq 'v23_jp' %>selected<%/if%>>v23_jp</option>
				<option value="dtmf" <% if $chan_dahdi.cidsignalling eq 'dtmf' %>selected<%/if%>>dtmf</option>
				<option value="smdi" <% if $chan_dahdi.cidsignalling eq 'smdi' %>selected<%/if%>>smdi</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">取得来电方式</td>
			<td height="30">
			<select size="1" name="cidstart">
				<option value="ring" <% if $chan_dahdi.cidstart eq 'ring' %>selected<%/if%>>通过震铃</option>
				<option value="polarity" <% if $chan_dahdi.cidstart eq 'polarity' %>selected<%/if%>>反极信号</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">音量增益</td>
			<td height="30">&nbsp;接收&nbsp;<input type="text" id="iptext1" name="rxgain" size="4" value="<% $chan_dahdi.rxgain %>">&nbsp;&nbsp;&nbsp;&nbsp;发送&nbsp;<input type="text" id="iptext1" name="txgain" size="4" value="<% $chan_dahdi.txgain %>">
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
	<table border="0" style="margin: 20px;margin-top: 40px">
		<tr>
			<td colspan="2">&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>语音卡状态</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td align="left" colspan='2'>
			<table border="0" cellspacing="0" style="margin-top: 0px">
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;SPAN编号&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;设备信息&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;通道数量&nbsp;</td>
				</tr>
		<%foreach from=$cardstat item=eachone key=keyname %>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><b>&nbsp;<% $eachone.filename %></td>
					<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><b>&nbsp;<% $eachone.span %>&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>" class="tipmsg" title="<%foreach from=$eachone.channels item=chan key=keyname %><% $chan %><br><%/foreach%>"><b>&nbsp;查看&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><input type="button" value="设置 模拟口参数" name="button" id='btn1' class='showpopDialog' func="option_hardware.php?action=func_analog_option">&nbsp;&nbsp;<input type="button" value="设置 数字中继参数" name="button" id='btn1' class='showpopDialog' func="option_hardware.php?action=func_digital_option"></td>
		</tr>
		<tr>
			<td align="left" colspan='2'>
			<table border="0" cellspacing="0" width="100%">
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;FXS分机&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;FXS缺电报警&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;FXO外线&nbsp;</td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;未安装&nbsp;</td>
				</tr>
				<tr>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $hardware_map.analog.fxs %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $hardware_map.analog.fxs_failed %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $hardware_map.analog.fxo %></td>
					<td height="30" align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;<% $hardware_map.analog.not_install %></td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
