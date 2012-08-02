<?php
/**
 * Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


class linuxcoe_template_edit
{

var $identifier_name;
var $lang;
var $actions_name = 'linuxcoe-remove';



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
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->template_name = $response->html->request()->get('linuxcoe_template');
		$this->response->add('linuxcoe_template', $this->template_name);
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

		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
	
		$t = $this->response->html->template($this->tpldir."/linuxcoe-template-edit.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->template_name), 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($response->form);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {

		$response = $this->get_response();
		$form     = $response->form;

		if(!$form->get_errors() && $response->submit()) {
			$template_comment		= $this->response->html->request()->get('template_comment');
			file_put_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info", $template_comment);
			$response->msg = sprintf($this->lang['msg_edit'], $this->template_name);
		}
		return $response;

	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
		
		$lcoe_profile_comment_str = '';
		if (file_exists($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info")) {
			$lcoe_profile_comment_str = file_get_contents($this->webdir."/plugins/linuxcoe/profiles/".$this->template_name."/openqrm.info");
		}
		
		$d['template_comment']['label']                     = $this->lang['form_comment'];
		$d['template_comment']['validate']['regex']         = '/^[a-z0-9._ -]+$/i';
		$d['template_comment']['validate']['errormsg']      = sprintf($this->lang['error_comment'], 'a-z0-9._ -');
		$d['template_comment']['object']['type']            = 'htmlobject_textarea';
		$d['template_comment']['object']['attrib']['name']  = 'template_comment';
		$d['template_comment']['object']['attrib']['value'] = $lcoe_profile_comment_str;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
