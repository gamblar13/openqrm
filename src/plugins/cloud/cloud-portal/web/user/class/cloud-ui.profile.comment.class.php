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


class cloud_ui_profile_comment
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
		require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
		$this->cloudprofile	= new cloudprofile();
		require_once $this->rootdir."/plugins/cloud/class/cloudicon.class.php";
		$this->cloudicon	= new cloudicon();
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
		$response = $this->profile_comment();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'profiles', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template($this->tpldir."/cloud-ui.profile-comment.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_profile_comment_update'], 'cloud_ui_profile_comment_update');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}

	//--------------------------------------------
	/**
	 * profile_comment
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function profile_comment() {
		$this->pr_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->pr_id);
		$this->cloudprofile->get_instance_by_id($this->pr_id);
		
		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$cloud_profile_comment = $form->get_request('cloud_profile_comment');
			if(isset($cloud_profile_comment)) {
				if ($this->cloudprofile->cu_id != $this->clouduser->id) {
					// not request of the authuser
					exit(1);
				}
				$cloud_profile_name = $this->cloudprofile->name;
				$pr_fields['pr_description'] = $cloud_profile_comment;
				$this->cloudprofile->update($this->cloudprofile-> id, $pr_fields);
				$response->msg = sprintf($this->lang['cloud_ui_profile_comment_updated'], $cloud_profile_name);
			}
		}
		return $response;
	}


	function get_response() {
		$profile_id = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'profile_comment');
		
		$d['cloud_profile_comment']['label']                       = $this->cloudprofile->name;
		$d['cloud_profile_comment']['object']['type']              = 'htmlobject_input';
		$d['cloud_profile_comment']['object']['attrib']['type']    = 'text';
		$d['cloud_profile_comment']['object']['attrib']['name']    = 'cloud_profile_comment';
		$d['cloud_profile_comment']['object']['attrib']['id']      = 'cloud_profile_comment';
		$d['cloud_profile_comment']['object']['attrib']['value']   = $this->cloudprofile->description;

		
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>







