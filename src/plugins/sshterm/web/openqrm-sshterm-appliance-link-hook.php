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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function get_sshterm_appliance_link($appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$p_resource = new resource();
	$p_resource->get_instance_by_id($p_appliance->resources);
	// get the parameters from the plugin config file
	$OPENQRM_PLUGIN_SSHTERM_CONFIG_FILE="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sshterm/etc/openqrm-plugin-sshterm.conf";
	$store = openqrm_parse_conf($OPENQRM_PLUGIN_SSHTERM_CONFIG_FILE);
	extract($store);
	$sshterm_login_ip = $p_resource->ip;
	$sshterm_login_url="https://$sshterm_login_ip:$OPENQRM_PLUGIN_WEBSHELL_PORT";
	$info_ssh_login = "<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('$sshterm_login_url','','location=0,status=0,scrollbars=1,width=580,height=420,left=400,top=100,screenX=400,screenY=100');\">
		<image border=\"0\" height=\"24\" width=\"24\" alt=\"SSH-Login to ".$p_appliance->name."\" title=\"SSH-Login to ".$p_appliance->name."\" src=\"/openqrm/base/plugins/sshterm/img/login.png\">
		</a>";

	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		$plugin_link = $info_ssh_login;
	}
	if ($p_resource->id == 0) {
		$plugin_link = $info_ssh_login;
	}
	if ($p_resource->id == '') {
		$plugin_link = "";
	}

	return $plugin_link;
}

?>

