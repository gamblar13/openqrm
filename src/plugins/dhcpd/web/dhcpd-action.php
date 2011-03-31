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

	Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
*/



$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// get params
$dhcpd_command = htmlobject_request('dhcpd_command');
$dhcpd_resource_id = htmlobject_request('resource_id');
$dhcpd_resource_ip = htmlobject_request('resource_ip');

// get event + openQRM server
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "dhcpd-action", "Un-Authorized access to dhcpd-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$dhcpd_command", $_SERVER['REQUEST_TIME'], 5, "dhcpd-action", "Processing dhcpd command $dhcpd_command", "", "", 0, 0, 0);
switch ($dhcpd_command) {
	case 'post_ip':
		$event->log("$dhcpd_command", $_SERVER['REQUEST_TIME'], 5, "dhcpd-action", "Updateing resource $dhcpd_resource_id with ip $dhcpd_resource_ip", "", "", 0, 0, 0);
		$dhcpd_resource = new resource();
		$dhcpd_resource->get_instance_by_id($dhcpd_resource_id);
		$dhcpd_resource_fields["resource_ip"] = $dhcpd_resource_ip;
		$dhcpd_resource->update_info($dhcpd_resource_id, $dhcpd_resource_fields);
		break;

	default:
		$event->log("$dhcpd_command", $_SERVER['REQUEST_TIME'], 3, "dhcpd-action", "No such dhcpd command ($dhcpd_command)", "", "", 0, 0, 0);
		break;


}

?>
