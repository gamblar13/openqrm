<?php
/**
 * Add new hybrid-cloud account
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class hybrid_cloud_account_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_account_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_account_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_account_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_account_tab';
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->user = $openqrm->user();
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
		$response = $this->add();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		$a = $this->response->html->a();
		$a->label  = $this->lang['lang_help_link'];
		$a->target = '_blank';
		$a->href   = $this->openqrm->get('baseurl').'/plugins/hybrid-cloud/hybrid-cloud-example-rc-config.php';

		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-account-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label_add'], 'label');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->actions_name, 'actions_name');
		$t->add($this->lang['label_help'], 'label_help');
		$t->add(sprintf($this->lang['lang_help'], $a->get_string()), 'lang_help');
		$t->add($this->lang['lang_browse'], 'lang_browse');
		$t->add($this->lang['lang_browser'], 'lang_browser');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
	function add() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			require_once($this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
			$hc = new hybrid_cloud();
			$fi = $form->get_request();
			$fi['hybrid_cloud_id']  = openqrm_db_get_free_id('hybrid_cloud_id', $hc->_db_table);
			$hc->add($fi);
			$response->msg = sprintf($this->lang['msg_added'], $fi['hybrid_cloud_account_name']);
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
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');


		$type[] = array('uec', 'Ubuntu Enterprise Cloud');
		$type[] = array('aws', 'Amazon Cloud');
		$type[] = array('euca', 'Eucalyptus Cloud');

		$d['type']['label']                       = $this->lang['form_type'];
		$d['type']['object']['type']              = 'htmlobject_select';
		$d['type']['object']['attrib']['id']      = 'type';
		$d['type']['object']['attrib']['name']    = 'hybrid_cloud_account_type';
		$d['type']['object']['attrib']['index']   = array(0,1);
		$d['type']['object']['attrib']['options'] = $type;
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['name']      = 'hybrid_cloud_account_name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = '';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$d['rc_config']['label']                         = $this->lang['form_config'];
		$d['rc_config']['required']                      = true;
		$d['rc_config']['object']['type']                = 'htmlobject_input';
		$d['rc_config']['object']['attrib']['id']        = 'rc_config';
		$d['rc_config']['object']['attrib']['name']      = 'hybrid_cloud_rc_config';
		$d['rc_config']['object']['attrib']['type']      = 'text';
		$d['rc_config']['object']['attrib']['value']     = '';
		$d['rc_config']['object']['attrib']['maxlength'] = 255;

		$d['ssh']['label']                         = $this->lang['form_ssh'];
		$d['ssh']['required']                      = true;
		$d['ssh']['object']['type']                = 'htmlobject_input';
		$d['ssh']['object']['attrib']['id']        = 'ssh';
		$d['ssh']['object']['attrib']['name']      = 'hybrid_cloud_ssh_key';
		$d['ssh']['object']['attrib']['type']      = 'text';
		$d['ssh']['object']['attrib']['value']     = '';
		$d['ssh']['object']['attrib']['maxlength'] = 255;

		$d['description']['label']                         = $this->lang['form_description'];
		$d['description']['object']['type']                = 'htmlobject_textarea';
		$d['description']['object']['attrib']['id']        = 'description';
		$d['description']['object']['attrib']['name']      = 'hybrid_cloud_description';
		$d['description']['object']['attrib']['type']      = 'text';
		$d['description']['object']['attrib']['value']     = '';
		$d['description']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
