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


$netapp_storage_command = $_REQUEST["netapp_storage_command"];

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
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
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/netapp-storage/storage';
$netapp_image_name = htmlobject_request('netapp_image_name');

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "netapp-storage-action", "Un-Authorized access to netapp-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Processing netapp-storage command $netapp_storage_command", "", "", 0, 0, 0);
if (!file_exists($StorageDir)) {
	mkdir($StorageDir);
}

// main actions
switch ($netapp_storage_command) {

	case 'init':
		// this command creates the following tables
		// -> netapp_storage_servers
		// na_id INT(5)
		// na_storage_id INT(5)
		// na_storage_name VARCHAR(20)
		// na_storage_user VARCHAR(20)
		// na_storage_password VARCHAR(20)
		// na_storage_comment VARCHAR(50)
		//
		$create_netapp_storage_config = "create table netapp_storage_servers(na_id INT(5), na_storage_id INT(5), na_storage_name VARCHAR(20), na_storage_user VARCHAR(20), na_storage_password VARCHAR(20), na_storage_comment VARCHAR(50))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_netapp_storage_config);
		$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Initialyzed NetApp-storage Server table", "", "", 0, 0, 0);
		$db->Close();
		break;

	case 'uninstall':
		$drop_netapp_storage_config = "drop table netapp_storage_servers";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_netapp_storage_config);
		$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Uninstalled NetApp-storage Server table", "", "", 0, 0, 0);
		$db->Close();
		break;

	case 'get_ident':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
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

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($netapp_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "netapp-storage-action", "Removing authblocker for image $netapp_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;


	default:
		$event->log("$netapp_storage_command", $_SERVER['REQUEST_TIME'], 3, "netapp-storage-action", "No such netapp-storage command ($netapp_storage_command)", "", "", 0, 0, 0);
		break;
}
?>

