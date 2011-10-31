<?php
/**
 * Toggles the VMs Boot sequence
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

class vmware_esx_vm_boot
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
		$response = $this->vm_boot();
		if(!isset($response->msg)) {
			$response->msg = "Default: Opening a console for VM";
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'vm', $this->message_param, $response->msg)
		);
	}

	//--------------------------------------------
	/**
	 * VM Manager
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function vm_boot() {
		global $OPENQRM_SERVER_BASE_DIR;
		$response		= $this->get_response();
		$form			= $response->form;
		$vm_name		= $this->response->html->request()->get('vm_name');
		$vm_bootorder	= $this->response->html->request()->get('vm_bootorder');

		switch ($vm_bootorder) {
			case 'net':
				$new_vm_boot_order = "local";
				break;
			case 'local':
				$new_vm_boot_order = "net";
				break;
			default:
				$new_vm_boot_order = "net";
				break;
		}

		$command  = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx setboot_by_name -i ".$this->resource->ip." -n ".$vm_name." -b ".$new_vm_boot_order;
		if(file_exists($this->statfile_vm)) {
			unlink($this->statfile_vm);
		}
		$this->openqrm->send_command($this->openqrm->ip, $command);
		while (!file_exists($this->statfile_vm)) {
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}
		$response->msg = "Setting the boot sequence for VM ".$vm_name." to ".$new_vm_boot_order;
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
		$form = $response->get_form($this->actions_name, 'vm_boot');
		$response->form = $form;
		return $response;
	}
	
}
?>
