<?php

/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class cloud_ui_appliance_private
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';



	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		// include classes and prepare ojects
		require_once $this->rootdir."/plugins/cloud/class/cloudappliance.class.php";
		$this->cloudappliance	= new cloudappliance();
	    require_once $this->rootdir."/class/appliance.class.php";
	    $this->appliance	= new appliance();
		$this->image		= new image();
		require_once $this->rootdir."/plugins/cloud/class/cloudimage.class.php";
		$this->cloudimage	= new cloudimage();
		require_once $this->rootdir."/plugins/cloud/class/cloudiplc.class.php";
		$this->cloudiplc	= new cloudiplc();
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest	= new cloudrequest();

	}

	//--------------------------------------------
	/**
	 * Action remove
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}
		$response = $this->appliance_private();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'appliances', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template($this->tpldir."/cloud-ui.appliance-private.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_appliance_create_private_image'], 'cloud_ui_appliance_create_private_image');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}

	//--------------------------------------------
	/**
	 * appliance_private
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function appliance_private() {
		$this->ca_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->ca_id);
		$this->cloudappliance->get_instance_by_id($this->ca_id);

		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$cloud_appliance_private = $form->get_request('cloud_appliance_private');
			if(isset($cloud_appliance_private)) {
				$this->cloudrequest->get_instance_by_id($this->cloudappliance->cr_id);
				if ($this->cloudrequest->cu_id != $this->clouduser->id) {
					// not request of the authuser
					exit(1);
				}
				$this->appliance->get_instance_by_id($this->cloudappliance->appliance_id);
				$cloud_appliance_name = $this->appliance->name;
				$this->image->get_instance_by_id($this->appliance->imageid);
				$this->cloudimage->get_instance_by_image_id($this->image->id);
				$cloud_image_current_disk_size = $this->cloudimage->disk_size;

				$error = false;
				// check if no other command is currently running
				if ($this->cloudappliance->cmd != 0) {
					$response->error = sprintf($this->lang['cloud_ui_appliance_command_running'], $cloud_appliance_name);
					$error = true;
				}
				// check that state is active
				if ($this->cloudappliance->state != 1) {
					$response->error = $this->lang['cloud_ui_appliance_command_needs_active'];
					$error = true;
				}

				if (!$error) {
					// put the size + clone name in the cloud_image
					$time_token = $_SERVER['REQUEST_TIME'];
					$private_image_name = str_replace("cloud", "private", $this->image->name);
					$private_image_name = substr($private_image_name,0,11).$time_token;
					$cloudi_request = array(
						'ci_disk_rsize' => $cloud_image_current_disk_size,
						'ci_clone_name' => $private_image_name,
					);
					$this->cloudimage->update($this->cloudimage->id, $cloudi_request);
					// create a new cloud-image private-life-cycle / using the cloudappliance id
					$ciplc_fields['cp_id'] = openqrm_db_get_free_id('cp_id', $this->cloudiplc->_db_table);
					$ciplc_fields['cp_appliance_id'] = $this->cloudappliance->id;
					$ciplc_fields['cp_cu_id'] = $this->clouduser->id;
					$ciplc_fields['cp_state'] = '1';
					$ciplc_fields['cp_start_private'] = $_SERVER['REQUEST_TIME'];
					$this->cloudiplc->add($ciplc_fields);
					$response->msg = sprintf($this->lang['cloud_ui_appliance_private_created'], $private_image_name, $cloud_appliance_name);
				}
			}
		}
		return $response;
	}


	function get_response() {
		$ca_id = $this->response->html->request()->get($this->identifier_name);
		$this->appliance->get_instance_by_id($this->cloudappliance->appliance_id);
		$cloud_appliance_name = $this->appliance->name;

		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'appliance_private');
		
		$d['cloud_appliance_private']['label']                       = $this->appliance->name;
		$d['cloud_appliance_private']['object']['type']              = 'htmlobject_input';
		$d['cloud_appliance_private']['object']['attrib']['type']    = 'checkbox';
		$d['cloud_appliance_private']['object']['attrib']['name']    = 'cloud_appliance_private';
		$d['cloud_appliance_private']['object']['attrib']['id']      = 'cloud_appliance_private';
		$d['cloud_appliance_private']['object']['attrib']['checked'] = true;
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>







