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



function openqrm_xen_storage_appliance($cmd, $appliance_fields) {
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

	$event->log("openqrm_xen_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	// check resource type -> xen-strorage-vm
	$virtualization = new virtualization();
	$virtualization->get_instance_by_type("xen-storage-vm");
	if ($resource->vtype != $virtualization->id) {
		$event->log("openqrm_xen_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-storage-appliance-hook.php", "$appliance_id is not from type xen-storage-vm, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
		return;
	}

	// check image is on the same storage server
	// get the xen host resource
	$xen_storage_host_resource = new resource();
	$xen_storage_host_resource->get_instance_by_id($resource->vhostid);
	// get the xen-storage resource
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	$storage = new storage();
	$storage->get_instance_by_id($image->storageid);
	$xen_storage_resource = new resource();
	$xen_storage_resource->get_instance_by_id($storage->resource_id);
	if ($xen_storage_host_resource->id != $xen_storage_resource->id) {
		$event->log("openqrm_xen_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-storage-appliance-hook.php", "Appliance $appliance_id image is not available on this xen-storage host. Assuming SAN-Backend", "", "", 0, 0, $appliance_id);
	}

	switch($cmd) {
		case "start":
			// send command to assign image and start vm
			$xen_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage-vm start_by_mac -m $resource->mac -d $image->rootdevice";
			$xen_storage_host_resource->send_command($xen_storage_host_resource->ip, $xen_storage_command);
			break;
		case "stop":

			// send command to stop the vm and deassign image
			// send command to assign image and start vm
			$xen_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage-vm restart_by_mac -m $resource->mac";
			$xen_storage_host_resource->send_command($xen_storage_host_resource->ip, $xen_storage_command);
			break;

	}
}



?>


