<?php
$vmware_esx_command = $_REQUEST["vmware_esx_command"];
$vmware_esx_id = $_REQUEST["vmware_esx_id"];
?>

<html>
<head>
<title>openQRM VMware-server actions</title>
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

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "vmware-esx-action", "Un-Authorized access to vmware-esx-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

$vmware_esx_name = $_REQUEST["vmware_esx_name"];
$vmware_esx_mac = $_REQUEST["vmware_esx_mac"];
$vmware_esx_ip = $_REQUEST["vmware_esx_ip"];
$vmware_esx_ram = $_REQUEST["vmware_esx_ram"];
$vmware_esx_disk = $_REQUEST["vmware_esx_disk"];

$vmware_esx_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "vmware_esx_", 14) == 0) {
		$vmware_esx_fields[$key] = $value;
	}
}
unset($vmware_esx_fields["vmware_esx_command"]);

	$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-action", "Processing command $vmware_esx_command", "", "", 0, 0, 0);
	switch ($vmware_esx_command) {

	case 'init':
		// this command creates the following table
		// -> vmw_esx_auto_discovery
		// vmw_esx_ad_id BIGINT(5)
		// vmw_esx_ad_ip VARCHAR(50)
		// vmw_esx_ad_mac VARCHAR(50)
		// vmw_esx_ad_hostname VARCHAR(50)
		// vmw_esx_ad_user VARCHAR(50)
		// vmw_esx_ad_password VARCHAR(50)
		// vmw_esx_ad_comment VARCHAR(255)
		// vmw_esx_ad_is_integrated BIGINT

		$create_vmw_auto_discovery_table = "create table vmw_esx_auto_discovery(vmw_esx_ad_id BIGINT, vmw_esx_ad_ip VARCHAR(255), vmw_esx_ad_mac VARCHAR(50), vmw_esx_ad_hostname VARCHAR(50), vmw_esx_ad_user VARCHAR(50), vmw_esx_ad_password VARCHAR(50), vmw_esx_ad_comment VARCHAR(255), vmw_esx_ad_is_integrated BIGINT)";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_vmw_auto_discovery_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_vmw_auto_discovery_table = "drop table vmw_esx_auto_discovery";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_vmw_auto_discovery_table);
		$db->Close();
		break;




		default:
			$event->log("$vmware_esx_command", $_SERVER['REQUEST_TIME'], 3, "vmware-esx-action", "No such vmware-esx command ($vmware_esx_command)", "", "", 0, 0, 0);
			break;


	}
?>

</body>
