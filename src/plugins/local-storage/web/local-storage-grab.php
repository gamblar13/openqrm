<!doctype html>
<html lang="en">
<head>
	<title>Select Local Storage</title>
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
// place for the storage stat files
$StorageDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/local-storage/storage';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/image_authentication.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special local-storage classes
require_once "$RootDir/plugins/local-storage/class/localstoragestate.class.php";
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $IMAGE_AUTHENTICATION_TABLE;
$event = new event();
global $event;

$resource_id = htmlobject_request('resource_id');
$storage_id = htmlobject_request('storage_id');
$template_export = htmlobject_request('template_export');
global $resource_id;
global $storage_id;
global $template_export;
global $APPLIANCE_INFO_TABLE;
$action=htmlobject_request('action');
$step=htmlobject_request('step');
if (!strlen($step)) {
	$step=1;
}


$refresh_delay=1;
$refresh_loop_max=20;


function redirect_resource($strMsg, $resource_id, $step) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$resource_id.'&step='.$step;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}

function redirect_storage($strMsg, $resource_id, $storage_id, $step) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&identifier[]='.$storage_id.'&resource_id='.$resource_id.'&step='.$step;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}
function redirect_template_export($strMsg, $resource_id, $storage_id, $template_export, $step) {
	global $thisfile;
	global $action;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&resource_id='.$resource_id.'&storage_id='.$storage_id.'&identifier[]='.$template_export.'&step='.$step;
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


function wait_for_template_exports($sfile) {
	$refresh_delay=1;
	$refresh_loop_max=20;
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




// running the actions
$redir_msg = '';
if(htmlobject_request('redirect') != 'yes') {
	if(htmlobject_request('action') != '') {
		if ($OPENQRM_USER->role == "administrator") {

			switch (htmlobject_request('action')) {
				case 'grab':
					if (isset($_REQUEST['identifier'])) {
						foreach($_REQUEST['identifier'] as $id) {
							$step = 2;
							show_progressbar();
							$resource = new resource();
							$resource->get_instance_by_id($id);
							$redir_msg="Selected resource $id";
							redirect_resource($redir_msg, $id, $step);
						}
					}
					break;

				case 'storage':
					if (isset($_REQUEST['identifier'])) {
						foreach($_REQUEST['identifier'] as $id) {
							$step = 3;
							show_progressbar();
							$storage = new storage();
							$storage->get_instance_by_id($id);
							$redir_msg="Selected storage $id";
							// get a list of template exports
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							$storage_resource_id = $storage_resource->id;
							$ident_file = $StorageDir."/".$storage_resource_id.".lv.local-storage.ident";
							if (file_exists($ident_file)) {
								unlink($ident_file);
							}
							// send command
							$resource_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/local-storage/bin/openqrm-local-storage post_identifier -u ".$OPENQRM_ADMIN->name." -p ".$OPENQRM_ADMIN->password;
							$storage_resource->send_command($storage_resource->ip, $resource_command);
							if (!wait_for_template_exports($ident_file)) {
								$redir_msg="Timeout reached for getting the list of template exports from storage ".$id;
								redirect_resource($redir_msg, $resource_id, 2);

							}
							redirect_storage($redir_msg, $resource_id, $id, $step);
						}
					}
					break;


				case 'put':
					if (isset($_REQUEST['identifier'])) {
						foreach($_REQUEST['identifier'] as $template_export) {
							$step = 4;
							show_progressbar();
							// get storage, authenticate template export
							$storage = new storage();
							$storage->get_instance_by_id($storage_id);
							$storage_resource = new resource();
							$storage_resource->get_instance_by_id($storage->resource_id);
							$resource = new resource();
							$resource->get_instance_by_id($resource_id);
							// authenticating the storage volume
							$local_storage_auth_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/local-storage/bin/openqrm-local-storage auth -r ".$template_export." -i ".$resource->ip;
							$storage_resource->send_command($storage_resource->ip, $local_storage_auth_command);
							// we should create an authblocker here and wait until it is removed, anyway the image is existing in this case
							// send grab command
							$template_name=basename($template_export);
							$local_storage_command=$OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/local-storage/bin/openqrm-local-storage-manager grab -m ".$resource->mac." -i ".$resource->ip." -n ".$template_name." -d ".$storage_resource->ip.":".$template_export;
							$openqrm_server->send_command($local_storage_command);
							sleep(4);
							$resource->send_command($resource->ip, "reboot");
							$resource_fields=array();
							$resource_fields["resource_state"]="transition";
							$resource->update_info($resource->id, $resource_fields);
							sleep(4);
							// creating an image-auth object to reset the storage auth when the system is idle again
//							$image_authentication = new image_authentication();
//							$ia_id = openqrm_db_get_free_id('ia_id', $IMAGE_AUTHENTICATION_TABLE);
//							$image_auth_ar = array(
//								'ia_id' => $ia_id,
//								'ia_image_id' => $image->id,
//								'ia_resource_id' => $resource->id,
//								'ia_auth_type' => 0,
//							);
//							$image_authentication->add($image_auth_ar);
							$redir_msg="Running 'grab' on resource ".$resource_id." transfering to storage ".$storage_id.":".$template_export." as ".$template_name;
							redirect_template_export($redir_msg, $resource_id, $storage_id, $template_export, $step);
						}
					}
					break;

			}
		}
	}
}



function local_select_resource() {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_table_builder('resource_id', '', '', '', 'grab1');

	$arHead = array();
	$arHead['resource_state'] = array();
	$arHead['resource_state']['title'] ='';
	$arHead['resource_state']['sortable'] = false;

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';
	$arHead['resource_icon']['sortable'] = false;

	$arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_hostname'] = array();
	$arHead['resource_hostname']['title'] ='Name';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_mac'] = array();
	$arHead['resource_mac']['title'] ='Mac';

	$resource_count=0;
	$arBody = array();
	$resource_tmp = new resource();
	$resource_array = $resource_tmp->display_idle_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($resource_array as $index => $resource_db) {
		$resource = new resource();
		$resource->get_instance_by_id($resource_db["resource_id"]);
		$resource_resource = new resource();
		$resource_resource->get_instance_by_id($resource->id);
		$resource_icon_default="/openqrm/base/img/resource.png";
		$resource_icon="/openqrm/base/plugins/local-resource/img/resource.png";
		$state_icon="/openqrm/base/img/$resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$resource_icon)) {
			$resource_icon_default=$resource_icon;
		}
		$arBody[] = array(
			'resource_state' => "<img src=$state_icon>",
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource->id,
			'resource_hostname' => $resource->hostname,
			'resource_ip' => $resource->ip,
			'resource_mac' => "$resource->mac",
		);
		$resource_count++;
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('grab');
		$table->identifier = 'resource_id';
	}
	$table->max = $resource_tmp->get_count("idle");

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab1.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'idle_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function local_select_storage($resource_id) {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_table_builder('storage_id', '', '', '', 'grab2');

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
	$t_deployment->get_instance_by_type("local-storage");
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
		$storage_icon="/openqrm/base/plugins/nfs-storage/img/storage.png";
		$state_icon="/openqrm/base/img/$storage_resource->state.png";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
			$resource_icon_default=$storage_icon;
		}
		$arBody[] = array(
			'storage_state' => "<img src=$state_icon>",
			'storage_icon' => "<img width=24 height=24 src=".$resource_icon_default."><input type='hidden' name='resource_id' value=".$resource_id.">",
			'storage_id' => $storage->id,
			'storage_name' => $storage->name,
			'storage_resource_id' => $storage->resource_id,
			'storage_resource_ip' => $storage_resource->ip,
			'storage_type' => "$deployment->storagedescription",
			'storage_comment' => $storage->comment,
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
		$table->bottom = array('storage');
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
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab2.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function local_select_template_export($resource_id, $storage_id) {
	global $OPENQRM_USER;
	global $thisfile;
	global $StorageDir;

	$storage = new storage();
	$storage->get_instance_by_id($storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$storage_resource_id = $storage_resource->id;
	$ident_file = $StorageDir."/".$storage_resource_id.".lv.local-storage.ident";

	$table = new htmlobject_table_builder('template_export', '', '', '', 'grab3');
	$arHead = array();

	$arHead['template_icon'] = array();
	$arHead['template_icon']['title'] ='';
	$arHead['template_icon']['sortable'] = false;

	$arHead['template_name'] = array();
	$arHead['template_name']['title'] ='Name';

	$arHead['template_export'] = array();
	$arHead['template_export']['title'] ='Export';

	$template_count=0;
	$template_icon_default="/openqrm/base/img/template.png";
	$arBody = array();

	if (file_exists($ident_file)) {
		$fcontent = file($ident_file);
		foreach($fcontent as $lun_info) {
			$tpos = strpos($lun_info, ",");
			$template_export = trim(substr($lun_info, $tpos+1));
			$template_name = basename($template_export);

			$arBody[] = array(
				'template_icon' => "<img width=24 height=24 src=".$template_icon_default."><input type='hidden' name='resource_id' value=".$resource_id."><input type='hidden' name='storage_id' value=".$storage_id.">",
				'template_name' => $template_name,
				'template_export' => $template_export,
			);
			$template_count++;
		}
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
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('put');
		$table->identifier = 'template_export';
	}
	$table->max = $template_count;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab3.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'template_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function local_transfer_disk($resource_id, $storage_id, $template_export) {

	$storage = new storage();
	$storage->get_instance_by_id($storage_id);
	$storage_resource = new resource();
	$storage_resource->get_instance_by_id($storage->resource_id);
	$template_name = basename($template_export);
	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'local-storage-grab4.tpl.php');
	$t->setVar(array(
		'resource_id' => $resource_id,
		'storage_id' => $storage_id,
		'storage_ip' => $storage_resource->ip,
		'template_name' => $template_name,
		'template_export' => $template_export,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}




$output = array();

switch ($step) {
	case '1':
		$output[] = array('label' => 'Select resource', 'value' => local_select_resource());
		break;

	case '2':
		foreach($_REQUEST['identifier'] as $resource_id) {
			$output[] = array('label' => 'Select storage', 'value' => local_select_storage($resource_id));
		}
		break;

	case '3':
		foreach($_REQUEST['identifier'] as $storage_id) {
			$output[] = array('label' => 'Select template export', 'value' => local_select_template_export($resource_id, $storage_id));
		}
		break;

	case '4':
		foreach($_REQUEST['identifier'] as $template_export) {
			$output[] = array('label' => 'Running grab phase', 'value' => local_transfer_disk($resource_id, $storage_id, $template_export));
		}
		break;

}



?>
<script type="text/javascript">
	$("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
