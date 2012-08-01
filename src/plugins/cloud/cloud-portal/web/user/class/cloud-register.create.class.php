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


class cloud_register_create
{
var $tpldir;
var $lang;
var $actions_name = 'cloud-register-create';

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
		$response = $this->create();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'home', $this->message_param, $response->msg));
		}
		$template = $response->html->template($this->tpldir."/cloud-register-create.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * create
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function create() {
		$response = $this->get_response("create");
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// name already in use ?
			$this->clouduser->get_instance_by_name($data['cu_name']);
			if($this->clouduser->id > 0) {
				$form->set_error("cu_name", "This Username is already in use!");
			}
			if(!$form->get_errors()) {
				// create token
				$data['cu_token'] = md5(uniqid(rand(), true));
				// disabed for now
				$data['cu_status'] = 0;
				// default user group
				$data['cu_cg_id'] = 0;
				$data['cu_id'] = openqrm_db_get_free_id('cu_id', $this->clouduser->_db_table);
				// check how many ccunits to give for a new user
				$data['cu_ccunits'] = $this->cloudconfig->get_value_by_key('auto_give_ccus');
				$this->clouduser->add($data);
				$this->clouduser->get_instance_by_id($data['cu_id']);
				// send mail to user
				$cloud_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
				$external_portal_name = $this->cloudconfig->get_value_by_key('external_portal_url');
				if (!strlen($external_portal_name)) {
					$external_portal_name = 'http://'.$_SERVER['SERVER_NAME'].'/cloud-portal';
				}
				$this->cloudmailer->to = $this->clouduser->email;
				$this->cloudmailer->from = $cloud_admin_email;
				$this->cloudmailer->subject = "openQRM Cloud: Activate your account";
				$this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/activate_new_cloud_user.mail.tmpl";
				$arr = array('@@USER@@' => $this->clouduser->name, '@@ID@@' => $this->clouduser->id, '@@TOKEN@@' => $this->clouduser->token, '@@EXTERNALPORTALNAME@@' => $external_portal_name, '@@FORENAME@@' => $this->clouduser->forename, '@@LASTNAME@@' => $this->clouduser->lastname);
				$this->cloudmailer->var_array = $arr;
				$this->cloudmailer->send();
				// success msg
				$response->msg = "<strong>Successfully registered new Cloud User</strong>";
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
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "create");

		$d = array();
		switch($mode) {

			case 'create':

				$d['cu_name']['label']                     = "Username";
				$d['cu_name']['required']                  = true;
				$d['cu_name']['validate']['regex']         = '~^[a-z0-9]+$~i';
				$d['cu_name']['validate']['errormsg']      = 'Name must be [a-z] only';
				$d['cu_name']['object']['type']            = 'htmlobject_input';
				$d['cu_name']['object']['attrib']['type']  = 'text';
				$d['cu_name']['object']['attrib']['id']    = 'cu_name';
				$d['cu_name']['object']['attrib']['name']  = 'cu_name';

				$d['cu_password']['label']                     = "Password";
				$d['cu_password']['required']                  = true;
				$d['cu_password']['validate']['regex']         = '~^[a-z0-9]+$~i';
				$d['cu_password']['validate']['errormsg']      = 'Password must be [a-z] only';
				$d['cu_password']['object']['type']            = 'htmlobject_input';
				$d['cu_password']['object']['attrib']['type']  = 'password';
				$d['cu_password']['object']['attrib']['id']    = 'cu_password';
				$d['cu_password']['object']['attrib']['name']  = 'cu_password';

				$d['cu_email']['label']                     = "Email";
				$d['cu_email']['required']                  = true;
				$d['cu_email']['validate']['regex']         = '~^[a-z0-9@._-]+$~i';
				$d['cu_email']['validate']['errormsg']      = 'Email must be valid';
				$d['cu_email']['object']['type']            = 'htmlobject_input';
				$d['cu_email']['object']['attrib']['type']  = 'text';
				$d['cu_email']['object']['attrib']['id']    = 'cu_email';
				$d['cu_email']['object']['attrib']['name']  = 'cu_email';

				$d['cu_forename']['label']                     = "Forename";
				$d['cu_forename']['required']                  = true;
				$d['cu_forename']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_forename']['validate']['errormsg']      = 'Forename must be [a-z0-9] only';
				$d['cu_forename']['object']['type']            = 'htmlobject_input';
				$d['cu_forename']['object']['attrib']['type']  = 'text';
				$d['cu_forename']['object']['attrib']['id']    = 'cu_forename';
				$d['cu_forename']['object']['attrib']['name']  = 'cu_forename';

				$d['cu_lastname']['label']                     = "Lastname";
				$d['cu_lastname']['required']                  = true;
				$d['cu_lastname']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_lastname']['validate']['errormsg']      = 'Lastname must be [a-z] only';
				$d['cu_lastname']['object']['type']            = 'htmlobject_input';
				$d['cu_lastname']['object']['attrib']['type']  = 'text';
				$d['cu_lastname']['object']['attrib']['id']    = 'cu_lastname';
				$d['cu_lastname']['object']['attrib']['name']  = 'cu_lastname';

				$d['cu_street']['label']                     = "Adress";
				$d['cu_street']['required']                  = true;
				$d['cu_street']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_street']['validate']['errormsg']      = 'Street must be [a-z] only';
				$d['cu_street']['object']['type']            = 'htmlobject_input';
				$d['cu_street']['object']['attrib']['type']  = 'text';
				$d['cu_street']['object']['attrib']['id']    = 'cu_street';
				$d['cu_street']['object']['attrib']['name']  = 'cu_street';

				$d['cu_city']['label']                     = "City";
				$d['cu_city']['required']                  = true;
				$d['cu_city']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_city']['validate']['errormsg']      = 'City must be [a-z] only';
				$d['cu_city']['object']['type']            = 'htmlobject_input';
				$d['cu_city']['object']['attrib']['type']  = 'text';
				$d['cu_city']['object']['attrib']['id']    = 'cu_city';
				$d['cu_city']['object']['attrib']['name']  = 'cu_city';

				$d['cu_country']['label']                     = "Country";
				$d['cu_country']['required']                  = true;
				$d['cu_country']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_country']['validate']['errormsg']      = 'City must be [a-z] only';
				$d['cu_country']['object']['type']            = 'htmlobject_input';
				$d['cu_country']['object']['attrib']['type']  = 'text';
				$d['cu_country']['object']['attrib']['id']    = 'cu_country';
				$d['cu_country']['object']['attrib']['name']  = 'cu_country';

				$d['cu_phone']['label']                     = "Phone number";
				$d['cu_phone']['required']                  = true;
				$d['cu_phone']['validate']['regex']         = '~^[a-z0-9 ]+$~i';
				$d['cu_phone']['validate']['errormsg']      = 'Phone must be [a-z] only';
				$d['cu_phone']['object']['type']            = 'htmlobject_input';
				$d['cu_phone']['object']['attrib']['type']  = 'text';
				$d['cu_phone']['object']['attrib']['id']    = 'cu_phone';
				$d['cu_phone']['object']['attrib']['name']  = 'cu_phone';

			break;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}




}












?>
