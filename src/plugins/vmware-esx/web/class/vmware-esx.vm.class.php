<?php
/**
 * ESX Hosts VM Manager
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class vmware_esx_vm
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
		$this->statfile			= $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
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
			$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm.tpl.php');
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
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm() {

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

							// first nic
							unset($first_mac);
							$first_nic_str = explode(',', $line[4]);
							$first_mac = $first_nic_str[0];
							$first_nic_type = $first_nic_str[1];
							// additional nics
							$add_nic_loop = 1;
							$add_nic_str = '<small>';
							$add_nic_arr = explode('/', $line[5]);
							foreach($add_nic_arr as $add_nic) {
								if (strlen($add_nic)) {
									$add_one_nic = explode(',', $add_nic);
									$add_nic_str .= '<nobr>'.$add_nic_loop.': '.$add_one_nic[0].'/'.$add_one_nic[1].'</nobr><br>';
									$add_nic_loop++;
								}
							}
							$add_nic_str .= '</small>';
							// state/id/ip
							$vm_resource = new resource();
							$vm_resource->get_instance_by_mac($first_mac);
							// state
							if (!strcmp($line[1], 'active')) {
								$vm_state_icon = "/openqrm/base/img/active.png";
							} else {
								$vm_state_icon = "/openqrm/base/img/off.png";
							}
							$vm_state_img = "<img width=16 height=16 src=$vm_state_icon>";
							// vnc access
							$a_vnc = $this->response->html->a();
							$a_vnc->label = $this->lang['action_console'];
							$a_vnc->css   = 'console';
							$a_vnc->handler = 'onclick="wait();"';
							$a_vnc->href  = $this->response->get_url($this->actions_name, "vm_console")."&vm_name=".$line[0]."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
							// update vm
							$a_update = $this->response->html->a();
							$a_update->label = $this->lang['lang_update'];
							$a_update->css   = 'edit';
							$a_update->handler = 'onclick="wait();"';
							$a_update->href  = $this->response->get_url($this->actions_name, "vm_update")."&vm_name=".$line[0]."&vm_mac=".$first_mac."&vm_id=".$vm_resource->id;
							// format vnc info
							$vnc_info_str = "<small>vnc:".$this->resource->ip.":".$line[11]."<br>";
							$vnc_info_str .= "Password: ".$line[10]."<br></small>";
							// format network info
							$network_info_str = "<small><nobr>NIC Type: ".$first_nic_type."</nobr><br>";
							$network_info_str .= "<nobr>MAC: ".$first_mac."</nobr><br>";
							$network_info_str .= "<nobr>IP: ".$vm_resource->ip."</nobr><br></small>";
							// toggle boot order
							$a_boot = $this->response->html->a();
							$a_boot->label = $line[12];
							$a_boot->css   = 'toggle';
							$a_boot->handler = 'onclick="wait();"';
							$a_boot->href  = $this->response->get_url($this->actions_name, "vm_boot")."&vm_name=".$line[0]."&vm_bootorder=".$line[12];
						
							$body[] = array(
								'state' => $d['icon'].' '.$vm_state_img,
								'name'   => $line[0],
								'res' => $vm_resource->id,
								'cpu' => $line[2],
								'mem' => "<nobr>".$line[3]." MB</nobr>",
								'disk' => "<nobr>".$line[9]." MB</nobr>",
								'datastore' => $line[8],
								'network' => $network_info_str,
								'add_nics' => $add_nic_str,
								'vnc' => $vnc_info_str,
								'boot' => $a_boot->get_string(),
								'console' => $a_vnc->get_string(),
								'update' => $a_update->get_string(),
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
			$h['res'] = array();
			$h['res']['title'] = $this->lang['table_resource'];
			$h['cpu'] = array();
			$h['cpu']['title'] = $this->lang['table_cpu'];
			$h['mem'] = array();
			$h['mem']['title'] = $this->lang['table_memory'];
			$h['disk'] = array();
			$h['disk']['title'] = $this->lang['table_disk'];
			$h['datastore'] = array();
			$h['datastore']['title'] = $this->lang['table_datastore'];
			$h['network'] = array();
			$h['network']['title'] = $this->lang['table_network'];
			$h['add_nics'] = array();
			$h['add_nics']['title'] = $this->lang['table_additional_nics'];
			$h['vnc'] = array();
			$h['vnc']['title'] = $this->lang['table_vnc'];
			$h['boot'] = array();
			$h['boot']['title'] = $this->lang['table_boot'];
			$h['console'] = array();
			$h['console']['title'] = ' ';
			$h['update'] = array();
			$h['update']['title'] = ' ';


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
