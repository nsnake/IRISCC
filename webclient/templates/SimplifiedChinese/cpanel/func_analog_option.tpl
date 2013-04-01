<% include file="cpanel/func_header.inc.tpl" %>
	<h4>模拟口参数</h4>
<form name="do_analog_option" method="POST" action="?action=do_analog_option&type=hardware" target="takefire">
	<table border="0" style="margin: 20px;">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>驱动配置</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">FXO外线接口信令</td>
			<td height="30">
			<select size="1" name="fxo_protocol">
				<option value="fxsls" <% if $analog.fxo_protocol eq 'fxsls' %>selected<%/if%>>FXS Loopstart</option>
				<option value="fxsgs" <% if $analog.fxo_protocol eq 'fxsgs' %>selected<%/if%>>FXS Groundstart</option>
				<option value="fxsks" <% if $analog.fxo_protocol eq 'fxsks' %>selected<%/if%>>FXS Koolstart</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">FXS分机接口信令</td>
			<td height="30">
			<select size="1" name="fxs_protocol">
				<option value="fxols" <% if $analog.fxs_protocol eq 'fxols' %>selected<%/if%>>FXO Loopstart</option>
				<option value="fxogs" <% if $analog.fxs_protocol eq 'fxogs' %>selected<%/if%>>FXO Groundstart</option>
				<option value="fxoks" <% if $analog.fxs_protocol eq 'fxoks' %>selected<%/if%>>FXO Koolstart</option>
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
<form name="do_analog_option" method="POST" action="?action=do_analog_option&type=fxo" target="takefire">
	<table border="0" style="margin: 20px;">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>FXO 模拟外线</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">挂机忙音检测</td>
			<td height="30"><input type="radio" value="yes" <% if $chan_dahdi.busydetect eq 'yes' %>checked<%/if%> name="busydetect">好的&nbsp;<input type="text" id="iptext1" name="busycount" size="4" value="<% $chan_dahdi.busycount %>">&nbsp;次&nbsp;&nbsp;&nbsp;<input type="radio" value="no" <% if $chan_dahdi.busydetect eq 'no' %>checked<%/if%> name="busydetect">不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">挂机忙音频率</td>
			<td height="30"><input type="text" id="iptext1" name="busypattern" size="4" value="<% $chan_dahdi.busypattern %>"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">脉冲拨号</td>
			<td height="30"><input type="radio" value="yes" <% if $chan_dahdi.pulsedial eq 'yes' %>checked<%/if%> name="pulsedial">好的&nbsp;<input type="radio" value="no" <% if $chan_dahdi.pulsedial eq 'no' %>checked<%/if%> name="pulsedial">不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">反极性应答检测</td>
			<td height="30"><input type="radio" value="yes" <% if $chan_dahdi.answeronpolarityswitch eq 'yes' %>checked<%/if%> name="answeronpolarityswitch">好的&nbsp;<input type="radio" value="no" <% if $chan_dahdi.answeronpolarityswitch eq 'no' %>checked<%/if%> name="answeronpolarityswitch">不嘛</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">反极性挂机检测</td>
			<td height="30"><input type="radio" value="yes" <% if $chan_dahdi.hanguponpolarityswitch eq 'yes' %>checked<%/if%> name="hanguponpolarityswitch">好的&nbsp;<input type="radio" value="no" <% if $chan_dahdi.hanguponpolarityswitch eq 'no' %>checked<%/if%> name="hanguponpolarityswitch">不嘛</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_analog_option" method="POST" action="?action=do_analog_option&type=fxs" target="takefire">
	<table border="0" style="margin: 20px;">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>FXS 模拟分机</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
