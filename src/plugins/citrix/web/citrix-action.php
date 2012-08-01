<html>
<head>
<title>openQRM Citrix actions</title>
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

// place for the citrix stat files
$CitrixDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix/citrix-stat';

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "citrix-action", "Un-Authorized access to citrix-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$citrix_command = $_REQUEST["citrix_command"];
$citrix_uuid = $_REQUEST["citrix_uuid"];
$citrix_name = $_REQUEST["citrix_name"];
$citrix_ram = $_REQUEST["citrix_ram"];
$citrix_id = $_REQUEST["citrix_id"];
$citrix_server_passwd = $_REQUEST["citrix_server_passwd"];
$citrix_server_user = $_REQUEST["citrix_server_user"];

$citrix_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "citrix_", 4) == 0) {
		$citrix_fields[$key] = $value;
	}
}



unset($citrix_fields["citrix_command"]);

$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 5, "citrix-action", "Processing citrix command $citrix_command", "", "", 0, 0, 0);
switch ($citrix_command) {

	case 'init':
		// this command creates the following table
		// -> citrix_auto_discovery
		// xenserver_ad_id BIGINT(5)
		// xenserver_ad_ip VARCHAR(50)
		// xenserver_ad_mac VARCHAR(50)
		// xenserver_ad_hostname VARCHAR(50)
		// xenserver_ad_user VARCHAR(50)
		// xenserver_ad_password VARCHAR(50)
		// xenserver_ad_comment VARCHAR(255)
		// xenserver_ad_is_integrated BIGINT

		$create_auto_discovery_table = "create table citrix_auto_discovery(xenserver_ad_id BIGINT, xenserver_ad_ip VARCHAR(255), xenserver_ad_mac VARCHAR(50), xenserver_ad_hostname VARCHAR(50), xenserver_ad_user VARCHAR(50), xenserver_ad_password VARCHAR(50), xenserver_ad_comment VARCHAR(255), xenserver_ad_is_integrated BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_auto_discovery_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_auto_discovery_table = "drop table citrix_auto_discovery";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_auto_discovery_table);
		$db->Close();
		break;



	default:
		$event->log("$citrix_command", $_SERVER['REQUEST_TIME'], 3, "citrix-action", "No such event command ($citrix_command)", "", "", 0, 0, 0);
		break;


}
?>

</body>
