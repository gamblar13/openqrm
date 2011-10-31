<?php
/**
 * ESX Hosts Add VSwitch
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

class vmware_esx_ne_add_vs
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
		$response = $this->ne_add_vs();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'ne', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ne-add-vs.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->appliance->name), 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Network add VSwitch
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ne_add_vs() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			global $OPENQRM_SERVER_BASE_DIR;
			$name			= $form->get_request('name');
			$ports			= $form->get_request('ports');
			$command     = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx-network add_vs -i ".$this->resource->ip." -n ".$name." -p ".$ports;

			if (file_exists($this->statfile_ne)) {
				$lines = explode("\n", file_get_contents($this->statfile_ne));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if ($line[0] === 'vs') {
								if($name === $line[1]) {
									$error = sprintf($this->lang['error_exists'], $name);
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
				$this->resource->send_command($this->openqrm->ip, $command);
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
		$form = $response->get_form($this->actions_name, 'ne_add_vs');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['name']['label']							= $this->lang['form_name'];
		$d['name']['required']						= true;
		$d['name']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['name']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['name']['object']['type']				= 'htmlobject_input';
		$d['name']['object']['attrib']['name']		= 'name';
		$d['name']['object']['attrib']['type']		= 'text';
		$d['name']['object']['attrib']['value']		= '';
		$d['name']['object']['attrib']['maxlength']	= 50;

		$vswitch_ports_arr[] = array("value" => '128', "label" => '128');
		$vswitch_ports_arr[] = array("value" => '256', "label" => '256');
		$d['ports']['label']						= $this->lang['form_ports'];
		$d['ports']['object']['type']				= 'htmlobject_select';
		$d['ports']['object']['attrib']['index']	= array('value', 'label');
		$d['ports']['object']['attrib']['id']		= 'ports';
		$d['ports']['object']['attrib']['name']		= 'ports';
		$d['ports']['object']['attrib']['options']	= $vswitch_ports_arr;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}
?>
