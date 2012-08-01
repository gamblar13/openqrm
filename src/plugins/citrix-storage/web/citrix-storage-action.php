<html>
<head>
<title>openQRM Citrix-storage actions</title>
</head>
<body>

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

	case 'init':
		// this command creates the following table
		// -> xenserver_auto_discovery
		// xenserver_ad_id BIGINT(5)
		// xenserver_ad_ip VARCHAR(50)
		// xenserver_ad_mac VARCHAR(50)
		// xenserver_ad_hostname VARCHAR(50)
		// xenserver_ad_user VARCHAR(50)
		// xenserver_ad_password VARCHAR(50)
		// xenserver_ad_comment VARCHAR(255)
		// xenserver_ad_is_integrated BIGINT

		$create_auto_discovery_table = "create table xenserver_auto_discovery(xenserver_ad_id BIGINT, xenserver_ad_ip VARCHAR(255), xenserver_ad_mac VARCHAR(50), xenserver_ad_hostname VARCHAR(50), xenserver_ad_user VARCHAR(50), xenserver_ad_password VARCHAR(50), xenserver_ad_comment VARCHAR(255), xenserver_ad_is_integrated BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_auto_discovery_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_auto_discovery_table = "drop table xenserver_auto_discovery";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_auto_discovery_table);
		$db->Close();
		break;



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
