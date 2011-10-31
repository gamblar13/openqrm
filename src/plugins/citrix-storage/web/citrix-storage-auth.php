<!doctype html>
<html lang="en">
<head>
	<title>Citrix-storage Authentication Manager</title>
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
$refresh_delay=1;
$refresh_loop_max=40;

$citrix_storage_server_id = htmlobject_request('citrix_storage_server_id');
$citrix_storage_server_ip = htmlobject_request('citrix_storage_server_ip');
$citrix_storage_server_user = htmlobject_request('citrix_storage_server_user');
$citrix_storage_server_password = htmlobject_request('citrix_storage_server_password');
$auth_action = htmlobject_request('auth_action');

// place for the citrix-storage stat files
$citrix_storage_dir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/citrix-storage/citrix-storage-stat';


$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;


function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	global $citrix_storage_server_id;
	global $citrix_storage_server_ip;
	if($url == '') {
		$url = 'citrix-storage-vm-manager.php?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_storage_server_id='.$citrix_storage_server_id;
	} else {
		$url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab.'&citrix_storage_server_id='.$citrix_storage_server_id.'&citrix_storage_server_ip='.$citrix_storage_server_ip;
	}
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



// Dom0 actions
$strMsg = '';
if(htmlobject_request('auth_action') != '') {
	switch ($auth_action) {
		case 'authenticate':
			if (!strlen($citrix_storage_server_user)) {
				$strMsg .= "Citrix-storage XenServer user not set. Not setting/updating authentication!";
				redirect($strMsg, "tab0", $thisfile);
			}
			if (!strlen($citrix_storage_server_password)) {
				$strMsg .= "Citrix-storage XenServer password not set. Not setting/updating authentication!";
				redirect($strMsg, "tab0", $thisfile);
			}
			if (!strlen($citrix_storage_server_ip)) {
				$strMsg .= "Citrix-storage XenServer server-ip not set. Not setting/updating authentication!";
				redirect($strMsg, "tab0", $thisfile);
			}
			if (!strlen($citrix_storage_server_id)) {
				$strMsg .= "Citrix-storage XenServer server-id not set. Not setting/updating authentication!";
				redirect($strMsg, "tab0", $thisfile);
			}
			$auth_file=$citrix_storage_dir.'/citrix-storage-host.pwd.'.$citrix_storage_server_ip;
			$fp = fopen($auth_file, 'w+');
			fwrite($fp, $citrix_storage_server_user);
			fwrite($fp, "\n");
			fwrite($fp, $citrix_storage_server_password);
			fwrite($fp, "\n");
			fclose($fp);
			$strMsg .= "Authenticated Citrix-storage XenServer $citrix_storage_server_ip";
			redirect($strMsg, "tab0");
			break;

		default:
			$strMsg .= "No such auth_action $auth_action <br>";
			redirect($strMsg, "tab0");
			break;

	}
}






function citrix_storage_auth() {
	global $citrix_storage_server_id;
	global $thisfile;

	$citrix_storage_server_tmp = new appliance();
	$citrix_storage_server_tmp->get_instance_by_id($citrix_storage_server_id);
	$citrix_storage_server_resource = new resource();
	$citrix_storage_server_resource->get_instance_by_id($citrix_storage_server_tmp->resources);
	$citrix_storage_server_ip = $citrix_storage_server_resource->ip;

	// set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'citrix-storage-auth.tpl.php');
	$t->setVar(array(
		'formaction' => $thisfile,
		'citrix_storage_server_user' => htmlobject_input('citrix_storage_server_user', array("value" => '', "label" => 'Username'), 'text', 20),
		'citrix_storage_server_password' => htmlobject_input('citrix_storage_server_password', array("value" => '', "label" => 'Password'), 'password', 20),
		'citrix_storage_server_id' => $citrix_storage_server_id,
		'hidden_citrix_storage_server_id' => "<input type=\"hidden\" name=\"citrix_storage_server_id\" value=\"$citrix_storage_server_id\">",
		'hidden_citrix_storage_server_ip' => "<input type=\"hidden\" name=\"citrix_storage_server_ip\" value=\"$citrix_storage_server_ip\">",
		'hidden_action' => "<input type=\"hidden\" name=\"auth_action\" value=\"authenticate\">",
		'backlink' => '<a href=citrix-storage-manager.php?citrix_storage_server_id='.$citrix_storage_server_id.'><strong>Back</strong></a>',
		'submit' => htmlobject_input('submit_action', array("value" => 'Set', "label" => 'Set'), 'submit'),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



$output = array();
// if admin
if ($OPENQRM_USER->role == "administrator") {
	$output[] = array('label' => 'Authenticate with Citrix-storage Server', 'value' => citrix_storage_auth());
}

echo htmlobject_tabmenu($output);

?>


