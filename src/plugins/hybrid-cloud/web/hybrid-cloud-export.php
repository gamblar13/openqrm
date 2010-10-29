<!doctype html>
<html lang="en">
<head>
	<title>Hybrid-Cloud manager</title>
    <link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
    <link rel="stylesheet" type="text/css" href="hybrid-cloud.css" />
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

    Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
*/


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special hybrid-cloud classe
require_once "$RootDir/plugins/hybrid-cloud/class/hybrid-cloud.class.php";

// post parameters
$step = htmlobject_request('step');
$instance_id = htmlobject_request('instance_id');
$image_id = htmlobject_request('image_id');
$hybrid_cloud_id = htmlobject_request('hybrid_cloud_id');
$hybrid_cloud_ami_export_location = htmlobject_request('hybrid_cloud_ami_export_location');
global $hybrid_cloud_ami_export_location;
global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
// set refresh timeout
$refresh_delay=1;
$refresh_loop_max=40;
// actions
if (!strlen($step)) {
    $step=1;
}



function redirect($strMsg, $currenttab = 'tab0', $url = '', $step, $hybrid_cloud_id) {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&step='.$step.'&hybrid_cloud_id='.$hybrid_cloud_id;
	}
	// using meta refresh because of the java-script in the header
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



if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'select':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    show_progressbar();
                    redirect($redir_msg, '', '', 2, $id);
                    break;
                }
            }
			break;

		case 'next':
            if (isset($_REQUEST['identifier'])) {
                foreach($_REQUEST['identifier'] as $id) {
                    $image_id = $id;
                    $hybrid_cloud_id = htmlobject_request('hybrid_cloud_id');
                    $step=3;
                    break;
                }
                break;
            }
            $redir_msg = "No Server Image selected. Skipping export ...";
            redirect($redir_msg, '', '', 1, 0);
			break;

		case 'export':
            $image_id = htmlobject_request('image_id');
            $hybrid_cloud_id = htmlobject_request('hybrid_cloud_id');
            $hybrid_cloud_ami_name = htmlobject_request('hybrid_cloud_ami_name');
            $hybrid_cloud_ami_size = htmlobject_request('hybrid_cloud_ami_size');
            $hybrid_cloud_ami_arch = htmlobject_request('hybrid_cloud_ami_arch');
            $step=4;
            if (!strlen($image_id)) {
                $step=1;
                $redir_msg = "No Server Image selected. Skipping export ...";
                redirect($redir_msg, '', '', 1, 0);
            }
            if (strlen($hybrid_cloud_ami_name) < 8) {
                $step=1;
                $redir_msg = "AMI Name empty or too short (min. 8 character). Skipping export ...";
                redirect($redir_msg, '', '', 1, 0);
            }
			break;

	}
}




function hybrid_cloud_select_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

    $table = new htmlobject_table_builder('hybrid_cloud_id', '', '', '', 'select');
    $arHead = array();

	$arHead['hybrid_cloud_id'] = array();
	$arHead['hybrid_cloud_id']['title'] ='Id';

	$arHead['hybrid_cloud_account_name'] = array();
	$arHead['hybrid_cloud_account_name']['title'] ='Name';

    $arHead['hybrid_cloud_account_type'] = array();
	$arHead['hybrid_cloud_account_type']['title'] ='Type';

	$arHead['hybrid_cloud_rc_config'] = array();
	$arHead['hybrid_cloud_rc_config']['title'] ='Config';

	$arHead['hybrid_cloud_ssh_key'] = array();
	$arHead['hybrid_cloud_ssh_key']['title'] ='SSH-key';

	$arHead['hybrid_cloud_description'] = array();
	$arHead['hybrid_cloud_description']['title'] ='Description';

	$hybrid_cloud_count=0;
	$hybrid_cloud_tmp = new hybrid_cloud();
	$hybrid_cloud_array = $hybrid_cloud_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	$arBody = array();
	foreach ($hybrid_cloud_array as $index => $hybrid_cloud_db) {
		$arBody[] = array(
			'hybrid_cloud_id' => $hybrid_cloud_db["hybrid_cloud_id"],
			'hybrid_cloud_account_name' => $hybrid_cloud_db["hybrid_cloud_account_name"],
			'hybrid_cloud_account_type' => $hybrid_cloud_db["hybrid_cloud_account_type"],
			'hybrid_cloud_rc_config' => $hybrid_cloud_db["hybrid_cloud_rc_config"],
			'hybrid_cloud_ssh_key' => $hybrid_cloud_db["hybrid_cloud_ssh_key"],
			'hybrid_cloud_description' => $hybrid_cloud_db["hybrid_cloud_description"],
		);
		$hybrid_cloud_count++;
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
		$table->identifier = 'hybrid_cloud_id';
	}
	$table->max = $hybrid_cloud_tmp->get_count();
    // is there at least one account setup already ?
    if ($hybrid_cloud_count == 0) {
        $hybrid_cloud_account_hint = "<h4>No account configured yet.<br>Click <a href='hybrid-cloud-setup.php'><strong>here</strong></a> to setup an account now.</h4>";
    }
   // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'hybrid-cloud-select-account.tpl.php');
	$t->setVar(array(
		'hybrid_cloud_table' => $table->get_string(),
		'hybrid_cloud_account_hint' => $hybrid_cloud_account_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





function image_storage_select($hybrid_cloud_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$image_tmp = new image();
	$image_icon = "/openqrm/base/img/image.png";
    // nfs table
    $table = new htmlobject_table_builder('image_id', '', '', '', 'nfs');

	$arHead = array();
	$arHead['image_icon'] = array();
	$arHead['image_icon']['title'] ='';
	$arHead['image_icon']['sortable'] = false;

	$arHead['image_id'] = array();
	$arHead['image_id']['title'] ='ID';

	$arHead['image_name'] = array();
	$arHead['image_name']['title'] ='Name';

	$arHead['image_version'] = array();
	$arHead['image_version']['title'] ='Version';

	$arHead['image_type'] = array();
	$arHead['image_type']['title'] ='Deployment Type';

	$arHead['image_comment'] = array();
	$arHead['image_comment']['title'] ='Comment';

    $image_nfs_count=0;
	$arBody = array();
	$image_array = $image_tmp->display_overview_per_type("nfs-deployment", $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);
        $arBody[] = array(
            'image_icon' => "<img width=20 height=20 src=$image_icon>",
            'image_id' => $image_db["image_id"],
            'image_name' => $image_db["image_name"],
            'image_version' => $image_db["image_version"],
            'image_type' => "$image_deployment->description",
            'image_comment' => $image_db["image_comment"],
        );
        $image_nfs_count++;
	}

    // are there any active hybrid-cloud instances ? if not give a hint
    if ($image_nfs_count == 0) {
        $create_nfs_image_hint = "<h4>There are no NFS-Server-Images available.";
        $create_nfs_image_hint .= " Please create a <a href=\"/openqrm/base/server/image/image-new.php?currenttab=tab1\"><strong>NFS-Server-Image</strong></a></h4>";
    }

    $table->add_headrow("<input type=\"hidden\" name=\"hybrid_cloud_id\" value=\"$hybrid_cloud_id\"><input type=\"hidden\" name=\"step\" value=\"2\">");
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
    $table->autosort = true;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('next');
		$table->identifier = 'image_id';
	}
    $table->max = $image_tmp->get_count_per_type("nfs-deployment");


    // lvm-nfs table
    $table1 = new htmlobject_table_builder('image_id', '', '', '', 'lvmnfs');

	$arHead1 = array();
	$arHead1['image_icon'] = array();
	$arHead1['image_icon']['title'] ='';
	$arHead1['image_icon']['sortable'] = false;

	$arHead1['image_id'] = array();
	$arHead1['image_id']['title'] ='ID';

	$arHead1['image_name'] = array();
	$arHead1['image_name']['title'] ='Name';

	$arHead1['image_version'] = array();
	$arHead1['image_version']['title'] ='Version';

	$arHead1['image_type'] = array();
	$arHead1['image_type']['title'] ='Deployment Type';

	$arHead1['image_comment'] = array();
	$arHead1['image_comment']['title'] ='Comment';

    $image_lvmnfs_count=0;
	$arBody1 = array();
	$image_array = $image_tmp->display_overview_per_type("lvm-nfs-deployment", $table->offset, $table->limit, $table->sort, $table->order);
	foreach ($image_array as $index => $image_db) {
		$image = new image();
		$image->get_instance_by_id($image_db["image_id"]);
		$image_deployment = new deployment();
		$image_deployment->get_instance_by_type($image_db["image_type"]);
        $arBody1[] = array(
            'image_icon' => "<img width=20 height=20 src=$image_icon>",
            'image_id' => $image_db["image_id"],
            'image_name' => $image_db["image_name"],
            'image_version' => $image_db["image_version"],
            'image_type' => "$image_deployment->description",
            'image_comment' => $image_db["image_comment"],
        );
        $image_lvmnfs_count++;
	}

    // are there any active hybrid-cloud instances ? if not give a hint
    if ($image_lvmnfs_count == 0) {
        $create_lvn_nfs_image_hint = "<h4>There are no LVM-NFS-Server-Images available.";
        $create_lvn_nfs_image_hint .= " Please create a <a href=\"/openqrm/base/server/image/image-new.php?currenttab=tab1\"><strong>LVM-NFS Server-Image</strong></a></h4>";
    }

    $table1->add_headrow("<input type=\"hidden\" name=\"hybrid_cloud_id\" value=\"$hybrid_cloud_id\"><input type=\"hidden\" name=\"step\" value=\"2\">");
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
		$table1->bottom = array('next');
		$table1->identifier = 'image_id';
	}
    $table1->max = $image_tmp->get_count_per_type("lvm-nfs-deployment");
    
    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'hybrid-cloud-export.tpl.php');
	$t->setVar(array(
		'image_nfs_table' => $table->get_string(),
        'create_nfs_image_hint' => $create_nfs_image_hint,
		'image_lvm_nfs_table' => $table1->get_string(),
        'create_lvn_nfs_image_hint' => $create_lvn_nfs_image_hint,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}






function hybrid_cloud_ami_setup($image_id, $hybrid_cloud_id) {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

    $hidden_hybrid_cloud_id = "<input type=\"hidden\" name=\"hybrid_cloud_id\" value=\"$hybrid_cloud_id\">";
    $hidden_image_id = "<input type=\"hidden\" name=\"image_id\" value=\"$image_id\">";
    $hybrid_cloud_ami_name = htmlobject_input('hybrid_cloud_ami_name', array("value" => htmlobject_request('hybrid_cloud_ami_name'), "label" => 'AMI Name'), 'text', 20);
    $hybrid_cloud_ami_size = "AMI Size <select name=\"hybrid_cloud_ami_size\" size=\"1\"><option value=\"500\">500 MB</option><option value=\"1000\">1 GB</option><option value=\"2000\">2 GB</option><option value=\"5000\">5 GB</option><option value=\"10000\">10 GB</option></select>";
    $hybrid_cloud_ami_arch = "AMI Arch <select name=\"hybrid_cloud_ami_arch\" size=\"1\"><option value=\"x86_64\">x86_64</option><option value=\"i386\">i386</option></select>";
    // check if to add location-select box for aws exports
    $hybrid_cloud = new hybrid_cloud();
    $hybrid_cloud->get_instance_by_id($hybrid_cloud_id);
    if (!strcmp($hybrid_cloud->account_type, "aws")) {
        // account type select
        $ars = array();
        $ars[] = array('value'=> 'US', 'label'=> 'US Standard - US');
        $ars[] = array('value'=> 'EU', 'label'=> 'Europe - EU');
        $ars[] = array('value'=> 'us-west-1', 'label'=> 'US West - us-west-1');
        $ars[] = array('value'=> 'ap-southeast-1', 'label'=> 'Asia East - ap-southeast-1');
        $hybrid_cloud_ami_export_location_select = htmlobject_select("hybrid_cloud_ami_export_location", $ars , 'Export to ', 0);
    }

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'hybrid-cloud-export-setup.tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
		'hidden_hybrid_cloud_id' => $hidden_hybrid_cloud_id,
		'hidden_image_id' => $hidden_image_id,
		'hybrid_cloud_ami_name' => $hybrid_cloud_ami_name,
		'hybrid_cloud_ami_size' => $hybrid_cloud_ami_size,
		'hybrid_cloud_ami_arch' => $hybrid_cloud_ami_arch,
		'hybrid_cloud_export_location_select' => $hybrid_cloud_ami_export_location_select,
        'submit_save' => htmlobject_input('action', array("value" => 'export', "label" => 'export'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}





function hybrid_cloud_export_final($image_id, $hybrid_cloud_id, $hybrid_cloud_ami_name, $hybrid_cloud_ami_size, $hybrid_cloud_ami_arch) {
	global $openqrm_server;
	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;
    global $hybrid_cloud_ami_export_location;
	// here we execute the request !
	$image_count=1;
	$hybrid_cloud = new hybrid_cloud();
	$hybrid_cloud->get_instance_by_id($hybrid_cloud_id);
    $hybrid_cloud_account_name = $hybrid_cloud->account_name;
    $hybrid_cloud_account_type = $hybrid_cloud->account_type;
    $hybrid_cloud_rc_config = $hybrid_cloud->rc_config;
    $hybrid_cloud_ssh_key = $hybrid_cloud->ssh_key;

    $image = new image();
    $image->get_instance_by_id($image_id);
    $storage = new storage();
    $storage->get_instance_by_id($image->storageid);
    $resource = new resource();
    $resource->get_instance_by_id($storage->resource_id);
    $image_store = $resource->ip.":".$image->rootdevice;
    // for aws we need to send the location
    if (strlen($hybrid_cloud_ami_export_location)) {
        $ec2_cloud_location_parameter=" -l $hybrid_cloud_ami_export_location";
    }

    // send command
    $hybrid_cloud_run_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/hybrid-cloud/bin/openqrm-hybrid-cloud export_image -i ".$hybrid_cloud_id." -n ".$hybrid_cloud_account_name." -c ".$hybrid_cloud_rc_config." -t ".$hybrid_cloud_account_type." -s ".$image_store." -m ".$hybrid_cloud_ami_size." -a ".$hybrid_cloud_ami_name." -r ".$hybrid_cloud_ami_arch." ".$ec2_cloud_location_parameter;

    // send command
	$openqrm_server->send_command($hybrid_cloud_run_command);

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'hybrid-cloud-export-final.tpl.php');
	$t->setVar(array(
        'image_id' => $image_id,
        'image_name' => $image->name,
		'instance_id' => $instance_id,
        'hybrid_cloud_id' => $hybrid_cloud_id,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
switch ($step) {
	case 1:
		$output[] = array('label' => 'Export', 'value' => hybrid_cloud_select_account());
		break;
	case 2:
		$output[] = array('label' => 'Export', 'value' => image_storage_select($hybrid_cloud_id));
		break;
	case 3:
		$output[] = array('label' => 'Export', 'value' => hybrid_cloud_ami_setup($image_id, $hybrid_cloud_id));
		break;
	case 4:
		$output[] = array('label' => 'Export', 'value' => hybrid_cloud_export_final($image_id, $hybrid_cloud_id, $hybrid_cloud_ami_name, $hybrid_cloud_ami_size, $hybrid_cloud_ami_arch));
		break;
	default:
		$output[] = array('label' => 'Export', 'value' => hybrid_cloud_select_account());
		break;
}

echo htmlobject_tabmenu($output);

?>
