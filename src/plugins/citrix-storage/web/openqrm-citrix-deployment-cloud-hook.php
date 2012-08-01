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


// This file implements the cloud storage methods

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";

global $RESOURCE_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


function wait_for_clone_finished($sfile) {
	global $event;
	$refresh_delay=1;
	$refresh_loop_max=540;
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


// ---------------------------------------------------------------------------------
// general cloudstorage methods
// ---------------------------------------------------------------------------------


// clones the volume of an image
function create_clone_citrix_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $openqrm_server;
	global $RootDir;

	// we got the cloudimage id here, get the image out of it
	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Creating clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
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
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;

	// For citrix-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the lxc host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	// san backend ?
	if ($vm_host_resource->id != $resource->id) {
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this citrix-storage host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
	} else {
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Image ".$image_id." IS available on this citrix-storage host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
	}
	$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Cloning image ".$image_id." / ".$image_name." to ".$image_clone_name, "", "", 0, 0, 0);

	// here we set the root device of the cloned image to null
	// this prevents to destroy the original image root-device in case of deprovision during cloning
	$ar_image_update = array(
		'image_rootdevice' => '',
	);
	$image->update($image_id, $ar_image_update);
	$image->get_instance_by_id($image_id);
	// prepare clone command
	$openqrm_admin_user = new user("openqrm");
	$openqrm_admin_user->set_user();
	$image_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage clone -i ".$vm_host_resource->ip." -n ".$image_name." -r ".$image_rootdevice." -s ".$image_clone_name." -u ".$openqrm_admin_user->name." -p ".$openqrm_admin_user->password." -t ".$deployment->type;
	$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Running : ".$image_clone_cmd, "", "", 0, 0, 0);
	// here we have to loop until the clone finished command arrived
	// since this hook is being run from the cloud-monitor hook we need to give the full path
	$statfile=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/web/citrix-storage-stat/".$image_clone_name.".clone";
	if (file_exists($statfile)) {
		unlink($statfile);
	}
	// send command
	$openqrm_server->send_command($image_clone_cmd);
	// and wait for the resulting statfile
	if (!wait_for_clone_finished($statfile)) {
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-citrix-deployment-cloud-hook.php", "Timeout for creating clone ".$image_clone_name." of image ".$cloudimage->image_id."!", "", "", 0, 0, 0);
	}
	if (file_exists($statfile)) {
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Created clone ".$image_clone_name." of image ".$cloudimage->image_id." on the storage", "", "", 0, 0, 0);
		// update rootfs with vdi uuid from statfile
		$new_vdi_uuid = trim(file_get_contents($statfile));
		// update the image rootdevice parameter
		$ar_image_update = array(
			'image_rootdevice' => $new_vdi_uuid,
		);
		$image->update($image_id, $ar_image_update);
		$image->get_instance_by_id($image_id);
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Updated image ".$image_clone_name." with rootdevice ".$new_vdi_uuid.".", "", "", 0, 0, 0);
		unlink($statfile);
	} else {
		$event->log("create_clone_citrix_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-citrix-deployment-cloud-hook.php", "Timeout, statfile ".$statfile." does not exist!", "", "", 0, 0, 0);

	}
}




// removes the volume of an image
function remove_citrix_deployment($cloud_image_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $openqrm_server;
	global $RootDir;

	$cloudimage = new cloudimage();
	$cloudimage->get_instance_by_id($cloud_image_id);
	$event->log("remove_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Removing image ".$cloudimage->image_id." from storage.", "", "", 0, 0, 0);
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
	// get deployment type
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);
	// get storage resource
	$resource = new resource();
	$resource->get_instance_by_id($storage_resource_id);
	$resource_id = $resource->id;
	$resource_ip = $resource->ip;

	// For citrix-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a NAS/Glusterfs backend with all volumes accessible for all hosts
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		// try to get the vm resource
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("remove_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Image ".$image_id." IS NOT available on this citrix-storage host, ".$resource->id." not equal ".$vm_host_resource->id." !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("remove_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Image ".$image_id." IS available on this citrix-storage host, ".$resource->id." equal ".$vm_host_resource->id.".", "", "", 0, 0, $appliance_id);
		}
	}
	$image_remove_clone_cmd=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage remove -i ".$vm_host_resource->ip." -r ".$image_rootdevice." -t ".$deployment->type;
	$event->log("remove_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Running : ".$image_remove_clone_cmd.".", "", 0, 0, 0);
	$openqrm_server->send_command($image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_citrix_deployment($cloud_image_id, $resize_value) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $openqrm_server;
	global $RootDir;

	$event->log("resize_citrix_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-citrix-deployment-cloud-hook.php", "Resize is not support for citix-deployment.", "", 0, 0, 0);
}

// creates a private copy of the volume of an image
function create_private_citrix_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $IMAGE_AUTHENTICATION_TABLE;
	global $openqrm_server;
	global $RootDir;


	// the regular clone function
	create_clone_citrix_deployment($cloud_image_id, $private_image_name, $private_disk);
	// set the storage specific image root_device parameter
	// TODO
	$new_rootdevice = "";
	return $new_rootdevice;
}



// ---------------------------------------------------------------------------------


?>