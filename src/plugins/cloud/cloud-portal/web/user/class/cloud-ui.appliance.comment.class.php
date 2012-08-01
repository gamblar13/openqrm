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


class cloud_ui_appliance_comment
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
		$response = $this->appliance_comment();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'appliances', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template($this->tpldir."/cloud-ui.appliance-comment.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_appliance_comment_update'], 'cloud_ui_appliance_comment_update');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}

	//--------------------------------------------
	/**
	 * appliance_comment
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function appliance_comment() {
		$this->ca_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->ca_id);
		$this->cloudappliance->get_instance_by_id($this->ca_id);

		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$cloud_appliance_comment = $form->get_request('cloud_appliance_comment');
			if(isset($cloud_appliance_comment)) {
				$this->cloudrequest->get_instance_by_id($this->cloudappliance->cr_id);
				if ($this->cloudrequest->cu_id != $this->clouduser->id) {
					// not request of the authuser
					exit(1);
				}
				$this->appliance->get_instance_by_id($this->cloudappliance->appliance_id);
				$cloud_appliance_name = $this->appliance->name;
				$appliance_fields['appliance_comment'] = $cloud_appliance_comment;
				$this->appliance->update($this->appliance->id, $appliance_fields);
				$response->msg = sprintf($this->lang['cloud_ui_appliance_comment_updated'], $cloud_appliance_name);
			}
		}
		return $response;
	}


	function get_response() {
		$ca_id = $this->response->html->request()->get($this->identifier_name);
		$this->appliance->get_instance_by_id($this->cloudappliance->appliance_id);
		$cloud_appliance_name = $this->appliance->name;

		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'appliance_comment');
		
		$d['cloud_appliance_comment']['label']                       = $this->appliance->name;
		$d['cloud_appliance_comment']['object']['type']              = 'htmlobject_input';
		$d['cloud_appliance_comment']['object']['attrib']['type']    = 'text';
		$d['cloud_appliance_comment']['object']['attrib']['name']    = 'cloud_appliance_comment';
		$d['cloud_appliance_comment']['object']['attrib']['id']      = 'cloud_appliance_comment';
		$d['cloud_appliance_comment']['object']['attrib']['value']   = $this->appliance->comment;

		
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>







