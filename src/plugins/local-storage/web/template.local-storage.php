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
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;

function wait_for_local_storage_template_list($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
	$refresh_loop=0;
	while (!file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}

function get_local_storage_templates($local_storage_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/local-storage/storage';
	$storage = new storage();
	$storage->get_instance_by_id($local_storage_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = "$StorageDir/$storage_resource_id.lv.local-storage.ident";
	if (file_exists($ident_file)) {
		unlink($ident_file);
	}
	// send command
	$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/local-storage/bin/openqrm-local-storage post_identifier -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
	$storage_resource->send_command($storage_resource->ip, $resource_command);
	if (!wait_for_local_storage_template_list($ident_file)) {
		$event->log("get_local_deployment_templates_identifier", $_SERVER['REQUEST_TIME'], 2, "template.local-storage.php", "Timeout while requesting template identifier from storage id $storage->id", "", "", 0, 0, 0);
		return;
	}
	$local_deployment_tepmplates_identifier_array = array();
	$fcontent = file($ident_file);
	foreach($fcontent as $lun_info) {
		$tpos = strpos($lun_info, ",");
		$template_export = trim(substr($lun_info, $tpos+1));
		$template_name = basename($template_export);
		$template_deployment_parameter = "local-storage:".$local_storage_storage_id.":".$template_export;
		$local_deployment_tepmplates_identifier_array[] = array("value" => "$template_deployment_parameter", "label" => "$template_name");
	}
	return $local_deployment_tepmplates_identifier_array;
}

function get_local_storage_methods() {
	$local_storage_deployment_array = array("value" => "local-storage", "label" => "Automatic Clone from template");
	return $local_storage_deployment_array;
}



function get_local_storage_additional_parameters() {
	$local_deployment_additional_parameters[] = '';
	return $local_deployment_additional_parameters;
}



?>

