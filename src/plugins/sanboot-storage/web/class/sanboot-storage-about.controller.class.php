<?php
/**
 * sanboot-about Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


class sanboot_storage_about_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'sanboot_storage_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'sanboot_storage_about_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'sanboot_storage_about_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'sanboot_storage_about_identifier';
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
		'tab' => 'About Sanboot-Storage',
		'label' => 'About Sanboot-Storage',
		'introduction_title' => 'Introduction',
		'introduction_content' => 'The "Sanboot-Storage" plugin integrates with <a href="http://etherboot.org/wiki/start" target="_BLANK">gPXE</a> and supports booting and deploying Windows systems
			directly from a SAN Storage (iSCSI or AOE) without using any local disk.<br><br>
			For a detailed documentation about how to setup Windows to directly boot from a SAN Storage please refer to the Etherboot Wiki - <a href="http://etherboot.org/wiki/sanboot" target="_BLANK">SAN Boot</a> and <a href="http://etherboot.org/wiki/sanboot/iscsi_install" target="_BLANK">SAN Install</a>.',

		'howto_title' => 'How to use Sanboot-Storage',
		'howto_content' => '
					<strong>Initial Windows Installation on the SAN Volume</strong><br>
					<li>Create a Sanboot-Storage Storage Aoe/Iscsi (please check the requirements)</li>
					<li>Create a new Sanboot-Storage volume. This automatically creates a new Image for deployment</li>
				   <li>Enable and start the "dhcpd" and "tftpd" plugin. This automatically sets up a "network-boot" environment for deployment with Sanboot-Storage</li>
				   <li>Boot one (or more) physical Systems via the network (PXE) - Set the boot-order of the physical System in its BIOS to 1. Network-boot, 2. Boot-from-local-DVD/CD</li>
				   <li>Insert a Windows Installation Media in the physical Systems DVD/CD</li>
				   <li>Now create a new Appliance using the (idle) physical System as resource and the previously created Sanboot-Storage Image as the Image</li>
				   <li>Start the Appliance</li><br>
				   The System now reboots assigned to gPXE network-boot. In the (network-) bootloader stage it connects the Sanboot-Storage Volume (either per iSCSI or AOE).
				   It will fail to boot from the connected SAN Volume since it is still empty. It will move on to the next Boot Device (the CD/DVD) and start the Windows installation.<br>
				   You can now directly install into the SAN Volume! - please check the Etherboot Wiki about the installation details<br><br>
				   It is recommended to install the "Windows openQRM Client" after the initial installation of the SAN Volume.<br>
				   The Installation Media can now be removed from the physical System (you can also remove any local disk if you like).<br><br>

				   <strong>Deployment</strong><br>
				   After the initial installation of the Windows operating system on a SAN volume is it recommended to use this as a "Master Template" and to not deploy it any more but just "snapshots" or "clones" of it.
				   You can easily create "snapshots/clones" of the Sanboot-Storage volumes via the volume manager.<br><br>
				   <strong>Cloud Deployment</strong><br>
				   Windows Deployment to physical Systems (and VMs) via the openQRM Cloud is fully supported for Sanboot-Storage.',


		'requirements_title' => 'Requirements',
		'requirements_list' => '<li>A resource for the Sanboot-Storage Storage (this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
					<li>One (or more) LVM volume group(s) with free space dedicated for the Sanboot-Storage Volumes</li>
				   <li>The following packages must be installed: aoetools, open-iscsi, screen, e2fsprogs, ntfsprogs, kpartx</li>',

		'tested_title' => 'Tested with',
		'tested_content' => 'This plugin is tested with the Debian, Ubuntu and CentOS Linux distributions.<br><br>
			Deployment with Sanboot-Storage is tested with Windows XP, Windows 7, Windows Server 2008 and Windows 8',

		'provides_title' => 'Provides',
		'provides_list' => '<li>Storage type: "Sanboot Storage Server (Aoe/Iscsi)"</li>
					<li>Deployment types: "iscsi-san-deployment" and "aoe-san-deployment"</li>',
		'type_title' => 'Plugin Type',
		'type_content' => 'Storage',

		'deployment_title' => 'Deployment Type',
		'deployment_content' => 'Network-Deployment',

		'documentation_title' => 'Documentation',
		'use_case_title' => 'Use-Case',
		'network_deploymet' => 'Network-Deployment',
		'doc1' => '',
	),
	'bootservice' => array (
		'tab' => 'Boot-Service',
		'label' => 'Sanboot-Storage Boot-Service',
		'boot_service_title' => 'Sanboot-Storage Host Boot-Service',
		'boot_service_content' => 'The Sanboot-Storage Plugin provides an openQRM Boot-Service.
			This "Sanboot-Storage Boot-Service" is automatically downloaded and executed by the openQRM-Client on all integrated Systems.
			The Boot-Service is located at:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/sanboot-storage/web/boot-service-sanboot-storage.tgz</b></i>
			<br>
			<br>
			The "Sanboot-Storage Boot-Service contains the Client files of the Sanboot-Storage Plugin.<br>
			Also a configuration file for the Sanboot-Storage server is included in this Boot-Service.<br>
			<br>
			The Boot-Service configuration can be viewed and administrated by the "openqrm" utility.<br>
			To view the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n sanboot-storage -a default</b></i>
			<br>
			<br>
			To view a Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service view -n sanboot-storage -a [appliance-name]</b></i>
			<br>
			<br>
			To adapt a parameter in the current default Boot-Service configuration run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n sanboot-storage -a default -k [key] -v [value]</b></i>
			<br>
			<br>
			To adapt a paramter in the Boot-Service configuration of a specific appliance run:<br>
			<br>
				<i><b>/usr/share/openqrm/bin/openqrm boot-service configure -n sanboot-storage -a [appliance-name] -k [key] -v [value]</b></i>
			<br>
			<br>
			In case the openmQRM Server itself is used as the Sanboot-Storage Storage please edit:<br>
			<br>
				<i><b>/usr/share/openqrm/plugins/sanboot-storage/etc/openqrm-plugin-sanboot-storage.conf</b></i>
			<br>
			<br>
			and set the configuration keys.<br>
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/sanboot-storage/lang", 'sanboot-storage-about.ini');
		$this->tpldir   = $this->rootdir.'/plugins/sanboot-storage/tpl';
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
	 * About Sanboot-Storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function documentation( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/sanboot-storage/class/sanboot-storage-about.documentation.class.php');
			$controller = new sanboot_storage_about_documentation($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/sanboot-storage/class/sanboot-storage-about.bootservice.class.php');
			$controller = new sanboot_storage_about_bootservice($this->openqrm, $this->response);
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
