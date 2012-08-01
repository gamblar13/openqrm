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


class cloud_ui_deprovision
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
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest	= new cloudrequest();
		require_once $this->rootdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloudmailer	= new cloudmailer();

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
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}
		$response = $this->deprovision();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'requests', $this->message_param, $response->msg));
		}

		$template = $this->response->html->template($this->tpldir."/cloud-ui.deprovision.tpl.php");
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_confirm_deprovision'], 'confirm_deprovision');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}

	//--------------------------------------------
	/**
	 * deprovision
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function deprovision() {
		$response = $this->get_response();
		$form = $response->form;

		if($this->response->submit()) {
			$request = $form->get_request($this->identifier_name);

			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();

				foreach($request as $key => $cz_id) {
				    $this->cloudrequest->get_instance_by_id($cz_id);
				    // is it ours ?
				    if ($this->cloudrequest->cu_id != $this->clouduser->id) {
					  continue;
				    }
				    // only allow to deprovision if cr is in state active or no-res
				    if (($this->cloudrequest->status != 3) && ($this->cloudrequest->status != 7)) {
					  $message[] = $this->lang['cloud_ui_deprovision_failed']." ".$cz_id;
					  continue;
				    }
				    // mail user before deprovisioning
				    $start = date("d-m-Y H-i", $this->cloudrequest->start);
				    $now = date("d-m-Y H-i", $_SERVER['REQUEST_TIME']);
				    // get admin email
				    $cc_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
				    // send mail to user
				    $this->cloudmailer->to = $this->clouduser->email;
				    $this->cloudmailer->from = $cc_admin_email;
				    $this->cloudmailer->subject = "openQRM Cloud: Your request ".$cz_id." is going to be deprovisioned now !";
				    $this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/deprovision_cloud_request.mail.tmpl";
				    $arr = array('@@ID@@' => $cz_id, '@@FORENAME@@' => $this->clouduser->forename, '@@LASTNAME@@' => $this->clouduser->lastname, '@@START@@' => $start, '@@STOP@@' => $now);
				    $this->cloudmailer->var_array = $arr;
				    $this->cloudmailer->send();
				    // send mail to cloud-admin
				    $this->cloudmailer->to = $cc_admin_email;
				    $this->cloudmailer->from = $cc_admin_email;
				    $this->cloudmailer->subject = "openQRM Cloud: Your request ".$cz_id." is going to be deprovisioned now !";
				    $this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/deprovision_cloud_request.mail.tmpl";
				    $aarr = array('@@ID@@' => $cz_id, '@@FORENAME@@' => "", '@@LASTNAME@@' => "CloudAdmin", '@@START@@' => $start, '@@STOP@@' => $now);
				    $this->cloudmailer->var_array = $aarr;
				    $this->cloudmailer->send();
				    // set cr status
				    $this->cloudrequest->setstatus($cz_id, 'deprovision');
				    $message[] = $this->lang['cloud_ui_deprovisioned']." ".$cz_id;
				}
				$response->msg = join('<br>', $message);
			}
		}
		return $response;
	}


	function get_response() {
		$todelete = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'deprovision');
		if( $todelete !== '' ) {
			$i = 0;
			foreach($todelete as $folder) {
				$d['param_f'.$i]['label']                       = $folder;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';
				$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $folder;
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


