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
		// cobbler_id INT(5)
		// cobbler_resource_id INT(5)
		// cobbler_install_start VARCHAR(20)
		// cobbler_timeout INT(5)
		$create_cobbler_state = "create table cobbler_state(cobbler_id INT(5), cobbler_resource_id INT(5), cobbler_install_start VARCHAR(20), cobbler_timeout INT(5))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_cobbler_state);
		break;

	case 'uninstall':
		// remove cobbler_resource
		$remove_cobbler_state = "drop table cobbler_state;";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($remove_cobbler_state);
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
