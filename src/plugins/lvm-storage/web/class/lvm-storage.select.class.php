<?php
/**
 * lvm-Storage Select Storage
 *
 * This file is part of openQRM.
 * 
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 * 
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class lvm_storage_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function select() {
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$type = $this->response->html->request()->get('storage_type');
		if($type === '') {
			$type = 'lvm-nfs-deployment';
		}
		$deployment->get_instance_by_type($type);
		$storages = $storage->display_overview(0, $storage->get_count(), 'storage_id', 'ASC');
		if(count($storages) >= 1) {
			foreach($storages as $k => $v) {
				$storage->get_instance_by_id($v["storage_id"]);
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);
				if($deployment->storagetype === 'lvm-storage') {
					$resource_icon_default="/openqrm/base/img/resource.png";
					$storage_icon="/openqrm/base/plugins/lvm-storage/img/storage.png";
					$state_icon="/openqrm/base/img/".$resource->state.".png";
					if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
						$state_icon="/openqrm/base/img/unknown.png";
					}
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
						$resource_icon_default=$storage_icon;
					}
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_edit'];
					$a->label   = $this->lang['action_edit'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'edit';
					$a->href    = $this->response->get_url($this->actions_name, "edit").'&storage_id='.$storage->id;

					$data  = '<b>'.$this->lang['table_recource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';
					$data .= '<b>'.$this->lang['table_type'].':</b> '.$deployment->storagetype.'<br>';
					$data .= '<b>'.$this->lang['table_deployment'].':</b> '.$deployment->storagedescription.'<br>';

					$b[] = array(
						'state' => '<img src="'.$state_icon.'" alt="State">',
						'icon' => '<img width="24" height="24" src="'.$resource_icon_default.'" alt="Icon">',
						'storage_id' => $storage->id,
						'name' => $storage->name,
						'storage_resource_id' => $storage->resource_id,
						'deployment' => $deployment->storagedescription,
						'storage_data' => $data,
						'storage_comment' => '',
						'edit' => $a->get_string(),
					);
				}
			}

			$h = array();
			$h['state'] = array();
			$h['state']['title'] ='&#160;';
			$h['state']['sortable'] = false;
			$h['icon'] = array();
			$h['icon']['title'] ='&#160;';
			$h['icon']['sortable'] = false;
			$h['storage_id'] = array();
			$h['storage_id']['title'] = $this->lang['table_id'];
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['storage_resource_id'] = array();
			$h['storage_resource_id']['title'] = $this->lang['table_recource'];
			$h['storage_resource_id']['hidden'] = true;
			$h['storage_data'] = array();
			$h['storage_data']['title'] = '&#160;';
			$h['storage_data']['sortable'] = false;
			$h['deployment'] = array();
			$h['deployment']['title'] = $this->lang['table_deployment'];
			$h['deployment']['hidden'] = true;
			$h['storage_comment'] = array();
			$h['storage_comment']['title'] ='&#160;';
			$h['storage_comment']['sortable'] = false;
			$h['edit'] = array();
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

			$table = $this->response->html->tablebuilder('lvm', $this->response->get_array($this->actions_name, 'select'));
			$table->sort      = 'storage_id';
			$table->limit     = 20;
			#$table->offset    = 0;
			$table->order     = 'ASC';
			$table->max       = count($b);
			$table->autosort  = false;
			$table->sort_link = false;
			$table->autosort  = true;

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->head = $h;
			$table->body = $b;
			#$table->limit_select = array(
			#	array("value" => 10, "text" => 10),
			#	array("value" => 20, "text" => 20),
			#	array("value" => 30, "text" => 30),
			#	array("value" => 40, "text" => 40),
			#	array("value" => 50, "text" => 50),
			#);
			return $table->get_string();
		} else {
			$box = $this->response->html->div();
			$box->id = 'htmlobject_box_add_storage';
			$box->css = 'htmlobject_box';
			$box_content  = $this->lang['error_no_storage'].'<br><br>';
			$box_content .= '<a href="/openqrm/base/server/storage/storage-new.php?currenttab=tab1">'.$this->lang['new_storage'].'</a>';
			$box->add($box_content);
			return $box->get_string();
		}
	}

}
?>
