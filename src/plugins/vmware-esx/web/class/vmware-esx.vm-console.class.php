<?php
/**
 * ESX Hosts open VM Console
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class vmware_esx_vm_console
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
		$this->openqrm_server = $openqrm_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
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
		$response = $this->vm_console();
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
	function vm_console() {
		$response		= $this->get_response();
		$form			= $response->form;
		$vm_name		= $this->response->html->request()->get('vm_name');
		$vm_mac			= $this->response->html->request()->get('vm_mac');
		$vm_id			= $this->response->html->request()->get('vm_id');
		$vnc_port		= $vm_id;

		$GLOBALS['novnc_web_port_range_start'] = 6000;
		$GLOBALS['novnc_proxy_port_range_start'] = 6800;

		// check if we have a plugin implementing the remote console
		$plugin_remote_console_function = '';
		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		foreach ($enabled_plugins as $index => $plugin_name) {
			$plugin_remote_console_running = $this->rootdir."/plugins/".$plugin_name."/.running";
			$plugin_remote_console_hook = $this->rootdir."/plugins/".$plugin_name."/openqrm-".$plugin_name."-remote-console-hook.php";
			if (file_exists($plugin_remote_console_hook)) {
				if (file_exists($plugin_remote_console_running)) {
					require_once "$plugin_remote_console_hook";
					$plugin_remote_console_function="openqrm_".$plugin_name."_remote_console";
					$plugin_remote_console_function=str_replace("-", "_", $plugin_remote_console_function);
					break;
				}
			}
		}

		if(!strlen($plugin_remote_console_function)) {
			$response->msg = "No Remote-Console Plugin found!";
		} else {

			$plugin_remote_console_function($this->resource->ip, $vnc_port, $vm_id, $vm_mac, $vm_name);
			flush();
			$response->msg = "Opening a Console for VM ".$vm_name."/".$vm_mac."/".$vm_id;
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
		$form = $response->get_form($this->actions_name, 'vm_console');
		$response->form = $form;
		return $response;
	}
	
}
?>
