<?php
/**
 * novnc Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class novnc_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'novnc_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'novnc_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "novnc_msg";
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
		$this->user     = $openqrm->user();
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
		$t = $this->response->html->template($this->tpldir.'/novnc-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->prefix_tab, 'prefix_tab');
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

		$h = array();
		$h['appliance_state']['title'] ='&#160;';
		$h['appliance_state']['sortable'] = false;
		$h['appliance_icon']['title'] ='&#160;';
		$h['appliance_icon']['sortable'] = false;
		$h['appliance_id']['title'] = $this->lang['table_id'];
		$h['appliance_name']['title'] = $this->lang['table_name'];
		$h['appliance_values']['title'] = '&#160;';
		$h['appliance_values']['sortable'] = false;
		$h['login']['title'] ='&#160;';
		$h['login']['sortable'] = false;

		$appliance = new appliance();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		$table = $this->response->html->tablebuilder('login', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->init();

		$appliances = $appliance->display_overview($table->offset, 100000, $table->sort, $table->order);
		foreach ($appliances as $index => $appliance_db) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);

			$resource = new resource();
			$resource->get_instance_by_id($appliance->resources);

			$kernel = new kernel();
			$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
			$image = new image();
			$image->get_instance_by_id($appliance_db["appliance_imageid"]);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
			$resource_icon_default="/openqrm/base/img/appliance.png";
			$active_state_icon="/openqrm/base/img/active.png";
			$inactive_state_icon="/openqrm/base/img/idle.png";
			$login = '';
			if ($appliance->stoptime == 0 || $appliance->resources == 0)  {
				$state_icon=$active_state_icon;
				// login
				$a = $this->response->html->a();
				$a->title   = $this->lang['table_login'];
				$a->label   = $this->lang['table_login'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'console';
				$a->href    = $this->response->get_url($this->actions_name, 'login').'&'.$this->identifier_name.'='.$appliance->id;
				$login      = $a->get_string();
				
			} else {
				$state_icon=$inactive_state_icon;
				$login = '';
			}
			$str = '<b>Kernel:</b> '.$kernel->name.'<br>
					<b>Image:</b> '.$image->name.'<br>
					<b>Resource:</b> '.$resource->id." / ".$resource->ip.'<br>
					<b>Type:</b> '.$virtualization->name;
			$b[] = array(
				'appliance_state' => "<img  width=24 height=24 src=$state_icon>",
				'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'appliance_id' => $appliance->id,
				'appliance_name' => $appliance->name,
				'appliance_values' => $str,
				'login' => $login,
			);
		}

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->form_action	= $this->response->html->thisfile;
		$table->autosort = true;
		$table->sort_link = false;
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d = array();
		$d['table']  = $table;
		return $d;
	}

}
?>
