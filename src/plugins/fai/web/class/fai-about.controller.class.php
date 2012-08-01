<?php
/**
 * fai-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


class fai_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'fai_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'fai_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'fai_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'fai_about_identifier';
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
		'tab' => 'About FAI',
		'label' => 'About FAI',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "FAI" plugin integrates <a href="http://fai-project.org/" target="_BLANK">FAI</a> Install Server for automatic Linux deployments.
					   ',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the FAI Install Server Storage (a remote system with FAI installed and setup integrated into openQRM via the "local-server" plugin)</li>
				   <li>The following packages must be installed: screen</li>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Storage type: "fai-deployment"</li>
					<li>Deployment types: "Automatic Linux Installation (FAI)"</li>',

		'howto_title' => 'How to use',
		'howto_list' => '<li>Integrate a FAI install server into openQRM via the "local-server" plugin</li>
					<li>Create a new Storage server from the type "fai-deployment" using the FAI systems resource</li>
					<li>Images for local-deployment can now be set to "install-from-template" via FAI</li>
					<li>Add the FAI snippet <a href="/openqrm/boot-service/openqrm_client_fai_auto_install.snippets" target="_BLANK">openqrm_client_fai_auto_install.snippets</a> to your "preseed" configuration files post section  on your FAI server to automatically install the openQRM Client on the provisioned systems</li>',

		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local-Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'FAI Boot-Service',
		'boot_service_title' => 'FAI Boot-Service',
		'boot_service_content' => 'The FAI Plugin provides an openQRM Boot-Service.
			This "FAI Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/fai/web/boot-service-fai.tgz</b></i>
			<br>
			<br>
			The "FAI Boot-Service contains the Client files of the FAI Plugin.<br>
			Also a configuration file for the FAI server is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n fai -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n fai -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n fai -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n fai -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/fai/lang", 'fai-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/fai/tpl';
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
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * About FAI
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/fai/class/fai-about.documentation.class.php');
			$controller = new fai_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/fai/class/fai-about.bootservice.class.php');
			$controller = new fai_about_bootservice($this->openqrm, $this->response);
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




}
?>
