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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_sanboot_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$appliance_image_id=$appliance_fields["appliance_imageid"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$resource_id=$resource->id;
	$image = new image();
	$image->get_instance_by_id($appliance_image_id);
	$image_deployment_type = $image->type;
	$apply_hook = 0;

	// run only for our deployment types
	if (!strcmp($image_deployment_type, "aoe-san-deployment")) {
		$apply_hook=1;
	}
	if (!strcmp($image_deployment_type, "iscsi-san-deployment")) {
		$apply_hook=1;
	}
	if ($apply_hook == 0) {
		$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-sanboot-storage-appliance-hook.php", "Skipping $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
	} else {
		$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-sanboot-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
		// we remove the assignment to sanboot in the dhcpd.conf
		switch($cmd) {
			case "start":
				// here we set the image deployment parameter IMAGE_VIRTUAL_RESOURCE_COMMAND
				$image->set_deployment_parameters("IMAGE_VIRTUAL_RESOURCE_COMMAND", "true");
				break;

			case "stop":
				$openqrm_server = new openqrm_server();
				$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm-sanboot-storage-assign deassign -t $image_deployment_type -m $resource_mac -r $resource_id -z $resource_ip");
				break;
		}
	}
}



?>


