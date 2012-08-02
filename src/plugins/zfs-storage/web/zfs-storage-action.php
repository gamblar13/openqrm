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
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/zfs-storage/storage';
// global event for logging
$event = new event();

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "iscsi-action", "Un-Authorized access to iscsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$zfs_storage_command = htmlobject_request('zfs_storage_command');
$zfs_storage_id = htmlobject_request('zfs_storage_id');
$source_tab = htmlobject_request('source_tab');
$zfs_storage_name = htmlobject_request('zfs_storage_name');
$zfs_storage_image_size = htmlobject_request('zfs_storage_image_size');
$zfs_storage_image_name = htmlobject_request('zfs_storage_image_name');
$zfs_storage_image_snapshot_name = htmlobject_request('zfs_storage_image_snapshot_name');


$event->log("$zfs_storage_command", $_SERVER['REQUEST_TIME'], 5, "zfs-storage-action", "Processing zfs-storage command $zfs_storage_command", "", "", 0, 0, 0);
switch ($zfs_storage_command) {
	case 'get_luns':
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

	case 'get_zpools':
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

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($zfs_storage_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "zfs-storage-action", "Removing authblocker for image $zfs_storage_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;

	default:
		$event->log("$zfs_storage_command", $_SERVER['REQUEST_TIME'], 3, "zfs-storage-action", "No such zfs-storage command ($zfs_storage_command)", "", "", 0, 0, 0);
		break;


}

?>

</body>
