<?php
/**
 * xen-storage-vm Remove VM(s)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_storage_vm_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_storage_vm_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "xen_storage_vm_msg";
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
var $prefix_tab = 'xen_storage_vm_tab';
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
		$response = $this->remove();
		if(isset($response->msg)) {
			sleep(2);
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/xen-storage-vm-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$vms  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $vms !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($vms as $ex) {
				$d['param_f'.$i]['label']                       = $ex;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$appliance_id = $this->response->html->request()->get('appliance_id');
				$appliance    = new appliance();
				$resource     = new resource();
				$errors       = array();
				$message      = array();
				foreach($vms as $key => $vm) {
					$appliance->get_instance_by_id($appliance_id);
					$resource->get_instance_by_id($appliance->resources);
					$file = $this->openqrm->get('basedir').'/plugins/xen-storage/web/xen-stat/'.$resource->id.'.vm_list';
					if($this->file->exists($file)) {					
						$lines = explode("\n", $this->file->get_contents($file));
						if(count($lines) >= 1) {
							foreach($lines as $line) {
								if($line !== '') {
									$line = explode('@', $line);
									if($vm === $line[1]) {
										$xen = new resource();
										$xen->get_instance_by_mac($line[2]);
										// check if it is still in use
										$appliances_using_resource = $appliance->get_ids_per_resource($xen->id);
										if (count($appliances_using_resource) > 0) {
											$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
											$errors[] = sprintf($this->lang['msg_vm_resource_still_in_use'], $vm, $xen->id, $appliances_using_resource_str);
										} else {
											$xen->remove($xen->id, $line[2]);
											$command  = $this->openqrm->get('basedir').'/plugins/xen-storage/bin/openqrm-xen-storage-vm remove -n '.$vm;
											$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
											$resource->send_command($resource->ip, $command);
											$form->remove($this->identifier_name.'['.$key.']');
											$message[] = sprintf($this->lang['msg_removed'], $vm);

											// stop remote console
											$port   = $line[5];
											$mac    = $line[2];
											$rid    = $xen->id;
											$host_ip = $resource->ip;
											$event   = new event();
											$plugin  = new plugin();
											$enabled = $plugin->enabled();
											foreach ($enabled as $index => $name) {
												$running = $this->openqrm->get('webdir').'/plugins/'.$name.'/.running';
												$hook = $this->openqrm->get('webdir').'/plugins/'.$name.'/openqrm-'.$name.'-remote-console-hook.php';
												if (file_exists($hook)) {
													if (file_exists($running)) {
														$event->log("console", $_SERVER['REQUEST_TIME'], 5, "xen-storage-vm.remove.class.php", 'Found plugin '.$name.' providing a remote console.', "", "", 0, 0, $xen->id);
														require_once($hook);
														$console_function = 'openqrm_'.$name.'_disable_remote_console';
														$console_function = str_replace("-", "_", $console_function);
														echo "$console_function($host_ip, $port, $rid, $mac, $vm)";
														$console_function($host_ip, $port, $rid, $mac, $vm);
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}
?>
