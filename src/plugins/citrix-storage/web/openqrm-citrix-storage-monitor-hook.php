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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;



// this function is going to be called by the monitor-hook in the resource-monitor
// It monitors the Citrix Hosts

function openqrm_citrix_storage_monitor() {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $BaseDir;
	global $RootDir;

	$event->log("citrix-storage", $_SERVER['REQUEST_TIME'], 5, "citrix-storage-monitor-hook", "Citrix-storage monitor hook", "", "", 0, 0, 0);
	$appliance = new appliance();
	$appliance_id_arr = $appliance->get_all_ids();
	foreach ($appliance_id_arr as $appliance_arr) {
		$appliance_id = $appliance_arr['appliance_id'];
		$appliance->get_instance_by_id($appliance_id);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance->virtualization);
		if (!strcmp($virtualization->name, "Citrix-storage Host")) {
			$event->log("citrix-storage", $_SERVER['REQUEST_TIME'], 5, "citrix-storage-monitor-hook", "Citrix-storage monitor hook - checking appliance ".$appliance_id, "", "", 0, 0, 0);
			$citrix_resource = new resource();
			$citrix_resource->get_instance_by_id($appliance->resources);
			$citrix_host_monitor_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage-vm statistics -i ".$citrix_resource->ip." -x ".$citrix_resource->id." -n ".$appliance->name;
			// send command
			$openqrm_server->send_command($citrix_host_monitor_cmd);
		}
	}
}

?>


