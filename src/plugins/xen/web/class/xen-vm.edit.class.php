<?php
/**
 * xen-vm Edit VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "xen_vm_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_vm_tab';
/**
* identifier name
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
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		// set ENV

		$appliance  = new appliance();
		$resource   = new resource();

		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);

		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
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
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/xen-vm-edit.tpl.php');
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_host'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg));
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
		$resource_icon_default = "/img/resource.png";
		$storage_icon = "/plugins/xen/img/plugin.png";
		$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
		if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
			$resource_icon_default = $storage_icon;
		}
		$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
		// check if we have a plugin implementing the remote console
		$remote_console = false;
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_remote_console_running = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/.running";
			$plugin_remote_console_hook = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/openqrm-".$plugin_name."-remote-console-hook.php";
			if ($this->file->exists($plugin_remote_console_hook)) {
				if ($this->file->exists($plugin_remote_console_running)) {
					$remote_console = true;
				}
			}
		}
		// prepare list of all Host resource id for the migration select
		// we need a select with the ids/ips from all resources which
		// are used by appliances with xen capabilities
		$xen_hosts = array();
		$appliance_list = new appliance();
		$appliance_list_array = $appliance_list->get_list();
		foreach ($appliance_list_array as $index => $app) {
			$appliance_xen_host_check = new appliance();
			$appliance_xen_host_check->get_instance_by_id($app["value"]);
			// only active appliances
			if ((!strcmp($appliance_xen_host_check->state, "active")) || ($appliance_xen_host_check->resources == 0)) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_xen_host_check->virtualization);
				if ((!strcmp($virtualization->type, "xen-storage")) && (!strstr($virtualization->type, "xen-vm"))) {
					$xen_host_resource = new resource();
					$xen_host_resource->get_instance_by_id($appliance_xen_host_check->resources);
					// exclude source host
					#if ($xen_host_resource->id == $this->resource->id) {
					#	continue;
					#}
					// only active appliances
					if (!strcmp($xen_host_resource->state, "active")) {
						$migration_select_label = "Res. ".$xen_host_resource->id."/".$xen_host_resource->ip;
						$xen_hosts[] = array("value"=>$xen_host_resource->id, "label"=> $migration_select_label,);
					}
				}
			}
		}

		$d['state'] = '<img width="24" height="24" src='.$state_icon.'>';
		$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
		$d['name'] = $this->appliance->name;
		$d['id'] = $this->appliance->id;

		$a = $this->response->html->a();
		$a->label   = $this->lang['action_add'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = $this->response->get_url($this->actions_name, "add");
		$d['add']   = $a->get_string();

		$body = array();
		$identifier_disabled = array();
		$file = $this->statfile;
		if($this->file->exists($file)) {				
			$lines = explode("\n", $this->file->get_contents($file));
			if(count($lines) >= 1) {
				$i = 0;
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						
						$state = $line[0];
						$name = $line[1];
						$mac = $line[2];

						$resource = new resource();
						$resource->get_instance_by_mac($mac);

						$res_virtualization = new virtualization();
						$res_virtualization->get_instance_by_id($resource->vtype);
						if (strcmp($res_virtualization->type, 'xen-vm')) {
							continue;
						}

						$update = '';
						if($state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_update'];
							$a->label   = $this->lang['action_update'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "update").'&vm='.$name;							
							$update = $a->get_string();
						}
						
						$console = '';
						if($remote_console === true && $resource->imageid !== 1 && $state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_console'];
							$a->label   = $this->lang['action_console'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'console';
							$a->href    = $this->response->get_url($this->actions_name, "console").'&vm='.$name;							
							$console    = $a->get_string();
						}
						$migrate = '';
						if(count($xen_hosts) >= 1 && $state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_migrate'];
							$a->label   = $this->lang['action_migrate'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'migrate';
							$a->href    = $this->response->get_url($this->actions_name, "migrate").'&vm='.$name.'&mac='.$mac;							
							$migrate    = $a->get_string();
						}

						$clone = '';
						if($state !== '2') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_clone'];
							$a->label   = $this->lang['action_clone'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'clone';
							$a->href    = $this->response->get_url($this->actions_name, "clone").'&vm='.$name;							
							$clone    = $a->get_string();
						}

						$network  = '<b>'.$this->lang['table_ip'].'</b>: '.$resource->ip.'<br>';
						$network .= '<b>'.$this->lang['table_mac'].'</b>: '.$mac.'<br>';
						$network .= '<b>'.$this->lang['table_vnc'].'</b>: '.$line[5];

						if($state !== '2') {						
							$hardware  = '<b>'.$this->lang['table_cpu'].'</b>: '.$line[3].'<br>';
							$hardware .= '<b>'.$this->lang['table_ram'].'</b>: '.$line[4].'<br>';
							$hardware .= '<b>'.$this->lang['table_nics'].'</b>: '.$resource->nics;
						} else {
							$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
							$identifier_disabled[] = $name;
							// progressbar
							$t->add(uniqid('b'), 'id');
							$t->add($this->openqrm->get('baseurl').'/plugins/xen/xen-stat/'.$name.'.vm_migration_progress', 'url');
							$t->add($this->lang['action_migrate_in_progress'], 'lang_in_progress');
							$t->add($this->lang['action_migrate_finished'], 'lang_finished');
							$hardware = $t->get_string();
						}

						$state = '<img src="'.$this->openqrm->get('baseurl').'/img/idle.png">';
						if($line[0] === '1') {
							$state = '<img src="'.$this->openqrm->get('baseurl').'/img/active.png">';
						}

						$body[$i] = array(
							'state' => $state,
							'icon' => '<img width="24" height="24" src="'.$this->openqrm->get('baseurl').'/plugins/xen/img/plugin.png">',
							'name' => $name,
							'resource' => $resource->id,
							'id' => $resource->id,
							'mac' => $mac,
							'cpu' => $line[3],
							'ram' => $line[4],
							'ip' => $resource->ip,
							'vnc' => $line[5],
							'network' => $network,
							'hardware' => $hardware,
							'update' => $update,
							'clone' => $clone,
						);
						if($remote_console === true) {
							$body[$i]['console'] = $console;
						}
						if(count($xen_hosts) >= 1) {
							$body[$i]['migrate'] = $migrate;
						}
					}
					$i++;
				}
			}
		}

		$h['state']['title'] = '&#160;';
		$h['state']['sortable'] = false;
		$h['icon']['title'] = '&#160;';
		$h['icon']['sortable'] = false;
		$h['name']['title'] = $this->lang['table_name'];
		$h['resource']['title'] = '<img width="24px" height="24px" src="'.$resource_icon_default.'" title="'.$this->lang['table_resource'].'">';
		$h['resource']['sortable'] = false;
		$h['id']['title'] = $this->lang['table_resource'];
		$h['id']['hidden'] = true;
		$h['ip']['title'] = $this->lang['table_ip'];
		$h['ip']['hidden'] = true;
		$h['mac']['title'] = $this->lang['table_mac'];
		$h['mac']['hidden'] = true;
		$h['vnc']['title'] = $this->lang['table_vnc'];
		$h['vnc']['hidden'] = true;
		$h['network']['title'] = $this->lang['table_network'];
		$h['network']['sortable'] = false;
		$h['hardware']['title'] = $this->lang['table_hardware'];
		$h['hardware']['sortable'] = false;
		$h['cpu']['title'] = $this->lang['table_cpu'];
		$h['cpu']['hidden'] = true;
		$h['ram']['title'] = $this->lang['table_ram'];
		$h['ram']['hidden'] = true;
		if($remote_console === true) {
			$h['console']['title'] = '&#160;';
			$h['console']['sortable'] = false;
		}
		if(count($xen_hosts) >= 1) {
			$h['migrate']['title'] = '&#160;';
			$h['migrate']['sortable'] = false;
		}
		$h['clone']['title'] = '&#160;';
		$h['clone']['sortable'] = false;
		$h['update']['title'] = '&#160;';
		$h['update']['sortable'] = false;
	
		$table = $this->response->html->tablebuilder('exports', $this->response->get_array($this->actions_name, 'edit'));
		$table->sort            = 'name';
		$table->limit           = 10;
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
		$table->form_action     = $this->response->html->thisfile;
		$table->head            = $h;
		$table->body            = $body;
		$table->identifier      = 'name';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $identifier_disabled;
		$table->actions_name    = $this->actions_name;
		$table->actions         = array(
				$this->lang['action_start'],
				$this->lang['action_stop'],
				$this->lang['action_reboot'],
				$this->lang['action_remove']
			);

		$d['table'] = $table->get_string();
		return $d;
	}

}
?>
