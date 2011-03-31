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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
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
function create_clone_xen_lvm_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Creating clone of image on storage", "", "", 0, 0, 0);

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
	$volume_group_location=dirname($image_rootdevice);
	$volume_group=basename($volume_group_location);
	$image_location_name=basename($image_rootdevice);
	// set default snapshot size
	if (!strlen($disk_size)) {
		$disk_size=5000;
	}
	// update the image rootdevice parameter
	$ar_image_update = array(
		'image_rootdevice' => "/dev/$volume_group/$image_clone_name",
	);

	// For xen-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a SAN backend with dedicated volumes per vm-host which all
	// contain all "golden-images" which are used for snapshotting.
	// We do this to overcome the current lvm limitation of not supporting cluster-wide snapshots
	// get the vm resource
	$vm_resource = new resource();
	$vm_resource->get_instance_by_id($cloudimage->resource_id);
	// get the lxc host
	$vm_host_resource = new resource();
	$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
	// san backend ?
	if ($vm_host_resource->id != $resource->id) {
		$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS NOT available on this xen-storage host, $resource->id not equal $vm_host_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		// update the image storage id with the vm-host-resource
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_type);
		// loop over all storage id from type $image_type
		$found_image_storage=0;
		$storage_list_by_type = new storage();
		$storage_id_list_by_type = $storage_list_by_type->get_ids_by_storage_type($image_deployment->id);
		foreach($storage_id_list_by_type as $storage_id_list) {
			$storage_id = $storage_id_list['storage_id'];
			$tstorage = new storage();
			$tstorage->get_instance_by_id($storage_id);
			if ($tstorage->resource_id == $vm_host_resource->id) {
				// re-create update array + new storage id
				$ar_image_update = array(
					'image_rootdevice' => "/dev/$volume_group/$image_clone_name",
					'image_storageid' => $tstorage->id,
				);
				$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Updating Image $image_id / $image_name with storage id $tstorage->id", "", "", 0, 0, $appliance_id);
				$found_image_storage=1;
				break;
			}
		}
		if ($found_image_storage == 0) {
			$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-xen-lvm-deployment-cloud-hook.php", "SETUP ERROR: Could not find a storage server type $image_type using resource $vm_host_resource->id. Please create one!", "", "", 0, 0, $appliance_id);
			$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 2, "openqrm-xen-lvm-deployment-cloud-hook.php", "SETUP ERROR: Not cloning image $image_id", "", "", 0, 0, $appliance_id);
			return;
		}

	} else {
		$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS available on this xen-storage host, $resource->id equal $vm_host_resource->id", "", "", 0, 0, $appliance_id);
	}

	$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Updating rootdevice of image $image_id / $image_name with /$vol/$image_clone_name", "", "", 0, 0, 0);
	$image->update($image_id, $ar_image_update);
	$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage snap -n $image_location_name -v $volume_group -s $image_clone_name -m $disk_size";
	$event->log("create_clone_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Running : $image_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($vm_host_resource->ip, $image_clone_cmd);
}



// removes the volume of an image
function remove_xen_lvm_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Removing image on storage", "", "", 0, 0, 0);

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
	$volume_group_location=dirname($image_rootdevice);
	$volume_group=basename($volume_group_location);
	$image_location_name=basename($image_rootdevice);

	// For xen-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a SAN backend with dedicated volumes per vm-host which all
	// contain all "golden-images" which are used for snapshotting.
	// We do this to overcome the current lvm limitation of not supporting cluster-wide snapshots
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("remove_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS NOT available on this xen-storage host, $resource->id not equal $vm_host_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("remove_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS available on this xen-storage host, $resource->id equal $vm_host_resource->id", "", "", 0, 0, $appliance_id);
		}
	}

	$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage remove -n $image_location_name -v $volume_group";
	$event->log("remove_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_remove_clone_cmd);
}


// resizes the volume of an image
function resize_xen_lvm_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Resize image on storage", "", "", 0, 0, 0);

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
	$volume_group_location=dirname($image_rootdevice);
	$volume_group=basename($volume_group_location);
	$image_location_name=basename($image_rootdevice);

	// For xen-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a SAN backend with dedicated volumes per vm-host which all
	// contain all "golden-images" which are used for snapshotting.
	// We do this to overcome the current lvm limitation of not supporting cluster-wide snapshots
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("resize_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS NOT available on this xen-storage host, $resource->id not equal $vm_host_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("resize_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS available on this xen-storage host, $resource->id equal $vm_host_resource->id", "", "", 0, 0, $appliance_id);
		}
	}

	$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage resize -n $image_location_name -v $volume_group -m $resize_value";
	$event->log("resize_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
}



// creates a private copy of the volume of an image
function create_private_xen_lvm_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Creating private image on storage", "", "", 0, 0, 0);

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
	$volume_group_location=dirname($image_rootdevice);
	$volume_group=basename($volume_group_location);
	$image_location_name=basename($image_rootdevice);

	// For xen-storage vms we assume that the image is located on the vm-host
	// so we send the auth command to the vm-host instead of the image storage.
	// This enables using a SAN backend with dedicated volumes per vm-host which all
	// contain all "golden-images" which are used for snapshotting.
	// We do this to overcome the current lvm limitation of not supporting cluster-wide snapshots
	//
	// Still we need to send the remove command to the storage resource since the
	// create-phase automatically adapted the image->storageid, we cannot use the vm-resource here
	// because cloudimage->resource_id will be set to -1 when the cloudapp is in paused/resize/private state
	//
	if ($cloudimage->resource_id > 0) {
		$vm_resource = new resource();
		$vm_resource->get_instance_by_id($cloudimage->resource_id);
		// get the lxc host
		$vm_host_resource = new resource();
		$vm_host_resource->get_instance_by_id($vm_resource->vhostid);
		// san backend ?
		if ($vm_host_resource->id != $resource->id) {
			$event->log("create_private_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS NOT available on this xen-storage host, $resource->id not equal $vm_host_resource->id !! Assuming SAN Backend", "", "", 0, 0, $appliance_id);
		} else {
			$event->log("create_private_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Image $image_id IS available on this xen-storage host, $resource->id equal $vm_host_resource->id", "", "", 0, 0, $appliance_id);
		}
	}

	$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage clone -n $image_location_name -s $private_image_name -v $volume_group -m $private_disk -u $openqrm_admin_user->name -p $openqrm_admin_user->password";
	$event->log("create_private_xen_lvm_deployment", $_SERVER['REQUEST_TIME'], 5, "openqrm-xen-lvm-deployment-cloud-hook.php", "Running : $image_resize_cmd", "", "", 0, 0, 0);
	$resource->send_command($resource_ip, $image_resize_cmd);
	// set the storage specific image root_device parameter
	$new_rootdevice = str_replace($image_location_name, $private_image_name, $image->rootdevice);
	return $new_rootdevice;
}



// ---------------------------------------------------------------------------------


?>