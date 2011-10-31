<!doctype html>
<html lang="en">
<head>
	<title>LXC manager</title>
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$lxc_server_id = htmlobject_request('lxc_server_id');
$lxc_vm_mac = htmlobject_request('lxc_vm_mac');
$lxc_vm_mac_ar = htmlobject_request('lxc_vm_mac_ar');
$action=htmlobject_request('action');
global $lxc_server_id;
global $lxc_vm_mac;
global $lxc_vm_mac_ar;
$refresh_delay=1;
$refresh_loop_max=20;

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	global $lxc_server_id;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&lxc_server_id='.$lxc_server_id;
	}
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


// check if we got some actions to do
$strMsg = '';
if(htmlobject_request('action') != '') {
	if ($OPENQRM_USER->role == "administrator") {

		switch (htmlobject_request('action')) {
			case 'select':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $lxc_server_id) {
						show_progressbar();
						$lxc_appliance = new appliance();
						$lxc_appliance->get_instance_by_id($lxc_server_id);
						$lxc_server = new resource();
						$lxc_server->get_instance_by_id($lxc_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$lxc_server_resource_id = $lxc_server->id;
						$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$lxc_server->send_command($lxc_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Refreshing vm list<br>";
						}
						redirect($strMsg, "tab0");
						exit(0);
					}
				}
				break;

			case 'reload':
				show_progressbar();
				$lxc_appliance = new appliance();
				$lxc_appliance->get_instance_by_id($lxc_server_id);
				$lxc_server = new resource();
				$lxc_server->get_instance_by_id($lxc_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
				// remove current stat file
				$lxc_server_resource_id = $lxc_server->id;
				$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
				if (file_exists($statfile)) {
					unlink($statfile);
				}
				// send command
				$lxc_server->send_command($lxc_server->ip, $resource_command);
				// and wait for the resulting statfile
				if (!wait_for_statfile($statfile)) {
					$strMsg .= "Error during refreshing vm list ! Please check the Event-Log<br>";
				} else {
					$strMsg .="Refreshing vm list<br>";
				}
				redirect($strMsg, "tab0");
				break;


			case 'start':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $lxc_server_name) {
						show_progressbar();
						$lxc_appliance = new appliance();
						$lxc_appliance->get_instance_by_id($lxc_server_id);
						$lxc_server = new resource();
						$lxc_server->get_instance_by_id($lxc_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm start -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$lxc_server_resource_id = $lxc_server->id;
						$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$lxc_server->send_command($lxc_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during starting $lxc_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Starting $lxc_server_name <br>";
						}
					}
					redirect($strMsg, "tab0");
				} else {
					$strMsg ="No virtual machine selected<br>";
					redirect($strMsg, "tab0");
				}
				break;


			case 'stop':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $lxc_server_name) {
						show_progressbar();
						$lxc_appliance = new appliance();
						$lxc_appliance->get_instance_by_id($lxc_server_id);
						$lxc_server = new resource();
						$lxc_server->get_instance_by_id($lxc_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm stop -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$lxc_server_resource_id = $lxc_server->id;
						$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$lxc_server->send_command($lxc_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during stopping $lxc_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Stopping $lxc_server_name <br>";
						}
					}
					redirect($strMsg, "tab0");
				} else {
					$strMsg ="No virtual machine selected<br>";
					redirect($strMsg, "tab0");
				}
				break;

			case 'restart':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $lxc_server_name) {
						show_progressbar();
						$lxc_appliance = new appliance();
						$lxc_appliance->get_instance_by_id($lxc_server_id);
						$lxc_server = new resource();
						$lxc_server->get_instance_by_id($lxc_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm reboot -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$lxc_server_resource_id = $lxc_server->id;
						$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$lxc_server->send_command($lxc_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during restarting $lxc_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Restarting $lxc_server_name <br>";
						}
					}
					redirect($strMsg, "tab0");
				} else {
					$strMsg ="No virtual machine selected<br>";
					redirect($strMsg, "tab0");
				}
				break;

			case 'delete':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $lxc_server_name) {
						show_progressbar();
						// check if the resource still belongs to an appliance, if yes we do not remove it
						$lxc_vm_mac = $lxc_vm_mac_ar[$lxc_server_name];
						$lxc_resource = new resource();
						$lxc_resource->get_instance_by_mac($lxc_vm_mac);
						$lxc_vm_id=$lxc_resource->id;
						$resource_is_used_by_appliance = "";
						$remove_error = 0;
						$appliance = new appliance();
						$appliance_id_list = $appliance->get_all_ids();
						foreach($appliance_id_list as $appliance_list) {
							$appliance_id = $appliance_list['appliance_id'];
							$app_resource_remove_check = new appliance();
							$app_resource_remove_check->get_instance_by_id($appliance_id);
							if ($app_resource_remove_check->resources == $lxc_vm_id) {
								$resource_is_used_by_appliance .= $appliance_id." ";
								$remove_error = 1;
							}
						}
						if ($remove_error == 1) {
							$strMsg .= "VM Resource id ".$lxc_vm_id." is used by appliance(s): ".$resource_is_used_by_appliance." <br>";
							$strMsg .= "Not removing VM resource id ".$lxc_vm_id." !<br>";
							continue;
						}
						// remove vm
						$lxc_appliance = new appliance();
						$lxc_appliance->get_instance_by_id($lxc_server_id);
						$lxc_server = new resource();
						$lxc_server->get_instance_by_id($lxc_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage-vm delete -n $lxc_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$lxc_server_resource_id = $lxc_server->id;
						$statfile="lxc-stat/".$lxc_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$lxc_server->send_command($lxc_server->ip, $resource_command);
						$lxc_resource->remove($lxc_vm_id, $lxc_vm_mac);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during removing $lxc_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Removed $lxc_server_name and its resource $lxc_vm_id<br>";
						}
					}
					redirect($strMsg, "tab0");
				} else {
					$strMsg ="No virtual machine selected<br>";
					redirect($strMsg, "tab0");
				}
				break;


		}
	}
}





function lxc_server_select() {

	global $OPENQRM_USER;
	global $thisfile;
	$table = new htmlobject_table_builder('appliance_id', '', '', '', 'select');

	$arHead = array();
	$arHead['appliance_state'] = array();
	$arHead['appliance_state']['title'] ='';
	$arHead['appliance_state']['sortable'] = false;

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';
	$arHead['appliance_icon']['sortable'] = false;

	$arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_resource_id'] = array();
	$arHead['appliance_resource_id']['title'] ='Res.ID';
	$arHead['appliance_resource_id']['sortable'] = false;

	$arHead['appliance_resource_ip'] = array();
	$arHead['appliance_resource_ip']['title'] ='Ip';
	$arHead['appliance_resource_ip']['sortable'] = false;

	$arHead['appliance_comment'] = array();
	$arHead['appliance_comment']['title'] ='Comment';

	$lxc_server_count=0;
	$arBody = array();
	$virtualization = new virtualization();
	$virtualization->get_instance_by_type("lxc-storage");
	$lxc_server_tmp = new appliance();
	$lxc_server_array = $lxc_server_tmp->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($lxc_server_array as $index => $lxc_server_db) {
		$lxc_server_resource = new resource();
		$lxc_server_resource->get_instance_by_id($lxc_server_db["appliance_resources"]);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$lxc_server_icon="/openqrm/base/plugins/lxc-storage/img/plugin.png";
		$state_icon="/openqrm/base/img/$lxc_server_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$lxc_server_icon)) {
			$resource_icon_default=$lxc_server_icon;
		}
		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $lxc_server_db["appliance_id"],
			'appliance_name' => $lxc_server_db["appliance_name"],
			'appliance_resource_id' => $lxc_server_resource->id,
			'appliance_resource_ip' => $lxc_server_resource->ip,
			'appliance_comment' => $lxc_server_db["appliance_comment"],
		);
		$lxc_server_count++;
	}
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'appliance_id';
	}
	$table->max = $lxc_server_tmp->get_count_per_virtualization($virtualization->id);

	// are there any host appliances yet ?
	if(count($arBody) > 0) {
		$disp = $table->get_string();
	} else {
		$box = new htmlobject_box();
		$box->id = 'htmlobject_box_add_host';
		$box->css = 'htmlobject_box';
		$box->label = '<br><nobr><b>No host appliances configured yet!</b></nobr>';
		$box_content = '<br><br><br><br>Please create a '.$virtualization->name.' appliance first!<br>';
		$box_content .= '<a href="/openqrm/base/server/appliance/appliance-new.php?currenttab=tab1"><b>New appliance</b></a><br>';
		$box->content = $box_content;
		$disp = $box->get_string();
	}

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-lxc-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'lxc_server_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function lxc_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;

	$table = new htmlobject_table_identifiers_checked('lxc_server_id');

	$arHead = array();
	$arHead['lxc_server_state'] = array();
	$arHead['lxc_server_state']['title'] ='State';

	$arHead['lxc_server_icon'] = array();
	$arHead['lxc_server_icon']['title'] ='Type';

	$arHead['lxc_server_id'] = array();
	$arHead['lxc_server_id']['title'] ='ID';

	$arHead['lxc_server_name'] = array();
	$arHead['lxc_server_name']['title'] ='Name';

	$arHead['lxc_server_resource_id'] = array();
	$arHead['lxc_server_resource_id']['title'] ='Res.ID';

	$arHead['lxc_server_resource_ip'] = array();
	$arHead['lxc_server_resource_ip']['title'] ='Ip';

	$arHead['lxc_server_comment'] = array();
	$arHead['lxc_server_comment']['title'] ='';

	$arHead['lxc_server_create'] = array();
	$arHead['lxc_server_create']['title'] ='';

	$lxc_server_count=1;
	$arBody = array();
	$lxc_server_tmp = new appliance();
	$lxc_server_tmp->get_instance_by_id($appliance_id);
	$lxc_server_resource = new resource();
	$lxc_server_resource->get_instance_by_id($lxc_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$lxc_server_icon="/openqrm/base/plugins/lxc-storage/img/plugin.png";
	$state_icon="/openqrm/base/img/$lxc_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$lxc_server_icon)) {
		$resource_icon_default=$lxc_server_icon;
	}
	$lxc_server_create_button="<a href=\"lxc-storage-vm-create.php?lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'lxc_server_state' => "<img src=$state_icon>",
		'lxc_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'lxc_server_id' => $lxc_server_tmp->id,
		'lxc_server_name' => $lxc_server_tmp->name,
		'lxc_server_resource_id' => $lxc_server_resource->id,
		'lxc_server_resource_ip' => $lxc_server_resource->ip,
		'lxc_server_comment' => $lxc_server_tmp->comment,
		'lxc_server_create' => $lxc_server_create_button,
	);

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->sort = '';
	$table->head = $arHead;
	$table->body = $arBody;
	$table->max = $lxc_server_count;

	// table 1
	$table1 = new htmlobject_table_builder('lxc_vm_id', '', '', '', 'vms');
	$arHead1 = array();
	$arHead1['lxc_vm_state'] = array();
	$arHead1['lxc_vm_state']['title'] ='State';
	$arHead1['lxc_vm_state']['sortable'] = false;

	$arHead1['lxc_vm_id'] = array();
	$arHead1['lxc_vm_id']['title'] ='Res.';

	$arHead1['lxc_vm_name'] = array();
	$arHead1['lxc_vm_name']['title'] ='Name';

	$arHead1['lxc_vm_ip'] = array();
	$arHead1['lxc_vm_ip']['title'] ='IP';

	$arHead1['lxc_vm_mac'] = array();
	$arHead1['lxc_vm_mac']['title'] ='MAC';

	$arHead1['lxc_vm_actions'] = array();
	$arHead1['lxc_vm_actions']['title'] ='Actions';
	$arHead1['lxc_vm_actions']['sortable'] = false;
	$arBody1 = array();

	$lxc_server_vm_list_file="lxc-stat/$lxc_server_resource->id.vm_list";
	$lxc_vm_registered=array();
	$lxc_vm_count=0;
	if (file_exists($lxc_server_vm_list_file)) {
		$lxc_server_vm_list_content=file($lxc_server_vm_list_file);
		foreach ($lxc_server_vm_list_content as $index => $lxc_vm) {
			// find the vms
			if (!strstr($lxc_vm, "#")) {

				$first_at_pos = strpos($lxc_vm, "@");
				$first_at_pos++;
				$lxc_name_first_at_removed = substr($lxc_vm, $first_at_pos, strlen($lxc_vm)-$first_at_pos);
				$second_at_pos = strpos($lxc_name_first_at_removed, "@");
				$second_at_pos++;
				$lxc_name_second_at_removed = substr($lxc_name_first_at_removed, $second_at_pos, strlen($lxc_name_first_at_removed)-$second_at_pos);
				$third_at_pos = strpos($lxc_name_second_at_removed, "@");
				$third_at_pos++;
				$lxc_name_third_at_removed = substr($lxc_name_second_at_removed, $third_at_pos, strlen($lxc_name_second_at_removed)-$third_at_pos);
				$fourth_at_pos = strpos($lxc_name_third_at_removed, "@");
				$fourth_at_pos++;
				$lxc_name_fourth_at_removed = substr($lxc_name_third_at_removed, $fourth_at_pos, strlen($lxc_name_third_at_removed)-$fourth_at_pos);
				$fivth_at_pos = strpos($lxc_name_fourth_at_removed, "@");
				$fivth_at_pos++;
				$lxc_name_fivth_at_removed = substr($lxc_name_fourth_at_removed, $fivth_at_pos, strlen($lxc_name_fourth_at_removed)-$fivth_at_pos);
				$sixth_at_pos = strpos($lxc_name_fivth_at_removed, "@");
				$sixth_at_pos++;
				$lxc_name_sixth_at_removed = substr($lxc_name_fivth_at_removed, $sixth_at_pos, strlen($lxc_name_fivth_at_removed)-$sixth_at_pos);
				$seventh_at_pos = strpos($lxc_name_sixth_at_removed, "@");
				$seventh_at_pos++;

				$lxc_short_name = trim(substr($lxc_vm, 0, $first_at_pos-1));
				$lxc_vm_state = trim(substr($lxc_name_first_at_removed, 0, $second_at_pos-1));
				$lxc_vm_mac = trim(substr($lxc_name_second_at_removed, 0, $third_at_pos-1));
				// get ip
				$lxc_resource = new resource();
				$lxc_resource->get_instance_by_mac($lxc_vm_mac);
				$lxc_vm_ip = $lxc_resource->ip;
				$lxc_vm_id = $lxc_resource->id;

				// fill the actions and set state icon
				$vm_actions = "";
				if (!strcmp($lxc_vm_state, "RUNNING")) {
					$state_icon="/openqrm/base/img/active.png";
					$vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$lxc_short_name&action=stop&lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"$thisfile?identifier[]=$lxc_short_name&action=restart&lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Restart</a>&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"lxc-storage-vm-config.php?lxc_server_name=$lxc_short_name&lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Change</a></nobr>";
				} else {
					$state_icon="/openqrm/base/img/off.png";
					$vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$lxc_short_name&action=start&lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"lxc-storage-vm-net-config.php?lxc_server_name=$lxc_short_name&lxc_server_id=$lxc_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a>&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"$thisfile?identifier[]=$lxc_short_name&action=delete&lxc_server_id=$lxc_server_tmp->id&lxc_vm_mac_ar[$lxc_short_name]=$lxc_vm_mac\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a></nobr>";
				}

				$lxc_vm_registered[] = $lxc_short_name;
				$lxc_vm_count++;

				$arBody1[] = array(
					'lxc_vm_state' => "<img src=$state_icon><input type='hidden' name='lxc_vm_mac_ar[$lxc_short_name]' value=$lxc_vm_mac>",
					'lxc_vm_id' => $lxc_vm_id,
					'lxc_vm_name' => $lxc_short_name,
					'lxc_vm_ip' => $lxc_vm_ip,
					'lxc_vm_mac' => $lxc_vm_mac,
					'lxc_vm_actions' => $vm_actions,
				);

			}
		}
	}
	$table1->add_headrow("<input type='hidden' name='lxc_server_id' value=$appliance_id>");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->autosort = true;
	$table1->identifier_type = "checkbox";
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('start', 'stop', 'restart', 'delete', 'reload');
		$table1->identifier = 'lxc_vm_name';
	}
	$table1->max = $lxc_vm_count;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'lxc_server_table' => $table->get_string(),
		'lxc_server_id' => $lxc_server_resource->id,
		'lxc_server_name' => $lxc_server_resource->hostname,
		'lxc_vm_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




$output = array();
if(htmlobject_request('action') != '') {
	if (isset($_REQUEST['identifier'])) {
		switch (htmlobject_request('action')) {
			case 'select':
				foreach($_REQUEST['identifier'] as $id) {
					$output[] = array('label' => 'LXC Storage VM Manager', 'value' => lxc_server_display($id));
				}
				break;
			case 'reload':
				foreach($_REQUEST['identifier'] as $id) {
					$output[] = array('label' => 'LXC Storage VM Manager', 'value' => lxc_server_display($id));
				}
				break;
		}
	} else {
		$output[] = array('label' => 'LXC Storage VM Manager', 'value' => lxc_server_select());
	}
} else if (strlen($lxc_server_id)) {
	$output[] = array('label' => 'LXC Storage VM Manager', 'value' => lxc_server_display($lxc_server_id));
} else  {
	$output[] = array('label' => 'LXC Storage VM Manager', 'value' => lxc_server_select());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
