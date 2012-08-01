<?php
/**
 * highavailability Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class highavailability_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'highavailability_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "highavailability_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'highavailability_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'highavailability_identifier';
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
var $lang = array(
	'select' => array (
		'tab' => 'Highavailability',
		'label' => 'Appliance highavailability',
		'action_enable' => 'enable',
		'action_enable_title' => 'Enable highavailability timeout',
		'action_disable' => 'disable',
		'action_disable_title' => 'Disable %s minutes highavailability timeout',
		'action_edit' => 'Edit highavailability timeout',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_kernel' => 'Kernel',
		'table_image' => 'Image',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'please_wait' => 'Loading. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit',
		'label' => 'Edit highavailability timeout for appliance %s',
		'timeout' => 'Timeout',
		'timeout_title' => 'Timeout in minutes',
		'msg_timeout' => 'Changed appliance %s highavailability timeout to %s minutes',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'msg_enabled' => 'Enabled appliance %s highavailability timeout',
	'msg_disabled' => 'Disabled appliance %s highavailability timeout',

);

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
		$this->tpldir   = $this->rootdir.'/plugins/highavailability/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/highavailability/lang", 'highavailability.ini');

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
		if($this->response->cancel()) {
			$this->action = "select";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case $this->lang['select']['action_enable']:
			case 'enable':
				$this->enable();
			break;
			case $this->lang['select']['action_disable']:
			case 'disable':
				$this->disable();
			break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}
	
	//--------------------------------------------
	/**
	 * Select Appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/highavailability/class/highavailability.select.class.php');
			$controller = new highavailability_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Edit appliance (resource)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/highavailability/class/highavailability.edit.class.php');
			$controller = new highavailability_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Enable ha
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function enable() {
		$msg = array();
		$appliances = $this->response->html->request()->get($this->identifier_name);
		if($appliances !== '') {
			$appliance = new appliance();
			foreach($appliances as $id) {
				$appliance->update($id, array('appliance_highavailable' => 1));
				$msg[] = sprintf($this->lang['msg_enabled'], $id);
			}
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, implode('<br>', $msg))
		);
	}

	//--------------------------------------------
	/**
	 * Disable ha
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function disable() {
		$msg = array();
		$appliances = $this->response->html->request()->get($this->identifier_name);
		if($appliances !== '') {
			$appliance = new appliance();
			foreach($appliances as $id) {
				$appliance->update($id, array('appliance_highavailable' => 0));
				$msg[] = sprintf($this->lang['msg_disabled'], $id);
			}
		}
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'select', $this->message_param, implode('<br>', $msg))
		);
	}



}
?>
