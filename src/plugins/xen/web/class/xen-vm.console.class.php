<?php
/**
 * xen-vm Console
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_vm_console
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
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_identifier';
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
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
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
		$response = $this->console();
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Console
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function console() {
		$response = '';
		$vm = $this->response->html->request()->get('vm');
		if( $vm !== '' ) {
			$port = '';
			$mac = '';
			$rid = '';
			$host_ip = '';
			$appliance  = new appliance();
			$resource = new resource();
			$appliance->get_instance_by_id($this->response->html->request()->get('appliance_id'));
			$resource->get_instance_by_id($appliance->resources);
			$file  = $this->openqrm->get('basedir').'/plugins/xen/web/xen-stat/'.$resource->id.'.vm_list';
			if($this->file->exists($file)) {				
				$lines   = explode("\n", $this->file->get_contents($file));
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $vm) {
							$port   = $line[5];
							$mac    = $line[2];
							$resource->get_instance_by_mac($mac);
							$rid    = $resource->id;
							$host_resource = new resource();
							$host_resource->get_instance_by_id($resource->vhostid);
							$host_ip = $host_resource->ip;
						}						
					}
				}
				$event   = new event();
				$plugin  = new plugin();
				$enabled = $plugin->enabled();
				foreach ($enabled as $index => $name) {
					$running = $this->openqrm->get('webdir').'/plugins/'.$name.'/.running';
					$hook = $this->openqrm->get('webdir').'/plugins/'.$name.'/openqrm-'.$name.'-remote-console-hook.php';
					if (file_exists($hook)) {
						if (file_exists($running)) {
							$event->log("console", $_SERVER['REQUEST_TIME'], 5, "xen-vm.console.class.php", 'Found plugin '.$name.' providing a remote console.', "", "", 0, 0, $resource->id);
							require_once($hook);
							$console_function = 'openqrm_'.$name.'_remote_console';
							$console_function = str_replace("-", "_", $console_function);
							 // echo "$console_function($host_ip, $port, $rid, $mac, $vm)";
							$console_function($host_ip, $port, $rid, $mac, $vm);
						}
					}
				}
			}
		} else {
			$response = '';
		}
		return $response;
	}

}
?>
