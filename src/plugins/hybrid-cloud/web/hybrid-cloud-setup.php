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
$image_id = htmlobject_request('image_id');
$hybrid_cloud_id = htmlobject_request('hybrid_cloud_id');


global $OPENQRM_SERVER_BASE_DIR;
// set ip
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
// set refresh timeout
$refresh_delay=1;
$refresh_loop_max=20;
// actions
if (!strlen($step)) {
    $step=1;
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
		case 'save':
            show_progressbar();
            $hybrid_cloud_account_name = htmlobject_request('hybrid_cloud_account_name');
            $hybrid_cloud_account_type = htmlobject_request('hybrid_cloud_account_type');
            $hybrid_cloud_rc_config = htmlobject_request('hybrid_cloud_rc_config');
            $hybrid_cloud_ssh_key = htmlobject_request('hybrid_cloud_ssh_key');
            $hybrid_cloud_description = htmlobject_request('hybrid_cloud_description');

            // check user input
            if (!strlen($hybrid_cloud_account_name)) {
                $redir_msg = "Cloud account name empty. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!strlen($hybrid_cloud_rc_config)) {
                $redir_msg = "Cloud rc-config file empty. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!strlen($hybrid_cloud_ssh_key)) {
                $redir_msg = "Cloud ssh-key empty. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            // check if user name exists already
            $hybrid_cloud = new hybrid_cloud();
            $hybrid_cloud->get_instance_by_name($hybrid_cloud_account_name);
            if (strlen($hybrid_cloud->id)) {
                $redir_msg = "Cloud account name already exists. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }

            /* in most cases this will be a hidden file which php cannot check if existing
             if (!file_exists($hybrid_cloud_rc_config)) {
                $redir_msg = "Cloud rc-config file does not exist. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
            if (!file_exists($hybrid_cloud_ssh_key)) {
                $redir_msg = "Cloud ssh-key does not exist. Not creating new account entry <br>";
                redirect($redir_msg, '', '');
                exit(0);
            }
             */

            $fields = array();
            $fields["hybrid_cloud_id"] = openqrm_db_get_free_id('hybrid_cloud_id', $hybrid_cloud->_db_table);
            $fields['hybrid_cloud_account_name'] = $hybrid_cloud_account_name;
            $fields['hybrid_cloud_account_type'] = $hybrid_cloud_account_type;
            $fields['hybrid_cloud_rc_config'] = $hybrid_cloud_rc_config;
            $fields['hybrid_cloud_ssh_key'] = $hybrid_cloud_ssh_key;
            $fields['hybrid_cloud_description'] = $hybrid_cloud_description;
            $hybrid_cloud->add($fields);
            $redir_msg = "Created new Hybrid Cloud account configuration <br>";
            redirect($redir_msg, '', '');
            break;

		case 'remove':
            if (isset($_REQUEST['identifier'])) {
                show_progressbar();
                foreach($_REQUEST['identifier'] as $id) {
                    $hybrid_cloud = new hybrid_cloud();
                    $hybrid_cloud->remove($id);
                    $redir_msg .= "Removed Hybrid Cloud account configuration $id <br>";
                }
                redirect($redir_msg, '', '');
            }
            break;
	}
}




function hybrid_cloud_setup_account() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_BASE_DIR;
	global $thisfile;

	$table = new htmlobject_db_table('hybrid_cloud_id');
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

	$hybrid_cloud_count=1;
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
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('remove');
		$table->identifier = 'hybrid_cloud_id';
	}
    $table->max = $hybrid_cloud_tmp->get_count();

    // create new hybrid-cloud account
    $hybrid_cloud_account_name = htmlobject_input('hybrid_cloud_account_name', array("value" => htmlobject_request('hybrid_cloud_account_name'), "label" => 'Account Name'), 'text', 50);
    $hybrid_cloud_rc_config = htmlobject_input('hybrid_cloud_rc_config', array("value" => htmlobject_request('hybrid_cloud_rc_config'), "label" => 'rc-config (file)'), 'text', 255);
    $hybrid_cloud_ssh_key = htmlobject_input('hybrid_cloud_ssh_key', array("value" => htmlobject_request('hybrid_cloud_ssh_key'), "label" => 'SSH-Key (file)'), 'text', 255);
    $hybrid_cloud_description = htmlobject_input('hybrid_cloud_description', array("value" => htmlobject_request('hybrid_cloud_description'), "label" => 'Description'), 'text', 255);
    // account type select
    $ar = array();
    $ar[] = array('value'=> 'uec', 'label'=> 'Ubuntu Enterprise Cloud');
    $ar[] = array('value'=> 'aws', 'label'=> 'Amazon Cloud');
    $ar[] = array('value'=> 'euca', 'label'=> 'Eucalyptus Cloud');
    $account_type_select = htmlobject_select("hybrid_cloud_account_type", $ar , 'Type:', 0);

    // set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'hybrid-cloud-setup-account.tpl.php');
	$t->setVar(array(
        'thisfile' => $thisfile,
		'hybrid_cloud_table' => $table->get_string(),
		'hybrid_cloud_account_name' => $hybrid_cloud_account_name,
		'hybrid_cloud_account_type' => $hybrid_cloud_account_type,
		'hybrid_cloud_rc_config' => $hybrid_cloud_rc_config,
		'hybrid_cloud_ssh_key' => $hybrid_cloud_ssh_key,
		'hybrid_cloud_description' => $hybrid_cloud_description,
		'hybrid_cloud_account_type_select' => $account_type_select,
        'submit_save' => htmlobject_input('action', array("value" => 'save', "label" => 'save'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}


$output = array();
$output[] = array('label' => 'Accounts', 'value' => hybrid_cloud_setup_account());


?>
<script type="text/javascript">
    $("#progressbar").remove();
</script>
<?php

echo htmlobject_tabmenu($output);

?>
