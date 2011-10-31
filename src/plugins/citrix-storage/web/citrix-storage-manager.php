<!doctype html>
<html lang="en">
<head>
	<title>Citrix-Storage Manager</title>
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
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/include/htmlobject.inc.php";


$citrix_storage_id = htmlobject_request('citrix_storage_id');
$citrix_volume_name=htmlobject_request('citrix_volume_name');
$citrix_volume_uuid=htmlobject_request('citrix_volume_uuid');
$citrix_volume_snap_name=htmlobject_request('citrix_volume_snap_name');
$citrix_volume_snap_size=htmlobject_request('citrix_volume_snap_size');
$citrix_volume_resize=htmlobject_request('citrix_volume_resize');
$citrix_sr_select=htmlobject_request('citrix_sr_select');

// to gather one of the deployment types within citrix-storage
$citrix_storage_type=htmlobject_request('type');

$action=htmlobject_request('action');
global $citrix_storage_id;
global $citrix_volume_name;
global $citrix_volume_snap_name;
global $citrix_volume_resize;
global $citrix_storage_type;
global $citrix_volume_uuid;
global $citrix_sr_select;

$refresh_delay=1;
$refresh_loop_max=20;

$event = new event();
global $event;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_SERVER_BASE_DIR;

function redirect_vdi($strMsg, $citrix_storage_id) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$citrix_storage_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function redirect_reload($strMsg, $citrix_storage_id) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&citrix_storage_id='.$citrix_storage_id;
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



// running the actions
$redir_msg = '';
if(htmlobject_request('redirect') != 'yes') {
	if(htmlobject_request('action') != '') {
		if ($OPENQRM_USER->role == "administrator") {

			switch (htmlobject_request('action')) {

				case 'select':
					if (isset($_REQUEST['identifier'])) {
						foreach($_REQUEST['identifier'] as $id) {
							show_progressbar();
							$storage = new storage();
							$storage->get_instance_by_id($id);
							$deployment = new deployment();
							$deployment->get_instance_by_id($storage->type);
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							// post vg status
							$storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage post_vdi -i ".$storage_resource->ip." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
							// remove current stat file
							$storage_resource_id = $storage_resource->id;
							$statfile="citrix-storage-stat/vdi.stat.".$storage_resource->ip;
							if (file_exists($statfile)) {
								unlink($statfile);
							}
							// send command
							$openqrm_server->send_command($storage_command);
							// and wait for the resulting statfile
							if (!wait_for_statfile($statfile)) {
								$redir_msg = "Error during selecting storage location ! Please check the Event-Log";
							} else {
								$redir_msg = "Displaying storage locations on storage id ".$id;
							}
							redirect_vdi($redir_msg, $id);
						}
					}
					break;


				case 'add':
					$citrix_volume_name = htmlobject_request('citrix_volume_name');
					show_progressbar();
					if (!strlen($citrix_sr_select)) {
						$redir_msg = "Got emtpy SR name. Not adding ...";
						redirect_reload($redir_msg, $citrix_storage_id);
						exit(0);
					}
					if (!strlen($citrix_volume_name)) {
						$redir_msg = "Got emtpy logical VDI name. Not adding ...";
						redirect_reload($redir_msg, $citrix_storage_id);
						exit(0);
					} else if (!validate_input($citrix_volume_name, 'string')) {
						$redir_msg = "Got invalid logical VDI name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
						redirect_reload($redir_msg, $citrix_storage_id);
						exit(0);
					}
					$citrix_volume_size = htmlobject_request('citrix_volume_size');
					if (!strlen($citrix_volume_size)) {
						$citrix_volume_size=2000;
					} else if (!validate_input($citrix_volume_size, 'number')) {
						$redir_msg = "Got invalid logical VDI size. Not adding ...";
						redirect_reload($redir_msg, $citrix_storage_id);
						exit(0);
					}
					$storage = new storage();
					$storage->get_instance_by_id($citrix_storage_id);
					$storage_resource = new resource();
					$storage_resource->get_instance_by_id($storage->resource_id);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage add -i ".$storage_resource->ip." -n ".$citrix_volume_name." -x ".$citrix_sr_select." -m ".$citrix_volume_size." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
					// remove current stat file
					$storage_resource_id = $storage_resource->id;
					$statfile="citrix-storage-stat/vdi.stat.".$storage_resource->ip;
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$openqrm_server->send_command($resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$redir_msg = "Error during adding VDI $citrix_volume_name to storage location $citrix_storage_location ! Please check the Event-Log";
					} else {
						$redir_msg = "Added VDI $citrix_volume_name to storage location $citrix_storage_location";
					}
					redirect_reload($redir_msg, $citrix_storage_id);
					break;

				case 'remove':
					if (isset($_REQUEST['identifier'])) {
						show_progressbar();
						foreach($_REQUEST['identifier'] as $citrix_volume_uuid) {
							$storage = new storage();
							$storage->get_instance_by_id($citrix_storage_id);
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							$deployment = new deployment();
							$deployment->get_instance_by_id($storage->type);
							$storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage remove -i ".$storage_resource->ip." -r ".$citrix_volume_uuid." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
							// remove current stat file
							$storage_resource_id = $storage_resource->id;
							$statfile="citrix-storage-stat/vdi.stat.".$storage_resource->ip;
							if (file_exists($statfile)) {
								unlink($statfile);
							}
							// send command
							$openqrm_server->send_command($storage_command);
							// and wait for the resulting statfile
							if (!wait_for_statfile($statfile)) {
								$redir_msg .= "Error during removing VDI ".$citrix_volume_uuid." from storage ID ".$citrix_storage_id."! Please check the Event-Log<br>";
							} else {
								$redir_msg .= "Removed VDI $citrix_volume_uuid from storage ID ".$citrix_storage_id."<br>";
							}
						}
						redirect_reload($redir_msg, $citrix_storage_id);
					} else {
						$redir_msg = "No storage location selected. Skipping removal !";
						redirect_reload($redir_msg, $citrix_storage_id);
					}
					break;


				case 'reload':
					show_progressbar();
					$storage = new storage();
					$storage->get_instance_by_id($citrix_storage_id);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$storage_resource = new resource();
					$storage_resource->get_instance_by_id($storage->resource_id);
					// post lv status
					$storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage post_vdi -i ".$storage_resource->ip." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
					// remove current stat file
					$storage_resource_id = $storage_resource->id;
					$statfile="citrix-storage-stat/vdi.stat.".$storage_resource->ip;
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$openqrm_server->send_command($storage_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$redir_msg = "Error during displaying volumes on storage! Please check the Event-Log";
					} else {
						$redir_msg = "Displaying volumes on storage";
					}
					redirect_reload($redir_msg, $citrix_storage_id);
					break;


				case 'clone':
					$citrix_action = htmlobject_request('action');
					if (strlen($citrix_volume_snap_name)) {
						show_progressbar();
						if (!strlen($citrix_volume_name)) {
							$redir_msg = "Got emtpy VDI name. Not adding ...";
							redirect_reload($redir_msg, $citrix_storage_id);
							exit(0);
						} else if (!validate_input($citrix_volume_name, 'string')) {
							$redir_msg = "Got invalid VDI name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
							redirect_reload($redir_msg, $citrix_storage_id);
							exit(0);
						}

						if (!strlen($citrix_volume_snap_name)) {
							$redir_msg = "Got emtpy VDI clone name. Not adding ...";
							redirect_reload($redir_msg, $citrix_storage_id);
							exit(0);
						} else if (!validate_input($citrix_volume_snap_name, 'string')) {
							$redir_msg = "Got invalid VDI clone name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
							redirect_reload($redir_msg, $citrix_storage_id);
							exit(0);
						}

						// snap/clone
						$storage = new storage();
						$storage->get_instance_by_id($citrix_storage_id);
						$storage_resource = new resource();
						$storage_resource->get_instance_by_id($storage->resource_id);
						$deployment = new deployment();
						$deployment->get_instance_by_id($storage->type);
						$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/citrix-storage/bin/openqrm-citrix-storage clone -i ".$storage_resource->ip." -n ".$citrix_volume_name." -r ".$citrix_volume_uuid." -s ".$citrix_volume_snap_name." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
						// send command
						$openqrm_server->send_command($resource_command);
						redirect_reload($redir_msg, $citrix_storage_id);
					} else {
						$redir_msg = "Got empty name. Skipping snapshot procedure !";
						redirect_reload($redir_msg, $citrix_storage_id);
					}
					break;

			}
		}
	}
}




function citrix_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	global $citrix_storage_type;

	$table = new htmlobject_table_builder('storage_id', '', '', '', 'select');

	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';
	$arHead['storage_state']['sortable'] = false;

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';
	$arHead['storage_icon']['sortable'] = false;

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.ID';
	$arHead['storage_resource_id']['sortable'] = false;

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';
	$arHead['storage_resource_ip']['sortable'] = false;

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_comment'] = array();
	$arHead['storage_comment']['title'] ='Comment';

	$storage_count=0;
	$arBody = array();
	$t_deployment = new deployment();
	$t_deployment->get_instance_by_type("citrix-deployment");
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview_per_type($t_deployment->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$storage_count++;
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon="/openqrm/base/plugins/citrix-storage/img/storage.png";
		$state_icon="/openqrm/base/img/$storage_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
			$resource_icon_default=$storage_icon;
		}
		$arBody[] = array(
			'storage_state' => "<img src=$state_icon>",
			'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'storage_id' => $storage->id,
			'storage_name' => $storage->name,
			'storage_resource_id' => $storage->resource_id,
			'storage_resource_ip' => $storage_resource->ip,
			'storage_type' => "$deployment->storagedescription",
			'storage_comment' => $storage->comment,
		);
		$storage_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->identifier_type = "radio";
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->add_headrow("<input type='hidden' name='type' value=$citrix_storage_type>");

	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('select');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_tmp->get_count_per_type($t_deployment->id);

	// are there any storage server yet ?
	if(count($arBody) > 0) {
		$disp = $table->get_string();
	} else {
		$box = new htmlobject_box();
		$box->id = 'htmlobject_box_add_storage';
		$box->css = 'htmlobject_box';
		$box->label = '<br><nobr><b>No storage configured yet!</b></nobr>';
		$box_content = '<br><br><br><br>Please create a '.$t_deployment->storagedescription.' first!<br>';
		$box_content .= '<a href="/openqrm/base/server/storage/storage-new.php?currenttab=tab1"><b>New storage</b></a><br>';
		$box->content = $box_content;
		$disp = $box->get_string();
	}

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function citrix_storage_vdi_display($citrix_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$storage = new storage();
	$storage->get_instance_by_id($citrix_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	// vdi table
	$table = new htmlobject_table_builder('citrix_volume_uuid', '', '', '', 'citrix_volume_uuid');
	$arHead = array();
	$arHead['citrix_volume_icon'] = array();
	$arHead['citrix_volume_icon']['title'] ='';
	$arHead['citrix_volume_icon']['sortable'] = false;

	$arHead['citrix_volume_name'] = array();
	$arHead['citrix_volume_name']['title'] ='Name';

	$arHead['citrix_volume_description'] = array();
	$arHead['citrix_volume_description']['title'] ='Description';
	$arHead['citrix_volume_description']['sortable'] = false;

	$arHead['citrix_volume_uuid'] = array();
	$arHead['citrix_volume_uuid']['title'] ='UUID';

	$arHead['citrix_volume_size'] = array();
	$arHead['citrix_volume_size']['title'] ='Size';

	$arHead['citrix_volume_sr'] = array();
	$arHead['citrix_volume_sr']['title'] ='Storage';

	$arHead['citrix_volume_actions'] = array();
	$arHead['citrix_volume_actions']['title'] ='Name/Size';

	$arBody = array();
	$citrix_volume_count=0;
	$storage_icon="/openqrm/base/plugins/citrix-storage/img/storage.png";
	$storage_export_list="citrix-storage-stat/vdi.stat.".$storage_resource->ip;
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $cvolume) {
			$citrix_line = trim($cvolume);
			$citrix_parameter_arr = explode(":", $citrix_line);
			$citrix_volume_uuid = $citrix_parameter_arr[0];
			$citrix_volume_name = ltrim($citrix_parameter_arr[1], "@");
			$citrix_volume_name = str_replace("@", " ", $citrix_volume_name);
			$citrix_volume_description = ltrim($citrix_parameter_arr[2], "@");
			$citrix_volume_description = str_replace("@", " ", $citrix_volume_description);
			$citrix_volume_sr_uuid = $citrix_parameter_arr[3];
			$citrix_volume_size = ltrim($citrix_parameter_arr[4], "@");
			$citrix_volume_size = $citrix_volume_size/1024;
			$citrix_volume_size = $citrix_volume_size/1024;
			$citrix_volume_size = number_format($citrix_volume_size, 0);

			// build the snap-shot input
			$citrix_volume_snap = "<form action=\"$thisfile\" method=\"GET\">";
			$citrix_volume_snap .= "<input type='hidden' name='citrix_storage_id' value=$citrix_storage_id>";
			$citrix_volume_snap .= "<input type='hidden' name='citrix_volume_name' value=$citrix_volume_name>";
			$citrix_volume_snap .= "<input type='hidden' name='citrix_volume_uuid' value=$citrix_volume_uuid>";
			$citrix_volume_snap .= "<input type='text' name='citrix_volume_snap_name' value='' size='10' maxlength='20'>";
			$citrix_volume_snap .= "<input type='submit' name='action' value='clone'>";
			$citrix_volume_snap .= "</form>";

			$arBody[] = array(
				'citrix_volume_icon' => "<img width=24 height=24 src=$storage_icon>",
				'citrix_volume_name' => "<nobr>".$citrix_volume_name."</nobr>",
				'citrix_volume_description' => $citrix_volume_description,
				'citrix_volume_uuid' => $citrix_volume_uuid,
				'citrix_volume_size' => "<nobr>".$citrix_volume_size." MB</nobr>",
				'citrix_volume_sr' => "<nobr>".$citrix_volume_sr_uuid."</nobr>",
				'citrix_volume_actions' => "<nobr>".$citrix_volume_snap."</nobr>",
			);
			$citrix_volume_count++;
		}
	}

	$table->add_headrow("<input type='hidden' name='citrix_storage_id' value=$citrix_storage_id>");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('reload', 'remove');
		$table->identifier = 'citrix_volume_uuid';
	}
	$table->max = $citrix_volume_count;

	// sr select box
	$sr_select_options_arr = array();
	$storage_sr_list="citrix-storage-stat/sr.stat.".$storage_resource->ip;
	if (file_exists($storage_sr_list)) {
		$storage_sr_content=file($storage_sr_list);
		foreach ($storage_sr_content as $index => $sr_line) {
			$sr_line = trim($sr_line);
			$citrix_parameter_arr = explode(":", $sr_line);
			$citrix_sr_uuid = $citrix_parameter_arr[0];
			$citrix_sr_name = $citrix_parameter_arr[1];
			$sr_select_options_arr[] = array('label' => $citrix_sr_name, 'value' => $citrix_sr_uuid);

		}
	}

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-storage-vdi.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'vdi_table' => $table->get_string(),
		'citrix_volume_name' => htmlobject_input('citrix_volume_name', array("value" => '', "label" => 'Volume Name'), 'text', 20),
		'citrix_volume_size' => htmlobject_input('citrix_volume_size', array("value" => '2000', "label" => 'Size (MB)'), 'text', 20),
		'citrix_sr_select' => htmlobject_select('citrix_sr_select', $sr_select_options_arr, 'Storage'),
		'hidden_citrix_storage_id' => "<input type='hidden' name='citrix_storage_id' value=$citrix_storage_id>",
		'submit' => htmlobject_input('action', array("value" => 'add', "label" => 'Add'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}







$output = array();

if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$output[] = array('label' => 'Citrix VDI Admin', 'value' => citrix_storage_vdi_display($id));
				}
			} else {
				$output[] = array('label' => 'Select', 'value' => citrix_select_storage());
			}
			break;
		case 'add':
			$output[] = array('label' => 'Citrix VDI Admin', 'value' => citrix_storage_vdi_display($citrix_storage_id));
			break;

		case 'remove':
			$output[] = array('label' => 'Citrix VDI Admin', 'value' => citrix_storage_vdi_display($citrix_storage_id));
			break;

		case 'reload':
			$output[] = array('label' => 'Citrix VDI Admin', 'value' => citrix_storage_vdi_display($citrix_storage_id));
			break;

		case 'clone':
			$output[] = array('label' => 'Citrix VDI Admin', 'value' => citrix_storage_vdi_display($citrix_storage_id));
			break;

		default:
			$output[] = array('label' => 'Select', 'value' => citrix_select_storage());
			break;
	}
} else {
	$output[] = array('label' => 'Select', 'value' => citrix_select_storage());
}

?>
<style>
	.htmlobject_tab_box {
		width:800px;
	}
</style>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>


