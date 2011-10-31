<?php
/**
 * ESX Hosts DataStore Manager
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

class vmware_esx_ds
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
		$this->statfile = 'vmware-esx-stat/'.$resource->ip.'.ds_list';
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
		$data = $this->ds();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-ds.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_esx'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * DataStore Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds() {

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

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_nas'];
			$a->css   = 'ds_add_nas';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "ds_add_nas");
			$d['ds_add_nas'] = $a->get_string();

			$a = $this->response->html->a();
			$a->label = $this->lang['action_ds_add_iscsi'];
			$a->css   = 'ds_add_iscsi';
			$a->handler = 'onclick="wait();"';
			$a->href  = $this->response->get_url($this->actions_name, "ds_add_iscsi");
			$d['ds_add_iscsi'] = $a->get_string();

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							// prepare remove link
							$remove_action = '';
							$remove_action_link = '';
							$a = $this->response->html->a();
							if (!strcmp($line[2], "NFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->css   = 'ds_remove_nas';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'ds_remove_nas')."&name=".$line[0];
							}
							if (!strcmp($line[2], "VMFS"))  {
								$a->label = $this->lang['action_ds_remove'];
								$a->css   = 'ds_remove_iscsi';
								$a->handler = 'onclick="wait();"';
								$a->href  = $this->response->get_url($this->actions_name, 'ds_remove_iscsi')."&name=".$line[0];
							}
							// format capacity + available
							$capacity = number_format($line[3], 2, '.', '');
							$available = number_format($line[4], 2, '.', '');

							// fill body
							$body[] = array(
								'state' => $d['icon'],
								'name'   => $line[0],
								'location' => "<nobr>".$line[1]."</nobr>",
								'filesystem' => $line[2],
								'capacity' => $capacity." GB",
								'available' => $available." GB",
								'action' => $a->get_string(),
							);
						}
					}
				}
			}

			$h['state'] = array();
			$h['state']['title'] = $this->lang['table_state'];
			$h['state']['sortable'] = false;
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['location'] = array();
			$h['location']['title'] = $this->lang['table_location'];
			$h['filesystem'] = array();
			$h['filesystem']['title'] = $this->lang['table_filesystem'];
			$h['capacity'] = array();
			$h['capacity']['title'] = $this->lang['table_capacity'];
			$h['available'] = array();
			$h['available']['title'] = $this->lang['table_available'];
			$h['action'] = array();
			$h['action']['title'] = ' ';

			$table = $this->response->html->tablebuilder('ds-list', $this->response->get_array($this->actions_name, 'ds'));
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
//			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
//			$table->actions_name    = $this->actions_name;
//			$table->actions         = array($this->lang['action_remove']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
