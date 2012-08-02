<?php
/**
 * openvz-storage-vm Edit VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openvz_storage_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'openvz_storage_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "openvz_storage_vm_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'openvz_storage_vm_tab';
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
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
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
		$appliance  = new appliance();
		$resource   = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/openvz-storage/web/openvz-stat/'.$resource->id.'.vm_list';
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
			$t = $this->response->html->template($this->tpldir.'/openvz-storage-vm-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
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
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
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
		$storage_icon = "/plugins/openvz-storage/img/plugin.png";
		$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
		if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
			$resource_icon_default = $storage_icon;
		}
		$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
		// prepare list of all Host resource id for the migration select
		// we need a select with the ids/ips from all resources which
		// are used by appliances with openvz capabilities
		$openvz_hosts = array();
		$appliance_list = new appliance();
		$appliance_list_array = $appliance_list->get_list();
		foreach ($appliance_list_array as $index => $app) {
			$appliance_openvz_host_check = new appliance();
			$appliance_openvz_host_check->get_instance_by_id($app["value"]);
			// only active appliances
			if ((!strcmp($appliance_openvz_host_check->state, "active")) || ($appliance_openvz_host_check->resources == 0)) {
				$virtualization = new virtualization();
				$virtualization->get_instance_by_id($appliance_openvz_host_check->virtualization);
				if ((!strcmp($virtualization->type, "openvz-storage")) && (!strstr($virtualization->type, "openvz-storage-vm"))) {
					$openvz_host_resource = new resource();
					$openvz_host_resource->get_instance_by_id($appliance_openvz_host_check->resources);
					// exclude source host
					#if ($openvz_host_resource->id == $this->resource->id) {
					#	continue;
					#}
					// only active appliances
					if (!strcmp($openvz_host_resource->state, "active")) {
						$migration_select_label = "Res. ".$openvz_host_resource->id."/".$openvz_host_resource->ip;
						$openvz_hosts[] = array("value"=>$openvz_host_resource->id, "label"=> $migration_select_label,);
					}
				}
			}
		}

		$d['state'] = '<img width="24" height="24" src='.$state_icon.'>';
		$d['icon'] = '<img width="24" height="24" src="'.$this->openqrm->get('baseurl').'/plugins/openvz-storage/img/plugin.png">';
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
						$name = $line[1];
						$state = $line[2];
						$mac = $line[3];
						$hostname = $line[4];
						$cpus = $line[5];

						$resource = new resource();
						$resource->get_instance_by_mac($mac);
						if ($resource->vhostid != $this->resource->id) {
							continue;
						}

						$update = '';
						if($state == '1') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_update'];
							$a->label   = $this->lang['action_update'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "update").'&vm='.$name;							
							$update = $a->get_string();
						}
						
						$migrate = '';
						if(count($openvz_hosts) >= 1 && $state == '1') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_migrate'];
							$a->label   = $this->lang['action_migrate'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'migrate';
							$a->href    = $this->response->get_url($this->actions_name, "migrate").'&vm='.$name.'&mac='.$mac;							
							$migrate    = $a->get_string();
						}

						$clone = '';
						if($state == '1') {
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

						if($state !== '3') {
							$hardware  = '<b>'.$this->lang['table_cpu'].'</b>: '.$cpus.'<br>';
							$hardware .= '<b>'.$this->lang['table_nics'].'</b>: '.$resource->nics;
						} else {
							$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
							$identifier_disabled[] = $name;
							// progressbar
							$t->add(uniqid('b'), 'id');
							$t->add($this->openqrm->get('baseurl').'/plugins/openvz-storage/openvz-stat/'.$name.'.vm_migration_progress', 'url');
							$t->add($this->lang['action_migrate_in_progress'], 'lang_in_progress');
							$t->add($this->lang['action_migrate_finished'], 'lang_finished');
							$hardware = $t->get_string();
						}

						$state_icon = '<img src="'.$this->openqrm->get('baseurl').'/img/idle.png">';
						if($state === '1') {
							$state_icon = '<img src="'.$this->openqrm->get('baseurl').'/img/active.png">';
						}

						$body[$i] = array(
							'state' => $state_icon,
							'icon' => $d['icon'],
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
						if(count($openvz_hosts) >= 1) {
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
		$h['nics']['title'] = $this->lang['table_nics'];
		$h['nics']['hidden'] = true;
		if(count($openvz_hosts) >= 1) {
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
