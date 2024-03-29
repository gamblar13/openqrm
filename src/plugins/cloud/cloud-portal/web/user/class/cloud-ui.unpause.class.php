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


class cloud_ui_unpause
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui-unpause';

var $OPENQRM_SERVER_BASE_DIR = "/usr/share";



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
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
	    // include classes and prepare ojects
	    require_once $this->rootdir."/plugins/cloud/class/cloudappliance.class.php";
	    $this->cloudappliance	= new cloudappliance();
		require_once $this->rootdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloudmailer	= new cloudmailer();
		require_once $this->rootdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloudconfig	= new cloudconfig();
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest	= new cloudrequest();

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
		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, 'appliances'));
		}
		$response = $this->unpause();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'appliances', $this->message_param, $response->msg));
		}
		$template = $this->response->html->template($this->tpldir."/cloud-ui.unpause.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_confirm_unpause'], 'confirm_unpause');
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * unpause
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function unpause() {
		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$request = $form->get_request($this->identifier_name);

			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();
				foreach($request as $key => $cloudappliance_id) {
					$this->cloudappliance->get_instance_by_id($cloudappliance_id);
					$this->cloudrequest->get_instance_by_id($this->cloudappliance->cr_id);
					if ($this->cloudrequest->cu_id != $this->clouduser->id) {
						// not request of the authuser
						exit(1);
					}
					// check if no other command is currently running
					if ($this->cloudappliance->cmd != 0) {
						$errors[] = $this->lang['cloud_ui_appliance_command_running']." ".$cloudappliance_id;
						continue;
					}
					// check that state is paused
					if ($this->cloudappliance->state == 0) {
						$this->cloudappliance->set_cmd($this->cloudappliance->id, "start");
						$this->cloudappliance->set_state($this->cloudappliance->id, "active");

						// send mail to cloud-admin
						$cloud_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
						$external_portal_name = $this->cloudconfig->get_value_by_key('external_portal_url');
						if (!strlen($external_portal_name)) {
							$external_portal_name = 'http://'.$_SERVER['SERVER_NAME'].'/cloud-portal';
						}
						$this->cloudmailer->to = $cloud_admin_email;
						$this->cloudmailer->from = $cloud_admin_email;
						$this->cloudmailer->subject = "openQRM Cloud: Cloud Appliance $cloudappliance_id registered for start (unpause)";
						$this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/unpaused_cloud_appliance.mail.tmpl";
						$arr = array('@@USER@@' => $this->clouduser->name, '@@CLOUD_APPLIANCE_ID@@' => $cloudappliance_id);
						$this->cloudmailer->var_array = $arr;
						$this->cloudmailer->send();

						$message[] = $this->lang['cloud_ui_unpaused']." ".$cloudappliance_id;
					} else {
						$errors[] = $this->lang['cloud_ui_unpause_failed']." ".$cloudappliance_id;
						continue;
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		}
		return $response;
	}


	function get_response() {
		$tounpause = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'unpause');
		if( $tounpause !== '' ) {
			$i = 0;
			foreach($tounpause as $system_id) {
				$d['param_f'.$i]['label']                       = "System ID ".$system_id;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';
				$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $system_id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}




}

?>


