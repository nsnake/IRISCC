<?php
/* 
    the Asterisk config read and write module.

	Asteriskconf can parse and saving data with 
	Asterisk config files. this module support asterisk 
	1.0 1.2 1.4 1.6, and it also support Zaptel config 
	files.

    COPYRIGHT

    Copyright (c) Sun bing <hoowa.sun@gmail.com> 
	All rights reserved.

    You may distribute under the terms of either the GNU 
	General Public License or the Artistic License.

	The asterskconf be Part of FreeIris opensource Telephony 
	Project Access http://www.freeiris.org for more details.

    $Id: asteriskconf.inc.php 353 2010-01-22 08:42:22Z hoowa $

	NOT SUPPORT WARNNING:
	1. not support extensions language extensions.conf each.
	2. not support same key name in same section.
*/

class asteriskconf {
	// defined const
	const version = 0.2;

	// use resource array when save make fast than open file, but need more memory, default enabled. 
	// 打开过文件以后保留资源数组,可能占用更多内存,但是可以加快写入速度,默认打开
	public $keep_resource_array = true;
	// when save done, auto call .default enable. 
	// 当保存时候自动reload新保存的结构
	public $reload_when_save = true;
	// when reload done, auto clean_assign with current object. default enable. 
	// 当reload完成后清理掉commit_list里已经操作完成的
	public $clean_when_reload = true;
	// error messages
	// 如果系统返回false 显示错误信息
	public $errstr;
	// last changed file
	// 最后一次是否修改了配置文件
	public $last_changed_file = false;
	// comments flags
	// 注释符号
//	public $comments_flags = '\;|\#';

	// does this object loaded config files?
	// 是否已经打开过文件
	private $boolLoaded=false;
	// internal variable listed all command. i suggest don't modify and change this variable.
	private $commit_list=array();
	// internal variable of parsed. i suggest don't modify and change this variable.
	private $parsed_self=array();

	// under construct
	public function __construct() {
	}

	// return class version
	public function version() {return(self::version);}
	// return class important variables
	public function debug() {
		echo "\n====\$parsed_self=================================================================\n";
		print_r($this->parsed_self);
		echo "\n====\$commit_list=================================================================\n";
		print_r($this->commit_list);
		echo "==================================================================================\n";
		return(true);
	}

	/*------------------------------------------------------------------------------------------
		follow function read
	------------------------------------------------------------------------------------------*/

	/*
		read conf from file
		从文件读取conf文件
	*/
	public function parse_in_file($conffile) {

		// loaded?
		if ($this->boolLoaded == true) {
			$this->errstr="object alreadly parsed.\n";
			return(false);
		}

		// file exists?
		if (file_exists($conffile)==false) {
			$this->errstr="file ".$conffile." noexists!\n";
			return(false);
		}

		// set mode to loadded
		$this->boolLoaded = true;

		// read file in to array
//		$resource_list = file($conffile);
//		$resource_list = array_map(array('self','do_trimfunc'),$resource_list);
		$resource_list = explode("\n", file_get_contents($conffile)); // faster than that

		// call parse function
		$parsed_conf = $this->do_parse($resource_list);
		#try define default variable
		if ($this->keep_resource_array == false) {
			$resource_list = null;
		}

		$this->parsed_self=array(
			#user input
			'file'=> $conffile,
			#internal
			'parsed_conf'=> $parsed_conf,
//			parsed_section_chunk=> $parsed_section_chunk,
			'resource_list'=> $resource_list,
		);

		return(true);
	}

	/*
		do parse from data system
		内部函数,将conf数据进行解析
	*/
	private function do_parse($resource_list)	{

		$DATA = array();
		$DATA['[unsection]']=array();
//		$DATA_CHUNK = null;
//		$DATA_CHUNK['[unsection]']={} if ($section_chunk);
		$last_section_name = null;

		foreach ($resource_list as $eachline) {
			$line_sp = $this->do_cleanstring($eachline);

			#format : Find New Section ???
			if (preg_match('/^\[(.+)\]/',$line_sp,$matches)) {
				$last_section_name = $matches[1];
				$DATA[$last_section_name]=array();
//				$DATA_CHUNK[$last_section_name]=array();
				continue;

//			data_chunk not support at this time
//			#save source chunk to data_chunk
//			} else if ($section_chunk) {
//				next if ($one_line eq '');
//			my	$section_name = $last_section_name;
//				$section_name = '[unsection]' if (!$section_name);
//				#copying source chunk to data_chunk
//				push(@{$DATA_CHUNK{$section_name}},$one_line);
			}

			#next if just comment
			if ($line_sp == '') continue;

			#fromat : begin with # means include another files
			if (preg_match('/^\#/',$line_sp)) {
				$section_name = $last_section_name;
				if (!$section_name) $section_name = '[unsection]';
				$DATA[$section_name][$line_sp]=$line_sp;
				continue;
			}

			#format : Key=Value ???
//			if (preg_match('/\=/',$line_sp)) {
			if (strpos($line_sp,'=') !== false) {
				#split data and key
				$keyvalue = $this->do_clean_keyvalue($line_sp);

				$section_name = $last_section_name;
				if (!$section_name) $section_name = '[unsection]';
//				$DATA{$section_name}{$key}=[] if (!$DATA{$section_name}{$key});
//				push(@{$DATA{$section_name}{$key}},$value);
				$DATA[$section_name][$keyvalue[0]]=$keyvalue[1];
				continue;
			}
		}

		return($DATA);
	}

//	private function do_trimfunc($value) { 
//		return(rtrim($value,"\n"));
//	}
	/*
		do clean string spare and sharp
		内部函数 清理字符串
	*/
	private function do_cleanstring($line) {

		// if null return
		if ($line == "") return("");
		// if this line not begin with #
		//if (!preg_match('/^('.$this->comments_flags.')/',$line)) {
		//	$splited = preg_split('/'.$this->comments_flags.'/',$line);
		//	$line = $splited[0];
		//}

		$matchcomment_a = strpos($line,'#');
		// comment # at frist means include other
		if ($matchcomment_a === 0)
			return(trim($line));
		// comment # and ; in string means comments
		if ($matchcomment_a !== false || strpos($line,';') !== false) {
			$line = preg_replace('/([\;|\#].*)/',"",$line);
		}

		return(trim($line));
	}

	/*
		do clean key value spare and sharp
		内部函数 清理key/value
	*/
	private function do_clean_keyvalue($string) {
//		$keyvalue = preg_split('/\=/',$string);
		$keyvalue = explode('=',$string);

		#分离出两个参数,把前后的回车换行空格都删除掉,删除掉值的>如果值存在
		$keyvalue[0] = trim($keyvalue[0]);
		if ($keyvalue[1]) {
			$keyvalue[1] = preg_replace('/^\>/','',$keyvalue[1]);
			$keyvalue[1] = trim($keyvalue[1]);
		};
		return($keyvalue);
	}

	/*
		data append in frist string
		内部函数 向第一个参数append指定的数据
	*/
	private function do_data_append(&$sourcearray,$newdata) {

		if (is_scalar($newdata)) {
			$sourcearray[] = $newdata;#fast than array_push
		} else {
			$sourcearray = array_merge((array)$sourcearray,(array)$newdata);
		}

		return(true);
	}

	/*
		only return sections name list. does not include 'unsection'.
		返回所有sections的名字

		$sip_conf->section_list();

	*/
	public function section_list()
	{
		$sections = array();
		foreach (array_keys($this->parsed_self['parsed_conf']) as $each) {
			if ($each == '[unsection]')
				continue;
			array_push($sections,$each);
		}
		return($sections);
	}

	/*
		this function return parsed config files data.
		返回所有解吸出来的数据

		$sip_conf->section_all();

	*/
	public function section_all()
	{
		return($this->parsed_self['parsed_conf']);
	}

	/*
		return keys list of section name or unsection.
		返回指定section下的key

		$sip_conf->key_list('section name|unsection');

	*/
	public function key_list($section)
	{
		if (!array_key_exists($section,$this->parsed_self['parsed_conf'])) {
			$this->errstr="no key $section found.\n";
			return(false);
		}
		return(array_keys($this->parsed_self['parsed_conf'][$section]));
	}

	/*
		return referenced key list (and keys value), section value 'unsection' return all unsection keys, if section name unreachable return failed.
		返回指定section所有结构

		$sip_conf->key_all('section name|unsection');

	*/
	public function key_all($section)
	{
		if (!array_key_exists($section,$this->parsed_self['parsed_conf'])) {
			$this->errstr="no key $section found.\n";
			return(false);
		}
		return($this->parsed_self['parsed_conf'][$section]);
	}

	/*
		return referenced value list, if section name unreachable return failed. if key name unreachable return failed.
		返回指定section/key的值

		$sip_conf->get('section name|unsection','key name');
	*/
	public function get($section,$key)
	{
		if (!array_key_exists($section,$this->parsed_self['parsed_conf'])) {
			$this->errstr="no key $section found.\n";
			return(false);
		}
		if (!array_key_exists($key,$this->parsed_self['parsed_conf'][$section])) {
			$this->errstr="no $key / $section found.\n";
			return(false);
		}
		return($this->parsed_self['parsed_conf'][$section][$key]);
	}

	/*
		reload and parse config file. if clean_when_reload true will do clean_assign.
		reload重新读取数据

		$sip_conf->reload();

	*/
	public function reload()
	{
		#try read
		if (file_exists($this->parsed_self['file'])==false) {
			$this->errstr="file ".$this->parsed_self['file']." not found!\n";
			return(false);
		}

		// read file in to array
//		$resource_list = file($this->parsed_self['file']);
		$resource_list = explode("\n", file_get_contents($this->parsed_self['file'])); // faster than that

		// call parse function
		// save to
		$this->parsed_self['parsed_conf'] = $this->do_parse($resource_list);

		// try define default variable
		if ($this->keep_resource_array == false) {
			$resource_list = null;
		}
		$this->parsed_self['resource_list'] = $resource_list;

		// save to commit_list / do clean_when_reload ?
		if ($this->clean_when_reload==true) {
			$this->clean_assign();
		}
		return(true);
	}

	/*------------------------------------------------------------------------------------------
		follow function write
	------------------------------------------------------------------------------------------*/

	/*
		clean all assign command.
		清理掉已经分配的编辑命令

		$sip_conf->clean_assign();

	*/
	public function clean_assign()
	{
		$this->commit_list=array();
		return(true);
	}

	//-----------------------------------------------------------
	//  assign method to commit_list
	//	分配指令

	/*
		clean all assign command.
		清理掉已经分配的编辑命令

		$sip_conf->assign_cleanfile();

	*/
	public function assign_cleanfile()
	{
		$command=array();
		$command{'action'}='cleanfile';
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		replace new data when matched.
		将匹配的字符串替换为新字符串

		$sip_conf->assign_matchreplace('string','string');

		match -> string of matched data. 
		replace -> new data string. 
		

	*/
	public function assign_matchreplace($match,$replace)
	{
		$command=array();
		$command{'action'}='matchreplace';
		$command{'match'}=$match;
		$command{'replace'}=$replace;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		append data around with section name.
		在section周围增加数据


	    $sip_conf->assign_append('up'|'down'|'foot','section name',data=>'key=value'|array('key=value','key2=value2')|array(key=>'value',key=>'value'),NULL);

			point -> append data up / down / foot with section. 
			section -> matched section name, expect 'unsection'. 
			data -> new replace data in string/array/hash.


		append data around with section name and key/value in same section.
		在section下指定的key/value周围增加数据

		$sip_conf->assign_append('up'|'down'|'over','section name',data=>'key=value'|array('key=value','key2=value2')|array(key=>'value',key=>'value'),comkey=>array('key','value'),NULL};

			point -> over will overwrite with key/value matched. 
			comkey -> match key and value. 

		simple append data without any section.
		直接增加数据不考虑section

	    $sip_conf->assign_append('up'|'down',NULL,data=>'key=value'|array('key=value','key2=value2')|array(key=>'value',key=>'value'),NULL);

			point -> over will overwrite with key/value matched. 
			data -> new replace data in string/array/hash.

	*/
	public function assign_append($point,$section,$data,$comkey)
	{
		$command=array();
		$command{'action'}='append';
		$command{'point'}=$point;
		$command{'section'}=$section;
		$command{'data'}=$data;
		$command{'comkey'}=$comkey;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		replace the section body data.
		整体替换section所有内容

		$sip_conf->assign_replacesection('section name|unsection',data=>'key=value'|array('key=value','key2=value2')|array(key=>'value',key=>'value'));

		section -> all section name and '[unsection]'. 
	*/
	public function assign_replacesection($section,$data)
	{
		$command=array();
		$command{'action'}='replacesection';
		$command{'section'}=$section;
		$command{'data'}=$data;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		erase section name and section data.
		清理掉section

		$sip_conf->assign_delsection('section name|unsection');

		section -> all section and 'unsection'. 
	*/
	public function assign_delsection($section)
	{
		$command=array();
		$command{'action'}='delsection';
		$command{'section'}=$section;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		add section with name.
		新增section

		section -> name of new section. 

		$sip_conf->assign_addsection('section');
	*/
	public function assign_addsection($section)
	{
		$command=array();
		$command{'action'}='addsection';
		$command{'section'}=$section;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		this Method was different with Asterisk::config in Perl language!!!

		modify value with matched section.
		修改匹配的section下key的值

		$sip_conf->assign_editkey('section name|unsection','keyname','new_value');
	*/
	public function assign_editkey($section,$key,$new_value)
	{
		$command=array();
		$command{'action'}='editkey';
		$command{'section'}=$section;
		$command{'key'}=$key;
		$command{'new_value'}=$new_value;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		this Method was different with Asterisk::config in Perl language!!!

		erase all matched keyname in section or in 'unsection'.
		清理掉secion下的keyname

		$sip_conf->assign_delkey('section name|unsection','keyname');

	*/
	public function assign_delkey($section,$key)
	{
		$command=array();
		$command{'action'}='delkey';
		$command{'section'}=$section;
		$command{'key'}=$key;
		array_push($this->commit_list,$command);
		return(true);
	}

	/*
		save method and save internal method
		filename: run assign rules and save to file
		save_file();

		process commit list and save to file. if reload_when_save true will do reload. if no object variable file or file not exists or can't be save return failed. if defined new_file will save to new file, default overwrite objvar 'file'.
		保存修改到文件中

		$sip_conf->save_file('filename');
	*/
	public function save_file($new_file=null)
	{
		// loaded?
		if ($this->boolLoaded == false) {
			$this->errstr="please parse conf file frist\n";
			return(false);
		}

		// any in commit_list?
		if (count($this->commit_list) <= 0) {
			$this->errstr="not command under list\n";
			return(false);
		}

		$used_resource=array();
		#check to use resource_list?
		if ($this->keep_resource_array == true && is_array($this->parsed_self['resource_list']) == true) {
			$used_resource = $this->parsed_self['resource_list'];
		}

		if (count($used_resource) <= 0) {
			// file exists?
			if (file_exists($this->parsed_self['file'])==false) {
				$this->errstr="file ".$this->parsed_self['file']." noexists!\n";
				return(false);
			}
			// read file in to array
			//$used_resource = file($this->parsed_self['file']);
			//$used_resource = array_map(array('self','do_trimfunc'),$used_resource);
			$used_resource = explode("\n", file_get_contents($this->parsed_self['file'])); // faster than that
		}

		foreach ($this->commit_list as $one_case) {
			if ($one_case['action'] == 'editkey' || $one_case['action'] == 'delkey')
				$used_resource = $this->do_editkey($one_case,$used_resource);
			if ($one_case['action'] == 'delsection' || $one_case['action'] == 'replacesection')
				$used_resource = $this->do_delsection($one_case,$used_resource);
			if ($one_case['action'] == 'addsection')
				$used_resource = $this->do_addsection($one_case,$used_resource);
			if ($one_case['action'] == 'append')
				$used_resource = $this->do_append($one_case,$used_resource);
			if ($one_case['action'] == 'matchreplace')
				$used_resource = $this->do_matchreplace($one_case,$used_resource);
			if ($one_case['action'] == 'cleanfile') {
				$used_resource=array();
				break;
			}

		}

		#save file and check new_file
		if (count($used_resource) < 0) {
			$this->errstr="no listed used_resource\n";
			$this->last_changed_file=false;
			return(false);
		};
		$save_handle = null;
		if  (is_string($new_file) == true) {
			$save_handle = fopen($new_file,'w');
			$changed_filename = $new_file;
		} else {
			$save_handle = fopen($this->parsed_self['file'],'w');
			$changed_filename = $this->parsed_self['file'];
		}
		if (!$save_handle) {
			$this->errstr="Asteriskconf Save_file can't write : ".$new_file."\n";
			return(false);
		};
		flock($save_handle, LOCK_EX | LOCK_NB) or die("Unable to lock file !");
		fwrite($save_handle,implode("\n", $used_resource));
		flock($save_handle,LOCK_UN);
		fclose($save_handle);
		$this->last_changed_file=$changed_filename;

		#reload when save
		if ($this->reload_when_save == true) {
			$this->reload();
		}

		return(true);
	}

	/*
		do matchreplace
	*/
	private function do_matchreplace($one_case,$used_resource)
	{
		$NEW=array();
		$match = $one_case['match'];

		foreach ($used_resource as $one_line) {
			if (preg_match("/$match/",$one_line)==true) {
				$one_line = $one_case['replace'];
			}
			//$NEW = array_merge((array)$NEW,(array)$one_line);
			$this->do_data_append($NEW,$one_line);
		}

		return($NEW);
	}

	private function do_editkey($one_case,$used_resource)
	{
		$NEW=array();
		$last_section_name='[unsection]';
		$auto_save=false;

		foreach ($used_resource as $one_line) {

			#tune on auto save
			if ($auto_save==true) {
				//$NEW = array_merge((array)$NEW,(array)$one_line);
				$this->do_data_append($NEW,$one_line);
				continue;
			}

			$line_sp = $this->do_cleanstring($one_line);

			#income new section
			if (preg_match("/^\[(.+)\]/",$line_sp,$matches)==true) {
				$last_section_name = $matches[1];
			} elseif ($last_section_name == $one_case['section'] && preg_match("/\=/",$line_sp)) {

				#split data and key
				$keyvalue=$this->do_clean_keyvalue($line_sp);

				if ($keyvalue[0] == $one_case['key'] && $one_case['action'] == 'delkey') {
					$one_line="";
					// disabled auto save when delkey helpful for delete same key name
//					$auto_save = true;
				} elseif ($keyvalue[0] == $one_case['key']) {
					$one_line = $keyvalue[0]."=".$one_case['new_value'];
					$auto_save = true;
				}
			}

			//if ($one_line != "")
			//	$NEW = array_merge((array)$NEW,(array)$one_line);
			$this->do_data_append($NEW,$one_line);

		}
		return($NEW);
	}

	private function do_delsection($one_case,$used_resource)
	{
		$NEW=array();
		$last_section_name='[unsection]';
		$auto_save=false;

		if ($one_case['section'] == '[unsection]' && $one_case['action'] == 'replacesection')
			$NEW = array_merge((array)$NEW,(array)$this->do_format_convert($one_case['data']));

		foreach ($used_resource as $one_line) {

			#tune on auto save
			if ($auto_save==true) {
//				$NEW = array_merge((array)$NEW,(array)$one_line);
				$this->do_data_append($NEW,$one_line);
				continue;
			}

			$line_sp = $this->do_cleanstring($one_line);

			if ($last_section_name == $one_case['section'] && preg_match("/^\[(.+)\]/",$line_sp)==true) {
				#when end of compared section and come new different section
				$auto_save = true;
			} elseif ($last_section_name == $one_case['section']) {
				continue;
			} elseif (preg_match("/^\[(.+)\]/",$line_sp,$matches)==true) {
				#is this new section?
				if ($one_case['section'] == $matches[1]) {
					$last_section_name = $matches[1];
					if ($one_case['action'] == 'delsection')
						continue;
					//$NEW = array_merge((array)$NEW,(array)$one_line);
					$this->do_data_append($NEW,$one_line);
					$one_line=$this->do_format_convert($one_case['data']);
				}
			}

			// add data fast push than combined two array
			//$NEW = array_merge((array)$NEW,(array)$one_line);
			$this->do_data_append($NEW,$one_line);
		}

		return($NEW);
	}

	/*
		income scalar,array ref,hash ref output array data
	*/
	private function do_format_convert($data)
	{
		if (is_string($data) == true) {
			return($data);
		}
		
		if (is_array($data) == true) {
			$ishash=false;
			foreach (array_keys($data) as $value) {
				if (preg_match("/[^0-9]/",$value)) {
					$ishash=true;
					break;
				}
			}
			if ($ishash == false) {
				return($data);
			} else {
				$abc = array();
				foreach ($data as $key => $value) {
					array_push($abc,"$key=$value");
				}
				return($abc);
			}
		}
	}

	private function do_addsection($one_case,$used_resource)
	{
		$exists=false;
		$section = '[' . $one_case['section'] . ']';
		
		foreach ($used_resource as $one_line) {
			
			$line_sp = $this->do_cleanstring($one_line);

			if (preg_match("/^\[(.+)\]/",$line_sp,$matches)==true) {
				if ($section == $line_sp) {
					$exists = true;
					break;
				}
			}
		}
		if($exists==false) {
			$used_resource = array_merge((array)$used_resource, (array)$section);
		}

		return($used_resource);
	}


	private function do_append($one_case,$used_resource)
	{
		$NEW=array();

		if ($one_case['section'] == '') {
		#Append data head of source data/foot of source data
			if ($one_case['point'] == 'up') {
				$NEW = array_merge((array)$this->do_format_convert($one_case['data']),(array)$used_resource);
			} else {
				$NEW = array_merge((array)$used_resource,(array)$this->do_format_convert($one_case['data']));
			}

		} elseif ($one_case['comkey'] == '') {
		#Append data head/foot of section_name
			$auto_save=false;
			$save_tmpmem=false;
			$offset=0;
			foreach ($used_resource as $one_line) {

				#tune on auto save
				if ($auto_save==true) {
					//$NEW = array_merge((array)$NEW,(array)$one_line);
					$this->do_data_append($NEW,$one_line);
					$offset++;
					continue;
				}
				#check section
				$line_sp = $this->do_cleanstring($one_line);
				$section_name=null;
				if (preg_match("/^\[(.+)\]/",$line_sp,$matches)==true) {
					$section_name = $matches[1];
				}
				# for up / down
				if ($section_name != '' && $one_case['section'] == $section_name && $one_case['point'] == 'up') {
					$NEW = array_merge((array)$NEW,(array)$this->do_format_convert($one_case['data']));
					$auto_save=true;
				} elseif ($section_name != '' && $one_case['section'] == $section_name && $one_case['point'] == 'down') {
					//$NEW = array_merge((array)$NEW,(array)$one_line);
					$this->do_data_append($NEW,$one_line);
					$one_line=$this->do_format_convert($one_case['data']);
					$auto_save=true;

				# for foot matched section
				} elseif ($section_name != '' && $one_case['section'] == $section_name && $one_case['point'] == 'foot') {
					$save_tmpmem=true;
				# for foot 
				} elseif ($save_tmpmem == true && $section_name && $one_case['section'] != $section_name) {
					$NEW = array_merge((array)$NEW,(array)$this->do_format_convert($one_case['data']));
					$auto_save=true;
					$save_tmpmem=false;
				# for foot 
				} 
				if ($save_tmpmem == true && $offset==(count($used_resource)-1)) {
					//$NEW = array_merge((array)$NEW,(array)$one_line);
					$this->do_data_append($NEW,$one_line);
					$one_line=$this->do_format_convert($one_case['data']);
					$auto_save=true;
					$save_tmpmem=false;
				}

				//$NEW = array_merge((array)$NEW,(array)$one_line);
				$this->do_data_append($NEW,$one_line);
				$offset++;
			}

		} else {

			$last_section_name='[unsection]';
			$auto_save=false;
			foreach ($used_resource as $one_line) {

				#tune on auto save
				if ($auto_save==true) {
					//$NEW = array_merge((array)$NEW,(array)$one_line);
					$this->do_data_append($NEW,$one_line);
					continue;
				}

				$line_sp = $this->do_cleanstring($one_line);
				#income new section
				if (preg_match("/^\[(.+)\]/",$line_sp,$matches)==true) {
					$last_section_name = $matches[1];
				} elseif ($last_section_name == $one_case['section'] && preg_match("/\=/",$line_sp)==true) {
					#split data and key
					$keyvalue = $this->do_clean_keyvalue($line_sp);
					if ($keyvalue[0] == $one_case['comkey'][0] && $keyvalue[1] == $one_case['comkey'][1] && $one_case['point'] == 'up') {
						$NEW = array_merge((array)$NEW,(array)$this->do_format_convert($one_case['data']));
						$auto_save=true;
					} elseif ($keyvalue[0] == $one_case['comkey'][0] && $keyvalue[1] == $one_case['comkey'][1] && $one_case['point'] == 'down') {
						//$NEW = array_merge((array)$NEW,(array)$one_line);
						$this->do_data_append($NEW,$one_line);
						$one_line=$this->do_format_convert($one_case['data']);
						$auto_save=true;
					} elseif ($keyvalue[0] == $one_case['comkey'][0] && $keyvalue[1] == $one_case['comkey'][1] && $one_case['point'] == 'over') {
						$one_line=$this->do_format_convert($one_case['data']);
						$auto_save=true;
					}
				}
				//$NEW = array_merge((array)$NEW,(array)$one_line);
				$this->do_data_append($NEW,$one_line);
			}

		}

		return($NEW);
	}

}
?>
