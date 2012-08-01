<?php
/**
 * XenServer Hosts VM Manager
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_vm
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_msg";
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
		$virtualization	= new virtualization();
		$appliance		= new appliance();
		$resource		= new resource();
		$openqrm_server = new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$openqrm_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource			= $resource;
		$this->appliance		= $appliance;
		$this->virtualization	= $virtualization;
		$this->openqrm_server	= $openqrm_server;
		$this->statfile			= $this->rootdir.'/plugins/citrix-storage/citrix-storage-stat/'.$resource->ip.'.vm_list';
		$this->vnc_web_base_port	= 6000;
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
		$data = $this->vm();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/citrix-storage-vm.tpl.php');
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
			$msg = sprintf($this->lang['error_no_citrix'], $this->response->html->request()->get('appliance_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm() {

		if($this->virtualization->type === 'citrix-storage') {
			$resource_icon_default="/openqrm/base/img/resource.png";
			$host_icon="/openqrm/base/plugins/citrix-storage/img/plugin.png";
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
			$a->label   = $this->lang['action_add'];
			$a->css     = 'add';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, "vm_add");
			$d['add']   = $a->get_string();

			$body = array();
			$file = $this->statfile;
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$memory = $line[5];
							// first nic
							unset($first_mac);
							$first_mac = $line[3];
							$bootorder = $line[6];
							$bootorder_display = "net";
							switch($bootorder) {
								case 'cnd':
									$bootorder_display = "Local";
									break;
								case 'ncd':
									$bootorder_display = "Network";
									break;
							}
							// state/id/ip
							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($first_mac);
							$update = '';
							$vnc = '';
							$clone = '';
							// state
							if (!strcmp($line[2], 'running')) {
								$vm_state_icon = "/openqrm/base/img/active.png";
								// vnc access / post the domid
								$a_vnc = $this->response->html->a();
								$a_vnc->label = $this->lang['action_console'];
								$a_vnc->css   = 'console';
								$a_vnc->handler = 'onclick="wait();"';
								$a_vnc->href  = $this->response->get_url($this->actions_name, "vm_console")."&vm_name=".$line[1]."&vm_mac=".$first_mac."&vm_id=".$line[7];
								$vnc = $a_vnc->get_string();

							} else {
								$vm_state_icon = "/openqrm/base/img/off.png";
								// clone
								$a_clone = $this->response->html->a();
								$a_clone->title   = $this->lang['action_clone'];
								$a_clone->label   = $this->lang['action_clone'];
								$a_clone->handler = 'onclick="wait();"';
								$a_clone->css     = 'clone';
								$a_clone->href    = $this->response->get_url($this->actions_name, "vm_clone").'&vm_name='.$line[1];
								$clone    = $a_clone->get_string();
								// update vm
								$a_update = $this->response->html->a();
								$a_update->label = $this->lang['lang_update'];
								$a_update->css   = 'edit';
								$a_update->handler = 'onclick="wait();"';
								$a_update->href  = $this->response->get_url($this->actions_name, "vm_update")."&vm_name=".$line[1]."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
								$update = $a_update->get_string();
							}
							$vm_state_img = "<img width=16 height=16 src=$vm_state_icon>";
							
							// format network info
							$network_info_str = "<small>";
							$network_info_str .= "<nobr>MAC: ".$first_mac."</nobr><br>";
							$network_info_str .= "<nobr>IP: ".$vm_resource->ip."</nobr><br></small>";

							$body[] = array(
								'state' => $d['icon'].' '.$vm_state_img,
								'res' => $vm_resource->id,
								'name'   => $line[1],
								'cpu' => $line[4],
								'mem' => "<nobr>".$memory." MB</nobr>",
								'network' => $network_info_str,
								'boot' => $bootorder_display,
								'datastore' => $line[8],
								'console' => $vnc,
								'clone' => $clone,
								'update' => $update,
							);
						}
					}
				}
			}

			$h['state']['title'] = $this->lang['table_state'];
			$h['state']['sortable'] = false;
			$h['res']['title'] = $this->lang['table_resource'];
			$h['name']['title'] = $this->lang['table_name'];
			$h['cpu']['title'] = $this->lang['table_cpu'];
			$h['mem']['title'] = $this->lang['table_memory'];
			$h['network']['title'] = $this->lang['table_network'];
			$h['boot']['title'] = $this->lang['table_boot'];
			$h['datastore']['title'] = $this->lang['table_datastore'];
			$h['console']['title'] = '&#160;';
			$h['console']['sortable'] = false;
			$h['clone']['title'] = '&#160;';
			$h['clone']['sortable'] = false;
			$h['update']['title'] = '&#160;';
			$h['update']['sortable'] = false;

			$table = $this->response->html->tablebuilder('vm_list', $this->response->get_array($this->actions_name, 'vm'));
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
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
			$table->actions_name    = $this->actions_name;
			$table->actions         = array($this->lang['action_start'], $this->lang['action_stop'], $this->lang['action_remove']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}


}
?>
