<?php
/**
 * OpenVZ-Storage-VM Update VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openvz_storage_vm_update
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
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'openvz_storage_vm_tab';
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
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$vm = $this->response->html->request()->get('vm');
		if($vm === '') {
			return false;
		}
		$this->vm = $vm;
		$this->response->params['vm'] = $this->vm;
		$appliance = new appliance();
		$resource  = new resource();
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
		$response = $this->update();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/openvz-storage-vm-update.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm), 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_boot'], 'lang_boot');
		$t->add($this->lang['lang_vnc'], 'lang_vnc');
		$t->add($this->lang['lang_browse'], 'lang_browse');
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->lang['lang_password_generate'], 'lang_password_generate');
		$t->add($this->lang['lang_password_show'], 'lang_password_show');
		$t->add($this->lang['lang_password_hide'], 'lang_password_hide');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->response->html->request()->get('appliance_id'), 'appliance_id');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$this->reload();
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $this->response->submit()) {

			$enabled = array();
			for($i = 1; $i < 5; $i++) {			
				$enabled[$i] = true;
				if($form->get_request('net'.$i) !== '') {
					if($form->get_request('mac'.$i) === '') {
						$form->set_error('mac'.$i, $this->lang['error_mac']);
						$enabled[$i] = false;
					}
					if($form->get_request('bridge'.$i) === '') {
						$form->set_error('bridge'.$i, $this->lang['error_bridge']);
						$enabled[$i] = false;
					}
				} else {
					$enabled[$i] = false;
				}
			}

			if(count($errors) > 0 || $form->get_errors()) {
				$response->error = join('<br>', $errors);
			} else {
				$command  = $this->openqrm->get('basedir').'/plugins/openvz-storage/bin/openqrm-openvz-storage-vm update';
				$command .= ' -n '.$this->vm;
				$command .= ' -m '.strtolower($this->response->html->request()->get('mac'));
				$command .= ' -c '.$form->get_request('cpus');
				$command .= ' -z '.$form->get_request('bridge');

				foreach($enabled as $key => $value) {
					if($value === true) {
						$command .= ' -m'.($key+1).' '.$form->get_request('mac'.$key);
						$command .= ' -z'.($key+1).' '.$form->get_request('bridge'.$key);
					}
				}
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;

				$this->resource->send_command($this->resource->ip, $command);
				$response->msg = sprintf($this->lang['msg_updated'], $this->vm);
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
		$cpus[] = array("1", "1 CPU");
		$cpus[] = array("2", "2 CPUs");
		$cpus[] = array("4", "4 CPUs");
		$cpus[] = array("8", "8 CPUs");
		$cpus[] = array("16", "16 CPUs");

//		$ram[] = array("256", "256 MB");
//		$ram[] = array("512", "512 MB");
//		$ram[] = array("1024", "1 GB");
//		$ram[] = array("2048", "2 GB");
//		$ram[] = array("4096", "4 GB");
//		$ram[] = array("8192", "8 GB");
//		$ram[] = array("16384", "16 GB");
//		$ram[] = array("32768", "32 GB");
//		$ram[] = array("65536", "64 GB");

		$file = $this->openqrm->get('basedir').'/plugins/openvz-storage/web/openvz-stat/'.$this->resource->id.'.'.$this->vm.'.vm_config';
		$data = openqrm_parse_conf($file);
		$bridges = array();
		$bridges[] = array($data['OPENQRM_OPENVZ_STORAGE_MGMT_BRIDGE'], $data['OPENQRM_OPENVZ_STORAGE_MGMT_BRIDGE']);
		$bridges[] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET1'], $data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET1']);
		$bridges[] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET2'], $data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET2']);
		$bridges[] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET3'], $data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET3']);
		$bridges[] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET4'], $data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET4']);

		$this->response->params['mac'] = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_0'];
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'update');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['static']                        = true;
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->vm;
		$d['name']['object']['attrib']['disabled']  = true;

		$d['cpus']['label']                         = $this->lang['form_cpus'];
		$d['cpus']['required']                      = true;
		$d['cpus']['object']['type']                = 'htmlobject_select';
		$d['cpus']['object']['attrib']['name']      = 'cpus';
		$d['cpus']['object']['attrib']['index']     = array(0,1);
		$d['cpus']['object']['attrib']['options']   = $cpus;
		$d['cpus']['object']['attrib']['selected']  = array($data['OPENQRM_OPENVZ_STORAGE_VM_CPUS']);

//		$d['memory']['label']                       = $this->lang['form_memory'];
//		$d['memory']['required']                    = true;
//		$d['memory']['object']['type']              = 'htmlobject_select';
//		$d['memory']['object']['attrib']['name']    = 'memory';
//		$d['memory']['object']['attrib']['index']   = array(0,1);
//		$d['memory']['object']['attrib']['options'] = $ram;
//		if($data['OPENQRM_OPENVZ_VM_RAM'] !== '') {
//			$d['memory']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_VM_RAM']);
//		}

		$d['mac']['label']                         = $this->lang['form_mac'];
		$d['mac']['static']                        = true;
		$d['mac']['object']['type']                = 'htmlobject_input';
		$d['mac']['object']['attrib']['name']      = 'dummy';
		$d['mac']['object']['attrib']['type']      = 'text';
		$d['mac']['object']['attrib']['value']     = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_0'];
		$d['mac']['object']['attrib']['maxlength'] = 50;
		$d['mac']['object']['attrib']['disabled']  = true;

		$d['bridge']['label']                        = $this->lang['form_bridge'];
		$d['bridge']['required']                     = true;
		$d['bridge']['object']['type']               = 'htmlobject_select';
		$d['bridge']['object']['attrib']['name']     = 'bridge';
		$d['bridge']['object']['attrib']['index']    = array(0,1);
		$d['bridge']['object']['attrib']['options']  = $bridges;
		$d['bridge']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_STORAGE_MGMT_BRIDGE']);

		// net 1
		$checked = false;
		if($data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_1'] !== '' ) {
			$mac = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_1'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net1']['label']                       = $this->lang['form_enable'];
		$d['net1']['object']['type']              = 'htmlobject_input';
		$d['net1']['object']['attrib']['type']    = 'checkbox';
		$d['net1']['object']['attrib']['name']    = 'net1';
		$d['net1']['object']['attrib']['value']   = 'enabled';
		$d['net1']['object']['attrib']['checked'] = $checked;

		$d['mac1']['label']                         = $this->lang['form_mac'];
		$d['mac1']['object']['type']                = 'htmlobject_input';
		$d['mac1']['object']['attrib']['name']      = 'mac1';
		$d['mac1']['object']['attrib']['type']      = 'text';
		$d['mac1']['object']['attrib']['value']     = $mac;
		$d['mac1']['object']['attrib']['maxlength'] = 50;

		$d['bridge1']['label']                       = $this->lang['form_bridge'];
		$d['bridge1']['object']['type']              = 'htmlobject_select';
		$d['bridge1']['object']['attrib']['name']    = 'bridge1';
		$d['bridge1']['object']['attrib']['index']   = array(0,1);
		$d['bridge1']['object']['attrib']['options'] = $bridges;
		if(isset($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET1']) && $checked === true) {
			$d['bridge1']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET1']);
		}

		// net 2
		$checked = false;
		if($data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_2'] !== '' ) {
			$mac = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_2'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net2']['label']                       = $this->lang['form_enable'];
		$d['net2']['object']['type']              = 'htmlobject_input';
		$d['net2']['object']['attrib']['type']    = 'checkbox';
		$d['net2']['object']['attrib']['name']    = 'net2';
		$d['net2']['object']['attrib']['value']   = 'enabled';
		$d['net2']['object']['attrib']['checked'] = $checked;

		$d['mac2']['label']                         = $this->lang['form_mac'];
		$d['mac2']['object']['type']                = 'htmlobject_input';
		$d['mac2']['object']['attrib']['name']      = 'mac2';
		$d['mac2']['object']['attrib']['type']      = 'text';
		$d['mac2']['object']['attrib']['maxlength'] = 50;
		$d['mac2']['object']['attrib']['value']     = $mac;

		$d['bridge2']['label']                       = $this->lang['form_bridge'];
		$d['bridge2']['object']['type']              = 'htmlobject_select';
		$d['bridge2']['object']['attrib']['name']    = 'bridge2';
		$d['bridge2']['object']['attrib']['index']   = array(0,1);
		$d['bridge2']['object']['attrib']['options'] = $bridges;
		if(isset($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET2']) && $checked === true) {
			$d['bridge2']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET2']);
		}

		// net 3
		$checked = false;
		if($data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_3'] !== '' ) {
			$mac = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_3'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net3']['label']                       = $this->lang['form_enable'];
		$d['net3']['object']['type']              = 'htmlobject_input';
		$d['net3']['object']['attrib']['type']    = 'checkbox';
		$d['net3']['object']['attrib']['name']    = 'net3';
		$d['net3']['object']['attrib']['value']   = 'enabled';
		$d['net3']['object']['attrib']['checked'] = $checked;

		$d['mac3']['label']                         = $this->lang['form_mac'];
		$d['mac3']['object']['type']                = 'htmlobject_input';
		$d['mac3']['object']['attrib']['name']      = 'mac3';
		$d['mac3']['object']['attrib']['type']      = 'text';
		$d['mac3']['object']['attrib']['value']     = $mac;
		$d['mac3']['object']['attrib']['maxlength'] = 50;

		$d['bridge3']['label']                       = $this->lang['form_bridge'];
		$d['bridge3']['object']['type']              = 'htmlobject_select';
		$d['bridge3']['object']['attrib']['name']    = 'bridge3';
		$d['bridge3']['object']['attrib']['index']   = array(0,1);
		$d['bridge3']['object']['attrib']['options'] = $bridges;
		if(isset($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET3']) && $checked === true) {
			$d['bridge3']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET3']);
		}

		// net 4
		$checked = false;
		if($data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_4'] !== '' ) {
			$mac = $data['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_4'];
			$checked = true;
		} else {
			$this->resource->generate_mac();
			$mac = $this->resource->mac;
		}

		$d['net4']['label']                       = $this->lang['form_enable'];
		$d['net4']['object']['type']              = 'htmlobject_input';
		$d['net4']['object']['attrib']['type']    = 'checkbox';
		$d['net4']['object']['attrib']['name']    = 'net4';
		$d['net4']['object']['attrib']['value']   = 'enabled';
		$d['net4']['object']['attrib']['checked'] = $checked;

		$d['mac4']['label']                         = $this->lang['form_mac'];
		$d['mac4']['object']['type']                = 'htmlobject_input';
		$d['mac4']['object']['attrib']['name']      = 'mac4';
		$d['mac4']['object']['attrib']['type']      = 'text';
		$d['mac4']['object']['attrib']['value']     = $mac;
		$d['mac4']['object']['attrib']['maxlength'] = 50;

		$d['bridge4']['label']                       = $this->lang['form_bridge'];
		$d['bridge4']['object']['type']              = 'htmlobject_select';
		$d['bridge4']['object']['attrib']['name']    = 'bridge4';
		$d['bridge4']['object']['attrib']['index']   = array(0,1);
		$d['bridge4']['object']['attrib']['options'] = $bridges;
		if(isset($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET4']) && $checked === true) {
			$d['bridge4']['object']['attrib']['selected'] = array($data['OPENQRM_OPENVZ_STORAGE_BRIDGE_NET4']);
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Reload
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload() {
		$command  = $this->openqrm->get('basedir').'/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_config';
		$command .=  ' -n '.$this->vm;
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		$id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$file = $this->openqrm->get('basedir').'/plugins/openvz-storage/web/openvz-stat/'.$resource->id.'.'.$this->vm.'.vm_config';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

}
?>
