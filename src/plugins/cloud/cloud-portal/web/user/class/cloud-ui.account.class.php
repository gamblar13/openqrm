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


class cloud_ui_account
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';

/**
* user
* @access public
* @var string
*/
var $user;
/**
* cloud-id
* @access public
* @var int
*/
var $cloud_id;


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
		require_once $this->rootdir."/plugins/cloud/class/cloudusergroup.class.php";
		$this->cloudusergroup	= new cloudusergroup();
		require_once $this->rootdir."/plugins/cloud/class/clouduserslimits.class.php";
		$this->clouduserlimits	= new clouduserlimits();

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
		$response = $this->account();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'account', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template("./tpl/cloud-ui.account.tpl.php");
		$template->add($this->lang['cloud_ui_big_title']." ".$this->lang['cloud_ui_account_details'], 'title');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'account_details'));
		$template->add($this->lang['cloud_ui_request_components_details'], "show_details");
		$template->add($this->lang['cloud_ui_account_details'], "cloud_user_details");
		// ccus
		$template->add($this->lang['cloud_ui_account_user_ccunits'], "cloud_user_ccus");
		$template->add($this->clouduser->ccunits, "cloud_user_ccus_value");
		// lang
		$template->add($this->lang['cloud_ui_language'], "cloud_user_lang");
		$cloud_user_lang_value = $this->clouduser->lang;
		switch ($this->clouduser->lang) {
			case 'ch':
				$cloud_user_lang_value = "<img src='img/ch.gif' alt='ch'/>";
				break;
			case 'de':
				$cloud_user_lang_value = "<img src='img/de.gif' alt='de'/>";
				break;
			case 'en':
				$cloud_user_lang_value = "<img src='img/en.gif' alt='en'/>";
				break;
			case 'es':
				$cloud_user_lang_value = "<img src='img/es.gif' alt='es'/>";
				break;
			case 'fr':
				$cloud_user_lang_value = "<img src='img/fr.gif' alt='fr'/>";
				break;
			case 'it':
				$cloud_user_lang_value = "<img src='img/it.gif' alt='it'/>";
				break;
			case 'nl':
				$cloud_user_lang_value = "<img src='img/nl.gif' alt='nl'/>";
				break;
		}
		$template->add($cloud_user_lang_value, "cloud_user_lang_value");
		$this->clouduserlimits->get_instance_by_cu_id($this->clouduser->id);
		
		// limits
		$template->add($this->lang['cloud_ui_account_user_permissions'], "cloud_user_limits");
		// resource limit
		$template->add($this->lang['cloud_ui_appliances'], "cloud_userlimit_resource_limit");
		$template->add($this->clouduserlimits->resource_limit, "cloud_userlimit_resource_limit_value");
		// memory limit
		$template->add($this->lang['cloud_ui_request_memory'], "cloud_userlimit_memory_limit");
		$template->add($this->clouduserlimits->memory_limit, "cloud_userlimit_memory_limit_value");
		// disk limit
		$template->add($this->lang['cloud_ui_request_disk'], "cloud_userlimit_disk_limit");
		$template->add($this->clouduserlimits->disk_limit, "cloud_userlimit_disk_limit_value");
		// cpu limit
		$template->add($this->lang['cloud_ui_request_cpu'], "cloud_userlimit_cpu_limit");
		$template->add($this->clouduserlimits->cpu_limit, "cloud_userlimit_cpu_limit_value");
		// network limit
		$template->add($this->lang['cloud_ui_request_network'], "cloud_userlimit_network_limit");
		$template->add($this->clouduserlimits->network_limit, "cloud_userlimit_network_limit_value");

		return $template;
	}

	//--------------------------------------------
	/**
	 * account
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function account() {
		$response = $this->get_response("update");
		$form     = $response->form;
		
//		if(!$form->get_errors()	&& $response->submit()) {
		if($response->submit()) {
			$data = $form->get_request();
			// TODO add checs + update
		
			if(!$form->get_errors()) {
				unset($data['cu_id']);
				unset($data['cu_cg_id']);
				unset($data['cu_name']);
				unset($data['cu_ccunits']);
				unset($data['cu_status']);
				unset($data['cu_token']);
				$dberror = $this->clouduser->update($this->clouduser->id, $data);
			}
			// success msg
			$response->msg = $this->lang['cloud_ui_account_user_update_successful'];
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
		$form = $response->get_form($this->actions_name, "account");
		
		$this->cloudusergroup->get_instance_by_id($this->clouduser->cg_id);

		$d = array();
		
		$d['cu_cg_id']['label']                     = $this->lang['cloud_ui_account_user_group'];
		$d['cu_cg_id']['required']                  = false;
		$d['cu_cg_id']['object']['type']            = 'htmlobject_input';
		$d['cu_cg_id']['object']['attrib']['type']  = 'text';
		$d['cu_cg_id']['object']['attrib']['id']    = "cu_cg_id";
		$d['cu_cg_id']['object']['attrib']['name']  = "cu_cg_id";
		$d['cu_cg_id']['object']['attrib']['value']    = $this->cloudusergroup->name;

		$d['cu_name']['label']                     = $this->lang['cloud_ui_account_user_name'];
		$d['cu_name']['required']                  = false;
		$d['cu_name']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['cu_name']['validate']['errormsg']      = 'Name must contain [a-z0-9] only';
		$d['cu_name']['object']['type']            = 'htmlobject_input';
		$d['cu_name']['object']['attrib']['type']  = 'text';
		$d['cu_name']['object']['attrib']['id']    = 'cu_name';
		$d['cu_name']['object']['attrib']['name']  = 'cu_name';
		$d['cu_name']['object']['attrib']['value']  = $this->clouduser->name;

		if (file_exists($this->rootdir."/plugins/ldap/.running")) {
			// central user management, do now show to update the users password
			$d['cu_password']                     = $this->lang['cloud_ui_user_managed_by_ldap'];
		} else {
			// regular user management
			$d['cu_password']['label']                     = $this->lang['cloud_ui_account_user_password'];
			$d['cu_password']['required']                  = true;
			$d['cu_password']['validate']['regex']         = '~^[a-z0-9]+$~i';
			$d['cu_password']['validate']['errormsg']      = 'Password must contain [a-z0-9] only';
			$d['cu_password']['object']['type']            = 'htmlobject_input';
			$d['cu_password']['object']['attrib']['type']  = 'password';
			$d['cu_password']['object']['attrib']['id']    = 'cu_password';
			$d['cu_password']['object']['attrib']['name']  = 'cu_password';
			$d['cu_password']['object']['attrib']['value']  = $this->clouduser->password;
		}

		$d['cu_email']['label']                     = $this->lang['cloud_ui_account_user_email'];
		$d['cu_email']['required']                  = true;
		$d['cu_email']['object']['type']            = 'htmlobject_input';
		$d['cu_email']['object']['attrib']['type']  = 'text';
		$d['cu_email']['object']['attrib']['id']    = 'cu_email';
		$d['cu_email']['object']['attrib']['name']  = 'cu_email';
		$d['cu_email']['object']['attrib']['value']  = $this->clouduser->email;

		$d['cu_forename']['label']                     = $this->lang['cloud_ui_account_user_forename'];
		$d['cu_forename']['required']                  = true;
		$d['cu_forename']['object']['type']            = 'htmlobject_input';
		$d['cu_forename']['object']['attrib']['type']  = 'text';
		$d['cu_forename']['object']['attrib']['id']    = 'cu_forename';
		$d['cu_forename']['object']['attrib']['name']  = 'cu_forename';
		$d['cu_forename']['object']['attrib']['value']  = $this->clouduser->forename;

		$d['cu_lastname']['label']                     = $this->lang['cloud_ui_account_user_lastname'];
		$d['cu_lastname']['required']                  = true;
		$d['cu_lastname']['object']['type']            = 'htmlobject_input';
		$d['cu_lastname']['object']['attrib']['type']  = 'text';
		$d['cu_lastname']['object']['attrib']['id']    = 'cu_lastname';
		$d['cu_lastname']['object']['attrib']['name']  = 'cu_lastname';
		$d['cu_lastname']['object']['attrib']['value']  = $this->clouduser->lastname;

		$d['cu_street']['label']                     = $this->lang['cloud_ui_account_user_street'];
		$d['cu_street']['required']                  = true;
		$d['cu_street']['object']['type']            = 'htmlobject_input';
		$d['cu_street']['object']['attrib']['type']  = 'text';
		$d['cu_street']['object']['attrib']['id']    = 'cu_street';
		$d['cu_street']['object']['attrib']['name']  = 'cu_street';
		$d['cu_street']['object']['attrib']['value']  = $this->clouduser->street;

		$d['cu_city']['label']                     = $this->lang['cloud_ui_account_user_city'];
		$d['cu_city']['required']                  = true;
		$d['cu_city']['object']['type']            = 'htmlobject_input';
		$d['cu_city']['object']['attrib']['type']  = 'text';
		$d['cu_city']['object']['attrib']['id']    = 'cu_city';
		$d['cu_city']['object']['attrib']['name']  = 'cu_city';
		$d['cu_city']['object']['attrib']['value']  = $this->clouduser->city;

		$d['cu_country']['label']                     = $this->lang['cloud_ui_account_user_country'];
		$d['cu_country']['required']                  = true;
		$d['cu_country']['object']['type']            = 'htmlobject_input';
		$d['cu_country']['object']['attrib']['type']  = 'text';
		$d['cu_country']['object']['attrib']['id']    = 'cu_country';
		$d['cu_country']['object']['attrib']['name']  = 'cu_country';
		$d['cu_country']['object']['attrib']['value']  = $this->clouduser->country;

		$d['cu_phone']['label']                     = $this->lang['cloud_ui_account_user_phone'];
		$d['cu_phone']['required']                  = true;
		$d['cu_phone']['object']['type']            = 'htmlobject_input';
		$d['cu_phone']['object']['attrib']['type']  = 'text';
		$d['cu_phone']['object']['attrib']['id']    = 'cu_phone';
		$d['cu_phone']['object']['attrib']['name']  = 'cu_phone';
		$d['cu_phone']['object']['attrib']['value']  = $this->clouduser->phone;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}

?>


