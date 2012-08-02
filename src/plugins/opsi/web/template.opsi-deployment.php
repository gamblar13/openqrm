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

function wait_for_opsi_netboot_product_list($sfile) {
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

function get_opsi_deployment_templates($local_storage_storage_id) {
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_ADMIN;
	global $event;

	$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/opsi/storage';

	// get domain name from dns plugin
	$dns_plugin_conf_file=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/dns/etc/openqrm-plugin-dns.conf";
	if (!file_exists($dns_plugin_conf_file)) {
		$event->log("get_opsi_deployment_templates", $_SERVER['REQUEST_TIME'], 5, "template.opsi-deployment.php", "The Opsi-Plugin depends on the DNS-Plugin. Please enable and start the DNS-Plugin", "", "", 0, 0, $resource->id);
		return;
	}
	$store = openqrm_parse_conf($dns_plugin_conf_file);
	extract($store);
	$resource_domain = $store['OPENQRM_SERVER_DOMAIN'];
	if (!strlen($resource_domain)) {
		$event->log("get_opsi_deployment_templates", $_SERVER['REQUEST_TIME'], 5, "template.opsi-deployment.php", "Could not get Domain-Name from DNS-Plugin. Please configure the DNS-Plugin", "", "", 0, 0, $resource->id);
		return;
	}
	// get the opsi-server resource
	$opsi_storage = new storage();
	$opsi_storage->get_instance_by_id($local_storage_storage_id);
	$opsi_server_resource = new resource();
	$opsi_server_resource->get_instance_by_id($opsi_storage->resource_id);

	// remove statfile
	$template_list_file = $StorageDir."/".$opsi_server_resource->id.".opsi-netboot-products.list";
	if (file_exists($template_list_file)) {
		unlink($template_list_file);
	}
	$opsi_server_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/opsi/bin/openqrm-opsi post_netboot_products -d ".$resource_domain." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
	$opsi_server_resource->send_command($opsi_server_resource->ip, $opsi_server_command);
	sleep(2);
	if (!wait_for_opsi_netboot_product_list($template_list_file)) {
		$event->log("get_opsi_deployment_templates", $_SERVER['REQUEST_TIME'], 2, "template.opsi-deployment.php", "Timeout while requesting template identifier from storage id $opsi_storage->id", "", "", 0, 0, 0);
		return;
	}
	$local_deployment_tepmplates_identifier_array = array();
	$fcontent = file($template_list_file);
	foreach($fcontent as $template_list_info) {
		$tpos = strpos($template_list_info, ",");
		$template_name = trim(substr($template_list_info, $tpos+1));
		$template_identifier = $template_name;
		$template_deployment_parameter = "opsi-deployment:".$local_storage_storage_id.":".$template_identifier;
		$local_deployment_tepmplates_identifier_array[] = array("value" => "$template_deployment_parameter", "label" => "$template_name");
	}
	return $local_deployment_tepmplates_identifier_array;
}


function get_opsi_deployment_methods() {
	$opsi_deployment_methods_array = array("value" => "opsi-deployment", "label" => "Automatic Windows Installation");
	return $opsi_deployment_methods_array;
}


function get_opsi_deployment_additional_parameters() {
	$local_deployment_additional_parameters[] = array("value" => "", "label" => 'Product-Key');
	return $local_deployment_additional_parameters;
}

?>


