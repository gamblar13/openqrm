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



function get_collectd_appliance_link($appliance_id) {
	global $event;
	global $RootDir;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$plugin_link='';
	$appliance_name='';

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);

	if ($p_appliance->resources != -1) {
		$appliance_name = $p_appliance->name;
	}
	if ($p_appliance->resources == 0) {
		$appliance_name = "openqrm";
	}
	// pending ??
	$icon_size = "";
	$graph_link = "";
	$icon_title = "";
	$graph_html = $RootDir."/plugins/collectd/graphs/".$appliance_name."/index.html";
	if (file_exists($graph_html)) {
		$collectd_statistics_icon = "/openqrm/base/plugins/collectd/img/plugin.png";
		$graph_link = "/openqrm/base/plugins/collectd/graphs/".$appliance_name."/index.html";
		$icon_title = "Collectd statistics";
	} else {
		$collectd_statistics_icon = "/openqrm/base/img/progress.gif";
		$icon_size = "width='25' height='25'";
		$graph_link = "";
		$icon_title = "Collectd statistics pending";
	}
	$plugin_link = "<a href=$graph_link><img title='$icon_title' alt='$icon_title' $icon_size src=$collectd_statistics_icon border=0></a>&nbsp;&nbsp;";
	return $plugin_link;
}



?>


