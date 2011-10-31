<!doctype html>
<html lang="en">
<head>
	<title>LXC create vm</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
	<link rel="stylesheet" type="text/css" href="lxc-storage.css" />
	<link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
	<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">
.ui-progressbar-value {
	background-image: url(/openqrm/base/img/progress.gif);
}
#progressbar {
	position: absolute;
	left: 150px;
	top: 250px;
	width: 400px;
	height: 20px;
}
</style>
</head>
<body>
<div id="progressbar">
</div>

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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $RESOURCE_INFO_TABLE;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;

// get the post parmater
$action = htmlobject_request('action');
$lxc_server_id = htmlobject_request('lxc_server_id');
$lxc_server_name = htmlobject_request('lxc_server_name');
$lxc_server_mac = htmlobject_request('lxc_server_mac');
$lxc_server_ip = htmlobject_request('lxc_server_ip');
$lxc_server_subnet = htmlobject_request('lxc_server_subnet');
$lxc_server_network = htmlobject_request('lxc_server_network');
$lxc_server_default_gateway = htmlobject_request('lxc_server_default_gateway');


$lxc_server_cpus = htmlobject_request('lxc_server_cpus');




function redirect_mgmt($strMsg, $file, $lxc_server_id) {
	global $thisfile;
	global $action;
	$url = $file.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&lxc_server_id='.$lxc_server_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function wait_for_statfile($sfile) {
	global $refresh_delay;
	global $refresh_loop_max;
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

function show_progressbar() {
?>
	<script type="text/javascript">
		$("#progressbar").progressbar({
			value: 100
		});
		var options = {};
		$("#progressbar").effect("shake",options,2000,null);
	</script>
<?php
		flush();
}


function validate_input($var, $type) {
	switch ($type) {
		case 'string':
			// remove allowed chars
			$var = str_replace(".", "", $var);
			$var = str_replace("-", "", $var);
			$var = str_replace("_", "", $var);
			$var = str_replace("/", "", $var);
			for ($i = 0; $i<strlen($var); $i++) {
				if (!ctype_alpha($var[$i])) {
					if (!ctype_digit($var[$i])) {
						return false;
					}
				}
			}
			return true;
			break;
		case 'number';
			for ($i = 0; $i<strlen($var); $i++) {
				if (!ctype_digit($var[$i])) {
					return false;
				}
			}
			return true;
			break;
	}
}


$strMsg = '';
if(htmlobject_request('action') != '') {
	if ($OPENQRM_USER->role == "administrator") {

		$event->log("$action", $_SERVER['REQUEST_TIME'], 5, "lxc-action", "Processing command $action", "", "", 0, 0, 0);
		switch ($action) {
			case 'new':
				show_progressbar();
				// name check
				$additional_network_parameters="";
				if (!strlen($lxc_server_name)) {
					$strMsg .= "Empty vm name. Not creating new vm on LXC Host $lxc_server_id";
					redirect_mgmt($strMsg, $thisfile, $lxc_server_id);
				} else if (!validate_input($lxc_server_name, 'string')) {
					$strMsg .= "Invalid vm name. Not creating new vm on LXC Host $lxc_server_id <br>(allowed characters are [a-z][A-z][0-9].-_)";
					redirect_mgmt($strMsg, $thisfile, $lxc_server_id);
				}
				if (!strlen($lxc_server_mac)) {
					$strMsg="Got empty mac-address. Not creating new vm on LXC Host $lxc_server_id";
					redirect_mgmt($strMsg, $thisfile, $lxc_server_id);
				}
				if (!strlen($lxc_server_ip)) {
					$strMsg="Got empty IP address. Not creating new vm on LXC Host $lxc_server_id";
					redirect_mgmt($strMsg, $thisfile, $lxc_server_id);
				}
				if (strlen($lxc_server_subnet)) {
					$additional_network_parameters .= " -s $lxc_server_subnet";
				}
				if (strlen($lxc_server_network)) {
					$additional_network_parameters .= " -t $lxc_server_network";
				}
				if (strlen($lxc_server_default_gateway)) {
					$additional_network_parameters .= " -g $lxc_server_default_gateway";
				}
				// send command to lxc_server-host to create the new vm
				$lxc_appliance = new appliance();
				$lxc_appliance->get_instance_by_id($lxc_server_id);
				$lxc_server = new resource();
				$lxc_server->get_instance_by_id($lxc_appliance->resources);
				// final command
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm create -n $lxc_server_name -m $lxc_server_mac -i $lxc_server_ip $additional_network_parameters -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
				// remove current stat file
				$lxc_server_resource_id = $lxc_server->id;
				$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
				if (file_exists($statfile)) {
					unlink($statfile);
				}
				// add resource + type + vhostid
				$resource = new resource();
				$resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
				$resource_ip=$lxc_server_ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_server_add_resource $resource_id $lxc_server_mac $resource_ip");
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("lxc-storage-vm");
				// add to openQRM database
				$resource_fields["resource_id"]=$resource_id;
				$resource_fields["resource_ip"]=$resource_ip;
				$resource_fields["resource_subnet"]=$lxc_server_subnet;
				$resource_fields["resource_network"]=$lxc_server_network;
				$resource_fields["resource_mac"]=$lxc_server_mac;
				$resource_fields["resource_localboot"]=0;
				$resource_fields["resource_vtype"]=$virtualization->id;
				$resource_fields["resource_vhostid"]=$lxc_server->id;
				$resource->add($resource_fields);
				// wait for new resource hook
				sleep(5);
				// send command
				$lxc_server->send_command($lxc_server->ip, $resource_command);
				// and wait for the resulting statfile
				if (!wait_for_statfile($statfile)) {
					$strMsg .= "Error during creating new LXC vm ! Please check the Event-Log<br>";
				} else {
					$strMsg .="Created new LXC vm resource $resource_id<br>";
				}
				redirect_mgmt($strMsg, "lxc-storage-vm-manager.php", $lxc_server_id);
				break;

		}
	}
}




function lxc_server_create($lxc_server_id) {

	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $thisfile;
	$lxc_server_appliance = new appliance();
	$lxc_server_appliance->get_instance_by_id($lxc_server_id);
	$lxc_server = new resource();
	$lxc_server->get_instance_by_id($lxc_server_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-vm-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'lxc_server_id' => $lxc_server_id,
		'lxc_server_name' => htmlobject_input('lxc_server_name', array("value" => '', "label" => 'VM name'), 'text', 20),
		'lxc_server_mac' => htmlobject_input('lxc_server_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'lxc_server_ip' => htmlobject_input('lxc_server_ip', array("value" => "0.0.0.0", "label" => 'IP address'), 'text', 20),
		'lxc_server_subnet' => htmlobject_input('lxc_server_subnet', array("value" => "", "label" => 'Subnetmask'), 'text', 20),
		'lxc_server_network' => htmlobject_input('lxc_server_network', array("value" => "", "label" => 'Network'), 'text', 20),
		'lxc_server_default_gateway' => htmlobject_input('lxc_server_default_gateway', array("value" => "", "label" => 'Gateway'), 'text', 20),
		'hidden_lxc_server_id' => "<input type=hidden name=lxc_server_id value=$lxc_server_id>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	if (isset($lxc_server_id)) {
		$output[] = array('label' => 'LXC Create VM', 'value' => lxc_server_create($lxc_server_id));
	}
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


