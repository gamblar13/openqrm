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
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/cobbler/storage';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "cobbler-action", "Un-Authorized access to cobbler-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$cobbler_command = htmlobject_request('cobbler_command');

// main
$event->log("$cobbler_command", $_SERVER['REQUEST_TIME'], 5, "cobbler-action", "Processing cobbler command $cobbler_command", "", "", 0, 0, 0);
switch ($cobbler_command) {


	case 'init':
		// create cobbler_state
		// -> cobbler_state
		// cobbler_id BIGINT
		// cobbler_resource_id BIGINT
		// cobbler_install_start VARCHAR(20)
		// cobbler_timeout BIGINT
		$create_cobbler_state = "create table cobbler_state(cobbler_id BIGINT, cobbler_resource_id BIGINT, cobbler_install_start VARCHAR(20), cobbler_timeout BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_cobbler_state);
		// -> cobbler_volumes
		// cobbler_volume_id BIGINT
		// cobbler_volume_name VARCHAR(50)
		// cobbler_volume_size VARCHAR(50)
		// cobbler_volume_description VARCHAR(255)
		$create_cobbler_volume_table = "create table cobbler_volumes(cobbler_volume_id BIGINT, cobbler_volume_name VARCHAR(50), cobbler_volume_root VARCHAR(50), cobbler_volume_description VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_cobbler_volume_table);

		break;

	case 'uninstall':
		// remove cobbler_resource
		$remove_cobbler_state = "drop table cobbler_state;";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($remove_cobbler_state);
		// remove volume table
		$drop_cobbler_volume_table = "drop table cobbler_volumes";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_cobbler_volume_table);
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
		$event->log("$cobbler_command", $_SERVER['REQUEST_TIME'], 3, "cobbler-action", "No such event command ($cobbler_command)", "", "", 0, 0, 0);
		break;


}

?>
