<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
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
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Creating VMware ESX VM $name on Host $host_resource_ip", "", "", 0, 0, 0);
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
	$event->log("create_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
	$openqrm->send_command($vm_create_cmd);
}



// fences a vm
function fence_vmware_esx_vm($host_resource_id, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// fences the vm on its host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
	$event->log("fence_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Fencing VMware ESX VM $mac from Host $host_resource_id", "", "", 0, 0, 0);
	// we need to have an openQRM server object too since some of the
	// virtualization commands are sent from openQRM directly
	$openqrm = new openqrm_server();
	// send command to fence the vm on the host
	$vm_fence_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx fence -i ".$host_resource->ip." -m ".$mac;
	$event->log("fence_vmware_esx_vm", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-ha-hook", "Running $vm_fence_cmd", "", "", 0, 0, 0);
	$openqrm->send_command($vm_fence_cmd);
}




// ---------------------------------------------------------------------------------


?>
