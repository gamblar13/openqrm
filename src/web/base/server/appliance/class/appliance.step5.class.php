<?php
/**
 * Appliance step5 (Kernel)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class appliance_step5
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user       = $openqrm->user();

		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/appliance-step5.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {		
		$response  = $this->get_response();
		$form      = $response->form;
		$id        = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		if(!$form->get_errors() && $this->response->submit()) {
			$kernel = $form->get_request('kernel');
			$fields['appliance_kernelid'] = $kernel;
			$fields['appliance_wizard'] = null;
			$appliance->update($id, $fields);
			// reset wizard
			$rs = $this->user->set_wizard($this->user->name, 0,0,0);
			// now we have to run the appliance add hook
			$appliance->run_add_hook($id);
			$response->msg = sprintf($this->lang['msg'], $kernel, $appliance->name);
			$event = new event();
			$event_description_step4 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 4, $this->user->name);
			$event_description_step5 = sprintf($this->lang['appliance_created'], $appliance->name, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step4, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 5, "add", $event_description_step5, "", "", 0, 0, 0);
		}
		$response->name = $appliance->name;
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
		$form = $response->get_form($this->actions_name, 'step5');

		$id        = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		// if not openQRM resource
		if($resource->id != 0) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($resource->vtype);
		}
		$image = new image();
		$image->get_instance_by_id($appliance->imageid);
		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$kernel  = new kernel();
		$list   = $kernel->get_list();
		unset($list[0]);

		$kernels = array();
		if($resource->id == 0) {
			$kernels[] = array(0, 'openQRM');

		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			$local_kernel = new kernel();
			$local_kernel->get_instance_by_name("resource".$resource->id);
			$kernels[] = array($local_kernel->id, 'Local OS Installation');

		// ...-storage-vms - local-deployment
		} else if (strstr($virtualization->type, "-storage-vm")) {

			$kernels[] = array(1, 'Local OS Installation');

		// ...-vms - network-deployment - show only network-boot images
		} else if (strstr($virtualization->type, "-vm")) {

			foreach ($list as $value) {
				$id = $value['value'];
				$kernel->get_instance_by_id($id);
				if (!strstr($kernel->capabilities, "TYPE=local-server")) {
					$kernels[] = array($id, $kernel->id.' / '.$kernel->name.' ('.$kernel->version.')');
				}
			}

		// network deployment - physical systems - show only network-boot images
		} else {

			foreach ($list as $value) {
				$id = $value['value'];
				$kernel->get_instance_by_id($id);
				if (!strstr($kernel->capabilities, "TYPE=local-server")) {
					$kernels[] = array($id, $kernel->id.' / '.$kernel->name.' ('.$kernel->version.')');
				}
			}

		}


		$d['kernel']['label']                          = $this->lang['form_kernel'];
		$d['kernel']['required']                       = true;
		$d['kernel']['object']['type']                 = 'htmlobject_select';
		$d['kernel']['object']['attrib']['index']      = array(0, 1);
		$d['kernel']['object']['attrib']['id']         = 'kernel';
		$d['kernel']['object']['attrib']['name']       = 'kernel';
		$d['kernel']['object']['attrib']['options']    = $kernels;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
