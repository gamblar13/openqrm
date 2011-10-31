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
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special fai classes
require_once "$RootDir/plugins/fai/class/faistate.class.php";
require_once "$RootDir/plugins/fai/class/fairesource.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;



// this function is going to manage the fai installation states
function openqrm_fai_monitor() {
	global $event;
	global $RootDir;
	global $openqrm_server;
	global $OPENQRM_SERVER_BASE_DIR;

	$event->log("openqrm_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-fai-monitor-hook.php", "!! checking the fai states .....", "", "", 0, 0, 0);
	$now = $_SERVER['REQUEST_TIME'];
	$fai_state_monitor = new faistate();
	$fai_state_id_arr = $fai_state_monitor->get_all_ids();
	foreach($fai_state_id_arr as $fai_state_id_db) {
		$fai_state_id = $fai_state_id_db['fai_id'];
		$fai_state = new faistate();
		$fai_state->get_instance_by_id($fai_state_id);
		$fai_time_diff = $now - $fai_state->install_start;
		if ($fai_time_diff >= $fai_state->timeout) {
			$event->log("openqrm_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-fai-monitor-hook.php", "!! fai states resource ".$fai_state->resource_id." timeout, setting to localboot.....", "", "", 0, 0, 0);
			$fairesource = new fairesource();
			$fairesource->set_boot($fai_state->resource_id, 1);
			// remove
			$fai_state->remove($fai_state->id);
		} else {
			$event->log("openqrm_fai_monitor", $_SERVER['REQUEST_TIME'], 5, "openqrm-fai-monitor-hook.php", "!! fai states still waiting for ".$fai_state->resource_id." timeout to appear .....", "", "", 0, 0, 0);
		}
	}
}



?>
