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

    Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
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
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "hybrid-cloud-action", "Un-Authorized access to hybrid-cloud-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$hybrid_cloud_command = htmlobject_request('hybrid_cloud_command');

// main
$event->log("$hybrid_cloud_command", $_SERVER['REQUEST_TIME'], 5, "hybrid-cloud-action", "Processing hybrid-cloud command $hybrid_cloud_command", "", "", 0, 0, 0);
switch ($hybrid_cloud_command) {

    case 'init':
        // this command creates the following table
        // -> hybrid_cloud_accounts
        // hybrid_cloud_id INT(5)
        // hybrid_cloud_account_name VARCHAR(50)
        // hybrid_cloud_account_type VARCHAR(50)
        // hybrid_cloud_rc_config VARCHAR(255)
        // hybrid_cloud_ssh_key VARCHAR(255)
        // hybrid_cloud_description VARCHAR(255)
        $create_hybrid_cloud_table = "create table hybrid_cloud_accounts(hybrid_cloud_id INT(5), hybrid_cloud_account_name VARCHAR(50), hybrid_cloud_account_type VARCHAR(50), hybrid_cloud_rc_config VARCHAR(255), hybrid_cloud_ssh_key VARCHAR(255), hybrid_cloud_description VARCHAR(255))";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($create_hybrid_cloud_table);

        $db->Close();
        break;

    case 'uninstall':
        $drop_hybrid_cloud_table = "drop table hybrid_cloud_accounts";
        $db=openqrm_get_db_connection();
        $recordSet = &$db->Execute($drop_hybrid_cloud_table);
        $db->Close();
        break;


    default:
        $event->log("$hybrid_cloud_command", $_SERVER['REQUEST_TIME'], 3, "hybrid-cloud-action", "No such event command ($hybrid_cloud_command)", "", "", 0, 0, 0);
        break;


}

?>
