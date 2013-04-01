<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<script type="text/javascript">
$(document).ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$(".reloadbtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('确定执行吗?' ,'提示' , function(r) {
			if (r == true)
			{
				window.location.href=gotourl;
//				$('#takefire').attr('src',gotourl);
			}
		});
	});

	$(".restartbtn").click(function(){
		var gotourl = $(this).attr("gotourl");
		jConfirm('硬件重启执行后请在50秒后登入系统,确定执行吗?' ,'提示' , function(r) {
			if (r == true)
			{
				window.location.href=gotourl;
//				$('#takefire').attr('src',gotourl);
			}
		});
	});

});
</script>
<!-- body -->
<div id="body-body">
	<h3>重启系统</h3>
	<div align="left">
	<table border="0" height='200' width="90%" align="left" style="margin: 0px">
		<tr>
			<td height="30" align="left" width="100%"></td>
		</tr>
		<tr>
			<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;&nbsp;&nbsp;<input type="button" value="重置系统" name="button" class="reloadbtn" id='btn1' gotourl="pbx_reload.php?action=reload&area=all&return=pbx_reload.php">&nbsp;&nbsp;&nbsp;重新读取所有设置的配置,将最大限度保证整个通话的正常通畅.但对于有一些硬件依赖功能 必须执行'硬件重启'</td>
		</tr>
		<tr>
			<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;&nbsp;&nbsp;<input type="button" value="软件重启" name="button" class="restartbtn" id='btn2' gotourl="pbx_reload.php?action=reload&area=softrestart&return=./">&nbsp;&nbsp;&nbsp;执行该功能将重设所有通信部分的组建,一切正在进行的通话将中断.</td>
		</tr>
		<tr>
			<td height="30" align="left" style="border-bottom: #ACA8A1 1px solid;">&nbsp;&nbsp;&nbsp;<input type="button" value="硬件重启" name="button" class="restartbtn" id='btn2' gotourl="pbx_reload.php?action=restart&area=all&return=./">&nbsp;&nbsp;&nbsp;执行该功能将进行硬件级别的重新启动,一切正在进行的通话将中断.</td>
		</tr>
	</table>
	</div>
</div>


<% include file="cpanel/page_footer.inc.tpl" %>

