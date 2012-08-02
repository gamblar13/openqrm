<?php
/**
 * local-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


class local_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'local_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'local_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'local_storage_about_identifier';
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
		'tab' => 'About Local-Storage',
		'label' => 'About Local-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Local-Storage" plugin integrates <a href="http://clonezilla.org/" target="_BLANK">Clonezilla</a>
			and provides network-deployment to the physical server or virtual machines local-disk. 
			The templates for the automatic deployment via harddisk-cloning are created (grabbed) from existing, local-installed physical systems or VMs.
			Templates are then attached to ("local-storage") Images via the "install-from-template" mechanism pointing the automtic installation to the local harddisk.
			<br><br>
			<strong>Templates</strong>
			<br>
			<ul><li>
				Create a Storage object from the type "local-storage" (please check the requirements).
			</li><li>
				Enable and start the "dhcpd" and "tftpd" plugins providing the network-boot environment.
			</li><li>
				Network-boot a physical, local-installed server (or local-installed Virtual Machine) into openQRM via PXE.
			</li><li>
				Create a new "local-storage" template location via the "Template Admin". The size should be a bit bigger than the used space on the PXE-booted systems harddisk(s).
			</li><li>
				Create a new "local-storage" template by clicking on the "grab" button.
			</li><li>
				In the next step please select the "idle" network-booted System.
			</li></ul>
			<br>
			The "idle" System reboots, starts Clonezilla, mounts the "local-storage" template location via NFS and transfers
				the content of all harddisks (only the used blocks) to the "local-storage" template location.
				After that it reboots to idle.
			<br><br>
			<strong>Images</strong>
			<br>
			<ul><li>
				Create a Storage object from the type "local-storage" (please check the requirements).
				(can be the same as used for creating templates)
			</li><li>
				Create a new "local-storage" Image to be used in an Appliance
			</li></ul>
			<br><br>
			<strong>Deployment to Physical Systems</strong>
			<br>
			For "local-storage" deployment please create a new Appliance using a network-booted physical System as its resource.
			Select a (previously created) "local-storage" Image and attach a (previously created) Template to it. Start the Appliance.
			<br><br>
			The "idle" System reboots, starts Clonezilla, mounts the "local-storage" template location via NFS and transfers
				the template content to all harddisks (only the used blocks).
				After that it reboots from its now populated local harddisk.
			<br><br>
			<strong>Deployment to Virtual Machines</strong>
			<br>
			"local-storage" deployment is also supported for Virtual Machines from following types:
			<ul><li>
				kvm-storage
			</li><li>
				xen-storage
			</li></ul>
			<br>
			To deploy a Virtual Machines local harddisk with "local-storage" simply attach a "local-storage" Template to the VMs Image.
			Same as for physical Systems the Virtual Machine local harddisk is deployed with an initial network-boot via Clonezilla during the start of its Appliance.
			<br><br>
		.',

		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the Local-Storage Storage (this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
			<li>A LVM Volume group on the integrated system with available free space</li>
			',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.
			<br><br>
			Deployment via the Local-Storage plugin is Operating-System independent! (Windows/Linux)',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Storage type: "Local Storage"</li><li>Deployment types: "Local Storage"</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Deployment',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Network-Deployment to local disk',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/local-storage/lang", 'local-storage-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/local-storage/tpl';
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
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}


	//--------------------------------------------
	/**
	 * About Local-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/local-storage/class/local-storage-about.documentation.class.php');
			$controller = new local_storage_about_documentation($this->openqrm, $this->response);
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


}
?>
