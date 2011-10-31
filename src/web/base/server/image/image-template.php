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

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

// gather post params
$image_id = htmlobject_request('image_id');
$local_deployment_method = htmlobject_request('local_deployment_method');
$local_deployment_template = htmlobject_request('local_deployment_template');
$local_deployment_persistent = htmlobject_request('local_deployment_persistent');
// plugable addtional parameters
$local_deployment_additional_parameter1 = htmlobject_request('local_deployment_additional_parameter1');
$local_deployment_additional_parameter2 = htmlobject_request('local_deployment_additional_parameter2');
$local_deployment_additional_parameter3 = htmlobject_request('local_deployment_additional_parameter3');
$local_deployment_additional_parameter4 = htmlobject_request('local_deployment_additional_parameter4');

$storage_id = htmlobject_request('storage_id');
$step = 1;


if(strtolower(OPENQRM_USER_ROLE_NAME) != 'administrator') {
	echo 'Access denied';
	exit;
}

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	// using meta refresh because of the java-script in the header
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




if(htmlobject_request('action') != '') {
	$strMsg = '';
	$error = 0;

	switch (htmlobject_request('action')) {

		case 'Select':
			if (isset($_REQUEST['identifier'])) {
				// show_progressbar();
				foreach($_REQUEST['identifier'] as $id) {
					$storage = new storage();
					$storage->get_instance_by_id($id);
					$storage_id = $id;
					$step = 2;
				}
			}
			break;

		case 'Save':
				// show_progressbar();
				$image = new image();
				$image->get_instance_by_id($image_id);

				// plugable addtional parameters
				if (strlen($local_deployment_additional_parameter1)) {
					$local_deployment_template = $local_deployment_template.":".$local_deployment_additional_parameter1;
				}
				if (strlen($local_deployment_additional_parameter2)) {
					$local_deployment_template = $local_deployment_template.":".$local_deployment_additional_parameter2;
				}
				if (strlen($local_deployment_additional_parameter3)) {
					$local_deployment_template = $local_deployment_template.":".$local_deployment_additional_parameter3;
				}
				if (strlen($local_deployment_additional_parameter4)) {
					$local_deployment_template = $local_deployment_template.":".$local_deployment_additional_parameter4;
				}
				// add mode at the beginning
				$local_deployment_template = $local_deployment_persistent.":".$local_deployment_template;
				$image->set_deployment_parameters("INSTALL_CONFIG", $local_deployment_template);
				$step = 3;
			break;

	}
}



function deployment_server_select($image_id, $local_deployment_method) {
	global $BaseDir, $OPENQRM_USER, $thisfile;

	$image = new image();
	$image->get_instance_by_id($image_id);
	$deployment = new deployment();
	$deployment->get_instance_by_type($local_deployment_method);

	$disp = "<h1>Select ".$deployment->name." Deployment Server for  Image ".$image->name."</h1>";

	$table = new htmlobject_table_builder('storage_id', '', '', '', 'select');
	$table->add_headrow("<input type='hidden' name='image_id' value=".$image_id."><input type='hidden' name='local_deployment_method' value=".$local_deployment_method.">");

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
	$storage_tmp = new storage();
	$storage_array = $storage_tmp->display_overview_per_type($deployment->id, $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($storage_array as $index => $storage_db) {
		$storage = new storage();
		$storage->get_instance_by_id($storage_db["storage_id"]);
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$t_deployment = new deployment();
		$t_deployment->get_instance_by_id($storage->type);
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
			'storage_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'storage_id' => $storage->id,
			'storage_name' => $storage->name,
			'storage_resource_id' => $storage->resource_id,
			'storage_resource_ip' => $storage_resource->ip,
			'storage_type' => "$t_deployment->storagedescription",
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
		$table->bottom = array('Select');
		$table->identifier = 'storage_id';
	}
	$table->max = $storage_tmp->get_count_per_type($t_deployment->id);

	// are there any storage server yet ?
	if(count($arBody) > 0) {
		$disp .= $table->get_string();
	} else {
		$box = new htmlobject_box();
		$box->id = 'htmlobject_box_add_storage';
		$box->css = 'htmlobject_box';
		$box->label = '<br><nobr><b>No storage configured yet!</b></nobr>';
		$box_content = '<br><br><br><br>Please create a '.$deployment->storagedescription.' first!<br>';
		$box_content .= '<a href="/openqrm/base/server/storage/storage-new.php?currenttab=tab1"><b>New storage</b></a><br>';
		$box->content = $box_content;
		$disp .= $box->get_string();
	}

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './image-template1.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'storage_server_table' => $disp,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function deployment_template_select($image_id, $local_deployment_method, $storage_id) {
	global $BaseDir, $OPENQRM_USER, $thisfile;

	$image = new image();
	$image->get_instance_by_id($image_id);
	$deployment = new deployment();
	$deployment->get_instance_by_type($local_deployment_method);
	$storage = new storage();
	$storage->get_instance_by_id($storage_id);

	$disp = "<h1>Select ".$deployment->name." Deployment Template for  Image ".$image->name." on Deployment Server ".$storage->id."</h1>";

	// require template-deployment file
	$local_deployment_templates_identifier_hook = $BaseDir."/boot-service/template.".$deployment->type.".php";
	require_once "$local_deployment_templates_identifier_hook";
	$get_deployment_templates_function="get_"."$deployment->type"."_templates";
	$get_deployment_templates_function=str_replace("-", "_", $get_deployment_templates_function);
	$local_deployment_templates_arr = $get_deployment_templates_function($storage_id);
	$local_deployment_templates_select = htmlobject_select('local_deployment_template', $local_deployment_templates_arr, 'Installation');

	// get additional optional local-deployment parameters from the template hook
	$get_additional_parameters_function="get_"."$deployment->type"."_additional_parameters";
	$get_additional_parameters_function=str_replace("-", "_", $get_additional_parameters_function);
	$additional_local_deployment_parameter = $get_additional_parameters_function();

	// persistent deployment ?
	$local_deployment_persistent_arr = array();
	$local_deployment_persistent_arr[] = array("value" => "0", "label" => "First boot");
	$local_deployment_persistent_arr[] = array("value" => "1", "label" => "Persistent");
	$local_deployment_persistent =  htmlobject_select('local_deployment_persistent', $local_deployment_persistent_arr, 'Mode');

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './image-template2.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'local_deployment_templates_select' => $local_deployment_templates_select,
		'local_deployment_persistent' => $local_deployment_persistent,
		'local_deployment_additional_parameter' => $additional_local_deployment_parameter,
		'local_deployment_hidden_image_id' => "<input type='hidden' name='image_id' value=".$image_id.">",
		'local_deployment_hidden_storage_id' => "<input type='hidden' name='storage_id' value=".$storage_id.">",
		'local_deployment_hidden_local_deployment_method' => "<input type='hidden' name='local_deployment_method' value=".$local_deployment_method.">",
		'submit' => htmlobject_input('action', array("value" => 'Save', "label" => 'Save'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}




function deployment_template_save($image_id, $local_deployment_method, $storage_id) {
	global $BaseDir, $OPENQRM_USER, $thisfile;

	$image = new image();
	$image->get_instance_by_id($image_id);
	$deployment = new deployment();
	$deployment->get_instance_by_type($local_deployment_method);
	$storage = new storage();
	$storage->get_instance_by_id($storage_id);

	$disp = "<h1>Saved ".$deployment->name." Deployment Template<br>for Image ".$image->name."</h1>";

   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './image-template3.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
	));
	$disp .=  $t->parse('out', 'tplfile');
	return $disp;
}






$output = array();
switch ($step) {
	case '1':
		$output[] = array('label' => 'Deployment', 'value' => deployment_server_select($image_id, $local_deployment_method));
		break;

	case '2':
		$output[] = array('label' => 'Deployment Template', 'value' => deployment_template_select($image_id, $local_deployment_method, $storage_id));
		break;

	case '3':
		$output[] = array('label' => 'Saved Template', 'value' => deployment_template_save($image_id, $local_deployment_method, $storage_id));
		break;

}

?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="image.css" />
<?php
echo htmlobject_tabmenu($output);
?>
