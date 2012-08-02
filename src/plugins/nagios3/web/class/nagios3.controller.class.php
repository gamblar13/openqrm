<?php
/**
 * Nagios3 Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class nagios3_controller
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
var $lang = array(
	'services' => array(
		'tab' => 'Services',
		'label' => 'Nagios3 Services',
		'label_edit' => 'Edit service %s',
		'label_add' => 'Add a new service',
		'label_delete' => 'Delete service(s)',
		'id' => 'ID',
		'name' => 'Service',
		'type' => 'Type',
		'port' => 'Port',
		'description' => 'Description',
		'action_delete' => 'delete',
		'action_edit' => 'edit',
		'action_add' => 'Add new service',
		'select_service' => 'Select a service',
		'or_manually_add' => 'or manually add a service',
		'manual_port' => 'Port',
		'manual_type' => 'Type',
		'manual_service' => 'Service',
		'manual_description' => 'Description',
		'error_manual_port' => 'Port must be a number',
		'error_in_use' => 'Service %s is in use by an appliance',
		'error_port_in_use' => 'Port %s is already in use',
		'msg_deleted' => 'Deleted service %s',
		'msg_added' => 'Added service %s',
		'msg_updated' => 'Updated %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'appliance' => array(
		'tab' => 'Appliances',
		'label' => 'Nagios3 appliances',
		'label_edit' => 'Edit services for appliance %s',
		'id' => 'ID',
		'appliance' => 'Appliance',
		'name' => 'Name',
		'resource' => 'Resource',
		'services' => 'Services',
		'action_edit' => 'edit',
		'select_services' => 'Select services',
		'msg_updated' => 'Updated appliance %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..'
	),
	'automap' => array(
		'tab' => 'AutoConfig',
		'label' => 'Nagios3 AutoConfig',
		'action_map' => 'Map openQRM network',
		'explanation_map' => 'Click on the button below to automatically map the openQRM network into Nagios. 
			Please notice that generating the Nagios configuration will take some time. You can check the status in the %s',
		'explanation_automap' => 'Click on the button below to enable/disable automatic mapping of the openQRM network into Nagios.',
		'action_eventlist' => 'Event List',
		'action_enable_automap' => 'Enable automap',
		'action_disable_automap' => 'Disable automap',
		'msg_automap_on' => 'Enabled automap',
		'msg_automap_off' => 'Disabled automap',
		'msg_mapping' => 'Started mapping openQRM network',
		'please_wait' => 'Loading. Please wait ..',
	),
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
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/nagios3/lang", 'nagios3.ini');
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

		$this->response->add($this->actions_name, $this->action);

		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'services':
				$content[] = $this->services(true);
				$content[] = $this->appliance(false);
				$content[] = $this->automap(false);
			break;
			case 'appliance':
				$content[] = $this->services(false);
				$content[] = $this->appliance(true);
				$content[] = $this->automap(false);
			break;
			case 'automap':
				$content[] = $this->services(false);
				$content[] = $this->appliance(false);
				$content[] = $this->automap(true);
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
	 * Services
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function services( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nagios3/class/nagios3.services.class.php');
			$response = $this->response;

			$controller = new nagios3_services($this->openqrm, $response);
			$controller->actions_name  = 'nagios3_services';
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['services'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['services']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'services' );
		$content['onclick'] = false;
		if($this->action === 'services'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function appliance( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nagios3/class/nagios3.appliance.class.php');
			$response = $this->response;

			$controller = new nagios3_appliance($this->openqrm, $response);
			$controller->actions_name  = 'nagios3_appliance';
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['appliance'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['appliance']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'appliance' );
		$content['onclick'] = false;
		if($this->action === 'appliance'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Automap
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function automap( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/nagios3/class/nagios3.automap.class.php');
			$response = $this->response;

			$controller = new nagios3_automap($this->openqrm, $response);
			$controller->actions_name  = 'nagios3_automap';
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['automap'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['automap']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'automap' );
		$content['onclick'] = false;
		if($this->action === 'automap'){
			$content['active']  = true;
		}
		return $content;
	}


}
?>
