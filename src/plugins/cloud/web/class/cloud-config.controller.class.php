<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class cloud_config_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_config';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-cloud-config";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'project_tab';
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
	'cloud_config_list' => 'Configuration',
	'cloud_config_key' => 'Name',
	'cloud_config_value' => 'Value',
	'cloud_config_id' => 'ID',
	'cloud_config_description' => 'Description',
	'cloud_config_management' => 'Main Cloud Configuration',
	'cloud_config_update_successful' => 'Successful updated Cloud Configuration.',
	'cloud_config_update' => 'Update',
	'cloud_config_actions' => 'Actions',
	'cloud_config_description_1' => 'The email address for the Cloud to send status messages and events to.',
	'cloud_config_description_2' => 'If the Cloud should automatically provision Systems or wait until a Cloud administrator approves the request.',
	'cloud_config_description_3' => 'The external DNS/Domain name for the Cloud portal accessible for the Cloud Users.',
	'cloud_config_description_4' => 'If the Cloud should also enable the automatic provisioning of physical Server.',
	'cloud_config_description_5' => 'By default the Cloud provisions a clone of the requested Image (not the origin)',
	'cloud_config_description_6' => 'Global Cloud limit. Until openQRM 5.0 this is statically set to 1.',
	'cloud_config_description_7' => 'If the Cloud should automatically create Virtual Machines on available Virtualization Host Appliances or if it should use a static pool of pre-created VMs.',
	'cloud_config_description_8' => 'Global Cloud limit. Maximum overall disk space (in MB) used by a Cloud User.',
	'cloud_config_description_9' => 'Global Cloud limit. Maximum overall network-interfaces used by a Cloud User.',
	'cloud_config_description_10' => 'If to show the Highavailability option for Cloud requests. Needs the highavailability plugin enabled and started.',
	'cloud_config_description_11' => 'If to show the automatic Application deployment option (Puppet) for Cloud requests. Needs the puppet plugin enabled and started.',
	'cloud_config_description_12' => 'Automatically provides some Cloud Computing Units (CCUS, the virtual currency of the Cloud) to new registered Cloud User.',
	'cloud_config_description_13' => 'Global Cloud limit. Maximum overall number of active appliances used by a Cloud User.',
	'cloud_config_description_14' => 'If Cloud Users should be able to register themselves via the public portal.',
	'cloud_config_description_15' => 'Use this option to set the Cloud in a maintenance mode. If set to false running systems will stay as they are but Cloud Users will not be able to submit new requests.',
	'cloud_config_description_16' => 'Enables/disables the internal billing mechanism. If disabled Cloud Users will not be charged.',
	'cloud_config_description_17' => 'If to enable/disable the Web-SSH login option for the Cloud Users. Needs the sshterm plugin enabled and started.',
	'cloud_config_description_18' => 'If to translate the (private) openQRM managed network to a public network. Requires to set pre/post-routing on the gateway/router to the external (public) network.',
	'cloud_config_description_19' => 'If to enable/disable System statistics for systems requested by the Cloud Users. Needs the collectd plugin enabled and started.',
	'cloud_config_description_20' => 'If to provide the Disk-resize option to the Cloud Users.',
	'cloud_config_description_21' => 'The private Image option allows to map certain Images to specific Cloud Users.',
	'cloud_config_description_22' => 'Enables/disables the Cloud Product-Manager.',
	'cloud_config_description_23' => 'The real currency to which the virtual Cloud currency (CCUs) are mapped to.',
	'cloud_config_description_24' => 'Defines the mapping/value of 1000 CCUs (virtual Cloud currency, CCUs) to the real currency defined in config option 23.',
	'cloud_config_description_25' => 'Allows mapping of Virtualization Host appliances to specific Cloud Usergroups.',
	'cloud_config_description_26' => 'Enables/disables the automatic IP-address configuration for the external (public) network interfaces of the requested Cloud Systems. Requires the ip-mgmt plugin to be enabled and started.',
	'cloud_config_description_27' => 'Performance optimization parameter. How many actions should run in phase 1.',
	'cloud_config_description_28' => 'Performance optimization parameter. How many actions should run in phase 2.',
	'cloud_config_description_29' => 'Performance optimization parameter. How many actions should run in phase 3.',
	'cloud_config_description_30' => 'Performance optimization parameter. How many actions should run in phase 4.',
	'cloud_config_description_31' => 'Performance optimization parameter. How many actions should run in phase 5.',
	'cloud_config_description_32' => 'Performance optimization parameter. How many actions should run in phase 6.',
	'cloud_config_description_33' => 'Performance optimization parameter. How many actions should run in phase 7.',
	'cloud_config_description_34' => 'If to allow Cloud Users to provision their own hostnames for their Cloud systems.',
	'cloud_config_description_35' => 'If this Cloud is an openQRM Enterprise Cloud Zones client.',
	'cloud_config_description_36' => 'Defines the openQRM Enterprise Cloud Zones IP-address.',
	'cloud_config_description_37' => 'Defines the public IP-address of this Cloud.',
	'cloud_config_description_38' => 'Sends a deprovision warning to the user when the configured CCU number is reached.',
	'cloud_config_description_39' => 'Pauses Appliances of requests when the configured CCU number is reached.',
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
		$this->webdir   = $this->openqrm->get('webdir');
		$this->rootdir  = $this->openqrm->get('rootdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-config.ini');
		$this->tpldir   = $this->webdir.'/plugins/cloud/tpl';
		$this->identifier_name = "cloud_config_id";
		require_once $this->webdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->webdir."/class/htmlobjects/");
		$this->html->lang = $this->user->translate($this->html->lang, $this->webdir."/plugins/cloud/lang", 'htmlobjects.ini');
		require_once($this->webdir.'/plugins/cloud/class/cloudconfig.class.php');
		$this->config = new cloudconfig();
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
				$content[] = $this->update(false);
			break;
			case 'update':
				$content[] = $this->select(false);
				$content[] = $this->update(true);
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
	 * select
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-config.select.class.php');
			$controller = new cloud_config_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang;
			$controller->config          = $this->config;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_config_list'];
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
	 * Update
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-config.update.class.php');
			$controller = new cloud_config_update($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang;
			$controller->config          = $this->config;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_config_update'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
