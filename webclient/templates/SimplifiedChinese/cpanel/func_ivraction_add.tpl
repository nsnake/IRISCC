<% include file="cpanel/func_header.inc.tpl" %>
<script language="javascript">

	infomsg = new Array();
	infomsg[10]='<img src="../images/icon/xi66.png">&nbsp;这个动作会向呼叫者播放一个声音,然后再处理后续的动作.声音可以指定系统任何已经存在的语音文件.';
	infomsg[11]='<img src="../images/icon/microphone.png">&nbsp;从呼叫者进行录音. 如果呼叫者要停止录音需要按井("#")号键.　声音会被存储到一个你所设置的变量, 等待后续的动作操作.';
	infomsg[12]='<img src="../images/icon/xi66.png">&nbsp;播放之前从呼叫者那里产生的录音. 声音文件需要是之前所指定的变量.';
	infomsg[20]='<img src="../images/icon/pi401.png">&nbsp;从呼叫者那里录制0-9的字符. 如果呼叫者要停止录制需要按井("#")号键. 数字会被存储到一个你所设置的变量中, 等待后续的动作操作.';
	infomsg[21]='<img src="../images/icon/19.png">&nbsp;向呼叫者读出字符. 每个字符将一个一个读出, 比如: 1234 会读做 "一　二　三　四". 如果是想象数字那样读出来(" 一千二百三十四 ")请使用"数字方式读出". 被读取的字符可以是固定的设置也可以是来自之前录制的.';
	infomsg[22]='<img src="../images/icon/19-2.png">&nbsp;向呼叫者读出数字. 比如: 1234 会读做" 一千二百三十四 ". 如果你想每个字符一个一个读出("一　二　三　四")请使用"读出0-9字符". 被读取的字符可以是固定的设置也可以是来自之前录制的.';
	infomsg[30]='<img src="../images/icon/pi126.png">&nbsp;读出当前的日期和时间或选择一个指定的日期时间, 可以从变量中读出.';
	infomsg[31]='<img src="../images/icon/pi125.png">&nbsp;这个步骤将根据所设置的条件检测日期. 工作时间和非工作时间. 一旦匹配了检测条件就跳转到指定的IVR菜单和一个被设定了的动作位置';
	infomsg[40]='<img src="../images/icon/xa38.png">&nbsp;对这个呼叫者的主叫号码进行修改, 增加前缀或直接替换成为一个新的主叫号码,或从字符变量中读出.';
	infomsg[41]='<img src="../images/icon/11.png">&nbsp;拨打一个预设的固定号码, 或是从之前录制的字符拨打, 可以指定号码类型(任意类型,分机,呼叫队列,会议室).';
	infomsg[42]='<img src="../images/icon/pi447.png">&nbsp;让呼叫者进入一个指定分机的语音信箱.';
	infomsg[43]='<img src="../images/icon/43.png">&nbsp;让呼叫者进入一个被选择了的IVR菜单和一个被定了的动作位置. 这个功能可以作为IVR跳转或返回到本菜单的某个位置.';
	infomsg[44]='<img src="../images/icon/43.png">&nbsp;通过HTTP协议访问第三方程,并且将第三方程序发会的数据以变量形式存储在IVR流程中以备调用,此功能用于同其他系统进行数据交互.';
	infomsg[45]='<img src="../images/icon/43.png">&nbsp;将用户的呼叫控制完全转移到第三方AGI程序的高级接口.';
	infomsg[80]='<img src="../images/icon/pi237.png">&nbsp;等待指定的秒数后再继续下一步的操作.';
	infomsg[81]='<img src="../images/icon/pi144.png">&nbsp;向呼叫者发出指定的电话音调.';
	infomsg[99]='<img src="../images/icon/01.png">&nbsp;执行挂机结束整个呼叫.';

	$().ready(function() {
		$("#actmode").change(function(){
			infoid = $("#actmode").val();
			if (infoid != '')
			{
				$("#info").attr('innerHTML',infomsg[infoid]);
				$("#info").show();
			} else {
				$("#info").hide();
			}
		});
	});
</script>
	<h4>增加新动作</h4>
<form name="func_ivraction_add_step2" method="POST" action="?action=func_ivraction_add_step2&ivrnumber=<% $ivrnumber %>">
	<table border="0" cellspacing="0" width='550' style="margin: 20px">
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">动作类型</td>
			<td height="30">
			<select size="1" name="actmode" id='actmode'>
			<option value=""></option>
			<option value="10">播放语音</option>
			<option value="11">发起录音</option>
			<option value="12">播放录音</option>
			<option value="">　　　　</option>
			<option value="20">录制0-9字符</option>
			<option value="21">读出0-9字符</option>
			<option value="22">数字方式读出</option>
			<option value="">　　　　</option>
			<option value="30">读出日期时间</option>
			<option value="31">检测日期</option>
			<option value="">　　　　</option>
			<option value="40">主叫变换</option>
			<option value="41">拨打号码</option>
			<option value="42">跳转信箱或传真</option>
			<option value="43">跳转到IVR菜单</option>
			<option value="44">WEB交互接口</option>
			<option value="45">AGI扩展接口</option>
			<option value="">　　　　</option>
			<option value="80">等待几秒</option>
			<option value="81">播放音调</option>
			<option value="">　　　　</option>
			<option value="99">挂机</option>
			</select>
			</td>
		</tr>
		<tr>
			<td height="60" align="left" colspan='2'>&nbsp;<font color="#ACA8A1"><span id='info' style='display:none'></span>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" style="margin: 20px">
		<tr>
			<td>
				<input type="submit" value="继续" name="submit" id='btn1'>
			</td>
		</tr>
	</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
