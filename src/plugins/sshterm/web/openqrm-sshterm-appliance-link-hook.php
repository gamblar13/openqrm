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

	$html = new htmlobject($OPENQRM_SERVER_BASE_DIR.'/openqrm/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = '<image height="24" width="24" alt="SSH-Login to '.$p_appliance->name.'" title="SSH-Login to '.$p_appliance->name.'" src="/openqrm/base/img/menu/sshterm/plugin.png">';
	$a->href = '#';
	$a->handler = 'onclick="window.open(\''.$sshterm_login_url.'\',\'\', \'location=0,status=0,scrollbars=1,width=580,height=420,left=400,top=100,screenX=400,screenY=100\');return false;"';

	$plugin_link = '';
	if (strstr($p_appliance->state, "active")) {
		$plugin_link = $a;
	}
	if ($p_resource->id == 0) {
		$plugin_link = $a;
	}
	if ($p_resource->id == '') {
		$plugin_link = "";
	}

	return $plugin_link;
}

?>

