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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// special nagios classes
require_once "$RootDir/plugins/nagios3/class/nagios3_service.class.php";
require_once "$RootDir/plugins/nagios3/class/nagios3_host.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_nagios3_appliance($cmd, $appliance_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	$appliance_id=$appliance_fields["appliance_id"];
	$appliance_name=$appliance_fields["appliance_name"];
	$resource = new resource();
	$resource->get_instance_by_id($appliance_fields["appliance_resources"]);
	$appliance_ip=$resource->ip;

	// get the nagios service checks
	$nagios_host = new nagios3_host();
	$nagios_host->get_instance_by_appliance_id($appliance_id);
	$active_nagios_services = explode(',', $nagios_host->appliance_services);
	$nagios_service_list = '';
	foreach($active_nagios_services as $service_id) {
		$nagios_service = new nagios3_service();
		$nagios_service->get_instance_by_id($service_id);
		$nagios_service_list = $nagios_service_list.",".$nagios_service->port;;
	}
	$nagios_service_list = substr($nagios_service_list, 1);
	if (!strlen($nagios_service_list)) {
		$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-nagios3-appliance-hook.php", "Appliance $appliance_id has no configured nagios services, skipping...", "", "", 0, 0, $appliance_id);
		return 0;
	}

	$event->log("openqrm_new_appliance", $_SERVER['REQUEST_TIME'], 5, "openqrm-nagios3-appliance-hook.php", "Handling $cmd event $appliance_id/$appliance_name/$appliance_ip", "", "", 0, 0, $appliance_id);
	switch($cmd) {
		case "start":
			$nagios_appliance_start_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/nagios3/bin/openqrm-nagios-manager add -n ".$appliance_name." -i ".$resource->ip." -p ".$nagios_service_list;
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command($nagios_appliance_start_cmd);
			break;
		case "stop":
			$nagios_appliance_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager remove_host -n ".$appliance_name;
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command($nagios_appliance_stop_cmd);
			break;
		case "remove":
			$nagios_appliance_stop_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager remove_host -n ".$appliance_name;
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command($nagios_appliance_stop_cmd);
			// remove nagios_host from the db
			$nagios_host->remove_by_appliance_id($appliance_id);
			break;

	}
}



?>


