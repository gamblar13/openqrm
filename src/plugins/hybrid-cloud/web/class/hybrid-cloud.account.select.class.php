<?php
/**
 * hybrid_cloud_account Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class hybrid_cloud_account_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_account_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_account_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_account_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_account_tab';
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
	function __construct($openqrm, $response, $controller) {
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->controller = $controller;
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

		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-account-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
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

		$h = array();
		$h['hybrid_cloud_id']['title'] = $this->lang['table_id'];
		$h['hybrid_cloud_account_name']['title'] = $this->lang['table_name'];
		$h['hybrid_cloud_account_type']['title'] = $this->lang['table_type'];
		$h['hybrid_cloud_rc_config']['title'] = $this->lang['table_config'];
		$h['hybrid_cloud_ssh_key']['title'] = $this->lang['table_ssh'];
		$h['hybrid_cloud_description']['title'] = $this->lang['table_description'];

		$h['import']['title'] = '&#160;';
		$h['import']['sortable'] = false;
		$h['export']['title'] = '&#160;';
		$h['export']['sortable'] = false;
		$h['edit']['title'] = '&#160;';
		$h['edit']['sortable'] = false;

		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		require_once($this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$resource = new hybrid_cloud();

		$table = $this->response->html->tablebuilder('accounts', $params);
		$table->offset = 0;
		$table->sort = 'hybrid_cloud_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max = $resource->get_count('all');

		$table->init();

		$resources = $resource->display_overview($table->offset, $table->limit, $table->sort, $table->order);

		foreach ($resources as $k => $v) {

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, "edit").'&hybrid_cloud_id='.$v["hybrid_cloud_id"];

			$i = $this->response->html->a();
			$i->title   = $this->lang['action_import'];
			$i->label   = $this->lang['action_import'];
			$i->handler = 'onclick="wait();"';
			$i->css     = 'import';
			$i->href    = $this->response->get_url($this->controller->actions_name, "import").'&hybrid_cloud_id='.$v["hybrid_cloud_id"];

			$e = $this->response->html->a();
			$e->title   = $this->lang['action_export'];
			$e->label   = $this->lang['action_export'];
			$e->handler = 'onclick="wait();"';
			$e->css     = 'export';
			$e->href    = $this->response->get_url($this->controller->actions_name, "export").'&hybrid_cloud_id='.$v["hybrid_cloud_id"];

			if(!isset($v["hybrid_cloud_description"])) {
				$v["hybrid_cloud_description"] = '&#160;';
			}

			$b[] = array(
				'hybrid_cloud_id' => $v["hybrid_cloud_id"],
				'hybrid_cloud_account_name' => $v["hybrid_cloud_account_name"],
				'hybrid_cloud_account_type' => $v["hybrid_cloud_account_type"],
				'hybrid_cloud_rc_config' => $v["hybrid_cloud_rc_config"],
				'hybrid_cloud_ssh_key' => $v["hybrid_cloud_ssh_key"],
				'hybrid_cloud_description' => $v["hybrid_cloud_description"],
				'import' => $i->get_string(),
				'export' => $e->get_string(),
				'edit' => $a->get_string(),
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
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;
		$table->actions_name = $this->actions_name;
		$table->actions = array($this->lang['action_remove']);
		$table->identifier = 'hybrid_cloud_id';
		$table->identifier_name = $this->identifier_name;

		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['table'] = $table;
		$d['form']  = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']   = $add->get_string();		

		return $d;
	}




}
?>
