<!doctype html>
<html lang="en">
<head>
	<title>LVM Storage manager</title>
	<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
	<link rel="stylesheet" type="text/css" href="xen-storage.css" />
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


$xen_storage_id = htmlobject_request('xen_storage_id');
$xen_storage_location = htmlobject_request('xen_storage_location');
$xen_volume_name=htmlobject_request('xen_volume_name');
$xen_volume_snap_name=htmlobject_request('xen_volume_snap_name');
$xen_volume_snap_size=htmlobject_request('xen_volume_snap_size');
$xen_volume_resize=htmlobject_request('xen_volume_resize');
// to gather one of the deployment types within xen-storage
$xen_storage_type=htmlobject_request('type');

$action=htmlobject_request('action');
global $xen_storage_id;
global $xen_storage_location;
global $xen_volume_name;
global $xen_volume_snap_name;
global $xen_volume_resize;
global $xen_storage_type;

$refresh_delay=1;
$refresh_loop_max=20;


function redirect_vg($strMsg, $xen_storage_id) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$xen_storage_id;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function redirect_lv($strMsg, $xen_storage_id, $xen_storage_location) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&xen_storage_id='.$xen_storage_id.'&identifier[]='.$xen_storage_location;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function redirect_lvmgmt($strMsg, $xen_storage_id, $xen_storage_location) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&xen_storage_id='.$xen_storage_id.'&xen_storage_location='.$xen_storage_location;
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
							$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage post_vg -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
							// remove current stat file
							$storage_resource_id = $storage_resource->id;
							$statfile="storage/".$storage_resource_id.".vg.stat";
							if (file_exists($statfile)) {
								unlink($statfile);
							}
							// send command
							$storage_resource->send_command($storage_resource->ip, $resource_command);
							// and wait for the resulting statfile
							if (!wait_for_statfile($statfile)) {
								$redir_msg = "Error during selecting storage location ! Please check the Event-Log";
							} else {
								$redir_msg = "Displaying storage locations on storage id ".$id;
							}
							redirect_vg($redir_msg, $id);
						}
					}
					break;

				case 'select-vg':
					if (isset($_REQUEST['identifier'])) {
						foreach($_REQUEST['identifier'] as $xen_storage_location) {
							show_progressbar();
							$storage = new storage();
							$storage->get_instance_by_id($xen_storage_id);
							$deployment = new deployment();
							$deployment->get_instance_by_id($storage->type);
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							// post lv status
							$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage post_lv -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -v ".$xen_storage_location." -t ".$deployment->type;
							// remove current stat file
							$storage_resource_id = $storage_resource->id;
							$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
							if (file_exists($statfile)) {
								unlink($statfile);
							}
							// send command
							$storage_resource->send_command($storage_resource->ip, $resource_command);
							// and wait for the resulting statfile
							if (!wait_for_statfile($statfile)) {
								$redir_msg = "Error during selecting storage location ! Please check the Event-Log";
							} else {
								$redir_msg = "Displaying storage locations on storage id ".$xen_storage_id;
							}
							redirect_lv($redir_msg, $xen_storage_id, $xen_storage_location);
						}
					}
					break;


				case 'add':
					$xen_volume_name = htmlobject_request('xen_volume_name');
					show_progressbar();
					if (!strlen($xen_volume_name)) {
						$redir_msg = "Got emtpy volume name. Not adding ...";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					} else if (!validate_input($xen_volume_name, 'string')) {
						$redir_msg = "Got invalid volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					}
					$xen_volume_size = htmlobject_request('xen_volume_size');
					if (!strlen($xen_volume_size)) {
						$xen_volume_size=2000;
					} else if (!validate_input($xen_volume_size, 'number')) {
						$redir_msg = "Got invalid volume size. Not adding ...";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					}
					$storage = new storage();
					$storage->get_instance_by_id($xen_storage_id);
					$storage_resource = new resource();
					$storage_resource->get_instance_by_id($storage->resource_id);
					$storage_deployment = new deployment();
					$storage_deployment->get_instance_by_id($storage->type);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage add -n ".$xen_volume_name." -v ".$xen_storage_location." -m ".$xen_volume_size." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$storage_deployment->type;
					// remove current stat file
					$storage_resource_id = $storage_resource->id;
					$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$storage_resource->send_command($storage_resource->ip, $resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$redir_msg = "Error during adding volume ".$xen_volume_name." to storage location ".$xen_storage_location." ! Please check the Event-Log";
					} else {
						$redir_msg = "Added volume ".$xen_volume_name." to storage location ".$xen_storage_location;
					}
					redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					break;

				case 'remove':
					if (isset($_REQUEST['identifier'])) {
						show_progressbar();
						foreach($_REQUEST['identifier'] as $xen_volume_name) {
							$storage = new storage();
							$storage->get_instance_by_id($xen_storage_id);
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							$storage_deployment = new deployment();
							$storage_deployment->get_instance_by_id($storage->type);
							$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage remove -n ".$xen_volume_name." -v ".$xen_storage_location." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$storage_deployment->type;
							// remove current stat file
							$storage_resource_id = $storage_resource->id;
							$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
							if (file_exists($statfile)) {
								unlink($statfile);
							}
							// send command
							$storage_resource->send_command($storage_resource->ip, $resource_command);
							// and wait for the resulting statfile
							if (!wait_for_statfile($statfile)) {
								$redir_msg .= "Error during removing volume ".$xen_volume_name." from storage location ".$xen_storage_location." ! Please check the Event-Log<br>";
							} else {
								$redir_msg .= "Removed volume ".$xen_volume_name." from storage location ".$xen_storage_location."<br>";
							}
						}
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					} else {
						$redir_msg = "No LVM location selected. Skipping removal !";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					}
					break;


				case 'reload':
					show_progressbar();
					$storage = new storage();
					$storage->get_instance_by_id($xen_storage_id);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$storage_resource = new resource();
					$storage_resource->get_instance_by_id($storage->resource_id);
					// post lv status
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage post_lv -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -v ".$xen_storage_location." -t ".$deployment->type;
					// remove current stat file
					$storage_resource_id = $storage_resource->id;
					$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$storage_resource->send_command($storage_resource->ip, $resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$redir_msg = "Error during displaying volumes on storage location ".$xen_storage_location." ! Please check the Event-Log";
					} else {
						$redir_msg = "Displaying volumes on storage location ".$xen_storage_location;
					}
					redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					break;


				case 'snap':
				case 'clone':
					$xen_action = htmlobject_request('action');
					if (strlen($xen_volume_snap_name)) {
						show_progressbar();
						if (!strlen($xen_volume_name)) {
							$redir_msg = "Got emtpy volume name. Not adding ...";
							redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
							exit(0);
						} else if (!validate_input($xen_volume_name, 'string')) {
							$redir_msg = "Got invalid volume name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
							redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
							exit(0);
						}

						if (!strlen($xen_volume_snap_name)) {
							$redir_msg = "Got emtpy volume clone name. Not adding ...";
							redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
							exit(0);
						} else if (!validate_input($xen_volume_snap_name, 'string')) {
							$redir_msg = "Got invalid volume clone name. Not adding ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
							redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
							exit(0);
						}

						if (!strlen($xen_volume_snap_size)) {
							$xen_volume_snap_size=5000;
						} else if (!validate_input($xen_volume_snap_size, 'number')) {
							$redir_msg = "Got invalid volume clone size. Not adding ...";
							redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
							exit(0);
						}
						// snap/clone
						$storage = new storage();
						$storage->get_instance_by_id($xen_storage_id);
						$storage_resource = new resource();
						$storage_resource->get_instance_by_id($storage->resource_id);
						$deployment = new deployment();
						$deployment->get_instance_by_id($storage->type);
						$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage ".$xen_action." -n ".$xen_volume_name." -v ".$xen_storage_location." -s ".$xen_volume_snap_name." -m ".$xen_volume_snap_size." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
						// remove current stat file
						$storage_resource_id = $storage_resource->id;
						$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
						if (file_exists($statfile)) {
							unlink($statfile);
						}
						// send command
						$storage_resource->send_command($storage_resource->ip, $resource_command);
						// and wait for the resulting statfile
						if (!wait_for_statfile($statfile)) {
							$redir_msg = "Error during snapshotting volume ".$xen_volume_name." -> ".$xen_volume_snap_name." on storage location ".$xen_storage_location." ! Please check the Event-Log";
						} else {
							$redir_msg = "Created snapshot of volume ".$xen_volume_name." -> ".$xen_volume_snap_name." on storage location ".$xen_storage_location;
						}
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					} else {
						$redir_msg = "Got empty name. Skipping snapshot procedure !";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					}
					break;




				case 'resize':
					show_progressbar();
					if (!strlen($xen_volume_name)) {
						$redir_msg = "Got emtpy volume name. Not resizing ...";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					} else if (!validate_input($xen_volume_name, 'string')) {
						$redir_msg = "Got invalid volume name. Not resizing ...<br>(allowed characters are [a-z][A-z][0-9].-_)";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					}
					if (!strlen($xen_volume_resize)) {
						$xen_volume_resize=5000;
					} else if (!validate_input($xen_volume_resize, 'number')) {
						$redir_msg = "Got invalid volume resize value. Not resizing ...";
						redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
						exit(0);
					}
					// snap
					$storage = new storage();
					$storage->get_instance_by_id($xen_storage_id);
					$storage_resource = new resource();
					$storage_resource->get_instance_by_id($storage->resource_id);
					$deployment = new deployment();
					$deployment->get_instance_by_id($storage->type);
					$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/xen-storage/bin/openqrm-xen-storage resize -n ".$xen_volume_name." -v ".$xen_storage_location." -m ".$xen_volume_resize." -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password." -t ".$deployment->type;
					// remove current stat file
					$storage_resource_id = $storage_resource->id;
					$statfile="storage/".$storage_resource_id.".".$xen_storage_location.".lv.stat";
					if (file_exists($statfile)) {
						unlink($statfile);
					}
					// send command
					$storage_resource->send_command($storage_resource->ip, $resource_command);
					// and wait for the resulting statfile
					if (!wait_for_statfile($statfile)) {
						$redir_msg = "Error during resizing volume ".$xen_volume_name." on storage location ".$xen_storage_location." ! Please check the Event-Log";
					} else {
						$redir_msg = "Resized volume ".$xen_volume_name." on storage location ".$xen_storage_location;
					}
					redirect_lvmgmt($redir_msg, $xen_storage_id, $xen_storage_location);
					break;

			}
		}
	}
}




function xen_select_storage() {
	global $OPENQRM_USER;
	global $thisfile;
	global $xen_storage_type;

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
	$b_deployment = new deployment();
	$t_deployment->get_instance_by_type("xen-lvm-deployment");
	$b_deployment->get_instance_by_type("xen-bf-deployment");
	$storage_tmp = new storage();
	// lvm
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
		$storage_icon="/openqrm/base/plugins/xen-storage/img/storage.png";
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

	$storage_array = $storage_tmp->display_overview_per_type($b_deployment->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$deployment = new deployment();
		$deployment->get_instance_by_id($storage->type);
		$storage_count++;
		$resource_icon_default="/openqrm/base/img/resource.png";
		$storage_icon="/openqrm/base/plugins/xen-storage/img/storage.png";
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
	$table->add_headrow("<input type='hidden' name='type' value=$xen_storage_type>");

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
	$t->setFile('tplfile', './tpl/' . 'xen-storage-select.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


function xen_storage_display($xen_storage_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$storage = new storage();
	$storage->get_instance_by_id($xen_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	$table = new htmlobject_table_identifiers_checked('storage_id');
	$arHead = array();
	$arHead['storage_state'] = array();
	$arHead['storage_state']['title'] ='';

	$arHead['storage_icon'] = array();
	$arHead['storage_icon']['title'] ='';

	$arHead['storage_id'] = array();
	$arHead['storage_id']['title'] ='ID';

	$arHead['storage_name'] = array();
	$arHead['storage_name']['title'] ='Name';

	$arHead['storage_resource_id'] = array();
	$arHead['storage_resource_id']['title'] ='Res.ID';

	$arHead['storage_resource_ip'] = array();
	$arHead['storage_resource_ip']['title'] ='Ip';

	$arHead['storage_type'] = array();
	$arHead['storage_type']['title'] ='Type';

	$arHead['storage_capabilities'] = array();
	$arHead['storage_capabilities']['title'] ='Capabilities';

	$storage_count=1;
	$arBody = array();
	$resource_icon_default="/openqrm/base/img/resource.png";
	$storage_icon="/openqrm/base/plugins/xen-storage/img/storage.png";
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
		'storage_capabilities' => $storage_resource->capabilities,
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
	$table->max = $storage_count;


	// vg table
	$table1 = new htmlobject_table_builder('vg_name', '', '', '', 'vgs');
	$arHead1 = array();
	$arHead1['vg_icon'] = array();
	$arHead1['vg_icon']['title'] ='';
	$arHead1['vg_icon']['sortable'] = false;

	$arHead1['vg_name'] = array();
	$arHead1['vg_name']['title'] ='Name';

	$arHead1['vg_pv'] = array();
	$arHead1['vg_pv']['title'] ='PV';

	$arHead1['vg_lv'] = array();
	$arHead1['vg_lv']['title'] ='LV';

	$arHead1['vg_sn'] = array();
	$arHead1['vg_sn']['title'] ='SN';

	$arHead1['vg_attr'] = array();
	$arHead1['vg_attr']['title'] ='Attr';

	$arHead1['vg_vsize'] = array();
	$arHead1['vg_vsize']['title'] ='VSize';

	$arHead1['vg_vfree'] = array();
	$arHead1['vg_vfree']['title'] ='VFree';

	$arBody1 = array();
	$vg_count=0;
	$storage_vg_list="storage/$storage_resource->id.vg.stat";
	if (file_exists($storage_vg_list)) {
		$storage_vg_content=file($storage_vg_list);
		foreach ($storage_vg_content as $index => $lvm) {
			$vg_line = trim($lvm);

			$first_at_pos = strpos($vg_line, "@");
			$first_at_pos++;
			$vg_line_first_at_removed = substr($vg_line, $first_at_pos, strlen($vg_line)-$first_at_pos);
			$second_at_pos = strpos($vg_line_first_at_removed, "@");
			$second_at_pos++;
			$vg_line_second_at_removed = substr($vg_line_first_at_removed, $second_at_pos, strlen($vg_line_first_at_removed)-$second_at_pos);
			$third_at_pos = strpos($vg_line_second_at_removed, "@");
			$third_at_pos++;
			$vg_line_third_at_removed = substr($vg_line_second_at_removed, $third_at_pos, strlen($vg_line_second_at_removed)-$third_at_pos);
			$fourth_at_pos = strpos($vg_line_third_at_removed, "@");
			$fourth_at_pos++;
			$vg_line_fourth_at_removed = substr($vg_line_third_at_removed, $fourth_at_pos, strlen($vg_line_third_at_removed)-$fourth_at_pos);
			$fivth_at_pos = strpos($vg_line_fourth_at_removed, "@");
			$fivth_at_pos++;
			$vg_line_fivth_at_removed = substr($vg_line_fourth_at_removed, $fivth_at_pos, strlen($vg_line_fourth_at_removed)-$fivth_at_pos);
			$sixth_at_pos = strpos($vg_line_fivth_at_removed, "@");
			$sixth_at_pos++;
			$vg_line_sixth_at_removed = substr($vg_line_fivth_at_removed, $sixth_at_pos, strlen($vg_line_fivth_at_removed)-$sixth_at_pos);
			$seventh_at_pos = strpos($vg_line_sixth_at_removed, "@");
			$seventh_at_pos++;

			$vg_name = trim(substr($vg_line, 0, $first_at_pos-1));
			$vg_pv = trim(substr($vg_line_first_at_removed, 0, $second_at_pos-1));
			$vg_lv = trim(substr($vg_line_second_at_removed, 0, $third_at_pos-1));
			$vg_sn = trim(substr($vg_line_third_at_removed, 0, $fourth_at_pos-1));
			$vg_attr = trim(substr($vg_line_fourth_at_removed, 0, $fivth_at_pos-1));
			$vg_vsize = trim(substr($vg_line_fivth_at_removed, 0, $sixth_at_pos-1));
			$vg_vfree = trim(substr($vg_line_sixth_at_removed, 0, $seventh_at_pos-1));

			$arBody1[] = array(
				'vg_icon' => "<img width=24 height=24 src=$storage_icon>",
				'vg_name' => $vg_name,
				'vg_pv' => $vg_pv,
				'vg_lv' => $vg_lv,
				'vg_sn' => $vg_sn,
				'vg_attr' => $vg_attr,
				'vg_vsize' => $vg_vsize,
				'vg_vfree' => $vg_vfree,
			);
			$vg_count++;
		}
	}
	$table1->add_headrow("<input type='hidden' name='xen_storage_id' value=$xen_storage_id>");
	$table1->id = 'Tabelle';
	$table1->css = 'htmlobject_table';
	$table1->border = 1;
	$table1->cellspacing = 0;
	$table1->cellpadding = 3;
	$table1->form_action = $thisfile;
	$table1->identifier_type = "radio";
	$table1->autosort = true;
	$table1->head = $arHead1;
	$table1->body = $arBody1;
	if ($OPENQRM_USER->role == "administrator") {
		$table1->bottom = array('select-vg');
		$table1->identifier = 'vg_name';
	}
	$table1->max = $vg_count;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'xen-storage-vgs.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'storage_table' => $table->get_string(),
		'vg_table' => $table1->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}






function xen_storage_lv_display($xen_storage_id, $xen_storage_location) {
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

	$storage = new storage();
	$storage->get_instance_by_id($xen_storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$deployment = new deployment();
	$deployment->get_instance_by_id($storage->type);

	// lvm table
	$table = new htmlobject_table_builder('xen_volume_name', '', '', '', 'luns');
	$arHead = array();
	$arHead['xen_volume_icon'] = array();
	$arHead['xen_volume_icon']['title'] ='';
	$arHead['xen_volume_icon']['sortable'] = false;

	$arHead['xen_volume_name'] = array();
	$arHead['xen_volume_name']['title'] ='Volume';

	$arHead['xen_volume_attr'] = array();
	$arHead['xen_volume_attr']['title'] ='Attr';

	$arHead['xen_volume_lsize'] = array();
	$arHead['xen_volume_lsize']['title'] ='LSize';

	$arHead['xen_volume_rsize'] = array();
	$arHead['xen_volume_rsize']['title'] ='Resize (+ MB)';
	$arHead['xen_volume_rsize']['sortable'] = false;

	$arHead['xen_volume_snap'] = array();
	$arHead['xen_volume_snap']['title'] ='Snap/Clone (name + size)';
	$arHead['xen_volume_snap']['sortable'] = false;

	$arBody = array();
	$xen_volume_count=0;
	$storage_icon="/openqrm/base/plugins/xen-storage/img/storage.png";
	$storage_export_list="storage/".$storage->resource_id.".".$xen_storage_location.".lv.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $lvm) {
			$xen_line = trim($lvm);

			$first_at_pos = strpos($xen_line, "@");
			$first_at_pos++;
			$xen_line_first_at_removed = substr($xen_line, $first_at_pos, strlen($xen_line)-$first_at_pos);
			$second_at_pos = strpos($xen_line_first_at_removed, "@");
			$second_at_pos++;
			$xen_line_second_at_removed = substr($xen_line_first_at_removed, $second_at_pos, strlen($xen_line_first_at_removed)-$second_at_pos);
			$third_at_pos = strpos($xen_line_second_at_removed, "@");
			$third_at_pos++;
			$xen_line_third_at_removed = substr($xen_line_second_at_removed, $third_at_pos, strlen($xen_line_second_at_removed)-$third_at_pos);
			$fourth_at_pos = strpos($xen_line_third_at_removed, "@");
			$fourth_at_pos++;
			$xen_line_fourth_at_removed = substr($xen_line_third_at_removed, $fourth_at_pos, strlen($xen_line_third_at_removed)-$fourth_at_pos);
			$fivth_at_pos = strpos($xen_line_fourth_at_removed, "@");
			$fivth_at_pos++;
			$xen_line_fivth_at_removed = substr($xen_line_fourth_at_removed, $fivth_at_pos, strlen($xen_line_fourth_at_removed)-$fivth_at_pos);
			$sixth_at_pos = strpos($xen_line_fivth_at_removed, "@");
			$sixth_at_pos++;
			$xen_line_sixth_at_removed = substr($xen_line_fivth_at_removed, $sixth_at_pos, strlen($xen_line_fivth_at_removed)-$sixth_at_pos);
			$seventh_at_pos = strpos($xen_line_sixth_at_removed, "@");
			$seventh_at_pos++;

			$xen_volume_name = trim(substr($xen_line, 0, $first_at_pos-1));
			$xen_volume_vol = trim(substr($xen_line_first_at_removed, 0, $second_at_pos-1));
			$xen_volume_attr = trim(substr($xen_line_second_at_removed, 0, $third_at_pos-1));
			$xen_volume_lsize = trim(substr($xen_line_third_at_removed, 0, $fourth_at_pos-1));

			// build the resize input
			$xen_volume_rsize = "<form action=\"$thisfile\" method=\"GET\">";
			$xen_volume_rsize .= "<input type='hidden' name='xen_storage_id' value=$xen_storage_id>";
			$xen_volume_rsize .= "<input type='hidden' name='xen_storage_location' value=$xen_storage_location>";
			$xen_volume_rsize .= "<input type='hidden' name='xen_volume_name' value=$xen_volume_name>";
			$xen_volume_rsize .= "<input type='text' name='xen_volume_resize' value='' size='5' maxlength='10'> MB ";
			$xen_volume_rsize .= "<input type='submit' name='action' value='resize'>";
			$xen_volume_rsize .= "</form>";

			// build the snap-shot input
			$xen_volume_snap = "<form action=\"$thisfile\" method=\"GET\">";
			$xen_volume_snap .= "<input type='hidden' name='xen_storage_id' value=$xen_storage_id>";
			$xen_volume_snap .= "<input type='hidden' name='xen_storage_location' value=$xen_storage_location>";
			$xen_volume_snap .= "<input type='hidden' name='xen_volume_name' value=$xen_volume_name>";
			$xen_volume_snap .= "<input type='text' name='xen_volume_snap_name' value='' size='10' maxlength='20'>";
			$xen_volume_snap .= "<input type='text' name='xen_volume_snap_size' value='' size='5' maxlength='10'> MB ";
			// check if to show the snap button
			if (!strstr($xen_volume_attr, "swi")) {
				$xen_volume_snap .= "<input type='submit' name='action' value='snap'>";
			} else {
				$xen_volume_snap .= "<input type='submit' name='action' value='snap' disabled='true'>";
			}
			$xen_volume_snap .= "<input type='submit' name='action' value='clone'>";
			$xen_volume_snap .= "</form>";


			$arBody[] = array(
				'xen_volume_icon' => "<img width=24 height=24 src=$storage_icon>",
				'xen_volume_name' => $xen_volume_name,
				'xen_volume_attr' => $xen_volume_attr,
				'xen_volume_lsize' => $xen_volume_lsize,
				'xen_volume_rsize' => "<nobr>".$xen_volume_rsize."</nobr>",
				'xen_volume_snap' => "<nobr>".$xen_volume_snap."</nobr>",
			);
			$xen_volume_count++;
		}
	}

	$table->add_headrow("<input type='hidden' name='xen_storage_id' value=$xen_storage_id><input type='hidden' name='xen_storage_location' value=$xen_storage_location>");
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
		$table->identifier = 'xen_volume_name';
	}
	$table->max = $xen_volume_count;


	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'xen-storage-luns.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_name' => $storage->name,
		'xen_storage_location' => $xen_storage_location,
		'lun_table' => $table->get_string(),
		'xen_volume_name' => htmlobject_input('xen_volume_name', array("value" => '', "label" => 'Volume Name'), 'text', 20),
		'xen_volume_size' => htmlobject_input('xen_volume_size', array("value" => '2000', "label" => 'Volume Size (MB)'), 'text', 20),
		'hidden_xen_storage_location' => "<input type='hidden' name='xen_storage_location' value=$xen_storage_location>",
		'hidden_xen_storage_id' => "<input type='hidden' name='xen_storage_id' value=$xen_storage_id>",
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
					$output[] = array('label' => 'Storage Admin', 'value' => xen_storage_display($id));
				}
			} else {
				$output[] = array('label' => 'Select', 'value' => xen_select_storage());
			}
			break;

		case 'select-vg':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $xen_storage_location) {
					$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
				}
			} else {
				$output[] = array('label' => 'Storage Admin', 'value' => xen_storage_display($xen_storage_id));
			}
			break;

		case 'add':
			$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
			break;

		case 'remove':
			$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
			break;

		case 'reload':
			$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
			break;

		case 'snap':
			$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
			break;

		case 'resize':
			$output[] = array('label' => $xen_storage_location, 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
			break;


	}

} else if (strlen($xen_storage_location)) {
	$output[] = array('label' => 'Volume Admin', 'value' => xen_storage_lv_display($xen_storage_id, $xen_storage_location));
} else if (strlen($xen_storage_id)) {
	$output[] = array('label' => 'Storage Admin', 'value' => xen_storage_display($xen_storage_id));
} else  {
	$output[] = array('label' => 'Select', 'value' => xen_select_storage());
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


