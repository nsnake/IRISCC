<% include file="cpanel/func_header.inc.tpl" %>
	<h4>数字中继参数</h4>
<form name="do_digital_option" method="POST" action="?action=do_digital_option&type=hardware" target="takefire">
	<table border="0" style="margin: 20px;">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>驱动配置</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">时钟源(timing sync source)</td>
			<td height="30"><input type="radio" value="0" <% if $digital.span_timesource eq '0' %>checked<%/if%> name="span_timesource">主动时钟&nbsp;<input type="radio" value="1" <% if $digital.span_timesource eq '1' %>checked<%/if%> name="span_timesource">被动时钟</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">LBO(line build-out)</td>
			<td height="30">
			<select size="1" name="span_lbo">
				<option value="0" <% if $digital.span_lbo eq '0' %>selected<%/if%>>0 db (CSU) / 0-133 feet (DSX-1)</option>
				<option value="1" <% if $digital.span_lbo eq '1' %>selected<%/if%>>133-266 feet (DSX-1)</option>
				<option value="2" <% if $digital.span_lbo eq '2' %>selected<%/if%>>266-399 feet (DSX-1)</option>
				<option value="3" <% if $digital.span_lbo eq '3' %>selected<%/if%>>399-533 feet (DSX-1)</option>
				<option value="4" <% if $digital.span_lbo eq '4' %>selected<%/if%>>533-655 feet (DSX-1)</option>
				<option value="5" <% if $digital.span_lbo eq '5' %>selected<%/if%>>-7.5db (CSU)</option>
				<option value="6" <% if $digital.span_lbo eq '6' %>selected<%/if%>>-15db (CSU)</option>
				<option value="7" <% if $digital.span_lbo eq '7' %>selected<%/if%>>-22.5db (CSU)</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">成帧(framing)</td>
			<td height="30">
			<select size="1" name="span_framing">
				<option value="d4" <% if $digital.span_framing eq 'd4' %>selected<%/if%>>T1 d4</option>
				<option value="esf" <% if $digital.span_framing eq 'esf' %>selected<%/if%>>T1 esf</option>
				<option value="cas" <% if $digital.span_framing eq 'cas' %>selected<%/if%>>E1 cas</option>
				<option value="ccs" <% if $digital.span_framing eq 'ccs' %>selected<%/if%>>E1 ccs</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">编码(coding)</td>
			<td height="30">
			<select size="1" name="span_coding">
				<option value="ami" <% if $digital.span_coding eq 'ami' %>selected<%/if%>>T1/E1 ami</option>
				<option value="b8zs" <% if $digital.span_coding eq 'b8zs' %>selected<%/if%>>T1 b8zs</option>
				<option value="hdb3" <% if $digital.span_coding eq 'hdb3' %>selected<%/if%>>E1 hdb3</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">CRC4效验</td>
			<td height="30">
			<select size="1" name="span_option">
				<option value="" <% if $digital.span_option eq '' %>selected<%/if%>>关闭</option>
				<option value="crc4" <% if $digital.span_option eq 'crc4' %>selected<%/if%>>CRC4</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">D信道位置</td>
			<td height="30"><input type="text" id="iptext1" name="dchan_num" size="4" value="<% $digital.dchan_num %>"></td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>
<form name="do_digital_option" method="POST" action="?action=do_digital_option&type=chan_dahdi" target="takefire">
	<table border="0" style="margin: 20px;">
		<tr>
			<td>&nbsp;<img src='../images/icon/xi78.png'>&nbsp;&nbsp;<b>中继参数</td>
		</tr>
		<tr>
			<td align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">交换类型</td>
			<td height="30">
			<select size="1" name="switchtype">
				<option value="national" <% if $chan_dahdi.switchtype eq 'national' %>selected<%/if%>>National ISDN 2</option>
				<option value="dms100" <% if $chan_dahdi.switchtype eq 'dms100' %>selected<%/if%>>Nortel DMS100</option>
				<option value="4ess" <% if $chan_dahdi.switchtype eq '4ess' %>selected<%/if%>>AT&T 4ESS</option>
				<option value="5ess" <% if $chan_dahdi.switchtype eq '5ess' %>selected<%/if%>>Lucent 5ESS</option>
				<option value="euroisdn" <% if $chan_dahdi.switchtype eq 'euroisdn' %>selected<%/if%>>EuroISDN</option>
				<option value="ni1" <% if $chan_dahdi.switchtype eq 'ni1' %>selected<%/if%>>Old National ISDN 1</option>
				<option value="qsig" <% if $chan_dahdi.switchtype eq 'qsig' %>selected<%/if%>>Q.SIG</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">信号方式</td>
			<td height="30"><input type="radio" value="pri_net" <% if $chan_dahdi.signalling eq 'pri_net' %>checked<%/if%> name="signalling">网络端&nbsp;<input type="radio" value="pri_cpe" <% if $chan_dahdi.signalling eq 'pri_cpe' %>checked<%/if%> name="signalling">客户端</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">PRI拨号方案</td>
			<td height="30">
			<select size="1" name="pridialplan">
				<option value="unknown" <% if $chan_dahdi.pridialplan eq 'unknown' %>selected<%/if%>>Unknown</option>
				<option value="private" <% if $chan_dahdi.pridialplan eq 'private' %>selected<%/if%>>Private ISDN</option>
				<option value="local" <% if $chan_dahdi.pridialplan eq 'local' %>selected<%/if%>>Local ISDN</option>
				<option value="national" <% if $chan_dahdi.pridialplan eq 'national' %>selected<%/if%>>National ISDN</option>
				<option value="international" <% if $chan_dahdi.pridialplan eq 'international' %>selected<%/if%>>International ISDN</option>
				<option value="dynamic" <% if $chan_dahdi.pridialplan eq 'dynamic' %>selected<%/if%>>Dynamically selects the appropriate dialplan</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">PRI本地拨号方案</td>
			<td height="30">
			<select size="1" name="prilocaldialplan">
				<option value="unknown" <% if $chan_dahdi.prilocaldialplan eq 'unknown' %>selected<%/if%>>Unknown</option>
				<option value="private" <% if $chan_dahdi.prilocaldialplan eq 'private' %>selected<%/if%>>Private ISDN</option>
				<option value="local" <% if $chan_dahdi.prilocaldialplan eq 'local' %>selected<%/if%>>Local ISDN</option>
				<option value="national" <% if $chan_dahdi.prilocaldialplan eq 'national' %>selected<%/if%>>National ISDN</option>
				<option value="international" <% if $chan_dahdi.prilocaldialplan eq 'international' %>selected<%/if%>>International ISDN</option>
				<option value="dynamic" <% if $chan_dahdi.prilocaldialplan eq 'dynamic' %>selected<%/if%>>Dynamically selects the appropriate dialplan</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">端口重启周期</td>
			<td height="30"><input type="text" id="iptext1" name="resetinterval" size="20" value="<% $chan_dahdi.resetinterval %>">&nbsp;秒</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">叠纵拨号</td>
			<td height="30"><input type="radio" value="no" <% if $chan_dahdi.overlapdial eq 'no' %>checked<%/if%> name="overlapdial">不是&nbsp;<input type="radio" value="yes" <% if $chan_dahdi.overlapdial eq 'yes' %>checked<%/if%> name="overlapdial">是的</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>


<% include file="cpanel/func_footer.inc.tpl" %>
