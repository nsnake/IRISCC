<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>

<!-- body -->
<div id="body-body">
	<h3>功能热键</h3>
	<p>&nbsp;</p>

<form method="POST" action="?action=do_features_key" target="takefire">
	<div align="left">
	<table border="0" align="left" style="margin: 15px">
		<tr>
			<td height="30" align="left" colspan='2'><b>热键为呼叫过程中分机使用的功能.</b><span class="tipmsg" title="如果想完全禁止功能请将热键设置为不可能输入的字符比如英文字母." style="background-color: #F5A830;color:#FFFFFF;text-decoration: none;"><b>&nbsp;?&nbsp;</b></span></td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/11.png">&nbsp;&nbsp;分机代答</td>
			<td height="30"><input type="text" id="iptext1" name="pickupexten" size="8" value="<% $pickupexten %>" class="tipmsg" title="同一分组内如果有分机震铃,其他话机按此键可以代替其应答."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 30px"><img src="../images/icon/11.png">&nbsp;&nbsp;指定号码代答</td>
			<td height="30">* + 分机号码, 如*8001表示代8001接起.</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/17.png">&nbsp;&nbsp;电话盲转</td>
			<td height="30"><input type="text" id="iptext1" name="blindxfer" size="8" value="<% $blindxfer %>" class="tipmsg" title="将当前的呼叫直接转移给另外一个人。"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/17.png">&nbsp;&nbsp;话务员转接</td>
			<td height="30"><input type="text" id="iptext1" name="atxfer" size="8" value="<% $atxfer %>" class="tipmsg" title="将话务转给其它分机."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/pi237.png">&nbsp;&nbsp;呼叫驻留</td>
			<td height="30"><input type="text" id="iptext1" name="parkcall" size="8" value="<% $parkcall %>" class="tipmsg" title="你接起来了一个电话,这个时候按此热键系统会将这个用户驻留在等待音乐中并且告诉你一个号码,比如701 你这个时候可以挂掉电话,然后走到任何一部话机上按701可以将此电话转接过来.">&nbsp;<input type="text" id="iptext1" name="parkpos" size="8" value="<% $parkpos %>" class="tipmsg" title="呼叫驻留号码范围."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/pi401.png">&nbsp;&nbsp;一键录音</td>
			<td height="30"><input type="text" id="iptext1" name="fri2automon" size="8" value="<% $fri2automon %>" class="tipmsg" title="如果你觉得当前谈话十分重要,按此热键系统将在向你播放beep的一声后开始录下你们谈话内容."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/pi447.png">&nbsp;&nbsp;信箱管理</td>
			<td height="30"><input type="text" id="iptext1" name="voicemailmain" size="8" value="<% $voicemailmain %>" class="tipmsg" title="按此键，收听自己语音信箱中的留言."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/11.png">&nbsp;&nbsp;读分机号码</td>
			<td height="30"><input type="text" id="iptext1" name="originate_diagnosis" size="8" value="<% $originate_diagnosis %>" class="tipmsg" title="读出当前分机的号码."></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px"><img src="../images/icon/11.png">&nbsp;&nbsp;热键会议</td>
			<td height="30"><input type="text" id="iptext1" name="nwaystart" size="8" value="<% $nwaystart %>" class="tipmsg" title="在通话过程中主叫按该热键可以使双方都进入到隐秘的会议室中,如果需要继续邀请其它人可以使用热键0,邀请完成后按*0返回会议室."></td>
		</tr>
		<tr>
			<td height="100">
				<input type="submit" value="保存" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
	</div>
</form>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>

