<!doctype html>
<html lang="en">
<head>
	<title>LXC vm configuration</title>
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

	Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
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
$lxc_server_id = htmlobject_request('lxc_server_id');
$lxc_server_name = htmlobject_request('lxc_server_name');
$lxc_nic_number = htmlobject_request('lxc_nic_number');
$lxc_storage_vm_bridge = htmlobject_request('lxc_storage_vm_bridge');
$lxc_new_nic_mac = htmlobject_request('lxc_new_nic_mac');


function redirect_config($strMsg, $lxc_server_id, $lxc_server_name) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&action=none&lxc_server_id='.$lxc_server_id.'&lxc_server_name='.$lxc_server_name;
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
			case 'Add':
					show_progressbar();
					if ($lxc_nic_number >4) {
						$strMsg ="Maximal number of network cards reached for LXC VM ".$lxc_server_name;
						redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
					}
					$new_lxc_nic_number = $lxc_nic_number+1;
					$lxc_storage_appliance = new appliance();
					$lxc_storage_appliance->get_instance_by_id($lxc_server_id);
					$lxc_storage_resource = new resource();
					$lxc_storage_resource->get_instance_by_id($lxc_storage_appliance->resources);
					if (strlen($lxc_storage_vm_bridge)) {
						$lxc_storage_vm_bridge_str = "-b ".$lxc_storage_vm_bridge;
					}

					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm addnic -n ".$lxc_server_name." -m".$new_lxc_nic_number." $lxc_new_nic_mac $lxc_storage_vm_bridge_str -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
					// remove current stat file
					$lxc_storage_resource_id = $lxc_storage_resource->id;
					$statfile="lxc-stat/".$lxc_storage_resource_id.".".$lxc_server_name.".vm_net_config";
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$lxc_storage_resource->send_command($lxc_storage_resource->ip, $resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$strMsg = "Error during addnic for LXC VM $lxc_server_name ! Please check the Event-Log $statfile<br>";
					} else {
						$strMsg ="Added network card eth".$lxc_nic_number."/".$lxc_storage_vm_bridge." to LXC VM ".$lxc_server_name;
					}
					redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
				break;

			case 'Remove':
					show_progressbar();
					if ($lxc_nic_number < 2) {
						$strMsg ="Not removing first network card from LXC VM ".$lxc_server_name;
						redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
					}
					$removed_nic_number = $lxc_nic_number -1;
					$lxc_storage_appliance = new appliance();
					$lxc_storage_appliance->get_instance_by_id($lxc_server_id);
					$lxc_storage_resource = new resource();
					$lxc_storage_resource->get_instance_by_id($lxc_storage_appliance->resources);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm removenic -n ".$lxc_server_name." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
					// remove current stat file
					$lxc_storage_resource_id = $lxc_storage_resource->id;
					$statfile="lxc-stat/".$lxc_storage_resource_id.".".$lxc_server_name.".vm_net_config";
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$lxc_storage_resource->send_command($lxc_storage_resource->ip, $resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$strMsg = "Error during removenic for LXC VM $lxc_server_name ! Please check the Event-Log $statfile<br>";
					} else {
						$strMsg ="Removed network card eth".$removed_nic_number." from LXC VM ".$lxc_server_name;
					}
					redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
				break;


		}
	}
} else {
	// refresh config parameter
	$lxc_server_appliance = new appliance();
	$lxc_server_appliance->get_instance_by_id($lxc_server_id);
	$lxc_server = new resource();
	$lxc_server->get_instance_by_id($lxc_server_appliance->resources);
	$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm post_vm_net_config -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
	// remove current stat file
	$lxc_server_resource_id = $lxc_server->id;
	$statfile="lxc-stat/".$lxc_server_resource_id.".".$lxc_server_name.".vm_net_config";
	if (file_exists($statfile)) {
		unlink($statfile);
	}
	// send command
	$lxc_server->send_command($lxc_server->ip, $resource_command);
	// and wait for the resulting statfile
	if (!wait_for_statfile($statfile)) {
	   echo "<b>Could not get config status file! Please checks the event log";
	   extit(0);
	}
}




function lxc_vm_net_config() {
	global $lxc_server_id;
	global $lxc_server_name;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_USER;
	global $thisfile;
	global $refresh_delay;

	$lxc_server_appliance = new appliance();
	$lxc_server_appliance->get_instance_by_id($lxc_server_id);
	$lxc_server = new resource();
	$lxc_server->get_instance_by_id($lxc_server_appliance->resources);

	$table = new htmlobject_table_builder('lxc_network', 'DESC', '', '', 'lxc_network');
	$arHead = array();

	$arHead['lxc_net_id'] = array();
	$arHead['lxc_net_id']['title'] ='Adapter';

	$arHead['lxc_net_mac'] = array();
	$arHead['lxc_net_mac']['title'] ='Mac-Adress';

	$arHead['lxc_net_bridge'] = array();
	$arHead['lxc_net_bridge']['title'] ='Bridge';

	$arBody = array();
	$lxc_net_count=0;

	$lxc_storage_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/web/lxc-stat/$lxc_server->id.$lxc_server_name.vm_net_config";
	$store = openqrm_parse_conf($lxc_storage_vm_conf_file);
	extract($store);
	// mgmt nic
	$arBody[] = array(
			'lxc_net_id' => "eth".$lxc_net_count,
			'lxc_net_mac' => $store['OPENQRM_LXC_STORAGE_VM_MAC_0'],
			'lxc_net_bridge' => $store['OPENQRM_LXC_STORAGE_VM_BRIDGE_0'],
		);
	$lxc_net_count++;
	// additional nics
	if (isset($store['OPENQRM_LXC_STORAGE_VM_MAC_1'])) {
		if (strlen($store['OPENQRM_LXC_STORAGE_VM_MAC_1'])) {
			$arBody[] = array(
					'lxc_net_id' => "eth".$lxc_net_count,
					'lxc_net_mac' => $store['OPENQRM_LXC_STORAGE_VM_MAC_1'],
					'lxc_net_bridge' => $store['OPENQRM_LXC_STORAGE_VM_BRIDGE_1'],
				);
			$lxc_net_count++;
		}
	}
	if (isset($store['OPENQRM_LXC_STORAGE_VM_MAC_2'])) {
		if (strlen($store['OPENQRM_LXC_STORAGE_VM_MAC_2'])) {
			$arBody[] = array(
					'lxc_net_id' => "eth".$lxc_net_count,
					'lxc_net_mac' => $store['OPENQRM_LXC_STORAGE_VM_MAC_2'],
					'lxc_net_bridge' => $store['OPENQRM_LXC_STORAGE_VM_BRIDGE_2'],
				);
			$lxc_net_count++;
		}
	}
	if (isset($store['OPENQRM_LXC_STORAGE_VM_MAC_3'])) {
		if (strlen($store['OPENQRM_LXC_STORAGE_VM_MAC_3'])) {
			$arBody[] = array(
					'lxc_net_id' => "eth".$lxc_net_count,
					'lxc_net_mac' => $store['OPENQRM_LXC_STORAGE_VM_MAC_3'],
					'lxc_net_bridge' => $store['OPENQRM_LXC_STORAGE_VM_BRIDGE_3'],
				);
			$lxc_net_count++;
		}
	}
	if (isset($store['OPENQRM_LXC_STORAGE_VM_MAC_4'])) {
		if (strlen($store['OPENQRM_LXC_STORAGE_VM_MAC_4'])) {
			$arBody[] = array(
					'lxc_net_id' => "eth".$lxc_net_count,
					'lxc_net_mac' => $store['OPENQRM_LXC_STORAGE_VM_MAC_4'],
					'lxc_net_bridge' => $store['OPENQRM_LXC_STORAGE_VM_BRIDGE_4'],
				);
			$lxc_net_count++;
		}
	}


	$table->id = 'LXC-Net-Tabelle';
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
	$table->max = $lxc_net_count;
	$table->limit = 5;
	// backlink
	$backlink = "<a href='lxc-storage-vm-manager.php?lxc_server_id=".$lxc_server_id."'>back</a>";
	$reloadlink = "<a href='lxc-storage-vm-net-config.php?lxc_server_id=".$lxc_server_id."&lxc_server_name=".$lxc_server_name."'>reload</a>";
	// input and submit
	$lxc_mac_gen_res = new resource();
	$lxc_mac_gen_res->generate_mac();
	$lxc_new_mac = $lxc_mac_gen_res->mac;
	$lxc_new_nic_mac = htmlobject_input('lxc_new_nic_mac', array("value" => $lxc_new_mac, "label" => 'Mac-adress'), 'text');

	// bridge array for the select
	$lxc_storage_mgmt_bridge = $store['OPENQRM_LXC_STORAGE_MGMT_BRIDGE'];
	$lxc_storage_bridge_net1 = $store['OPENQRM_LXC_STORAGE_BRIDGE_NET1'];
	$lxc_storage_bridge_net2 = $store['OPENQRM_LXC_STORAGE_BRIDGE_NET2'];
	$lxc_storage_bridge_net3 = $store['OPENQRM_LXC_STORAGE_BRIDGE_NET3'];
	$lxc_storage_bridge_net4 = $store['OPENQRM_LXC_STORAGE_BRIDGE_NET4'];
	$bridge_identifier_array = array();
	$bridge_identifier_array[] = array("value" => "$lxc_storage_mgmt_bridge", "label" => "$lxc_storage_mgmt_bridge (MGMT Network)");
	$bridge_identifier_array[] = array("value" => "$lxc_storage_bridge_net1", "label" => "$lxc_storage_bridge_net1 (Network 1)");
	$bridge_identifier_array[] = array("value" => "$lxc_storage_bridge_net2", "label" => "$lxc_storage_bridge_net2 (Network 2)");
	$bridge_identifier_array[] = array("value" => "$lxc_storage_bridge_net3", "label" => "$lxc_storage_bridge_net3 (Network 3)");
	$bridge_identifier_array[] = array("value" => "$lxc_storage_bridge_net4", "label" => "$lxc_storage_bridge_net4 (Network 4)");
	$lxc_bridge_select = htmlobject_select('lxc_storage_vm_bridge', $bridge_identifier_array, 'Network-Bridge');


	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-vm-net-config.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'submit' => htmlobject_input('action', array("value" => 'Add', "label" => 'Add'), 'submit'),
		'remove_nic' => htmlobject_input('action', array("value" => 'Remove', "label" => 'Remove'), 'submit'),
		'lxc_network_table' => $table->get_string(),
		'backlink' => $backlink,
		'reloadlink' => $reloadlink,
		'hidden_lxc_server_id' => "<input type='hidden' name='lxc_server_id' value=$lxc_server_id>",
		'hidden_lxc_server_name' => "<input type='hidden' name='lxc_server_name' value=$lxc_server_name>",
		'hidden_lxc_nic_number' => "<input type='hidden' name='lxc_nic_number' value=$lxc_net_count>",
		'lxc_new_nic_input' => $lxc_new_nic_mac,
		'lxc_storage_vm_bridge' => $lxc_bridge_select,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Lxc Network Configuration', 'value' => lxc_vm_net_config());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


