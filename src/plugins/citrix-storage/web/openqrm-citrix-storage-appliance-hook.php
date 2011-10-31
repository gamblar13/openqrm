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
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function openqrm_citrix_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $openqrm_server;
	global $RootDir;


	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);

	$event->log("openqrm_citrix_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	// check resource type -> citrix-strorage-vm
	$virtualization = new virtualization();
	$virtualization->get_instance_by_type("citrix-storage-vm");
	if ($resource->vtype != $virtualization->id) {
		$event->log("openqrm_citrix_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-storage-appliance-hook.php", "$appliance_id is not from type citrix-storage-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
		return;
	}

	// check image is on the same storage server
	// get the citrix host resource
	$citrix_storage_host_resource = new resource();
	$citrix_storage_host_resource->get_instance_by_id($resource->vhostid);
	// get the citrix-storage resource
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	$storage = new storage();
	$storage->get_instance_by_id($image->storageid);
	$citrix_storage_resource = new resource();
	$citrix_storage_resource->get_instance_by_id($storage->resource_id);
	if ($citrix_storage_host_resource->id != $citrix_storage_resource->id) {
		$event->log("openqrm_citrix_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-storage-appliance-hook.php", "Appliance $appliance_id image is not available on this citrix-storage host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
	}

	switch($cmd) {
		case "start":
			// send command to assign image and start vm
			$citrix_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage-vm restart_by_mac -i ".$citrix_storage_host_resource->ip." -m ".$resource->mac." -d ".$image->rootdevice;
			$openqrm_server->send_command($citrix_storage_command);
			break;
		case "stop":
			// send command to stop the vm and deassign image
			$citrix_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage-vm restart_by_mac -i ".$citrix_storage_host_resource->ip." -m ".$resource->mac;
			$openqrm_server->send_command($citrix_storage_command);
			break;

	}
}



?>


