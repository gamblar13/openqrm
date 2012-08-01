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



function openqrm_equallogic_storage_appliance($cmd, $appliance_fields) {
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

	$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);

	// check that appliance is not using openqrm resource
	if ($resource->id == 0) {
		return;
	}
	// check image type -> equallogic
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	$storage = new storage();
	$storage->get_instance_by_id($image->storageid);
	if(!preg_match('/equallogic$/i',$image->type)) {
		$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "$appliance_id is not from type equallogic-storage-, skipping .. $appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
		return;
	}

	switch($cmd) {
		case "add":
			// set CREATE_FS=TRUE deployment parameter when it's not set (e.g. with newly created images)
			$create_fs_param = $image->get_deployment_parameter("CREATE_FS");
			if($create_fs_param == "") {
				$image->set_deployment_parameters("CREATE_FS", "TRUE");
				$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Set CREATE_FS parameter for $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
			} else {
				$event->log("openqrm_equallogic_storage_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-equallogic-storage-appliance-hook.php", "Not setting CREATE_FS parameter for $appliance_id/$appliance_name/$appliance_ip, already set to ".$create_fs_param, "", "", 0, 0, $appliance_id);
			}
			break;
	}
}



?>


