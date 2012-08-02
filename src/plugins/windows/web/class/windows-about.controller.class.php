<?php
/**
 * windows-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class windows_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'windows_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'windows_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'windows_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'windows_about_identifier';
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
		'tab' => 'About Windows',
		'label' => 'About Windows',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The Windows Plugin adds support for the Windows Operating Systems to openQRM.
			It consist of a basic monitoring agent and a remote-execution subsystem which runs
			on the Windows system as Windows services after integrating it with a simple setup program.',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<ul><li>The Windows system needs to be integrated before installing the openQRM Client on it!</li>
			<li>To integrate set the Systems BIOS to "network-boot" (PXE) and reboot</li>
			<li>The system will be network-booted and automatically discovered by openQRM</li>
			<li>Once the system is added to openQRM as a new resource reboot to Windows on the local disk</li>
			<li>Now follow the steps "Usage" to install the openQRM Client on the Windows system</li></ul>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the KVM-Storage, Sanboot-Storage and Xen-Storage.<br>
			The Windows openQRM Client is tested with Windows XP, Windows 7, Windows Server 2008 and Windows 8',
		'provides_title' => 'Provides',
		'provides_list' => '<ul><li>Adds support for the Windows Operating Systems to openQRM</li></ul>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
	),
	'usage' => array (
		'tab' => 'Windows openQRM Client',
		'label' => 'Windows openQRM Client',
		'setup_preparations' => 'Preparations before the setup',
		'setup_title' => 'Windows openQRM Client Setup',
		'setup_requirements1' => 'Before you run the setup program for the Windows openQRM-Client please create a new user "root" on the windows system!',
		'setup_requirements2' => 'Please make sure to have TCP port 22 (ssh) enabled in the Windows firewall!',
		'setup_requirements3' => 'Please run "gpedit.msc" and add the Permission to remote shutdown the system to user "root"<br><br>
			&nbsp;&nbsp;Local Computer Policy<br>
			 &nbsp;&nbsp;&nbsp;&nbsp;-> Computer Configuration<br>
			   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Software Settings<br>
			   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Windows Settings<br>
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Security Settings<br>
					 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Account Policies<br>
					 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Local Policies<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> Audit Policies<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> User Rights Assingment<br>
							 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--> Force shutdown from remote system - here assign user "root"<br><br>',
			
		'setup_instructions' => '<ul><li>Download the openQRM Client from here -> <a href="/openqrm/base/plugins/windows/openQRM-Client-4.8.0-setup.exe">openQRM-Client-4.8.0-setup.exe</a></li>
			<li>Run the openQRM-Client-setup.exe on the Windows system</li></ul>',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/windows/lang", 'windows-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/windows/tpl';
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
			case 'bootservice':
				$content[] = $this->bootservice(true);
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
	 * About Windows
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/windows/class/windows-about.documentation.class.php');
			$controller = new windows_about_documentation($this->openqrm, $this->response);
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
	 * Boot-Service
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function bootservice( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/windows/class/windows-about.bootservice.class.php');
			$controller = new windows_about_bootservice($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['bootservice'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['bootservice']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'bootservice' );
		$content['onclick'] = false;
		if($this->action === 'bootservice'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * About Windows VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/windows/class/windows-about.vms.class.php');
			$controller = new windows_about_vms($this->openqrm, $this->response);
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
	 * About Windows Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/windows/class/windows-about.usage.class.php');
			$controller = new windows_about_usage($this->openqrm, $this->response);
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
