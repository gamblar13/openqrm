<?php
/**
 * KVM-Storage-VM Add new VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_vm_add
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
var $prefix_tab = 'xen_vm_tab';
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
	function __construct($openqrm, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$this->user = $openqrm->user();

		$appliance = new appliance();
		$resource  = new resource();
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
		$response = $this->add();
		if(isset($response->msg)) {
			sleep(2);
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
				$this->controller->reload();
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/xen-vm-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['lang_basic'], 'lang_basic');
		$t->add($this->lang['lang_hardware'], 'lang_hardware');
		$t->add($this->lang['lang_swap'], 'lang_swap');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_net_0'], 'lang_net_0');
		$t->add($this->lang['lang_net_1'], 'lang_net_1');
		$t->add($this->lang['lang_net_2'], 'lang_net_2');
		$t->add($this->lang['lang_net_3'], 'lang_net_3');
		$t->add($this->lang['lang_net_4'], 'lang_net_4');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		$response = $this->get_response();
		$form     = $response->form;

		// check vnc password
		$vnc = $form->get_request('vnc');
		if($vnc !== '' && $vnc !== $form->get_request('vnc_1')) {
			$form->set_error('vnc_1', $this->lang['error_vnc_password']);
		}
		if(isset($vnc) && $vnc !== '' && strlen($vnc) < 6) {
			$form->set_error('vnc', $this->lang['error_vnc_password_count']);
		}

		$iso_path = '';
		if($form->get_request('boot') !== '' && $form->get_request('boot') === 'iso') {
			if($form->get_request('iso_path') === '') {
				$form->set_error('iso_path', $this->lang['error_iso_path']);
			} else {
				$iso_path = ' -i '.$form->get_request('iso_path');
			}
		}

		if(!$form->get_errors() && $this->response->submit()) {
			$errors = array();
			$name   = $form->get_request('name');
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

			// check vm name
			if ($this->file->exists($this->statfile)) {
				$lines = explode("\n", $this->file->get_contents($this->statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$check = $line[1];
							if($name === $check) {
								$errors[] = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if(count($errors) > 0 || $form->get_errors()) {
				$response->error = join('<br>', $errors);
			} else {
				$tables = $this->openqrm->get('table');
				$resource = new resource();
				$id = openqrm_db_get_free_id('resource_id', $tables['resource']);
				$ip = "0.0.0.0";
				$mac = strtolower($form->get_request('mac'));
				// send command to the openQRM-server
				$openqrm = new openqrm_server();
				$openqrm->send_command('openqrm_server_add_resource '.$id.' '.$mac.' '.$ip);
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("xen-vm");
				// add to openQRM database
				$fields["resource_id"] = $id;
				$fields["resource_ip"] = $ip;
				$fields["resource_mac"] = $mac;
				$fields["resource_localboot"] = 0;
				$fields["resource_vtype"] = $virtualization->id;
				$fields["resource_vhostid"] = $this->resource->id;
				$this->resource->add($fields);

				$command  = $this->openqrm->get('basedir').'/plugins/xen/bin/openqrm-xen create';
				$command .= ' -n '.$name;
				$command .= ' -m '.$mac;
				$command .= ' -r '.$form->get_request('memory');
				$command .= ' -c '.$form->get_request('cpus');
				$command .= ' -z '.$form->get_request('bridge');
				$command .= ' -s '.$form->get_request('swap');

				foreach($enabled as $key => $value) {
					if($value === true) {
						$command .= ' -m'.($key+1).' '.$form->get_request('mac'.$key);
						$command .= ' -z'.($key+1).' '.$form->get_request('bridge'.$key);
					}
				}
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;

				$this->resource->send_command($this->resource->ip, $command);
				$response->resource_id = $id;
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
		$cpus[] = array("1", "1 CPU");
		$cpus[] = array("2", "2 CPUs");
		$cpus[] = array("4", "4 CPUs");
		$cpus[] = array("8", "8 CPUs");
		$cpus[] = array("16", "16 CPUs");

		$ram[] = array("256", "256 MB");
		$ram[] = array("512", "512 MB");
		$ram[] = array("1024", "1 GB");
		$ram[] = array("2048", "2 GB");
		$ram[] = array("4096", "4 GB");
		$ram[] = array("8192", "8 GB");
		$ram[] = array("16384", "16 GB");
		$ram[] = array("32768", "32 GB");
		$ram[] = array("65536", "64 GB");

		$swap[] = array("0", "0 MB");
		$swap[] = array("512", "512 MB");
		$swap[] = array("1024", "1 GB");
		$swap[] = array("2048", "2 GB");
		$swap[] = array("4096", "4 GB");
		$swap[] = array("8192", "8 GB");
		$swap[] = array("16384", "16 GB");
		$swap[] = array("32768", "32 GB");
		$swap[] = array("65536", "64 GB");

		$file = $this->openqrm->get('basedir').'/plugins/xen/web/xen-stat/'.$this->resource->id.'.bridge_config';
		$data = openqrm_parse_conf($file);
		$bridges = array();
		foreach($data as $key => $bridge) {
			$bridges[] = array($bridge, $bridge);
		}

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = '';
		$d['name']['object']['attrib']['maxlength'] = 8;

		$d['cpus']['label']                       = $this->lang['form_cpus'];
		$d['cpus']['required']                    = true;
		$d['cpus']['object']['type']              = 'htmlobject_select';
		$d['cpus']['object']['attrib']['name']    = 'cpus';
		$d['cpus']['object']['attrib']['index']   = array(0,1);
		$d['cpus']['object']['attrib']['options'] = $cpus;

		$d['memory']['label']                        = $this->lang['form_memory'];
		$d['memory']['required']                     = true;
		$d['memory']['object']['type']               = 'htmlobject_select';
		$d['memory']['object']['attrib']['name']     = 'memory';
		$d['memory']['object']['attrib']['index']    = array(0,1);
		$d['memory']['object']['attrib']['options']  = $ram;
		$d['memory']['object']['attrib']['selected'] = array(512);

		$this->resource->generate_mac();

		$d['mac']['label']                         = $this->lang['form_mac'];
		$d['mac']['required']                      = true;
		$d['mac']['object']['type']                = 'htmlobject_input';
		$d['mac']['object']['attrib']['name']      = 'mac';
		$d['mac']['object']['attrib']['type']      = 'text';
		$d['mac']['object']['attrib']['value']     = $this->resource->mac;
		$d['mac']['object']['attrib']['maxlength'] = 50;

		$d['bridge']['label']                       = $this->lang['form_bridge'];
		$d['bridge']['required']                    = true;
		$d['bridge']['object']['type']              = 'htmlobject_select';
		$d['bridge']['object']['attrib']['name']    = 'bridge';
		$d['bridge']['object']['attrib']['index']   = array(0,1);
		$d['bridge']['object']['attrib']['options'] = $bridges;

		// net 1
		$this->resource->generate_mac();

		$d['net1']['label']                     = $this->lang['form_enable'];
		$d['net1']['object']['type']            = 'htmlobject_input';
		$d['net1']['object']['attrib']['type']  = 'checkbox';
		$d['net1']['object']['attrib']['name']  = 'net1';
		$d['net1']['object']['attrib']['value'] = 'enabled';

		$d['mac1']['label']                         = $this->lang['form_mac'];
		$d['mac1']['object']['type']                = 'htmlobject_input';
		$d['mac1']['object']['attrib']['name']      = 'mac1';
		$d['mac1']['object']['attrib']['type']      = 'text';
		$d['mac1']['object']['attrib']['value']     = $this->resource->mac;
		$d['mac1']['object']['attrib']['maxlength'] = 50;

		$d['bridge1']['label']                       = $this->lang['form_bridge'];
		$d['bridge1']['object']['type']              = 'htmlobject_select';
		$d['bridge1']['object']['attrib']['name']    = 'bridge1';
		$d['bridge1']['object']['attrib']['index']   = array(0,1);
		$d['bridge1']['object']['attrib']['options'] = $bridges;

		// net 2
		$this->resource->generate_mac();

		$d['net2']['label']                     = $this->lang['form_enable'];
		$d['net2']['object']['type']            = 'htmlobject_input';
		$d['net2']['object']['attrib']['type']  = 'checkbox';
		$d['net2']['object']['attrib']['name']  = 'net2';
		$d['net2']['object']['attrib']['value'] = 'enabled';

		$d['mac2']['label']                         = $this->lang['form_mac'];
		$d['mac2']['object']['type']                = 'htmlobject_input';
		$d['mac2']['object']['attrib']['name']      = 'mac2';
		$d['mac2']['object']['attrib']['type']      = 'text';
		$d['mac2']['object']['attrib']['value']     = $this->resource->mac;
		$d['mac2']['object']['attrib']['maxlength'] = 50;

		$d['bridge2']['label']                       = $this->lang['form_bridge'];
		$d['bridge2']['object']['type']              = 'htmlobject_select';
		$d['bridge2']['object']['attrib']['name']    = 'bridge2';
		$d['bridge2']['object']['attrib']['index']   = array(0,1);
		$d['bridge2']['object']['attrib']['options'] = $bridges;

		// net 3
		$this->resource->generate_mac();

		$d['net3']['label']                     = $this->lang['form_enable'];
		$d['net3']['object']['type']            = 'htmlobject_input';
		$d['net3']['object']['attrib']['type']  = 'checkbox';
		$d['net3']['object']['attrib']['name']  = 'net3';
		$d['net3']['object']['attrib']['value'] = 'enabled';

		$d['mac3']['label']                         = $this->lang['form_mac'];
		$d['mac3']['object']['type']                = 'htmlobject_input';
		$d['mac3']['object']['attrib']['name']      = 'mac3';
		$d['mac3']['object']['attrib']['type']      = 'text';
		$d['mac3']['object']['attrib']['value']     = $this->resource->mac;
		$d['mac3']['object']['attrib']['maxlength'] = 50;

		$d['bridge3']['label']                       = $this->lang['form_bridge'];
		$d['bridge3']['object']['type']              = 'htmlobject_select';
		$d['bridge3']['object']['attrib']['name']    = 'bridge3';
		$d['bridge3']['object']['attrib']['index']   = array(0,1);
		$d['bridge3']['object']['attrib']['options'] = $bridges;

		// net 4
		$this->resource->generate_mac();

		$d['net4']['label']                     = $this->lang['form_enable'];
		$d['net4']['object']['type']            = 'htmlobject_input';
		$d['net4']['object']['attrib']['type']  = 'checkbox';
		$d['net4']['object']['attrib']['name']  = 'net4';
		$d['net4']['object']['attrib']['value'] = 'enabled';

		$d['mac4']['label']                         = $this->lang['form_mac'];
		$d['mac4']['object']['type']                = 'htmlobject_input';
		$d['mac4']['object']['attrib']['name']      = 'mac4';
		$d['mac4']['object']['attrib']['type']      = 'text';
		$d['mac4']['object']['attrib']['value']     = $this->resource->mac;
		$d['mac4']['object']['attrib']['maxlength'] = 50;

		$d['bridge4']['label']                       = $this->lang['form_bridge'];
		$d['bridge4']['object']['type']              = 'htmlobject_select';
		$d['bridge4']['object']['attrib']['name']    = 'bridge4';
		$d['bridge4']['object']['attrib']['index']   = array(0,1);
		$d['bridge4']['object']['attrib']['options'] = $bridges;

		// swap
		$d['swap']['label']                       = $this->lang['form_swap'];
		$d['swap']['required']                    = true;
		$d['swap']['object']['type']              = 'htmlobject_select';
		$d['swap']['object']['attrib']['name']    = 'swap';
		$d['swap']['object']['attrib']['id']      = 'swap';
		$d['swap']['object']['attrib']['index']   = array(0,1);
		$d['swap']['object']['attrib']['options'] = $swap;

		$form->add($d);
		$response->form = $form;

#$response->html->help($form);



		return $response;
	}

}
?>
