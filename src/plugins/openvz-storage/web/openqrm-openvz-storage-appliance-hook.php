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

	Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_openvz_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	// check resource type -> lxc-strorage-vm
	$virtualization = new virtualization();
	$virtualization->get_instance_by_type("openvz-storage-vm");
	if ($resource->vtype != $virtualization->id) {
		$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "$appliance_id is not from type openvz-storage-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
		return;
	}
	// check image is on the same storage server
	// get the lxc host resource
	$openvz_storage_host_resource = new resource();
	$openvz_storage_host_resource->get_instance_by_id($resource->vhostid);
	// get the openvz-storage resource
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	$storage = new storage();
	$storage->get_instance_by_id($image->storageid);
	$openvz_storage_resource = new resource();
	$openvz_storage_resource->get_instance_by_id($storage->resource_id);
	if ($openvz_storage_host_resource->id != $openvz_storage_resource->id) {
		$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "Appliance $appliance_id image IS NOT available on this openvz-storage host, $openvz_storage_host_resource->id not equal $openvz_storage_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
	} else {
		$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "Appliance $appliance_id image IS available on this openvz-storage host, $openvz_storage_host_resource->id equal $openvz_storage_resource->id .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
	}

	switch($cmd) {
		case "start":
			// idle resource has an ip ?
			$res_ip_loop = 0;
			while (!strcmp($resource->ip, "0.0.0.0")) {
				$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "Still empty mgmt-ip for resource $resource->id", "", "", 0, 0, $appliance_id);
				sleep(5);
				$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
				$res_ip_loop++;
				if ($res_ip_loop > 24) {
					$event->log("openqrm_openvz_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-openvz-storage-appliance-hook.php", "Failed to gather the mgmt-ip for resource $resource->id", "", "", 0, 0, $appliance_id);
					return;
				}
			}
			// send command to assign image and start vm
			$openvz_ip_config = "-i $resource->ip";
			$openvz_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm restart_by_mac -m $resource->mac -d $image->rootdevice $openvz_ip_config";
			$openvz_storage_host_resource->send_command($openvz_storage_host_resource->ip, $openvz_storage_command);
			break;
		case "stop":
			// send command to stop the vm and deassign image
			// send command to assign image and start vm
			$openvz_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm restart_by_mac -m $resource->mac -d idle";
			$openvz_storage_host_resource->send_command($openvz_storage_host_resource->ip, $openvz_storage_command);
			break;

	}
}



?>


