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



function redirect($strMsg) {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redir=yes';
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
			case 'add':
				show_progressbar();
				$nagios3_add_service_port_arr = htmlobject_request('nagios3_add_service_port_arr');

				// for custom services/ports
				$nagios3_add_service_name = htmlobject_request('nagios3_add_service_name');
				if (strlen($nagios3_add_service_name)) {
					$service_name = $nagios3_add_service_name;
					$service_port = $nagios3_add_service_port_arr[0];
					$service_description = "No description available";
					// check for duplicates
					$nagios_service = new nagios3_service();
					$nagios_service->get_instance_by_name($service_name);
					if ($nagios_service->id != "") {
						$strMsg .="Service with name - $service_name already existing. Not adding!<br>";
						redirect($strMsg);
					}
					$nagios_service->get_instance_by_port($service_port);
					if ($nagios_service->id != "") {
						$strMsg .="Service with port - $service_port already existing. Not adding!<br>";
						redirect($strMsg);
					}
					// adding
					$fields = array();
					$fields["nagios3_service_id"] = openqrm_db_get_free_id('nagios3_service_id', $nagios_service->_db_table);
					$fields['nagios3_service_name'] = $service_name;
					$fields['nagios3_service_type'] = "tcp";
					$fields['nagios3_service_port'] = $service_port;
					$fields['nagios3_service_description'] = $service_description;
					$nagios_service->add($fields);
					$strMsg .="Added service Port $service_port - $service_name - $service_description<br>";

				} else {

					foreach($nagios3_add_service_port_arr as $nport) {
						$lines = file("/etc/services");
						foreach($lines as $line) {
							if (strstr($line, "/tcp")) {
								$line = trim($line);
								$service_start = strpos($line, "/tcp");
								$service_description_start = strpos($line, "#");
								if ($service_description_start > 0) {
									$service_description = substr($line, $service_description_start+2);
									// remove description
									$line = substr($line, 0, $service_description_start);
								} else {
									$service_description = "No description available";
								}
								// find /
								$first_slash = strpos($line, '/');
								$line = substr($line, 0, $first_slash);
								list($service_name, $service_port) = sscanf($line, "%s %d");
								if ($service_port == $nport) {
									break;
								}
							}
						}

						// check for duplicates
						$nagios_service = new nagios3_service();
						$nagios_service->get_instance_by_name($service_name);
						if ($nagios_service->id != "") {
							$strMsg .="Service with name - $service_name already existing. Not adding!<br>";
							continue;
						}
						$nagios_service->get_instance_by_port($service_port);
						if ($nagios_service->id != "") {
							$strMsg .="Service with port - $service_port already existing. Not adding!<br>";
							continue;
						}

						// adding
						$fields = array();
						$fields["nagios3_service_id"] = openqrm_db_get_free_id('nagios3_service_id', $nagios_service->_db_table);
						$fields['nagios3_service_name'] = $service_name;
						$fields['nagios3_service_type'] = "tcp";
						$fields['nagios3_service_port'] = $service_port;
						$fields['nagios3_service_description'] = $service_description;
						$nagios_service->add($fields);
						$strMsg .="Added service Port $nport - $service_name - $service_description<br>";
					}

				}
				redirect($strMsg);
				break;


			case 'remove':
				show_progressbar();
				if(htmlobject_request('identifier') != '') {
					$nagios_service = new nagios3_service();
					foreach(htmlobject_request('identifier') as $id) {

						$can_be_removed = true;
						// check if allowed to be removed
						$nagios_host = new nagios3_host();
						$nagios_host_list = $nagios_host->get_ids();
						foreach($nagios_host_list as $nagios_host_db) {
							$nagios_host_id = $nagios_host_db['nagios3_host_id'];
							$nagios_host->get_instance_by_id($nagios_host_id);
							$active_nagios_services = explode(',', $nagios_host->appliance_services);
							if (in_array($id, $active_nagios_services)) {
								$strMsg .= "Not removing Nagios service id $id. Appliance id $nagios_host->appliance_id is still using it! <br>";
								$can_be_removed = false;
							}
						}
						if ($can_be_removed == true) {
							$nagios_service->remove($id);
							$strMsg .= "Removed Nagios service id $id <br>";
						}
					}
				}
				redirect($strMsg);
				break;

			case 'update':
				show_progressbar();
				$nagios3_service_description_arr = htmlobject_request('nagios3_service_description');
				if(htmlobject_request('identifier') != '') {
					$nagios_service = new nagios3_service();
					foreach(htmlobject_request('identifier') as $id) {

						$updated_service_description = $nagios3_service_description_arr[$id];

						$fields = array();
						$fields['nagios3_service_description'] = $updated_service_description;
						$nagios_service->update($id, $fields);
						$strMsg .= "Updated description of Nagios service id $id<br>";
					}
				}
				redirect($strMsg);
				break;

		}
	}
}



function nagios_service_select() {

	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_table_builder('nagios3_service_id', '', '', '', 'select');

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

	// build the service select box
	$nagios3_add_service_select_box="<select name=\"nagios3_add_service_port_arr[]\" size=\"5\" multiple>";
	$lines = file("/etc/services");
	foreach($lines as $line) {
		if (strstr($line, "/tcp")) {
			$line = trim($line);
			$service_start = strpos($line, "/tcp");
			$service_description_start = strpos($line, "#");
			if ($service_description_start > 0) {
				$service_description = substr($line, $service_description_start+2);
				// remove description
				$line = substr($line, 0, $service_description_start);
			} else {
				$service_description = "No description available";
			}
			// find /
			$first_slash = strpos($line, '/');
			$line = substr($line, 0, $first_slash);
			list($service_name, $service_port) = sscanf($line, "%s %d");
			$nagios3_add_service_select_box .= "<option value=\"$service_port\">Port $service_port - $service_name - $service_description</option>";
		}
	}
	$nagios3_add_service_select_box .= "</select>";

	$nagios_count=0;
	$arBody = array();
	$nagios_tmp = new nagios3_service();
	$nagios_array = $nagios_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	foreach ($nagios_array as $index => $nagios_db) {
		$nagios_count++;
		// active or inactive
		$resource_icon_default="/openqrm/base/img/resource.png";
		$nagios_service_id = $nagios_db["nagios3_service_id"];
		$nagios_service_description = $nagios_db["nagios3_service_description"];

		$arBody[] = array(
			'nagios3_service_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'nagios3_service_id' => $nagios_db["nagios3_service_id"],
			'nagios3_service_name' => $nagios_db["nagios3_service_name"],
			'nagios3_service_type' => $nagios_db["nagios3_service_type"],
			'nagios3_service_port' => $nagios_db["nagios3_service_port"],
			'nagios3_service_description' => "<input type=text name=\"nagios3_service_description[$nagios_service_id]\" value=\"$nagios_service_description\" size=\"50\">",
		);
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('update', 'remove');
		$table->identifier = 'nagios3_service_id';
	}
	$table->max = $nagios_tmp->get_count();
	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'nagios-service-select.tpl.php');
	$t->setVar(array(
		'nagios_service_table' => $table->get_string(),
		'nagios_add_service_select_box' => $nagios3_add_service_select_box,
		'formaction' => $thisfile,
		'submit' => htmlobject_input('action', array("value" => 'add', "label" => 'Add'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
$output[] = array('label' => 'Nagios Service Manager', 'value' => nagios_service_select());

?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
