<?php
/**
 * kvm-storage-vm Reboot VM(s)
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class kvm_storage_vm_reboot
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_storage_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kvm_storage_vm_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_identifier';
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
var $prefix_tab = 'kvm_storage_vm_tab';
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
		$response = $this->reboot();
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response)
		);
	}

	//--------------------------------------------
	/**
	 * Stop
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function reboot() {
		$response = '';
		$vms = $this->response->html->request()->get($this->identifier_name);
		if( $vms !== '' ) {
			$appliance_id = $this->response->html->request()->get('appliance_id');
			$appliance    = new appliance();
			$resource     = new resource();
			$errors       = array();
			$message      = array();
			foreach($vms as $key => $vm) {
				$appliance->get_instance_by_id($appliance_id);
				$resource->get_instance_by_id($appliance->resources);
				$file = $this->openqrm->get('basedir').'/plugins/kvm-storage/web/kvm-stat/'.$resource->id.'.vm_list';
				$command  = $this->openqrm->get('basedir').'/plugins/kvm-storage/bin/openqrm-kvm-storage-vm reboot -n '.$vm;
				$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
				$resource->send_command($resource->ip, $command);
				$message[] = sprintf($this->lang['msg_rebooted'], $vm);
			}
			if(count($errors) === 0) {
				$response = join('<br>', $message);
			} else {
				$msg = array_merge($errors, $message);
				$response = join('<br>', $msg);
			}
		} else {
			$response = '';
		}
		return $response;
	}

}
?>
