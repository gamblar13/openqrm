<?php
/**
 * ESX Hosts Add Uplink to VSwitch
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class vmware_esx_ne_add_vs_up
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
		$vs_name = $this->response->html->request()->get('vs_name');
		if($vs_name === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$openqrm_server = new resource();
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
		$this->vs_name = $vs_name;
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
		$response = $this->ne_add_vs_up();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'ne', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ne-add-vs-up.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vs_name), 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Network add Uplink to VSwitch
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne_add_vs_up() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$uplink			= $form->get_request('uplink');
			$vs_name		= $form->get_request('vs_name');
			$command		= $this->openqrm->get('basedir')."/plugins/vmware-esx/bin/openqrm-vmware-esx-network add_vs_up -i ".$this->resource->ip." -n ".$vs_name." -u ".$uplink;

			$error = sprintf($this->lang['error_not_exists'], $vs_name);
			if (file_exists($this->statfile_ne)) {
				$lines = explode("\n", file_get_contents($this->statfile_ne));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if ($line[0] === 'vs') {
								if($vs_name === $line[1]) {
									unset($error);
									break;
								}
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_ne)) {
					unlink($this->statfile_ne);
				}

				// send command to add the nas
				$this->resource->send_command($this->openqrm_server->ip, $command);
				while (!file_exists($this->statfile_ne)) {
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
		$form = $response->get_form($this->actions_name, 'ne_add_vs_up');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		for ($i=0;$i<10;$i++) {
			$uplink_arr[] = array("value" => "vmnic".$i, "label" => "vmnic".$i);
		}
		$d['uplink']['label']						= $this->lang['form_uplink'];
		$d['uplink']['object']['type']				= 'htmlobject_select';
		$d['uplink']['object']['attrib']['index']	= array('value', 'label');
		$d['uplink']['object']['attrib']['id']		= 'uplink';
		$d['uplink']['object']['attrib']['name']	= 'uplink';
		$d['uplink']['object']['attrib']['options']	= $uplink_arr;

		$d['vs_name']['label']						= ' ';
		$d['vs_name']['object']['type']				= 'htmlobject_input';
		$d['vs_name']['object']['attrib']['name']	= 'vs_name';
		$d['vs_name']['object']['attrib']['type']	= 'hidden';
		$d['vs_name']['object']['attrib']['value']	= $this->vs_name;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}
?>
