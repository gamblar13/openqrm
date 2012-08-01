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
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";
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
		$icon_title = "System statistics of Appliance ".$appliance_name;
	} else {
		$collectd_statistics_icon = "/openqrm/base/img/progress.gif";
		$icon_size = "width='25' height='25'";
		$graph_link = "";
		$icon_title = "System statistics of Appliance ".$appliance_name." pending";
	}
	$plugin_image = "<img title='$icon_title' alt='$icon_title' $icon_size src=$collectd_statistics_icon border=0>";

	$html = new htmlobject($OPENQRM_SERVER_BASE_DIR.'/openqrm/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = $plugin_image;
	$a->href = '#';
	$a->handler = 'onclick="window.open(\''.$graph_link.'\',\'\', \'location=0,status=0,scrollbars=1,width=800,height=600,left=50,top=50,screenX=50,screenY=50\');return false;"';

	return $a;
}



?>


