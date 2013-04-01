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
 <p><b>作者</b> : { $author }</p>
 <p><b>版本</b> : { $version }</p>
 <p><b>文件</b> : { $filename }</p>
 <p><b>菜单</b> :</p>
<UL>
{ foreach from=$menu item=eachone key=keyname }
	<LI><a href="#{$eachone.name}">{$eachone.name}</a>
{ /foreach }
</UL>
 <p>说明 : </p>
 { $description }
 <p>
{ foreach from=$funclist item=eachone key=keyname }
 <p>&nbsp;</p>
 <p>&nbsp;</p>
 <h2><a name="{$eachone.name}">{$eachone.name}</a></h2>
 <p>{$eachone.synopsis}</p>

{ foreach from=$eachone.param item=subeach key=subkey }
 <p><a>参数: <b>{$subeach.param_name}</b></a></p>
 <p>{$subeach.param_body}</p>
{ /foreach }

 <p><a>返回数据:<b> {$eachone.return}</b></a> </p>
 <p>{$eachone.return_body}</p>

{ /foreach }
</p>
 <p>&nbsp;</p>
 <p>&nbsp;</p>
 </div>
 </BODY>
</HTML>