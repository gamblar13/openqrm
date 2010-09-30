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
// special netapp-storage classes, only if enabled
$netapp_storage_class = "$RootDir/plugins/netapp-storage/class/netapp-storage-server.class.php";
require_once $netapp_storage_class;
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
function create_clone_netapp_deployment($cloud_image_id, $image_clone_name, $disk_size) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_clone", $_SERVER['REQUEST_TIME'], 5, "netapp-deployment-cloud-hook", "Creating clone of image on storage", "", "", 0, 0, 0);

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
    // netapp-storage
    $netapp_volume_name=basename($image_rootdevice);
    // we need to special take care that the volume name does not contain special characters
    $image_clone_name = str_replace(".", "", $image_clone_name);
    $image_clone_name = str_replace("_", "", $image_clone_name);
    $image_clone_name = str_replace("-", "", $image_clone_name);
    // and do not let the volume name start with a number
    $image_clone_name = "na".$image_clone_name;
    // get the password for the netapp-filer
    $na_storage = new netapp_storage();
    $na_storage->get_instance_by_storage_id($storage->id);
    if (!strlen($na_storage->storage_id)) {
        $strMsg = "NetApp Storage server $storage->id not configured yet<br>";
        $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "netapp-deployment-cloud-hook", $strMsg, "", "", 0, 0, 0);
    } else {
        // generate a new image password for the clone
        $image->get_instance_by_id($image_id);
        $image_password = $image->generatePassword(14);
        $image->set_deployment_parameters("IMAGE_ISCSI_AUTH", $image_password);
        $na_storage_ip = $resource_ip;
        $na_password = $na_storage->storage_password;
        // update the image rootdevice parameter
        $clone_image_root_device = str_replace($netapp_volume_name, $image_clone_name, $image_rootdevice);
        $ar_image_update = array(
            'image_rootdevice' => $clone_image_root_device,
        );
        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "netapp-deployment-cloud-hook", "Updating rootdevice of image $image_id / $image_name with $clone_image_root_device", "", "", 0, 0, 0);
        $image->update($image_id, $ar_image_update);
        // send command
        $image_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage snap -n $netapp_volume_name -s $image_clone_name -i $image_password -p $na_password -e $na_storage_ip";
        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "netapp-deployment-cloud-hook", "Running : $image_clone_cmd", "", "", 0, 0, 0);
        $output = shell_exec($image_clone_cmd);


    }
}



// removes the volume of an image
function remove_netapp_deployment($cloud_image_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("remove_netapp_deployment", $_SERVER['REQUEST_TIME'], 5, "netapp-deployment-cloud-hook", "Removing image on storage", "", "", 0, 0, 0);

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
    // netapp-storage
    $netapp_volume_name=basename($image_rootdevice);
    // get the password for the netapp-filer
    $na_storage = new netapp_storage();
    $na_storage->get_instance_by_storage_id($storage->id);
    if (!strlen($na_storage->storage_id)) {
        $strMsg = "NetApp Storage server $storage->id not configured yet<br>";
        $event->log("cloud", $_SERVER['REQUEST_TIME'], 2, "netapp-deployment-cloud-hook", $strMsg, "", "", 0, 0, 0);
    } else {
        $na_storage_ip = $resource_ip;
        $na_password = $na_storage->storage_password;
        $image_remove_clone_cmd="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/netapp-storage/bin/openqrm-netapp-storage remove -n $netapp_volume_name -p $na_password -e $na_storage_ip";
        $event->log("cloud", $_SERVER['REQUEST_TIME'], 5, "netapp-deployment-cloud-hook", "Running : $image_remove_clone_cmd", "", "", 0, 0, 0);
        $output = shell_exec($image_remove_clone_cmd);
    }
}


// resizes the volume of an image
function resize_netapp_deployment($cloud_image_id, $resize_value) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("resize_netapp_deployment", $_SERVER['REQUEST_TIME'], 2, "netapp-deployment-cloud-hook", "Resize image is not supported by netapp-storage", "", "", 0, 0, 0);
}



// creates a private copy of the volume of an image
function create_private_netapp_deployment($cloud_image_id, $private_disk, $private_image_name) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $RESOURCE_INFO_TABLE;
	global $event;
	$event->log("create_private_netapp_deployment", $_SERVER['REQUEST_TIME'], 2, "netapp-deployment-cloud-hook", "Creating private image is not supported by netapp-storage", "", "", 0, 0, 0);
}



// ---------------------------------------------------------------------------------


?>