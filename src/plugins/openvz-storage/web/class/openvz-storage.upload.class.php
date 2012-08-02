<?php
/**
 * OpenVZ-Storage Add new Volume
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openvz_storage_upload
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'openvz_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "openvz_storage_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'openvz_tab';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user       = $openqrm->user();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$response = $this->upload();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/openvz-storage-upload.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function upload() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$template_url     = $form->get_request('url');
			$command  = $this->openqrm->get('basedir').'/plugins/openvz-storage/bin/openqrm-openvz-storage download_openvz_template';
			$command .= ' -d '.$template_url;
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command($command);
			$response->msg = sprintf($this->lang['msg_uploaded'], $template_url);
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'upload');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['url']['label']                         = $this->lang['form_url'];
		$d['url']['required']                      = true;
		$d['url']['object']['type']                = 'htmlobject_input';
		$d['url']['object']['attrib']['id']        = 'url';
		$d['url']['object']['attrib']['name']      = 'url';
		$d['url']['object']['attrib']['type']      = 'text';
		$d['url']['object']['attrib']['value']     = '';
		$d['url']['object']['attrib']['maxlength'] = 500;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
