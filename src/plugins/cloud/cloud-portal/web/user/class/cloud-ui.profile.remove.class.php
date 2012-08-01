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


class cloud_ui_profile_remove
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
		$response = $this->profile_remove();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'profiles', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template($this->tpldir."/cloud-ui.profile-remove.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_confirm_profile_remove'], 'confirm_profile_remove');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}

	//--------------------------------------------
	/**
	 * profile_remove
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function profile_remove() {
		
		$this->pr_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->pr_id);
		$this->cloudprofile->get_instance_by_id($this->pr_id);
		
		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$cloud_profile_id = $form->get_request($this->identifier_name);
			if(isset($cloud_profile_id)) {
				if ($this->cloudprofile->cu_id != $this->clouduser->id) {
					// not request of the authuser
					exit(1);
				}
				$cloud_profile_name = $this->cloudprofile->name;
				$this->cloudicon->get_instance_by_details($this->clouduser->id, 1, $this->cloudprofile->id);
				if (strlen($this->cloudicon->filename)) {
					$this->cloudicon->remove($this->cloudicon->id);
				}
				$this->cloudprofile->remove($this->cloudprofile->id);
				$response->msg = sprintf($this->lang['cloud_ui_confirm_profile_removed'], $cloud_profile_name);
			}
		}
		return $response;
	}


	function get_response() {
		$profile_id = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'profile_remove');
		$i = 0;
		if( $profile_id !== '' ) {
			$d['param_f'.$i]['label']                       = $this->cloudprofile->name;
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
			$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name;
			$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name;
			$d['param_f'.$i]['object']['attrib']['value']   = $profile_id;
			$d['param_f'.$i]['object']['attrib']['checked'] = true;
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>







