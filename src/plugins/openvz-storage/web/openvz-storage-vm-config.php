<!doctype html>
<html lang="en">
<head>
	<title>OpenVZ VM configuration</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;

// get the post parmater
$action = htmlobject_request('action');
$openvz_server_id = htmlobject_request('openvz_server_id');
$openvz_server_name = htmlobject_request('openvz_server_name');
$openvz_command_parameter = htmlobject_request('openvz_command_parameter');
$openvz_command_value = htmlobject_request('openvz_command_value');
$openvz_command_limit = htmlobject_request('openvz_command_limit');
$openvz_vm_new_mac = htmlobject_request('openvz_vm_new_mac');
$openvz_vm_network_count = htmlobject_request('openvz_vm_network_count');

function redirect_config($strMsg, $openvz_server_id, $openvz_server_name) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&openvz_server_id='.$openvz_server_id.'&openvz_server_name='.$openvz_server_name;
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


// run the actions
$strMsg = '';
if(htmlobject_request('action') != '') {
	if ($OPENQRM_USER->role == "administrator") {
		switch (htmlobject_request('action')) {
			case 'Apply':
					show_progressbar();
					if (!strlen($openvz_command_value)) {
						$strMsg ="Empty openvz value. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					if (!strlen($openvz_command_parameter)) {
						$strMsg ="Empty openvz parameter. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					if (isset($openvz_command_limit)) {
						$openvz_command_limit_parameter=":".$openvz_command_limit;
					}
					// send command
					$openvz_appliance = new appliance();
					$openvz_appliance->get_instance_by_id($openvz_server_id);
					$openvz_res = new resource();
					$openvz_res->get_instance_by_id($openvz_appliance->resources);
					$resource_command="/usr/sbin/vzctl set ".$openvz_server_name." ".$openvz_command_parameter." ".$openvz_command_value.$openvz_command_limit_parameter." --save";
					$openvz_res->send_command($openvz_res->ip, $resource_command);
					$strMsg ="Setting ".$openvz_command_parameter." to ".$openvz_command_value.$openvz_command_limit_parameter;
					sleep(2);
					redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
				break;

			case 'Add':
					show_progressbar();
					if (!strlen($openvz_vm_new_mac)) {
						$strMsg ="Empty openvz VM Mac value. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					if ($openvz_vm_network_count > 4) {
						$strMsg ="Max Network-Interfaces reached. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					// send command
					$openvz_vm_network_count++;
					$openvz_appliance = new appliance();
					$openvz_appliance->get_instance_by_id($openvz_server_id);
					$openvz_res = new resource();
					$openvz_res->get_instance_by_id($openvz_appliance->resources);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm addnic -n ".$openvz_server_name." -m".$openvz_vm_network_count." ".$openvz_vm_new_mac." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
					$openvz_res->send_command($openvz_res->ip, $resource_command);
					$openvz_vm_network_count--;
					$strMsg ="Adding $openvz_vm_network_count. Network-Interface ".$openvz_vm_new_mac." to ".$openvz_server_name;
					sleep(2);
					redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
				break;

			case 'Remove':
					show_progressbar();
					if (!strlen($openvz_vm_network_count)) {
						$strMsg ="Empty openvz VM netdev value. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					if ($openvz_vm_network_count == 0) {
						$strMsg ="Not removing Management Network-Interfaces. Skipping to update VM configuration";
						redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
					}
					// send command
					$openvz_appliance = new appliance();
					$openvz_appliance->get_instance_by_id($openvz_server_id);
					$openvz_res = new resource();
					$openvz_res->get_instance_by_id($openvz_appliance->resources);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm removenic -n ".$openvz_server_name." -v ".$openvz_vm_network_count." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
					$openvz_res->send_command($openvz_res->ip, $resource_command);
					$strMsg ="Removing $openvz_vm_network_count. Network-Interface ".$openvz_vm_new_mac." to ".$openvz_server_name;
					sleep(2);
					redirect_config($strMsg, $openvz_server_id, $openvz_server_name);
				break;


		}
	}
}
// refresh config parameter
$openvz_server_appliance = new appliance();
$openvz_server_appliance->get_instance_by_id($openvz_server_id);
$openvz_server = new resource();
$openvz_server->get_instance_by_id($openvz_server_appliance->resources);
$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_config -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
// remove current stat file
$openvz_server_resource_id = $openvz_server->id;
$statfile="openvz-stat/".$openvz_server_resource_id.".".$openvz_server_name.".vm_config";
if (file_exists($statfile)) {
	unlink($statfile);
}
// send command
$openvz_server->send_command($openvz_server->ip, $resource_command);
// and wait for the resulting statfile
if (!wait_for_statfile($statfile)) {
	echo "<b>Could not get config status file! Please checks the event log";
	extit(0);
}





function openvz_vm_config() {
	global $openvz_server_id;
	global $openvz_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $refresh_delay;
	global $thisfile;

	$openvz_server_appliance = new appliance();
	$openvz_server_appliance->get_instance_by_id($openvz_server_id);
	$openvz_server = new resource();
	$openvz_server->get_instance_by_id($openvz_server_appliance->resources);

	$openvz_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/web/openvz-stat/$openvz_server->id.$openvz_server_name.vm_config";
	$store = openqrm_parse_conf($openvz_vm_conf_file);
	extract($store);


	// ###################################################

	$table1 = new htmlobject_table_builder('openvz_vm_network', 'DESC', '', '', 'openvz_vm_network');
	$arHead1 = array();

	$arHead1['openvz_vm_net_id'] = array();
	$arHead1['openvz_vm_net_id']['title'] ='ID';

	$arHead1['openvz_vm_device'] = array();
	$arHead1['openvz_vm_device']['title'] ='Device';

	$arHead1['openvz_vm_mac'] = array();
	$arHead1['openvz_vm_mac']['title'] ='Mac';

	$arHead1['openvz_vm_vdevice'] = array();
	$arHead1['openvz_vm_vdevice']['title'] ='Virtual';

	$arHead1['openvz_vm_device_action'] = array();
	$arHead1['openvz_vm_device_action']['title'] ='';

	$arBody1 = array();
	$openvz_vm_network_count=0;

	// mgmt network card
	$net0_arr = explode(",", $store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_0']);
	$net0_device = str_replace("ifname=", "", $net0_arr[0]);
	$net0_mac = str_replace("mac=", "", $net0_arr[1]);
	$net0_vnet = str_replace("host_ifname=", "", $net0_arr[2]);
	$net0_device_remove = "";
	$arBody1[] = array(
		'openvz_vm_net_id' => "0",
		'openvz_vm_device' => $net0_device,
		'openvz_vm_mac' => $net0_mac,
		'openvz_vm_vdevice' => $net0_vnet,
		'openvz_vm_device_action' => $net0_device_remove,
	);
	$openvz_vm_network_count++;

	// 1. network card
	if (strlen($store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_1'])) {
		$net1_arr = explode(",", $store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_1']);
		$net1_device = str_replace("ifname=", "", $net1_arr[0]);
		$net1_mac = str_replace("mac=", "", $net1_arr[1]);
		$net1_vnet = str_replace("host_ifname=", "", $net1_arr[2]);
		$openvz_vm_network_to_remove = str_replace("eth", "", $net1_device);
		$net1_device_remove = "<a href=\"openvz-storage-vm-config.php?action=Remove&openvz_vm_network_count=".$openvz_vm_network_to_remove."&openvz_server_id=".$openvz_server_id."&openvz_server_name=".$openvz_server_name."\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
		$arBody1[] = array(
			'openvz_vm_net_id' => "1",
			'openvz_vm_device' => $net1_device,
			'openvz_vm_mac' => $net1_mac,
			'openvz_vm_vdevice' => $net1_vnet,
			'openvz_vm_device_action' => $net1_device_remove,
		);
		$openvz_vm_network_count++;
	}

	// 2. network card
	if (strlen($store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_2'])) {
		$net2_arr = explode(",", $store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_2']);
		$net2_device = str_replace("ifname=", "", $net2_arr[0]);
		$net2_mac = str_replace("mac=", "", $net2_arr[1]);
		$net2_vnet = str_replace("host_ifname=", "", $net2_arr[2]);
		$openvz_vm_network_to_remove = str_replace("eth", "", $net2_device);
		$net2_device_remove = "<a href=\"openvz-storage-vm-config.php?action=Remove&openvz_vm_network_count=".$openvz_vm_network_to_remove."&openvz_server_id=".$openvz_server_id."&openvz_server_name=".$openvz_server_name."\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
		$arBody1[] = array(
			'openvz_vm_net_id' => "2",
			'openvz_vm_device' => $net2_device,
			'openvz_vm_mac' => $net2_mac,
			'openvz_vm_vdevice' => $net2_vnet,
			'openvz_vm_device_action' => $net2_device_remove,
		);
		$openvz_vm_network_count++;
	}

	// 3. network card
	if (strlen($store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_3'])) {
		$net3_arr = explode(",", $store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_3']);
		$net3_device = str_replace("ifname=", "", $net3_arr[0]);
		$net3_mac = str_replace("mac=", "", $net3_arr[1]);
		$net3_vnet = str_replace("host_ifname=", "", $net3_arr[2]);
		$openvz_vm_network_to_remove = str_replace("eth", "", $net3_device);
		$net3_device_remove = "<a href=\"openvz-storage-vm-config.php?action=Remove&openvz_vm_network_count=".$openvz_vm_network_to_remove."&openvz_server_id=".$openvz_server_id."&openvz_server_name=".$openvz_server_name."\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
		$arBody1[] = array(
			'openvz_vm_net_id' => "3",
			'openvz_vm_device' => $net3_device,
			'openvz_vm_mac' => $net3_mac,
			'openvz_vm_vdevice' => $net3_vnet,
			'openvz_vm_device_action' => $net3_device_remove,
		);
		$openvz_vm_network_count++;
	}

	// 4. network card
	if (strlen($store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_4'])) {
		$net4_arr = explode(",", $store['OPENQRM_OPENVZ_STORAGE_VM_INTERFACE_4']);
		$net4_device = str_replace("ifname=", "", $net4_arr[0]);
		$net4_mac = str_replace("mac=", "", $net4_arr[1]);
		$net4_vnet = str_replace("host_ifname=", "", $net4_arr[2]);
		$openvz_vm_network_to_remove = str_replace("eth", "", $net4_device);
		$net4_device_remove = "<a href=\"openvz-storage-vm-config.php?action=Remove&openvz_vm_network_count=".$openvz_vm_network_to_remove."&openvz_server_id=".$openvz_server_id."&openvz_server_name=".$openvz_server_name."\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Remove</a>";
		$arBody1[] = array(
			'openvz_vm_net_id' => "4",
			'openvz_vm_device' => $net4_device,
			'openvz_vm_mac' => $net4_mac,
			'openvz_vm_vdevice' => $net4_vnet,
			'openvz_vm_device_action' => $net4_device_remove,
		);
		$openvz_vm_network_count++;
	}

	$table1->id = 'OpenVZ-Network';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	$table1->autosort = true;
	$table1->sort = false;
	$table1->bottom = "";
	$table1->identifier = '';
	$table1->max = $openvz_vm_network_count;
	$table1->limit = 200;

	# new network card
	$generate_mac_res = new resource();
	$generate_mac_res->generate_mac();
	$generated_mac = $generate_mac_res->mac;
	$openvz_vm_mac_input = "<b>New Network-Interface <input type='text' name='openvz_vm_new_mac' value=\"".$generated_mac."\"></b>";

	// ###################################################

	$table = new htmlobject_table_builder('openvz_parameter', 'DESC', '', '', 'openvz_parameter');
	$arHead = array();
	$arHead['openvz_parameter'] = array();
	$arHead['openvz_parameter']['title'] ='Parameter';
	$arHead['openvz_value'] = array();
	$arHead['openvz_value']['title'] ='Value/Barrier';
	$arHead['openvz_limit'] = array();
	$arHead['openvz_limit']['title'] ='Limit';
	$arBody = array();
	$openvz_parameter_count=0;
	$openvz_parameter_select_arr = array();
	$lines = file($openvz_vm_conf_file);
	foreach ($lines as $line_num => $line) {
		if (strstr($line, "NETIF")) {
			continue;
		}
		if (strstr($line, "OPENQRM")) {
			continue;
		}
		$equal_pos = strpos($line, '=');
		$openvz_param = substr($line, 0, $equal_pos);
		$openvz_value = substr($line, $equal_pos+1);
		$openvz_value = str_replace('"', '', $openvz_value);

		$colon_pos = strpos($openvz_value, ':');
		if ($colon_pos >0) {
			$openvz_limit = substr($openvz_value, $colon_pos+1);
			$openvz_value = substr($openvz_value, 0, $colon_pos);
		} else {
			$openvz_limit = "";
		}

		$arBody[] = array(
			'openvz_parameter' => $openvz_param,
			'openvz_value' => $openvz_value,
			'openvz_limit' => $openvz_limit,
		);
		$openvz_parameter_count++;
	}

	// prepare the parameter select box
	$openvz_parameter_select_arr[] = array("value" => "--onboot", "label" => "ONBOOT");
	$openvz_parameter_select_arr[] = array("value" => "--kmemsize", "label" => "KMEMSIZE");
	$openvz_parameter_select_arr[] = array("value" => "--lockedpages", "label" => "LOCKEDPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--privvmpages", "label" => "PRIVVMPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--shmpages", "label" => "SHMPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--numproc", "label" => "NUMPROC");
	$openvz_parameter_select_arr[] = array("value" => "--physpages", "label" => "PHYSPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--vmguarpages", "label" => "VMGUARPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--oomguarpages", "label" => "OOMGUARPAGES");
	$openvz_parameter_select_arr[] = array("value" => "--numtcpsock", "label" => "NUMTCPSOCK");
	$openvz_parameter_select_arr[] = array("value" => "--numflock", "label" => "NUMFLOCK");
	$openvz_parameter_select_arr[] = array("value" => "--numpty", "label" => "NUMPTY");
	$openvz_parameter_select_arr[] = array("value" => "--numsiginfo", "label" => "NUMSIGINFO");
	$openvz_parameter_select_arr[] = array("value" => "--tcpsndbuf", "label" => "TCPSNDBUF");
	$openvz_parameter_select_arr[] = array("value" => "--tcprcvbuf", "label" => "TCPRCVBUF");
	$openvz_parameter_select_arr[] = array("value" => "--othersockbuf", "label" => "OTHERSOCKBUF");
	$openvz_parameter_select_arr[] = array("value" => "--dgramrcvbuf", "label" => "DGRAMRCVBUF");
	$openvz_parameter_select_arr[] = array("value" => "--numothersock", "label" => "NUMOTHERSOCK");
	$openvz_parameter_select_arr[] = array("value" => "--numfile", "label" => "NUMFILE");
	$openvz_parameter_select_arr[] = array("value" => "--avnumproc", "label" => "AVNUMPROC");
	$openvz_parameter_select_arr[] = array("value" => "--numiptent", "label" => "NUMIPTENT");
	$openvz_parameter_select_arr[] = array("value" => "--diskspace", "label" => "DISKSPACE");
	$openvz_parameter_select_arr[] = array("value" => "--diskinodes", "label" => "DISKINODES");
	$openvz_parameter_select_arr[] = array("value" => "--quotatime", "label" => "QUOTATIME");
	$openvz_parameter_select_arr[] = array("value" => "--cpus", "label" => "CPUS");
	$openvz_parameter_select_arr[] = array("value" => "--cpuunits", "label" => "CPUUNITS");
	$openvz_parameter_select_arr[] = array("value" => "--cpulimit", "label" => "CPULIMIT");
	$openvz_parameter_select_arr[] = array("value" => "--hostname", "label" => "HOSTNAME");

	$openvz_parameter_select = htmlobject_select("openvz_command_parameter", $openvz_parameter_select_arr, "Set ");
	$openvz_parameter_input = htmlobject_input("openvz_command_value", array("value" => '', "label" => 'Value/Barrier '), 'text', 20);
	$openvz_parameter_limit = htmlobject_input("openvz_command_limit", array("value" => '', "label" => 'Limit '), 'text', 20);

	$table->id = 'OpenVZ-Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->autosort = true;
	$table->sort = false;
	$table->bottom = "";
	$table->identifier = '';
	$table->max = $openvz_parameter_count;
	$table->limit = 200;
	// backlink
	$backlink = "<a href='openvz-storage-vm-manager.php?openvz_server_id=".$openvz_server_id."'>back</a>";
	$reloadlink = "<a href='openvz-storage-vm-config.php?openvz_server_id=".$openvz_server_id."&openvz_server_name=".$openvz_server_name."'>reload</a>";
   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'openvz-storage-vm-config.tpl.php');
	$t->setVar(array(
		'openvz_parameter_select' => $openvz_parameter_select,
		'openvz_parameter_input' => $openvz_parameter_input,
		'openvz_parameter_limit' => $openvz_parameter_limit,
		'formaction' => $thisfile,
		'submit' => htmlobject_input('action', array("value" => 'Apply', "label" => 'Apply'), 'submit'),
		'openvz_parameter_table' => $table->get_string(),
		'openvz_vm_network_table' => $table1->get_string(),
		'backlink' => $backlink,
		'reloadlink' => $reloadlink,
		'openvz_vm_mac_input' => $openvz_vm_mac_input,
		'addnet' => htmlobject_input('action', array("value" => 'Add', "label" => 'Add'), 'submit'),
		'hidden_openvz_vm_network_count' => "<input type='hidden' name='openvz_vm_network_count' value=$openvz_vm_network_count>",
		'hidden_openvz_server_id' => "<input type='hidden' name='openvz_server_id' value=$openvz_server_id>",
		'hidden_openvz_server_name' => "<input type='hidden' name='openvz_server_name' value=$openvz_server_name>",
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'OpenVZ Configure VM', 'value' => openvz_vm_config());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


