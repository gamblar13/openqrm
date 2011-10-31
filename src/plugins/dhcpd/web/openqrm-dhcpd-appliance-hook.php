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

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


function openqrm_dhcpd_appliance($cmd, $appliance_fields) {
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

	$event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$resource_ip/$resource_mac", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":
			$event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Adding hostname ".$appliance->name." from resource ".$resource->id.".", "", "", 0, 0, $resource->id);
                        $dhcpd_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-appliance add_hostname -m ".$resource_mac." -n ".$appliance->name." -d ".$resource->id;
                        $openqrm_server->send_command($dhcpd_command);
			break;

		case "stop":

                        $event->log("openqrm_dhcpd_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-dhcpd-appliance-hook.php", "Removing hostname ".$appliance->name." from resource ".$resource->id.".", "", "", 0, 0, $resource->id);
                        $dhcpd_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dhcpd/bin/openqrm-dhcpd-appliance remove_hostname -m ".$resource_mac." -n ".$appliance->name." -d ".$resource->id;
                        $openqrm_server->send_command($dhcpd_command);
                        break;
	}


}


?>


