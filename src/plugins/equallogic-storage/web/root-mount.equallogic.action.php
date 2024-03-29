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


$equallogic_root_mount_command = $_REQUEST["equallogic_root_mount_command"];
$equallogic_root_mount_image_id = $_REQUEST["equallogic_root_mount_image_id"];

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$EquallogicDir = $_SERVER["DOCUMENT_ROOT"].'/equallogic-storage-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special equallogic-storage classes
require_once "$RootDir/plugins/equallogic-storage/class/equallogic-storage-server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// main
$event->log("$equallogic_root_mount_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-root-mount-action", "Processing equallogic-storage command $equallogic_root_mount_command", "", "", 0, 0, 0);

	switch ($equallogic_root_mount_command) {

		case 'create_fs_finished':
			$event->log("$equallogic_root_mount_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-root-mount-action", "Removing CREATE_FS parameter for image_id $equallogic_root_mount_image_id", "", "", 0, 0, 0);
			$image = new image();
			$image->get_instance_by_id($equallogic_root_mount_image_id);
			$image->set_deployment_parameters("CREATE_FS", "FALSE");
			break;

		case 'resize_fs_finished':
			$event->log("$equallogic_root_mount_command", $_SERVER['REQUEST_TIME'], 5, "equallogic-root-mount-action", "Removing RESIZE_FS parameter for image_id $equallogic_root_mount_image_id", "", "", 0, 0, 0);
			$image = new image();
			$image->get_instance_by_id($equallogic_root_mount_image_id);
			$image->set_deployment_parameters("RESIZE_FS", "FALSE");
			break;


		default:
			$event->log("$equallogic_root_mount_command", $_SERVER['REQUEST_TIME'], 3, "equallogic-root-mount-action", "No such command ($equallogic_root_mount_command)", "", "", 0, 0, 0);
			break;

	}






?>
