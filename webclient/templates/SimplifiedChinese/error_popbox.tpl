<HTML>
 <HEAD>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <TITLE> Warnning messages </TITLE>
  <script language="javascript" src="../js/msgcode.js"></script>
	<script>
	function response()
	{
		var type = "<% $type %>";
		var errorcode = "<% $errorcode %>";
		var newurl = "<% $location %>";
		var isNumeric=/^[0-9]+$/;

		if (type == "submit_successfuly")
		{
			if (isNumeric.exec(errorcode))
			{
				top.jAlert(msgcode[errorcode], '提示');
			} else if (errorcode != '')
			{
				top.jAlert(errorcode, '系统错误');
			}
			parent.document.location.href=newurl;


		} else if (type == "submit_confirm")
		{
			if (isNumeric.exec(errorcode))
			{
				top.jConfirm(msgcode[errorcode] ,'确认', function(r) {
					if (r == true) {
						parent.document.location.href=newurl;
					} else {
						parent.document.location.reload();
					}
				});

			} else if (errorcode != '')
			{
				top.jAlert(errorcode, '系统错误');
			} else if (errorcode == '')
			{
				top.jAlert(msgcode[101], '系统错误');
			}


		} else if (type == "submit_failed")
		{
			if (isNumeric.exec(errorcode))
			{
				top.jAlert(msgcode[errorcode], '提示');
			} else if (errorcode != '')
			{
				parent.iframeresponse.style.display='block';
				top.jAlert(errorcode, '系统错误');
			} else if (errorcode == '')
			{
				top.jAlert(msgcode[101], '系统错误');
			}
		} else if (type == "debug")
		{
			parent.iframeresponse.style.display='block';
		}
	}
	</script>
 </HEAD>

 <BODY onload="javascript: response()">
<pre>
errorcode : <% $errorcode %>
line : <% $line %>
function : <% $function %>
location : <% $location %>
type : <% $type %>
filename : <% $filename %>



<% $rawerrstr %>

</pre>
 </BODY>
</HTML>
