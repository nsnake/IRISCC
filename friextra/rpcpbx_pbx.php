<?php
/*
	Freeiris2 -- An Opensource telephony project.
	Copyright (C) 2005 - 2008, Sun bing.
	Sun bing <hoowa.sun@gmail.com>

	See http://www.freeiris.org for more information about
	the Freeiris project.

	This program is free software, distributed under the terms of
	the GNU General Public License Version 2. See the LICENSE file
	at the top of the source tree.

	Freeiris2 -- 开源通信系统
	本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
	请查阅LICENSE文件。

*/
/* 
FRIPAPER

	$Id$
	@author <link src="mailto:hoowa.sun AT gmail DOT com">Sun bing</link>
	@version 1.0
	@package PBX函数包
	@description
	&nbsp;&nbsp;PBX类函数接口

ENDPAPER
*/
/*
    注册函数
*/
// 热键编辑features
$server->add('features_hotkey_get');
$server->add('features_hotkey_set');
// 计费控制操作
$server->add('billing_var_get'); //(已作废)
$server->add('billing_var_set'); //(已作废)
$server->add('billing_rule_list');
$server->add('billing_rule_add');
$server->add('billing_rule_delete');
$server->add('billing_invoice_list'); //(已作废)

// 语音文件管理接口
$server->add('voicefiles_list');     #获得记录列表
$server->add('voicefiles_get');      #获得某一条记录
$server->add('voicefiles_diskfree'); #磁盘剩余容量
$server->add('voicefiles_getstream');#获得文件数据流
$server->add('voicefiles_add');      #[仅支持sound和moh] 增加一个语音文件
$server->add('voicefiles_edit');     #[仅支持sound和moh] 修改一个文件和记录
$server->add('voicefiles_delete');   #[全部类型] 删除一个文件和记录

/*
    函数内容
*/
/*
FRIPAPER

	@name features_hotkey_get
	@synopsis
		取得功能热键,热键来自asterisk的features.conf中
		<code>	
  $retrun = features_hotkey_get($key)
		</code>
	@param $key
		热键名称
	@return $retrun
		@item  $key : 热键值

ENDPAPER
*/
function features_hotkey_get($key)
{
	global $freeiris_conf;
	global $dbcon;

	//取得配置
	$features_conf = new asteriskconf();

	//#在这里不再是注释符号
	$features_conf->comments_flags = '\;';
if ($features_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/features.conf')==false)
	return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/features.conf',100,null));
	
	//检测是不是基本热键
	if ($features_conf->get('featuremap',$key)) {
		return(rpcreturn(200,null,null,array($key=>$features_conf->get('featuremap',$key))));
	}
	if ($features_conf->get('general',$key)) {
		return(rpcreturn(200,null,null,array($key=>$features_conf->get('general',$key))));
	}
	if ($features_conf->get('applicationmap',$key)) {
		$fullvalues = $features_conf->get('applicationmap',$key);
		$allarray = preg_split("/\,/", $fullvalues);
		return(rpcreturn(200,null,null,array($key=>$allarray[0])));
	}

	return(rpcreturn(200,null,null,null,true));        
}

/*
FRIPAPER

	@name features_hotkey_set
	@synopsis
		设置功能热键,热键来自asterisk的features.conf中
		<code>	
  $retrun = features_hotkey_set($key,$hotkey)
		</code>
	@param $key
		热键名称
	@param $hotkey
		热键值
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function features_hotkey_set($key,$hotkey)
{
	global $freeiris_conf;
	global $dbcon;

	//取得配置
	$features_conf = new asteriskconf();
        //#在这里不再是注释符号
        $features_conf->comments_flags = '\;';        
	if ($features_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/features.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/features.conf',100,null));

        if ($features_conf->get('featuremap',$key)) {
            $features_conf->assign_editkey('featuremap',$key,$hotkey);
            
        } elseif ($features_conf->get('general',$key)) {
            $features_conf->assign_editkey('general',$key,$hotkey);
            
        } elseif ($features_conf->get('applicationmap',$key)) {
            $fullvalues = $features_conf->get('applicationmap',$key);
            $allarray = preg_split("/\,/", $fullvalues);
            $newvalue = preg_replace('/^'.preg_quote($allarray[0], '\\').'/', $hotkey, $fullvalues);
            $features_conf->assign_editkey('applicationmap',$key,$newvalue);            
        }
	
	//如果执行成功
	$features_conf->save_file();

        return(rpcreturn(200,null,null,null,true));
}


/*
FRIPAPER

	@name billing_var_get
	@synopsis
		取得billing的启动配置变量(已作废,已改变)
		<code>	
  $retrun = billing_var_get($key)
		</code>
	@param $key
		变量键名称
	@param $hotkey
		键值
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function billing_var_get($key)
{
	return(rpcreturn(500,'function billing_var_get was deprecated and removed!',109,null));
//
//	global $freeiris_conf;
//	global $dbcon;
//
//	//取得配置
//	$fri2d_conf = new asteriskconf();
//	if ($fri2d_conf->parse_in_file($freeiris_conf->get('general','freeiris_root').'/etc/fri2d.conf')==false)
//		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','freeiris_root').'/etc/fri2d.conf',100,null));
//        
//        //获得参数
//        if ($fri2d_conf->get('fri2bill',$key)) {
//            return(rpcreturn(200,null,null,array($key=>$fri2d_conf->get('fri2bill',$key))));
//        }
//        
//        //featuremap
//        return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name billing_var_set
	@synopsis
		取得billing的启动配置变量(已作废)
		<code>	
  $retrun = billing_var_set($key,$value)
		</code>
	@param $key
		变量键名称
	@return $retrun
		@item  $key : 键值

ENDPAPER
*/
function billing_var_set($key,$value)
{
	return(rpcreturn(500,'function billing_var_set was deprecated and removed!',109,null));
//
//	global $freeiris_conf;
//	global $dbcon;
//
//	//取得配置
//	$fri2d_conf = new asteriskconf();
//	if ($fri2d_conf->parse_in_file($freeiris_conf->get('general','freeiris_root').'/etc/fri2d.conf')==false)
//		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','freeiris_root').'/etc/fri2d.conf',100,null));
//        
//        //执行修改
//        $fri2d_conf->assign_editkey('fri2bill',$key,$value);
//	
//	//如果执行成功
//	$fri2d_conf->save_file();
//
//        return(rpcreturn(200,null,null,null,true));
}

/*
FRIPAPER

	@name billing_rule_list
	@synopsis
		列表计费规则
		<code>	
  $retrun = billing_rule_list()
		</code>
	@return $retrun
		@item  array 'rules' : 规则列表结构

ENDPAPER
*/
function billing_rule_list()
{
	global $freeiris_conf;
	global $dbcon;
	

	//执行sql
	$result_array=array();
	$result=mysql_query("select * from billingrate order by dst_prefix desc");
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('rules'=>$result_array)));
}

/*
FRIPAPER

	@name billing_rule_add
	@synopsis
		新增一条计费规则
		<code>	
  $retrun = billing_rule_add($ruledata)
		</code>
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function billing_rule_add($ruledata)
{
    	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------raterule created
	$sql = "insert into billingrate set ".
			"destnation='".$ruledata['destnation']."',".
			"dst_prefix='".$ruledata['dst_prefix']."',".
			"persecond='".$ruledata['persecond']."',".
			"percost='".$ruledata['percost']."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name billing_rule_delete
	@synopsis
		删除billing的计费规则
		<code>	
  $retrun = billing_rule_delete($id)
		</code>
	@param $id
		规则编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function billing_rule_delete($id)
{
	global $freeiris_conf;
	global $dbcon;
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from billingrate where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name billing_invoice_list
	@synopsis
		取得计费结果话单(已作废)
		<code>	
  $retrun = billing_invoice_list($startdate,$enddate,$accountcode)
		</code>
	@param $startdate
		开始时间
	@param $enddate
		结束时间
	@param $accountcode
		帐号
	@return $retrun
		@item  array 'invoice' : 计费结果话单

ENDPAPER
*/
function billing_invoice_list($startdate,$enddate,$accountcode)
{
	return(rpcreturn(500,'function billing_var_set was deprecated and removed!',109,null));
//
//	global $freeiris_conf;
//	global $dbcon;
//        
//        $wheresql=null;
//        
//        if ($startdate != "" && $enddate != "") {
//            $wheresql = "where cretime >= '".$startdate."' and cretime <= '".$enddate."' and ";
//        }
//        if ($accountcode != "") {
//            if ($wheresql == "") $wheresql = "where ";
//            $wheresql = $wheresql."accountcode = '".$accountcode."' and ";
//        }
//        $wheresql = rtrim($wheresql,"and ");
//        
//	//执行sql
//	$result_array=array();
//	$result=mysql_query("select * from billinginvoice $wheresql order by cretime desc");
//	if (!$result)
//		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
//	while ($each = mysql_fetch_array($result))
//	{
//	    array_push($result_array,$each);
//	}
//	mysql_free_result($result);
//        
//                
//    return(rpcreturn(200,null,null,array('invoice'=>$result_array)));
}

/*
FRIPAPER

	@name voicefiles_list
	@synopsis
		语音文件列表
		<code>	
  $retrun = voicefiles_list($label=null,$limitfrom=null,$limitoffset=null,$folder=null)
		</code>
	@param $label
		可以为空,语音文件标签,可以填写'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
	@param $limitfrom
		可以为空,开始数据
	@param $limitoffset
		可以为空,数据量
	@param $folder
		可以为空,目录
	@return $retrun
		@item  array 'resdata' : 语音文件列表

ENDPAPER
*/
function voicefiles_list($label=null,$limitfrom=null,$limitoffset=null,$folder=null)
{
	global $freeiris_conf;
	global $dbcon;
	
	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	//分类选择
	if (trim($label) != '') {
		$sql .= "and label = '".$label."'";
	}
	if (trim($folder) != '') {
		$sql .= "and folder = '".$folder."'";
	}
	$sql = ltrim($sql,'and ');
	if (trim($sql) != '') {
		$sql = "select * from voicefiles where ".$sql." order by cretime desc";
	} else {
		$sql = 'select * from voicefiles order by cretime desc';
	}
	//offset
	if (trim($limitfrom) != '' && trim($limitoffset) != '') {
		$sql .= " limit ".$limitfrom.",".$limitoffset;
	}

	//执行sql
	$result_array=array();
	$result=mysql_query($sql);
	if (!$result)
		return(array('response'=>array('statcode'=>500,'message'=>mysql_error())));
	while ($each = mysql_fetch_array($result))
	{
		//取出这个文件的文件尺寸,不同类型

		if ($label == 'sound') {
			$filepath = $asterisk_conf->get('directories','astvarlibdir').'/sounds/'.$each['associate'].'/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'];
			$each['filesize']=filesize($filepath);

		} elseif ($label == 'moh') {
			$filepath = $asterisk_conf->get('directories','astvarlibdir').'/moh/'.$each['filename'].'.'.$each['extname'];
			$each['filesize']=filesize($filepath);

		} elseif ($label == 'voicemail') {

			$filestat=preg_split("/\_/",$each['filename']);
			$each['filesize']=round(filesize($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'])/1024);
			$each['caller']=$filestat[3];

		} elseif ($label == 'onetouch') {

			$filestat=preg_split("/\_/",$each['filename']);
			$each['filesize']=round(filesize($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'])/1024);
			$each['callee']=$filestat[3];

			//取得cdr
			$cdrresult=mysql_query("select * from cdr where userfield = '".$filestat[1]."'");
			if (!$cdrresult)
				return(rpcreturn(500,mysql_error(),100,null));
			$cdr = mysql_fetch_array($cdrresult);
			mysql_free_result($cdrresult);
			$each['cdr']=$cdr;

		} elseif ($label == 'sysautomon') {

			if (file_exists($asterisk_conf->get('directories','astspooldir').'/monitor/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'])) {
				$each['filesize']=round(filesize($asterisk_conf->get('directories','astspooldir').'/monitor/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'])/1024);
			} else {
				mysql_query("delete from voicefiles where id = '".$each['id']."'");
				continue;
			}

		} elseif ($label == 'ivrmenu') {

			$filestat=preg_split("/\_/",$each['filename']);
			$each['filesize']=round(filesize($asterisk_conf->get('directories','astspooldir').'/ivrmenu/'.$each['folder'].'/'.$each['filename'].'.'.$each['extname'])/1024);

		}
	    array_push($result_array,$each);
	}
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$result_array)));
}

/*
FRIPAPER

	@name voicefiles_diskfree
	@synopsis
		取得指定磁盘的容量
		<code>	
  $retrun = voicefiles_diskfree($label)
		</code>
	@param $label
		 可选'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
	@return $retrun
		@item string 'diskfree' : 磁盘剩余空间
		@item string 'disktotal' : 磁盘总容量

ENDPAPER
*/
function voicefiles_diskfree($label)
{
	global $freeiris_conf;
	global $dbcon;

	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));
                
    if ($label == 'sound') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astvarlibdir').'/sounds');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astvarlibdir').'/sounds');            

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    } elseif ($label == 'moh') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astvarlibdir').'/moh');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astvarlibdir').'/moh');                        

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    } elseif ($label == 'voicemail') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/');               

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    } elseif ($label == 'onetouch') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/');               

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    } elseif ($label == 'ivrmenu') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astspooldir').'/ivrmenu');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astspooldir').'/ivrmenu');

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    } elseif ($label == 'sysautomon') {
        $disktotal = disk_total_space($asterisk_conf->get('directories','astspooldir').'/monitor');
        $diskfree = disk_free_space($asterisk_conf->get('directories','astspooldir').'/monitor');

		return(rpcreturn(200,null,null,array('diskfree'=>$diskfree,'disktotal'=>$disktotal)));

    }


	return(rpcreturn(200,null,null,null));
}

/*
FRIPAPER

	@name voicefiles_get
	@synopsis
		取得指定语音文件信息
		<code>	
  $retrun = voicefiles_get($id=null,$label=null,$filename=null,$folder=null)
		</code>
	@param $id
		可以为空,语音文件编号
	@param $label
		可以为空,语音文件标签,可以填写'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
	@param $filename
		可以为空,文件名称
	@param $folder
		可以为空,目录
	@return $retrun
		@item  array 'resdata' : 数据信息结构

ENDPAPER
*/
function voicefiles_get($id=null,$label=null,$filename=null,$folder=null)
{
	global $freeiris_conf;
	global $dbcon;
	
	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));
                
	//执行sql
	if (trim($id) != '') {
		$result=mysql_query("select * from voicefiles where id = '".$id."'");

	} elseif (trim($label) != '' && trim($filename) != '' && trim($folder) != '') {
		$result=mysql_query("select * from voicefiles where filename = '".$filename."' and label = '".$label."' and folder = '".$folder."'");

	} elseif (trim($label) != '' && trim($filename) != '') {
		$result=mysql_query("select * from voicefiles where filename = '".$filename."' and label = '".$label."'");

	}
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);

	return(rpcreturn(200,null,null,array('resdata'=>$resdata)));    
}

/*
FRIPAPER

	@name voicefiles_add
	@synopsis
		增加语音文件(数据记录)
		<code>	
  $retrun = voicefiles_add($label,$newrecord)
		</code>
	@param $label
		可选'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
	@param $newrecord
		语音文件数据部分
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function voicefiles_add($label,$newrecord)
{
   	global $freeiris_conf;
	global $dbcon;

	//------------------------------------------------------raterule created
	$sql = "insert into voicefiles set ".
			"filename='".$newrecord['filename']."',".
			"extname='".$newrecord['extname']."',".                        
			"folder='".$newrecord['folder']."',".
			"cretime=now(),".
			"description='".$newrecord['description']."',".
			"label='".$label."',".
			"associate='".$newrecord['associate']."',".
			"args='".$newrecord['args']."',".
			"readonly='".$newrecord['readonly']."'";

	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
 
	//如果执行成功
	return(rpcreturn(200,null,null,null,null));
}

/*
FRIPAPER

	@name voicefiles_edit
	@synopsis
		编辑语音文件(包括在线语音上传)
		<code>	
  $retrun = voicefiles_edit($label,$id,$smdata)
		</code>
	@param $label
		可选'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
	@param $id
		语音文件编号
	@param $smdata
		语音文件数据结构
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function voicefiles_edit($label,$id,$smdata)
{
	global $freeiris_conf;
	global $dbcon;

	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	//本函数只支持sound和moh
	if ($label != 'sound' && $label != 'moh') {
		return(rpcreturn(200,null,null,null,null));
	}

	//检查是否上载了新文件,如果是就覆盖掉旧文件并且更新数据库相关资料
	if ($smdata['filestream'] != null) {
		
		//找到旧文件
		$result=mysql_query("select * from voicefiles where id = '".$id."'");
		if (!$result)
				return(rpcreturn(500,mysql_error(),100,null));
		$resdata = mysql_fetch_array($result);
		mysql_free_result($result);
		if (!$resdata)
			return(rpcreturn(500,"can't find file",113,null));
		//
		if ($resdata['label'] == 'sound') {
			$filepath = $asterisk_conf->get('directories','astvarlibdir').'/sounds/'.$resdata['associate'].'/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];
			//删除掉旧文件
			unlink($filepath);
			//存储新文件
			file_put_contents($asterisk_conf->get('directories','astvarlibdir').'/sounds/'.$resdata['associate'].'/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$smdata['extname'],$smdata['filestream']);

		//更新moh
		} else {
			$filepath = $asterisk_conf->get('directories','astvarlibdir').'/moh/'.$resdata['filename'].'.'.$resdata['extname'];
			//删除掉旧文件
			unlink($filepath);
			//存储新文件
			file_put_contents($asterisk_conf->get('directories','astvarlibdir').'/moh/'.$resdata['filename'].'.'.$smdata['extname'],$smdata['filestream']);
		}

		//更新文件名称
		$sql = "update voicefiles set extname='".$smdata['extname']."' where id = '".$id."'";
		$result=mysql_query($sql);
		if (!$result)
				return(rpcreturn(500,mysql_error(),100,null));            
	}

	//------------------------------------------------------更新一般数据库资料
	$sql = "update voicefiles set description='".$smdata['description']."' where id = '".$id."'";
	$result=mysql_query($sql);
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	//结束
	if ($label == 'sound') {

		return(rpcreturn(200,null,null,null,null));

	} elseif ($label == 'moh') {

		return(rpcreturn(200,null,null,null,true));

	}
}

/*
FRIPAPER

	@name voicefiles_getstream
	@synopsis
		取得指定语音文件数据流
		<code>	
  $retrun = voicefiles_getstream($id)
		</code>
	@param $id
		语音文件编号
	@return $retrun
		@item  binarystring 'filestream' : 语音二进制数据
		@item  string 'filename' : 文件名称
		@item  string 'extname' : 文件扩展名

ENDPAPER
*/
function voicefiles_getstream($id)
{
	global $freeiris_conf;
	global $dbcon;
        
	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	//执行sql
	$result=mysql_query("select * from voicefiles where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
	    return(rpcreturn(500,"can't find file",113,null));

    if ($resdata['label'] == 'sound') {
		$filepath = $asterisk_conf->get('directories','astvarlibdir').'/sounds/'.$resdata['associate'].'/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];            

    } elseif ($resdata['label'] == 'moh') {
		$filepath = $asterisk_conf->get('directories','astvarlibdir').'/moh/'.$resdata['filename'].'.'.$resdata['extname'];

    } elseif ($resdata['label'] == 'voicemail') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

    } elseif ($resdata['label'] == 'onetouch') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

    } elseif ($resdata['label'] == 'sysautomon') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/monitor/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

    } elseif ($resdata['label'] == 'ivrmenu') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/ivrmenu/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	}
        
    //打开文件body
    if (file_exists($filepath)) {
	    $filestream = file_get_contents($filepath);
    }

	return(rpcreturn(200,null,null,array('filestream'=>$filestream,'filename'=>$resdata['filename'],'extname'=>$resdata['extname'])));
}

/*
FRIPAPER

	@name voicefiles_delete
	@synopsis
		删除语音文件
		<code>	
  $retrun = voicefiles_delete($id)
		</code>
	@param $id
		语音文件编号
	@return $retrun
		fri2标准返回

ENDPAPER
*/
function voicefiles_delete($id)
{
    global $freeiris_conf;
	global $dbcon;

	//取得主配置
	$asterisk_conf = new asteriskconf();     
	if ($asterisk_conf->parse_in_file($freeiris_conf->get('general','asterisketc').'/asterisk.conf')==false)
		return(rpcreturn(500,"can't open ".$freeiris_conf->get('general','asterisketc').'/asterisk.conf',100,null));

	$result=mysql_query("select * from voicefiles where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));
	$resdata = mysql_fetch_array($result);
	mysql_free_result($result);
	if (!$resdata)
		return(rpcreturn(500,"can't find file",113,null));

	if ($resdata['label'] == 'sound') {
		$filepath = $asterisk_conf->get('directories','astvarlibdir').'/sounds/'.$resdata['associate'].'/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	} elseif ($resdata['label'] == 'moh') {
		$filepath = $asterisk_conf->get('directories','astvarlibdir').'/moh/'.$resdata['filename'].'.'.$resdata['extname'];

	} elseif ($resdata['label'] == 'voicemail') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	} elseif ($resdata['label'] == 'onetouch') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/voicemail/freeiris/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	} elseif ($resdata['label'] == 'sysautomon') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/monitor/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	} elseif ($resdata['label'] == 'ivrmenu') {
        $filepath = $asterisk_conf->get('directories','astspooldir').'/ivrmenu/'.$resdata['folder'].'/'.$resdata['filename'].'.'.$resdata['extname'];

	}
	
	//删除掉旧文件
	unlink($filepath);
	
	//------------------------------------------------------删除
	$result=mysql_query("delete from voicefiles where id = '".$id."'");
	if (!$result)
		return(rpcreturn(500,mysql_error(),100,null));

	return(rpcreturn(200,null,null,null,null));
}

?>