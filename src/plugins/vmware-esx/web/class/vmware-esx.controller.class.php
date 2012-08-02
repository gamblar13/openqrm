<?php
/**
 * VMware ESX Host Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class vmware_esx_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_id';
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
		'tab' => 'ESX Hosts Management',
		'label' => 'Select ESX Host',
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
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a NFS Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'vm' => array (
		'tab' => 'VM Manager',
		'label' => 'Virtual Machines on ESX Host %s',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'vm_add' => array (
		'tab' => 'Add VM',
		'label' => 'Create Virtual Machine on ESX Host %s',
		'lang_basic' => 'Basic',
		'lang_hardware' => 'Hardware',
		'lang_net' => 'Network',
		'lang_net_0' => 'Network_0',
		'lang_net_1' => 'Network_1',
		'lang_net_2' => 'Network_2',
		'lang_net_3' => 'Network_3',
		'lang_net_4' => 'Network_4',
		'lang_boot' => 'Boot from',
		'lang_vnc' => 'VNC',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
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
		'lang_vnc' => 'VNC',
		'lang_password_generate' => 'generate password',
		'lang_password_show' => 'show password',
		'lang_password_hide' => 'hide password',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'error_vnc' => 'VNC Password must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_remove' => array (
		'tab' => 'Remove VM',
		'label' => 'Remove Virtual Machine from ESX Host %s',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_start' => array (
		'tab' => 'Start VM',
		'label' => 'Start Virtual Machine on ESX Host %s',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_stop' => array (
		'tab' => 'Stop VM',
		'label' => 'Stop Virtual Machine on ESX Host %s',
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
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'vm_console' => array (
		'tab' => 'VM Console',
		'label' => 'Open a VM Console',
		'lang_id' => 'ID',
	),
	'vm_boot' => array (
		'tab' => 'VM Boot Sequence',
		'label' => 'Set VM Boot Sequence',
		'lang_id' => 'ID',
	),
	'ds' => array (
		'tab' => 'Datastore Manager',
		'label' => 'Datastores on ESX Host %s',
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
		'table_available' => 'Available',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'ds_add_nas' => array (
		'tab' => 'Add NAS DataStore',
		'label' => 'Add NAS DataStore to ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ds_remove_nas' => array (
		'tab' => 'Remove NAS DataStore',
		'label' => 'Remove NAS DataStore from ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ds_add_iscsi' => array (
		'tab' => 'Add iSCSI DataStore',
		'label' => 'Add iSCSI DataStore to ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_subnet' => 'Subnetmask',
		'form_target' => 'Target',
		'form_portgroup' => 'PortGroup',
		'form_vswitch' => 'vSwitch',
		'form_vmk' => 'NIC-Name',
		'form_datastore' => 'DataStore',
		'msg_added' => 'Added DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
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
		'label' => 'Remove iSCSI DataStore from ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_path' => 'Path',
		'form_datastore' => 'DataStore',
		'msg_removed' => 'Removed DataStore %s',
		'error_exists' => 'DataStore %s allready exists',
		'error_not_exists' => 'DataStore %s does not exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'error_no_target' => 'Target name empty',
		'error_no_ip' => 'Target IP address empty',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne' => array (
		'tab' => 'vSwitch Manager',
		'label' => 'vSwitches on ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new vSwitch',
		'action_remove' => 'Remove vSwitch',
		'action_remove_vs_up' => 'Remove Uplink',
		'action_refresh' => 'Reload Page',
		'action_remove' => 'ne_remove_vs',
		'action_select' => 'ne_select_vs',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_num_ports' => 'Ports',
		'table_used_ports' => 'Used',
		'table_conf_ports' => 'Conf.',
		'table_mtu' => 'MTU',
		'table_uplink' => 'Uplink',
		'please_wait' => 'Loading Host configuration. Please wait ..',
	),
	'ne_add_vs' => array (
		'tab' => 'Add vSwitch',
		'label' => 'Add vSwitch to ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_ports' => 'Ports',
		'form_vswitch' => 'vSwitch',
		'msg_added' => 'Added vSwitch %s',
		'error_exists' => 'vSwitch %s allready exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_remove_vs' => array (
		'tab' => 'Remove vSwitch',
		'label' => 'Remove vSwitch from ESX Host %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_ports' => 'Ports',
		'form_vswitch' => 'vSwitch',
		'msg_removed' => 'Removed vSwitch %s',
		'msg_not_removing' => 'Not removing vSwitch0',
		'error_exists' => 'vSwitch %s allready exists',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_select_vs' => array (
		'tab' => 'Portgroups',
		'label' => 'Configure Portgroups and Uplinks',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_pg_name' => 'PortGroup',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'table_state' => 'Status',
		'table_name' => 'Name',
		'table_pg_name' => 'Name',
		'table_vs_name' => 'vSwitch',
		'table_pg_vlan' => 'VLAN',
		'table_pg_ports' => 'Ports',
		'table_pg_uplink' => 'Uplink',
		'action_remove' => 'ne_remove_vs_pg',
		'action_add_pg' => 'Add Portgroup to vSwitch',
		'action_add_up' => 'Add Uplink to vSwitch',
		'action_add_pg_up' => 'Add Uplink',
		'action_remove_pg_up' => 'Remove Uplink',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_ports' => 'Ports',
		'form_vswitch' => 'vSwitch',
		'msg_removed' => 'Removed Portgroups %s',
		'msg_not_removing' => 'Not removing Portgroups',
		'error_exists' => 'vSwitch %s allready exists',
		'error_no_esx' => 'Appliance %s is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_add_vs_pg' => array (
		'tab' => 'Add PortGroup',
		'label' => 'Add PortGroup to vSwitch %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_vlan' => 'VLAN ID',
		'form_vswitch' => 'vSwitch',
		'msg_added' => 'Added PortGroup  %s to vSwitch',
		'error_exists' => 'PortGroup %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_remove_vs_pg' => array (
		'tab' => 'Remove PortGroup',
		'label' => 'Remove PortGroup from vSwitch %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_vlan' => 'VLAN ID',
		'form_vswitch' => 'vSwitch',
		'msg_removed' => 'Removed PortGroup %s',
		'msg_not_removing' => 'Not removing PortGroup',
		'error_exists' => 'vSwitch %s allready exists',
		'error_exists' => 'PortGroup %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_add_vs_up' => array (
		'tab' => 'Add Uplink',
		'label' => 'Add Uplink to vSwitch %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_uplink' => 'Uplink',
		'form_vswitch' => 'vSwitch',
		'msg_added' => 'Added Uplink  %s to vSwitch',
		'error_exists' => 'Uplink %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_remove_vs_up' => array (
		'tab' => 'Remove Uplink',
		'label' => 'Remove Uplink %s from vSwitch',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_vlan' => 'VLAN ID',
		'form_vswitch' => 'vSwitch',
		'msg_removed' => 'Removed Uplink %s',
		'msg_not_removing' => 'Not removing Uplink %s vom vSwitch0!<br>This is the Management Network-connection.',
		'error_exists' => 'vSwitch %s allready exists',
		'error_exists' => 'Uplink %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_add_pg_up' => array (
		'tab' => 'Add Uplink',
		'label' => 'Add Uplink to PortGroup %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_uplink' => 'Uplink',
		'form_vswitch' => 'vSwitch',
		'msg_added' => 'Added Uplink  %s to PortGroup',
		'error_exists' => 'PortGroup %s allready exists',
		'error_not_exists' => 'vSwitch %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'ne_remove_pg_up' => array (
		'tab' => 'Remove Uplink',
		'label' => 'Remove Uplink %s from PortGroup',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'form_name' => 'Name',
		'form_ip' => 'IP Address',
		'form_vlan' => 'VLAN ID',
		'form_vswitch' => 'vSwitch',
		'msg_removed' => 'Removed PortGroup %s',
		'msg_not_removing' => 'Not removing Uplink %s vom PortGroup!<br>This is the Management Network-connection.',
		'error_exists' => 'vSwitch %s allready exists',
		'error_exists' => 'Uplink %s does not exist',
		'error_no_esx' => 'Appliance is not an ESX Server!',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'reboot' => array (
		'tab' => 'Reboot ESX Host',
		'label' => 'Reboot ESX Host %s',
		'msg_rebooted' => 'Rebooted ESX Host %s',
		'please_wait' => 'Loading Host configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'shutdown' => array (
		'tab' => 'Shutdown ESX Host',
		'label' => 'Shutdown ESX Host %s',
		'msg_shutdown' => 'Powered off ESX Host %s',
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
		$this->lang = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-esx/lang", 'vmware-esx.ini');
		$this->tpldir = $this->rootdir.'/plugins/vmware-esx/tpl';
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
			$this->action = "select";
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
				$content[] = $this->vm(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_add':
				$content[] = $this->vm_add(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_update':
				$content[] = $this->vm_update(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_remove':
				$content[] = $this->vm_remove(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_start':
				$content[] = $this->vm_start(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_stop':
				$content[] = $this->vm_stop(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_console':
				$content[] = $this->vm_console(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'vm_boot':
				$content[] = $this->vm_boot(true);
				$content[] = $this->ds(false);
				$content[] = $this->ne(false);
			break;
			case 'ds':
				$content[] = $this->vm(false);
				$content[] = $this->ds(true);
				$content[] = $this->ne(false);
			break;
			case 'ds_add_nas':
				$content[] = $this->vm(false);
				$content[] = $this->ds_add_nas(true);
				$content[] = $this->ne(false);
			break;
			case 'ds_remove_nas':
				$content[] = $this->vm(false);
				$content[] = $this->ds_remove_nas(true);
				$content[] = $this->ne(false);
			break;
			case 'ds_add_iscsi':
				$content[] = $this->vm(false);
				$content[] = $this->ds_add_iscsi(true);
				$content[] = $this->ne(false);
			break;
			case 'ds_remove_iscsi':
				$content[] = $this->vm(false);
				$content[] = $this->ds_remove_iscsi(true);
				$content[] = $this->ne(false);
			break;
			case 'ne':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne(true);
			break;
			case 'ne_add_vs':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_add_vs(true);
			break;
			case 'ne_remove_vs':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_remove_vs(true);
			break;
			case 'ne_select_vs':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_select_vs(true);
			break;
			case 'ne_add_vs_pg':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_add_vs_pg(true);
			break;
			case 'ne_remove_vs_pg':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_remove_vs_pg(true);
			break;
			case 'ne_add_vs_up':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_add_vs_up(true);
			break;
			case 'ne_remove_vs_up':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_remove_vs_up(true);
			break;
			case 'ne_add_pg_up':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_add_pg_up(true);
			break;
			case 'ne_remove_pg_up':
				$content[] = $this->vm(false);
				$content[] = $this->ds(false);
				$content[] = $this->ne_remove_pg_up(true);
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
	 * Select ESX Host for management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.select.class.php');
			$controller = new vmware_esx_select($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm.class.php');
				$controller                  = new vmware_esx_vm($this->openqrm, $this->response);
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
		$command  = $this->basedir."/plugins/vmware-esx/bin/openqrm-vmware-esx post_vm_list -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.vm_list';
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
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-add.class.php');
				$controller                  = new vmware_esx_vm_add($this->openqrm, $this->response);
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
		    if($this->reload_ds()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-update.class.php');
				$controller                  = new vmware_esx_vm_update($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-remove.class.php');
				$controller                  = new vmware_esx_vm_remove($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-start.class.php');
				$controller                  = new vmware_esx_vm_start($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-stop.class.php');
				$controller                  = new vmware_esx_vm_stop($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-console.class.php');
			$controller                  = new vmware_esx_vm_console($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.vm-boot.class.php');
				$controller                  = new vmware_esx_vm_boot($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ds.class.php');
				$controller                  = new vmware_esx_ds($this->openqrm, $this->response);
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
		$command  = $this->basedir."/plugins/vmware-esx/bin/openqrm-vmware-esx-datastore post_ds_list -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.ds_list';
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ds-add-nas.class.php');
				$controller                  = new vmware_esx_ds_add_nas($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ds-remove-nas.class.php');
				$controller                  = new vmware_esx_ds_remove_nas($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ds-add-iscsi.class.php');
				$controller                  = new vmware_esx_ds_add_iscsi($this->openqrm, $this->response);
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
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ds-remove-iscsi.class.php');
				$controller                  = new vmware_esx_ds_remove_iscsi($this->openqrm, $this->response);
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
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ds_remove_iscsi' );
		$content['onclick'] = false;
		if($this->action === 'ds_remove_iscsi'){
			$content['active']  = true;
		}
		return $content;
	}



// #############################################################################
// ################## Network Management #######################################
// #############################################################################

	//--------------------------------------------
	/**
	 * Network management
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne.class.php');
				$controller                  = new vmware_esx_ne($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne'];
				$data = $controller->action();
			}
		}
		$content['label']   = $this->lang['ne']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne' );
		$content['onclick'] = false;
		if($this->action === 'ne'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Reload Network states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_ne() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$resource = new resource();
		$resource->get_instance_by_id($appliance->resources);
		$command  = $this->basedir."/plugins/vmware-esx/bin/openqrm-vmware-esx-network post_net_config -i ".$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/vmware-esx/vmware-esx-stat/'.$resource->ip.'.net_config';
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
	 * Add vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_add_vs( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-add-vs.class.php');
				$controller                  = new vmware_esx_ne_add_vs($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_add_vs'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_add_vs']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_add_vs' );
		$content['onclick'] = false;
		if($this->action === 'ne_add_vs'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_remove_vs( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-remove-vs.class.php');
				$controller                  = new vmware_esx_ne_remove_vs($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_remove_vs'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_remove_vs']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_remove_vs' );
		$content['onclick'] = false;
		if($this->action === 'ne_remove_vs'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Configure vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_select_vs( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-select-vs.class.php');
				$controller                  = new vmware_esx_ne_select_vs($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_select_vs'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_select_vs']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_select_vs' );
		$content['onclick'] = false;
		if($this->action === 'ne_select_vs'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * Add PortGroup to vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_add_vs_pg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-add-vs-pg.class.php');
				$controller                  = new vmware_esx_ne_add_vs_pg($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_add_vs_pg'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_add_vs_pg']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_add_vs_pg' );
		$content['onclick'] = false;
		if($this->action === 'ne_add_vs_pg'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove PortGroup from vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_remove_vs_pg( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-remove-vs-pg.class.php');
				$controller                  = new vmware_esx_ne_remove_vs_pg($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_remove_vs_pg'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_remove_vs_pg']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_remove_vs_pg' );
		$content['onclick'] = false;
		if($this->action === 'ne_remove_vs_pg'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * Add Uplink to vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_add_vs_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-add-vs-up.class.php');
				$controller                  = new vmware_esx_ne_add_vs_up($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_add_vs_up'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_add_vs_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_add_vs_up' );
		$content['onclick'] = false;
		if($this->action === 'ne_add_vs_up'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove Uplink from vSwitch
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_remove_vs_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-remove-vs-up.class.php');
				$controller                  = new vmware_esx_ne_remove_vs_up($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_remove_vs_up'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_remove_vs_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_remove_vs_up' );
		$content['onclick'] = false;
		if($this->action === 'ne_remove_vs_up'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Add Uplink to PortGroup
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_add_pg_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-add-pg-up.class.php');
				$controller                  = new vmware_esx_ne_add_pg_up($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_add_pg_up'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_add_pg_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_add_pg_up' );
		$content['onclick'] = false;
		if($this->action === 'ne_add_pg_up'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Remove Uplink from PortGroup
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ne_remove_pg_up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
		    if($this->reload_ne()) {
				require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.ne-remove-pg-up.class.php');
				$controller                  = new vmware_esx_ne_remove_pg_up($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['ne_remove_pg_up'];
				$data = $controller->action();
		    }
		}
		$content['label']   = $this->lang['ne_remove_pg_up']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ne_remove_pg_up' );
		$content['onclick'] = false;
		if($this->action === 'ne_remove_pg_up'){
			$content['active']  = true;
		}
		return $content;
	}


// #############################################################################
// ################## Host Management ##########################################
// #############################################################################


	//--------------------------------------------
	/**
	 * Reboot an ESX Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function reboot( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.reboot.class.php');
			$controller                  = new vmware_esx_reboot($this->openqrm, $this->response);
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
	 * Shutdown an ESX Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function shutdown( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx.shutdown.class.php');
			$controller                  = new vmware_esx_shutdown($this->openqrm, $this->response);
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
