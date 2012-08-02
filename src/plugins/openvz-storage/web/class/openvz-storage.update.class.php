<?php
/**
 * openvz-Storage update Templates
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openvz_storage_update
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
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'openvz_identifier';
/**
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
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
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->volgroup = $this->response->html->request()->get('volgroup');
		$this->template = $this->response->html->request()->get('template');
		$this->response->add('template', $this->template);
		require_once($this->openqrm->get('webdir').'/plugins/openvz-storage/class/openvz-template.class.php');
		$this->openvztemplate = new openvztemplate();
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
		$response = $this->update();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/openvz-storage-update.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->template), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$template = $this->response->html->request()->get('template');
			$comment     = $form->get_request('comment');

			$this->openvztemplate->get_instance_by_name($template);
			if ($this->openvztemplate->id === '') {
				// add
				$openvz_fields["openvz_template_id"] = openqrm_db_get_free_id('openvz_template_id', $this->openvztemplate->_db_table);
				$openvz_fields["openvz_template_name"] = $template;
				$openvz_fields["openvz_template_description"] = $comment;
				$this->openvztemplate->add($openvz_fields);
			} else {
				// update
				$openvz_fields["openvz_template_description"] = $comment;
				$this->openvztemplate->update($this->openvztemplate->id, $openvz_fields);
			}
			$this->openvztemplate->id = '';
			$response->msg = sprintf($this->lang['msg_updated'], $this->template);
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
		$form = $response->get_form($this->actions_name, 'update');
		$template = $this->response->html->request()->get('template');

		$comment = '';
		$this->openvztemplate->get_instance_by_name($template);
		if ($this->openvztemplate->id != '') {
			if (strlen($this->openvztemplate->description)) {
				$comment = $this->openvztemplate->description;
			}
		}
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['comment']['label']                         = $this->lang['form_comment'];
		$d['comment']['required']                      = true;
		$d['comment']['object']['type']                = 'htmlobject_input';
		$d['comment']['object']['attrib']['id']        = 'comment';
		$d['comment']['object']['attrib']['name']      = 'comment';
		$d['comment']['object']['attrib']['type']      = 'text';
		$d['comment']['object']['attrib']['value']     = $comment;
		$d['comment']['object']['attrib']['maxlength'] = 250;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
