<?php
/**
 * ESX Hosts Add iSCSI DataStore
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

class vmware_esx_ds_add_iscsi
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
		$response = $this->ds_add_iscsi();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'ds', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ds-add-iscsi.tpl.php');
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
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function ds_add_iscsi() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			global $OPENQRM_SERVER_BASE_DIR;
			$name			= $form->get_request('name');
			$target			= $form->get_request('target');
			$targetip		= $form->get_request('targetip');
			$portgroup		= $form->get_request('portgroup');
			$vswitch		= $form->get_request('vswitch');
			$vmk			= $form->get_request('vmk');
			$vmkip			= $form->get_request('vmkip');
			$vmksubnet		= $form->get_request('vmksubnet');

			if (file_exists($this->statfile_ds)) {
				$lines = explode("\n", file_get_contents($this->statfile_ds));
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

			// param check
			if($target === '') {
				$error = $this->lang['error_no_target']."<br>";
			}
			if($targetip === '') {
				$error = $this->lang['error_no_targetip']."<br>";
			}
			if($portgroup === '') {
				$error = $this->lang['error_no_portgroup']."<br>";
			}
			if($vswitch === '') {
				$error = $this->lang['error_no_vswitch']."<br>";
			}
			if($vmk === '') {
				$error = $this->lang['error_no_vmk']."<br>";
			}
			if($vmkip === '') {
				$error = $this->lang['error_no_vmk_ip']."<br>";
			}
			if($vmksubnet === '') {
				$error = $this->lang['error_no_vmk_subnet']."<br>";
			}

			$command     = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx-datastore add_iscsi -i ".$this->resource->ip." -n ".$name." -t ".$target." -q ".$targetip." -g ".$portgroup." -v ".$vswitch." -k ".$vmk." -ki ".$vmkip." -ks ".$vmksubnet;
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_ds)) {
					unlink($this->statfile_ds);
				}

				// send command to add the iscsi
				$this->resource->send_command($this->openqrm->ip, $command);
				while (!file_exists($this->statfile_ds)) {
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
		$form = $response->get_form($this->actions_name, 'ds_add_iscsi');

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

		$d['target']['label']							= "Target ".$this->lang['form_name'];
		$d['target']['required']						= true;
//		$d['target']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['target']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['target']['object']['type']					= 'htmlobject_input';
		$d['target']['object']['attrib']['name']		= 'target';
		$d['target']['object']['attrib']['type']		= 'text';
		$d['target']['object']['attrib']['value']		= '';
		$d['target']['object']['attrib']['maxlength']	= 50;

		$d['targetip']['label']							= $this->lang['form_ip'];
		$d['targetip']['required']						= true;
//		$d['targetip']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['targetip']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['targetip']['object']['type']					= 'htmlobject_input';
		$d['targetip']['object']['attrib']['name']		= 'targetip';
		$d['targetip']['object']['attrib']['type']		= 'text';
		$d['targetip']['object']['attrib']['value']		= '';
		$d['targetip']['object']['attrib']['maxlength']	= 50;

		$d['portgroup']['label']							= $this->lang['form_portgroup'];
		$d['portgroup']['required']						= true;
//		$d['portgroup']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['portgroup']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['portgroup']['object']['type']					= 'htmlobject_input';
		$d['portgroup']['object']['attrib']['name']		= 'portgroup';
		$d['portgroup']['object']['attrib']['type']		= 'text';
		$d['portgroup']['object']['attrib']['value']		= '';
		$d['portgroup']['object']['attrib']['maxlength']	= 50;

		$d['vswitch']['label']							= $this->lang['form_vswitch'];
		$d['vswitch']['required']						= true;
//		$d['vswitch']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vswitch']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['vswitch']['object']['type']					= 'htmlobject_input';
		$d['vswitch']['object']['attrib']['name']		= 'vswitch';
		$d['vswitch']['object']['attrib']['type']		= 'text';
		$d['vswitch']['object']['attrib']['value']		= '';
		$d['vswitch']['object']['attrib']['maxlength']	= 50;

		$d['vmk']['label']							= $this->lang['form_vmk'];
		$d['vmk']['required']						= true;
//		$d['vmk']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vmk']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['vmk']['object']['type']					= 'htmlobject_input';
		$d['vmk']['object']['attrib']['name']		= 'vmk';
		$d['vmk']['object']['attrib']['type']		= 'text';
		$d['vmk']['object']['attrib']['value']		= '';
		$d['vmk']['object']['attrib']['maxlength']	= 50;

		$d['vmkip']['label']							= $this->lang['form_ip'];
		$d['vmkip']['required']							= true;
//		$d['vmkip']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vmkip']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['vmkip']['object']['type']					= 'htmlobject_input';
		$d['vmkip']['object']['attrib']['name']			= 'vmkip';
		$d['vmkip']['object']['attrib']['type']			= 'text';
		$d['vmkip']['object']['attrib']['value']		= '';
		$d['vmkip']['object']['attrib']['maxlength']	= 50;

		$d['vmksubnet']['label']							= $this->lang['form_subnet'];
		$d['vmksubnet']['required']							= true;
//		$d['vmksubnet']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['vmksubnet']['validate']['errormsg']				= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['vmksubnet']['object']['type']					= 'htmlobject_input';
		$d['vmksubnet']['object']['attrib']['name']			= 'vmksubnet';
		$d['vmksubnet']['object']['attrib']['type']			= 'text';
		$d['vmksubnet']['object']['attrib']['value']		= '';
		$d['vmksubnet']['object']['attrib']['maxlength']	= 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
