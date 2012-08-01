<?php
/**
 * Lists discovered XenServer Hosts
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_discovery_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_msg";
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->rootdir = $this->openqrm->get('webdir');
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
		$t = $this->response->html->template($this->tpldir.'/citrix-discovery-select.tpl.php');
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

		$head['xenserver_ad_state']['title'] = "&#160;";
		$head['xenserver_ad_state']['sortable'] = false;
		$head['xenserver_ad_id']['title'] = $this->lang['table_id'];
		$head['xenserver_ad_ip']['title'] = $this->lang['table_ip'];
		$head['xenserver_ad_mac']['title'] = $this->lang['table_mac'];
		$head['xenserver_ad_hostname']['title'] = $this->lang['table_hostname'];
		$head['xenserver_ad_user']['title'] = $this->lang['table_user'];
		$head['xenserver_ad_password']['title'] = $this->lang['table_password'];
		$head['xenserver_ad_comment']['title'] = $this->lang['table_comment'];

		$table = $this->response->html->tablebuilder('discovery', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $head;
		$table->sort            = 'xenserver_ad_id';
		$table->autosort        = false;
		$table->sort_link 		= false;
		$table->max		        = $this->discovery->get_count();
		$table->identifier      = 'xenserver_ad_id';
		$table->identifier_name = 'xenserver_ad_id';
		$table->identifier_type = "radio";
		$table->actions         = array('rescan', 'add', 'delete');
		$table->actions_name    = $this->actions_name;
		$table->form_action	    = $this->response->html->thisfile;
		$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
			);
		$table->init();

		$citrix_discovery_array = $this->discovery->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		foreach ($citrix_discovery_array as $index => $esx) {

			if ($esx["xenserver_ad_is_integrated"] == 0) {
				$esx_state_icon = "<img src=/openqrm/base/img/unknown.png>";
			} else {
				$esx_state_icon = "<img src=/openqrm/base/img/active.png>";
			}
			$ta[] = array(
				'xenserver_ad_state' => $esx_state_icon,
				'xenserver_ad_id' => $esx["xenserver_ad_id"],
				'xenserver_ad_ip' => $esx["xenserver_ad_ip"],
				'xenserver_ad_mac' => $esx["xenserver_ad_mac"],
				'xenserver_ad_hostname' => $esx["xenserver_ad_hostname"],
				'xenserver_ad_user' => $esx["xenserver_ad_user"],
				'xenserver_ad_password' => $esx["xenserver_ad_password"],
				'xenserver_ad_comment' => $esx["xenserver_ad_comment"],
			);
		}

		// have at least on empty row to show the actions
		if (!isset($ta)) {
			$ta = array();
			$table->actions = array('rescan');
		}
		
		$table->body = $ta;
		return $table;
	}

}
?>
