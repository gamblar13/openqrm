<?php
/**
 * Image Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'image_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";
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
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/image-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
		$h['image_icon']['title'] = '&#160;';
		$h['image_icon']['sortable'] = false;
		$h['image_id']['title'] = $this->lang['table_id'];
		$h['image_name']['title'] = $this->lang['table_name'];

		$h['image_data']['title'] = '&#160;';
		$h['image_data']['sortable'] = false;

		$h['image_version']['title'] = $this->lang['table_version'];
		$h['image_version']['hidden'] = true;

		$h['image_type']['title'] = $this->lang['table_deployment'];
		$h['image_type']['hidden'] = true;

		$h['image_isactive']['title'] = $this->lang['table_isactive'];
		$h['image_comment']['title'] = $this->lang['table_comment'];
		$h['image_comment']['sortable'] = false;
		$h['image_edit']['title'] = '&#160;';
		$h['image_edit']['sortable'] = false;

		$image = new image();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		$table = $this->response->html->tablebuilder('image', $params);
		$table->offset = 0;
		$table->sort = 'image_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max = $image->get_count();

		$table->init();

		$image_arr = $image->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$image_icon = "/openqrm/base/img/image.png";
		foreach ($image_arr as $index => $image_db) {
			// prepare the values for the array
			$image = new image();
			$image->get_instance_by_id($image_db["image_id"]);
			$image_comment = $image_db["image_comment"];
			if (!strlen($image_comment)) {
				$image_comment = "-";
			}
			$image_version = $image_db["image_version"];
			if (!strlen($image_version)) {
				$image_version = "-";
			}
			// edit
			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, 'edit').'&image_id='.$image->id;
			$image_edit = $a->get_string();

			// set the active icon
			$isactive_icon = '<img src="/openqrm/base/img/enable.png" title="'.$this->lang['lang_inactive'].'"width="24" height="24" alt="State">';
			if ($image_db["image_isactive"] == 1) {
				$isactive_icon = '<img src="/openqrm/base/img/disable.png" title="'.$this->lang['lang_active'].'"width="24" height="24" alt="State">';
			}

			// infos
			$storage = new storage();
			$storage->get_instance_by_id($image_db['image_storageid']);
			$deployment = new deployment();
			$deployment->get_instance_by_id($storage->type);
			$link = $storage->name;
			if($deployment->storagetype !== 'local-server') {
				$a = $this->response->html->a();
				$a->label   = $storage->name;
				$a->handler = 'onclick="wait();"';
				$a->href    = $this->response->html->thisfile.'?plugin='.$deployment->storagetype.'&'.str_replace('-', '_',$deployment->storagetype).'_action=edit&storage_id='.$storage->id;
				$link = $a->get_string();
			}

			$data  = '<b>'.$this->lang['table_storage'].'</b>: '.$link.'<br>';
			$data .= '<b>'.$this->lang['table_type'].'</b>: '.$image_db["image_type"].'<br>';
			$data .= '<b>'.$this->lang['table_version'].'</b>: '.$image_version;

			$b[] = array(
				'image_icon' => "<img width='24' height='24' src='".$image_icon."'>",
				'image_id' => $image_db["image_id"],
				'image_name' => $image_db["image_name"],
				'image_data' => $data,
				'image_isactive' => $isactive_icon,
				'image_comment' => $image_comment,
				'image_edit' => $image_edit,
			);
		}

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add");
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->max = $image->get_count()-2;
		$table->head = $h;
		$table->body = $b;
		$table->form_action = $this->response->html->thisfile;
//		$table->actions_name = $this->actions_name;
//		$table->actions = array($this->lang['action_remove']);
//		$table->identifier = 'image_id';
//		$table->identifier_name = $this->identifier_name;
//		$table->identifier_disabled = array(0);
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['add']    = $add->get_string();
		$d['table']  = $table;
		return $d;
	}

}
?>
