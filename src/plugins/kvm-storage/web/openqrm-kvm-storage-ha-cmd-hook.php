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

// ---------------------------------------------------------------------------------
// general kvm-storage cloudvm methods
// ---------------------------------------------------------------------------------


// creates a vm
function create_kvm_storage_vm($host_resource_id, $name, $mac, $memory, $cpu, $swap, $additional_nic_str) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
    global $event;
	$event->log("create_kvm_storage_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-storage-ha-hook", "Creating KVM-Storage VM $name on Host $host_resource_ip", "", "", 0, 0, 0);
	// start the vm on the host
	$host_resource = new resource();
	$host_resource->get_instance_by_id($host_resource_id);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();
    // send command to create vm
    $vm_create_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-storage-vm create -n ".$name." -m ".$mac." -r ".$memory." -c ".$cpu." -b local ".$additional_nic_str;
    $host_resource->send_command($host_resource->ip, $vm_create_cmd);
	$event->log("create_kvm_storage_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-storage-ha-hook", "Running $vm_create_cmd", "", "", 0, 0, 0);
}



// fences a vm
function fence_kvm_storage_vm($host_resource_id, $mac) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;

	// fences the vm on its host
    $host_resource = new resource();
    $host_resource->get_instance_by_id($host_resource_id);
	$event->log("fence_kvm_storage_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-storage-ha-hook", "Fencing KVM VM $mac from Host $host_resource_id", "", "", 0, 0, 0);
    // we need to have an openQRM server object too since some of the
    // virtualization commands are sent from openQRM directly
    $openqrm = new openqrm_server();
    // send command to fence the vm on the host
    $vm_fence_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm fence -m ".$mac;
	$event->log("fence_kvm_storage_vm", $_SERVER['REQUEST_TIME'], 5, "kvm-storage-ha-hook", "Running $vm_fence_cmd", "", "", 0, 0, 0);
    $host_resource->send_command($host_resource->ip, $vm_fence_cmd);
}




// ---------------------------------------------------------------------------------


?>