<?php
/**
 * lxc-storage-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class lxc_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lxc_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'lxc_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lxc_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lxc_storage_about_identifier';
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
		'tab' => 'About LXC-Storage',
		'label' => 'About LXC-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "LXC-Storage" plugin integrates <a href="http://lxc.sourceforge.net/" target="_BLANK">LXC</a>.
			It manages LXC Virtual Machines and their belonging virtual disk. As common in openQRM the Virtual Machines and their virtual disk volumes are managed separately.
			Therefore the "LXC-Storage" plugin splits up into VM- and Volume-Management. The VM part provides the Virtual Machines which are abstracted as "Resources".
			The Storage part provides volumes which are abstracted as "Images". Appliance deployment automatically combines "resource" and "Image".',
			
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the LXC-Storage Host Appliance<br>(this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
				   <li>The following packages must be installed: ovzkernel/vzkernel, screen, e2fsprogs</li>
				   <li>For LXC LVM Storage: One (or more) LVM volume group(s) with free space dedicated for the LXC VM Storage</li>
				   <li>One or more bridges configured for the virtual machines</li>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with with the Debian, Ubuntu and CentOS Linux distributions.',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Virtualization types: "LXC Host" and "LXC VM"</li>
				   <li>Storage types: "LXC LVM Storage"</li>
				   <li>Deployment types: "LVM deployment for LXC"</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization and Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local Deployment for Virtual Machines',

		'migration_title' => 'Requirements for LXC live-migration',
		'migration_content' => 'Shared LVM volume group between the LXC-Storage Hosts',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'LXC-Storage Boot-Service',
		'boot_service_title' => 'LXC-Storage Host Boot-Service',
		'boot_service_content' => 'The LXC-Storage Plugin provides an openQRM Boot-Service.
			This "LXC-Storage Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/lxc-storage/web/boot-service-lxc-storage.tgz</b></i>
			<br>
			<br>
			The "LXC-Storage Boot-Service" contains the Client files of the LXC-Storage Plugin.<br>
			Also a configuration file for the LXC-Storage Hosts is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n lxc-storage -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n lxc-storage -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n lxc-storage -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n lxc-storage -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the LXC-Storage Host please edit:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/lxc-storage/etc/openqrm-plugin-lxc-storage.conf</b></i>
			<br>
			<br>
			and set the configuration keys according to your bridge-configuration.<br>
			<br>
			',
	),
	'storage' => array (
		'tab' => 'About LXC-Storage',
		'label' => 'About Storage in LXC-Storage',
		'storage_mgmt_title' => 'LXC Storage Management',
		'storage_mgmt_list' => '<li>Create a new storage from type "LXC LVM Storage"</li>
				   <li>Create a new Volume on this storage</li>
				   <li>Creating the Volume automatically creates a new Image using volume as root-device</li>',

	),
	'vms' => array (
		'tab' => 'About LXC-Storage',
		'label' => 'About Virtual Machines in LXC-Storage',
		'vm_mgmt_title' => 'LXC-Storage VM Management',
		'vm_mgmt_list' => '<li>Create a new appliance and set its resource type to "LXC Host" (please check the requirements)</li>
				   <li>Create and manage LXC virtual machines via the LXC VM Manager</li>',
	),
	'usage' => array (
		'tab' => 'About LXC-Storage',
		'label' => 'LXC-Storage Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/lxc-storage/lang", 'lxc-storage-about.ini');


		$this->tpldir   = $this->rootdir.'/plugins/lxc-storage/tpl';
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
			case 'storage':
				$content[] = $this->storage(true);
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
	 * About LXC-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage-about.documentation.class.php');
			$controller = new lxc_storage_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage-about.bootservice.class.php');
			$controller = new lxc_storage_about_bootservice($this->openqrm, $this->response);
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
	 * About Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function storage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage-about.storage.class.php');
			$controller = new lxc_storage_about_storage($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang            = $this->lang['storage'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['storage']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'storage' );
		$content['onclick'] = false;
		if($this->action === 'storage'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * About LXC VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage-about.vms.class.php');
			$controller = new lxc_storage_about_vms($this->openqrm, $this->response);
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
	 * About LXC Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage-about.usage.class.php');
			$controller = new lxc_storage_about_usage($this->openqrm, $this->response);
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
