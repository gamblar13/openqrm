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

	Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
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
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/opsi/storage';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "opsi-action", "Un-Authorized access to opsi-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$opsi_command = htmlobject_request('opsi_command');

// main
$event->log("$opsi_command", $_SERVER['REQUEST_TIME'], 5, "opsi-action", "Processing opsi command $opsi_command", "", "", 0, 0, 0);
switch ($opsi_command) {

	case 'init':
		// create opsi_state
		// -> opsi_state
		// opsi_id INT(5)
		// opsi_resource_id INT(5)
		// opsi_install_start VARCHAR(20)
		// opsi_timeout INT(5)
		$create_opsi_state = "create table opsi_state(opsi_id INT(5), opsi_resource_id INT(5), opsi_install_start VARCHAR(20), opsi_timeout INT(5))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_opsi_state);
		break;

	case 'uninstall':
		// remove opsi_resource
		$remove_opsi_state = "drop table opsi_state;";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($remove_opsi_state);
		break;



	case 'get_netboot_products':
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
		$event->log("$opsi_command", $_SERVER['REQUEST_TIME'], 3, "opsi-action", "No such event command ($opsi_command)", "", "", 0, 0, 0);
		break;


}

?>
