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
