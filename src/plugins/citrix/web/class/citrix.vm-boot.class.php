<?php
/**
 * Toggles the VMs Boot sequence
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_vm_boot
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
		$this->openqrm_server		= $openqrm_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.ds_list';
		$this->citrix_mac_base = "00:50:56";
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

		$command  = $this->openqrm->get('basedir')."/plugins/citrix/bin/openqrm-citrix setboot_by_name -i ".$this->resource->ip." -n ".$vm_name." -b ".$new_vm_boot_order;
		if(file_exists($this->statfile_vm)) {
			unlink($this->statfile_vm);
		}
		$this->resource->send_command($this->openqrm_server->ip, $command);
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
