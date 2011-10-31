<!doctype html>
<html lang="en">
<head>
	<title>Nagios manager</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
	<link rel="stylesheet" type="text/css" href="nagios.css" />
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
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special nagios classes
require_once "$RootDir/plugins/nagios3/class/nagios3_service.class.php";
require_once "$RootDir/plugins/nagios3/class/nagios3_host.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
$refresh_delay=1;
$refresh_loop_max=20;



function redirect($strMsg, $nagios_id) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redir=yes&nagios_id='.$nagios_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
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



// action !
$strMsg = '';
if(htmlobject_request('redir') != 'yes') {
	if(htmlobject_request('action') != '') {
		switch (htmlobject_request('action')) {
			case 'set':
				show_progressbar();
				$nagios_id = htmlobject_request('nagios_id');
				// get the appliance name
				$appliance = new appliance();
				$appliance->get_instance_by_id($nagios_id);
				$appliance_id = $nagios_id;
				$appliance_name = $appliance->name;
				$resource_id = $appliance->resources;

				$nagios_service_list = '';
				$nagios_service_id_list = '';
				if(htmlobject_request('identifier') != '') {
					$nagios_service_identifier = htmlobject_request('identifier');
					foreach($nagios_service_identifier as $service_id) {
						$nagios_service = new nagios3_service();
						$nagios_service->get_instance_by_id($service_id);
						$appliance_service = $nagios_service->port;
						$nagios_service_list = $nagios_service_list.",".$appliance_service;
						$nagios_service_id_list = $nagios_service_id_list.",".$service_id;
						$strMsg .="Activated Nagios Service check $service_id (port $appliance_service) for appliance $appliance_name<br>";
					}
					$nagios_service_list = substr($nagios_service_list, 1);
					$nagios_service_id_list = substr($nagios_service_id_list, 1);

					// send command
					if (strlen($nagios_service_list)) {
						// autoselect ? active ? then we directly create the new nagios conf
						if (($resource_id >= 0) && (($appliance->state == "active")) || ($resource_id == 0)) {
							$resource = new resource();
							$resource->get_instance_by_id($resource_id);
							$resource_ip = $resource->ip;
							$nagios_service_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager add -n ".$appliance_name." -i ".$resource_ip." -p ".$nagios_service_list;
							$openqrm_server->send_command($nagios_service_command);
						} else {
							$strMsg .="Appliance is not active!<br>";
						}

						// here we map the service to the appliance
						$nagios_host = new nagios3_host();
						$nagios_host->get_instance_by_appliance_id($appliance_id);
						if (!strlen($nagios_host->id)) {
							// add
							$fields = array();
							$fields["nagios3_host_id"] = openqrm_db_get_free_id('nagios3_host_id', $nagios_host->_db_table);
							$fields['nagios3_appliance_id'] = $appliance_id;
							$fields['nagios3_appliance_services'] = "";
							$nagios_host->add($fields);
						}
						$nagios_host->get_instance_by_appliance_id($appliance_id);
						// update + set services
						$new_appliance_services = $nagios_service_id_list;
						// do we want to keep existing services ? no
						// $current_appliance_services = $nagios_host->appliance_services;
						// if (strlen($current_appliance_services)) {
						//     $new_appliance_services = $current_appliance_services.",".$nagios_service_id_list;
						// } else {
						//     $new_appliance_services = $nagios_service_id_list;
						// }
						$ufields = array();
						$ufields['nagios3_appliance_services'] = $new_appliance_services;
						$nagios_host->update($nagios_host->id, $ufields);

					} else {
						$strMsg .="No service(s) selected!<br>";
					}
				}
				redirect($strMsg, $nagios_id);
				break;



			case 'unset':
				show_progressbar();
				$nagios_id = htmlobject_request('nagios_id');
				// get the appliance name
				$appliance = new appliance();
				$appliance->get_instance_by_id($nagios_id);
				$appliance_id = $nagios_id;
				$appliance_name = $appliance->name;
				$resource_id = $appliance->resources;

				// here we unmap the service from the appliance
				$nagios_host = new nagios3_host();
				$nagios_host->get_instance_by_appliance_id($appliance_id);
				// update + set services
				// $current_appliance_services = $nagios_host->appliance_services;
				$active_nagios_services = explode(',', $nagios_host->appliance_services);
				$nagios_service_list = '';
				$nagios_service_id_list = '';
				if(htmlobject_request('identifier') != '') {
					$nagios_service_identifier = htmlobject_request('identifier');
					foreach($nagios_service_identifier as $service_id) {
						$nagios_service = new nagios3_service();
						$nagios_service->get_instance_by_id($service_id);
						$appliance_service = $nagios_service->port;
						$nagios_service_list = $nagios_service_list.",".$appliance_service;
						$nagios_service_id_list = $nagios_service_id_list.",".$service_id;
						$strMsg .="Removed Nagios Service check $service_id (port $appliance_service) for appliance $appliance_name<br>";

						// remove service from appliance, update the appliance object before
						$new_appliance_services="";
						$nagios_host->get_instance_by_appliance_id($appliance_id);
						$active_nagios_services = explode(',', $nagios_host->appliance_services);
						foreach($active_nagios_services as $active_service_id) {
							if (strlen($active_service_id)) {
								if ($active_service_id != $service_id) {
									$new_appliance_services = $new_appliance_services.",".$active_service_id;
								}
							}
						}


						$new_appliance_services = substr($new_appliance_services, 1);
						$ufields = array();
						$ufields['nagios3_appliance_services'] = $new_appliance_services;
						$nagios_host->update($nagios_host->id, $ufields);

					}
					$nagios_service_list = substr($nagios_service_list, 1);
					$nagios_service_id_list = substr($nagios_service_id_list, 1);

					// send command
					if (strlen($nagios_service_list)) {
						// autoselect ? active ? then we directly create the new nagios conf
						if (($resource_id >= 0) && (($appliance->state == "active")) || ($resource_id == 0)) {
							$resource = new resource();
							$resource->get_instance_by_id($resource_id);
							$resource_ip = $resource->ip;
							$nagios_service_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/nagios3/bin/openqrm-nagios-manager remove_service -n ".$appliance_name." -p ".$nagios_service_list;
							$openqrm_server->send_command($nagios_service_command);
						} else {
							$strMsg .="Appliance is not active!<br>";
						}

					} else {
						$strMsg .="No service(s) selected!<br>";
					}
				}
				redirect($strMsg, $nagios_id);
				break;

		}
	}
}



function nagios_select() {

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

	$nagios_count=0;
	$arBody = array();
	$nagios_tmp = new appliance();
	$nagios_array = $nagios_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($nagios_array as $index => $nagios_db) {
		$nagios_app = new appliance();
		$nagios_app->get_instance_by_id($nagios_db["appliance_id"]);
		$nagios_app_resources=$nagios_db["appliance_resources"];
		$nagios_resource = new resource();
		$nagios_resource->get_instance_by_id($nagios_app_resources);

		$nagios_count++;
		// active or inactive
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		$resource_icon_default="/openqrm/base/img/resource.png";
		if ($nagios_app->stoptime == 0 || $nagios_app_resources == 0)  {
			$state_icon=$active_state_icon;
		} else {
			$state_icon=$inactive_state_icon;
		}

		$arBody[] = array(
			'appliance_state' => "<img src=$state_icon>",
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $nagios_db["appliance_id"],
			'appliance_name' => $nagios_db["appliance_name"],
			'appliance_resource_id' => $nagios_resource->id,
			'appliance_resource_ip' => $nagios_resource->ip,
			'appliance_comment' => $nagios_db["appliance_comment"],
		);
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
	$table->max = $nagios_tmp->get_count();
	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'nagios-select.tpl.php');
	$t->setVar(array(
		'nagios_appliance_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function nagios_display($nagios_id) {

	global $OPENQRM_USER;
	global $RootDir;
	global $thisfile;

	$table = new htmlobject_db_table('nagios3_service_id');
	$appliance = new appliance();
	$appliance->get_instance_by_id($nagios_id);
	$appliance_name = $appliance->name;
	$nagios_host = new nagios3_host();
	$nagios_host->get_instance_by_appliance_id($nagios_id);
	if (strlen($nagios_host->id)) {
		$active_nagios_services = explode(',', $nagios_host->appliance_services);
	}

	$arHead = array();
	$arHead['nagios3_service_icon'] = array();
	$arHead['nagios3_service_icon']['title'] ='';
	$arHead['nagios3_service_icon']['sortable'] = false;

	$arHead['nagios3_service_id'] = array();
	$arHead['nagios3_service_id']['title'] ='ID';

	$arHead['nagios3_service_name'] = array();
	$arHead['nagios3_service_name']['title'] ='Name';

	$arHead['nagios3_service_type'] = array();
	$arHead['nagios3_service_type']['title'] ='Type';

	$arHead['nagios3_service_port'] = array();
	$arHead['nagios3_service_port']['title'] ='Port';
	$arHead['nagios3_service_port']['sortable'] = false;

	$arHead['nagios3_service_description'] = array();
	$arHead['nagios3_service_description']['title'] ='Description';

	$nagios_count=0;
	$arBody = array();

	$active_appliance_nagios_services = array();

	$nagios_tmp = new nagios3_service();
	$nagios_array = $nagios_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($nagios_array as $index => $nagios_db) {
		$nagios_count++;
		// active or inactive
		$resource_icon_default="/openqrm/base/img/resource.png";

		// check for pre-set identifier
		$nagios_service_id = $nagios_db["nagios3_service_id"];
		if (in_array($nagios_service_id, $active_nagios_services)) {
			$active_appliance_nagios_services[] = $nagios_service_id;
		}

		$arBody[] = array(
			'nagios3_service_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'nagios3_service_id' => $nagios_db["nagios3_service_id"],
			'nagios3_service_name' => $nagios_db["nagios3_service_name"],
			'nagios3_service_type' => $nagios_db["nagios3_service_type"],
			'nagios3_service_port' => $nagios_db["nagios3_service_port"],
			'nagios3_service_description' => $nagios_db["nagios3_service_description"],
		);
	}

	$table->add_headrow("<input type=\"hidden\" name=\"nagios_id\" value=\"$nagios_id\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->limit = 1000;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->identifier_checked = $active_appliance_nagios_services;
	$table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('set', 'unset');
		$table->identifier = 'nagios3_service_id';
	}
	$table->max = $nagios_count;
	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'nagios-apply.tpl.php');
	$t->setVar(array(
		'nagios_services_table' => $table->get_string(),
		'appliance_name' => $appliance_name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$nagios_id = htmlobject_request('nagios_id');
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			foreach($_REQUEST['identifier'] as $id) {
				$output[] = array('label' => 'Nagios Manager', 'value' => nagios_display($id));
			}
			break;
		case 'update':
			$output[] = array('label' => 'Nagios Manager', 'value' => nagios_display($nagios_id));
			break;
	}
} else if (strlen($nagios_id)) {
	$output[] = array('label' => 'Nagios Manager', 'value' => nagios_display($nagios_id));
} else  {
	$output[] = array('label' => 'Nagios Manager', 'value' => nagios_select());
}


?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
