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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// ip mgmt class
require_once "$RootDir/plugins/cobbler/class/cobbler.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


function openqrm_cobbler_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$resource_mac=$resource->mac;
	$resource_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// check if image is type cobbler-deployment
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);
	// cobbler configured in image deployment parameters ?
	$cobbler_auto_install_enabled = false;
	$cobbler_deployment_parameters = trim($image->get_deployment_parameter("INSTALL_CONFIG"));
	if (strlen($cobbler_deployment_parameters)) {
		$cobbler_deployment_parameter_arr = explode(":", $cobbler_deployment_parameters);
		$local_deployment_persistent = $cobbler_deployment_parameter_arr[0];
		$local_deployment_type = $cobbler_deployment_parameter_arr[1];
		if (strcmp($local_deployment_type, "cobbler-deployment")) {
			$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance_name." image is not from type cobbler-deployment", "", "", 0, 0, $resource->id);
			return;
		}
		$cobbler_server_storage_id = $cobbler_deployment_parameter_arr[2];
		$cobbler_installation_profile = $cobbler_deployment_parameter_arr[3];
		//$cobbler_product_key = $cobbler_deployment_parameter_arr[3];
		$cobbler_auto_install_enabled = true;
	}


	$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
			$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "START event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);

			if ($cobbler_auto_install_enabled) {
				// prepare automatic-installation

				// get the cobbler-server resource
				$cobbler_storage = new storage();
				$cobbler_storage->get_instance_by_id($cobbler_server_storage_id);
				$cobbler_server_resource = new resource();
				$cobbler_server_resource->get_instance_by_id($cobbler_storage->resource_id);

				// add client to cobbler server, get resource_id from image-deployment parameters, runs on Cobbler server
				$cobbler_server_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler add_cobbler_client -i ".$resource_ip." -x ".$resource->id." -m ".$resource_mac." -o ".$cobbler_installation_profile." -n ".$appliance->name;
				$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "add_cobbler_client $resource_ip", "", "", 0, 0, $resource->id);
				$cobbler_server_resource->send_command($cobbler_server_resource->ip, $cobbler_server_command);
				sleep(2);

				// transfer client to cobbler server, runs on openQRM, we have to use the hostname resource+id and not the appliance-name since the resource is in the dhcpd.conf with this name
				$cobbler_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler-manager transfer_to_cobbler -o ".$cobbler_server_resource->ip." -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id;
				$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "transfer_to_cobbler $resource_ip", "", "", 0, 0, $resource->id);
				$openqrm_server->send_command($cobbler_command);

				// Remove image-deployment paramters, if auto-install is a single-shot actions
				if (!strcmp($local_deployment_persistent, "0")) {
					$image->set_deployment_parameters("INSTALL_CONFIG", "");
				}

				// create cobbler-state object to allow to run a late setboot to local command on the vm host
				$cobbler_state = new cobblerstate();
				$cobbler_state->remove_by_resource_id($resource->id);
				$cobbler_state_fields=array();
				$cobbler_state_fields["cobbler_id"]=openqrm_db_get_free_id('cobbler_id', $cobbler_state->_db_table);
				$cobbler_state_fields["cobbler_resource_id"]=$resource->id;
				$cobbler_state_fields["cobbler_install_start"]=$_SERVER['REQUEST_TIME'];
				$cobbler_state_fields["cobbler_timeout"]=$cobbler_install_timeout;
				$cobbler_state->add($cobbler_state_fields);

			} else {

				if (strcmp($image->type, "cobbler-deployment")) {
					$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type cobbler-deployment", "", "", 0, 0, $resource->id);
				} else {
					// we have auto-installed already, if it is VM the cobblerresource object will care to set the boot-sequence on the VM Host to local boot
					$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "Setting resource $resource_ip to local-boot", "", "", 0, 0, $resource->id);
					$cobblerresource = new cobblerresource();
					$cobblerresource->set_boot($resource->id, 1);
					$cobbler_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler-manager set_cobbler_client_to_local_boot -m ".$resource_mac;
					$openqrm_server->send_command($cobbler_command);

				}
			}
			break;



		case "stop":

			if (strcmp($image->type, "cobbler-deployment")) {
				$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "Appliance $appliance_id/$appliance_name image is not from type cobbler-deployment", "", "", 0, 0, $resource->id);
			} else {
				$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "STOP event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
				// transfer client to openQRM again
				$cobbler_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler-manager take_over_from_cobbler -i ".$resource_ip." -m ".$resource_mac." -n resource".$resource->id;
				$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "take_over_from_cobbler $resource_ip", "", "", 0, 0, $resource->id);
				$openqrm_server->send_command($cobbler_command);

				// remove cobbler-state object if existing
				$cobbler_state = new cobblerstate();
				$cobbler_state->remove_by_resource_id($resource->id);
				// if it is VM the cobblerresource object will care to set the boot-sequence on the VM Host to network boot
				$cobblerresource = new cobblerresource();
				$cobblerresource->set_boot($resource->id, 0);

				// remove  client from cobbler server
	#			$cobbler_server_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler remove_cobbler_client -d ".$resource_domain." -n ".$appliance->name;
	#			$event->log("openqrm_cobbler_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-cobbler-appliance-hook.php", "remove_cobbler_client $resource_ip", "", "", 0, 0, $resource->id);
	#			$cobbler_server_resource->send_command($cobbler_server_resource->ip, $cobbler_server_command);

				break;
			}
	}


}


?>


