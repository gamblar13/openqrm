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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;

$event = new event();
global $event;
global $OPENQRM_SERVER_BASE_DIR;

function wait_for_identfile($sfile) {
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


function get_image_rootdevice_identifier($citrix_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;
	$openqrm_server = new openqrm_server();

	// place for the storage stat files
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix-storage/citrix-storage-stat';
	$rootdevice_identifier_array = array();
	$storage = new storage();
	$storage->get_instance_by_id($citrix_storage_id);
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = $StorageDir."/".$storage_resource->ip.".vdi.citrix-deployment.ident";
	if (file_exists($ident_file)) {
		unlink($ident_file);
	}
	// send command
	$storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage post_identifier -i ".$storage_resource->ip." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
	$openqrm_server->send_command($storage_command);
	if (!wait_for_identfile($ident_file)) {
		$event->log("get_image_rootdevice_identifier", $_SERVER['REQUEST_TIME'], 2, "image.citrix-deployment", "Timeout while requesting image identifier from storage id $storage->id", "", "", 0, 0, 0);
		return;
	}
	$fcontent = file($ident_file);
	foreach($fcontent as $vdi_info) {
		$identfier_array = explode(":", $vdi_info);
		$vdi_uuid = $identfier_array[0];
		$image_name = ltrim($identfier_array[1], "@");
		$image_name = str_replace("@", " ", $image_name);
		$rootdevice_identifier_array[] = array("value" => "$vdi_uuid", "label" => "$image_name");
	}
	return $rootdevice_identifier_array;
}


function get_image_default_rootfs() {
	return "local";
}

function get_rootfs_transfer_methods() {
	return false;
}

function get_rootfs_set_password_method() {
	return false;
}

function get_is_network_deployment() {
	return false;
}

function get_local_deployment_enabled() {
	return true;
}



?>


