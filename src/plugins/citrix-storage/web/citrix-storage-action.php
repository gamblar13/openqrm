<html>
<head>
<title>openQRM Citrix-storage actions</title>
</head>
<body>

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


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;

// place for the citrix-storage stat files
$citrix_storage_dir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix-storage/citrix-storage-stat';
$citrix_storage_command = $_REQUEST["citrix_storage_command"];
$citrix_storage_id = $_REQUEST["citrix_storage_id"];
$citrix_storage_image_name = $_REQUEST["citrix_storage_image_name"];

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "citrix-storage-action", "Un-Authorized access to citrix-storage-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$citrix_storage_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "citrix_storage_", 4) == 0) {
		$citrix_storage_fields[$key] = $value;
	}
}


unset($citrix_storage_fields["citrix_storage_command"]);

$event->log("$citrix_storage_command", $_SERVER['REQUEST_TIME'], 5, "citrix-storage-action", "Processing citrix-storage command $citrix_storage_command", "", "", 0, 0, 0);
switch ($citrix_storage_command) {


	case 'get_storage':
		if (!file_exists($citrix_storage_dir)) {
			mkdir($citrix_storage_dir);
		}
		$filename = $citrix_storage_dir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'get_ident':
		if (!file_exists($citrix_storage_dir)) {
			mkdir($citrix_storage_dir);
		}
		$filename = $citrix_storage_dir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'clone_finished':
		if (!file_exists($citrix_storage_dir)) {
			mkdir($citrix_storage_dir);
		}
		$filename = $citrix_storage_dir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($citrix_storage_image_name);
		if (strlen($authblocker->id)) {
			$event->log('auth_finished', $_SERVER['REQUEST_TIME'], 5, "citrix-storage-action", "Removing authblocker for image $citrix_storage_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		echo '';
		break;


	default:
		$event->log("$citrix_storage_command", $_SERVER['REQUEST_TIME'], 3, "citrix-storage-action", "No such event command ($citrix_storage_command)", "", "", 0, 0, 0);
		break;


}
?>

</body>
