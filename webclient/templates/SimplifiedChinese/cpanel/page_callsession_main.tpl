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

	$("#colorselected tr").mouseover(function(){
		if (!$(this).hasClass("selected_color_callsession_disabled"))
		{
			for (c = 0; c < $(this.cells).length; c++) { $(this.cells)[c].style.backgroundColor = '#0C8AD6'; }
			$(this).css({ cursor: 'pointer' });
		}
	});
	$("#colorselected tr").mouseout(function(){
		if (!$(this).hasClass("selected_color_callsession_disabled"))
		{
			for (c = 0; c < $(this.cells).length; c++) { $(this.cells)[c].style.backgroundColor = '#FFFFFF'; }
		}
	});

});
</script>


<!-- body -->
<div id="body-body">
	<h3>呼叫会话统计</h3>

	<div align="left">
<form method="POST" action="?action=page_callsession_main">
	<table border="0" width="95%" style="margin-top: 20px">
		<tr>
			<td height="30">日期范围从 <input type="text" name="start_date" size="16" value="<% $start_date %>" class='start_date'>  到 <input type="text" name="end_date" size="16" value="<% $end_date %>" class='end_date'>&nbsp;主叫号码 <input type="text" id="iptext1" name="src" size="16" value="<% $src %>" class="tipmsg" title="可以使用*作为通配符">  被叫号码 <input type="text" id="iptext1" name="dst" size="16" value="<% $dst %>" class="tipmsg" title="可以使用*作为通配符">
			</td>
		</tr>
		<tr>
			<td>
				<input type="radio" value="1" name="routerline" <% if $routerline eq '1'  %>checked<%/if%>>呼叫来自内线 <input type="radio" value="2" name="routerline" <% if $routerline eq '2'  %>checked<%/if%>>呼叫来自外线 <input type="radio" value="0" name="routerline" <% if $routerline eq '0'  %>checked<%/if%>>呼叫来自其他 <input type="radio" value="" name="routerline" <% if $routerline eq ''  %>checked<%/if%>>呼叫来自任意&nbsp;&nbsp;<input type="submit" value="新的筛选" name="submit" id='btn1'>
			</td>
		</tr>
		<tr>
			<td height="30" align="center" width="100%"><hr size="1"></td>
		</tr>
	</table>
</form>
	<table border="0" width="95%">
		<tr>
			<td height="30" align="left">当前&nbsp;(&nbsp;<% $from_cols %>&nbsp;-&nbsp;<% $to_cols %>&nbsp;)&nbsp;&nbsp;<% if $routerline eq '1'  %>来自内线<%/if%><% if $routerline eq '2'  %>来自外线<%/if%><% if $routerline eq '0'  %>来自其他<%/if%> <% if $start_date ne ''  %>自 <% $start_date %> 到 <% $end_date %> ,<%/if%>&nbsp;<% if $src ne ''  %>主叫 <% $src %>  被叫  <% $dst %>,<%/if%>&nbsp;<i>接通状态仅列最新其他请详细查看.</i></td>
			<td height="30" align="right">
			<a href="?action=page_callsession_main&cols_in_page=frist&routerline=<% $routerline|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/38.png' border='0'></a>
			<a href="?action=page_callsession_main&cols_in_page=<% $pre_cols %>&routerline=<% $routerline|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/32.png' border='0'></a>&nbsp;
			<a href="?action=page_callsession_main&cols_in_page=<% $next_cols %>&routerline=<% $routerline|escape:'url' %>&start_date=<% $start_date|escape:'url' %>&end_date=<% $end_date|escape:'url' %>&src=<% $src|escape:'url' %>&dst=<% $dst|escape:'url' %>"><img src='../images/icon/31.png' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td height="30" align="left" width="100%" colspan='2'>
			<table border="0" width="100%" align="left" cellspacing="0" cellpadding="0" id='colorselected'>
 				<tr class='selected_color_callsession_disabled'>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">会话来自</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">主叫</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">被叫</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">状态</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">时长</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">时间</td>
					<td  align="center" style="border-bottom: #ACA8A1 1px solid;">&nbsp;</td>
				</tr>
		<%foreach from=$recordlist item=eachone key=keyname %>
 				<tr class='showpopDialog' func="analyze_callsession.php?action=func_sessionflow_list&callsessionid=<% $eachone.id %>">
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;<% if $eachone.routerline eq '2' %>外&nbsp;线<%/if%><% if $eachone.routerline eq '1' %>内&nbsp;线<%/if%><% if $eachone.routerline eq '0' %>其&nbsp;它<%/if%><% if $eachone.routerline eq '' %><%/if%></td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;" class="tipmsg" title="帐户: <% $eachone.accountcode %>">&nbsp;<% $eachone.callernumber %></td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;<% $eachone.extension %></td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;<% if $eachone.cdr.disposition eq 'ANSWERED' %>接通<% elseif $eachone.cdr.disposition ne '' %>未接通<%else%><% $eachone.cdr.disposition %><%/if%></td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;<% $eachone.cdr.billsec %>&nbsp;秒&nbsp;</td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;<% $eachone.cretime %></td>
					<td  align="center" id='colorline' style="border-bottom: #ACA8A1 1px solid;font-size: 13px;">&nbsp;详&nbsp;细</td>
				</tr>
		<%/foreach%>
			</table>
			</td>
		</tr>
	</table>
	</div>

</div>

<% include file="cpanel/page_footer.inc.tpl" %>
