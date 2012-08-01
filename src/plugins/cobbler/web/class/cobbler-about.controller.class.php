<?php
/**
 * cobbler-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


class cobbler_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cobbler_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'cobbler_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'cobbler_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'cobbler_about_identifier';
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
		'tab' => 'About Cobbler',
		'label' => 'About Cobbler',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Cobbler" plugin integrates <a href="http://cobbler.github.com/" target="_BLANK">Cobbler</a> Install Server for automatic Linux deployments.
					   ',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the Cobbler Install Server Storage (a remote system with Cobbler installed and setup integrated into openQRM via the "local-server" plugin)</li>
				   <li>The following packages must be installed: screen</li>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Storage type: "cobbler-deployment"</li>
					<li>Deployment types: "Automatic Linux Installation (Cobbler)"</li>',

		'howto_title' => 'How to use',
		'howto_list' => '<li>Integrate a Cobbler install server into openQRM via the "local-server" plugin</li>
					<li>Create a new Storage server from the type "cobbler-deployment" using the Cobbler systems resource</li>
					<li>Images for local-deployment can now be set to "install-from-template" via Cobbler</li>
					<li>Add the Cobbler snippet <a href="/openqrm/boot-service/openqrm_client_auto_install.snippets" target="_BLANK">openqrm_client_auto_install.snippets</a> to your Cobbler profiles to automatically install the openQRM Client on the provisioned systems</li>',

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
		'label' => 'Cobbler Boot-Service',
		'boot_service_title' => 'Cobbler Boot-Service',
		'boot_service_content' => 'The Cobbler Plugin provides an openQRM Boot-Service.
			This "Cobbler Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/cobbler/web/boot-service-cobbler.tgz</b></i>
			<br>
			<br>
			The "Cobbler Boot-Service contains the Client files of the Cobbler Plugin.<br>
			Also a configuration file for the Cobbler server is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n cobbler -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n cobbler -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n cobbler -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n cobbler -a [appliance-name] -k [key] -v [value]</b></i>
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/cobbler/lang", 'cobbler-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/cobbler/tpl';
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
	 * About Cobbler
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/cobbler/class/cobbler-about.documentation.class.php');
			$controller = new cobbler_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/cobbler/class/cobbler-about.bootservice.class.php');
			$controller = new cobbler_about_bootservice($this->openqrm, $this->response);
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
