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
$lxc_server_id = htmlobject_request('lxc_server_id');
$lxc_server_name = htmlobject_request('lxc_server_name');
$lxc_command_parameter = htmlobject_request('lxc_command_parameter');
$lxc_command_value = htmlobject_request('lxc_command_value');


function redirect_config($strMsg, $lxc_server_id, $lxc_server_name) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&lxc_server_id='.$lxc_server_id.'&lxc_server_name='.$lxc_server_name;
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
					if (!strlen($lxc_command_value)) {
						$strMsg ="Empty lxc value. Skipping to update VM configuration";
						redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
					}
					if (!strlen($lxc_command_parameter)) {
						$strMsg ="Empty lxc parameter. Skipping to update VM configuration";
						redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
					}
					// send command
					$lxc_appliance = new appliance();
					$lxc_appliance->get_instance_by_id($lxc_server_id);
					$lxc_res = new resource();
					$lxc_res->get_instance_by_id($lxc_appliance->resources);
					$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm set_vm_config -n $lxc_server_name -v $lxc_command_parameter -x $lxc_command_value";
					$lxc_res->send_command($lxc_res->ip, $resource_command);
					$strMsg ="Setting $lxc_command_parameter to $lxc_command_value";
					sleep(2);
					redirect_config($strMsg, $lxc_server_id, $lxc_server_name);
				break;


		}
	}
}
// refresh config parameter
$lxc_server_appliance = new appliance();
$lxc_server_appliance->get_instance_by_id($lxc_server_id);
$lxc_server = new resource();
$lxc_server->get_instance_by_id($lxc_server_appliance->resources);
$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm post_vm_config -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
// remove current stat file
$lxc_server_resource_id = $lxc_server->id;
$statfile="lxc-stat/".$lxc_server_resource_id.".".$lxc_server_name.".vm_config";
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






function lxc_vm_config() {
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

	$lxc_vm_conf_file="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/web/lxc-stat/$lxc_server->id.$lxc_server_name.vm_config";

	$table = new htmlobject_table_builder('lxc_parameter', 'DESC', '', '', 'lxc_parameter');
	$arHead = array();
	$arHead['lxc_parameter'] = array();
	$arHead['lxc_parameter']['title'] ='Cgroup parameter';
	$arHead['lxc_value'] = array();
	$arHead['lxc_value']['title'] ='Value';
	$arBody = array();
	$lxc_parameter_count=0;
	$lxc_parameter_select_arr = array();
	$lines = file($lxc_vm_conf_file);
	$lxc_param = '';
	$lxc_val = '';
	foreach ($lines as $line_num => $line) {
		if (strstr($line, "##")) {
			if ((strlen($lxc_param)) && (strlen($lxc_val))) {
				$arBody[] = array(
					'lxc_parameter' => $lxc_param,
					'lxc_value' => $lxc_val,
				);
				$lxc_parameter_count++;
				// add to selectbox
				$lxc_parameter_select_arr[] = array("value" => $lxc_param, "label" => $lxc_param);
			}
			$lxc_param = str_replace("##", "", $line);
			$lxc_val = "";
		} else {
			$lxc_val .= $line;
		}

	}

	// prepare the parameter select box
	$lxc_parameter_select = htmlobject_select("lxc_command_parameter", $lxc_parameter_select_arr, "Set ");
	$lxc_parameter_input = htmlobject_input("lxc_command_value", array("value" => '', "label" => 'To '), 'text', 20);

	$table->id = 'LXC-Tabelle';
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
	$table->max = $lxc_parameter_count;
	$table->limit = 200;
	// backlink
	$backlink = "<a href='lxc-storage-vm-manager.php?lxc_server_id=".$lxc_server_id."'>back</a>";
	$reloadlink = "<a href='lxc-storage-vm-config.php?lxc_server_id=".$lxc_server_id."&lxc_server_name=".$lxc_server_name."'>reload</a>";
   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-vm-config.tpl.php');
	$t->setVar(array(
		'lxc_parameter_select' => $lxc_parameter_select,
		'lxc_parameter_input' => $lxc_parameter_input,
		'formaction' => $thisfile,
		'submit' => htmlobject_input('action', array("value" => 'Apply', "label" => 'Apply'), 'submit'),
		'lxc_parameter_table' => $table->get_string(),
		'backlink' => $backlink,
		'reloadlink' => $reloadlink,
		'hidden_lxc_server_id' => "<input type='hidden' name='lxc_server_id' value=$lxc_server_id>",
		'hidden_lxc_server_name' => "<input type='hidden' name='lxc_server_name' value=$lxc_server_name>",
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Lxc Configure VM', 'value' => lxc_vm_config());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


