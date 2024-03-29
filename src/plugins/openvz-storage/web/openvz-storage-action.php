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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/authblocker.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

global $IMAGE_INFO_TABLE;
global $DEPLOYMENT_INFO_TABLE;
global $OPENQRM_SERVER_BASE_DIR;

// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/openvz-storage/storage';
// place for the openvz_server stat files
$LxcDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/openvz-storage/openvz-stat';
// get params
$lvm_storage_command = htmlobject_request('lvm_storage_command');
$openvz_server_command = htmlobject_request('openvz_server_command');
$openvz_server_id = htmlobject_request('openvz_server_id');
if (!strlen($lvm_storage_command)) {
	$lvm_storage_command = $openvz_server_command;
}
// for remove authblocker
$openvz_storage_image_name = htmlobject_request('openvz_storage_image_name');
global $openvz_storage_image_name;

// get event + openQRM server
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "lvm-action", "Un-Authorized access to lvm-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}


$event->log("$lvm_storage_command", $_SERVER['REQUEST_TIME'], 5, "openvz-storage-action", "Processing openvz-storage command $lvm_storage_command", "", "", 0, 0, 0);
switch ($lvm_storage_command) {
	// storage commands
	case 'get_storage':
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

	case 'get_ident':
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

	case 'clone_finished':
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

	case 'get_sync_progress':
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

	case 'get_sync_finished':
		if (!file_exists($StorageDir)) {
			mkdir($StorageDir);
		}
		$filename = $StorageDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		sleep(5);
		unlink($filename);
		break;

	case 'auth_finished':
		// remove storage-auth-blocker if existing
		$authblocker = new authblocker();
		$authblocker->get_instance_by_image_name($openvz_storage_image_name);
		if (strlen($authblocker->id)) {
			$event->log("$lvm_storage_command", $_SERVER['REQUEST_TIME'], 5, "openvz-storage-action", "Removing authblocker for image $openvz_storage_image_name", "", "", 0, 0, 0);
			$authblocker->remove($authblocker->id);
		}
		break;

	// vm commands
	// get the incoming vm list
	case 'get_openvz_server':
		if (!file_exists($LxcDir)) {
			mkdir($LxcDir);
		}
		$filename = $LxcDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm list
	case 'refresh_vm_list':
		$openvz_appliance = new appliance();
		$openvz_appliance->get_instance_by_id($openvz_server_id);
		$openvz_server = new resource();
		$openvz_server->get_instance_by_id($openvz_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$openvz_server->send_command($openvz_server->ip, $resource_command);
		break;

	// get the incoming vm config
	case 'get_openvz_config':
		if (!file_exists($LxcDir)) {
			mkdir($LxcDir);
		}
		$filename = $LxcDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// send command to send the vm config
	case 'refresh_vm_config':
		$openvz_appliance = new appliance();
		$openvz_appliance->get_instance_by_id($openvz_server_id);
		$openvz_server = new resource();
		$openvz_server->get_instance_by_id($openvz_appliance->resources);
		$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_config -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
		$openvz_server->send_command($openvz_server->ip, $resource_command);
		break;

	// get the incoming bridge config
	case 'get_bridge_config':
		if (!file_exists($LxcDir)) {
			mkdir($LxcDir);
		}
		$filename = $LxcDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// get the incoming vm config
	case 'get_openvz_net_config':
		if (!file_exists($LxcDir)) {
			mkdir($LxcDir);
		}
		$filename = $LxcDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;

	// get VM migration status
	case 'get_vm_migration':
		if (!file_exists($LxcDir)) {
			mkdir($LxcDir);
		}
		$filename = $LxcDir."/".$_POST['filename'];
		$filedata = base64_decode($_POST['filedata']);
		echo "<h1>$filename</h1>";
		$fout = fopen($filename,"wb");
		fwrite($fout, $filedata);
		fclose($fout);
		break;



	case 'init':
		// this command creates the following table
		// -> openvz_templates
		// openvz_template_id INT(5)
		// openvz_template_name VARCHAR(255)
		// openvz_template_description VARCHAR(255)
		$create_openvz_table = "create table openvz_templates(openvz_template_id BIGINT, openvz_template_name VARCHAR(255), openvz_template_description VARCHAR(255))";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($create_openvz_table);

		$db->Close();
		break;

	case 'uninstall':
		$drop_openvz_table = "drop table openvz_templates";
		$db=openqrm_get_db_connection();
		$recordSet = &$db->Execute($drop_openvz_table);
		$db->Close();
		break;


	default:
		$event->log("$lvm_storage_command", $_SERVER['REQUEST_TIME'], 3, "openvz-storage-action", "No such openvz-storage command ($lvm_storage_command)", "", "", 0, 0, 0);
		break;


}

?>
