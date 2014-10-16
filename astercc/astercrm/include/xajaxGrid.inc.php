<?php /**
 * xhtml.inc.php :: Main XajaxGrid class file
 *
 * XajaxGrid version 1.2.1
 * copyright (c) 2006 by Jesus Velazquez ( jjvema@yahoo.com )
 * http://geocities.com/jjvema/
 *
 * XajaxGrid is an open source PHP class library for easily creating a grid data
 * on web-based Ajax Applications. Using XajaxGrid.
 *
 * xajax is released under the terms of the LGPL license
 * http://www.gnu.org/copyleft/lesser.html#SEC3
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package XajaxGrid
 * @version $Id: xajaxGrid.inc.php,v 1.2 2006/09/30 17:15:08 jjvema Exp $
 * @copyright Copyright (c) 2006  by Jesus Velazquez
 * @license http://www.gnu.org/copyleft/lesser.html#SEC3 LGPL License
 */
 /*
 * 2007-11-9 增加addRowSearchMore()函数,增加搜索选项	by solo
 */

/** \brief Class to generate a table dynamically
 *
 * The ScrollTable class generate dynamically a table
 *
 * @package XajaxGrid
 */


require_once ('localization.class.php');


if (isset($_SESSION['curuser']['country'])){
	$GLOBALS['local_grid']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'xajaxGrid');
}else
	$GLOBALS['local_grid']=new Localization('en','US','xajaxGrid');


class ScrollTable{
	/**
	 * <i>integer</i> Number of columns for the table.
	 */
	var $n_cols;
	/**
	 * <i>string</i> Row of table to display the search form.
	 */
	var $search;
	/**
	 * <i>string</i> Content of the table top
	 */
	var $top;
	/**
	 * <i>string</i> Content of the table headers
	 */
	var $header;
	/**
	 * <i>string</i> Content of each table row
	 */
	var $rows;
	/**
	 * <i>string</i> Content of table footer
	 */
	var $footer;
	/**
	 * <i>string</i> Style for table row
	 */
	var $rowStyle;
	/**
	 * <i>string</i> Attributes for each table column
	 */
	var $colAttrib;
	/**
	 * <i>integer</i> It contains the limit of records to show in the SQL sentence executed
	 */
	var $limit;
	/**
	 * <i>integer</i> It contains the initial record to show in the SQL sentence executed
	 */
	var $start;
	/**
	 * <i>string</i> It contains the filter of the SQL sentence executed
	 */
	var $filter;
	/**
	 * <i>integer</i> It contains the total number of rows to show
	 */
	var $numRowsToShow;
	/**
	 * <i>integer</i> It contains the total number of rows of the SQL sentence executed
	 */
	var $numRows;
	/**
	 * <i>string</i> It is the content to search in a SQL sentence
	 */
	var $content;
	/**
	 * <i>string</i> It is the field to organize the data of the table
	 */
	var $order;

	var $ordering;

	var $exportFlag;


	/**
	 * Constructor.
	 *
	 * @param integer $cols: Amount of columns of the table
	 * @param integer $start: initial record to show
	 * @param integer $limit: final record to show
	 * @param string  $filter: field name of the database table.
	 * @param integer $numRows: number of total rows of the search
	 * @param string  $content: content to search
	 * @param string  $order: field to organize the data of the table
	 */
	function ScrollTable($cols, $start = 0, $limit, $filter = null, $numRows = 0, $content = null, $order = null,$customerid='',$cdrtype = '',$userexten = '',$table = '',$divName = ''){
		$this->n_cols = $cols;
		$this->limit = $limit;
		$this->numRows = $numRows;
		$this->numRowsToShow = ROWSXPAGE;
		$this->start = $start;
		$this->top = '<table class="adminlist" border="1">';
		$this->rowStyle = "row1";
		$this->filter = $filter;
		$this->content = $content;
		$this->order = $order;
		$this->customerid = $customerid;
		$this->cdrtype = $cdrtype;
		$this->userexten = $userexten;
		$this->table = $table; 
		$this->divName = $divName;
		if ($cdrtype != '') {
			if($cdrtype == 'diallist_dup'){
				$this->setSpecFooter('diallist_dup'); //用于agent页面中customer信息页中的mycdr页脚
			}else{
				$this->setSpecFooter('mycdr');
			}
		}elseif ($userexten != '') {
			$this->setSpecFooter('diallist');//用于agent页面中customer信息页中的diallist页脚
		}elseif ($table == 'monitorrecord' && $customerid!='') {
			$this->setSpecFooter('monitorrecord');//用于agent页面中customer信息页中的monitorrecord页脚
		}elseif($table == 'agents'){
			//$this->setSpecFooter('agents');
		}elseif($table == 'ticket_details' && $divName == 'formCurTickets'){
			$this->setSpecFooter('formCurTickets');
		}elseif($table == 'ticket_details' && $divName == 'formMyTickets'){
			$this->setSpecFooter('formMyTickets',$customerid);
		}else{
			$this->setFooter();
		}
	}


	/**
	* Set a header Table with attributes on the variable "header" of the class.
	*
	* @param string 	$class		the clas style
	* @param array  	$options		array that contain the labels for the headers.
	* @param array 	$attribs		array that contain the attributes for the headers.
	* @param array		$events		array that contain the events on this labels.
	* @param boolean	$edit			Flag to determine if column Edit is showed.
	* @param boolean	$delete		Flag to determine if column Delete is showed.
	* @return none
	*
	*/

	function setHeader($class,$headers,$attribs,$events,$edit=true,$delete=true,$detail=true){

		global $local_grid; 
		if($_SESSION['curuser']['usertype'] == 'agent' && $this->table != 'diallist') $delete = false; //禁止agent的删除功能
		$ind = 0;
		$this->header = '
		<tr>';
		foreach($headers as $value){
			$this->header .= '
			<th '.$attribs[$ind].' class="'.$class.'">';
// 				if(!empty($events[$ind])){
// 					$this->header .= '<a href="?" '.$events[$ind].'>'.$value.'</a>';
// 				}else{
// 					$this->header .= $value;
// 				}
			$this->header .= $value;
			if(strstr($value,'checkbox') || $this->divName == "formDiallistPannel" || $class=="NONE"){
				$this->header .= '';
			}else{
				$this->header .= '
				&nbsp;
				<img src="skin/default/images/asc.png" title="A&nbsp;->&nbsp;Z" style="cursor: pointer;" '.str_replace("ORDERING","ASC",$events[$ind]).'>
				<img src="skin/default/images/desc.png" title="Z&nbsp;->&nbsp;A" style="cursor: pointer;" '.str_replace("ORDERING","DESC",$events[$ind]).'>
				</th>';
			}
			$ind++;
		}

		if($edit)
			$this->header .= '
				<th style="text-align: center" class="'.$class.'" width="5%" nowrap>
					'.$local_grid->Translate("edit").'
				</th>';

		if($delete)
			$this->header .= '
				<th style="text-align: center" class="'.$class.'" width="5%" nowrap>
					'.$local_grid->Translate("delete").'
				</th>';

		if($detail)
			$this->header .= '
				<th style="text-align: center" class="'.$class.'" width="5%" nowrap>
					'.$local_grid->Translate("detail").'
				</th>';

		$this->header .= '
			</tr>';
	}

	/**
	* Set the attributes for the table columns.
	*
	* @param array 	$attribsCols		array that contain the attributes for the headers.
	* @return none
	*
	*/

	function setAttribsCols($attribsCols){
		$this->colAttrib = $attribsCols;
	}

	/**
	* Add each row generates dynamically from database records obtained
	*
	* @param string 	$table		Table name of data base
	* @param array 	$arr			Array with the data extracted in the SQL Sentence
	* @param boolen	$edit			Flag to determine if column Edit is showed.
	* @param boolen	$delete		Flag to determine if column Delete is showed.
	* @param string	$divName		Name div to display the grid.
	* @param array		$fields		Array with all field's name.
	* @return none
	*
	*/

	function addRow($table,$arr,$edit=true,$delete=true,$detail=true,$divName="grid",$fields=null,$privilege=null,$styleStr = "",$forPortal = ""){
		global $local_grid;
		
		if($_SESSION['curuser']['usertype'] == 'agent') $delete = false;
		$nameRow = $divName."Row".$arr[0];
	    $row = '<tr id="'.$nameRow.'" class="'.$this->rowStyle.'" >'."\n";
		$ind = 0;
		if($table == "group" ){
		   $deleteAlert = $local_grid->Translate("delete_confirm_group");
		}elseif($table == "campaign" ){
		   $deleteAlert = $local_grid->Translate("delete_confirm_campaign");
		}else{
		   $deleteAlert = $local_grid->Translate("delete_confirm");
		}

	   foreach ($arr as $key => $value) {
	   	$nameCell = $nameRow."Col".$ind;
/*
		if($key != 'id')
   			$row .= '<td id="'.$nameCell.'" style="cursor: pointer;" '.$this->colAttrib[$ind-1].' onDblClick="xajax_editField(\''.$table.'\',\''.$fields[$ind-1].'\',\''.$nameCell.'\',\''.$value.'\',\''.$arr[0].'\');return false">'.$value.'</td>'."\n";
   		$ind++;
		}
*/
			
			if($key != 'id'){
				if($key == 'filename' && ($table == 'monitorrecord' || $table == 'mycdr') && $value != '') {	
					$row .= '<td id="'.$nameCell.'" style="cursor: pointer;" '.$this->colAttrib[$ind-1].'><a href="?" onClick="var l= getPosLeft(this); var t=getPosTop(this);xajax_playmonitor(\''.$value.'\',l,t);return false;" class="info"><span>'.basename($value).'</span><img src="skin/default/images/play.gif" border="0"></a></td>'."\n";
				}elseif($key == 'select_id'){
					$row .= '<td id="'.$nameCell.'" style="cursor: pointer;" '.$this->colAttrib[$ind-1].'><input type="checkbox" id="ckb[]" name="ckb[]" value="'.$value.'" ></td>'."\n";
				}elseif($table == 'ticket_details' && $key == 2 && $divName =='formCurTickets'){
					if($value != '') {
						$row .= '<td id="'.$nameCell.'" style="cursor: pointer;'.$styleStr.'" '.$this->colAttrib[$ind-1].'><a href="?" onclick="curTicketDetail('.$arr[0].');return false;">'.$value.'</a></td>'."\n";
					}
				}elseif($table == 'ticket_details' && $key == 3 && $divName =='formCurTickets'){
					if($value != '') {
						$row .= '<td id="'.$nameCell.'" style="cursor: pointer;'.$styleStr.'" '.$this->colAttrib[$ind-1].'><a href="?" onclick="curCustomerDetail(\''.$value.'\');return false;">'.$value.'</a></td>'."\n";
					}
				}else{
					$row .= '<td id="'.$nameCell.'" style="cursor: pointer;'.$styleStr.'" '.$this->colAttrib[$ind-1].'>'.$value.'</td>'."\n";
				}
			}
   			$ind++;
		}
		
		if ($divName == 'formDiallist' || $divName == 'formDiallistPannel'){
			if($privilege == $_SESSION['curuser']['username'] || $_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'groupadmin'){
				if($edit){
					$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="xajax_edit('.$arr[0].',\''.$table.'\');return false;"><img src="skin/default/images/edit.png" border="0"></a>
						</td>';
				}
				if('1'){
					$row .= '
							<td align="center" width="5%" nowrap>
								<a href="?" onClick="if (confirm(\''.$local_grid->Translate("delete_confirm").'\')){  xajax_searchDiallistFormSubmit(xajax.getFormValues(\'searchDiallistForm\'),0,5,\''.$arr[0].'\',\'delete\');}return false;"><img src="skin/default/images/trash.png" border="0"></a>
							</td>';
				}
			}else $row .= '<td align="center" width="5%" nowrap>&nbsp;</td>
							<td	align="center" width="5%" nowrap>&nbsp;</td>';
		}else{
			if($edit){
				if($forPortal == 'forportal') {
					$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="edit('.$arr[0].',\''.$table.'\');return false;"><img src="skin/default/images/edit.png" border="0"></a>
						</td>';
				} else {
					$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="xajax_edit('.$arr[0].',\''.$table.'\');return false;"><img src="skin/default/images/edit.png" border="0"></a>
						</td>';
				}
			}
			if($delete)
				$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="if (confirm(\''.$deleteAlert.'\')){  xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),0,5,\''.$arr[0].'\',\'delete\');}return false;"><img src="skin/default/images/trash.png" border="0"></a>
						</td>';
			if($detail){
				if($forPortal == 'forportal') {
					$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="showDetail(\''.$arr[0].'\');return false;">'.$local_grid->Translate("detail").'</a>
						</td>';
				} else {
					$row .= '
						<td align="center" width="5%" nowrap>
							<a href="?" onClick="xajax_showDetail(\''.$arr[0].'\');return false;">'.$local_grid->Translate("detail").'</a>
						</td>';
				}
			}
				
		}

		$row .= "</tr>\n";
		$this->rows .= $row;

		if($this->rowStyle == "row0") $this->rowStyle = "row1"; else $this->rowStyle = "row0";

	}


	/**
	* Add each row generates dynamically from database records obtained without Edit and Delete columns
	*
	* @param string 	$table		Table name of data base
	* @param array 	$arr			Array with the data extracted in the SQL Sentence
	* @return none
	*
	*/

	function addRow2($table,$arr){

	   $row = '<tr class="'.$this->rowStyle.'" >';
		$ind = 0;

	   foreach ($arr as $key => $value) {
	   	if($key != 'id')
   			$row .= '<td '.$this->colAttrib[$ind-1].'>'.$value.'</td>';
   		$ind++;
		}

		$row .= "</tr>";
		$this->rows .= $row;

		if($this->rowStyle == "row0") $this->rowStyle = "row1"; else $this->rowStyle = "row0";

	}


	/**
	* Add the line with the search form and the button to add a new record
	*
	* @param string 	$table		Table name of data base
	* @param array 	$fieldsFromSearch 	Its contains the values from "SELECT" search form.
	* @param array		$fieldsFromSearchShowAs	 Its contains the labels show in the "SELECT" search form.
	* @param boolen	$withNewButton	If = 0, then not print the "New Record" button.
	* @return none
	*
	*/

	function addRowSearch($table,$fieldsFromSearch,$fieldsFromSearchShowAs, $withNewButton = 1){
		global $local_grid;		$ind = 0;
		$this->search = '
			<table width="99%" border="0">
			<tr>
				<td align="left" width="10%">';
				if($withNewButton){
					$this->search .= '<button id="submitButton" onClick="xajax_add();return false;">'.$local_grid->Translate("add_record").'</button>';
				}
		$this->search .= '
				</td>
				<td> '.$local_grid->Translate("table").': '.
					$table.
				'</td>
				<td align="right" width="30%" nowrap>
				'.$local_grid->Translate(search).' : &nbsp;<input type="text" size="30" id="searchContent" name="searchContent" >
				&nbsp;&nbsp;'.$local_grid->Translate("by").' &nbsp;
					<select id="searchField" name="searchField">
						<option value="'.null.'">'.$local_grid->Translate("select_field").'</option>';
					foreach ($fieldsFromSearchShowAs as $value) {
						$this->search .= '<option value="'.$fieldsFromSearch[$ind].'">'.$value.'</option>';
						$ind++;
					}
		$this->search .= '
					</select>
				&nbsp;&nbsp;<button id="submitButton" onClick="xajax_showGrid(0,'.$this->numRowsToShow.',document.getElementById(\'searchField\').value,document.getElementById(\'searchContent\').value,document.getElementById(\'searchField\').value);return false;">'.$local_grid->Translate("continue").'</button>
				</td>

			</tr>
		</table>';
	}
	
	/*
	* customer addRowSearchMore
	*/
    //增加搜索选项
	function addRowSearchMore($table,$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit, $withNewButton = 1,$withDelButton=false,$typeFromSearch = null,$typeFromSearchShowAs = null,$stype = null,$radioFieldVal = null){
		global $local_grid;
		$ind = 0;
		$ind_type = 0;
		$ind_typeselected = 0;
		$ind_selected = 0;

		if ($table == 'mycdr' && $this->customerid !=''){
			$this->search = '
		    <form action="javascript:void(null);" name="searchCdrForm" id="searchCdrForm" onSubmit="xajax_searchCdrFormSubmit(xajax.getFormValues(\'searchCdrForm\'),0,5);">
			<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
			<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
			<input type="hidden" name="customerid" id="customerid" value="'.$this->customerid.'"/>
			<input type="hidden" name="cdrtype" id="cdrtype" value="'.$this->cdrtype.'"/>
			<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
			<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
			<table width="99%" border="0" style="line-height:30px;">
			
			<tr>
				<td align="left" width="10%">';
		}elseif ($table == 'diallist' && $this->userexten != ''){
			$this->search = '
		    <form action="javascript:void(null);" name="searchDiallistForm" id="searchDiallistForm" onSubmit="xajax_searchDiallistFormSubmit(xajax.getFormValues(\'searchDiallistForm\'),0,5);">
			<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
			<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
			<input type="hidden" name="userexten" id="userexten" value="'.$this->userexten.'"/>
			<input type="hidden" name="customerid" id="customerid" value="'.$this->customerid.'"/>
			<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
			<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
			<table width="99%" border="0" style="line-height:30px;">			
			<tr>
				<td align="left" width="10%">';
			if($withNewButton){
				$this->search .= '<input type="button" id="submitButton" onClick="xajax_addDiallist(\''.$this->userexten.'\',\''.$this->customerid.'\');return false;" value="'.$local_grid->Translate("add_record").'">';
			}
		}elseif ($table == 'cur_mycdrs' && $this->customerid !=''){// mycdrs of one customer,this is alias for distinguish
			$this->search = '
		    <form action="javascript:void(null);" name="searchRecordsForm" id="searchRecordsForm" onSubmit="xajax_searchRecordsFormSubmit(xajax.getFormValues(\'searchRecordsForm\'),0,5);">
			<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
			<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
			<input type="hidden" name="customerid" id="customerid" value="'.$this->customerid.'"/>
			<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
			<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
			<table width="99%" border="0" style="line-height:30px;">
			<tr>
				<td align="left">';
		}elseif ($table == 'ticket_details' && $this->customerid !=''){
			$this->search = '
			<form action="javascript:void(null);" name="searchTicketsForm" id="searchTicketsForm" onSubmit="xajax_searchTicketsFormSubmit(xajax.getFormValues(\'searchTicketsForm\'),0,5);">
			<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
			<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
			<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
			<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
			<table width="99%" border="0" style="line-height:30px;">
			<tr>
				<td align="left">';
		}elseif ($table == 'add_new_tickets'){
			$this->search = '
			<form action="javascript:void(null);" name="searchTicketsForm" id="searchTicketsForm" onSubmit="xajax_searchTicketsFormSubmit(xajax.getFormValues(\'searchTicketsForm\'),0,5);">
			<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
			<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
			<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
			<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
			<table width="99%" border="0" style="line-height:30px;">
			<tr>
				<td align="left">';
			if($withNewButton){
				$this->search .= '<input type="button" id="submitButton" onClick="xajax_addNewTicket();return false;" value="'.$local_grid->Translate("add_new_ticket").'">';
			}
		}else {
			$this->search = '
				<form action="javascript:void(null);" name="searchForm" id="searchForm" onSubmit="xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),0,'.$this->numRowsToShow.');">
				<input type="hidden" name="numRows" id="numRows" value="'.$start.'"/>
				<input type="hidden" name="limit" id="limit" value="'.$limit.'"/>
				<input type="hidden" name="ordering" id="ordering" value="'.$this->ordering.'"/>
				<input type="hidden" name="order" id="order" value="'.$this->order.'"/>
				<table width="99%" border="0" style="line-height:30px;">

				<tr>
					<td align="left" >';
			if($withNewButton){
				$this->search .= '<input type="button" id="submitButton" onClick="xajax_add();return false;" value="'.$local_grid->Translate("add_record").'">';
			}
			if($withDelButton){
				$this->search .= '&nbsp;&nbsp;<input type="button" id="submitButton" onClick="if (confirm(\''.$local_grid->Translate("delete_confirm").'\')){xajax_deleteByButton(xajax.getFormValues(\'delGrid\'),xajax.getFormValues(\'searchForm\'));}return false;" value="'.$local_grid->Translate("delete").'">';
			}			
		}
		if($stype != 'none'){
			$this->search .= '
					</td>
					<td> '.$local_grid->Translate("table").': ';
			if($table == 'cur_mycdrs'){
				$this->search .= "mycdr" ;
			} else {
				$this->search .= $table ;
			}
			
			$this->search .= '</td>
					<td align="right" >
						<div style="width:100%;height:auto;line-height:25px;" name="addSearth" id="addSearth">';
			if($filter != null){
				for($j=0;$j<count($filter);$j++){
					if(trim($content[$j]) != '' && trim($filter[$j]) != ''){
						$this->search .= ''.$local_grid->Translate(search).'&nbsp;'.$local_grid->Translate("by").' : &nbsp;
						<select name="searchField[]" id="searchField[]">
										<option value="'.null.'">'.$local_grid->Translate("select_field").'</option>';
									foreach ($fieldsFromSearchShowAs as $value_arr) {
										if($ind_selected > count($fieldsFromSearch)-1)
										{
											$ind_selected = 0;
										}
										$this->search .= '<option value="'.$fieldsFromSearch[$ind_selected].'" ';
										if($fieldsFromSearch[$ind_selected] == $filter[$j]){
											$this->search .= ' selected ';
										}
										$this->search .=  '>'.$value_arr.'</option>';
										$ind_selected++;
									}
						$this->search .= '</select>&nbsp;&nbsp;';
						
						if(is_array($typeFromSearchShowAs) ){
							$this->search .='<select name="searchType[]" id="searchType[]">';
								//<option value = "'.null.'">'.$local_grid->Translate("select_type").'</option>';
								foreach ($typeFromSearchShowAs as $value_arr) {
										if($ind_typeselected > count($typeFromSearch)-1)
										{
											$ind_typeselected = 0;
										}
										$this->search .= '<option value="'.$typeFromSearch[$ind_typeselected].'" ';
										if($typeFromSearch[$ind_typeselected] == $stype[$j]){
											$this->search .= ' selected ';
										}
										$this->search .=  '>'.$value_arr.'</option>';
										$ind_typeselected++;
								}
							$this->search .= '</select>&nbsp;&nbsp;';
						}
						$this->search .= '<input type="text" size="30"  name="searchContent[]" id="searchContent[]" value="'.$content[$j].'" />
								<br>';
									
					}
				}
			}
			$this->search .= ''.$local_grid->Translate('search').'&nbsp;'.$local_grid->Translate("by").' : &nbsp;<select name="searchField[]" id="searchField[]">
							<option value="'.null.'">'.$local_grid->Translate("select_field").'</option>';
						foreach ($fieldsFromSearchShowAs as $value) {
							$this->search .= '<option value="'.$fieldsFromSearch[$ind].'" ';
							$this->search .=  '>'.$value.'</option>';
							$ind++;
						}
			$this->search .= '</select>&nbsp;&nbsp;';
			if(is_array($typeFromSearchShowAs)){
				$this->search .='<select name="searchType[]" id="searchType[]">';
						//<option value = "'.null.'">'.$local_grid->Translate("select_type").'</option>';
				foreach ($typeFromSearchShowAs as $value) {
					$this->search .= '<option value="'.$typeFromSearch[$ind_type].'" ';
					$this->search .=  '>'.$value.'</option>';
					$ind_type++;
				}
				$this->search .= '</select>&nbsp;&nbsp;';
			}
			$this->search .= '<input type="text" size="30"  name="searchContent[]" id="searchContent[]" /><br>';
			
			$this->search .= '</div>
						</td>
						<td>
					&nbsp;&nbsp;
					<INPUT TYPE="hidden" value="" name="numRowsToShow" id="numRowsToShow"/>
					<INPUT TYPE="hidden" value="'.$this->limit.'" name="limit" id="limit"/>
					';
			if($table == 'mycdr') {
				if($radioFieldVal == 'answered') {
					$allRadioSelect = ' ';
					$answerRadioSelect = ' checked ';
				} else {
					$allRadioSelect = ' checked ';
					$answerRadioSelect = ' ';
				}
				$this->search .= '&nbsp;&nbsp;
					<input type="radio" name="allOrAnswer" value="all" id="cdrAll" '.$allRadioSelect.' onclick="xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),0,50);" />&nbsp;<b>'.$local_grid->Translate("All").'</b>
					<input type="radio" name="allOrAnswer" value="answered" id="cdrAnswered" '.$answerRadioSelect.' onclick="xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),0,50);" />&nbsp;<b>'.$local_grid->Translate("Answered").'</b>';
			}
			
			$this->search .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" id="searchButton" name="searchButton" value="'.$local_grid->Translate("continue").'" onclick="if(document.getElementById(\'optionFlag\').value == \'delete\'){if(confirm(\''.$local_grid->Translate("searchdelete_confirm").'\')) return true; return false;}"/></td>';
			
			if($this->deleteFlag != 1 && $this->exportFlag != 1 && $this->exportFlag != 2 && $this->multiEditFlag != 1){
				$this->search .= '<input type="hidden" name="optionFlag" id="optionFlag" value="">';
			}else{
				$option = '<option value = "'.null.'" >'.$local_grid->Translate("Select Option").'</option>';
				
				if($this->exportFlag == 1){
					$option .= '<option value="export">'.$local_grid->Translate("Export").'</option>';
					$optionOnchange .= 'if(this.value==\'export\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("Export").'\';}';
				}
				if($this->exportFlag == 2){
					$option .= '<option value="export">'.$local_grid->Translate("Export").' xls</option>';
					$optionOnchange .= 'if(this.value==\'export\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("Export").'\';}';

					$option .= '<option value="exportcsv">'.$local_grid->Translate("Export").' csv</option>';
					$optionOnchange .= 'if(this.value==\'export\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("Export").'\';}';
				}
				if($this->multiEditFlag == 1){
					$option .= '<option value="multiEdit">'.$local_grid->Translate("Multi Edit").'</option>';
					$optionOnchange .= 'if(this.value==\'multiEdit\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("Multi Edit").'\';}';
				}
				if($this->deleteFlag == 1){
					$option .= '<option value="delete">'.$local_grid->Translate("Delete").'</option>';
					$optionOnchange .= 'if(this.value==\'delete\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("delete").'\';}';				
				}
				if($table == "campaigndialedlist"){
					$option .= '<option value="recycle">'.$local_grid->Translate("recycle").'</option>';
					$optionOnchange .= 'if(this.value==\'recycle\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("recycle").'\';}';
				}
				if($table == "customer"){
					global $config;
					
					if($config['system']['customer_leads'] == 'move'||$config['system']['customer_leads'] == 'default_move') {
						$option .= '<option value="move_to_customerleads">'.$local_grid->Translate("move_to_customerleads").'</option>';
						$optionOnchange .= 'if(this.value==\'move_to_customerleads\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("move_to_customerleads").'\';}';
					} else if($config['system']['customer_leads'] == 'copy'||$config['system']['customer_leads'] == 'default_copy'){
						$option .= '<option value="copy_to_customerleads">'.$local_grid->Translate("copy_to_customerleads").'</option>';
						$optionOnchange .= 'if(this.value==\'copy_to_customerleads\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("copy_to_customerleads").'\';}';
					}
					
				}
				$optionOnchange .= 'if(this.value==\'\'){document.getElementById(\'searchButton\').value=\''.$local_grid->Translate("continue").'\';}';
				$this->search .='<td>
									<select name="optionFlag" id="optionFlag" onchange="'.$optionOnchange.'"> '.$option.'</select></td>';
									//echo $option;exit;
			}
		}
			$this->search .='</tr>

		</table></form>';
	}

	/**
	* Add the footer of the table (Grid), that its contains the record information such as number of records, previos, next and final,  totals records, etc. Each one with its link when it is posible.
	*
	*/

	function setFooter(){
		global $local_grid;
		$next_rows = $this->start + $this->limit;
		$previos_rows = $this->start - $this->limit;
		if($next_rows>$this->numRows) $next_rows = $this->numRows;
		if($previos_rows<0)$previos_rows = 0;
		if($this->numRows < 1) $this->start = -1;
		$this->footer = '</table>';
		$this->footer .= '
		<table class="adminlist">
			<tr>
				<th colspan="'.$this->n_cols.'">
					<span class="pagenav">';
					if($this->start>0){
						$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
						document.getElementById(\'numRows\').value = 0;
						document.getElementById(\'limit\').value='.$this->limit.';
						xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),0,'.$this->limit.');return false;">'.$local_grid->Translate("first").'</a>';
					}else{
						$this->footer .= $local_grid->Translate("first");
					}
					$this->footer .= '</span>
					<span class="pagenav">';

					if($this->start >0){
					$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
						document.getElementById(\'numRows\').value = '.$previos_rows.';
						document.getElementById(\'limit\').value='.$this->limit.';
						xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),'.$previos_rows.','.$this->limit.');
						return false;">'.$local_grid->Translate("previous").'</a>';
					}else{
						$this->footer .= $local_grid->Translate("previous");
					}
					$this->footer .= '
					</span>
					<span class="pagenav">';

					$this->footer .= ' [ ' . ($this->start+1) . ' / ' . $next_rows .$local_grid->Translate("total"). $this->numRows .' ] ';

					$this->footer .= '
					</span>
					<span class="pagenav">';

					if($next_rows < $this->numRows){
						$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
						document.getElementById(\'numRows\').value = '.$next_rows.';
						document.getElementById(\'limit\').value='.$this->limit.';
						xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),'.$next_rows.','.$this->limit.');return false;">'.$local_grid->Translate("next").'</a>';
					}else{
						$this->footer .= $local_grid->Translate("next");
					}

					$this->footer .= ' </span>
					<span class="pagenav">';

					if($next_rows < $this->numRows){
						$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
						document.getElementById(\'numRows\').value = '.($this->numRows - $this->limit).';
						document.getElementById(\'limit\').value='.$this->limit.';
						xajax_searchFormSubmit(xajax.getFormValues(\'searchForm\'),'.($this->numRows - $this->limit).','.$this->limit.');return false;">'.$local_grid->Translate("last").'</a>';
					}else{

						$this->footer .= $local_grid->Translate("last").'</span>';
					}
				$this->footer .= '
				</th>
			</tr>
		</table>';
		$this->footer .= $this->search = '
			<table width="99%" border="0">
			<tr>
				<td width="25%" align="left">&nbsp;</td>
				<td width="50%" align="center"><div id="msgZone">&nbsp;</div></td>
				<td width="25%" align="right">
					<button id="submitButton" onClick="xajax_showGrid(0,'.MAXROWSXPAGE.');return false;">'.$local_grid->Translate("show_all").'</button>
				</td>
			</tr>
		</table>';

	}

	function setSpecFooter($type,$customerid=0){
		global $local_grid;
		$pageNav = true;
		$next_rows = $this->start + $this->limit;
		$previos_rows = $this->start - $this->limit;
		if($next_rows>$this->numRows) $next_rows = $this->numRows;
		if($previos_rows<0)$previos_rows = 0;
		if($this->numRows < 1) $this->start = -1;
		if($type == 'mycdr') {
			$arg = 'cdrtype';
			$form = 'searchCdrForm';
			$submit = 'searchCdrFormSubmit';
		}elseif($type == 'diallist'){
			$arg = 'userexten';
			$form = 'searchDiallistForm';
			$submit = 'searchDiallistFormSubmit';
		}elseif($type == 'monitorrecord'){
			$arg = 'non';
			$form = 'searchRecordsForm';
			$submit = 'searchRecordsFormSubmit';
		}elseif($type == 'formCurTickets'){
			$arg = 'ticket_details';
			$form = 'searchTicketsForm';
			$submit = 'searchTicketsFormSubmit';
		}elseif($type == 'formMyTickets'){
			$arg = 'ticketCustomerdetails';
			$form = 'searchMyTicketsForm';
			$submit = 'searchMyTicketsFormSubmit';
		}elseif($type == 'agents'){
		}elseif($type == 'diallist_dup'){
			$pageNav = false;
		}
		$this->footer = '</table>';
		$this->footer .= '<table class="adminlist">';
		
		if($this->divName == 'formDiallistPannel') $pageNav = false;
		if($pageNav){
			$this->footer .= '<tr>
					<th colspan="'.$this->n_cols.'">
						<span class="pagenav">';
						if($this->start>0){
							$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
							document.getElementById(\'numRows\').value = 0;
							document.getElementById(\'limit\').value='.$this->limit.';';
							
							if ($arg != 'cdrtype' && $arg != 'ticket_details' && $arg != 'ticketCustomerdetails')
								$this->footer .= 'document.getElementById(\'customerid\').value = \''.$this->customerid.'\';';
							if($arg != 'non' && $arg != 'cdrtype'&& $arg != 'ticket_details' && $arg != 'ticketCustomerdetails') {
								$this->footer .= 'document.getElementById(\''.$arg.'\').value=\''.$arg.'\';';
							}
							if ($arg == 'cdrtype'){
								$this->footer .= $submit.'(\'recent\',0,'.$this->limit.');return false;">'.$local_grid->Translate("first").'</a>';
							} else if($arg == 'ticketCustomerdetails') {
								$this->footer .= $submit.'('.$customerid.',0,'.$this->limit.');return false;">'.$local_grid->Translate("first").'</a>';
							}else{
								$this->footer .= $submit.'(xajax.getFormValues(\''.$form.'\'),0,'.$this->limit.');return false;">'.$local_grid->Translate("first").'</a>';
							}
						}else{
							$this->footer .= $local_grid->Translate("first");
						}
						$this->footer .= '</span>
						<span class="pagenav">';

						if($this->start >0){
						$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
							document.getElementById(\'numRows\').value = '.$previos_rows.';
							document.getElementById(\'limit\').value='.$this->limit.';';
							if ($arg != 'cdrtype' && $arg != 'ticket_details' && $arg != 'ticketCustomerdetails')
								$this->footer .= 'document.getElementById(\'customerid\').value = \''.$this->customerid.'\';';
							if($arg != 'non' && $arg != 'cdrtype'&& $arg != 'ticket_details' && $arg != 'ticketCustomerdetails') {
								$this->footer .='document.getElementById(\''.$arg.'\').value=\''.$arg.'\';';
							}
							if ($arg == 'cdrtype'){
								$this->footer .=$submit.'(\'recent\','.$previos_rows.','.$this->limit.');
								return false;">'.$local_grid->Translate("previous").'</a>';
							} else if($arg == 'ticketCustomerdetails') {
								$this->footer .= $submit.'('.$customerid.','.$previos_rows.','.$this->limit.');return false;">'.$local_grid->Translate("previous").'</a>';
							}else{
								$this->footer .=$submit.'(xajax.getFormValues(\''.$form.'\'),'.$previos_rows.','.$this->limit.');
								return false;">'.$local_grid->Translate("previous").'</a>';
							}
						}else{
							$this->footer .= $local_grid->Translate("previous");
						}
						$this->footer .= '
						</span>
						<span class="pagenav">';

						$this->footer .= ' [ ' . ($this->start+1) . ' / ' . $next_rows .$local_grid->Translate("total"). $this->numRows .' ] ';

						$this->footer .= '
						</span>
						<span class="pagenav">';

						if($next_rows < $this->numRows){
							$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
							document.getElementById(\'numRows\').value = '.$next_rows.';
							document.getElementById(\'limit\').value='.$this->limit.';';
							if ($arg != 'cdrtype' && $arg != 'ticket_details' && $arg != 'ticketCustomerdetails')
							$this->footer .= 'document.getElementById(\'customerid\').value = \''.$this->customerid.'\';';
							if($arg != 'non' && $arg != 'cdrtype'&& $arg != 'ticket_details' && $arg != 'ticketCustomerdetails') {
								$this->footer .='document.getElementById(\''.$arg.'\').value=\''.$arg.'\';';
							}
							if ($arg == 'cdrtype')	
								$this->footer .=$submit.'(\'recent\','.$next_rows.','.$this->limit.');return false;">'.$local_grid->Translate("next").'</a>';
							else if($arg == 'ticketCustomerdetails') 
								$this->footer .= $submit.'('.$customerid.','.$next_rows.','.$this->limit.');return false;">'.$local_grid->Translate("next").'</a>';
							else
								$this->footer .=$submit.'(xajax.getFormValues(\''.$form.'\'),'.$next_rows.','.$this->limit.');return false;">'.$local_grid->Translate("next").'</a>';
						}else{
							$this->footer .= $local_grid->Translate("next");
						}

						$this->footer .= ' </span>
						<span class="pagenav">';

						if($next_rows < $this->numRows){
							$this->footer .= '<a href="?" onClick="document.getElementById(\'submitButton\').value=\''.$local_grid->Translate("continue").'\';document.getElementById(\'optionFlag\').value=\'\';
							document.getElementById(\'numRows\').value = '.($this->numRows - $this->limit).';
							document.getElementById(\'limit\').value='.$this->limit.';';
							if ($arg != 'cdrtype' && $arg != 'ticket_details' && $arg != 'ticketCustomerdetails')
							$this->footer .= 'document.getElementById(\'customerid\').value = \''.$this->customerid.'\';';
							if($arg != 'non' && $arg != 'cdrtype'&& $arg != 'ticket_details' && $arg != 'ticketCustomerdetails') {
								$this->footer .='document.getElementById(\''.$arg.'\').value=\''.$arg.'\';';
							}
							if ($arg == 'cdrtype')
								$this->footer .=$submit.'(\'recent\','.($this->numRows - $this->limit).','.$this->limit.');return false;">'.$local_grid->Translate("last").'</a>';
							else if($arg == 'ticketCustomerdetails') 
								$this->footer .= $submit.'('.$customerid.','.($this->numRows - $this->limit).','.$this->limit.');return false;">'.$local_grid->Translate("last").'</a>';
							else
								$this->footer .=$submit.'(xajax.getFormValues(\''.$form.'\'),'.($this->numRows - $this->limit).','.$this->limit.');return false;">'.$local_grid->Translate("last").'</a>';
						}else{

							$this->footer .= $local_grid->Translate("last").'</span>';
						}
		}else{
			$this->footer .='<th>&nbsp;';
		}
				$this->footer .= '
				</th>
			</tr>
		</table>';
		if($type == 'diallist_dup'){
			$this->footer .= $this->search = '
				<table width="99%" border="0">
				<tr>
					<td width="100%" align="right">
						<button id="submitButton" onClick="if(confirm(\''.$local_grid->Translate("Are you sure clear those duplicate records").'?\')){xajax_clearDuplicates(xajax.$(\'curdupdate\').value);}else{return false;}">'.$local_grid->Translate("Clear Duplicates").'</button>
						<button id="submitButton" onClick="xajax_exportDuplicates(xajax.$(\'curdupdate\').value);return false;">'.$local_grid->Translate("Export Duplicates").'</button>
					</td>
				</tr>
			</table>';
		}
//echo $this->footer;exit;
	}

	/**
	* It combines the variables $this->search . $this->top . $this->header . $this->rows . $this->footer to create the table with the data.
	*
	*/

	function render($arg=''){
		if($arg!=''){
			if($arg=='static'){
				$table =$this->top . $this->header . $this->rows . $this->footer;
			}elseif($arg=='delGrid'){
				$table = $this->search .'<form name="delGrid" id="delGrid" method="post">'. $this->top . $this->header . $this->rows .'</form>'. $this->footer;
			}
		}else{
			$table = $this->search . $this->top . $this->header . $this->rows . $this->footer;
		}

		return $table;
	}

}

/**
 * Class Table for general intentions
 *
 * @package XajaxGrid
 */
class Table {

	/**
	* Headers of table
	* @param string 	$tableTitle		Title of table
	* @return string
	*/
	function Top($tableTitle = "tableTitle", $formId = "formDiv"){
		
		if($formId == "formDiallistPannel" ){
			$table = '
			<table width="100%" border="1" align="center" class="adminlist" >
			<tr class="drsMoveHandle">
				<th align="right" valign="center" >
					<img src="skin/default/images/close.png" onClick=\'javascript: document.getElementById("dpnShow").value = 0;document.getElementById("'.$formId.'").style.visibility="hidden";document.getElementById("'.$formId.'").innerHTML = "";return false;\' title="Close Window" style="cursor: pointer; height: 16px;">
				</th>
			</tr>
			<tr ><td><fieldset><legend>'.$tableTitle.'</legend>';
		}else{
			$table = '
			<table width="100%" border="1" align="center" class="adminlist" >
			<tr class="drsMoveHandle">
				<th align="right" valign="center" >
					<img src="skin/default/images/close.png" onClick=\'javascript: document.getElementById("'.$formId.'").style.visibility="hidden";document.getElementById("'.$formId.'").innerHTML = "";return false;\' title="Close Window" style="cursor: pointer; height: 16px;">
				</th>
			</tr>
			<tr ><td><fieldset><legend>'.$tableTitle.'</legend>';
		}

		return $table;
	}

	/**
	* Footer of table
	* @return string
	*/
	function Footer(){
		$table = '
			</fieldset>
			</td></tr>
			</table><br>';
		return $table;
	}
}

/**
 * Class Table for general intentions
 *
 * @package XajaxGrid
 */
class TableA {

	/**
	* Headers of table
	* @param string 	$tableTitle		Title of table
	* @return string
	*/
	function Top($tableTitle = "tableTitle"){
		$table = '
			<table border="0" width="98%">
			<tr ><td>';

		return $table;
	}

	/**
	* Footer of table
	* @return string
	*/
	function Footer(){
		$table = '
			</td></tr>
			</table><br>';
		return $table;
	}
}

?>
