<?php
/**
 * puppet Appliance
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class puppet_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "puppet_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'puppet_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'puppet_identifier';
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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';

		require_once($this->rootdir.'/plugins/puppet/class/puppet.class.php');
		$this->puppet = new puppet();

		$id = $this->response->html->request()->get('appliance_id');
		$this->response->add('appliance_id', $id);

		$appliance = new appliance();
		$this->appliance = $appliance->get_instance_by_id($id);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action() {

		$response = $this->edit();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$response->redirect(
					$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		$data['canceled'] = $this->lang['canceled'];
		$data['please_wait'] = $this->lang['please_wait'];
		$data['prefix_tab'] = $this->prefix_tab;
		$data['label'] = sprintf($this->lang['label'], $this->appliance->name);
		$data['baseurl'] = $this->openqrm->get('baseurl');
		$data['thisfile']    = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/puppet-edit.tpl.php');
		$t->add($response->form);
		$t->add($data);
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
		$form = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$groups = $form->get_request('groups');
			$this->puppet->remove_appliance($this->appliance->name);
			if(!in_array('{empty}', $groups)) {
				$this->puppet->set_groups($this->appliance->name, $groups);
			}
			$response->msg = sprintf($this->lang['msg_updated'], $this->appliance->name);
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [select|edit]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {

		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'edit');
		$selected = $this->puppet->get_groups($this->appliance->name);
		$groups   = $this->puppet->get_available_groups();

		$select = array('{empty}', '&#160;');
		foreach($groups as $v) {
			$o = $response->html->option();
			$o->value = $v;
			$o->label = $v;
			$o->title = $this->puppet->get_group_info($v);
			$select[] = $o;
		}
		$d['select']['label']                        = $this->lang['puppet_groups'];
		$d['select']['object']['type']               = 'htmlobject_select';
		$d['select']['object']['attrib']['name']     = 'groups[]';
		$d['select']['object']['attrib']['index']    = array(0,1);
		$d['select']['object']['attrib']['multiple'] = true;
		$d['select']['object']['attrib']['css']      = 'puppet_select';
		$d['select']['object']['attrib']['options']  = $select;
		$d['select']['object']['attrib']['selected'] = $selected;

		$form->add($d);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;

	}

}
?>
