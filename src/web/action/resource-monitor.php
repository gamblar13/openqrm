
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
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $RESOURCE_INFO_TABLE;

$resource_id = htmlobject_request('resource_id');
$resource_command = htmlobject_request('resource_command');
$resource_mac = htmlobject_request('resource_mac');


$resource_ip = htmlobject_request('resource_ip');
$resource_state = htmlobject_request('resource_state');
$resource_event = htmlobject_request('resource_event');
$resource_lastgood = $_SERVER['REQUEST_TIME'];
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "resource_", 9) == 0) {
		$resource_fields[$key] = $value;
	}
}
unset($resource_fields["resource_command"]);
// set lastgood
$resource_fields["resource_lastgood"]=$resource_lastgood;

// gather for event vars
$event_name = htmlobject_request('event_name');
$event_priority = htmlobject_request('event_priority');
$event_source = htmlobject_request('event_source');
$event_description = htmlobject_request('event_description');


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

$event = new event();

switch ($resource_command) {

	// get_parameter requires :
	// resource_mac
	case 'get_parameter':
		// if resource-id = -1 we add a new resource first
		if ($resource_id == "-1") {
			// check if resource already exists
			$resource = new resource();
			if (!$resource->exists($resource_mac)) {
				// add resource
				$new_resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
				$resource->id = $new_resource_id;
				// 	check if resource_id is free
				if (!$resource->is_id_free($resource->id)) {
					$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 3, "resource-monitor", "Given resource id $resource->id is already in use!", "", "", 0, 1, $resource->id);
					echo "Given resource id $resource->id is already in use!";
					exit();
				}
				$event->log("get_parameter", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Adding new resource $new_resource_id ($resource_mac)", "", "", 0, 1, $resource->id);
				# send add resource to openQRM-server
				$openqrm_server->send_command("openqrm_server_add_resource $new_resource_id $resource_mac $resource_ip");
				# add resource to db
				$resource_fields["resource_id"]=$new_resource_id;
				$resource_fields["resource_localboot"]=0;
				$resource_fields["resource_vtype"]=1;
				$resource_fields["resource_vhostid"]=$new_resource_id;
				$resource->add($resource_fields);
			}
		}
		if (strlen($resource_mac)) {
			$resource = new resource();
			$resource->get_instance_by_mac("$resource_mac");
			// update the resource parameter in any way
			$resource->update_info($resource->id, $resource_fields);
			$resource->get_parameter($resource->id);
		} else if (strlen($resource_id)) {
			$resource = new resource();
			$resource->get_instance_by_id($resource_id);
			// update the resource parameter in any way
			$resource->update_info($resource->id, $resource_fields);
			$resource->get_parameter($resource->id);
		}
		exit();
		break;

	// update_info requires :
	// resource_id
	// array of resource_fields
	case 'update_info':
		$resource = new resource();
		if (strlen($resource_id)) {
			$resource->get_instance_by_id($resource_id);
			if (!strcmp($resource->event, "reboot")) {
				// we do not accept this stats since the resource will be rebooted
				// reset the events field
				$resource_reboot_fields=array();
				$resource_reboot_fields["resource_state"]="transition";
				$resource_reboot_fields["resource_event"]="";
				$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Rejecting statistics from rebooting resource $resource_id", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_reboot_fields);
			} else {
				// $event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Processing statistics from resource $resource_id", "", "", 0, 0, $resource_id);
				$resource->update_info($resource_id, $resource_fields);
			}
		}
		// in case the openQRM-server sends its stats we check
		// the states of all resources
		if ("$resource_id" == "0") {
			$event->log("update_info", $_SERVER['REQUEST_TIME'], 5, "resource-monitor", "Checking states of all resources", "", "", 0, 0, 0);
			$resource->check_all_states();

			// check if there are any image_authentications to manage
			$image_auth = new image_authentication();
			$image_auth->check_all_image_authentication();

			// here a plugin hook for things which needs to be done periodically
			$plugin = new plugin();
			$enabled_plugins = $plugin->enabled();
			foreach ($enabled_plugins as $index => $plugin_name) {
				$plugin_monitor_hook = "$RootDir/plugins/$plugin_name/openqrm-$plugin_name-monitor-hook.php";
				if (file_exists($plugin_monitor_hook)) {
					$event->log("plugin_monitor_hook", $_SERVER['REQUEST_TIME'], 5, "resource-monitor.php", "Found plugin $plugin_name handling monitor event.", "", "", 0, 0, $resource_id);
					require_once "$plugin_monitor_hook";
					$monitor_function="openqrm_"."$plugin_name"."_monitor";
					$monitor_function=str_replace("-", "_", $monitor_function);
					$monitor_function();
				}
			}

		}
		exit();
		break;

	// update_status requires :
	// resource_id
	// resource_state
	// resource_event
	case 'update_status':
		if (strlen($resource_id)) {
			$resource = new resource();
			$resource->update_status($resource_id, $resource_state, $resource_event);
		}
		exit();
		break;

	// post_event requires :
	// resource_id
	// event_name
	// event_priority
	// event_source
	// event_description
	case 'post_event':
		if (strlen($resource_id)) {
			$event->log($event_name, $_SERVER['REQUEST_TIME'], $event_priority, $event_source, $event_description, "", "", 0, 0, 0);
		}
		exit();
		break;

	default:
		echo "No Such openQRM-command!";
		break;
}

?>


