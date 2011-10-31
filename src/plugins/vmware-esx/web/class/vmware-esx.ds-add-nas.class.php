<?php
/**
 * ESX Hosts Add NAS DataStore
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

class vmware_esx_ds_add_nas
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
		$response = $this->ds_add_nas();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'ds', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ds-add-nas.tpl.php');
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
	function ds_add_nas() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			global $OPENQRM_SERVER_BASE_DIR;
			$name			= $form->get_request('name');
			$ip				= $form->get_request('ip');
			$path			= $form->get_request('path');
			$command     = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx-datastore add_nas -i ".$this->resource->ip." -n ".$name." -s ".$ip." -p ".$path;

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
			if(isset($error)) {
				$response->error = $error;
			} else {
				if(file_exists($this->statfile_ds)) {
					unlink($this->statfile_ds);
				}

				// send command to add the nas
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
		$form = $response->get_form($this->actions_name, 'ds_add_nas');

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

		$d['ip']['label']							= $this->lang['form_ip'];
		$d['ip']['required']						= true;
//		$d['ip']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['ip']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['ip']['object']['type']					= 'htmlobject_input';
		$d['ip']['object']['attrib']['name']		= 'ip';
		$d['ip']['object']['attrib']['type']		= 'text';
		$d['ip']['object']['attrib']['value']		= '';
		$d['ip']['object']['attrib']['maxlength']	= 50;

		$d['path']['label']							= $this->lang['form_path'];
		$d['path']['required']						= true;
//		$d['path']['validate']['regex']				= '/^[a-z0-9._-]+$/i';
		$d['path']['validate']['errormsg']			= sprintf($this->lang['error_name'], 'a-z0-9._-');
		$d['path']['object']['type']				= 'htmlobject_input';
		$d['path']['object']['attrib']['name']		= 'path';
		$d['path']['object']['attrib']['type']		= 'text';
		$d['path']['object']['attrib']['value']		= '';
		$d['path']['object']['attrib']['maxlength']	= 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
}
?>
