<?php
/**
 * kvm-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class kvm_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'kvm_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'kvm_about_identifier';
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
		'tab' => 'About KVM',
		'label' => 'About KVM',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "KVM" plugin manages KVM Virtual Machines.
					   The virtual disk of those KVM Virtual Machines is designed to be attached by one of the "*-storage" plugins.
					   The KVM plugin provides only the Virtual Machines which are abstracted as "Resources".
					   Appliance deployment automatically combines "Resource" and an "Image" from one of the Storage plugins for network-deployment.',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the KVM Host Appliance<br>(this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
				   <li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for KVM)</li>
				   <li>The following packages must be installed: kvm (eventual kvm-pxe), socat, bridge-utils</li>
				   <li>One or more bridges configured for the virtual machines</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with KVM kvm-62<br>To benefit from the KVM "virtio" feature at least kvm-84 is needed',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Virtualization types: "KVM Host" and "KVM VM"</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Network-Deployment',

		'migration_title' => 'Requirements for KVM live-migration',
		'migration_content' => 'Shared storage between the KVM Hosts for the location of the VM config files (/var/lib/kvm/openqrm)',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'KVM Boot-Service',
		'boot_service_title' => 'KVM Host Boot-Service',
		'boot_service_content' => 'The KVM Plugin provides an openQRM Boot-Service.
			This "KVM Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/kvm/web/boot-service-kvm.tgz</b></i>
			<br>
			<br>
			The "KVM Boot-Service contains the Client files of the KVM Plugin.<br>
			Also a configuration file for the KVM Hosts is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n kvm -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n kvm -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n kvm -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n kvm -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the KVM Host please edit:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/kvm/etc/openqrm-plugin-kvm.conf</b></i>
			<br>
			<br>
			and set the configuration keys according to your bridge-configuration.<br>
			<br>
			',
	),
	'vms' => array (
		'tab' => 'About KVM',
		'label' => 'About Virtual Machines in KVM',
		'vm_mgmt_title' => 'KVM VM Management',
		'vm_mgmt_list' => '<li>Create a new appliance and set its resource type to "KVM Host"</li>
				   <li>Create and manage KVM virtual machines via the KVM VM Manager</li>',
	),
	'usage' => array (
		'tab' => 'About KVM',
		'label' => 'KVM Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/kvm/lang", 'kvm-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/kvm/tpl';
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
	 * About KVM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.documentation.class.php');
			$controller = new kvm_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.bootservice.class.php');
			$controller = new kvm_about_bootservice($this->openqrm, $this->response);
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
	 * About KVM VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.vms.class.php');
			$controller = new kvm_about_vms($this->openqrm, $this->response);
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
	 * About KVM Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/kvm/class/kvm-about.usage.class.php');
			$controller = new kvm_about_usage($this->openqrm, $this->response);
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
