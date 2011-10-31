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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_SUBNET_MASK;
global $OPENQRM_SERVER_DEFAULT_GATEWAY;
$event = new event();
global $event;



function openqrm_idoit_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_SERVER_SUBNET_MASK;
	global $OPENQRM_SERVER_DEFAULT_GATEWAY;
	global $OPENQRM_EXEC_PORT;
	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$resource_mac=$resource_fields["resource_mac"];
	$event->log("openqrm_new_resource", $_SERVER['REQUEST_TIME'], 5, "openqrm-idoit-resource-hook.php", "Handling $cmd event $resource_id/$resource_ip/$resource_mac", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "add":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/idoit/bin/openqrm-idoit-manager map -i $resource_ip -m $resource_mac");
			break;
		case "remove":
			$openqrm_server = new openqrm_server();
			$openqrm_server->send_command("$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/idoit/bin/openqrm-idoitctl remove -m $resource_mac");
			break;

	}
}



?>


