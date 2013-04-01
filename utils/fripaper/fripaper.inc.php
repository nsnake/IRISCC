<?php
/***************************************************************************
fripaper.inc.php: remark to html module core
FRIPAPER
@description
	this module can read remark from program files in src directory, 
	and create help files use html language in in dst directory
@version 2009-06-22 1.0.1
@author freeiris-安静 <yuzegao@163.com>
@package Freeiris2 Package Fripaper
Please submit bug reports, patches, etc to <yuzegao@163.com>
www.voxen.cn

ENDPAPER
See http://www.freeiris.org for more information about
the Freeiris project.
Freeiris2 -- 开源通信系统
本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
请查阅LICENSE文件。

***************************************************************************/
define( 'FRIPAPER_PATH', dirname(__FILE__));
define('DEFAULT_CONFIG', FRIPAPER_PATH.'/lib/config.conf');

class fripaper{

	var $smarty=false;			//the instance of smarty class
	var $config;				//config array
	var $source;				//resouce file dir
	var $output;				//output file dir
	var $template;				//dufault smarty template file
	var $file_array=array();		//需要转换的文件数组
	function fripaper()			//构造数字,配置文件初始化,实例化smarty
	{
		$this->load_conf();
		$this->instance_smarty();
		
	}
/*********************************************************************************************
	加载配置文件
*********************************************************************************************/
	function load_conf() // load the config file, default config file is ./lib/config.conf
	{
		if(file_exists(DEFAULT_CONFIG))//默认的配置文件
		{
			$this->config = parse_ini_file(DEFAULT_CONFIG, true);
		}
		else
		{
			echo "error: config file is not in ".FRIPAPER_PATH."/lib/config.conf, please put config file in ".FRIPAPER_PATH."/lib/ \n";
			exit;
		}
	}
/*********************************************************************************************************
	初始化Smarty 函数
*********************************************************************************************************/

	function instance_smarty()// instance smarty calss 
	{
		if(substr($this->config['smarty']['smarty_dir'],0,1)=="/")
			define( 'SMARTY_DIR', $this->config['smarty']['smarty_dir']);
		else
			define( 'SMARTY_DIR', FRIPAPER_PATH."/".$this->config['smarty']['smarty_dir']);
		if(!is_dir(SMARTY_DIR))
   				die("[ERROR]: The Smarty path ".SMARTY_DIR."not exist! Please check config.conf\n");
		if(substr($this->config['smarty']['template_dir'],0,1)=="/")
			define( 'TEMPLATE_DIR', $this->config['smarty']['template_dir']);
		else
			define( 'TEMPLATE_DIR', FRIPAPER_PATH."/".$this->config['smarty']['template_dir']);
		if(!is_dir(TEMPLATE_DIR))
   				die("[ERROR]: The Smarty  template path ".TEMPLATE_DIR."not exist! Please check config.conf\n");

		if(substr($this->config['smarty']['template_c_dir'],0,1)=="/")
			define( 'TEMPLATE_C_DIR', $this->config['smarty']['template_c_dir']);
		else
			define( 'TEMPLATE_C_DIR', FRIPAPER_PATH."/".$this->config['smarty']['template_c_dir']);

		if(!is_dir(TEMPLATE_C_DIR))
   				die("[ERROR]: The Smarty compile path ".TEMPLATE_C_DIR."not exist! Please check config.conf\n");
		@system("chmod 777 ".TEMPLATE_C_DIR);
		require_once SMARTY_DIR . 'Smarty.class.php';
		$this->smarty = new Smarty;
		$this->smarty->template_dir = TEMPLATE_DIR ;
		$this->smarty->compile_dir = TEMPLATE_C_DIR;
	}

/*********************************************************************************************************
	初始化函数 对参数进行判断 获取目录信息 return $init
*********************************************************************************************************/

	function init() //初始化函数 对参数进行判断 获取目录信息 return $init
	{
		$this->template=TEMPLATE_DIR.$this->config['global']['default_template']; //默认的template file
		$init=0;// if init =1 init success,if init=0 init fail

		if($_SERVER['argc']>2 && $_SERVER['argc']<=5)//参数至少三个 至多五个
		{
				if($_SERVER['argv'][1]=='-t' || $_SERVER['argv'][1]=='-T')//如果第二个参数是-t
				{	
					if(file_exists(TEMPLATE_DIR.$_SERVER['argv'][2]) && !is_dir(TEMPLATE_DIR.$_SERVER['argv'][2]) )//判断第三个参数是否存在并且是文件
					{
						if(substr($_SERVER['argv'][3],0,1)=="/")
							$this->source=$_SERVER['argv'][3];
						else
							$this->source=FRIPAPER_PATH."/".$_SERVER['argv'][3];
						if(substr($_SERVER['argv'][4],0,1)=="/")
							$this->output=$_SERVER['argv'][4];
						else
							$this->output=FRIPAPER_PATH."/".$_SERVER['argv'][4];
						if(isset($_SERVER['argv'][3]) && isset($_SERVER['argv'][4]) && is_dir($this->source) && is_dir($this->output))//判断第四个参数和第五个参数都是目录
						{
							$this->template=TEMPLATE_DIR.$_SERVER['argv'][2];
							$init=1;
						}
						else{
							echo "[ERROR]: ".$this->source." or ".$this->output." not exist!\n";
							$init=0;}
					}
					else{	
						echo "[ERROR]: ".TEMPLATE_DIR.$_SERVER['argv'][2]." not exist!\n";
						$init=0;
					}	
				}else
				{
				   if(substr($_SERVER['argv'][1],0,1)=="/")
                                            $this->source=$_SERVER['argv'][1];
                                    else
                                            $this->source=FRIPAPER_PATH."/".$_SERVER['argv'][1];
                                   if(substr($_SERVER['argv'][2],0,1)=="/")
                                            $this->output=$_SERVER['argv'][2];
                                    else
                                            $this->output=FRIPAPER_PATH."/".$_SERVER['argv'][2];
				    if(!is_null($_SERVER['argv'][1]) && !is_null($_SERVER['argv'][2]) && is_dir($this->source) && is_dir($this->output))//判断第一个参数和第二个参数都是目录
				    {
					$this->template=TEMPLATE_DIR.$this->config['global']['default_template'];
					$init=1;
				    }else{
					echo "[ERROR]: ".$this->source." or ".$this->output." not exist!\n";
					$init=0;}
				   if(!file_exists($this->template) || is_dir($this->template))//判断默认的模板是否存在
				   {
					echo "[ERROR]: The default template file ".$this->template." not exist!\n";
                                        $init=0;
				   }
				}		
		}
		else{
				echo "[ERROR]: parameters are more than four or less than two\n";
				$init =0;
			}
		if(!$init)
		{
				echo "Parameter Error \n";
				echo"/***********************************************************************************************************************/
How to use this	command:
#./fripaper [-t template.tpl] /source /output
Description:
-t template.tpl: specify the template file use to create html file,optional parameters.default is default.tpl in smarty template dir
/source: specify the source files path
/output: specify the output files path
Notice:
please put template.tpl file in smarty template dir that defined in config.conf,source path and output path can use absolute address
or relative address,relative address in fripaper directory.
Exam: 
#./fripaper -t temp.tpl /var/www/html/from /var/www/html/to
#./fripaper /src /dst
#./fripaper -t temp.tpl src out
#./fripaper src out
/**************************************************************************************************************************/\n";
		}
		return $init;
	}
/**********************************************************************************
分析目录
**********************************************************************************/
	function parse_dir()
	{
		$fp=opendir($this->source);
		while( ($filename = readdir($fp)) !== false )
		{
			if(substr($filename,-3)=='php' || substr($filename,-3)=='PHP')
			{	$this->file_array[]=$filename;}
		}
		if(count($this->file_array)<1)
		{
			echo "[ERROR]:There are no php files in $this->source dir\n";
			exit;
		}
	}
	
/**********************************************************************************
	分析文件
**********************************************************************************/
	function parse_file()
	{	
		for($i=0;$i<count($this->file_array);$i++)		// 对每一个文件进行分析
		{
			$content=file($this->source."/".$this->file_array[$i]);		//打开文件
			$menu=array();							//存储当前文件当中所有函数名
			$last_data="";//用于存储文件最后一个参数
			for($j=0;$j<count($content);$j++)		//分析文件当中每一行
			{
				
				if(strlen(strstr(trim($content[$j]),"/*"))>0)		//如果找到/*
				{
//echo __FILE__.$content[$j]."\n";
					for(;$j<count($content);$j++)//分析 /*以后的内容
					{
						if(strstr(trim($content[$j]),"FRIPAPER")=='FRIPAPER')//如果在/*后找到FRIPAPER
						{
							$j++;
							for(;$j<count($content);$j++)
							{	//echo $content[$j]."\n";
								//echo strpos($content[$j],'@')."\n";
								if(!(strpos($content[$j],'@')===false))//如果找到了@,那后面的信息就是可用的
								{
									//echo $content[$j]."\n";
									$data=substr($content[$j],strpos($content[$j],'@')+1,strpos(trim($content[$j]),' ')-1);						    //取出参数名称
									//echo $data."\n";
									switch ($data){
									case "author":
										$author=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);
										if(strcasecmp($author,"author")==0)
											$author="";
										break;
									case "version":
			                                                        $version=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($version,"version")==0)
											$version="";
                                                                                break;
									case "package":
			                                                        $package=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($package,"package")==0)
											$package="";
                                                                                break;
									case "description":
			                                                        $description=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($description,"description")==0)
											$description="";
                                                                        	$temp="";//存储@下一行到下一个@上一行之间的内容
                                                                        	for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                        	{
                                                                            	if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                            	{
                                                                            	    $temp =$this->parse_function($temp);
											if(strlen(trim($temp))>0)
                                                                                	$description.=$temp;//把说明结合起来
                                                                        	        break;
                                                                        	    }
                                                                        	    $temp.=$content[$j+1];
                                                                        	}
                                                                                break;
									case "name":
			                                                        $name=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;//得到当前函数的名字
										if(strcasecmp($name,"name")==0)
											$name="";
										$menu["$name"]['name']=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;//以当前函数的名字创建一个数组
                                                                        	$temp="";//存储@下一行到下一个@上一行之间的内容
                                                                        	for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                        	{
                                                                            	if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                            	{
                                                                            	    $temp =$this->parse_function($temp);
                                                                            	    if(strlen(trim($temp))>0)
                                                                                        $menu["$name"]['name'].=$temp;//把说明结合起来
										  break;
                                                                            	}
                                                                            	$temp.=$content[$j+1];
                                                                        	}
                                                                                break;
									case "synopsis":
			                                                        $menu["$name"]['synopsis']=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($menu["$name"]['synopsis'],"synopsis")==0)
											$menu["$name"]['synopsis']="";
                                                                                $temp="";//存储@下一行到下一个@上一行之间的内容
                                                                                for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                                {
                                                                                if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                                {
                                                                                    $temp =$this->parse_function($temp);
                                                                                    if(strlen(trim($temp))>0)
                                                                                        $menu["$name"]['synopsis'].="<br>".$temp;//把说明结合起来
                                                                                break;
                                                                                }
                                                                                $temp.=$content[$j+1];
                                                                                }
                                                                                break;
									case "param":
										$param=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
			                                                        $menu["$name"]['param']["$param"]['name']=$param;
										if(strcasecmp($param,"param")==0)
											$menu["$name"]['param']["$param"]['name']="";
                                                                                $temp="";//存储@下一行到下一个@上一行之间的内容
                                                                                for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                                {
                                                                                if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                                {
                                                                                    $temp =$this->parse_function($temp);
                                                                                    if(strlen(trim($temp))>0)
                                                                                        $menu["$name"]['param']["$param"]['name'].=$temp;//把说明结合起来
                                                                                break;
                                                                                }
                                                                                $temp.=$content[$j+1];
                                                                                }
                                                                                break;
									case "item":
										$item=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if($last_data=="param"){
			                                                        	$menu["$name"]['param']["$param"]['item']["$item"]=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;}
										if($last_data== "return"){
			                                                        	$menu["$name"]["return"]["$return"]['item']["$item"]=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);}
										if(strcasecmp($item,"item")==0)
											$menu["$name"]["return"]["$return"]['item']["$item"]="";
                                                                                $temp="";//存储@下一行到下一个@上一行之间的内容
                                                                                for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                                {
                                                                                if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                                {
                                                                                    $temp =$this->parse_function($temp);
                                                                                    if(strlen(trim($temp))>0){
                                                                                	if($last_data=="param"){
                                                                                          $menu["$name"]['param']["$param"]['item']["$item"].="<br>".$temp;}
                                                                                	if($last_data== "return"){
                                                                                          $menu["$name"]["return"]["$return"]['item']["$item"].="<br>".$temp;}
}
	                                                                            break;
                                                                                }
                                                                                $temp.=$content[$j+1];
                                                                                }
                                                                                break;
									case "return":
										$return=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
			                                                        $menu["$name"]['return']["$return"]['name']=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($return,"return")==0)
											$menu["$name"]['return']["$return"]['name']="";
                                                                                $temp="";//存储@下一行到下一个@上一行之间的内容
                                                                                for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                                {
                                                                                if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                                {
                                                                                    $temp =$this->parse_function($temp);
                                                                                    if(strlen(trim($temp))>0)
                                                                                        $menu["$name"]['return']["$return"]['name'].="<br>".$temp;//把说明结合起来
                                                                                break;
                                                                                }
                                                                                $temp.=$content[$j+1];
                                                                                }
                                                                                break;
									default	:
										$default=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(!isset($name))
											$name=$this->file_array[$i];
										
										$menu["$name"]['default']["$default"]=substr(trim($content[$j]),strpos(trim($content[$j]),' ')+1);;
										if(strcasecmp($default,"default")==0)
											$menu["$name"]['default']["$default"]="";
                                                                                $temp="";//存储@下一行到下一个@上一行之间的内容
                                                                                for(;$j<count($content);$j++) //读取@之后的信息,到下一次出现@的前一行
                                                                                {
                                                                                if(strstr(trim($content[$j+1]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j+1]),"*/"))>0 || !(strpos($content[$j+1],'@')===false))//如果分析到ENDPAPER */和@ 退出
                                                                                {
                                                                                    $temp =$this->parse_function($temp);
                                                                                    if(strlen(trim($temp))>0)
                                                                                        $menu["$name"]['default']["$default"].="<br>".$temp;//把说明结合起来
                                                                                break;
                                                                                }
                                                                                $temp.=$content[$j+1];
                                                                                }
										break;
									}
									if($data != $last_data && $data !="item")
										$last_data=$data;//把上一次的参数存起来备用
								
								}
											
								
								if(strstr(trim($content[$j]),"ENDPAPER")=='ENDPAPER' || strlen(strstr(trim($content[$j]),"*/"))>0)// 如果之后是ENDPAPER 就不读取了 要是读到注释 也不读
								{
									break;
								}
							}//分析完一个FRIPAPER EDNPAPER
							
						}
						if(strlen(strstr(trim($content[$j]),"*/"))>0)//如果读取到注释结束
						{
							break;
						}
					}//分析完一个/* */
				}
			}//分析完成一个文件
			//这里完成对文件的生成
			//var_dump($menu);
			/****************************************************************
			 生成文件
			*****************************************************************/
			if(isset($author))
				$this->smarty->assign("author",$author);
			if(isset($version))
				$this->smarty->assign("version",$version);
			if(isset($package))
				$this->smarty->assign("package",$package);
			if(isset($description))
				$this->smarty->assign("description",$description);
			$this->smarty->assign("filename",$this->file_array[$i]);
			if(isset($menu)){
			$this->smarty->assign("menu",$menu);
			$this->smarty->assign("funclist",$menu);}
			$file_temp=$this->smarty->fetch($this->template);//把文件输入到一个指定的目录当中
			$fp = fopen($this->output."/".$this->file_array[$i].".html","w+");
			$copy_right='<!--/***************************************************************************
Don\'t remove this title!
FRIPAPER
@description
        this module can read remark from program files in src directory, 
        and create help files use html language in in dst directory
@version 2009-06-22 1.0.1
@author freeiris-安静 <yuzegao@163.com>
@package Freeiris2 Package Fripaper
Please submit bug reports, patches, etc to <yuzegao@163.com>
www.voxen.cn
ENDPAPER
See http://www.freeiris.org for more information about
the Freeiris project.
Freeiris2 -- 开源通信系统
本程序是自由软件，以GNU组织GPL协议第二版发布。关于授权协议内容
请查阅LICENSE文件。
***************************************************************************/-->
';
			fputs($fp,$copy_right);
			if(!fwrite($fp,$file_temp)){
   				die('[WARNING]: ".$this->file_array[$i]."生成Fripaper文件失败！');
			}
			fclose($fp);
			echo "[SUCCESS]: ".$this->file_array[$i]."生成Fripaper文件成功 目标文件".$this->output."/".$this->file_array[$i].".html\n";
		}
	}
/*********************************************************************************************************
分析@下一行到下一个@的上一行之间的内容
**********************************************************************************************************/
	function parse_function($temp)
	{
		if(strpos($temp,'<code>'))
		{
			$temp=str_replace("<code>","<pre id='code'>",$temp);
			$temp=str_replace('</code>',"</pre>",$temp);
			$temp=trim($temp);
		}
		if(strpos($temp,'<link'))
		{
			$temp=str_replace("<link src","<a href",$temp);
			$temp=str_replace('</link>',"</a>",$temp);
			$temp=trim($temp);
		}
		if(strpos($temp,'<key>'))
		{
			$temp=str_replace("<key>","<a name='关键字'>",$temp);
			$temp=str_replace("</key>","</a>",$temp);
			$temp=trim($temp);
		}
		return $temp;
	}

}
?>
