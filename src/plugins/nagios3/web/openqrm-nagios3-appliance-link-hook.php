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



function get_nagios3_appliance_link($appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	$p_resource = new resource();
	$p_resource->get_instance_by_id($p_appliance->resources);
	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		$plugin_link = "<a href='/cgi-bin/nagios3/status.cgi?host=".$p_appliance->name."'><img title='Service monitoring' alt='Service monitoring' height='24' width='24' src='/openqrm/base/plugins/nagios3/img/plugin.png' border=0></a>&nbsp;&nbsp;";
	}
	if ($p_resource->id == 0) {
		$plugin_link = "<a href='/cgi-bin/nagios3/status.cgi?host=".$p_appliance->name."'><img title='Service monitoring' alt='Service monitoring' height='24' width='24' src='/openqrm/base/plugins/nagios3/img/plugin.png' border=0></a>&nbsp;&nbsp;";
	}
	if ($p_resource->id == '') {
		$plugin_link = "";
	}

	return $plugin_link;
}


?>


