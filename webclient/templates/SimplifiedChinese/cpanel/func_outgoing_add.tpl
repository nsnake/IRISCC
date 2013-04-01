<% include file="cpanel/func_header.inc.tpl" %>
<script type="text/javascript" src="../js/jquery.datepick.js"></script>
<script type="text/javascript" src="../js/jquery.datepick-zh-CN.js"></script>
<script type="text/javascript"> 
	$(function() { 
		$.datepick.formatDate("yy-MM-DD");
		$('.favDate').datepick({minDate: new Date(),changeYear: false});
	}); 
</script>
<style type="text/css">@import "../css/jquery.datepick.css";</style>
	<h4>创建自动外呼计划</h4>
<form id='form' name="form" method="POST" action="?action=do_outgoing_add" target="takefire">
	<table border="0" cellspacing="0" style='margin:20px'>
		<tr>
			<td height="30" align="center" width="100%" colspan="2"><hr size="1"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">外呼名称</td>
			<td height="30"><input type="text" id="iptext1" name="name" size="20" value="" class="tipmsg" title="必填,纯数字填写"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">主叫号码</td>
			<td height="30"><input type="text" id="iptext1" name="outgoing_callerid" size="16" value="">&nbsp;以什么主叫号码发起呼叫.</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">震铃时长</td>
			<td height="30"><input type="text" id="iptext1" name="outgoing_waittime" size="16" value="30" class="tipmsg" title="必填">&nbsp;响铃多长时间表示无人接听.</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">每次并发</td>
			<td height="30"><input type="text" id="iptext1" name="concurrent" size="2" value="1">&nbsp;并发量请不要超过你的外线能力.</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">开始时间</td>
			<td height="30"><input type="text" class='favDate' name="startime_date" size="11" value="" class="tipmsg" title="必填">&nbsp;
			<select size="1" name="startime_hour">
			  <option value="00">00</option>
			  <option value="01">01</option>
			  <option value="02">02</option>
			  <option value="03">03</option>
			  <option value="04">04</option>
			  <option value="05">05</option>
			  <option value="06">06</option>
			  <option value="07">07</option>
			  <option value="08">08</option>
			  <option value="09">09</option>
			  <option value="10">10</option>
			  <option value="11">11</option>
			  <option value="12">12</option>
			  <option value="13">13</option>
			  <option value="14">14</option>
			  <option value="15">15</option>
			  <option value="16">16</option>
			  <option value="17">17</option>
			  <option value="18">18</option>
			  <option value="19">19</option>
			  <option value="20">20</option>
			  <option value="21">21</option>
			  <option value="22">22</option>
			  <option value="23">23</option>
			  </select>
			&nbsp;时&nbsp;
                <select size="1" name="startime_minute">
				<option value="00">00</option>
				<option value="01">01</option>
				<option value="02">02</option>
				<option value="03">03</option>
				<option value="04">04</option>
				<option value="05">05</option>
				<option value="06">06</option>
				<option value="07">07</option>
				<option value="08">08</option>
				<option value="09">09</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
				<option value="17">17</option>
				<option value="18">18</option>
				<option value="19">19</option>
				<option value="20">20</option>
				<option value="21">21</option>
				<option value="22">22</option>
				<option value="23">23</option>
                <option value="24">24</option>
                <option value="25">25</option>
                <option value="26">26</option>
                <option value="27">27</option>
                <option value="28">28</option>
                <option value="29">29</option>
                <option value="30">30</option>
                <option value="31">31</option>
                <option value="32">32</option>
                <option value="33">33</option>
                <option value="34">34</option>
                <option value="35">35</option>
                <option value="36">36</option>
                <option value="37">37</option>
                <option value="38">38</option>
                <option value="39">39</option>
                <option value="40">40</option>
                <option value="41">41</option>
                <option value="42">42</option>
                <option value="43">43</option>
                <option value="44">44</option>
                <option value="45">45</option>
                <option value="46">46</option>
                <option value="47">47</option>
                <option value="48">48</option>
                <option value="49">49</option>
                <option value="50">50</option>
                <option value="51">51</option>
                <option value="52">52</option>
                <option value="53">53</option>
                <option value="54">54</option>
                <option value="55">55</option>
                <option value="56">56</option>
                <option value="57">57</option>
                <option value="58">58</option>
                <option value="59">59</option>
                </select>&nbsp;分&nbsp;00&nbsp;秒	        </p></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">外呼的号码</td>
			<td height="30"><textarea rows="6" id="iptext1" name="members" cols="30" title='系统根据分机拨出规则选择外线.号码以换行做分割' class="tipmsg"></textarea></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">接通后连接本地号码</td>
			<td height="30"><input type="text" id="iptext1" name="localnumber" size="16" value=""></td>
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
