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


class cloud_register_recover
{
var $tpldir;
var $lang;
var $actions_name;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		$this->rootdir			= $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
		require_once $this->rootdir."/plugins/cloud/class/clouduser.class.php";
		$this->clouduser	= new clouduser();
		require_once $this->rootdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloudmailer	= new cloudmailer();
		require_once $this->rootdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloudconfig	= new cloudconfig();

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
		$response = $this->recover();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'home', $this->message_param, $response->msg));
		}
		$template = $response->html->template($this->tpldir."/cloud-register-recover.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * recover
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function recover() {
		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// user exists ?
			$this->clouduser->get_instance_by_name($data['cloud_user_name']);
			if (!$this->clouduser->id > 0) {
				$form->set_error("cloud_user_name", "No such User!");
			} else {
				// email fits ?
				if (strcmp($this->clouduser->email, $data['cloud_user_email'])) {
					$form->set_error("cloud_user_name", "Email does not fit to Username!");
				}
			}
			if(!$form->get_errors()) {
				$new_password = uniqid();
				$clouduser_fields['cu_password'] = $new_password;
				$this->clouduser->update($this->clouduser->id, $clouduser_fields);
				// send mail to user
				$cloud_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
				$external_portal_name = $this->cloudconfig->get_value_by_key('external_portal_url');
				if (!strlen($external_portal_name)) {
					$external_portal_name = 'http://'.$_SERVER['SERVER_NAME'].'/cloud-portal';
				}
				$this->cloudmailer->to = $this->clouduser->email;
				$this->cloudmailer->from = $cloud_admin_email;
				$this->cloudmailer->subject = "openQRM Cloud: Your password has been reseted";
				$this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/your_password_has_been_reseted.tmpl";
				$arr = array('@@USER@@' => $this->clouduser->name, '@@PASSWORD@@' => $new_password, '@@EXTERNALPORTALNAME@@' => $external_portal_name, '@@FORENAME@@' => $this->clouduser->forename, '@@LASTNAME@@' => $this->clouduser->lastname);
				$this->cloudmailer->var_array = $arr;
				$this->cloudmailer->send();
				$response->msg = "<strong>You have successfully recovered your Cloud User Account.<br>A Mail with the new password was sent to you.<br></strong>";
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
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "recover");

		$d = array();

		$d['cloud_user_name']['label']                     = "Username";
		$d['cloud_user_name']['required']                  = true;
		$d['cloud_user_name']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['cloud_user_name']['validate']['errormsg']      = 'Username must be [a-z0-9] only';
		$d['cloud_user_name']['object']['type']            = 'htmlobject_input';
		$d['cloud_user_name']['object']['attrib']['type']  = 'text';
		$d['cloud_user_name']['object']['attrib']['id']    = 'cloud_user_name';
		$d['cloud_user_name']['object']['attrib']['name']  = 'cloud_user_name';

		$d['cloud_user_email']['label']                     = "Email";
		$d['cloud_user_email']['required']                  = true;
		$d['cloud_user_email']['validate']['regex']         = '~^[a-z0-9@._-]+$~i';
		$d['cloud_user_email']['validate']['errormsg']      = 'Email contains special characters';
		$d['cloud_user_email']['object']['type']            = 'htmlobject_input';
		$d['cloud_user_email']['object']['attrib']['type']  = 'text';
		$d['cloud_user_email']['object']['attrib']['id']    = 'cloud_user_email';
		$d['cloud_user_email']['object']['attrib']['name']  = 'cloud_user_email';

		$form->add($d);
		$response->form = $form;
		return $response;
	}




}












?>
