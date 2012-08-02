<?php
/**
 * ESX Hosts Update VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class vmware_esx_vm_update
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
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
		$this->response->params['vm_id'] = $this->response->html->request()->get('vm_id');
		$this->response->params['vm_mac'] = $this->response->html->request()->get('vm_mac');
		$this->response->params['vm_name'] = $this->response->html->request()->get('vm_name');
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
		$vm_name = $this->response->html->request()->get('vm_name');
		if($vm_name === '') {
			return false;
		}
		$vm_mac = $this->response->html->request()->get('vm_mac');
		if($vm_mac === '') {
			return false;
		}
		$vm_id = $this->response->html->request()->get('vm_id');
		if($vm_id === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$openqrm_server	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$openqrm_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->openqrm_server = $openqrm_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
		$this->vm_name = $vm_name;
		$this->vm_mac = $vm_mac;
		$this->vm_id = $vm_id;
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
		$response = $this->vm_update();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'vm', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-update.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm_name), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_boot'], 'lang_boot');
		$t->add($this->lang['lang_password_generate'], 'lang_password_generate');
		$t->add($this->lang['lang_password_show'], 'lang_password_show');
		$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->lang['lang_vnc'], 'lang_vnc');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update VM
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_update() {

		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {
			$name			= $this->response->html->request()->get('vm_name');
			$mac			= $this->response->html->request()->get('vm_mac');
			$vm_id			= $this->response->html->request()->get('vm_id');
			$type			= $form->get_request('type');
			$datastore		= $form->get_request('datastore');
			$disk			= $form->get_request('disk');
			$memory			= $form->get_request('memory');
			$cpu			= $form->get_request('cpu');
			$vnc			= $form->get_request('vnc');
			$add_nics		= $form->get_request('add_nics');
			$mac1			= $form->get_request('mac1');
			$type1			= $form->get_request('type1');
			$mac2			= $form->get_request('mac2');
			$type2			= $form->get_request('type2');
			$mac3			= $form->get_request('mac3');
			$type3			= $form->get_request('type3');
			$mac4			= $form->get_request('mac4');
			$type4			= $form->get_request('type4');
			$bootorder		= $form->get_request('boot_order');
			// checks
			if (file_exists($this->statfile_vm)) {
				$error = sprintf($this->lang['error_not_exist'], $name);
				$lines = explode("\n", file_get_contents($this->statfile_vm));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($name === $line[0]) {
								unset($error);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_vm)) {
					unlink($this->statfile_vm);
				}
				// additional network cards
				$vm_additional_nics = "";
				switch ($add_nics) {
					case '0':
						$vm_additional_nics = "";
						break;
					case '1':
						$vm_additional_nics = "-m2 ".$mac1." -t2 ".$type1;
						break;
					case '2':
						$vm_additional_nics = "-m2 ".$mac1." -t2 ".$type1." -m3 ".$mac2." -t3 ".$type2;
						break;
					case '3':
						$vm_additional_nics = "-m2 ".$mac1." -t2 ".$type1." -m3 ".$mac2." -t3 ".$type2." -m4 ".$mac3." -t4 ".$type3;
						break;
					case '4':
						$vm_additional_nics = "-m2 ".$mac1." -t2 ".$type1." -m3 ".$mac2." -t3 ".$type2." -m4 ".$mac3." -t4 ".$type3." -m5 ".$mac4." -t5 ".$type4;
						break;
					default:
						$vm_additional_nics = "";
						break;
				}

				// send command to update the vm
				$vnc_port = 5900 + $vm_id;
				$command = $this->openqrm->get('basedir')."/plugins/vmware-esx/bin/openqrm-vmware-esx update -i ".$this->resource->ip." -n ".$name." -l ".$datastore." -d ".$disk." -m ".$mac." -t ".$type." -c ".$cpu." -r ".$memory." ".$vm_additional_nics." -va ".$vnc." -vp ".$vnc_port." -b ".$bootorder;

// echo "!!! updating ".$name." - ".$mac." - ".$type." - ".$datastore." - ".$disk." - ".$memory." - ".$cpu." :::<br>";
// echo "command : $command";

				$this->resource->send_command($this->openqrm_server->ip, $command);
				while (!file_exists($this->statfile_vm)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_updated'], $name);
			}
		}
		return $response;
	}



	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'vm_update');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		// get the datastore list for the select
		if (file_exists($this->statfile_ds)) {
			$lines = explode("\n", file_get_contents($this->statfile_ds));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						$datastore_select_arr [] = array("value" => $line[0], "label" => $line[0]);
					}
				}
			}
		}

		// get the current parameters
		$additional_nic_mac = array();
		$additional_nic_mac[1] = '';
		$additional_nic_mac[2] = '';
		$additional_nic_mac[3] = '';
		$additional_nic_mac[4] = '';
		$additional_nic_type[1] = '';
		$additional_nic_type[2] = '';
		$additional_nic_type[3] = '';
		$additional_nic_type[4] = '';
		if (file_exists($this->statfile_vm)) {
			$lines = explode("\n", file_get_contents($this->statfile_vm));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($this->vm_name === $line[0]) {
							$vm_current_cpu = $line[2];
							$vm_current_mem = $line[3];
							$vm_datastore = $line[8];
							$vm_disk = $line[9];
							$vm_vncauth = $line[10];
							// first nic type
							$first_nic_str = explode(',', $line[4]);
							$first_nic_type = $first_nic_str[1];
							switch ($first_nic_type) {
								case 'VirtualE1000':
									$first_nic_type = "e1000";
									break;
								case 'VirtualPCNet32':
									$first_nic_type = "pcnet";
									break;
								case 'VirtualVmxnet':
									$first_nic_type = "vmxnet3";
									break;
								default:
									$first_nic_type = "e1000";
									break;
							}
						
							// additional nics
							$add_nic_loop = 1;
							$add_nic_arr = explode('/', $line[5]);
							foreach($add_nic_arr as $add_nic) {
								if (strlen($add_nic)) {
									$add_one_nic = explode(',', $add_nic);
									$additional_nic_mac[$add_nic_loop] = $add_one_nic[0];
									$additional_nic_type[$add_nic_loop] = $this->translate_nic_type($add_one_nic[1]);
									$add_nic_loop++;
								}
							}
							break;

						}
					}
				}
			}
		}

		// genenrate mac
		$vm_resource = new resource();

		$d['name']['label']										= $this->lang['form_name'];
		$d['name']['static']									= true;
		$d['name']['object']['type']							= 'htmlobject_input';
		$d['name']['object']['attrib']['name']					= 'vm_name';
		$d['name']['object']['attrib']['type']					= 'text';
		$d['name']['object']['attrib']['value']					= $this->vm_name;
		$d['name']['object']['attrib']['disabled']				= true;

		$memory_select_arr [] = array("value" => '512', "label" => '512 MB');
		$memory_select_arr [] = array("value" => '1024', "label" => '1 GB');
		$memory_select_arr [] = array("value" => '2048', "label" => '2 GB');
		$memory_select_arr [] = array("value" => '4096', "label" => '4 GB');
		$memory_select_arr [] = array("value" => '8192', "label" => '8 GB');
		$memory_select_arr [] = array("value" => '16384', "label" => '16 GB');
		$d['memory']['label']									= $this->lang['form_memory'];
		$d['memory']['object']['type']							= 'htmlobject_select';
		$d['memory']['object']['attrib']['index']				= array('value', 'label');
		$d['memory']['object']['attrib']['id']					= 'memory';
		$d['memory']['object']['attrib']['name']				= 'memory';
		$d['memory']['object']['attrib']['options']				= $memory_select_arr;
		$d['memory']['object']['attrib']['selected']			= array($vm_current_mem);


		$cpu_select_arr [] = array("value" => '1', "label" => '1 CPU');
		$cpu_select_arr [] = array("value" => '2', "label" => '2 CPUs');
		$cpu_select_arr [] = array("value" => '4', "label" => '4 CPUs');
		$cpu_select_arr [] = array("value" => '8', "label" => '8 CPUs');
		$cpu_select_arr [] = array("value" => '16', "label" => '16 CPUs');
		$d['cpu']['label']										= $this->lang['form_cpu'];
		$d['cpu']['object']['type']								= 'htmlobject_select';
		$d['cpu']['object']['attrib']['index']					= array('value', 'label');
		$d['cpu']['object']['attrib']['id']						= 'cpu';
		$d['cpu']['object']['attrib']['name']					= 'cpu';
		$d['cpu']['object']['attrib']['options']				= $cpu_select_arr;
		$d['cpu']['object']['attrib']['selected']				= array($vm_current_cpu);

		$disk_select_arr [] = array("value" => '1024', "label" => '1 GB');
		$disk_select_arr [] = array("value" => '2048', "label" => '2 GB');
		$disk_select_arr [] = array("value" => '10240', "label" => '10 GB');
		$disk_select_arr [] = array("value" => '20480', "label" => '20 GB');
		$disk_select_arr [] = array("value" => '501200', "label" => '50 GB');
		$disk_select_arr [] = array("value" => '102400', "label" => '100 GB');
		$d['disk']['label']										= $this->lang['form_disk'];
		$d['disk']['object']['type']							= 'htmlobject_select';
		$d['disk']['object']['attrib']['index']					= array('value', 'label');
		$d['disk']['object']['attrib']['id']					= 'disk';
		$d['disk']['object']['attrib']['name']					= 'disk';
		$d['disk']['object']['attrib']['options']				= $disk_select_arr;
		$d['disk']['object']['attrib']['selected']				= array($vm_disk);

		$d['datastore']['label']								= $this->lang['form_datastore'];
		$d['datastore']['object']['type']						= 'htmlobject_select';
		$d['datastore']['object']['attrib']['index']			= array('value', 'label');
		$d['datastore']['object']['attrib']['id']				= 'datastore';
		$d['datastore']['object']['attrib']['name']				= 'datastore';
		$d['datastore']['object']['attrib']['options']			= $datastore_select_arr;
		$d['datastore']['object']['attrib']['selected']			= array($vm_datastore);

		$d['vm_mac']['label']									= ' ';
		$d['vm_mac']['static']									= true;
		$d['vm_mac']['object']['type']							= 'htmlobject_input';
		$d['vm_mac']['object']['attrib']['name']				= 'vm_mac';
		$d['vm_mac']['object']['attrib']['type']				= 'hidden';
		$d['vm_mac']['object']['attrib']['value']				= $this->vm_mac;
		$d['vm_mac']['object']['attrib']['maxlength']			= 50;

		$d['vm_id']['label']									= ' ';
		$d['vm_id']['static']									= true;
		$d['vm_id']['object']['type']							= 'htmlobject_input';
		$d['vm_id']['object']['attrib']['name']					= 'vm_id';
		$d['vm_id']['object']['attrib']['type']					= 'hidden';
		$d['vm_id']['object']['attrib']['value']				= $this->vm_id;
		$d['vm_id']['object']['attrib']['maxlength']			= 50;

		$d['mac']['label']										= $this->lang['form_1_nic']." ".$this->lang['form_mac'];
		$d['mac']['required']									= false;
		$d['mac']['object']['type']								= 'htmlobject_input';
		$d['mac']['object']['attrib']['id']					    = 'mac';
		$d['mac']['object']['attrib']['name']					= 'mac';
		$d['mac']['object']['attrib']['type']					= 'text';
		$d['mac']['object']['attrib']['value']					= $this->vm_mac;
		$d['mac']['object']['attrib']['disabled']				= true;

		$type_select_arr [] = array("value" => 'e1000', "label" => 'Intel E1000');
		$type_select_arr [] = array("value" => 'pcnet', "label" => 'PCNet 32');
		$type_select_arr [] = array("value" => 'vmxnet3', "label" => 'VMX');
		$d['type']['label']										= $this->lang['form_type'];
		$d['type']['object']['type']							= 'htmlobject_select';
		$d['type']['object']['attrib']['index']					= array('value', 'label');
		$d['type']['object']['attrib']['id']					= 'type';
		$d['type']['object']['attrib']['name']					= 'type';
		$d['type']['object']['attrib']['options']				= $type_select_arr;
		$d['type']['object']['attrib']['selected']				= array($first_nic_type);

		$d['vnc']['label']										= $this->lang['form_vnc'];
		$d['vnc']['required']									= true;
		$d['vnc']['validate']['regex']							= '/^[a-z0-9._-]+$/i';
		$d['vnc']['validate']['errormsg']						= sprintf($this->lang['error_vnc'], 'a-z0-9._-');
		$d['vnc']['object']['type']								= 'htmlobject_input';
		$d['vnc']['object']['attrib']['id']  					= 'vnc';
		$d['vnc']['object']['attrib']['name']					= 'vnc';
		$d['vnc']['object']['attrib']['type']					= 'password';
		$d['vnc']['object']['attrib']['value']					= $vm_vncauth;
		$d['vnc']['object']['attrib']['maxlength']				= 50;

		// additional nics
		$add_nics_select_arr [] = array("value" => '0', "label" => '0');
		$add_nics_select_arr [] = array("value" => '1', "label" => '1');
		$add_nics_select_arr [] = array("value" => '2', "label" => '2');
		$add_nics_select_arr [] = array("value" => '3', "label" => '3');
		$add_nics_select_arr [] = array("value" => '4', "label" => '4');
		$d['add_nics']['label']							= $this->lang['form_additional_nics'];
		$d['add_nics']['object']['type']				= 'htmlobject_select';
		$d['add_nics']['object']['attrib']['index']		= array('value', 'label');
		$d['add_nics']['object']['attrib']['id']		= 'add_nics';
		$d['add_nics']['object']['attrib']['name']		= 'add_nics';
		$d['add_nics']['object']['attrib']['options']	= $add_nics_select_arr;
		$d['add_nics']['object']['attrib']['selected']	= array($add_nic_loop - 1);

		$d['mac1']['label']								= $this->lang['form_1_nic']." ".$this->lang['form_mac'];
//		$d['mac1']['required']							= true;
//		$d['mac1']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac1']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac1']['object']['type']					= 'htmlobject_input';
		$d['mac1']['object']['attrib']['id']			= 'mac1';
		$d['mac1']['object']['attrib']['name']			= 'mac1';
		$d['mac1']['object']['attrib']['type']			= 'text';
		$d['mac1']['object']['attrib']['value']			= $additional_nic_mac[1];
		$d['mac1']['object']['attrib']['maxlength']		= 50;

		$d['type1']['label']							= $this->lang['form_type'];
		$d['type1']['object']['type']					= 'htmlobject_select';
		$d['type1']['object']['attrib']['index']		= array('value', 'label');
		$d['type1']['object']['attrib']['id']			= 'type1';
		$d['type1']['object']['attrib']['name']			= 'type1';
		$d['type1']['object']['attrib']['options']		= $type_select_arr;
		$d['type1']['object']['attrib']['selected']		= array($additional_nic_type[1]);

		$d['mac2']['label']								= $this->lang['form_2_nic']." ".$this->lang['form_mac'];
//		$d['mac2']['required']							= true;
//		$d['mac2']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac2']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac2']['object']['type']					= 'htmlobject_input';
		$d['mac2']['object']['attrib']['id']			= 'mac2';
		$d['mac2']['object']['attrib']['name']			= 'mac2';
		$d['mac2']['object']['attrib']['type']			= 'text';
		$d['mac2']['object']['attrib']['value']			= $additional_nic_mac[2];
		$d['mac2']['object']['attrib']['maxlength']		= 50;

		$d['type2']['label']							= $this->lang['form_type'];
		$d['type2']['object']['type']					= 'htmlobject_select';
		$d['type2']['object']['attrib']['index']		= array('value', 'label');
		$d['type2']['object']['attrib']['id']			= 'type2';
		$d['type2']['object']['attrib']['name']			= 'type2';
		$d['type2']['object']['attrib']['options']		= $type_select_arr;
		$d['type2']['object']['attrib']['selected']		= array($additional_nic_type[2]);

		$d['mac3']['label']								= $this->lang['form_3_nic']." ".$this->lang['form_mac'];
//		$d['mac3']['required']							= true;
//		$d['mac3']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac3']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac3']['object']['type']					= 'htmlobject_input';
		$d['mac3']['object']['attrib']['id']			= 'mac3';
		$d['mac3']['object']['attrib']['name']			= 'mac3';
		$d['mac3']['object']['attrib']['type']			= 'text';
		$d['mac3']['object']['attrib']['value']			= $additional_nic_mac[3];
		$d['mac3']['object']['attrib']['maxlength']		= 50;

		$d['type3']['label']							= $this->lang['form_type'];
		$d['type3']['object']['type']					= 'htmlobject_select';
		$d['type3']['object']['attrib']['index']		= array('value', 'label');
		$d['type3']['object']['attrib']['id']			= 'type3';
		$d['type3']['object']['attrib']['name']			= 'type3';
		$d['type3']['object']['attrib']['options']		= $type_select_arr;
		$d['type3']['object']['attrib']['selected']		= array($additional_nic_type[3]);

		$d['mac4']['label']								= $this->lang['form_4_nic']." ".$this->lang['form_mac'];
//		$d['mac4']['required']							= true;
//		$d['mac4']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac4']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac4']['object']['type']					= 'htmlobject_input';
		$d['mac4']['object']['attrib']['id']			= 'mac4';
		$d['mac4']['object']['attrib']['name']			= 'mac4';
		$d['mac4']['object']['attrib']['type']			= 'text';
		$d['mac4']['object']['attrib']['value']			= $additional_nic_mac[4];
		$d['mac4']['object']['attrib']['maxlength']		= 50;

		$d['type4']['label']							= $this->lang['form_type'];
		$d['type4']['object']['type']					= 'htmlobject_select';
		$d['type4']['object']['attrib']['index']		= array('value', 'label');
		$d['type4']['object']['attrib']['id']			= 'type4';
		$d['type4']['object']['attrib']['name']			= 'type4';
		$d['type4']['object']['attrib']['options']		= $type_select_arr;
		$d['type4']['object']['attrib']['selected']		= array($additional_nic_type[4]);

		// boot sequence
		$boot_order_select_arr [] = array("value" => 'net', "label" => $this->lang['form_boot_net']);
		$boot_order_select_arr [] = array("value" => 'local', "label" => $this->lang['form_boot_local']);
//		$boot_order_select_arr [] = array("value" => 'cd', "label" => 'cd');
		$d['boot_order']['label']						= $this->lang['form_boot_order'];
		$d['boot_order']['object']['type']				= 'htmlobject_select';
		$d['boot_order']['object']['attrib']['index']	= array('value', 'label');
		$d['boot_order']['object']['attrib']['id']		= 'boot_order';
		$d['boot_order']['object']['attrib']['name']	= 'boot_order';
		$d['boot_order']['object']['attrib']['options']	= $boot_order_select_arr;
		$d['boot_order']['object']['attrib']['selected']= array(1);


		$form->add($d);
		$response->form = $form;
		return $response;
	}






	function translate_nic_type($nic_type) {
		switch ($nic_type) {
			case 'VirtualE1000':
				$translated_nic_type = "e1000";
				break;
			case 'VirtualPCNet32':
				$translated_nic_type = "pcnet";
				break;
			case 'VirtualVmxnet':
				$translated_nic_type = "vmxnet3";
				break;
			default:
				$translated_nic_type = "e1000";
				break;
		}
		return $translated_nic_type;
	}


}
?>
