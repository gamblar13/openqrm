<?php
/**
 * Nagios3 Automap
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class nagios3_automap
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nagios3_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nagios3_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'nagios3_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nagios3_identifier';
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
		$this->tpldir   = $this->rootdir.'/plugins/nagios3/tpl';
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
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}

		switch( $this->action ) {
			// SELECT
			case $this->lang['action_map']:
			case 'map':
				$msg = $this->map();
			break;
			case $this->lang['action_enable_automap']:
			case 'enable':
				$msg = $this->automap('on');
			break;
			case $this->lang['action_disable_automap']:
			case 'disable':
				$msg = $this->automap('off');
			break;
		}
		$response = $this->get_response();
		if(isset($msg)) {
			$response->redirect(
				$response->get_url($this->actions_name, '', $this->message_param, $msg)
			);
		}

		$a          = $response->html->a();
		$a->href    = $response->html->thisfile.'?base=event';
		$a->label   = $this->lang['action_eventlist'];
		$a->title   = $this->lang['action_eventlist'];
		$a->handler = 'onclick="wait();"';

		$data['label']               = $this->lang['label'];
		$data['explanation_map']     = sprintf($this->lang['explanation_map'], $a->get_string());
		$data['explanation_automap'] = $this->lang['explanation_automap'];
		$data['please_wait']         = $this->lang['please_wait'];
		$data['prefix_tab']          = $this->prefix_tab;
		$data['baseurl']             = $this->openqrm->get('baseurl');
		$data['thisfile']            = $response->html->thisfile;
		$t = $response->html->template($this->tpldir.'/nagios3.automap.tpl.php');
		$t->add($data);
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;


	}

	//--------------------------------------------
	/**
	 * Map
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function map() {
		$cmd = $this->openqrm->get('basedir').'/plugins/nagios3/bin/openqrm-nagios-manager map';
		$oqs = new openqrm_server();
		$oqs->send_command($cmd);
		return $this->lang['msg_mapping'];
	}


	//--------------------------------------------
	/**
	 * Automap
	 *
	 * @access public
	 * @param enum $mode [enable|disable]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function automap($mode) {
		$cmd = $this->openqrm->get('basedir').'/plugins/nagios3/bin/openqrm-nagios-manager automap -t '. $mode;
		$oqs = new openqrm_server();
		$oqs->send_command($cmd);
		sleep(5);
		return $this->lang['msg_automap_'.$mode];
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
		$form = $response->get_form('', '');
		$auto = $response->html->input();
		$auto->type = 'submit';
		$auto->name = $this->actions_name;
		$auto->handler = 'onclick="wait();"';
		if (file_exists($this->openqrm->get('webdir')."/plugins/nagios3/.automap")) {
			$auto->value = $this->lang['action_disable_automap'];
		} else {
			$auto->value = $this->lang['action_enable_automap'];
		}
		$form->add($auto, 'automap');

		$map = $response->html->input();
		$map->type = 'submit';
		$map->name = $this->actions_name;
		$map->value = $this->lang['action_map'];
		$map->handler = 'onclick="wait();"';
		$form->add($map, 'map');

		$response->form = $form;
		return $response;
	}

}
?>
