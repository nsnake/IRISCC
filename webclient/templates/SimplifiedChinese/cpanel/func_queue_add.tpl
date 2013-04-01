<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
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
	//成员上移
	$("#membersmoveup").click(function(){
		var so = $("#members option:selected");
		if(so.get(0) && so.get(0).index!=0){
			so.each(function(){
				$(this).prev().before($(this));
			});
		}
	});
	//成员下移
	$("#membersmovedown").click(function(){
		var so = $("#members option:selected");
		if(so.get(0) && so.get(0).index!=($("#members option").length-1)){
			so.each(function(){
				$(this).next().after($(this));
			});
		}
	});
	//提交时全选
	$("#queue_form").submit(function(){
		$("#members option").each(function(){
			$(this).attr('selected',true);
		});
		return true;
	});

});

</script>
	<h4>创建呼叫队列</h4>
<form id='queue_form' name="queue_form" method="POST" action="?action=do_queue_add" target="takefire">
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;<img src="../images/icon/pi107.png"><img src="../images/icon/pi110.png">&nbsp;&nbsp;队列设置</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">队列号码</td>
			<td height="30"><input type="text" id="iptext1" name="queuenumber" size="8" value="" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">队列名称</td>
			<td height="30"><input type="text" id="iptext1" name="queuename" size="30" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">震铃策略</td>
			<td height="30">
			<select size="1" name="strategy">
				<option value="ringall">坐席全呼</option>
				<option value="roundrobin">轮流呼叫</option>
				<option value="leastrecent">最近接听最少</option>
				<option value="fewestcalls">完成呼叫最少</option>
				<option value="random">随机呼叫</option>
				<option value="rrmemory">记忆轮流呼叫</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'><span class="tipmsg" title="成员全呼     —— 所有成员一起响铃直到有人接起为止<br>轮流呼叫     —— 循环的让所有队列成员震铃<br>最近接听最少 —— 最近接听最少的成员震铃<br>完成呼叫最少 —— 队列中完成呼叫最少的成员震铃<br>随机呼叫     —— 随机一个成员震铃<br>记忆轮流呼叫 —— 循环的让所有队列成员震铃,并且记住上次是哪个成员应答的<br>" style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>学习选择适合的震铃策略?</b></span></td>
		</tr>
	</table>
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;呼叫者体验设置</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="40" style="padding-left: 10px;padding-right: 40px"><input type="radio" value="1" name="playring">在队列中播放震铃音</td>
		</tr>
		<tr>
			<td height="40" style="padding-left: 10px;padding-right: 40px"><input type="radio" value="0" checked name="playring">在队列中播放等待音乐</td>
		</tr>
		<tr>
			<td height="40" style="padding-left: 10px;padding-right: 40px"><input type="checkbox" value="1" name="saymember">用户接起后拨报坐席号码</td>
		</tr>
		<tr>
			<td height="50" align="left" style="padding-left: 10px;padding-right: 40px">繁忙循环通知周期&nbsp;&nbsp;<input type="text" id="iptext1" name="periodic-announce-frequency" size="4" value="20" class="tipmsg" title="必填,纯数字填写"> 秒</td>
		</tr>
		<tr>
			<td height="50" colspan='2' style="padding-left: 10px;padding-right: 40px">如果呼叫者已经在队列中等待超过 <input type="text" id="iptext1" name="queuetimeout" size="4" value="300" class="tipmsg" title="必填,纯数字填写"> 秒，将呼叫者跳转到本地号码 <input type="text" id="iptext1" name="failedon" size="8" value="" class="tipmsg" title="必填,纯数字填写"> 处理</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" id='recordoverphone' style='margin:20px'>
		<tr>
			<td height="30" align="left">&nbsp;&nbsp;队列成员</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">震铃时长</td>
			<td height="30"><input type="text" id="iptext1" name="timeout" size="4" value="16" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" colspan='2'><span style="color: #0C8AD6;text-decoration: none;font-size: 12px"><img src="../images/icon/19.png"><b>每个成员震铃时长大约消耗4秒</b></span></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">接听前通知</td>
			<td height="30">
			<select size="1" name="announce">
				<option value="">无</option>
				<option value="freeiris/callfromqueue">呼叫来自队列</option>
				<option value="freeiris/silence5">静音五秒</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px" valign='top'>选择成员</td>
			<td height="30" >
			<table border="0" width="100%" id="table1">
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
					<td><a href="###" id='membersmoveup'><img src="../images/icon/pi308.png" border='0'></a><br><br><a href="###" id='membersmovedown'><img src="../images/icon/pi307.png" border='0'></a></td>
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
