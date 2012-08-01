<?php
/**
 * XenServer Hosts open VM Console
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_vm_console
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
		$this->openqrm_server = $openqrm_server;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = $this->rootdir.'/plugins/citrix/citrix-stat/'.$resource->ip.'.ds_list';
		$this->citrix_mac_base = "00:50:56";
		$this->vm_vnc_console_offset = 3000;
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
			// send command to get the VM console port
			$statfile_vm_console = $this->rootdir.'/plugins/citrix/citrix-stat/'.$this->resource->ip.'.'.$vm_name.'.vm_console';
			if(file_exists($statfile_vm_console)) {
				unlink($statfile_vm_console);
			}
			$command = $this->openqrm->get('basedir')."/plugins/citrix/bin/openqrm-citrix-vm start_vnc -i ".$this->resource->ip." -n ".$vm_name." -x ".$vm_id;
			$this->resource->send_command($this->openqrm_server->ip, $command);
			while (!file_exists($statfile_vm_console)) {
				usleep(10000); // sleep 10ms to unload the CPU
				clearstatcache();
			}
			// ssh enabled correctly ? if not we get an error back in the file
			$vm_vnc_console_port = file_get_contents($statfile_vm_console);
			if (strstr($vm_vnc_console_port, "ERROR")) {
				$response->msg = $vm_vnc_console_port;
				return $response;
			}
			unlink($statfile_vm_console);
			$vm_vnc_console_port=trim($vm_vnc_console_port);
			$vm_vnc_console_port_full = $vm_vnc_console_port;
			$vm_vnc_console_port_local = $vm_vnc_console_port + $this->vm_vnc_console_offset;
			$vm_vnc_console_port = $vm_vnc_console_port - 5900;
			$vm_vnc_console_port = $vm_vnc_console_port + $this->vm_vnc_console_offset;
			$plugin_remote_console_function('localhost', $vm_vnc_console_port, $vm_id, $vm_mac, $vm_name);
			flush();

			$host_password = 'xxxxxxxx';
			$host_config = $this->openqrm->get('basedir').'/plugins/citrix/conf/host/'.$this->resource->ip.'.pwd';
			if (file_exists($host_config)) {
				$fp = @fopen($host_config, "r");
				$buffer = fgets($fp, 4096);
				$host_password = fgets($fp, 4096);
				$host_password = trim($host_password);
				fclose($fp);
			}
			$tunnel_command = $this->openqrm->get('basedir')."/plugins/citrix/bin/openqrm-citrix-tunnel ".$this->resource->ip." ".$vm_vnc_console_port_local." ".$vm_vnc_console_port_full;
			$msg = sprintf($this->lang['lang_open_console'], $vm_name);
			$msg .= sprintf($this->lang['lang_tunnel_command'], $tunnel_command);
			$response->msg = $msg;
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
