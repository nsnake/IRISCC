<% include file="cpanel/page_header.inc.tpl" %>
<% include file="cpanel/page_menu.inc.tpl" %>
<style type="text/css">@import "../css/jquery.datepick.css";</style>
<script type="text/javascript" src="../js/jquery.datepick.js"></script>
<script type="text/javascript" src="../js/jquery.datepick-zh-CN.js"></script>

<script type="text/javascript">
$(document).ready(function() {

	$(".showpopDialog").click(function(){loadpopDialog($(this).attr("func"));});

	$('.start_date').datepick($.datepick.regional['zh-CN'],{dateFormat: 'yy-mm-dd'});
	$('.end_date').datepick($.datepick.regional['zh-CN'],{dateFormat: 'yy-mm-dd'});

});
</script>


<!-- body -->
<div id="body-body">
	<h3>通话统计系统</h3>

	<div align="left">
<form method="POST" action="?action=page_statistical_main">
	<table border="0" style="margin-top: 20px">
		<tr>
			<td height="30">开始时间 <input type="text" name="start_date" size="16" value="<% $start_date %>" class='start_date'>  结束时间 <input type="text" name="end_date" size="16" value="<% $end_date %>" class='end_date'>&nbsp;主叫号码 <input type="text" id="iptext1" name="src" size="16" value="<% $src %>" class="tipmsg" title="可以使用*作为通配符">  被叫号码 <input type="text" id="iptext1" name="dst" size="16" value="<% $dst %>" class="tipmsg" title="可以使用*作为通配符">
			</td>
		</tr>
		<tr>
			<td>
				<input type="radio" value="exten" name="dcontext" <% if $dcontext eq 'exten'  %>checked<%/if%>>分机 <input type="radio" value="trunk" name="dcontext" <% if $dcontext eq 'trunk'  %>checked<%/if%>>中继 <input type="radio" value="" name="dcontext" <% if $dcontext eq ''  %>checked<%/if%>>全部&nbsp;&nbsp;<input type="submit" value="新的筛选" name="submit" id='btn1'>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%"><hr size="1"></td>
		</tr>
	</table>
</form>
	<table border="0" width="95%">
		<tr>
			<td height="30" align="left" width="100%" colspan="2"><% if $dcontext eq 'trunk'  %>中继<%/if%><% if $dcontext eq 'exten'  %>分机<%/if%> <% if $start_date ne ''  %>自 <% $start_date %> 到 <% $end_date %> ,<%/if%>&nbsp;<% if $src ne ''  %>主叫 <% $src %>  被叫  <% $dst %>,<%/if%></td>
		</tr>
		<tr>
			<td height="30" align="left" width="50%">当前&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;&nbsp;<img src="../images/icon/pi246.png">&nbsp;<a href="?action=do_record_download&dcontext=<% $dcontext|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>&id=<% $id|escape:'url' %>" target="takefire">下载详单</a></td>
			<td height="30" align="right">
			<a href="?action=page_statistical_main&cols_in_page=frist&dcontext=<% $dcontext|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/38.png' border='0'></a>
			<a href="?action=page_statistical_main&cols_in_page=<% $pre_cols %>&dcontext=<% $dcontext|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;
			<a href="?action=page_statistical_main&cols_in_page=<% $next_cols %>&dcontext=<% $dcontext|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0">
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">主叫号码</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">被叫号码</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">状态</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">时长</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">时间</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">会话关联</td>
				</tr>
		<%foreach from=$recordlist item=eachone key=keyname %>
 				<tr>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.src %></td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.dst %></td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.disposition %></td>
					<td  align="right" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.duration %>(<% $eachone.billsec %>)&nbsp;秒&nbsp;</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>">&nbsp;<% $eachone.calldate %></td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" bgcolor="<% if $keyname is even %>#FFFFFF<%else%>#F3F3F3<%/if%>"><% if $eachone.callsessionid ne '' %><a href="###" class='showpopDialog' func="analyze_callsession.php?action=func_sessionflow_list&callsessionid=<% $eachone.callsessionid %>&cdruniqueid=<% $eachone.uniqueid %>">查看会话</a><%/if%>&nbsp;</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
