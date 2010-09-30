<?php
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// This file implements the virtual machine abstraction in the cloud of openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;

$vmware_mac_address_space = "00:50:56:20";
global $vmware_mac_address_space;

// ---------------------------------------------------------------------------------
// general vmware-esx cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm from a specificed virtualization type + parameters
function create_vmware_esx_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
    global $vmware_mac_address_space;
    global $event;
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Creating VMware ESX VM $name on Host $host_resource_ip", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();
    // send command to create vm
    // also need to generate a new vmware mac for the first nic
    $fn_mac_gen_res = new resource();
    $fn_mac_gen_res->generate_mac();
    $fn_suggested_mac = $fn_mac_gen_res->mac;
    $fn_suggested_last_two_bytes = substr($fn_suggested_mac, 12);
    $fn_mac = $vmware_mac_address_space.":".$fn_suggested_last_two_bytes;
    $vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx create -i ".$host_resource->ip." -n ".$name." -m ".$fn_mac." -r ".$memory." -c ".$cpu." -s ".$swap." ".$additional_nic_str;
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
    $openqrm->send_command($vm_create_cmd);
}



// removes a cloud vm
function remove_vmware_esx_vm($host_resource_id, $name, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// remove the vm from host
    $host_resource = new resource();
    $host_resource->get_instance_by_id($host_resource_id);
	$event->log("remove_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Removing VMware ESX VM $name/$mac from Host $host_resource_id", "", "", 0, 0, 0);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();
    // send command to create the vm on the host
    $vm_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx delete -i ".$host_resource->ip." -n ".$name;
	$event->log("remove_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-cloud-hook", "Running $vm_remove_cmd", "", "", 0, 0, 0);
    $openqrm->send_command($vm_remove_cmd);
}




// ---------------------------------------------------------------------------------


?>