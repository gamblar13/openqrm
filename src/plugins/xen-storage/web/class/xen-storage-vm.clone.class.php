<?php
/**
 * xen-Storage-vm clone VM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_storage_vm_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "xen_storage_msg";
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
var $prefix_tab = 'xen_tab';
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
		$this->user = $openqrm->user();
		$id = $this->response->html->request()->get('appliance_id');
		if($id === '') {
			return false;
		}
		$vm = $this->response->html->request()->get('vm');
		if($vm === '') {
			return false;
		}
		$this->vm = $vm;
		$this->response->params['vm'] = $this->vm;
		$appliance = new appliance();
		$resource  = new resource();
		$appliance->get_instance_by_id($id);
		$resource->get_instance_by_id($appliance->resources);
		$this->resource  = $resource;
		$this->appliance = $appliance;
		$this->statfile  = $this->openqrm->get('basedir').'/plugins/xen-storage/web/xen-stat/'.$resource->id.'.vm_list';
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
		$response = $this->duplicate();
		if(isset($response->msg)) {
			sleep(2);
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 2) {
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&resource_id='.$response->resource_id
				);
			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/xen-storage-vm-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->vm), 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$this->resource->generate_mac();
			$name        = $form->get_request('name');
			$command     = $this->openqrm->get('basedir').'/plugins/xen-storage/bin/openqrm-xen-storage-vm clone';
			$command    .= ' -n '.$this->vm;
			$command    .= ' -w '.$name;
			$command    .= ' -m '.$this->resource->mac;
			$command    .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
			$statfile = $this->statfile;
			if ($this->file->exists($statfile)) {
				$lines = explode("\n", $this->file->get_contents($statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$check = $line[1];
							if($name === $check) {
								$error = sprintf($this->lang['error_exists'], $name);
							}
						}
					}
				}
			}
			if(isset($error)) {
				$response->error = $error;
			} else {
				$tables = $this->openqrm->get('table');
				$id = openqrm_db_get_free_id('resource_id', $tables['resource']);
				$ip = "0.0.0.0";
				$mac = $this->resource->mac;
				// send command to the openQRM-server
				$openqrm = new openqrm_server();
				$openqrm->send_command('openqrm_server_add_resource '.$id.' '.$mac.' '.$ip);
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("xen-storage-vm");
				// add to openQRM database
				$fields["resource_id"] = $id;
				$fields["resource_ip"] = $ip;
				$fields["resource_mac"] = $mac;
				$fields["resource_localboot"] = 0;
				$fields["resource_vtype"] = $virtualization->id;
				$fields["resource_vhostid"] = $this->resource->id;
				$this->resource->add($fields);
				$this->resource->send_command($this->resource->ip, $command);
				$response->resource_id = $id;
				$response->msg = sprintf($this->lang['msg_cloned'], $this->vm, $name);
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
		$form = $response->get_form($this->actions_name, 'clone');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->vm.'c';
		$d['name']['object']['attrib']['maxlength'] = 8;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
