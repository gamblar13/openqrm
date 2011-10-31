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

function wait_for_linuxcoe_netboot_product_list($sfile) {
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

function get_linuxcoe_deployment_templates($local_storage_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;
	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/linuxcoe/profiles';
	$local_deployment_tepmplates_identifier_array = array();
	if ($handle = opendir($StorageDir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$openqrm_profile_info = "";
				$openqrm_info_file = $StorageDir."/".$file."/openqrm.info";
				if (file_exists($openqrm_info_file)) {
					$openqrm_profile_info = file_get_contents($openqrm_info_file);
				} else {
					$openqrm_profile_info = file_get_contents($file);
				}
				$openqrm_install_parameter = "linuxcoe-deployment:0:".$file;
				$local_deployment_tepmplates_identifier_array[] = array("value" => $openqrm_install_parameter, "label" => $openqrm_profile_info);
			}
		}
		closedir($handle);
	}
	return $local_deployment_tepmplates_identifier_array;
}


function get_linuxcoe_deployment_methods() {
	$linuxcoe_deployment_methods_array = array("value" => "linuxcoe-deployment", "label" => "Automatic Linux Installation (LinuxCOE)");
	return $linuxcoe_deployment_methods_array;
}


function get_linuxcoe_deployment_additional_parameters() {
	$local_deployment_additional_parameters = "";
	$local_deployment_additional_parameters .= htmlobject_input('local_deployment_additional_parameter1', array("value" => "", "label" => 'Product-Key'), 'text', 40);
	return $local_deployment_additional_parameters;
}

?>

