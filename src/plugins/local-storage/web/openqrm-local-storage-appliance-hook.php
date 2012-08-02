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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
// special local-storage classes
require_once "$RootDir/plugins/local-storage/class/localstoragestate.class.php";
require_once "$RootDir/plugins/local-storage/class/localstorageresource.class.php";

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

$LOCAL_STORAGE_STATE_TABLE="local_storage_state";
global $LOCAL_STORAGE_STATE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;


function openqrm_local_storage_appliance($cmd, $appliance_fields) {
	global $event;
	global $openqrm_server;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $LOCAL_STORAGE_STATE_TABLE;
	// timeout for setting the resource to localboot after an installation started
	$local_storage_install_timeout=60;

	$appliance_id=$appliance_fields["appliance_id"];
	$appliance = new appliance();
	$appliance->get_instance_by_id($appliance_id);
	$resource = new resource();
	$resource->get_instance_by_id($appliance->resources);
	$image = new image();
	$image->get_instance_by_id($appliance->imageid);

	$event->log("openqrm-local-storage-appliance-hook.php", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Handling $cmd event for appliance $appliance_id", "", "", 0, 0, $resource->id);
	switch($cmd) {
		case "start":

			// local_storage configured in image deployment parameters ?
			$local_storage_auto_install_enabled = false;
			$local_storage_deployment_parameters = trim($image->get_deployment_parameter("INSTALL_CONFIG"));
			if (strlen($local_storage_deployment_parameters)) {
				$local_storage_deployment_parameter_arr = explode(":", $local_storage_deployment_parameters);
				$local_deployment_persistent = $local_storage_deployment_parameter_arr[0];
				$local_deployment_type = $local_storage_deployment_parameter_arr[1];
				// deployment type local-storage ?
				if (strcmp($local_deployment_type, "local-storage")) {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance->name." image is not from type Local-Disk deployment", "", "", 0, 0, $resource->id);
					return;
				} else {
					$local_storage_auto_install_enabled = true;
					$local_storage_server_id = $local_storage_deployment_parameter_arr[2];
					$local_storage_install_template = $local_storage_deployment_parameter_arr[3];
				}
			}

			if ($local_storage_auto_install_enabled) {
				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Detected local-storage deployment for appliance $appliance_id", "", "", 0, 0, $resource->id);
				// get the storage from the local-storage template
				$storage = new storage();
				$storage->get_instance_by_id($local_storage_server_id);
				$storage_resource = new resource();
				$storage_resource->get_instance_by_id($storage->resource_id);

				// authenticate the storage export of the clonezilla template
				// TODO : This has to support multiple parallel deployments
				$deployment = new deployment();
				$deployment->get_instance_by_id($storage->type);
				$deployment_type = $deployment->type;
				$deployment_plugin_name = $deployment->storagetype;

				$event->log("start", $_SERVER['REQUEST_TIME'], 5, "openqrm-local_storage-appliance-hook.php", "Local-Deployment: Authenticating ".$resource->ip." on storage id ".$storage->id.":".$storage_resource->ip.":".$local_storage_install_template.".", "", "", 0, 0, $resource->id);
				$auth_auto_install_storage_auth_cmd = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/".$deployment_plugin_name."/bin/openqrm-".$deployment_plugin_name." auth -r ".$local_storage_install_template." -i ".$resource->ip." -t ".$deployment_type;
				$storage_resource->send_command($storage_resource->ip, $auth_auto_install_storage_auth_cmd);
				sleep(2);

				// send deploy to openQRM to switch the pxe config to clonezilla
				$local_storage_install_template_name = basename($local_storage_install_template);
				$local_storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/local-storage/bin/openqrm-local-storage-manager deploy -m ".$resource->mac." -i ".$resource->ip." -x ".$resource->id." -n ".$local_storage_install_template_name." -d ".$storage_resource->ip.":".$local_storage_install_template;
				$openqrm_server->send_command($local_storage_command);

				// Remove image-deployment paramters, if auto-install is a single-shot actions
				if (!strcmp($local_deployment_persistent, "0")) {
					$image->set_deployment_parameters("INSTALL_CONFIG", "");
				}

				// create local-storage-state object to allow to run a late setboot to local command on the vm host
				$local_storage_state = new localstoragestate();
				$local_storage_state->remove_by_resource_id($resource->id);
				$local_storage_state_fields=array();
				$local_storage_state_fields["local_storage_id"]=openqrm_db_get_free_id('local_storage_id', $local_storage_state->_db_table);
				$local_storage_state_fields["local_storage_resource_id"]=$resource->id;
				$local_storage_state_fields["local_storage_install_start"]=$_SERVER['REQUEST_TIME'];
				$local_storage_state_fields["local_storage_timeout"]=$local_storage_install_timeout;
				$local_storage_state->add($local_storage_state_fields);

			} else {
				if (strcmp($image->type, "local-storage")) {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance->name." image not from type local-storage", "", "", 0, 0, $resource->id);
				} else {
					$event->log("start", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance->name." is installed already. Setting to local-boot", "", "", 0, 0, $resource->id);
					// we have auto-installed already, if it is VM the localstorageresource object will care to set the boot-sequence on the VM Host to local boot
					$localstorageresource = new localstorageresource();
					$localstorageresource->set_boot($resource->id, 1);
					// set pxe config to local-boot
					$local_storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/local-storage/bin/openqrm-local-storage-manager set_client_to_local_boot -m ".$resource->mac;
					$openqrm_server->send_command($local_storage_command);
				}
			}
			break;


		case "stop":

			if (strcmp($image->type, "local-storage")) {
				$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-appliance-hook.php", "Appliance ".$appliance_id."/".$appliance->name." image not from type local-storage", "", "", 0, 0, $resource->id);
			} else {
				$event->log("stop", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-appliance-hook.php", "Stop event for appliance ".$appliance_id."/".$appliance->name.".", "", "", 0, 0, $resource->id);
				// remove local-storage-state object if existing
				$local_storage_state = new localstoragestate();
				$local_storage_state->remove_by_resource_id($resource->id);
				// if it is VM the localstorageresource object will care to set the boot-sequence on the VM Host to network boot
				$localstorageresource = new localstorageresource();
				$localstorageresource->set_boot($resource->id, 0);

			}
			break;

	}
}



?>


