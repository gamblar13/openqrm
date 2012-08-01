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

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/fai/storage';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "fai-action", "Un-Authorized access to fai-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$fai_command = htmlobject_request('fai_command');

// main
$event->log("$fai_command", $_SERVER['REQUEST_TIME'], 5, "fai-action", "Processing fai command $fai_command", "", "", 0, 0, 0);
switch ($fai_command) {

	case 'init':
		// create fai_state
		// -> fai_state
		// fai_id BIGINT
		// fai_resource_id BIGINT
		// fai_install_start VARCHAR(20)
		// fai_timeout BIGINT
		$create_fai_state = "create table fai_state(fai_id BIGINT, fai_resource_id BIGINT, fai_install_start VARCHAR(20), fai_timeout BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_fai_state);
		// -> fai_volumes
		// fai_volume_id BIGINT
		// fai_volume_name VARCHAR(50)
		// fai_volume_size VARCHAR(50)
		// fai_volume_description VARCHAR(255)
		$create_fai_volume_table = "create table fai_volumes(fai_volume_id BIGINT, fai_volume_name VARCHAR(50), fai_volume_root VARCHAR(50), fai_volume_description VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_fai_volume_table);
		break;

	case 'uninstall':
		// remove fai_resource
		$remove_fai_state = "drop table fai_state;";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($remove_fai_state);
		// remove volume table
		$drop_fai_volume_table = "drop table fai_volumes";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_fai_volume_table);
		break;


	case 'get_profiles':
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



	default:
		$event->log("$fai_command", $_SERVER['REQUEST_TIME'], 3, "fai-action", "No such event command ($fai_command)", "", "", 0, 0, 0);
		break;


}

?>
