<!doctype html>
<html lang="en">
<head>
	<title>OpenVZ manager</title>
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
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

$openvz_server_id = htmlobject_request('openvz_server_id');
$openvz_vm_mac = htmlobject_request('openvz_vm_mac');
$openvz_vm_mac_ar = htmlobject_request('openvz_vm_mac_ar');
$action=htmlobject_request('action');
$openvz_migrate_to_id_ar = htmlobject_request('openvz_migrate_to_id');
global $openvz_server_id;
global $openvz_vm_mac;
global $openvz_vm_mac_ar;
global $openvz_migrate_to_id_ar;
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
	global $openvz_server_id;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&openvz_server_id='.$openvz_server_id;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function openvz_htmlobject_select($name, $value, $title = '', $selected = '') {
	$html = new htmlobject_select();
	$html->name = $name;
	$html->title = $title;
	$html->selected = $selected;
	$html->text_index = array("value" => "value", "text" => "label");
	$html->text = $value;
	return $html->get_string();
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
					foreach($_REQUEST['identifier'] as $openvz_server_id) {
						show_progressbar();
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$openvz_server_resource_id = $openvz_server->id;
						$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during refreshing VM list ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Refreshing VM list<br>";
						}
						redirect($strMsg, "tab0");
						exit(0);
					}
				}
				break;

			case 'reload':
				show_progressbar();
				$openvz_appliance = new appliance();
				$openvz_appliance->get_instance_by_id($openvz_server_id);
				$openvz_server = new resource();
				$openvz_server->get_instance_by_id($openvz_appliance->resources);
				$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm post_vm_list -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
				// remove current stat file
				$openvz_server_resource_id = $openvz_server->id;
				$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
				if (file_exists($statfile)) {
					unlink($statfile);
				}
				// send command
				$openvz_server->send_command($openvz_server->ip, $resource_command);
				// and wait for the resulting statfile
				if (!wait_for_statfile($statfile)) {
					$strMsg .= "Error during refreshing VM list ! Please check the Event-Log<br>";
				} else {
					$strMsg .="Refreshing VM list<br>";
				}
				redirect($strMsg, "tab0");
				break;


			case 'start':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $openvz_server_name) {
						show_progressbar();
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm start -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$openvz_server_resource_id = $openvz_server->id;
						$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during starting $openvz_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Starting $openvz_server_name <br>";
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
					foreach($_REQUEST['identifier'] as $openvz_server_name) {
						show_progressbar();
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm stop -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$openvz_server_resource_id = $openvz_server->id;
						$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during stopping $openvz_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Stopping $openvz_server_name <br>";
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
					foreach($_REQUEST['identifier'] as $openvz_server_name) {
						show_progressbar();
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm reboot -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$openvz_server_resource_id = $openvz_server->id;
						$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during restarting $openvz_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Restarting $openvz_server_name <br>";
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
					foreach($_REQUEST['identifier'] as $openvz_server_name) {
						show_progressbar();
						// check if the resource still belongs to an appliance, if yes we do not remove it
						$openvz_vm_mac = $openvz_vm_mac_ar[$openvz_server_name];
						$openvz_resource = new resource();
						$openvz_resource->get_instance_by_mac($openvz_vm_mac);
						$openvz_vm_id=$openvz_resource->id;
						$resource_is_used_by_appliance = "";
						$remove_error = 0;
						$appliance = new appliance();
						$appliance_id_list = $appliance->get_all_ids();
						foreach($appliance_id_list as $appliance_list) {
							$appliance_id = $appliance_list['appliance_id'];
							$app_resource_remove_check = new appliance();
							$app_resource_remove_check->get_instance_by_id($appliance_id);
							if ($app_resource_remove_check->resources == $openvz_vm_id) {
								$resource_is_used_by_appliance .= $appliance_id." ";
								$remove_error = 1;
							}
						}
						if ($remove_error == 1) {
							$strMsg .= "VM Resource id ".$openvz_vm_id." is used by appliance(s): ".$resource_is_used_by_appliance." <br>";
							$strMsg .= "Not removing VM resource id ".$openvz_vm_id." !<br>";
							continue;
						}
						// remove vm
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm delete -n $openvz_server_name -u $OPENQRM_ADMIN->name -p $OPENQRM_ADMIN->password";
						// remove current stat file
						$openvz_server_resource_id = $openvz_server->id;
						$statfile="openvz-stat/".$openvz_server_resource_id.".vm_list";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						$openvz_resource->remove($openvz_vm_id, $openvz_vm_mac);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during removing $openvz_server_name ! Please check the Event-Log<br>";
						} else {
							$strMsg .="Removed $openvz_server_name and its resource $openvz_vm_id<br>";
						}
					}
					redirect($strMsg, "tab0");
				} else {
					$strMsg ="No virtual machine selected<br>";
					redirect($strMsg, "tab0");
				}
				break;


			case 'migrate':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $openvz_server_name) {
						show_progressbar();
						// gather some infos
						$openvz_vm_mac = $openvz_vm_mac_ar[$openvz_server_name];
						$openvz_resource = new resource();
						$openvz_resource->get_instance_by_mac($openvz_vm_mac);
						$openvz_vm_id=$openvz_resource->id;
						// start as incoming on the destination host
						if (!isset($openvz_migrate_to_id_ar[$openvz_server_name])) {
							continue;
						}
						$openvz_destination_host_resource_id = $openvz_migrate_to_id_ar[$openvz_server_name];
						$openvz_destination_host_resource = new resource();
						$openvz_destination_host_resource->get_instance_by_id($openvz_destination_host_resource_id);
						$openvz_destination_host_resource_ip = $openvz_destination_host_resource->ip;
						// send migrate to source host
						$openvz_appliance = new appliance();
						$openvz_appliance->get_instance_by_id($openvz_server_id);
						$openvz_server = new resource();
						$openvz_server->get_instance_by_id($openvz_appliance->resources);
						$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/openvz-storage/bin/openqrm-openvz-storage-vm migrate -n ".$openvz_server_name." -i ".$openvz_destination_host_resource_ip." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;

						$statfile="openvz-stat/".$openvz_server_name.".vm_migrated_successfully";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						$openvz_server->send_command($openvz_server->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$strMsg .= "Error during migrating $openvz_server_name ! Please check the Event-Log<br>";
							continue;
						} else {
							$strMsg .= "Migrated VM ".$openvz_server_name." from ".$openvz_server->ip. " to ".$openvz_destination_host_resource_ip." <br>";
							unlink($statfile);
						}
						// we now have to also adjust the vhostid in the vm resource
						$resource_fields=array();
						$resource_fields["resource_vhostid"]=$openvz_destination_host_resource_id;
						$openvz_resource->update_info($openvz_resource->id, $resource_fields);

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





function openvz_server_select() {

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

	$openvz_server_count=0;
	$arBody = array();
	$virtualization = new virtualization();
	$virtualization->get_instance_by_type("openvz-storage");
	$openvz_server_tmp = new appliance();
	$openvz_server_array = $openvz_server_tmp->display_overview_per_virtualization($virtualization->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($openvz_server_array as $index => $openvz_server_db) {
		$openvz_server_resource = new resource();
		$openvz_server_resource->get_instance_by_id($openvz_server_db["appliance_resources"]);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$openvz_server_icon="/openqrm/base/plugins/openvz-storage/img/plugin.png";
		$state_icon="/openqrm/base/img/$openvz_server_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$openvz_server_icon)) {
			$resource_icon_default=$openvz_server_icon;
		}
		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $openvz_server_db["appliance_id"],
			'appliance_name' => $openvz_server_db["appliance_name"],
			'appliance_resource_id' => $openvz_server_resource->id,
			'appliance_resource_ip' => $openvz_server_resource->ip,
			'appliance_comment' => $openvz_server_db["appliance_comment"],
		);
		$openvz_server_count++;
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
	$table->max = $openvz_server_tmp->get_count_per_virtualization($virtualization->id);

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
	$t->setFile('tplfile', './tpl/' . 'openvz-storage-openvz-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'openvz_server_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function openvz_server_display($appliance_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;

	$table = new htmlobject_table_identifiers_checked('openvz_server_id');

	$arHead = array();
	$arHead['openvz_server_state'] = array();
	$arHead['openvz_server_state']['title'] ='State';

	$arHead['openvz_server_icon'] = array();
	$arHead['openvz_server_icon']['title'] ='Type';

	$arHead['openvz_server_id'] = array();
	$arHead['openvz_server_id']['title'] ='ID';

	$arHead['openvz_server_name'] = array();
	$arHead['openvz_server_name']['title'] ='Name';

	$arHead['openvz_server_resource_id'] = array();
	$arHead['openvz_server_resource_id']['title'] ='Res.ID';

	$arHead['openvz_server_resource_ip'] = array();
	$arHead['openvz_server_resource_ip']['title'] ='Ip';

	$arHead['openvz_server_comment'] = array();
	$arHead['openvz_server_comment']['title'] ='';

	$arHead['openvz_server_create'] = array();
	$arHead['openvz_server_create']['title'] ='';

	$openvz_server_count=1;
	$arBody = array();
	$openvz_server_tmp = new appliance();
	$openvz_server_tmp->get_instance_by_id($appliance_id);
	$openvz_server_resource = new resource();
	$openvz_server_resource->get_instance_by_id($openvz_server_tmp->resources);
	$resource_icon_default="/openqrm/base/img/resource.png";
	$openvz_server_icon="/openqrm/base/plugins/openvz-storage/img/plugin.png";
	$state_icon="/openqrm/base/img/$openvz_server_resource->state.png";
	if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
		$state_icon="/openqrm/base/img/unknown.png";
	}
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$openvz_server_icon)) {
		$resource_icon_default=$openvz_server_icon;
	}
	$openvz_server_create_button="<a href=\"openvz-storage-vm-create.php?openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration: none\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/enable.png\" border=\"0\"><b> VM</b></a>";
	// here we take the resource id as the identifier because
	// we need to run commands on the resource ip
	$arBody[] = array(
		'openvz_server_state' => "<img src=$state_icon>",
		'openvz_server_icon' => "<img width=24 height=24 src=$resource_icon_default>",
		'openvz_server_id' => $openvz_server_tmp->id,
		'openvz_server_name' => $openvz_server_tmp->name,
		'openvz_server_resource_id' => $openvz_server_resource->id,
		'openvz_server_resource_ip' => $openvz_server_resource->ip,
		'openvz_server_comment' => $openvz_server_tmp->comment,
		'openvz_server_create' => $openvz_server_create_button,
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
	$table->max = $openvz_server_count;

	// table 1
	$table1 = new htmlobject_table_builder('openvz_vm_id', '', '', '', 'vms');
	$arHead1 = array();
	$arHead1['openvz_vm_state'] = array();
	$arHead1['openvz_vm_state']['title'] ='State';
	$arHead1['openvz_vm_state']['sortable'] = false;

	$arHead1['openvz_vm_id'] = array();
	$arHead1['openvz_vm_id']['title'] ='Res.';

	$arHead1['openvz_vm_name'] = array();
	$arHead1['openvz_vm_name']['title'] ='VEID';

	$arHead1['openvz_vm_ip'] = array();
	$arHead1['openvz_vm_ip']['title'] ='IP';

	$arHead1['openvz_vm_hostname'] = array();
	$arHead1['openvz_vm_hostname']['title'] ='Name';

	$arHead1['openvz_vm_mac'] = array();
	$arHead1['openvz_vm_mac']['title'] ='MAC';

	$arHead1['openvz_vm_actions'] = array();
	$arHead1['openvz_vm_actions']['title'] ='Actions';
	$arHead1['openvz_vm_actions']['sortable'] = false;
	$arBody1 = array();


	// prepare list of all Host resource id for the migration select
	// we need a select with the ids/ips from all resources which
	// are used by appliances with openvz capabilities
	$openvz_host_resource_list = array();
	$appliance_list = new appliance();
	$appliance_list_array = $appliance_list->get_list();
	foreach ($appliance_list_array as $index => $app) {
		$appliance_openvz_host_check = new appliance();
		$appliance_openvz_host_check->get_instance_by_id($app["value"]);
		// only active appliances
		if ((!strcmp($appliance_openvz_host_check->state, "active")) || ($appliance_openvz_host_check->resources == 0)) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_openvz_host_check->virtualization);
			if ((strstr($virtualization->type, "openvz-storage")) && (!strstr($virtualization->type, "openvz-storage-vm"))) {
				$openvz_host_resource = new resource();
				$openvz_host_resource->get_instance_by_id($appliance_openvz_host_check->resources);
				// exclude source host
//				if ($openvz_host_resource->id == $openvz_server_resource->id) {
//					continue;
//				}
				// only active appliances
				if (!strcmp($openvz_host_resource->state, "active")) {
					$migration_select_label = "Res. ".$openvz_host_resource->id."/".$openvz_host_resource->ip;
					$openvz_host_resource_list[] = array("value"=>$openvz_host_resource->id, "label"=> $migration_select_label,);
				}
			}
		}
	}


	$openvz_server_vm_list_file="openvz-stat/$openvz_server_resource->id.vm_list";
	$openvz_vm_registered=array();
	$openvz_vm_count=0;
	if (file_exists($openvz_server_vm_list_file)) {
		$openvz_server_vm_list_content=file($openvz_server_vm_list_file);
		foreach ($openvz_server_vm_list_content as $index => $openvz_vm) {
			// find the vms
			if (!strstr($openvz_vm, "#")) {

				$first_at_pos = strpos($openvz_vm, "@");
				$first_at_pos++;
				$openvz_name_first_at_removed = substr($openvz_vm, $first_at_pos, strlen($openvz_vm)-$first_at_pos);
				$second_at_pos = strpos($openvz_name_first_at_removed, "@");
				$second_at_pos++;
				$openvz_name_second_at_removed = substr($openvz_name_first_at_removed, $second_at_pos, strlen($openvz_name_first_at_removed)-$second_at_pos);
				$third_at_pos = strpos($openvz_name_second_at_removed, "@");
				$third_at_pos++;
				$openvz_name_third_at_removed = substr($openvz_name_second_at_removed, $third_at_pos, strlen($openvz_name_second_at_removed)-$third_at_pos);
				$fourth_at_pos = strpos($openvz_name_third_at_removed, "@");
				$fourth_at_pos++;
				$openvz_name_fourth_at_removed = substr($openvz_name_third_at_removed, $fourth_at_pos, strlen($openvz_name_third_at_removed)-$fourth_at_pos);
				$fivth_at_pos = strpos($openvz_name_fourth_at_removed, "@");
				$fivth_at_pos++;
				$openvz_name_fivth_at_removed = substr($openvz_name_fourth_at_removed, $fivth_at_pos, strlen($openvz_name_fourth_at_removed)-$fivth_at_pos);
				$sixth_at_pos = strpos($openvz_name_fivth_at_removed, "@");
				$sixth_at_pos++;
				$openvz_name_sixth_at_removed = substr($openvz_name_fivth_at_removed, $sixth_at_pos, strlen($openvz_name_fivth_at_removed)-$sixth_at_pos);
				$seventh_at_pos = strpos($openvz_name_sixth_at_removed, "@");
				$seventh_at_pos++;

				$openvz_vm_id = trim(substr($openvz_name_first_at_removed, 0, $second_at_pos-1));
				$openvz_vm_state = trim(substr($openvz_name_second_at_removed, 0, $third_at_pos-1));
				$openvz_vm_mac = trim(substr($openvz_name_third_at_removed, 0, $fourth_at_pos-1));
				$openvz_vm_hostname = trim(substr($openvz_name_fourth_at_removed, 0, $fivth_at_pos-1));
				// get ip
				$openvz_resource = new resource();
				$openvz_resource->get_instance_by_mac($openvz_vm_mac);
				$openvz_vm_res_id = $openvz_resource->id;
				$openvz_vm_ip = $openvz_resource->ip;

				// fill the actions and set state icon
				$vm_actions = "";
				$mig_selected = array();
				if (!strcmp($openvz_vm_state, "running")) {
					$state_icon="/openqrm/base/img/active.png";
					$vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$openvz_vm_id&action=stop&openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/stop.png\" border=\"0\"> Stop</a>&nbsp;&nbsp;&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"$thisfile?identifier[]=$openvz_vm_id&action=restart&openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/img/active.png\" border=\"0\"> Restart</a>&nbsp;&nbsp;&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"openvz-storage-vm-config.php?openvz_server_name=$openvz_vm_id&openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a></nobr>";
					// migration
					$migration_select = openvz_htmlobject_select("openvz_migrate_to_id[$openvz_vm_id]", $openvz_host_resource_list, 'Migrate', $mig_selected);
					$vm_actions .= "<br>Migrate to : ".$migration_select;

				} else {
					$state_icon="/openqrm/base/img/off.png";
					$vm_actions = "<nobr><a href=\"$thisfile?identifier[]=$openvz_vm_id&action=start&openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/start.png\" border=\"0\"> Start</a>&nbsp;&nbsp;&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"openvz-storage-vm-net-config.php?openvz_server_name=$openvz_vm_id&openvz_server_id=$openvz_server_tmp->id\" style=\"text-decoration:none;\"><img height=16 width=16 src=\"/openqrm/base/plugins/aa_plugins/img/plugin.png\" border=\"0\"> Config</a>&nbsp;&nbsp;&nbsp;&nbsp;";
					$vm_actions .= "<a href=\"$thisfile?identifier[]=$openvz_vm_id&action=delete&openvz_server_id=$openvz_server_tmp->id&openvz_vm_mac_ar[$openvz_vm_id]=$openvz_vm_mac\" style=\"text-decoration:none;\"><img height=20 width=20 src=\"/openqrm/base/plugins/aa_plugins/img/disable.png\" border=\"0\"> Delete</a></nobr>";
				}

				$openvz_vm_registered[] = $openvz_vm_id;
				$openvz_vm_count++;

				$arBody1[] = array(
					'openvz_vm_state' => "<img src=$state_icon><input type='hidden' name='openvz_vm_mac_ar[$openvz_vm_id]' value=$openvz_vm_mac>",
					'openvz_vm_id' => $openvz_vm_res_id,
					'openvz_vm_name' => $openvz_vm_id,
					'openvz_vm_ip' => $openvz_vm_ip,
					'openvz_vm_hostname' => $openvz_vm_hostname,
					'openvz_vm_mac' => $openvz_vm_mac,
					'openvz_vm_actions' => $vm_actions,
				);

			}
		}
	}
	$table1->add_headrow("<input type='hidden' name='openvz_server_id' value=$appliance_id>");
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
		$table1->bottom = array('start', 'stop', 'restart', 'migrate', 'delete', 'reload');
		$table1->identifier = 'openvz_vm_name';
	}
	$table1->max = $openvz_vm_count;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'openvz-storage-vms.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'openvz_server_table' => $table->get_string(),
		'openvz_server_id' => $openvz_server_resource->id,
		'openvz_server_name' => $openvz_server_resource->hostname,
		'openvz_vm_table' => $table1->get_string(),
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
					$output[] = array('label' => 'OpenVZ VM Manager', 'value' => openvz_server_display($id));
				}
				break;
			case 'reload':
				foreach($_REQUEST['identifier'] as $id) {
					$output[] = array('label' => 'OpenVZ VM Manager', 'value' => openvz_server_display($id));
				}
				break;
		}
	} else {
		$output[] = array('label' => 'OpenVZ VM Manager', 'value' => openvz_server_select());
	}
} else if (strlen($openvz_server_id)) {
	$output[] = array('label' => 'OpenVZ VM Manager', 'value' => openvz_server_display($openvz_server_id));
} else  {
	$output[] = array('label' => 'OpenVZ VM Manager', 'value' => openvz_server_select());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
