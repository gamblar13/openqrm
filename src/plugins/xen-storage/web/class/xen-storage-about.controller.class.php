<?php
/**
 * xen-storage-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class xen_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'xen_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'xen_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'xen_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xen_storage_about_identifier';
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
		'tab' => 'About Xen-Storage',
		'label' => 'About Xen-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Xen-Storage" plugin manages Xen Virtual Machines and their belonging virtual disk.
					   As common in openQRM the Virtual Machines and their virtual disk volumes are managed separately.
					   Therefore the "Xen-Storage" plugin splits up into VM- and Volume-Management.
					   The VM part provides the Virtual Machines which are abstracted as "resources".
					   The Storage part provides volumes which are abstracted as "images".
					   Appliance deployment automatically combines "resource" and "image".',
		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the Xen-Storage Host Appliance<br>(this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
				   <li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for Xen)</li>
				   <li>The following packages must be installed: xen (eventual xen-pxe), socat, bridge-utils, lvm2</li>
				   <li>For Xen LVM Storage: One (or more) lvm volume group(s) with free space dedicated for the Xen VM storage</li>
				   <li>For Xen Blockfile Storage: free space dedicated for the Xen VM storage</li>
				   <li>One or more bridges configured for the virtual machines</li>',
		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with Xen 4',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Virtualization types: "Xen-Storage Host" and "Xen-Storage VM"</li>
				   <li>Storage types: "Xen LVM Storage" and "Xen Blockfile Storage"</li>
				   <li>Deployment types: "LVM deployment for Xen" and "Blockfile deployment for Xen"</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Virtualization and Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Local Deployment for Virtual Machines',

		'migration_title' => 'Requirements for Xen live-migration',
		'migration_content' => 'Shared storage between the Xen-Storage Hosts for the location of the VM config files (/var/lib/xen-storage/openqrm)
					and a shared LVM volume group between the Xen-Storage Hosts',
		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'Xen-Storage Boot-Service',
		'boot_service_title' => 'Xen-Storage Host Boot-Service',
		'boot_service_content' => 'The Xen-Storage Plugin provides an openQRM Boot-Service.
			This "Xen-Storage Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/xen-storage/web/boot-service-xen-storage.tgz</b></i>
			<br>
			<br>
			The "Xen-Storage Boot-Service" contains the Client files of the Xen-Storage Plugin.<br>
			Also a configuration file for the Xen-Storage Hosts is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n xen-storage -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n xen-storage -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n xen-storage -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n xen-storage -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the Xen-Storage Host please edit:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/xen-storage/etc/openqrm-plugin-xen-storage.conf</b></i>
			<br>
			<br>
			and set the configuration keys according to your bridge-configuration.<br>
			<br>
			',
	),
	'storage' => array (
		'tab' => 'About Xen-Storage',
		'label' => 'About Storage in Xen-Storage',
		'storage_mgmt_title' => 'Xen Storage Management',
		'storage_mgmt_list' => '<li>Create a new storage from type "Xen LVM Storage" or "Xen Blockfile Storage"</li>
				   <li>Create a new Volume on this storage (either LVM or Blockfile)</li>
				   <li>Creating the Volume automatically creates a new Image using volume as root-device</li>',

	),
	'vms' => array (
		'tab' => 'About Xen-Storage',
		'label' => 'About Virtual Machines in Xen-Storage',
		'vm_mgmt_title' => 'Xen-Storage VM Management',
		'vm_mgmt_list' => '<li>Create a new appliance and set its resource type to "Xen-Storage Host"</li>
				   <li>Create and manage Xen virtual machines via the Xen-Storage VM Manager</li>',
	),
	'usage' => array (
		'tab' => 'About Xen-Storage',
		'label' => 'Xen-Storage Use-Cases',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/xen-storage/lang", 'xen-storage-about.ini');


		$this->tpldir   = $this->rootdir.'/plugins/xen-storage/tpl';
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
	 * About Xen-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen-storage/class/xen-storage-about.documentation.class.php');
			$controller = new xen_storage_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/xen-storage/class/xen-storage-about.bootservice.class.php');
			$controller = new xen_storage_about_bootservice($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/xen-storage/class/xen-storage-about.storage.class.php');
			$controller = new xen_storage_about_storage($this->openqrm, $this->response);
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
	 * About Xen VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vms( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen-storage/class/xen-storage-about.vms.class.php');
			$controller = new xen_storage_about_vms($this->openqrm, $this->response);
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
	 * About Xen Use-Cases
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function usage( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/xen-storage/class/xen-storage-about.usage.class.php');
			$controller = new xen_storage_about_usage($this->openqrm, $this->response);
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
