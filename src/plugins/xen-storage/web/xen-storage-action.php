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



$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/xen-storage/storage';
// place for the xen_server stat files
$XenDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/xen-storage/xen-stat';
// get params
$xen_storage_command = htmlobject_request('xen_storage_command');
$xen_server_command = htmlobject_request('xen_server_command');
$xen_server_id = htmlobject_request('xen_server_id');
if (!strlen($xen_storage_command)) {
	$xen_storage_command = $xen_server_command;
}
$xen_storage_image_name = htmlobject_request('xen_storage_image_name');


// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "lvm-action", "Un-Authorized access to lvm-actions from $OPENQRM_ADMIN->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$xen_storage_command", $_SERVER['REQUEST_TIME'], 5, "xen-storage-action", "Processing xen-storage command $xen_storage_command", "", "", 0, 0, 0);
switch ($xen_storage_command) {
	// storage commands
	case 'get_storage':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_ident':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'clone_finished':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_sync_progress':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_sync_finished':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		sleep(5);
		unlink($filename);
		break;

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($xen_storage_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "xen-storage-action", "Removing authblocker for image $xen_storage_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;

	// vm commands
	// get the incoming vm list
	case 'get_xen':
		if (!file_exists($XenDir)) {
			mkdir($XenDir);
		}
		$filename = $XenDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm list
	case 'refresh_vm_list':
		$xen_appliance = new appliance();
		$xen_appliance->get_instance_by_id($xen_server_id);
		$xen_server = new resource();
		$xen_server->get_instance_by_id($xen_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$xen_server->send_command($xen_server->ip, $resource_command);
		break;

	// get the incoming vm config
	case 'get_xen_config':
		if (!file_exists($XenDir)) {
			mkdir($XenDir);
		}
		$filename = $XenDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm config
	case 'refresh_vm_config':
		$xen_appliance = new appliance();
		$xen_appliance->get_instance_by_id($xen_server_id);
		$xen_server = new resource();
		$xen_server->get_instance_by_id($xen_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/xen-storage/bin/openqrm-xen-storage-vm post_vm_config -n $xen_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$xen_server->send_command($xen_server->ip, $resource_command);
		break;

	// get the incoming bridge config
	case 'get_bridge_config':
		if (!file_exists($XenDir)) {
			mkdir($XenDir);
		}
		$filename = $XenDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// get VM migration status
	case 'get_vm_migration':
		if (!file_exists($XenDir)) {
			mkdir($XenDir);
		}
		$filename = $XenDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

			// get pick_iso config
	case 'get_pick_iso_config':
		if (!file_exists($XenDir)) {
			mkdir($XenDir);
		}
		$filename = $XenDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;
		

	default:
		$event->log("$xen_storage_command", $_SERVER['REQUEST_TIME'], 3, "xen-storage-action", "No such xen-storage command ($xen_storage_command)", "", "", 0, 0, 0);
		break;


}

?>
