<% include file="cpanel/func_header.inc.tpl" %>
<script language='javascript'>
$().ready(function() {

	$("#agree").click(function(){
		if ($("#agree").attr("checked")==true)
		{
			$(".submitbutton").attr("disabled",false);
		} else {
			$(".submitbutton").attr("disabled",true);
		}
	});

});
</script>
<form name="registration" method="POST" action="?action=do_registration" target="takefire">
<table border="0" width="100%" id="table1">
	<tr>
		<td align='center'><H5>注册系统信息</H5></td>
	</tr>
	<tr>
		<td colspan="3">
	<table border="0" cellspacing="0">
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">帐号</td>
			<td height="30"><input type="text" id="iptext1" name="username" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">密码</td>
			<td height="30"><input type="password" id="iptext1" name="password" size="20" value=""></td>
		</tr>
		<tr>
			<td align='center' colspan='2'><a href="http://cn.freeiris.org/user.php" target="_blank">如果你没有freeiris.org的帐号,请点击这里申请.</a></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">第一次使用freeiris2吗?</td>
			<td height="30"><input type="radio" name="frist_time" value="yes" checked>是的&nbsp;<input type="radio" name="frist_time" value="no">不是了</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">通过什么渠道得到的系统的?</td>
			<td height="30"><input type="radio" name="where_download" value="friend">朋友介绍&nbsp;<input type="radio" name="where_download" value="myself" checked>自己找到&nbsp;<input type="radio" name="where_download" value="search">通过搜索引擎&nbsp;<br><input type="radio" name="where_download" value="otherwebsite">通过其他网站&nbsp;<input type="radio" name="where_download" value="advert">通过广告</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">对这个系统有什么希望?</td>
			<td height="30"><input type="text" id="iptext1" name="request" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">这个系统的是用来做什么的</td>
			<td height="30"><input type="radio" name="usedfor" value="tech">技术研究&nbsp;<input type="radio" name="usedfor" value="pbx" checked>做单位交换机&nbsp;<input type="radio" name="usedfor" value="callcenter">做呼叫中心&nbsp;<br><input type="radio" name="usedfor" value="tomyproduct">改为自己产品销售&nbsp;<input type="radio" name="usedfor" value="spy">希望偷窃代码</td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">公司类型</td>
			<td height="30"><input type="text" id="iptext1" name="company" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">公司规模</td>
			<td height="30"><input type="text" id="iptext1" name="company_size" size="20" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left" style="padding-left: 10px;padding-right: 40px">联系方式</td>
			<td height="30"><input type="text" id="iptext1" name="company_contact" size="20" value=""></td>
		</tr>
	</table>
		</td>
	</tr>
	<tr>
		<td align='center'><H5><input type="checkbox" name="C1" value="ON" id ='agree'>我承诺以上信息都是真实有效的,如有虚假天打雷劈不得好死</H5></td>
	</tr>
	<tr>
		<td align='center'><input type="submit" value="完成注册" name="submit" id='btn1' class='submitbutton' disabled></td>
	</tr>
</table>
</form>

<% include file="cpanel/func_footer.inc.tpl" %>
