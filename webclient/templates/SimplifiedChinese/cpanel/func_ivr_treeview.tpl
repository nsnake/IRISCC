<% include file="cpanel/func_header.inc.tpl" %>
<script type="text/javascript" src="../js/jquery.treeview.js"></script>
<script language='javascript'>
$().ready(function() {

	$(".showpopDialog").click(function(){top.loadpopDialog($(this).attr("func"));});

	$("#ivrtreediv").treeview();

	top.$("#ivropt").attr('src','acd_ivrmenu.php?action=func_ivr_optmenu&ivrnumber=<% $ivrnumber %>');

	//L2 选择OPTMENU
	$(".folder").click(function(){top.$("#ivropt").attr('src',$(this).attr("func"));});
	
});
</script>
	<img src='../images/icon/pi107.png'>&nbsp;<b><a href='acd_ivrmenu.php?action=func_ivr_optmenu&ivrnumber=<% $ivrnumber %>' target='ivropt'><% $ivrmenu.ivrnumber %> - <% $ivrmenu.ivrname %></a></b><br>
<ul id="ivrtreediv" class="treeview" style='font-size: 12px;letter-spacing: 1px'>
<%foreach from=$action_array item=eachone key=keyname %>
<% if $eachone.actmode eq '10' %> 	<li>&nbsp;<img src='../images/icon/xi66.png'>&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachone.args_array.folder %>/<% $eachone.args_array.filename %>'"><% $eachone.proirety_aslevel %>.播放语音</span></a></li>
<% elseif $eachone.actmode eq '11' %> 	<li>&nbsp;<img src="../images/icon/microphone.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachone.args_array.recordvarname %>"><% $eachone.proirety_aslevel %>.发起录音</span></a></li>

<% elseif $eachone.actmode eq '12' %> 	<li>&nbsp;<img src="../images/icon/xi66.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachone.args_array.playbackvarname %>"><% $eachone.proirety_aslevel %>.播放录音</span></a></li>

<% elseif $eachone.actmode eq '20' %> 	<li>&nbsp;<img src="../images/icon/pi401.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachone.args_array.receivevarname %>"><% $eachone.proirety_aslevel %>.录制0-9字符</span></a></li>

<% elseif $eachone.actmode eq '21' %> 	<li>&nbsp;<img src="../images/icon/19.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.playbackvarname ne ''%>$<% $eachone.args_array.playbackvarname %><%else%>'<% $eachone.args_array.saydigits %>'<%/if%>"><% $eachone.proirety_aslevel %>.读出0-9字符</span></a></li>

<% elseif $eachone.actmode eq '22' %> 	<li>&nbsp;<img src="../images/icon/19-2.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.playbackvarname ne ''%>$<% $eachone.args_array.playbackvarname %><%else%>'<% $eachone.args_array.saydigits %>'<%/if%>"><% $eachone.proirety_aslevel %>.数字方式读出</span></a></li>

<% elseif $eachone.actmode eq '30' %> 	<li>&nbsp;<img src="../images/icon/pi126.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.saydatetime eq 'true'%>当前时间<% elseif $eachone.args_array.saydatefromvar ne ''%>日期 $<% $eachone.args_array.saydatefromvar %> <% elseif $eachone.args_array.saytimefromvar ne ''%>时间 $<% $eachone.args_array.saytimefromvar %><% elseif $eachone.args_array.saydatestring ne ''%>日期 '<% $eachone.args_array.saydatestring %>' <% elseif $eachone.args_array.saytimestring ne ''%>时间 '<% $eachone.args_array.saytimestring %>'<%/if%>"><% $eachone.proirety_aslevel %>.读出日期时间</span></a></li>

<% elseif $eachone.actmode eq '31' %> 	<li>&nbsp;<img src="../images/icon/pi125.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="匹配跳转 '<% $eachone.args_array.gotoivr %>'"><% $eachone.proirety_aslevel %>.检测日期 <i>(<% $eachone.args_array.gotoivr_name %>, <% if $eachone.args_array.actpoint eq ''%><%else%>第<% $eachone.args_array.actpoint_level %>步<%/if%>)</i></span></a></li>

<% elseif $eachone.actmode eq '40' %> 	<li>&nbsp;<img src="../images/icon/xa38.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.modify eq 'replace'%>替换<%elseif $eachone.args_array.modify eq 'append'%>增补<%elseif $eachone.args_array.modify eq '结尾增加'%><%/if%> '<% $eachone.args_array.altercallerid %>'"><% $eachone.proirety_aslevel %>.主叫变换</span></a></li>

<% elseif $eachone.actmode eq '41' %> 	<li>&nbsp;<img src="../images/icon/11.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.typeof eq 'extension'%>分机<%elseif $eachone.args_array.typeof eq 'queue'%>队列<%elseif $eachone.args_array.typeof eq 'conference'%>会议室<%else%>任意 <%/if%> <% if $eachone.args_array.dialvarname ne ''%>$<% $eachone.args_array.dialvarname %><%else%>'<% $eachone.args_array.dialdigits %>'<%/if%>"><% $eachone.proirety_aslevel %>.拨打号码</span></a></li>

<% elseif $eachone.actmode eq '42' %> 	<li>&nbsp;<img src="../images/icon/pi447.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachone.args_array.dialvarname ne ''%>$<% $eachone.args_array.dialvarname %><%else%>'<% $eachone.args_array.dialdigits %>'<%/if%>"><% $eachone.proirety_aslevel %>.跳转信箱或传真</span></a></li>

<% elseif $eachone.actmode eq '43' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="跳转 '<% $eachone.args_array.gotoivr %>'"><% $eachone.proirety_aslevel %>.跳转到 <i>(<% $eachone.args_array.gotoivr_name %>, <% if $eachone.args_array.actpoint eq ''%><%else%>第<% $eachone.args_array.actpoint_level %>步<%/if%>)</i></span></a></li>

<% elseif $eachone.actmode eq '44' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachone.args_array.urlvar|urldecode %>'"><% $eachone.proirety_aslevel %>.<i>WEB交互 (<% $eachone.args_array.urlvar|urldecode %>)</i></span></a></li>

<% elseif $eachone.actmode eq '45' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg"><% $eachone.proirety_aslevel %>.<i>AGI扩展 (<% $eachone.args_array.agi|urldecode %>)</i></span></a></li>

<% elseif $eachone.actmode eq '80' %> 	<li>&nbsp;<img src='../images/icon/pi237.png'>&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="等待 '<% $eachone.args_array.wait %>' 秒"><% $eachone.proirety_aslevel %>.等待几秒</span></a></li>

<% elseif $eachone.actmode eq '81' %> 	<li>&nbsp;<img src="../images/icon/pi144.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachone.args_array.playtone %>'"><% $eachone.proirety_aslevel %>.播放音调</span></a></li>

<% elseif $eachone.actmode eq '99' %> 	<li>&nbsp;<img src="../images/icon/01.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title=""><% $eachone.proirety_aslevel %>.挂机</span></a></li><%/if%>

<%/foreach%>
	<li>&nbsp;<img src='../images/icon/pi37.png'>&nbsp;<a href="acd_ivrmenu.php?action=page_ivruserinput_list&ivrnumber=<% $ivrmenu.ivrnumber %>" target='_parent'>等待用户输入选择</a></li>
<%foreach from=$ivropt_array item=eachopt key=optkeyname %>
<% if $eachopt.gotoivrnumber_ref.ivrnumber eq $ivrnumber %>
	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<i>(<% $eachopt.input %>) : <% $eachopt.gotoivrnumber_ref.ivrnumber %> - <% $eachopt.gotoivrnumber_ref.ivrname %>, <% if $eachopt.gotoivractid eq ''%><%else%>第<% $eachopt.gotoivractid_level %>步<%/if%></i></li>
<%else%>
		<li class="closed"><span class="folder" func='acd_ivrmenu.php?action=func_ivr_optmenu&ivrnumber=<% $eachopt.gotoivrnumber_ref.ivrnumber %>'>&nbsp;<img src='../images/icon/pi110.png'>&nbsp;<i>(<% $eachopt.input %>) : <% $eachopt.gotoivrnumber_ref.ivrnumber %> - <% $eachopt.gotoivrnumber_ref.ivrname %>, <% if $eachopt.gotoivractid eq ''%><%else%>第<% $eachopt.gotoivractid_level %>步<%/if%></i></span>
			<ul>
	<%foreach from=$eachopt.gotoivrnumber_ivractions_array item=eachl2act key=l2keyname %>
	<% if $eachl2act.actmode eq '10' %> 	<li>&nbsp;<img src='../images/icon/xi66.png'>&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachl2act.args_array.folder %>/<% $eachl2act.args_array.filename %>'"><% $eachl2act.proirety_aslevel %>.播放语音</span></a></li>
	<% elseif $eachl2act.actmode eq '11' %> 	<li>&nbsp;<img src="../images/icon/microphone.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachl2act.args_array.recordvarname %>"><% $eachl2act.proirety_aslevel %>.发起录音</span></a></li>

	<% elseif $eachl2act.actmode eq '12' %> 	<li>&nbsp;<img src="../images/icon/xi66.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachl2act.args_array.playbackvarname %>"><% $eachl2act.proirety_aslevel %>.播放录音</span></a></li>

	<% elseif $eachl2act.actmode eq '20' %> 	<li>&nbsp;<img src="../images/icon/pi401.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="$<% $eachl2act.args_array.receivevarname %>"><% $eachl2act.proirety_aslevel %>.录制0-9字符</span></a></li>

	<% elseif $eachl2act.actmode eq '21' %> 	<li>&nbsp;<img src="../images/icon/19.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.playbackvarname ne ''%>$<% $eachl2act.args_array.playbackvarname %><%else%>'<% $eachl2act.args_array.saydigits %>'<%/if%>"><% $eachl2act.proirety_aslevel %>.读出0-9字符</span></a></li>

	<% elseif $eachl2act.actmode eq '22' %> 	<li>&nbsp;<img src="../images/icon/19-2.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.playbackvarname ne ''%>$<% $eachl2act.args_array.playbackvarname %><%else%>'<% $eachl2act.args_array.saydigits %>'<%/if%>"><% $eachl2act.proirety_aslevel %>.数字方式读出</span></a></li>

	<% elseif $eachl2act.actmode eq '30' %> 	<li>&nbsp;<img src="../images/icon/pi126.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.saydatetime eq 'true'%>当前时间<% elseif $eachl2act.args_array.saydatefromvar ne ''%>日期 $<% $eachl2act.args_array.saydatefromvar %> <% elseif $eachl2act.args_array.saytimefromvar ne ''%>时间 $<% $eachl2act.args_array.saytimefromvar %><% elseif $eachl2act.args_array.saydatestring ne ''%>日期 '<% $eachl2act.args_array.saydatestring %>'<% elseif $eachl2act.args_array.saytimestring ne ''%> 时间 '<% $eachl2act.args_array.saytimestring %>'<%/if%>"><% $eachl2act.proirety_aslevel %>.读出日期时间</span></a></li>

	<% elseif $eachl2act.actmode eq '31' %> 	<li>&nbsp;<img src="../images/icon/pi125.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="匹配跳转 '<% $eachl2act.args_array.gotoivr %>'"><% $eachl2act.proirety_aslevel %>.检测日期 <i>(<% $eachl2act.args_array.gotoivr_name %>, <% if $eachl2act.args_array.actpoint eq ''%><%else%>第<% $eachl2act.args_array.actpoint_level %>步<%/if%>)</i></span></a></li>

	<% elseif $eachl2act.actmode eq '40' %> 	<li>&nbsp;<img src="../images/icon/xa38.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.modify eq 'replace'%>替换<%elseif $eachl2act.args_array.modify eq 'append'%>增补<%elseif $eachl2act.args_array.modify eq '结尾增加'%><%/if%> '<% $eachl2act.args_array.altercallerid %>'"><% $eachl2act.proirety_aslevel %>.主叫变换</span></a></li>

	<% elseif $eachl2act.actmode eq '41' %> 	<li>&nbsp;<img src="../images/icon/11.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.typeof eq 'extension'%>分机<%elseif $eachl2act.args_array.typeof eq 'queue'%>队列<%elseif $eachl2act.args_array.typeof eq 'conference'%>会议室<%else%>任意 <%/if%> <% if $eachl2act.args_array.dialvarname ne ''%>$<% $eachl2act.args_array.dialvarname %><%else%>'<% $eachl2act.args_array.dialdigits %>'<%/if%>"><% $eachl2act.proirety_aslevel %>.拨打号码</span></a></li>

	<% elseif $eachl2act.actmode eq '42' %> 	<li>&nbsp;<img src="../images/icon/pi447.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="<% if $eachl2act.args_array.dialvarname ne ''%>$<% $eachl2act.args_array.dialvarname %><%else%>'<% $eachl2act.args_array.dialdigits %>'<%/if%>"><% $eachl2act.proirety_aslevel %>.跳转信箱或传真</span></a></li>

	<% elseif $eachl2act.actmode eq '43' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="跳转 '<% $eachl2act.args_array.gotoivr %>'"><% $eachl2act.proirety_aslevel %>.跳转到 <i>(<% $eachl2act.args_array.gotoivr_name %>, <% if $eachl2act.args_array.actpoint eq ''%><%else%>第<% $eachl2act.args_array.actpoint_level %>步<%/if%>)</i></span></a></li>

	<% elseif $eachone.actmode eq '44' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachone.args_array.urlvar|urldecode %>'"><% $eachone.proirety_aslevel %>.<i>WEB交互 (<% $eachone.args_array.urlvar|urldecode %>)</i></span></a></li>

	<% elseif $eachone.actmode eq '45' %> 	<li>&nbsp;<img src="../images/icon/43.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachone.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachone.actmode %>&return=treeview"><span class="tipmsg"><% $eachone.proirety_aslevel %>.<i>AGI扩展 (<% $eachone.args_array.agi|urldecode %>)</i></span></a></li>

	<% elseif $eachl2act.actmode eq '80' %> 	<li>&nbsp;<img src='../images/icon/pi237.png'>&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="等待 '<% $eachl2act.args_array.wait %>' 秒"><% $eachl2act.proirety_aslevel %>.等待几秒</span></a></li>

	<% elseif $eachl2act.actmode eq '81' %> 	<li>&nbsp;<img src="../images/icon/pi144.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title="'<% $eachl2act.args_array.playtone %>'"><% $eachl2act.proirety_aslevel %>.播放音调</span></a></li>

	<% elseif $eachl2act.actmode eq '99' %> 	<li>&nbsp;<img src="../images/icon/01.png">&nbsp;<a href='#' class='showpopDialog' func="acd_ivrmenu.php?action=func_ivraction_edit&id=<% $eachl2act.id %>&ivrnumber=<% $ivrnumber %>&actmode=<% $eachl2act.actmode %>&return=treeview"><span class="tipmsg" title=""><% $eachl2act.proirety_aslevel %>.挂机</span></a></li><%/if%>

	<%/foreach%>
	<%foreach from=$eachopt.gotoivrnumber_ivropt_array item=l2eachopt key=l2optkeyname %>
		<li>&nbsp;<i>(<% $l2eachopt.input %>) : 转换到 <% $l2eachopt.gotoivrnumber %></i></li>
	<%/foreach%>
			</ul>
		</li>
<%/if%>
<%/foreach%>
	<li>&nbsp;</li>
</ul>

<% include file="cpanel/func_footer.inc.tpl" %>
