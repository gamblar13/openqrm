<?php
/**
 * Appliance step3 (image)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class appliance_step3
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
				$this->response->get_url($this->actions_name, 'step4', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$a = $this->response->html->a();
		$a->title   = $this->lang['action_add'];
		$a->label   = $this->lang['action_add'];
		$a->handler = 'onclick="wait();"';
		$a->css     = 'add';
		$a->href    = $this->response->html->thisfile.'?base=image&image_action=add';

		$t = $this->response->html->template($this->tpldir.'/appliance-step3.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['or'], 'or');
		$t->add($a, 'add');
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
			$image = $form->get_request('image');
			$fields['appliance_imageid'] = $image;
			$fields['appliance_wizard'] = 'wizard=step4,user='.$this->user->name;
			$appliance->update($id, $fields);
			$response->msg = sprintf($this->lang['msg'], $image, $appliance->name);
			// wizard
			$rs = $this->user->set_wizard($this->user->name, 'appliance', 4, $id);
			// update long term event, remove old event and add new one
			$event = new event();
			$event_description_step2 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 2, $this->user->name);
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 3, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step2, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description_step3, "", "", 0, 0, 0);
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
		$form = $response->get_form($this->actions_name, 'step3');

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

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		// prepare image list according to the resource capabilities + vtype
		$image  = new image();
		$list   = $image->get_list();
		unset($list[0]);
		unset($list[1]);
#$this->response->html->help($list);

		$images = array();
		// openQRM
		if($resource->id == 0) {
			$images[] = array(0, 'Local openQRM Installation');

		// local-server integrated resource
		} else if (strstr($resource->capabilities, "TYPE=local-server")) {
			$local_image = new image();
			$local_image->get_instance_by_name("resource".$resource->id);
			$images[] = array($local_image->id, 'Local OS Installation');

		// ...-storage-vms - local-deployment
		} else if (strstr($virtualization->type, "-storage-vm")) {

			$virtualization_plugin_name = str_replace("-vm", "", $virtualization->type);
			$deployment = new deployment();
			$deployment_id_arr = $deployment->get_deployment_ids();
			$possible_deployment_types_arr = '';
			foreach ($deployment_id_arr as $deployment_id_db) {
				$deployment_id = $deployment_id_db['deployment_id'];
				$deployment->get_instance_by_id($deployment_id);
				if ($deployment->storagetype === $virtualization_plugin_name) {
					$possible_deployment_types_arr[] = $deployment->type;
				}
			}
			// filter image list with only the images from the VM deployment type
			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				if (!in_array($image->type, $possible_deployment_types_arr)) {
					continue;
				}
				// filter local-server images
				$images[] = array($image_id, $image->id.' / '.$image->name.' ('.$image->type.')');
			}

		// ...-vms - network-deployment - show only network-boot images
		} else if (strstr($virtualization->type, "-vm")) {

			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				// filter local-server images
				if (strstr($image->capabilities, "TYPE=local-server")) {
					continue;
				}
				$deployment = new deployment();
				$deployment->get_instance_by_type($image->type);
				$is_network_deployment = false;
				$rootdevice_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/image.$deployment->type.php";
				if (file_exists($rootdevice_identifier_hook)) {
					require_once "$rootdevice_identifier_hook";
					$image_is_network_deployment_function="get_"."$deployment->type"."_is_network_deployment";
					$image_is_network_deployment_function=str_replace("-", "_", $image_is_network_deployment_function);
					$is_network_deployment = $image_is_network_deployment_function();
				} else {
					$is_network_deployment = false;
				}
				if ($is_network_deployment) {
					$images[] = array($image_id, $image->id.' / '.$image->name.' ('.$image->type.')');
				}
			}


		// network deployment - physical systems - show only network-boot images
		} else {

			foreach ($list as $value) {
				$image_id = $value['value'];
				$image->get_instance_by_id($image_id);
				// is image active ? then do not show it here
				if ($image->isactive == 1) {
					continue;
				}
				// filter local-server images
				if (strstr($image->capabilities, "TYPE=local-server")) {
					continue;
				}
				$deployment = new deployment();
				$deployment->get_instance_by_type($image->type);
				$is_network_deployment = false;
				$rootdevice_identifier_hook = $this->openqrm->get('basedir')."/web/boot-service/image.$deployment->type.php";
				if (file_exists($rootdevice_identifier_hook)) {
					require_once "$rootdevice_identifier_hook";
					$image_is_network_deployment_function="get_"."$deployment->type"."_is_network_deployment";
					$image_is_network_deployment_function=str_replace("-", "_", $image_is_network_deployment_function);
					$is_network_deployment = $image_is_network_deployment_function();
				} else {
					$is_network_deployment = false;
				}
				if ($is_network_deployment) {
					$images[] = array($image_id, $image->id.' / '.$image->name.' ('.$image->type.')');
				}
			}
		}


		$d['image']['label']                          = $this->lang['form_image'];
		$d['image']['required']                       = true;
		$d['image']['object']['type']                 = 'htmlobject_select';
		$d['image']['object']['attrib']['index']      = array(0, 1);
		$d['image']['object']['attrib']['id']         = 'image';
		$d['image']['object']['attrib']['name']       = 'image';
		$d['image']['object']['attrib']['options']    = $images;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
