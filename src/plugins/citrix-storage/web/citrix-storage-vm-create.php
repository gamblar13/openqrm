<!doctype html>
<html lang="en">
<head>
	<title>Citrix-storage XenServer Create VM</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
	<link rel="stylesheet" type="text/css" href="citrix-storage.css" />
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
require_once "$RootDir/include/htmlobject.inc.php";
global $OPENQRM_SERVER_BASE_DIR;
global $RESOURCE_INFO_TABLE;
$refresh_delay=1;
$refresh_loop_max=40;

$citrix_storage_server_id = htmlobject_request('citrix_storage_server_id');
$citrix_storage_command = htmlobject_request('citrix_storage_command');
$citrix_storage_name = htmlobject_request('citrix_storage_name');
$citrix_storage_ram = htmlobject_request('citrix_storage_ram');
$citrix_storage_mac = htmlobject_request('citrix_storage_mac');
$citrix_storage_cpus = htmlobject_request('citrix_storage_cpus');
$citrix_storage_template = htmlobject_request('citrix_storage_template');
global $citrix_storage_server_id;
global $citrix_storage_command;
global $citrix_storage_name;
global $citrix_storage_server_id;
global $citrix_storage_ram;
global $citrix_storage_mac;
global $citrix_storage_template;
global $citrix_storage_cpus;

// place for the citrix-storage stat files
$citrix_storage_dir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix-storage/citrix-storage-stat';


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect_mgmt($strMsg, $currenttab = 'tab0') {
	global $citrix_storage_server_id;
	$url = 'citrix-storage-vm-manager.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&action=reload&citrix_storage_server_id='.$citrix_storage_server_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


function redirect($strMsg, $currenttab = 'tab0') {
	global $thisfile;
	global $citrix_storage_server_id;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_storage_server_id='.$citrix_storage_server_id;
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



// check user input
function validate_input($var, $type) {
	switch ($type) {
		case 'string':
			// remove allowed chars
			$var = str_replace(".", "", $var);
			$var = str_replace("-", "", $var);
			$var = str_replace("_", "", $var);
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



// Dom0 actions
$strMsg = '';
if(htmlobject_request('citrix_storage_command') != '') {
	switch (htmlobject_request('citrix_storage_command')) {

		case 'new':
			if (!strlen($citrix_storage_server_id)) {
				$strMsg .= "Citrix-storage XenServer server-id not set. Not adding new VM!";
				redirect($strMsg, "tab0");
			}
			if (!strlen($citrix_storage_name)) {
				$strMsg .= "Citrix-storage XenServer VM name not set. Not adding new VM!";
				redirect($strMsg, "tab0", $thisfile);
			} else if (!validate_input($citrix_storage_name, 'string')) {
				$strMsg .= "Invalid Citrix-storage XenServer VM name. Not adding new VM!<br>(allowed characters are [a-z][A-z][0-9].-_)";
				redirect($strMsg, "tab0");
			}
			if (!strlen($citrix_storage_ram)) {
				$strMsg .= "Citrix-storage XenServer VM memory not set. Not adding new VM!";
				redirect($strMsg, "tab0", $thisfile);
			} else if (!validate_input($citrix_storage_ram, 'number')) {
				$strMsg .= "Invalid Citrix-storage XenServer VM memory. Not adding new VM!";
				redirect($strMsg, "tab0");
			}
			if (!strlen($citrix_storage_mac)) {
				$strMsg .= "Citrix-storage XenServer mac-address not set. Not adding new VM!";
				redirect($strMsg, "tab0");
			}
			if (!strlen($citrix_storage_template)) {
				$strMsg .= "Citrix-storage XenServer VM template not set. Not adding new VM!";
				redirect($strMsg, "tab0");
			}
			// check for cpu count is int
			if (!strlen($citrix_storage_cpus)) {
				$strMsg .= "Empty vm cpu number. Not adding new VM!";
				redirect($strMsg, "tab0");
			}
			if (!validate_input($citrix_storage_cpus, 'number')) {
				$strMsg .= "Invalid vm cpu number. Not adding new VM!";
				redirect($strMsg, "tab0");
			}

			show_progressbar();
			$citrix_storage_appliance = new appliance();
			$citrix_storage_appliance->get_instance_by_id($citrix_storage_server_id);
			$citrix_storage = new resource();
			$citrix_storage->get_instance_by_id($citrix_storage_appliance->resources);
			$citrix_storage_server_ip = $citrix_storage->ip;
			 // already authenticated ?
			$citrix_storage_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix-storage/citrix-storage-stat/citrix-storage-host.pwd.".$citrix_storage_server_ip;
			if (!file_exists($citrix_storage_auth_file)) {
				$strMsg .= "Citrix-storage XenServer not yet authenticated. Please authenticate !";
				redirect($strMsg, "tab0");
			}
			// remove current stat file
			$statfile="citrix-storage-stat/citrix-storage-vm.lst.".$citrix_storage_server_ip;
			if (file_exists($statfile)) {
				unlink($statfile);
			}
			// add resource + type + vhostid
			$resource = new resource();
			$resource_id=openqrm_db_get_free_id('resource_id', $RESOURCE_INFO_TABLE);
			$resource_ip="0.0.0.0";
			// prepare command
			$citrix_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage-vm create -i $citrix_storage_server_ip -n $citrix_storage_name -r $citrix_storage_ram -m $citrix_storage_mac -c $citrix_storage_cpus -t $citrix_storage_template";
			// send command to the openQRM-server
			$openqrm_server->send_command("openqrm_server_add_resource $resource_id $citrix_storage_mac $resource_ip");
			// set resource type
			$virtualization = new virtualization();
			$virtualization->get_instance_by_type("citrix-storage-vm");
			// add to openQRM database
			$resource_fields["resource_id"]=$resource_id;
			$resource_fields["resource_ip"]=$resource_ip;
			$resource_fields["resource_mac"]=$citrix_storage_mac;
			$resource_fields["resource_localboot"]=0;
			$resource_fields["resource_vtype"]=$virtualization->id;
			$resource_fields["resource_vhostid"]=$citrix_storage->id;
			$resource->add($resource_fields);
			// give some time for the new-resource hooks
			sleep(5);
			$openqrm_server->send_command($citrix_storage_command);
			// wait for statfile to appear again
			if (!wait_for_statfile($statfile)) {
				$strMsg .= "Error while creating Citrix-storage XenServer VM $citrix_storage_name! Please check the Event-Log<br>";
			} else {
				$strMsg .= "Created Citrix-storage XenServer VM $citrix_storage_name<br>";
			}
			redirect_mgmt($strMsg, "tab0");
			break;

	}
}

if (!strlen($citrix_storage_server_id)) {
	echo "ERROR: server-id not set <br>";
	exit(1);
}

// get template list
$citrix_storage_appliance = new appliance();
$citrix_storage_appliance->get_instance_by_id($citrix_storage_server_id);
$citrix_storage = new resource();
$citrix_storage->get_instance_by_id($citrix_storage_appliance->resources);
$citrix_storage_server_ip = $citrix_storage->ip;
 // already authenticated ?
$citrix_storage_auth_file=$_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/citrix-storage/citrix-storage-stat/citrix-storage-host.pwd.".$citrix_storage_server_ip;
if (!file_exists($citrix_storage_auth_file)) {
	$strMsg .= "Citrix-storage XenServer not yet authenticated. Please authenticate !";
	redirect_mgmt($strMsg, "tab0");
}
// remove current stat file
$template_list="citrix-storage-stat/citrix-storage-template.lst.".$citrix_storage_server_ip;
global $template_list;
if (file_exists($template_list)) {
	unlink($template_list);
}
// send command
$citrix_storage_command="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage-vm post_template_list -i $citrix_storage_server_ip";
$openqrm_server->send_command($citrix_storage_command);
// wait for statfile to appear again
if (!wait_for_statfile($template_list)) {
	echo "Error while getting list of templates from Citrix-storage XenServer Host $citrix_storage_server_id ! Please check the Event-Log<br>";
	exit(1);
}






function citrix_storage_create() {
	global $citrix_storage_server_id;
	global $template_list;
	global $thisfile;
	$citrix_storage = new resource();
	$citrix_storage->get_instance_by_id($citrix_storage_server_id);
	$resource_mac_gen = new resource();
	$resource_mac_gen->generate_mac();
	$suggested_mac = $resource_mac_gen->mac;
	$back_link = "<a href=\"citrix-storage-manager.php?action=refresh&identifier[]=$citrix_storage_server_id\">Back</a>";
	// read template file
	$template_list_select = array();
	if (file_exists($template_list)) {
		$citrix_storage_template_list_content=file($template_list);
		foreach ($citrix_storage_template_list_content as $citrix_storage_template) {
			$citrix_storage_template_params_arr = explode(":", $citrix_storage_template);
			$citrix_storage_template_uuid = $citrix_storage_template_params_arr[0];
			$citrix_storage_display_template_name = trim(str_replace("@", " ", $citrix_storage_template_params_arr[1]));
			// echo "-> $citrix_storage_template_uuid , $citrix_storage_display_template_name<br>";
			$template_list_select[] = array("value" => $citrix_storage_template_uuid, "label" => $citrix_storage_display_template_name);
		}
	}
	// cpus array for the select
	$cpu_identifier_array = array();
	$cpu_identifier_array[] = array("value" => "1", "label" => "1 CPU");
	$cpu_identifier_array[] = array("value" => "2", "label" => "2 CPUs");
	$cpu_identifier_array[] = array("value" => "3", "label" => "3 CPUs");
	$cpu_identifier_array[] = array("value" => "4", "label" => "4 CPUs");


	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-storage-vm-create.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'backlink' => $back_link,
		'citrix_storage_server_id' => $citrix_storage_server_id,
		'citrix_storage_server_name' => htmlobject_input('citrix_storage_name', array("value" => '', "label" => 'VM name'), 'text', 20),
		'citrix_storage_server_mac' => htmlobject_input('citrix_storage_mac', array("value" => $suggested_mac, "label" => 'Mac address'), 'text', 20),
		'citrix_storage_server_ram' => htmlobject_input('citrix_storage_ram', array("value" => '512', "label" => 'Memory (MB)'), 'text', 10),
		'citrix_storage_server_cpus' => htmlobject_select('citrix_storage_cpus', $cpu_identifier_array, 'CPUs'),
		'hidden_citrix_storage_server_id' => "<input type=hidden name=citrix_storage_server_id value=$citrix_storage_server_id><input type=hidden name=citrix_storage_command value='new'>",
		'template_list_select' => htmlobject_select('citrix_storage_template', $template_list_select, 'VM Template'),
		'submit' => htmlobject_input('action', array("value" => 'new', "label" => 'Create'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Citrix-storage Create VM', 'value' => citrix_storage_create());
}

echo htmlobject_tabmenu($output);

?>


