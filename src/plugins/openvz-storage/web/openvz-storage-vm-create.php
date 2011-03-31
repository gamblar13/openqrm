<!doctype html>
<html lang="en">
<head>
	<title>OpenVZ create vm</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
	<link rel="stylesheet" type="text/css" href="openvz-storage.css" />
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

	Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
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
$openvz_server_id = htmlobject_request('openvz_server_id');
$openvz_server_name = htmlobject_request('openvz_server_name');
$openvz_server_mac = htmlobject_request('openvz_server_mac');
$openvz_server_ip = htmlobject_request('openvz_server_ip');
$openvz_server_subnet = htmlobject_request('openvz_server_subnet');
$openvz_server_network = htmlobject_request('openvz_server_network');
$openvz_server_default_gateway = htmlobject_request('openvz_server_default_gateway');
$openvz_server_hostname = htmlobject_request('openvz_server_hostname');


$openvz_server_cpus = htmlobject_request('openvz_server_cpus');




function redirect_mgmt($strMsg, $file, $openvz_server_id) {
	global $thisfile;
	global $action;
	$url = $file.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&openvz_server_id='.$openvz_server_id;
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
	$event->log("$action", $_SERVER['REQUEST_TIME'], 5, "lxc-action", "Processing command $action", "", "", 0, 0, 0);
	if ($OPENQRM_USER->role == "administrator") {

		switch ($action) {
			case 'new':
				show_progressbar();
				// name check
				$additional_network_parameters="";
				if (!strlen($openvz_server_name)) {
					$strMsg .= "Empty VM name. Not creating new VM on OpenVZ Host $openvz_server_id";
					redirect_mgmt($strMsg, $thisfile, $openvz_server_id);
				} else if (!validate_input($openvz_server_name, 'string')) {
					$strMsg .= "Invalid VM name. Not creating new VM on OpenVZ Host $openvz_server_id <br>(allowed characters are [a-z][A-z][0-9].-_)";
					redirect_mgmt($strMsg, $thisfile, $openvz_server_id);
				}
				$openvz_server_hostname_parameter = '';
				if (strlen($openvz_server_hostname)) {
					if (!validate_input($openvz_server_hostname, 'string')) {
						$strMsg .= "Invalid VM hostname. Not creating new VM on OpenVZ Host $openvz_server_id <br>(allowed characters are [a-z][A-z][0-9].-_)";
						redirect_mgmt($strMsg, $thisfile, $openvz_server_id);
					} else {
						$openvz_server_hostname_parameter = "-w ".$openvz_server_hostname;
					}
				}
				if (!strlen($openvz_server_mac)) {
					$strMsg="Got empty mac-address. Not creating new VM on OpenVZ Host $openvz_server_id";
					redirect_mgmt($strMsg, $thisfile, $openvz_server_id);
				}
				if (!strlen($openvz_server_ip)) {
					$strMsg="Got empty IP address. Not creating new VM on OpenVZ Host $openvz_server_id";
					redirect_mgmt($strMsg, $thisfile, $openvz_server_id);
				}
				if (strlen($openvz_server_subnet)) {
					$additional_network_parameters .= " -s $openvz_server_subnet";
				}
				if (strlen($openvz_server_network)) {
					$additional_network_parameters .= " -t $openvz_server_network";
				}
				if (strlen($openvz_server_default_gateway)) {
					$additional_network_parameters .= " -g $openvz_server_default_gateway";
				}
				// send command to openvz_server-host to create the new vm
				$openvz_appliance = new appliance();
				$openvz_appliance->get_instance_by_id($openvz_server_id);
				$openvz_server = new resource();
				$openvz_server->get_instance_by_id($openvz_appliance->resources);
				// final command
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm create -n $openvz_server_name -m $openvz_server_mac -i $openvz_server_ip $additional_network_parameters $openvz_server_hostname_parameter -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
				// remove current stat file
				$openvz_server_resource_id = $openvz_server->id;
				$statfile="lxc-stat/".$openvz_server_resource_id.".vm_list";
				if (file_exists($statfile)) {
					unlink($statfile);
				}
				// add resource + type + vhostid
				$resource = new resource();
				$resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
				$resource_ip=$openvz_server_ip;
				// send command to the openQRM-server
				$openqrm_server->send_command("openqrm_server_add_resource $resource_id $openvz_server_mac $resource_ip");
				// set resource type
				$virtualization = new virtualization();
				$virtualization->get_instance_by_type("openvz-storage-vm");
				// add to openQRM database
				$resource_fields["resource_id"]=$resource_id;
				$resource_fields["resource_ip"]=$resource_ip;
				$resource_fields["resource_subnet"]=$openvz_server_subnet;
				$resource_fields["resource_network"]=$openvz_server_network;
				$resource_fields["resource_mac"]=$openvz_server_mac;
				$resource_fields["resource_hostname"]=$openvz_server_hostname;
				$resource_fields["resource_localboot"]=0;
				$resource_fields["resource_vtype"]=$virtualization->id;
				$resource_fields["resource_vhostid"]=$openvz_server->id;
				$resource->add($resource_fields);
				// wait for new resource hook
				sleep(5);
				// send command
				$openvz_server->send_command($openvz_server->ip, $resource_command);
				// and wait for the resulting statfile
				if (!wait_for_statfile($statfile)) {
					$strMsg .= "Error during creating new OpenVZ VM ! Please check the Event-Log<br>";
				} else {
					$strMsg .="Created new OpenVZ VM resource $resource_id<br>";
				}
				redirect_mgmt($strMsg, "openvz-storage-vm-manager.php", $openvz_server_id);
				break;

		}
	}
}




function openvz_server_create($openvz_server_id) {

	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $thisfile;
	$openvz_server_appliance = new appliance();
	$openvz_server_appliance->get_instance_by_id($openvz_server_id);
	$openvz_server = new resource();
	$openvz_server->get_instance_by_id($openvz_server_appliance->resources);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'openvz-storage-vm-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'openvz_server_id' => $openvz_server_id,
		'openvz_server_name' => htmlobject_input('openvz_server_name', array("value" => '', "label" => 'VM ID'), 'text', 20),
		'openvz_server_hostname' => htmlobject_input('openvz_server_hostname', array("value" => '', "label" => 'VM hostname'), 'text', 20),
		'openvz_server_mac' => htmlobject_input('openvz_server_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'openvz_server_ip' => htmlobject_input('openvz_server_ip', array("value" => "0.0.0.0", "label" => 'IP address'), 'text', 20),
		'openvz_server_subnet' => htmlobject_input('openvz_server_subnet', array("value" => "", "label" => 'Subnetmask'), 'text', 20),
		'openvz_server_network' => htmlobject_input('openvz_server_network', array("value" => "", "label" => 'Network'), 'text', 20),
		'openvz_server_default_gateway' => htmlobject_input('openvz_server_default_gateway', array("value" => "", "label" => 'Gateway'), 'text', 20),
		'hidden_openvz_server_id' => "<input type=hidden name=openvz_server_id value=$openvz_server_id>",
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	if (isset($openvz_server_id)) {
		$output[] = array('label' => 'OpenVZ Create VM', 'value' => openvz_server_create($openvz_server_id));
	}
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


