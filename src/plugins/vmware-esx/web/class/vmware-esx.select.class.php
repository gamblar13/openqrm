<?php
/**
 * Select ESX Hosts to manage
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
 * @author Matt Rechenburg <matt@openqrm-enterprise.com>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class vmware_esx_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
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
		$this->__rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
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
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-select.tpl.php');
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

		$virtualization = new virtualization();
		$virtualization->get_instance_by_type('vmware-esx');
		$appliance = new appliance();
                
		$head['appliance_icon']['title'] = " ";
		$head['appliance_id']['title'] = $this->lang['table_id'];
		$head['appliance_name']['title'] = $this->lang['table_name'];
		$head['appliance_comment']['title'] = $this->lang['table_comment'];
		$head['appliance_action']['title'] = " ";

		$table = $this->response->html->tablebuilder('management', $this->response->get_array($this->actions_name, 'select'));
		$table->sort            = 'appliance_id';
		$table->limit           = 20;
		$table->offset          = 0;
		$table->order           = 'ASC';
		$table->max		= $appliance->get_count_per_virtualization($virtualization->id);
		$table->autosort        = false;
		$table->sort_link       = false;
		$table->init();

		$vmware_esx_array = $appliance->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($vmware_esx_array as $index => $esx) {
			$esx_appliance_id = $esx["appliance_id"];
			$esx_state_icon = "<img src=/openqrm/base/img/active.png>";
			$edit_img = '<img border=0 src="/openqrm/base/plugins/vmware-esx/img/manager.png">';

			$a = $this->response->html->a();
			$a->title   = $this->lang['action_edit'];
			$a->label   = $this->lang['action_edit'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'select';
			$a->href    = $this->response->get_url($this->actions_name, "vm").'&appliance_id='.$esx_appliance_id;

			$ta[] = array(
				'appliance_icon' => $esx_state_icon,
				'appliance_id' => $esx["appliance_id"],
				'appliance_name' => $esx["appliance_name"],
				'appliance_comment' => $esx["appliance_comment"],
				'appliance_action' => $a->get_string(),
			);
		}
                
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'Tabelle';
		$table->head            = $head;

		$table->identifier      = 'appliance_id';
		$table->identifier_name = 'appliance_id';
		$table->actions_name    = $this->actions_name;
		$table->actions         = array($this->lang['action_host_reboot'], $this->lang['action_host_shutdown']);

		$table->body = $ta;
		return $table->get_string();
	}




}
?>
