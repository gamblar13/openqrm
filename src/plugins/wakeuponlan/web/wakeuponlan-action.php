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

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "wakeuponlan-action", "Un-Authorized access to wakeuponlan-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$wakeuponlan_command = htmlobject_request('wakeuponlan_command');

// main
$event->log("$wakeuponlan_command", $_SERVER['REQUEST_TIME'], 5, "wakeuponlan-action", "Processing wakeuponlan command $wakeuponlan_command", "", "", 0, 0, 0);
switch ($wakeuponlan_command) {

	case 'init':
		// this command creates the following table
		// -> wakeuponlan_locations
		// wakeuponlan_id INT(5)
		// wakeuponlan_token VARCHAR(50)
		// wakeuponlan_name VARCHAR(50)
		// wakeuponlan_user_id INT(5)
		// wakeuponlan_appliance_id INT(5)
		// wakeuponlan_nic_id INT(5)
		// wakeuponlan_state INT(5)
		// wakeuponlan_network VARCHAR(50)
		// wakeuponlan_address VARCHAR(50)
		// wakeuponlan_subnet VARCHAR(50)
		// wakeuponlan_broadcast VARCHAR(50)
		// wakeuponlan_gateway VARCHAR(50)
		// wakeuponlan_dns1 VARCHAR(50)
		// wakeuponlan_dns2 VARCHAR(50)
		// wakeuponlan_domain VARCHAR(255)
		// wakeuponlan_vlan_id VARCHAR(50)
		// wakeuponlan_vlan1 VARCHAR(50)
		// wakeuponlan_vlan2 VARCHAR(50)
		// wakeuponlan_vlan3 VARCHAR(50)
		// wakeuponlan_vlan4 VARCHAR(50)
		// wakeuponlan_comment VARCHAR(255)

		$create_wakeuponlan_table = "create table wakeuponlan(wakeuponlan_id INT(5), wakeuponlan_token VARCHAR(255), wakeuponlan_name VARCHAR(50), wakeuponlan_user_id INT(5), wakeuponlan_appliance_id INT(5), wakeuponlan_nic_id INT(5), wakeuponlan_state INT(5), wakeuponlan_network VARCHAR(50), wakeuponlan_address VARCHAR(50), wakeuponlan_subnet VARCHAR(50), wakeuponlan_broadcast VARCHAR(50), wakeuponlan_gateway VARCHAR(50), wakeuponlan_dns1 VARCHAR(50), wakeuponlan_dns2 VARCHAR(50), wakeuponlan_domain VARCHAR(255), wakeuponlan_vlan_id VARCHAR(50), wakeuponlan_vlan1 VARCHAR(50), wakeuponlan_vlan2 VARCHAR(50), wakeuponlan_vlan3 VARCHAR(50), wakeuponlan_vlan4 VARCHAR(50), wakeuponlan_comment VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_wakeuponlan_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_wakeuponlan_table = "drop table wakeuponlan";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_wakeuponlan_table);
		$db->Close();
		break;


	default:
		$event->log("$wakeuponlan_command", $_SERVER['REQUEST_TIME'], 3, "wakeuponlan-action", "No such event command ($wakeuponlan_command)", "", "", 0, 0, 0);
		break;


}

?>
