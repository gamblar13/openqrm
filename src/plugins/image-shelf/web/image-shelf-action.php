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
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "image-shelf-action", "Un-Authorized access to image-shelf-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}
// get command
$image_shelf_command = htmlobject_request('image_shelf_command');

// main
$event->log("$image_shelf_command", $_SERVER['REQUEST_TIME'], 5, "image-shelf-action", "Processing image-shelf command $image_shelf_command", "", "", 0, 0, 0);
switch ($image_shelf_command) {

	case 'init':
		// this command creates the following table
		// -> image_shelf_locations
		// imageshelf_id BIGINT
		// imageshelf_name VARCHAR(20)
		// imageshelf_username VARCHAR(20)
		// imageshelf_protocol VARCHAR(20)
		// imageshelf_uri VARCHAR(255)
		// imageshelf_user VARCHAR(20)
		// imageshelf_password VARCHAR(20)

		$create_image_shelf_locations = "create table image_shelf_locations(imageshelf_id BIGINT, imageshelf_name VARCHAR(20), imageshelf_username VARCHAR(20), imageshelf_protocol VARCHAR(20), imageshelf_uri VARCHAR(255), imageshelf_user VARCHAR(20), imageshelf_password VARCHAR(20))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_image_shelf_locations);

		// create the default configuration
		$create_default_image_shelf_config = "insert into image_shelf_locations(imageshelf_id, imageshelf_name, imageshelf_username, imageshelf_protocol, imageshelf_uri, imageshelf_user, imageshelf_password) values (1, 'openqrm-enterprise', 'openqrm', 'http', 'http://image-shelf.openqrm-enterprise.org', '', '')";
		$recordSet = &$db->Execute($create_default_image_shelf_config);

		$db->Close();
		break;

	case 'uninstall':
		$drop_image_shelf_locations = "drop table image_shelf_locations";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_image_shelf_locations);
		$db->Close();
		break;


	default:
		$event->log("$image_shelf_command", $_SERVER['REQUEST_TIME'], 3, "image-shelf-action", "No such event command ($image_shelf_command)", "", "", 0, 0, 0);
		break;


}

?>
