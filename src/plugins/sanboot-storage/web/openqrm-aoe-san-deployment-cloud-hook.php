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


// This file implements the cloud storage methods

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";

$event = new event();
global $event;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $RESOURCE_INFO_TABLE;


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_aoe_san_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_clone", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Creating clone of image on storage", "", "", 0, 0, 0);

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image, this is already the new logical clone
	// we just need to physical snapshot it and update the rootdevice
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm-sanboot-storage snap -n $image_location_name -v $volume_group -t aoe-san-deployment -s $image_clone_name -m $disk_size";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Running : $image_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_clone_cmd);
	// wait for clone
	sleep(4);
	// find the new rootdevice of the snapshot, get it via the storage-ident hook
	$rootdevice_identifier_hook = "$BaseDir/boot-service/image.aoe-san-deployment.php";
	// require once
	require_once "$rootdevice_identifier_hook";
	$rootdevice_identifier_arr = array();
	$rootdevice_identifier_arr = get_image_rootdevice_identifier($image->storageid);
	foreach($rootdevice_identifier_arr as $id) {
		foreach($id as $aoe_identifier_string) {
			if (strstr($aoe_identifier_string, $image_clone_name)) {
				$aoe_clone_rootdevice_tmp=strrchr($aoe_identifier_string, ":");
				$aoe_clone_rootdevice=trim(str_replace(":", "", $aoe_clone_rootdevice_tmp));
				break;
			}
		}
	}
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => "$volume_group:$image_clone_name:$aoe_clone_rootdevice",
	);
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Updating rootdevice of image $image_id / $image_name with $volume_group:$image_clone_name:$aoe_clone_rootdevice", "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
}



// removes the volume of an image
function remove_aoe_san_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_aoe_san_deployment", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Removing image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm-sanboot-storage remove -n $image_location_name -v $volume_group -t aoe-san-deployment";
	$event->log("remove_aoe_san_deployment", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_aoe_san_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_aoe_san_deployment", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Resize image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm-sanboot-storage resize -n $image_location_name -v $volume_group -m $resize_value -t aoe-san-deployment";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
}



// creates a private copy of the volume of an image
function create_private_aoe_san_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_aoe_san_deployment", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Creating private image on storage", "", "", 0, 0, 0);

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	// get image
	$image = new image();
	$image->get_instance_by_id($cloudimage->image_id);
	$image_id = $image->id;
	$image_name = $image->name;
	$image_type = $image->type;
	$image_version = $image->version;
	$image_rootdevice = $image->rootdevice;
	$image_rootfstype = $image->rootfstype;
	$image_storageid = $image->storageid;
	$image_isshared = $image->isshared;
	$image_comment = $image->comment;
	$image_capabilities = $image->capabilities;
	$image_deployment_parameter = $image->deployment_parameter;

	// get image storage
	$storage = new storage();
	$storage->get_instance_by_id($image_storageid);
	$storage_resource_id = $storage->resource_id;
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;
	// create an admin user to post when cloning has finished
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	// parse the volume group info in the identifier
	$ident_separate=strpos($image_rootdevice, ":");
	$volume_group=substr($image_rootdevice, 0, $ident_separate);
	$image_rootdevice_rest=substr($image_rootdevice, $ident_separate+1);
	$ident_separate2=strpos($image_rootdevice_rest, ":");
	$image_location_name=substr($image_rootdevice_rest, 0, $ident_separate2);
	$root_device=substr($image_rootdevice_rest, $ident_separate2+1);
	$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sanboot-storage/bin/openqrm-sanboot-storage clone -n $image_location_name -s $private_image_name -v $volume_group -m $private_disk -t aoe-san-deployment -u $openqrm_admin_user->name -p $openqrm_admin_user->password";
	$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "aoe-san-deployment-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
	// set the storage specific image root_device parameter
	$clone_image_fields["image_rootdevice"] = str_replace($image_location_name, $private_image_name, $image->rootdevice);
	$private_success = true;
	$new_rootdevice = str_replace($image_location_name, $private_image_name, $image->rootdevice);
	return $new_rootdevice;
}


// ---------------------------------------------------------------------------------


?>