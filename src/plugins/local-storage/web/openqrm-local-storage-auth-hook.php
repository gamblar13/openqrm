<?php
/**
 * @package openQRM
 */
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
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";

/**
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $IMAGE_AUTHENTICATION_TABLE;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



	//--------------------------------------------------
	/**
	* authenticates the storage volume for the appliance resource
	* <code>
	* storage_auth_function("start", 2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_function($cmd, $appliance_id) {
		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;
		global $IMAGE_AUTHENTICATION_TABLE;
		global $openqrm_server;
		global $RootDir;

		switch($cmd) {
			case "start":

			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_id);
			$image = new image();
			$image->get_instance_by_id($appliance->imageid);
			// we just need to remove the authblocker here
			$authblocker = new authblocker();
			$authblocker->get_instance_by_image_id($image->id);
			if (strlen($authblocker->id)) {
				$event->log('storage_auth_function', $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-auth-hook.php", "Removing authblocker for image ".$image->name.".", "", "", 0, 0, 0);
				$authblocker->remove($authblocker->id);
			}
			break;
		}
	}



	//--------------------------------------------------
	/**
	* de-authenticates the storage volume for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_stop($image_id) {

		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;
	}



	//--------------------------------------------------
	/**
	* de-authenticates the storage deployment volumes for the appliance resource
	* (runs via the image_authentication class)
	* <code>
	* storage_auth_deployment_stop(2);
	* </code>
	* @access public
	*/
	//--------------------------------------------------
	function storage_auth_deployment_stop($image_id) {

		global $event;
		global $OPENQRM_SERVER_BASE_DIR;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $OPENQRM_EXEC_PORT;

		$image = new image();
		$image->get_instance_by_id($image_id);
		$image_name=$image->name;
		$image_rootdevice=$image->rootdevice;

		$storage = new storage();
		$storage->get_instance_by_id($image->storageid);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$storage_ip = $storage_resource->ip;

		$deployment = new deployment();
		$deployment->get_instance_by_type($image->type);
		$deployment_type = $deployment->type;
		$deployment_plugin_name = $deployment->storagetype;

		// just for sending the commands
		$resource = new resource();



		// TODO : get the auto-install template from the INSTALL_CONFIG deployment parameters
		// the image rootfs is the local disk!
		$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-auth-hook.php", "Local-Deployment: De-authenticating the template storage export !!! TODO !!", "", "", 0, 0, $resource_id);
		return;


		// get install deployment params
		$install_from_nfs_param = trim($image->get_deployment_parameter("IMAGE_INSTALL_FROM_NFS"));
		if (strlen($install_from_nfs_param)) {
			// storage -> resource -> auth
			$ip_storage_id=$deployment->parse_deployment_parameter("id", $install_from_nfs_param);
			$ip_storage_ip=$deployment->parse_deployment_parameter("ip", $install_from_nfs_param);
			$ip_image_rootdevice=$deployment->parse_deployment_parameter("path", $install_from_nfs_param);

			$ip_storage = new storage();
			$ip_storage->get_instance_by_id($ip_storage_id);
			$ip_storage_resource = new resource();
			$ip_storage_resource->get_instance_by_id($ip_storage->resource_id);
			$op_storage_ip = $ip_storage_resource->ip;

			$ip_deployment = new deployment();
			$ip_deployment->get_instance_by_id($ip_storage->type);
			$ip_deployment_type = $ip_deployment->type;
			$ip_deployment_plugin_name = $ip_deployment->storagetype;

			$event->log("storage_auth_function", $_SERVER['REQUEST_TIME'], 5, "openqrm-local-storage-auth-hook.php", "Install-from-NFS: Authenticating $resource_ip on storage id $ip_storage_id:$ip_storage_ip:$ip_image_rootdevice", "", "", 0, 0, $resource_id);
			$auth_install_from_nfs_start_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/$ip_deployment_plugin_name/bin/openqrm-$ip_deployment_plugin_name auth -r $ip_image_rootdevice -i $OPENQRM_SERVER_IP_ADDRESS -t $ip_deployment_type";
			$resource->send_command($ip_storage_ip, $auth_install_from_nfs_start_cmd);
		}

	}



?>


