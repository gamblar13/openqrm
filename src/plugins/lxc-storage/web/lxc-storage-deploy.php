<!doctype html>
<html lang="en">
<head>
	<title>LXC Storage manager</title>
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

    Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special lxc-template classe
require_once "$RootDir/plugins/lxc-storage/class/lxc-template.class.php";
// post params
$lvm_storage_id = htmlobject_request('lvm_storage_id');
$lvm_volume_group = htmlobject_request('lvm_volume_group');
$lvm_lun_name=htmlobject_request('lvm_lun_name');
$lxc_template_name=htmlobject_request('lxc_template_name');
$lxc_template_url=htmlobject_request('lxc_template_url');
$lxc_template_description=htmlobject_request('lxc_template_description');

global $lvm_storage_id;
global $lvm_volume_group;
global $lvm_lun_name;
global $lxc_template_name;
global $lxc_template_url;
global $lxc_template_description;
$action = htmlobject_request('action');
$refresh_delay=1;
$refresh_loop_max=20;

global $OPENQRM_SERVER_BASE_DIR;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect_deploy($strMsg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name) {
    global $thisfile;
    global $action;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab=tab0&redirect=yes&action='.$action.'&lvm_storage_id='.$lvm_storage_id.'&lvm_volume_group='.$lvm_volume_group.'&lvm_lun_name='.$lvm_lun_name;
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




// running the actions
if(htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'deploy':
                if (isset($_REQUEST['identifier'])) {
                    show_progressbar();
                    foreach($_REQUEST['identifier'] as $lxc_template_name) {
                        $lxc_storage = new storage();
                        $lxc_storage->get_instance_by_id($lvm_storage_id);
                        $lxc_storage_resource = new resource();
                        $lxc_storage_resource->get_instance_by_id($lxc_storage->resource_id);
                        // send command
                        $lxc_template_deploy_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage deploy_lxc_template -t $lxc_template_name -n $lvm_lun_name -v $lvm_volume_group";
                        $lxc_storage_resource->send_command($lxc_storage_resource->ip, $lxc_template_deploy_cmd);
                        $redir_msg = "Deploying lxc-template $lxc_template_name to volume $lvm_lun_name";
                        redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
                        exit(0);
                    }
                }
                break;

            case 'reload':
                show_progressbar();
                $redir_msg = "Reloading lxc-templates";
                redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
                break;

            case 'remove':
                if (isset($_REQUEST['identifier'])) {
                    show_progressbar();
                    foreach($_REQUEST['identifier'] as $lxc_template_name) {
                        // send command
                        $lxc_template_remove_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage remove_lxc_template -t $lxc_template_name";
                        $openqrm_server->send_command($lxc_template_remove_cmd);
                        // remove from db
                        $lxc_template = new lxctemplate();
                        $lxc_template->get_instance_by_name($lxc_template_name);
                        if ($lxc_template->id > 0) {
                            $lxc_template->remove_by_name($lxc_template_name);
                        }
                        $redir_msg = "Removing lxc-template $lxc_template_name";
                        sleep(4);
                        redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
                        exit(0);
                    }
                }
                break;

            case 'update':
                if (isset($_REQUEST['identifier'])) {
                    show_progressbar();
                    foreach($_REQUEST['identifier'] as $lxc_template_name) {
                        // remove from db
                        $lxc_template = new lxctemplate();
                        $lxc_template->get_instance_by_name($lxc_template_name);
                        if ($lxc_template->id > 0) {
                            $fields = array();
                            $fields['lxc_template_description'] = "$lxc_template_description[$lxc_template_name]";
                            $lxc_template->update($lxc_template->id, $fields);
                            $redir_msg = "Updating lxc-template $lxc_template->id / $lxc_template_name with $lxc_template_description[$lxc_template_name]";
                        }
                        redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
                        exit(0);
                    }
                }
                break;

            case 'download':
                if (strlen($lxc_template_url)) {
                    show_progressbar();
                    // send command
                    $lxc_template_download_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage download_lxc_template -d $lxc_template_url";
                    $openqrm_server->send_command($lxc_template_download_cmd);
                    $redir_msg = "Downloading lxc-template from $lxc_template_url";
                    redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
                    exit(0);
                }
                break;


        }
	}
}


// get the list of temmplates
$statfile="storage/lxc-templates.stat";
if (file_exists($statfile)) {
    unlink($statfile);
}
// send command
$lxc_get_lxc_templates_cmd = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/lxc-storage/bin/openqrm-lxc-storage get_lxc_templates";
$openqrm_server->send_command($lxc_get_lxc_templates_cmd);
if (!wait_for_statfile($statfile)) {
    $redir_msg = "Error during displaying the available lxc-templates";
    redirect_deploy($redir_msg, $lvm_storage_id, $lvm_volume_group, $lvm_lun_name);
}




function lxc_select_template($lvm_storage_id, $lvm_volume_group, $lvm_lun_name) {
	global $OPENQRM_USER;
	global $thisfile;
	global $RootDir;

    // lvm table
    $table = new htmlobject_table_builder('lxc_template_name', '', '', '', 'lxc_template_name');
	$arHead = array();
	$arHead['lxc_template_icon'] = array();
	$arHead['lxc_template_icon']['title'] ='';
	$arHead['lxc_template_icon']['sortable'] = false;

    $arHead['lxc_template_name'] = array();
	$arHead['lxc_template_name']['title'] ='Template';

    $arHead['lxc_template_size'] = array();
	$arHead['lxc_template_size']['title'] ='Size';

	$arHead['lxc_template_description'] = array();
	$arHead['lxc_template_description']['title'] ='Description';

    $arBody = array();
	$lxc_template_count=0;
	$storage_icon="/openqrm/base/plugins/lxc-storage/img/storage.png";
    $storage_export_list="storage/lxc-templates.stat";
	if (file_exists($storage_export_list)) {
		$storage_vg_content=file($storage_export_list);
		foreach ($storage_vg_content as $index => $lvm) {
            $lxc_line = trim($lvm);

            $first_at_pos = strpos($lxc_line, "@");
            $first_at_pos++;
            $lxc_line_first_at_removed = substr($lxc_line, $first_at_pos, strlen($lxc_line)-$first_at_pos);
            $second_at_pos = strpos($lxc_line_first_at_removed, "@");
            $second_at_pos++;
            $lxc_line_second_at_removed = substr($lxc_line_first_at_removed, $second_at_pos, strlen($lxc_line_first_at_removed)-$second_at_pos);
            $third_at_pos = strpos($lxc_line_second_at_removed, "@");
            $third_at_pos++;
            $lxc_line_third_at_removed = substr($lxc_line_second_at_removed, $third_at_pos, strlen($lxc_line_second_at_removed)-$third_at_pos);
            $fourth_at_pos = strpos($lxc_line_third_at_removed, "@");
            $fourth_at_pos++;
            $lxc_line_fourth_at_removed = substr($lxc_line_third_at_removed, $fourth_at_pos, strlen($lxc_line_third_at_removed)-$fourth_at_pos);
            $fivth_at_pos = strpos($lxc_line_fourth_at_removed, "@");
            $fivth_at_pos++;
            $lxc_line_fivth_at_removed = substr($lxc_line_fourth_at_removed, $fivth_at_pos, strlen($lxc_line_fourth_at_removed)-$fivth_at_pos);
            $sixth_at_pos = strpos($lxc_line_fivth_at_removed, "@");
            $sixth_at_pos++;
            $lxc_line_sixth_at_removed = substr($lxc_line_fivth_at_removed, $sixth_at_pos, strlen($lxc_line_fivth_at_removed)-$sixth_at_pos);
            $seventh_at_pos = strpos($lxc_line_sixth_at_removed, "@");
            $seventh_at_pos++;

            $lxc_template_name = trim(substr($lxc_line, 0, $first_at_pos-1));
            $lxc_template_size = trim(substr($lxc_line_first_at_removed, 0, $second_at_pos-1));
            // get template object
            $lxc_template = new lxctemplate();
            $lxc_template->get_instance_by_name($lxc_template_name);
            if ($lxc_template->id > 0) {
                $lxc_template_description = htmlobject_input("lxc_template_description[$lxc_template_name]", array("value" => "$lxc_template->description", "label" => ''), 'text', 200);
            } else {
                // if not exit, create it
                $fields = array();
                $fields["lxc_template_id"] = openqrm_db_get_free_id('lxc_template_id', $lxc_template->_db_table);
                $fields['lxc_template_name'] = $lxc_template_name;
                $fields['lxc_template_description'] = "";
                $lxc_template->add($fields);
                $lxc_template_description = htmlobject_input("lxc_template_description[$lxc_template_name]", array("value" => 'not exist, creating', "label" => ''), 'text', 200);
            }
    
            $arBody[] = array(
                'lxc_template_icon' => "<img width=24 height=24 src=$storage_icon><input type='hidden' name='lvm_storage_id' value=$lvm_storage_id><input type='hidden' name='lvm_volume_group' value=$lvm_volume_group><input type='hidden' name='lvm_lun_name' value=$lvm_lun_name>",
                'lxc_template_name' => $lxc_template_name,
                'lxc_template_size' => $lxc_template_size,
                'lxc_template_description' => $lxc_template_description,
            );
            $lxc_template_count++;
		}
	}

    $table->add_headrow("<input type='hidden' name='lvm_storage_id' value=$lvm_storage_id>");
    $table->add_headrow("<input type='hidden' name='lvm_volume_group' value=$lvm_volume_group>");
    $table->add_headrow("<input type='hidden' name='lvm_lun_name' value=$lvm_lun_name>");
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
		$table->bottom = array('deploy', 'update', 'remove');
		$table->identifier = 'lxc_template_name';
	}
	$table->max = $lxc_template_count;


    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'lxc-storage-deploy.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'lxc_templates_table' => $table->get_string(),
		'lvm_lun_name' => $lvm_lun_name,
		'lvm_volume_group' => $lvm_volume_group,
        'lxc_template_url' => htmlobject_input('lxc_template_url', array("value" => '', "label" => 'URL'), 'text', 200),
		'hidden_lvm_lun_name' => "<input type='hidden' name='lvm_lun_name' value=$lvm_lun_name>",
		'hidden_lvm_volume_group' => "<input type='hidden' name='lvm_volume_group' value=$lvm_volume_group>",
    	'hidden_lvm_storage_id' => "<input type='hidden' name='lvm_storage_id' value=$lvm_storage_id>",
		'submit' => htmlobject_input('action', array("value" => 'download', "label" => 'download'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;

}







$output = array();
$output[] = array('label' => 'LXC Templates', 'value' => lxc_select_template($lvm_storage_id, $lvm_volume_group, $lvm_lun_name));


?>
<style>
    .htmlobject_tab_box {
        width:800px;
    }
</style>
<?php

echo htmlobject_tabmenu($output);

?>


