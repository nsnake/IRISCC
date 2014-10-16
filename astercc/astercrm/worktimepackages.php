<?php
/*******************************************************************************
* worktime.php

* 账户组管理界面文件
* worktimepackages management interface

* Function Desc
	provide an worktimepackages management interface

* 功能描述
	提供账户组管理界面

* Page elements

* div:							
				divNav				show management function list
				formDiv				show add/edit account form
				grid				show accout grid
				msgZone				show action result
				divCopyright		show copyright

* javascript function:		

				init				page onload function			 


* Revision 0.045  2007/10/18 11:44:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once('worktimepackages.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--

			function init(){
				xajax_init();
				dragresize.apply(document);
			}

			function searchFormSubmit(numRows,limit,id,type){
				//alert(xajax.getFormValues("searchForm"));
				xajax_searchFormSubmit(xajax.getFormValues("searchForm"),numRows,limit,id,type);
				return false;
			}

		//-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
		<style type="text/css">
			*{ padding:0; margin:0; font-size:12px;}
			.worktimeSltDiv{ width:300px; margin:20px auto;}
			#formTable{ border-collapse:collapse;}
			#formTable th,td{ border:#999999 0px solid; padding:1px; vertical-align:top;}
			#formTable th{ background-color:#8FA7ED}
			#formTable td{ background-color:#E8E8E8}
			#worktimeAllDiv{display:block; border:#666666 1px solid; width:300px; height:100px; overflow-y:scroll; list-style:none;padding:2px; background-color:#FFFFFF;}
			#worktimeAllDiv a{ display:block; height:20px; line-height:20px; width:100%; color:#000000; text-decoration:none;}
			#worktimeAllDiv a:hover{ background-color:#003399; color:#FFFFFF; cursor:pointer;}
			#worktimeAllDiv a.selected{background-color:#003399; color:#FFFFFF;}
			#worktimeSltdDiv{width:300px; height:80px; border:#333333 1px solid; background-color:#FFFFFF; overflow-y:scroll;}
			#worktimeSltdDiv span{overflow:hidden; display:block; float:left; margin:2px 0px;}
			#worktimeSltdDiv span a{ color:#FF0000; cursor:pointer; margin-left:0px; float:right;text-decoration:none;}
			#worktimeSltdDiv span a:hover{ background-color:#0066FF;}
		</style>
		<script type="text/javascript">
		//全局变量，保存被选中id
		var c_id = new Array();
		//全局变量，保持被选中名称
		var c_content = new Array();
		
		//每次点击add时,清空id和content
		function resetC(){
			c_id = new Array();
			c_content = new Array();
		}

		//选项单击事件，_id 选项的唯一id标识 _content 选项的文字表示
		function mf_click(_id, _name)
		{
		if (_id != '' && _name != '')
		{
		   var is_clicked = false;
		   for(var i=0; i<c_id.length; i++)
		   {
			if (c_id[i]==_id)
			{
			 is_clicked = true;
			 c_id.splice(i, 1);
			 c_content.splice(i, 1);
			 xajax.$('op_'+_id).className = '';
			}
		   }
		  
		   if (!is_clicked)
		   {
			c_id.push(_id);
			c_content.push(_name);
			xajax.$('op_'+_id).className = 'selected';
			//xajax.$('sltedWorktimes').value = xajax.$('sltedWorktimes').value + xajax.$('worktimeVal_'+_id).value+',';
			//alert(xajax.$('worktimeVal_'+_id).value);
			//alert(xajax.$('sltedWorktimes').value);
		   }
		   mf_view();
		}
		}

		//更新下方被选中试图，每次单击或删除事件后自动调用
		function mf_view()
		{
		var elm = xajax.$('worktimeSltdDiv');
		var html = '';
		var sltedWorktimes = '';
		for(var i=0; i<c_id.length; i++)
		{
			sltedWorktimes =  sltedWorktimes + xajax.$('worktimeVal_'+c_id[i]).value+',';
		   html +='<span><a href="javascript:return false;" onclick="mf_delete('+ c_id[i] +');">'+c_content[i]+'</a></span>';
		}
		xajax.$('sltedWorktimes').value = sltedWorktimes;
		elm.innerHTML = html;
		}

		//下方选中视图中删除事件
		function mf_delete(_id)
		{
		for(var i=0; i<c_id.length; i++)
		{
		   if (c_id[i]==_id)
		   {
			c_id.splice(i, 1);
			c_content.splice(i, 1);
			xajax.$('op_'+_id).className = '';
		   }
		}
		mf_view();
		}
		</script>
	</head>
	<body onload="init();">
		<div id="divNav"></div>
	<div id="divActive" name="divActive">
		<input type="button" value="" id="btnDial" name="btnDial" onClick="window.location='diallist.php';" />
		<input type="button" value="" id="btnDialed" name="btnDialed" onClick="window.location='dialedlist.php';" />
		<input type="button" value="<?php echo $locate->Translate("Campaign")?>" id="btnCampaign" name="btnCampaign" onClick="window.location='campaign.php';" />
		<input type="button" value="<?php echo $locate->Translate("Work time")?>" id="btnWorktime" name="btnWorktime" onClick="window.location='worktime.php';" />
	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
		<div id="formDiv"  class="formDiv drsElement" 
			style="left: 400px; top: 50px;width:580px;"></div>
		<div id="grid" name="grid" align="center"> </div>
		<div id="msgZone" name="msgZone" align="left"> </div>
				</fieldset>
			</td>
		</tr>
	</table>
	<form name="exportForm" id="exportForm" action="dataexport.php" >
		<input type="hidden" value="" id="hidSql" name="hidSql" />
	</form>

		<div id="divCopyright"></div>
	</body>
</html>
