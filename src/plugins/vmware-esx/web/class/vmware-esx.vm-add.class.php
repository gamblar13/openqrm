<?php
/**
 * ESX Hosts Add VM
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

class vmware_esx_vm_add
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
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
		$appliance	= new appliance();
		$resource	= new resource();
		$openqrm	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$openqrm->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->openqrm		= $openqrm;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = 'vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = 'vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = 'vmware-esx-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
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
		$response = $this->vm_add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'vm', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->lang['form_0_nic'], '0_nic_label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_add() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			global $OPENQRM_SERVER_BASE_DIR;
			$name			= $form->get_request('name');
			$mac			= $form->get_request('mac');
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
				$lines = explode("\n", file_get_contents($this->statfile_vm));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($name === $line[0]) {
								$error = sprintf($this->lang['error_exists'], $name);
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

				// create VM resource in db
				$vm_resource = new resource();
				$vm_resource_id = openqrm_db_get_free_id('resource_id', 'resource_info');
				$vm_resource_ip = "0.0.0.0";
				// send command to the openQRM-server
				$this->resource->send_command($this->openqrm->ip, "openqrm_server_add_resource ".$vm_resource_id." ".$mac." ".$vm_resource_ip);
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("vmware-esx-vm");
				// add to openQRM database
				$resource_fields["resource_id"] = $vm_resource_id;
				$resource_fields["resource_ip"] = $vm_resource_ip;
				$resource_fields["resource_mac"] = $mac;
				$resource_fields["resource_localboot"] = 0;
				$resource_fields["resource_hostname"] = "idle".$vm_resource_id;
				$resource_fields["resource_vtype"] = $virtualization->id;
				$resource_fields["resource_vhostid"] = $this->resource->id;
				$vm_resource->add($resource_fields);

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

				// send command to create the vm
				$vnc_port = 5900 + $vm_resource_id;
				$command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx create -i ".$this->resource->ip." -n ".$name." -l ".$datastore." -d ".$disk." -m ".$mac." -t ".$type." -c ".$cpu." -r ".$memory." ".$vm_additional_nics." -va ".$vnc." -vp ".$vnc_port." -b ".$bootorder;

// echo "!!! adding ".$name." - ".$mac." - ".$type." - ".$datastore." - ".$disk." - ".$memory." - ".$cpu." :::<br>";
// echo "command : $command";

				$this->resource->send_command($this->openqrm->ip, $command);
				while (!file_exists($this->statfile_vm)) {
					usleep(10000); // sleep 10ms to unload the CPU
					clearstatcache();
				}
				$response->msg = sprintf($this->lang['msg_added'], $name);
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
		$form = $response->get_form($this->actions_name, 'vm_add');

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

		// genenrate mac
		$vm_resource = new resource();
		$vm_resource->generate_mac();
		$vm_mac = strtolower($this->vmware_mac_base.":".substr($vm_resource->mac, 9));
		// 1 nic
		$vm_resource->generate_mac();
		$vm_mac1 = strtolower($this->vmware_mac_base.":".substr($vm_resource->mac, 9));
		// 2 nic
		$vm_resource->generate_mac();
		$vm_mac2 = strtolower($this->vmware_mac_base.":".substr($vm_resource->mac, 9));
		// 3 nic
		$vm_resource->generate_mac();
		$vm_mac3 = strtolower($this->vmware_mac_base.":".substr($vm_resource->mac, 9));
		// 4 nic
		$vm_resource->generate_mac();
		$vm_mac4 = strtolower($this->vmware_mac_base.":".substr($vm_resource->mac, 9));


		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		$memory_select_arr [] = array("value" => '512', "label" => '512 MB');
		$memory_select_arr [] = array("value" => '1024', "label" => '1 GB');
		$memory_select_arr [] = array("value" => '2048', "label" => '2 GB');
		$memory_select_arr [] = array("value" => '4096', "label" => '4 GB');
		$memory_select_arr [] = array("value" => '8192', "label" => '8 GB');
		$memory_select_arr [] = array("value" => '16384', "label" => '16 GB');
		$d['memory']['label']						= $this->lang['form_memory'];
		$d['memory']['object']['type']				= 'htmlobject_select';
		$d['memory']['object']['attrib']['index']	= array('value', 'label');
		$d['memory']['object']['attrib']['id']		= 'memory';
		$d['memory']['object']['attrib']['name']	= 'memory';
		$d['memory']['object']['attrib']['options']	= $memory_select_arr;

		$cpu_select_arr [] = array("value" => '1', "label" => '1 CPU');
		$cpu_select_arr [] = array("value" => '2', "label" => '2 CPUs');
		$cpu_select_arr [] = array("value" => '4', "label" => '4 CPUs');
		$cpu_select_arr [] = array("value" => '8', "label" => '8 CPUs');
		$cpu_select_arr [] = array("value" => '16', "label" => '16 CPUs');
		$d['cpu']['label']						= $this->lang['form_cpu'];
		$d['cpu']['object']['type']				= 'htmlobject_select';
		$d['cpu']['object']['attrib']['index']	= array('value', 'label');
		$d['cpu']['object']['attrib']['id']		= 'cpu';
		$d['cpu']['object']['attrib']['name']	= 'cpu';
		$d['cpu']['object']['attrib']['options']	= $cpu_select_arr;

		$disk_select_arr [] = array("value" => '1024', "label" => '1 GB');
		$disk_select_arr [] = array("value" => '2048', "label" => '2 GB');
		$disk_select_arr [] = array("value" => '10240', "label" => '10 GB');
		$disk_select_arr [] = array("value" => '20480', "label" => '20 GB');
		$disk_select_arr [] = array("value" => '501200', "label" => '50 GB');
		$disk_select_arr [] = array("value" => '102400', "label" => '100 GB');
		$d['disk']['label']							= $this->lang['form_disk'];
		$d['disk']['object']['type']				= 'htmlobject_select';
		$d['disk']['object']['attrib']['index']		= array('value', 'label');
		$d['disk']['object']['attrib']['id']		= 'disk';
		$d['disk']['object']['attrib']['name']		= 'disk';
		$d['disk']['object']['attrib']['options']	= $disk_select_arr;

		$d['datastore']['label']						= $this->lang['form_datastore'];
		$d['datastore']['object']['type']				= 'htmlobject_select';
		$d['datastore']['object']['attrib']['index']	= array('value', 'label');
		$d['datastore']['object']['attrib']['id']		= 'datastore';
		$d['datastore']['object']['attrib']['name']		= 'datastore';
		$d['datastore']['object']['attrib']['options']	= $datastore_select_arr;

		$d['mac']['label']							= $this->lang['form_0_nic']." ".$this->lang['form_mac'];
		$d['mac']['required']						= true;
//		$d['mac']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['mac']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac']['object']['type']					= 'htmlobject_input';
		$d['mac']['object']['attrib']['id']			= 'mac';
		$d['mac']['object']['attrib']['name']		= 'mac';
		$d['mac']['object']['attrib']['type']		= 'text';
		$d['mac']['object']['attrib']['value']		= $vm_mac;
		$d['mac']['object']['attrib']['maxlength']	= 50;

		$type_select_arr [] = array("value" => 'e1000', "label" => 'Intel E1000');
		$type_select_arr [] = array("value" => 'pcnet', "label" => 'PCNet 32');
		$type_select_arr [] = array("value" => 'vmxnet3', "label" => 'VMX');
		$d['type']['label']							= $this->lang['form_type'];
		$d['type']['object']['type']				= 'htmlobject_select';
		$d['type']['object']['attrib']['index']		= array('value', 'label');
		$d['type']['object']['attrib']['id']		= 'type';
		$d['type']['object']['attrib']['name']		= 'type';
		$d['type']['object']['attrib']['options']	= $type_select_arr;

		$d['vnc']['label']							= $this->lang['form_vnc'];
		$d['vnc']['required']						= true;
		$d['vnc']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vnc']['validate']['errormsg']			= sprintf($this->lang['error_vnc'], 'a-z0-9._-');
		$d['vnc']['object']['type']					= 'htmlobject_input';
		$d['vnc']['object']['attrib']['name']		= 'vnc';
		$d['vnc']['object']['attrib']['type']		= 'password';
		$d['vnc']['object']['attrib']['value']		= 'vnc-password';
		$d['vnc']['object']['attrib']['maxlength']	= 50;


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
		$d['add_nics']['object']['attrib']['selected']	= array(1);

		$d['mac1']['label']								= $this->lang['form_1_nic']." ".$this->lang['form_mac'];
//		$d['mac1']['required']							= true;
//		$d['mac1']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac1']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac1']['object']['type']					= 'htmlobject_input';
		$d['mac1']['object']['attrib']['id']			= 'mac1';
		$d['mac1']['object']['attrib']['name']			= 'mac1';
		$d['mac1']['object']['attrib']['type']			= 'text';
		$d['mac1']['object']['attrib']['value']			= $vm_mac1;
		$d['mac1']['object']['attrib']['maxlength']		= 50;

		$d['type1']['label']							= $this->lang['form_type'];
		$d['type1']['object']['type']					= 'htmlobject_select';
		$d['type1']['object']['attrib']['index']		= array('value', 'label');
		$d['type1']['object']['attrib']['id']			= 'type1';
		$d['type1']['object']['attrib']['name']			= 'type1';
		$d['type1']['object']['attrib']['options']		= $type_select_arr;

		$d['mac2']['label']								= $this->lang['form_2_nic']." ".$this->lang['form_mac'];
//		$d['mac2']['required']							= true;
//		$d['mac2']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac2']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac2']['object']['type']					= 'htmlobject_input';
		$d['mac2']['object']['attrib']['id']			= 'mac2';
		$d['mac2']['object']['attrib']['name']			= 'mac2';
		$d['mac2']['object']['attrib']['type']			= 'text';
		$d['mac2']['object']['attrib']['value']			= $vm_mac2;
		$d['mac2']['object']['attrib']['maxlength']		= 50;

		$d['type2']['label']							= $this->lang['form_type'];
		$d['type2']['object']['type']					= 'htmlobject_select';
		$d['type2']['object']['attrib']['index']		= array('value', 'label');
		$d['type2']['object']['attrib']['id']			= 'type2';
		$d['type2']['object']['attrib']['name']			= 'type2';
		$d['type2']['object']['attrib']['options']		= $type_select_arr;

		$d['mac3']['label']								= $this->lang['form_3_nic']." ".$this->lang['form_mac'];
//		$d['mac3']['required']							= true;
//		$d['mac3']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac3']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac3']['object']['type']					= 'htmlobject_input';
		$d['mac3']['object']['attrib']['id']			= 'mac3';
		$d['mac3']['object']['attrib']['name']			= 'mac3';
		$d['mac3']['object']['attrib']['type']			= 'text';
		$d['mac3']['object']['attrib']['value']			= $vm_mac3;
		$d['mac3']['object']['attrib']['maxlength']		= 50;

		$d['type3']['label']							= $this->lang['form_type'];
		$d['type3']['object']['type']					= 'htmlobject_select';
		$d['type3']['object']['attrib']['index']		= array('value', 'label');
		$d['type3']['object']['attrib']['id']			= 'type3';
		$d['type3']['object']['attrib']['name']			= 'type3';
		$d['type3']['object']['attrib']['options']		= $type_select_arr;

		$d['mac4']['label']								= $this->lang['form_4_nic']." ".$this->lang['form_mac'];
//		$d['mac4']['required']							= true;
//		$d['mac4']['validate']['regex']					= '/^[a-z0-9._-]+$/i';
		$d['mac4']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['mac4']['object']['type']					= 'htmlobject_input';
		$d['mac4']['object']['attrib']['id']			= 'mac4';
		$d['mac4']['object']['attrib']['name']			= 'mac4';
		$d['mac4']['object']['attrib']['type']			= 'text';
		$d['mac4']['object']['attrib']['value']			= $vm_mac4;
		$d['mac4']['object']['attrib']['maxlength']		= 50;

		$d['type4']['label']							= $this->lang['form_type'];
		$d['type4']['object']['type']					= 'htmlobject_select';
		$d['type4']['object']['attrib']['index']		= array('value', 'label');
		$d['type4']['object']['attrib']['id']			= 'type4';
		$d['type4']['object']['attrib']['name']			= 'type4';
		$d['type4']['object']['attrib']['options']		= $type_select_arr;

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
	
}
?>
