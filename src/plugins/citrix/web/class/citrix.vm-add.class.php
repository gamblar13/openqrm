<?php
/**
 * XenServer Hosts Add VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_vm_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_msg";
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
		$openqrm_server	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$openqrm_server->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->openqrm_server	= $openqrm_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.ds_list';
		$this->statfile_template = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.template_list';
		$this->citrix_mac_base = "00:50:56";
		$this->default_template_name = "Windows 7 (32-bit)";
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
		$t = $this->response->html->template($this->tpldir.'/citrix-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->lang['form_0_nic'], '0_nic_label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_boot'], 'lang_boot');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
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
			$name			= $form->get_request('name');
			$mac			= $form->get_request('mac');
			$template		= $form->get_request('template');
			$datastore		= $form->get_request('datastore');
			$memory			= $form->get_request('memory');
			$cpu			= $form->get_request('cpu');
			$vnc			= $form->get_request('vnc');
			$add_nics		= $form->get_request('add_nics');
			$mac1			= $form->get_request('mac1');
			$mac2			= $form->get_request('mac2');
			$mac3			= $form->get_request('mac3');
			$mac4			= $form->get_request('mac4');
			$bootorder		= $form->get_request('boot_order');

			// check - datastores exist
			if (file_exists($this->statfile_vm)) {
				$lines = explode("\n", file_get_contents($this->statfile_vm));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($name === $line[1]) {
								$error = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if (!strlen($datastore)) {
				$error = $this->lang['error_no_datastore'];
			}
			if (!strlen($template)) {
				$error = $this->lang['error_no_template'];
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
				$this->resource->send_command($this->openqrm_server->ip, "openqrm_server_add_resource ".$vm_resource_id." ".$mac." ".$vm_resource_ip);
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("citrix-vm");
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
						$vm_additional_nics = "-m1 ".$mac1;
						break;
					case '2':
						$vm_additional_nics = "-m1 ".$mac1." -m2 ".$mac2;
						break;
					case '3':
						$vm_additional_nics = "-m1 ".$mac1." -m2 ".$mac2." -m3 ".$mac3;
						break;
					case '4':
						$vm_additional_nics = "-m1 ".$mac1." -m2 ".$mac2." -m3 ".$mac3." -m4 ".$mac4;
						break;
					default:
						$vm_additional_nics = "";
						break;
				}

				// send command to create the vm
				$vnc_port = 5900 + $vm_resource_id;
				$command = $this->openqrm->get('basedir')."/plugins/citrix/bin/openqrm-citrix-vm create -i ".$this->resource->ip." -n ".$name." -l ".$datastore." -m ".$mac." -t ".$template." -c ".$cpu." -r ".$memory." ".$vm_additional_nics." -b ".$bootorder;
				$this->resource->send_command($this->openqrm_server->ip, $command);
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
						$datastore_select_arr [] = array("value" => $line[1], "label" => $line[0]);
					}
				}
			}
		}
		// get template list
		$template_select_arr = array();
		$default_template_uuid = '';
		if (file_exists($this->statfile_template)) {
			$lines = explode("\n", file_get_contents($this->statfile_template));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode(':', $line);
						$template_name = str_replace('@', ' ', $line[1]);
						if ((!strstr($template_name, "Windows")) || (strstr($template_name, "XenApp"))) {
							continue;
						}
						$template_select_arr[] = array("value" => $line[0], "label" => $template_name.' - max. CPUs: '.$line[2].' / min. Memory: '.$line[3].' / max. Memory: '.$line[4]);
						// default template
						if (strstr($template_name, $this->default_template_name)) {
							$default_template_uuid = $line[0];
						}
					}
				}
			}
		}

		// genenrate mac
		$vm_resource = new resource();
		$vm_resource->generate_mac();
		$vm_mac = strtolower($this->citrix_mac_base.":".substr($vm_resource->mac, 9));
		// 1 nic
		$vm_resource->generate_mac();
		$vm_mac1 = strtolower($this->citrix_mac_base.":".substr($vm_resource->mac, 9));
		// 2 nic
		$vm_resource->generate_mac();
		$vm_mac2 = strtolower($this->citrix_mac_base.":".substr($vm_resource->mac, 9));
		// 3 nic
		$vm_resource->generate_mac();
		$vm_mac3 = strtolower($this->citrix_mac_base.":".substr($vm_resource->mac, 9));
		// 4 nic
		$vm_resource->generate_mac();
		$vm_mac4 = strtolower($this->citrix_mac_base.":".substr($vm_resource->mac, 9));


		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['id']		= 'name';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		$d['template']['label']							= $this->lang['form_template'];
		$d['template']['object']['type']				= 'htmlobject_select';
		$d['template']['object']['attrib']['index']		= array('value', 'label');
		$d['template']['object']['attrib']['id']		= 'template';
		$d['template']['object']['attrib']['name']		= 'template';
		$d['template']['object']['attrib']['options']	= $template_select_arr;
		$d['template']['object']['attrib']['selected']	= array($default_template_uuid);

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
		$d['memory']['object']['attrib']['selected']	= array('2048');

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

		// if we do not have a ds yet show link to ds manager
		if (isset($datastore_select_arr)) {
			$d['datastore']['label']						= $this->lang['form_datastore'];
			$d['datastore']['object']['type']				= 'htmlobject_select';
			$d['datastore']['object']['attrib']['index']	= array('value', 'label');
			$d['datastore']['object']['attrib']['id']		= 'datastore';
			$d['datastore']['object']['attrib']['name']		= 'datastore';
			$d['datastore']['object']['attrib']['options']	= $datastore_select_arr;
		} else {
			$no_ds = $this->response->html->a();
			$no_ds->name = 'no_datastore';
			$no_ds->label = $this->lang['error_no_datastore'];
			$no_ds->handler = 'onclick="wait();"';
			$no_ds->href  = $this->response->get_url($this->actions_name, "ds");
			$d['datastore']['object'] = $no_ds;
		}
		
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
