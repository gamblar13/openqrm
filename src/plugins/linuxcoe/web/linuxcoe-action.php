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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "linuxcoe-action", "Un-Authorized access to linuxcoe-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$linuxcoe_command = htmlobject_request('linuxcoe_command');

// main
$event->log("$linuxcoe_command", $_SERVER['REQUEST_TIME'], 5, "linuxcoe-action", "Processing linuxcoe command $linuxcoe_command", "", "", 0, 0, 0);

switch ($linuxcoe_command) {

	case 'init':
		// create linuxcoe_state
		// -> linuxcoe_state
		// linuxcoe_id INT(5)
		// linuxcoe_resource_id INT(5)
		// linuxcoe_install_start VARCHAR(20)
		// linuxcoe_timeout INT(5)
		$create_linuxcoe_state = "create table linuxcoe_state(linuxcoe_id INT(5), linuxcoe_resource_id INT(5), linuxcoe_install_start VARCHAR(20), linuxcoe_timeout INT(5))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_linuxcoe_state);
		break;

	case 'uninstall':
		// remove linuxcoe_resource
		$remove_linuxcoe_state = "drop table linuxcoe_state;";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($remove_linuxcoe_state);
		break;



	default:
		$event->log("$linuxcoe_command", $_SERVER['REQUEST_TIME'], 3, "linuxcoe-action", "No such event command ($linuxcoe_command)", "", "", 0, 0, 0);
		break;


}






?>
