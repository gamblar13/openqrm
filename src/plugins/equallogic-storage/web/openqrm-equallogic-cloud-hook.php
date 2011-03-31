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
// special equallogic-storage classes
$equallogic_storage_class = "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";
require_once $equallogic_storage_class;

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
function create_clone_equallogic($image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_clone", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Creating clone of image on storage", "", "", 0, 0, 0);

	// get image, this is already the new logical clone
	// we just need to physical snapshot it and update the rootdevice
	$image = new image();
	$image->get_instance_by_id($image_id);
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
	// since the equallogic storage is not really good at cloning/snapshotting
	// we use regular disks + install-from-nfs !
	$equallogic_volume_name=basename($image_rootdevice);
	// get the password for the equallogic-filer
	$eq_storage = new equallogic_storage();
	$eq_storage->get_instance_by_storage_id($storage->id);
	if (!strlen($eq_storage->storage_id)) {
		$strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook", $strMsg, "", "", 0, 0, 0);
	} else {
		// generate a new image password for the clone
		$image->get_instance_by_id($image_id);
		$image_password = $image->generatePassword(14);
		$image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
		$eq_storage_ip = $resource_ip;
		$eq_user = $eq_storage->storage_user;
		$eq_password = $eq_storage->storage_password;
		// set default snapshot size
		if (!strlen($disk_size)) {
			$disk_size=5000;
		}
		// we need to special take care that the LUN name does not contain special characters
		$image_clone_lun_name = $image_clone_name;
		$image_clone_lun_name = preg_replace('/_/',"-", $image_clone_lun_name);

		// and that it does not exceed 16 chars for EQEMU (SCST based limit, real EQ does not care about lun length)
		if(preg_match("/emulator/i",$eq_storage->storage_comment)) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook","Equallogic emulator detected, trimming LUNs to 16 characters", "", "", 0, 0, 0);
			$image_clone_lun_name = substr($image_clone_lun_name,0,16);
		}
		$image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage add -n $image_clone_lun_name -m $disk_size -u $eq_user -p $eq_password -e $eq_storage_ip";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Running : $image_clone_cmd", "", "", 0, 0, 0);
		$output = shell_exec($image_clone_cmd);
		// update the image rootdevice parameter
		$clone_image_root_device = preg_replace('#'.$equallogic_volume_name.'$#', $image_clone_lun_name, $image_rootdevice);
		$ar_image_update = array(
			'image_rootdevice' => $clone_image_root_device,
		);
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Updating rootdevice of image $image_id / $image_name with $clone_image_root_device", "", "", 0, 0, 0);
		$image->update($image_id, $ar_image_update);
	}
}



// removes the volume of an image
function remove_equallogic($image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_equallogic", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Removing image on storage", "", "", 0, 0, 0);

	// get image
	$image = new image();
	$image->get_instance_by_id($image_id);
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
	// equallogic-storage
	$equallogic_volume_name=basename($image_rootdevice);
	// get the password for the equallogic-filer
	$eq_storage = new equallogic_storage();
	$eq_storage->get_instance_by_storage_id($storage->id);
	if (!strlen($eq_storage->storage_id)) {
		$strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook", $strMsg, "", "", 0, 0, 0);
	} else {
		$eq_storage_ip = $resource_ip;
		$eq_user = $eq_storage->storage_user;
		$eq_password = $eq_storage->storage_password;
		$image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage remove -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip";
		$event->log("remove_equallogic", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
		$output = shell_exec($image_remove_clone_cmd);
	}
}


// resizes the volume of an image
function resize_equallogic($image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_equallogic", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Resize image on storage", "", "", 0, 0, 0);

	// get image
	$image = new image();
	$image->get_instance_by_id($image_id);
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
	// equallogic-storage
	$equallogic_volume_name=basename($image_rootdevice);
	// get the password for the equallogic-filer
	$eq_storage = new equallogic_storage();
	$eq_storage->get_instance_by_storage_id($storage->id);
	if (!strlen($eq_storage->storage_id)) {
		$strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook", $strMsg, "", "", 0, 0, 0);
	} else {
		$eq_storage_ip = $resource_ip;
		$eq_user = $eq_storage->storage_user;
		$eq_password = $eq_storage->storage_password;
		$eq_resize_from = $ci->disk_size;
		$eq_resize_to = $ci->disk_rsize;
		// Convert values to MB
		if(preg_match('/TB$/i',$eq_resize_from)) {
		 $eq_resize_from = preg_replace('/TB$/i','',$eq_resize_from);
		 $eq_resize_from = round($eq_resize_from * 1024 * 1024);
		}
		if(preg_match('/GB$/i',$eq_resize_from)) {
		 $eq_resize_from = preg_replace('/GB$/i','',$eq_resize_from);
		 $eq_resize_from = round($eq_resize_from * 1024);
		}
		if(preg_match('/MB$/i',$eq_resize_from)) {
		 $eq_resize_from = preg_replace('/MB$/i','',$eq_resize_from);
		 $eq_resize_from = round($eq_resize_from);
		}
		if(preg_match('/TB$/i',$eq_resize_to)) {
		 $eq_resize_to = preg_replace('/TB$/i','',$eq_resize_to);
		 $eq_resize_to = round($eq_resize_to * 1024 * 1024);
		}
		if(preg_match('/GB$/i',$eq_resize_to)) {
		 $eq_resize_to = preg_replace('/GB$/i','',$eq_resize_to);
		 $eq_resize_to = round($eq_resize_to * 1024);
		}
		if(preg_match('/MB$/i',$eq_resize_to)) {
		 $eq_resize_to = preg_replace('/MB$/i','',$eq_resize_to);
		 $eq_resize_to = round($eq_resize_to);
		}
		if($eq_resize_to <= $eq_resize_from) {
			$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage resize -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip -m $eq_resize_to";
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Error, downsizing (".$ci->disk_size." to ".$ci->disk_rsize.") unsupported: $image_resize_cmd", "", "", 0, 0, 0);
		} else {
			// For Equallogic we set a deployment parameter RESIZE_FS that is used by the root-mount script
			// to determine whether if we have to do a filesystem resize
			$image->set_deployment_parameters("RESIZE_FS", "TRUE");
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Setting RESIZE_FS parameter for image_id $ci_image_id", "", "", 0, 0, 0);
			// Update image object and vars
			$image->get_instance_by_id($ci_image_id);
			$image_deployment_parameter = $image->deployment_parameter;
			// Execute resize command on equallogic
			$image_resize_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage resize -n $equallogic_volume_name -u $eq_user -p $eq_password -e $eq_storage_ip -m $eq_resize_to";
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Running : $image_resize_cmd", "", "", 0, 0, 0);
			$output = shell_exec($image_resize_cmd);
		}
	}
}



// creates a private copy of the volume of an image
function create_private_equallogic($image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_equallogic", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Creating private image on storage", "", "", 0, 0, 0);

	// get image
	$image = new image();
	$image->get_instance_by_id($image_id);
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
	// equallogic-storage
	$equallogic_volume_name=basename($image_rootdevice);
	// get the password for the equallogic-filer
	$eq_storage = new equallogic_storage();
	$eq_storage->get_instance_by_storage_id($storage->id);
	if (!strlen($eq_storage->storage_id)) {
		$strMsg = "Equallogic Storage server $storage->id not configured yet<br>";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook", $strMsg, "", "", 0, 0, 0);
	} else {
		// we need to special take care that the volume name does not contain special characters
		$private_image_lun_name = preg_replace('/_/',"-", $private_image_name);
		// and that it does not exceed 16 chars for EQEMU (SCST based limit, real EQ does not care about lun length)
		if(preg_match("/emulator/i",$eq_storage->storage_comment)) {
			$event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "equallogic-cloud-hook","Equallogic emulator detected, trimming LUNs to 16 characters", "", "", 0, 0, 0);
			$private_image_lun_name = preg_replace('/private/','pvt',$private_image_lun_name);
			$private_image_lun_name = substr($private_image_lun_name,0,11).rand(10000,99999);
		}
		$eq_storage_ip = $resource_ip;
		$eq_user = $eq_storage->storage_user;
		$eq_password = $eq_storage->storage_password;
		$eq_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/equallogic-storage/bin/openqrm-equallogic-storage clone -n $equallogic_volume_name -u $eq_user -p $eq_password -ou $openqrm_admin_user->name -op $openqrm_admin_user->password -e $eq_storage_ip -s $private_image_lun_name -m $private_disk -ci $private_image_name";
		$event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "equallogic-cloud-hook", "Running : $eq_clone_cmd", "", "", 0, 0, 0);
		$output = shell_exec($eq_clone_cmd);
		// set the storage specific image root_device parameter
		$new_rootdevice = preg_replace('#'.$equallogic_volume_name.'$#', $private_image_lun_name, $image->rootdevice);
		return $new_rootdevice;
	}
}



// ---------------------------------------------------------------------------------


?>