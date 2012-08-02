<?php
/**
 * lxc-storage-vm Edit VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class lxc_storage_vm_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lxc_storage_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lxc_storage_vm_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lxc_storage_vm_tab';
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
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/lxc-storage/web/lxc-stat/'.$resource->id.'.vm_list';
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
			$t = $this->response->html->template($this->tpldir.'/lxc-storage-vm-edit.tpl.php');
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
		$storage_icon = "/plugins/lxc-storage/img/plugin.png";
		$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
		if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
			$resource_icon_default = $storage_icon;
		}
		$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
		$d['state'] = '<img width="24" height="24" src='.$state_icon.'>';
		$d['icon'] = '<img width="24" height="24" src="'.$this->openqrm->get('baseurl').'/plugins/lxc-storage/img/plugin.png">';
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
//						$cpus = $line[5];

						$resource = new resource();
						$resource->get_instance_by_mac($mac);
						if ($resource->vhostid != $this->resource->id) {
							continue;
						}

						$update = '';
						if($state == '0') {
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_update'];
							$a->label   = $this->lang['action_update'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "update").'&vm='.$name;							
							$update = $a->get_string();
						}
						
						$clone = '';
						if($state == '0') {
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
						$hardware = '<b>'.$this->lang['table_nics'].'</b>: '.$resource->nics;
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
							'ip' => $resource->ip,
							'network' => $network,
							'hardware' => $hardware,
							'update' => $update,
							'clone' => $clone,
						);
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
		$h['network']['title'] = $this->lang['table_network'];
		$h['network']['sortable'] = false;
		$h['hardware']['title'] = $this->lang['table_hardware'];
		$h['hardware']['sortable'] = false;
		$h['nics']['title'] = $this->lang['table_nics'];
		$h['nics']['hidden'] = true;
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
