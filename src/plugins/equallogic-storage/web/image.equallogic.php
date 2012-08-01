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
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$EQUALLOGIC_STORAGE_SERVER_TABLE="equallogic_storage_servers";
global $EQUALLOGIC_STORAGE_SERVER_TABLE;
// global event for logging
$event = new event();
global $event;


function equallogic_wait_for_identfile($sfile) {
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


function get_equallogic_image_rootdevice_identifier($equallogic_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $EQUALLOGIC_STORAGE_SERVER_TABLE;
	global $event;

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/equallogic-storage/storage';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($equallogic_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	// get the storage configuration
	$eq_storage = new equallogic_storage();
	$eq_storage->get_instance_by_storage_id($equallogic_storage_id);
	$eq_storage_ip = $storage_resource->ip;
	$eq_user = $eq_storage->storage_user;
	$eq_password = $eq_storage->storage_password;
	$ident_file = "$StorageDir/$eq_storage_ip.equallogic.ident";
	if (file_exists($ident_file)) {
		unlink($ident_file);
	}
	// send command
	$openqrm_server_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage post_identifier -u $eq_user -p $eq_password -e $eq_storage_ip";
	$output = shell_exec($openqrm_server_command);
	if (!equallogic_wait_for_identfile($ident_file)) {
		$event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.equallogic-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
		return;
	}
	$lun_loop=1;
	$fcontent = file($ident_file);
	foreach($fcontent as $lun_info) {
		$equallogic_output = trim($lun_info);
		$first_at_pos = strpos($equallogic_output, "@");
		$first_at_pos++;
		$eq_name = trim(substr($equallogic_output, 0, $first_at_pos-1));
		$rootdevice_identifier_array[] = array("value" => "/dev/$eq_storage_ip/$eq_name", "label" => "$eq_name");
		$lun_loop++;
	}
	return $rootdevice_identifier_array;

}

function get_equallogic_image_default_rootfs() {
	return "ext3";
}

function get_equallogic_rootfs_transfer_methods() {
	return true;
}

function get_equallogic_rootfs_set_password_method() {
	return true;
}

function get_equallogic_is_network_deployment() {
	return true;
}

function get_equallogic_local_deployment_enabled() {
	return false;
}


?>


