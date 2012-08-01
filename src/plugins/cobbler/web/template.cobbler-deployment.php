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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $OPENQRM_SERVER_BASE_DIR;

// global event for logging
$event = new event();
global $event;

function wait_for_cobbler_profile_list($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
	$refresh_loop=0;
	while (!file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}

function get_cobbler_deployment_templates($local_storage_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;

	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/cobbler/storage';
	// get the cobbler-server resource
	$cobbler_storage = new storage();
	$cobbler_storage->get_instance_by_id($local_storage_storage_id);
	$cobbler_server_resource = new resource();
	$cobbler_server_resource->get_instance_by_id($cobbler_storage->resource_id);

	// remove statfile
	$template_list_file = $StorageDir."/".$cobbler_server_resource->id.".cobbler-profiles.list";
	if (file_exists($template_list_file)) {
		unlink($template_list_file);
	}
	$cobbler_server_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/cobbler/bin/openqrm-cobbler post_profiles -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
	$cobbler_server_resource->send_command($cobbler_server_resource->ip, $cobbler_server_command);
	sleep(2);
	if (!wait_for_cobbler_profile_list($template_list_file)) {
		$event->log("get_cobbler_deployment_templates", $_SERVER['REQUEST_TIME'], 2, "template.cobbler-deployment.php", "Timeout while requesting template identifier from storage id $cobbler_storage->id", "", "", 0, 0, 0);
		return;
	}
	$local_deployment_tepmplates_identifier_array = array();
	$fcontent = file($template_list_file);
	foreach($fcontent as $template_list_info) {
		$tpos = strpos($template_list_info, ",");
		$template_name = trim(substr($template_list_info, $tpos+1));
		$template_identifier = $template_name;
		$template_deployment_parameter = "cobbler-deployment:".$local_storage_storage_id.":".$template_identifier;
		$local_deployment_tepmplates_identifier_array[] = array("value" => "$template_deployment_parameter", "label" => "$template_name");
	}
	return $local_deployment_tepmplates_identifier_array;
}


function get_cobbler_deployment_methods() {
	$cobbler_deployment_methods_array = array("value" => "cobbler-deployment", "label" => "Automatic Linux Installation (Cobbler)");
	return $cobbler_deployment_methods_array;
}


function get_cobbler_deployment_additional_parameters() {
	// possible 4 additional inputs for addtional parameters
	$local_deployment_additional_parameters[] = '';
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Product-Key');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key2');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key3');
//	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Key4');
	return $local_deployment_additional_parameters;
}

?>


