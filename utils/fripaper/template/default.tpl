<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <TITLE> { $package } </TITLE>
  <link rel="stylesheet" title="" type="text/css" href="style.css">
 </HEAD>

<BODY>
<div align="center">
<H1>{ $package }</H1>
<p>&nbsp;</p>
</div>

<div align="left">
<p><b>作者</b> : { $author }<br>
<b>版本</b> : { $version }<br>
<b>文件</b> : { $filename }<br>
<b>说明 : </b><br>
{ $description }<br>
<b>函数</b> :
<UL>
{ foreach from=$menu item=eachone key=keyname }
<LI><a href="#{$eachone.name}">{$eachone.name}</a>
{ /foreach }
</UL>

{ foreach from=$menu item=eachone key=keyname }
<p>&nbsp;</p>
<h2><a name="{$eachone.name}">{$eachone.name}</a></h2>
<p><b>{$eachone.synopsis}</b></p>

 <p><a>参数: </a><br>
{ foreach from=$eachone.param item=subeach key=subkey }
 <b>{$subeach.name}</b><br>
	{ foreach from=$subeach.item item=item_name key=subkey }
	<li>{$item_name}</li>
	{ /foreach }
{ /foreach }</p>
<p>
{ foreach from=$eachone.return item=subeach key=subkey }
 <a>返回数据:</a> <b> {$subeach.name} </b>
	{ foreach from=$subeach.item item=item_name key=subkey }
	 <li>{$item_name}</li>
	{ /foreach }
{ /foreach }</p>
{ /foreach }
<p>&nbsp;</p>
<p>&nbsp;</p>
</div>
</BODY>
</HTML>
