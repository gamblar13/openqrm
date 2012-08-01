<?php
/**
 * Appliance step4 (image)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class appliance_step4
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
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

		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
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

			if(isset($response->image_edit)) {
				$id        = $this->response->html->request()->get('appliance_id');
				$appliance = new appliance();
				$appliance->get_instance_by_id($id);
				// image-edit
				$this->response->redirect(
					$this->response->html->thisfile.'?base=image&image_action=edit&image_id='.$appliance->imageid
				);

			} else {
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'step5', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/appliance-step4.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
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
	function add() {		
		$response  = $this->get_response();
		$form      = $response->form;
		$id        = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);
		if(!$form->get_errors() && $this->response->submit()) {

			$image_edit = $form->get_request('image_edit');

			$fields['appliance_wizard'] = 'wizard=step5,user='.$this->user->name;
			$appliance->update($id, $fields);

			$response->msg = sprintf($this->lang['msg'], $appliance->name);

			// reset wizard
			$rs = $this->user->set_wizard($this->user->name, 'appliance', 5, $id);
			// update long term event, remove old event and add new one
			$event = new event();
			$event_description_step3 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 3, $this->user->name);
			$event_description_step4 = sprintf($this->lang['appliance_create_in_progress_event'], $appliance->name, 4, $this->user->name);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 10, "add", $event_description_step3, "", "", 0, 0, 0);
			$event->log("appliance", $_SERVER['REQUEST_TIME'], 9, "add", $event_description_step4, "", "", 0, 0, 0);

			// guide where to redirect, for openqrm as the resource we do not allow editing the image
			if ($appliance->resources == 0) {
				$response->msg = sprintf($this->lang['notice_openqrm_image_not_editable']);
			} else {
				if ($image_edit === 'on') {
					$response->image_edit = "step5";
				}
			}

		}
		$response->name = $appliance->name;
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
		$form = $response->get_form($this->actions_name, 'step4');

		$id        = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($id);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		// do you want to edit the image ?
		$d['image_edit']['label']                         = $this->lang['form_image'];
		$d['image_edit']['object']['type']                = 'htmlobject_input';
		$d['image_edit']['object']['attrib']['type']      = 'checkbox';
		$d['image_edit']['object']['attrib']['id']        = 'image_edit';
		$d['image_edit']['object']['attrib']['name']      = 'image_edit';
		if ($appliance->resources == 0) {
			$d['image_edit']['object']['attrib']['checked']   = false;
		} else {
			$d['image_edit']['object']['attrib']['checked']   = true;
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
