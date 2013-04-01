	<div id="body-menu">
		<div id="mainmenu" class="ddsmoothmenu">
		<ul>
<%foreach from=$menutable item=eachcate %>
		<li><a href="#"><% $eachcate.category %></a>
		  <ul>
<%foreach from=$eachcate.submenu item=eachsub %>
			<li><a href="<% $eachsub.url %>"><img src="../images/right.gif" border="0"> <% $eachsub.name %></a></li>
<%/foreach%>
		  </ul> 
 		</li>
<%/foreach%>
		</ul>
		</div>
	</div>