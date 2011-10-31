<?php
/**
 * ESX Hosts VSwitch Manager
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

class vmware_esx_ne_select_vs
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
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vs_name';
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
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		if($appliance_id === '') {
			return false;
		}

		// set ENV
		$virtualization = new virtualization();
		$appliance    = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource   = $resource;
		$this->appliance    = $appliance;
		$this->virtualization = $virtualization;
		$this->statfile = 'vmware-esx-stat/'.$resource->ip.'.net_config';
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
		$this->init();
		$data = $this->ne_select_vs();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-ne-select-vs.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $data['vs_name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_esx'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect($this->response->get_url($this->actions_name, 'ne', $this->message_param, $msg));
		}
	}

	//--------------------------------------------
	/**
	 * VSwitch Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne_select_vs() {

		if($this->virtualization->type === 'vmware-esx') {
			$resource_icon_default="/openqrm/base/img/resource.png";
			$host_icon="/openqrm/base/plugins/vmware-esx/img/plugin.png";
			$state_icon="/openqrm/base/img/".$this->resource->state.".png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$host_icon)) {
				$resource_icon_default=$host_icon;
			}

			$d['state'] = "<img src=$state_icon>";
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->appliance->name;
			$d['id'] = $this->appliance->id;

			// get the vswitch name from the response
			$vs_name = '';
			$vswitch_arr  = $this->response->html->request()->get($this->identifier_name);
			foreach($vswitch_arr as $vs) {
				$vs_name = $vs;
				break;
			}
			// build the link to add a new portgroup to the vswitch
			$a = $this->response->html->a();
			$a->label = $this->lang['action_add_pg'];
			$a->css   = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "ne_add_vs_pg")."&vs_name=".$vs_name;
			$d['add_pg'] = $a->get_string();

			// build the link to add an uplink to the vswitch
			$a = $this->response->html->a();
			$a->label = $this->lang['action_add_up'];
			$a->handler = 'onclick="wait();"';
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "ne_add_vs_up")."&vs_name=".$vs_name;

			// not removing uplink from Portgroups on vSwitch0
			if ($vs === "vSwitch0") {
				$d['add_up'] = '';
			} else {
				$d['add_up'] = $a->get_string();
			}



			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if ($line[0] === 'pg') {
								if ($line[1] === $vs_name) {
									// build the link to add/remove an uplink to the portgroup
									// $line[5] = "yoho";
									if (strlen($line[5])) {
										$a = $this->response->html->a();
										$a->label = $this->lang['action_remove_pg_up'];
										$a->css   = 'remove';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, "ne_remove_pg_up")."&vs_name=".$vs_name."&pg_name=".$line[2]."&uplink=".$line[5];
									} else {
										$a = $this->response->html->a();
										$a->label = $this->lang['action_add_pg_up'];
										$a->css   = 'add';
										$a->handler = 'onclick="wait();"';
										$a->href  = $this->response->get_url($this->actions_name, "ne_add_pg_up")."&vs_name=".$vs_name."&pg_name=".$line[2];
									}
									$uplink_action = $a->get_string();
									// not removing uplink from Portgroups on vSwitch0
									if ($vs === "vSwitch0") {
										$uplink_action = '';
									}

									$body[] = array(
										'state' => $d['icon'],
										'pg_name'   => $line[2],
										'vs_name'   => $vs,
										'pg_vlan' => $line[3],
										'pg_ports' => $line[4],
										'pg_uplink' => $line[5],
										'action' => $uplink_action,
									);
								}
							}
						}
					}
				}
			}

			$h['state'] = array();
			$h['state']['title'] = $this->lang['table_state'];
			$h['state']['sortable'] = false;
			$h['pg_name'] = array();
			$h['pg_name']['title'] = $this->lang['table_pg_name'];
			$h['vs_name'] = array();
			$h['vs_name']['title'] = $this->lang['table_vs_name'];
			$h['pg_vlan'] = array();
			$h['pg_vlan']['title'] = $this->lang['table_pg_vlan'];
			$h['pg_ports'] = array();
			$h['pg_ports']['title'] = $this->lang['table_pg_ports'];
			$h['pg_uplink'] = array();
			$h['pg_uplink']['title'] = $this->lang['table_pg_uplink'];
			$h['action'] = array();
			$h['action']['title'] = ' ';

			$table = $this->response->html->tablebuilder('ne-vs-list', $this->response->get_array($this->actions_name, 'ne_select_vs'));
			// keep the vs_name
			$table->add_headrow("<input type='hidden' name='vs_name' value=$vs_name>");
			$table->sort            = 'name';
			$table->limit           = 20;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'pg_name';
			$table->identifier_name = $this->identifier_name;
			$table->actions_name    = $this->actions_name;
			$table->identifier_type = "radio";
			$table->actions         = array($this->lang['action_remove']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
