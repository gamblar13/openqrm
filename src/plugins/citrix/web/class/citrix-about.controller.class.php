<?php
/**
 * citrix-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'citrix_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'citrix_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'citrix_about_identifier';
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
	'documentation' => array (
		'tab' => 'About Citrix XenServer',
		'label' => 'About Citrix XenServer',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Citrix-Storage" plugin integrates Citrix XenServer and provides "network-deployment" for Citrix XenServer Virtual Machines.<br><br>
			First step is to automatically discover and integrate one or more Citrix XenServer by using the "Auto-Discovery" menu.
			Then proceed with VM- and VDI Volume Management.',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>Citrix XenServer Server integrated in openQRM</li>
			<li>Citrix "xe" Commandline-Utility copied to the openQRM Systeem at "/usr/bin/xe"</li>
			<li>Optional: openQRM NoVNC Plugin for access to the VM console<br>(requires SSH Login enabled on the Citrix XenServer configuration)</li>',
		
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.<br>
			with Citrix XenServer 5.6 - 6.0',
		
		'provides_title' => 'Provides',
		'provides_list' => '<li>Virtualization types: "Citrix Host" and "Citrix VM"</li>',
		
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),
	'vms' => array (
		'tab' => 'About Citrix XenServer',
		'label' => 'About Virtual Machines in Citrix XenServer',
		'vm_mgmt_title' => 'Citrix XenServer VM Management',
		'vm_mgmt_list' => 'Citrix XenServer Virtual Machines are stored on a Citrix SR (Storage Resource).
			To create a Virtual Machine use the VM Manager and select one of the integrated Citrix XenServer.
			Then use the included SR Datastore Manager to connect a NAS- and/or iSCSI Datastore.<br><br>
			<strong>Hint:</strong><br>
			You can use the "nfs-storage" and/or the "iscsi-storage" plugin to easily create a NAS- and/or iSCSI Datastore to be used as a Citrix Storage Resource!<br><br>
			After connecting a SR use the VM Manager to create new Virtual Machines.
			Creating a new VM automatically creates a new Resource for deployment via an Appliance in openQRM.',
	),
	'usage' => array (
		'tab' => 'About Citrix XenServer',
		'label' => 'Citrix XenServer Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/citrix/lang", 'citrix-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/citrix/tpl';
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
			$this->action = "documentation";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'documentation':
				$content[] = $this->documentation(true);
			break;
			case 'vms':
				$content[] = $this->vms(true);
			break;
			case 'usage':
				$content[] = $this->usage(true);
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
	 * About Citrix XenServer
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-about.documentation.class.php');
			$controller = new citrix_about_documentation($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['documentation'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['documentation']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'documentation' );
		$content['onclick'] = false;
		if($this->action === 'documentation'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * About Citrix XenServer VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-about.vms.class.php');
			$controller = new citrix_about_vms($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['vms'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['vms']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vms' );
		$content['onclick'] = false;
		if($this->action === 'vms'){
			$content['active']  = true;
		}
		return $content;
	}


	
	
	//--------------------------------------------
	/**
	 * About Citrix XenServer Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-about.usage.class.php');
			$controller = new citrix_about_usage($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['usage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['usage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'usage' );
		$content['onclick'] = false;
		if($this->action === 'usage'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
