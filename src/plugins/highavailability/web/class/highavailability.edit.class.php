<?php
/**
 * Edit highavailability
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class highavailability_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'highavailability_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'highavailability_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "highavailability_msg";
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

		$this->id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $this->id);

		$a = new appliance();
		$a = $a->get_instance_by_id($this->id);

		$r = new resource();
		$r = $r->get_instance_by_id($a->resources);
		$this->resource = $r;


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
		$t = $this->response->html->template($this->tpldir.'/highavailability-edit.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");

		$t->add(sprintf($this->lang['label'], $this->id), 'label');
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
			$timeout = $form->get_request('timeout');
			$this->resource->set_resource_capabilities('HA', $timeout);
			$response->msg = sprintf($this->lang['msg_timeout'], $this->id, ($timeout / 60));
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

		$s[] = array(60, 1);
		$s[] = array(120, 2);
		$s[] = array(180, 3);
		$s[] = array(240, 4);
		$s[] = array(300, 5);
		$s[] = array(360, 6);
		$s[] = array(420, 7);
		$s[] = array(480, 8);
		$s[] = array(540, 9);
		$s[] = array(600, 10);
		$s[] = array(1200, 20);
		$s[] = array(1800, 30);
		$s[] = array(2400, 40);
		$s[] = array(3000, 50);
		$s[] = array(3600, 60);

		$v = $this->resource->get_resource_capabilities('HA');

		$d['timeout']['label']                       = $this->lang['timeout'];
		$d['timeout']['required']                    = true;
		$d['timeout']['object']['type']              = 'htmlobject_select';
		$d['timeout']['object']['attrib']['name']    = 'timeout';
		$d['timeout']['object']['attrib']['title']   = $this->lang['timeout_title'];
		$d['timeout']['object']['attrib']['index']   = array(0,1);
		$d['timeout']['object']['attrib']['options'] = $s;
		if(isset($v) && $v !== '') {
			$d['timeout']['object']['attrib']['selected'] = array($v);
		}
		else {
			$d['timeout']['object']['attrib']['selected'] = array(240);
		}
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
