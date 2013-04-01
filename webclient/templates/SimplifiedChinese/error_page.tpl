<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <TITLE> <% $title %> </TITLE>
  <script language="javascript" src="../js/msgcode.js"></script>
  <script>
	function init() {
		<% if $errorcode eq '' %>
		window.ERROR_SUBJECT.innerHTML=msgcode[101];
		<%else%>
		window.ERROR_SUBJECT.innerHTML=msgcode[<% $errorcode %>];
		<%/if%>
		var secs =3; //倒计时的秒数
		for(var i=secs;i>=0;i--)
		{
			window.setTimeout('doUpdate(' + i + ')', (secs-i) * 1000); 
		} 
	}
	function doUpdate(num) 
	{
		if(num == 0) {self.window.history.go(-1); } 
	} 
  </script>
 </HEAD>

 <BODY onload="javascript:init()">

<pre>
<b>错误警告</b>

程  序: <% $filename %>
函数名: <% $function %>
行  数: <% $line %>

错误名称: <b><span id="ERROR_SUBJECT"></b></span>

<% $rawerrstr %>

将在3秒后返回上一页...
</pre>
  
 </BODY>
</HTML>
