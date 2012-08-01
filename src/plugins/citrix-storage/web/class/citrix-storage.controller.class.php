<?php
/**
 * Citrix XenServer Host Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'citrix_storage_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'xenserver_id';
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
	'select' => array (
		'tab' => 'XenServer Hosts Management',
		'label' => 'Select Citrix XenServer Host',
		'action_edit' => 'edit',
		'action_host_reboot' => 'reboot',
		'action_host_shutdown' => 'shutdown',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_mac' => 'MAC',
		'table_ip' => 'IP',
		'table_hostname' => 'Hostname',
		'table_user' => 'User',
		'table_password' => 'Password',
		'table_comment' => 'Comment',
		'table_type' => 'Type',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a Citrix XenServer Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'vm' => array (
		'tab' => 'VM Manager',
		'label' => 'Virtual Machines on Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_update' => 'Update',
		'action_add' => 'Add new VM',
		'action_console' => 'Console',
		'action_refresh' => 'Reload Page',
		'action_start' => 'vm_start',
		'action_stop' => 'vm_stop',
		'action_remove' => 'vm_remove',
		'action_update' => 'update',
		'action_clone' => 'clone',
		'table_name' => 'Name',
		'table_state' => 'Status',
		'table_resource' => 'ID',
		'table_ip' => 'IP',
		'table_network' => 'Network',
		'table_mac' => 'Mac',
		'table_nic_type' => 'Type',
		'table_cpu' => 'CPU(s)',
		'table_memory' => 'Memeory',
		'table_disk' => 'Disk',
		'table_datastore' => 'DataStore',
		'table_additional_nics' => 'Additional NICs',
		'table_vnc' => 'VNC Access',
		'table_boot' => 'Boot',
		'error_no_citrix' => 'Appliance is not an Citrix XenServer Server!',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'vm_add' => array (
		'tab' => 'Add VM',
		'label' => 'Create Virtual Machine on Citrix XenServer Host %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_name_generate' => 'generate name',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'form_name' => 'Name',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'Disk (Swap)',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC Address',
		'form_type' => 'Type',
		'form_template' => 'VM Template (HVM)',
		'form_vnc' => 'VNC Password',
		'form_additional_nics' => 'Network Connections',
		'form_boot_order' => 'Boot Sequence',
		'form_boot_net' => 'Network-Boot',
		'form_boot_local' => 'Local-Boot',
		'form_0_nic' => 'Mgmt-NIC',
		'form_1_nic' => '1. NIC',
		'form_2_nic' => '2. NIC',
		'form_3_nic' => '3. NIC',
		'form_4_nic' => '4. NIC',
		'msg_added' => 'Added Virtual Machine %s',
		'error_exists' => 'Virtual Machine %s allready exists',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_no_datastore' => 'Storage Resource empty! Please select a Datastore!',
		'error_no_template' => 'Please select a VM Template!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_update' => array (
		'tab' => 'Update VM',
		'label' => 'Update Virtual Machine %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_name_generate' => 'generate name',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'form_name' => 'Name',
		'form_template' => 'VM Template (HVM)',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'Disk (Swap)',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC Address',
		'form_type' => 'Type',
		'form_vnc' => 'VNC Password',
		'form_additional_nics' => 'Network Connections',
		'form_boot_order' => 'Boot Sequence',
		'form_boot_net' => 'Network-Boot',
		'form_boot_local' => 'Local-Boot',
		'form_0_nic' => 'Mgmt-NIC',
		'form_1_nic' => '1. NIC',
		'form_2_nic' => '2. NIC',
		'form_3_nic' => '3. NIC',
		'form_4_nic' => '4. NIC',
		'msg_updated' => 'Updated Virtual Machine %s',
		'error_not_exist' => 'Virtual Machine %s does not exist',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_remove' => array (
		'tab' => 'Remove VM',
		'label' => 'Remove Virtual Machine from Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'form_name' => 'Name',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'Disk',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC Address',
		'form_type' => 'Type',
		'msg_removed' => 'Removed Virtual Machine %s',
		'error_exists' => 'Virtual Machine %s allready exists',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_start' => array (
		'tab' => 'Start VM',
		'label' => 'Start Virtual Machine on Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'form_name' => 'Name',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'Disk',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC Address',
		'form_type' => 'Type',
		'msg_started' => 'Started Virtual Machine %s',
		'error_exists' => 'Virtual Machine %s allready exists',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_stop' => array (
		'tab' => 'Stop VM',
		'label' => 'Stop Virtual Machine on Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'form_name' => 'Name',
		'form_memory' => 'Memory',
		'form_cpu' => 'CPU',
		'form_disk' => 'Disk',
		'form_datastore' => 'DataStore',
		'form_mac' => 'MAC Address',
		'form_type' => 'Type',
		'msg_stopped' => 'Stopped Virtual Machine %s',
		'error_exists' => 'Virtual Machine %s allready exists',
		'error_in_use' => 'Virtual Machine %s is still in use',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_clone' => array (
		'label' => 'Clone VM %s',
		'tab' => 'Clone VM',
		'msg_cloned' => 'Cloned %s as %s',
		'form_name' => 'Name',
		'lang_name_generate' => 'generate name',
		'error_exists' => 'VM %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning VM. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_console' => array (
		'tab' => 'VM Console',
		'label' => 'Open a VM Console',
		'lang_id' => 'ID',
		'lang_open_console' => 'VNC Console for VM %s<br>',
		'lang_tunnel_command' => 'Please run as root on openQRM:<br><br>%s<br><br>Then connect the VNC Console.',
	),
	'vm_boot' => array (
		'tab' => 'VM Boot Sequence',
		'label' => 'Set VM Boot Sequence',
		'lang_id' => 'ID',
	),
	'ds' => array (
		'tab' => 'Datastore Manager',
		'label' => 'Datastores on Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_ds_add_nas' => 'Add new NAS Datastore',
		'action_ds_add_iscsi' => 'Add new iSCSI Datastore',
		'action_ds_remove' => 'remove',
		'action_refresh' => 'Reload Page',
		'action_remove' => 'remove',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_location' => 'Location',
		'table_filesystem' => 'Filesystem',
		'table_capacity' => 'Capacity',
		'table_used' => 'Used',
		'table_available' => 'Available',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'ds_add_nas' => array (
		'tab' => 'Add NAS DataStore',
		'label' => 'Add NAS DataStore to Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_citrix' => 'Appliance is not an Citrix XenServer Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ds_remove_nas' => array (
		'tab' => 'Remove NAS DataStore',
		'label' => 'Remove NAS DataStore from Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ds_add_iscsi' => array (
		'tab' => 'Add iSCSI DataStore',
		'label' => 'Add iSCSI DataStore to Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_subnet' => 'Subnetmask',
		'form_target' => 'Target',
		'form_username' => 'Username',
		'form_password' => 'Password',
		'form_datastore' => 'DataStore',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'error_no_target' => 'Target name empty',
		'error_no_targetip' => 'Target IP address empty',
		'error_no_portgroup' => 'Portgroup parameter empty',
		'error_no_vswitch' => 'vSwitch parameter empty',
		'error_no_vmk' => 'VNIC parameter empty',
		'error_no_vmk_ip' => 'VNIC IP address empty',
		'error_no_vmk_subnet' => 'VNIC Subnetmask empty',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ds_remove_iscsi' => array (
		'tab' => 'Remove iSCSI DataStore',
		'label' => 'Remove iSCSI DataStore from Citrix XenServer Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_citrix' => 'Appliance is not an XenServer Server!',
		'error_name' => 'Name must be %s',
		'error_no_target' => 'Target name empty',
		'error_no_ip' => 'Target IP address empty',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'reboot' => array (
		'tab' => 'Reboot Citrix XenServer Host',
		'label' => 'Reboot Citrix XenServer Host %s',
		'msg_rebooted' => 'Rebooted Citrix XenServer Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'shutdown' => array (
		'tab' => 'Shutdown Citrix XenServer Host',
		'label' => 'Shutdown Citrix XenServer Host %s',
		'msg_shutdown' => 'Powered off Citrix XenServer Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
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
		$this->basedir  = $this->openqrm->get('basedir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang = $this->user->translate($this->lang, $this->rootdir."/plugins/citrix-storage/lang", 'citrix-storage.ini');
		$this->tpldir = $this->rootdir.'/plugins/citrix-storage/tpl';
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
			switch( $this->action ) {
				case 'vm_add':
				case 'vm_update':
				case 'vm_clone':
				case 'vm_remove':
				case 'vm_start':
				case 'vm_stop':
				case 'vm_console':
					$this->action = 'vm';
				break;
				case 'ds_add_nas':
				case 'ds_remove_nas':
				case 'ds_add_iscsi':
				case 'ds_remove_iscsi':
					$this->action = 'ds';
				break;
				default:
					$this->action = "select";
				break;
			}
		}
		if($this->action == '') {
			$this->action = "select";
		}
		if($this->action !== 'select') {
			$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'reboot':
				$content[] = $this->reboot(true);
			break;
			case 'shutdown':
				$content[] = $this->shutdown(true);
			break;
			case 'vm':
				$content[] = $this->select(false);
				$content[] = $this->vm(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_add':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_add(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_update':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_update(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_remove':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_remove(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_start':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_start(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_stop':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_stop(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_console':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_console(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_boot':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_boot(true);
				$content[] = $this->ds(false);
			break;
			case 'vm_clone':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->vm_clone(true);
				$content[] = $this->ds(false);
			break;
			case 'ds':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->ds(true);
			break;
			case 'ds_add_nas':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ds_add_nas(true);
			break;
			case 'ds_remove_nas':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ds_remove_nas(true);
			break;
			case 'ds_add_iscsi':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ds_add_iscsi(true);
			break;
			case 'ds_remove_iscsi':
				$content[] = $this->select(false);
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ds_remove_iscsi(true);
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
	 * Select XenServer Host for management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.select.class.php');
			$controller = new citrix_storage_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->lang           = $this->lang['select'];
			$data                       = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}



// #############################################################################
// ################## VM Management ############################################
// #############################################################################


	//--------------------------------------------
	/**
	 * VM management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_vm()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm.class.php');
				$controller                  = new citrix_storage_vm($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm' );
		$content['onclick'] = false;
		if($this->action === 'vm'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Reload VM states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_vm() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/citrix-storage/bin/openqrm-citrix-storage-vm post_vm_list -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/citrix-storage/citrix-storage-stat/'.$resource->ip.'.vm_list';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($openqrm->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}


	//--------------------------------------------
	/**
	 * Reload VM states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_vm_templates() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/citrix-storage/bin/openqrm-citrix-storage-vm post_template_list -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/citrix-storage/citrix-storage-stat/'.$resource->ip.'.template_list';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($openqrm->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}

	//--------------------------------------------
	/**
	 * Reload VM cpmfig
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_vm_config() {
		$vm_name = $this->response->html->request()->get('vm_name');
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/citrix-storage/bin/openqrm-citrix-storage-vm post_vm_config -i ".$resource->ip." -n ".$vm_name;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/citrix-storage/citrix-storage-stat/'.$resource->ip.'.'.$vm_name.'.vm_config';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($openqrm->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}


	//--------------------------------------------
	/**
	 * Add VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if (($this->reload_ds()) && ($this->reload_vm_templates())) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-add.class.php');
				$controller                  = new citrix_storage_vm_add($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_add'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_add']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_add' );
		$content['onclick'] = false;
		if($this->action === 'vm_add'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Update VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if (($this->reload_ds()) && ($this->reload_vm_config()) && ($this->reload_vm_templates())) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-update.class.php');
				$controller                  = new citrix_storage_vm_update($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_update'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_update']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_update' );
		$content['onclick'] = false;
		if($this->action === 'vm_update'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * Remove VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_vm()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-remove.class.php');
				$controller                  = new citrix_storage_vm_remove($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_remove'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_remove']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_remove' );
		$content['onclick'] = false;
		if($this->action === 'vm_remove'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * Start VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_vm()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-start.class.php');
				$controller                  = new citrix_storage_vm_start($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_start'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_start']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_start' );
		$content['onclick'] = false;
		if($this->action === 'vm_start'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Stop VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_vm()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-stop.class.php');
				$controller                  = new citrix_storage_vm_stop($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_stop'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_stop']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_stop' );
		$content['onclick'] = false;
		if($this->action === 'vm_stop'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Opens a VM Console
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_console( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-console.class.php');
			$controller                  = new citrix_storage_vm_console($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['vm_console'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['vm_console']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_console' );
		$content['onclick'] = false;
		if($this->action === 'vm_console'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Toggles VM boot sequence
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_boot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_vm()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-boot.class.php');
				$controller                  = new citrix_storage_vm_boot($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_boot'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['vm_boot']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_boot' );
		$content['onclick'] = false;
		if($this->action === 'vm_boot'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Clone VM
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vm_clone( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.vm-clone.class.php');
				$controller                  = new citrix_storage_vm_clone($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['vm_clone'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['vm_clone']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'vm_clone' );
		$content['onclick'] = false;
		if($this->action === 'vm_clone'){
			$content['active']  = true;
		}
		return $content;
	}


// #############################################################################
// ################## DataStore Management #####################################
// #############################################################################

	//--------------------------------------------
	/**
	 * Datastore management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ds( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.ds.class.php');
				$controller                  = new citrix_storage_ds($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ds'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['ds']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds' );
		$content['onclick'] = false;
		if($this->action === 'ds'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Reload DataStore states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_ds() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/citrix-storage/bin/openqrm-citrix-storage-datastore post_ds_list -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/citrix-storage/citrix-storage-stat/'.$resource->ip.'.ds_list';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($openqrm->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}



	//--------------------------------------------
	/**
	 * Add NAS DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ds_add_nas( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.ds-add-nas.class.php');
				$controller                  = new citrix_storage_ds_add_nas($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ds_add_nas'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ds_add_nas']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds_add_nas' );
		$content['onclick'] = false;
		if($this->action === 'ds_add_nas'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove NAS DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ds_remove_nas( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.ds-remove-nas.class.php');
				$controller                  = new citrix_storage_ds_remove_nas($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ds_remove_nas'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ds_remove_nas']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds_remove_nas' );
		$content['onclick'] = false;
		if($this->action === 'ds_remove_nas'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Add iSCSI DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ds_add_iscsi( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.ds-add-iscsi.class.php');
				$controller                  = new citrix_storage_ds_add_iscsi($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ds_add_iscsi'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ds_add_iscsi']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds_add_iscsi' );
		$content['onclick'] = false;
		if($this->action === 'ds_add_iscsi'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove iSCSI DataStore
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ds_remove_iscsi( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.ds-remove-iscsi.class.php');
				$controller                  = new citrix_storage_ds_remove_iscsi($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ds_remove_iscsi'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ds_remove_iscsi']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds_remove_iscsi' );
		$content['onclick'] = false;
		if($this->action === 'ds_remove_iscsi'){
			$content['active']  = true;
		}
		return $content;
	}



// #############################################################################
// ################## Host Management ##########################################
// #############################################################################


	//--------------------------------------------
	/**
	 * Reboot an XenServer Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function reboot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.reboot.class.php');
			$controller                  = new citrix_storage_reboot($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['reboot'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['reboot']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'reboot' );
		$content['onclick'] = false;
		if($this->action === 'reboot'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Shutdown an XenServer Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function shutdown( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.shutdown.class.php');
			$controller                  = new citrix_storage_shutdown($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['shutdown'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['shutdown']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'shutdown' );
		$content['onclick'] = false;
		if($this->action === 'shutdown'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
