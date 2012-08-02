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
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// ip mgmt class
require_once "$RootDir/plugins/wakeuponlan/class/wakeuponlan.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


function openqrm_wakeuponlan_appliance($cmd, $appliance_fields) {
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
	$appliance_ip=$resource->ip;
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	// start-from-off enabled ?
	$resource_can_start_from_off = $resource->get_resource_capabilities("SFO");
	if ($resource_can_start_from_off != 1) {
		$event->log("openqrm_wakeuponlan_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-wakeuponlan-appliance-hook.php", "WOL is not enabled for resource $resource->id. Not Handling $cmd event $appliance_id", "", "", 0, 0, $resource->id);
		return;
	}
	$event->log("openqrm_wakeuponlan_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-wakeuponlan-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip/$resource_mac", "", "", 0, 0, $appliance_id);
	switch($cmd) {
		case "start":
			$event->log("openqrm_wakeuponlan_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-wakeuponlan-appliance-hook.php", "START event $appliance_id/$appliance_name/$appliance_ip/$resource_mac", "", "", 0, 0, $appliance_id);
			$wol_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/wakeuponlan/bin/openqrm-wakeuponlan wakeup -m $resource_mac";
			$openqrm_server->send_command($wol_command);
			break;
	}

}


?>


