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

// This hook removes the resource-pool and hostlimits for a specific resource
// when a resource/Host is removed from openQRM
//
// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudrespool.class.php";
require_once "$RootDir/plugins/cloud/class/cloudhostlimit.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function openqrm_cloud_resource($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$resource_id=$resource_fields["resource_id"];
	$resource_ip=$resource_fields["resource_ip"];
	$resource_mac=$resource_fields["resource_mac"];
	// $event->log("openqrm_remove_resource", $_SERVER['REQUEST_TIME'], 5, "openqrm-cloud-resource-hook.php", "Handling $cmd event $resource_id/$resource_ip/$resource_mac", "", "", 0, 0, $resource_id);
	switch($cmd) {
		case "remove":
			if (strlen($resource_id)) {
				// cloudrespool
				$resource_pool = new cloudrespool();
				$resource_pool->get_instance_by_resource($resource_id);
				if (strlen($resource_pool->id)) {
					$resource_pool->remove($resource_pool->id);
				}
				// cloudhostlimit
				$resource_hostlimit = new cloudhostlimit();
				$resource_hostlimit->get_instance_by_resource($resource_id);
				if (strlen($resource_hostlimit->id)) {
					$resource_hostlimit->remove($resource_hostlimit->id);
				}
			}
			break;
	}
}



?>


