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



class cloud_usergroup_update
{
var $tpldir;
var $lang;
var $actions_name = 'cloud-usergroup-update';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->webdir  = $this->openqrm->get('webdir');
		$this->rootdir  = $this->openqrm->get('basedir');
		require_once $this->webdir."/plugins/cloud/class/cloudusergroup.class.php";
		$this->cloud_user_group = new cloudusergroup();
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();

		// handle response
		$this->response->add('cloud_usergroup_id', $this->response->html->request()->get('cloud_usergroup_id'));
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->update();

		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$external_portal_name = $this->cloud_config->get_value_by_key('external_portal_url');
		if (!strlen($external_portal_name)) {
			$openqrm_server = new openqrm_server();
			$openqrm_server_ip = $openqrm_server->get_ip_address();
			$external_portal_name = "http://".$openqrm_server_ip."/cloud-portal";
		}
		
		$template = $response->html->template($this->tpldir."/cloud-usergroup-update.tpl.php");
		$template->add(sprintf($this->lang['cloud_usergroup_update_title'], $response->name), 'title');
		$template->add($external_portal_name, 'external_portal_name');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response("update");
		$form     = $response->form;
		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			$data['cg_id'] = $this->response->html->request()->get('cloud_usergroup_id');
			// update data
			if(!$form->get_errors()) {
			    $this->cloud_user_group->get_instance_by_id($data['cg_id']);
			    unset($data['cg_id']);
			    $this->cloud_user_group->update($this->cloud_user_group->id, $data);
			    // success msg
			    $response->msg = $this->lang['cloud_usergroup_update_successful'];
			}
		}
		return $response;
	}


	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode) {
		$cloud_user_id = $this->response->html->request()->get('cloud_usergroup_id');
		if (strlen($cloud_user_id)) {
			$this->cloud_user_group->get_instance_by_id($cloud_user_id);
		}
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "update");
		
		$d = array();

		$d['cloud_usergroup_description']['label']                     = $this->lang['cloud_usergroup_description'];
		$d['cloud_usergroup_description']['required']                  = true;
		$d['cloud_usergroup_description']['object']['type']            = 'htmlobject_input';
		$d['cloud_usergroup_description']['object']['attrib']['type']  = 'text';
		$d['cloud_usergroup_description']['object']['attrib']['id']    = 'cloud_usergroup_description';
		$d['cloud_usergroup_description']['object']['attrib']['name']  = 'cg_description';
		$d['cloud_usergroup_description']['object']['attrib']['value']  = $this->cloud_user_group->description;

		$form->add($d);
		$response->form = $form;
		$response->name = $this->cloud_user_group->name;
		return $response;
	}
}












?>
