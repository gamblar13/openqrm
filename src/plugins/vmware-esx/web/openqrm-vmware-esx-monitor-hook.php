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
// It handles the cloud-zone syncing

function openqrm_vmware_esx_monitor() {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $BaseDir;
	global $RootDir;

	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-monitor-hook", "VMware ESX monitor hook", "", "", 0, 0, 0);
	$appliance = new appliance();
	$appliance_id_arr = $appliance->get_all_ids();
	foreach ($appliance_id_arr as $appliance_arr) {
		$appliance_id = $appliance_arr['appliance_id'];
		$appliance->get_instance_by_id($appliance_id);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($appliance->virtualization);
		if (!strcmp($virtualization->name, "VMware-ESX Host")) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-monitor-hook", "VMware ESX monitor hook - checking appliance ".$appliance_id, "", "", 0, 0, 0);
			$vmware_esx_resource = new resource();
			$vmware_esx_resource->get_instance_by_id($appliance->resources);
			// lazy stats, keep the file if it is there
			$file = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/web/vmware-esx-stat/".$vmware_esx_resource->ip.".host_statistics";
			// read stats file
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						$esx_hostname = $line[0];
						$esx_cpu_speed = $line[1];
						$esx_cpu_load = $line[2];
						$esx_cpu_physical_mem = $line[3];
						$esx_cpu_used_mem = $line[4];
						$esx_cpu_network_cards = $line[5];
						$now=$_SERVER['REQUEST_TIME'];
$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-monitor-hook", "VMware ESX stats for appliance ".$appliance_id."-".$esx_hostname."-".$esx_cpu_network_cards, "", "", 0, 0, 0);
						$esx_resource_fields["resource_hostname"]=$esx_hostname;
						$esx_resource_fields["resource_cpuspeed"]=$esx_cpu_speed;
						$esx_resource_fields["resource_load"]=$esx_cpu_load;
						$esx_resource_fields["resource_memtotal"]=$esx_cpu_physical_mem;
						$esx_resource_fields["resource_memused"]=$esx_cpu_used_mem;
						$esx_resource_fields["resource_nics"]=$esx_cpu_network_cards;
						$esx_resource_fields["resource_state"]='active';
						$esx_resource_fields["resource_lastgood"]=$now;
						$vmware_esx_resource->update_info($vmware_esx_resource->id, $esx_resource_fields);
						unset($esx_hostname);
						unset($esx_cpu_speed);
						unset($esx_cpu_load);
						unset($esx_cpu_physical_mem);
						unset($esx_cpu_used_mem);
						unset($esx_cpu_network_cards);
						unset($esx_resource_fields);
					}
				}
			}
			unlink($file);
			// send command
			$vmware_esx_host_monitor_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx post_host_statistics -i ".$vmware_esx_resource->ip;
			$openqrm_server->send_command($vmware_esx_host_monitor_cmd);
		}
	}
}

?>

