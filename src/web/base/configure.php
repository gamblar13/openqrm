<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>openQRM Configuration</title>

	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="content-style-type" content="text/css">
	<meta http-equiv="content-script-type" content="text/javascript">
	<meta http-equiv="content-language" content="de">
	<meta name="date" content="2000-01-10T22:19:28+0100">
	<meta name="author" content="openQRM Enterprise GmbH">

	<link rel="stylesheet" type="text/css" href="/openqrm/base/css/default.css">
	<link rel="stylesheet" type="text/css" href="/openqrm/base/css/htmlobject.css">
	<link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet">
	<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>

<style type="text/css">
body {
background: url("img/header_dropShadow.png") repeat-x scroll 0 0 #DDDDDD;
}
.htmlobject_tabs {
	width: 800px;
	margin: 0 auto;
	float: none;
	height: 28px;
}
.htmlobject_tabs_box {
	position: relative;
	background: white;
	width: 770px;
	margin: 0 auto;
	border-left: 1px solid #999999;
	border-right: 1px solid #999999;
	border-bottom: 1px solid #999999;
	min-height: 400px;
	/* padding: 30px; */
}
.htmlobject_tabs_box h1 a {
	text-decoration: none;
}
.htmlobject_table {
	width: 100%;
}
.htmlobject_table .htmlobject_td.pageturn_head {
	display: none;
}
.ui-progressbar-value {
	background-image: url(/openqrm/base/img/progress.gif);
}
#steps {
	font-size: 13px;
	font-weight: normal;
	float: right;
	margin: 0 20px 0 0;
}
#config_text {
	float:left;
	width: 250px;
	margin: 40px 0 0 40px;
}
#config_table {
	float:left;
	width: 400px;
	margin: 40px 0 0 25px;
}
#progressbar {
	position: absolute;
	left: 150px;
	top: 250px;
	width: 400px;
	height: 20px;
}
#openqrm_logo {
	position: absolute;
	bottom: 20px;
	right: 15px;
	margin: 0 20px 0 0;
	text-align: center;
}
</style>
</head>
<body>



<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/

// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

require_once "$RootDir/class/htmlobjects/htmlobject.class.php";
$html = new htmlobject($RootDir.'/class/htmlobjects');
$html->debug();

global $html;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_DATABASE_TYPE;
global $OPENQRM_DATABASE_SERVER;
global $OPENQRM_DATABASE_NAME;
global $OPENQRM_DATABASE_USER;
global $OPENQRM_DATABASE_PASSWORD;

$openqrm_server = new openqrm_server();
$refresh_delay=1;
$refresh_loop_max=60;

// gather posts
$step = $html->request()->get('step');
$oqc_db_server = $html->request()->get('oqc_db_server');
$oqc_db_name = $html->request()->get('oqc_db_name');
$oqc_db_user = $html->request()->get('oqc_db_user');
$oqc_db_password = $html->request()->get('oqc_db_password');
$oqc_db_restore = $html->request()->get('oqc_db_restore');
$oqc_nic = $html->request()->get('oqc_nic');
// extra fields for oracle db
$oqc_db_ld_path = $html->request()->get('oqc_db_ld_path');
$oqc_db_home = $html->request()->get('oqc_db_home');
$oqc_db_tns = $html->request()->get('oqc_db_tns');



function redirect($strMsg) {
	global $thisfile;
	global $step;
	$url = $thisfile.'?msg='.urlencode($strMsg).'&step='.$step;
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

function wait_for_fileremoval($sfile) {
	global $refresh_delay;
	global $refresh_loop_max;
	$refresh_loop=0;
	while (file_exists($sfile)) {
		sleep($refresh_delay);
		$refresh_loop++;
		flush();
		if ($refresh_loop > $refresh_loop_max)  {
			return false;
		}
	}
	return true;
}

// gather the list of available network cards to setup openQRM on
// -> list is created by the init script
$oqc_available_nics = array();
if (file_exists("./unconfigured")) {
	$handle = @fopen("./unconfigured", "r");
	if ($handle) {
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strlen($buffer)) {
				$oqc_available_nics[] = trim($buffer);
			}
		}
		fclose($handle);
	}
} else {
	$html->response()->redirect('index.php');
}

// check if we got some actions to do
if($html->request()->get('action') !== '') {
	switch ($html->request()->get('action')) {
		case 'next':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $oqc_nic) {
					// create a lock file
					$nic_select_lock = "/tmp/openqrm-configure-nic.lock";
					if (file_exists($nic_select_lock)) {
						unlink($nic_select_lock);
					}
					$cmd_token = md5(uniqid(rand(), true));
					$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_SERVER_INTERFACE=.*/OPENQRM_SERVER_INTERFACE=$oqc_nic/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
					shell_exec($config_command);
					sleep(1);
					$lock_command = "touch $nic_select_lock && chmod 777 $nic_select_lock";
					shell_exec($lock_command);
					if (!wait_for_statfile($nic_select_lock)) {
						$strMsg="Error selecting Networkcard $oqc_nic<br>";
						$step=1;
					} else {
						$strMsg="Selected Networkcard $oqc_nic<br>";
						$step=2;
					}
					if (file_exists($nic_select_lock)) {
						unlink($nic_select_lock);
					}
					$step=2;
					$strMsg="Selected Networkcard $oqc_nic<br>";
					redirect($strMsg);
					break;
				}
			}
			break;

		case 'select':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $oqc_db_type) {
					// create a lock file
					$db_select_lock = "/tmp/openqrm-configure-db-select.lock";
					if (file_exists($db_select_lock)) {
						unlink($db_select_lock);
					}
					$cmd_token = md5(uniqid(rand(), true));
					$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_TYPE=.*/OPENQRM_DATABASE_TYPE=$oqc_db_type/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
					shell_exec($config_command);
					sleep(1);
					$lock_command = "touch $db_select_lock && chmod 777 $db_select_lock";
					shell_exec($lock_command);
					if (!wait_for_statfile($db_select_lock)) {
						$strMsg="Error selecting Database type $oqc_db_type<br>";
						$step=2;
					} else {
						$strMsg="Selected Database type $oqc_db_type<br>";
						$step=3;
					}
					if (file_exists($db_select_lock)) {
						unlink($db_select_lock);
					}
					redirect($strMsg);
					break;
				}
			}
			break;


		case 'initialize':
			$db_config_lock = "/tmp/openqrm-configure-db-config.lock";
			if (file_exists($db_config_lock)) {
				unlink($db_config_lock);
			}
			$cmd_token = md5(uniqid(rand(), true));
			if (!strcmp($OPENQRM_DATABASE_TYPE, "oracle")) {
				// enable the 3 extra fields
				$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_SERVER=.*/OPENQRM_DATABASE_SERVER=$oqc_db_server/g' -e 's/^OPENQRM_DATABASE_NAME=.*/OPENQRM_DATABASE_NAME=$oqc_db_name/g' -e 's/^OPENQRM_DATABASE_USER=.*/OPENQRM_DATABASE_USER=$oqc_db_user/g' -e 's/^OPENQRM_DATABASE_PASSWORD=.*/OPENQRM_DATABASE_PASSWORD=$oqc_db_password/g' -e 's/#OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH=$oqc_db_ld_path/g' -e 's/#OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME=$oqc_db_home/g' -e 's/#OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN=$oqc_db_tns/g' -e 's/OPENQRM_LD_LIBRARY_PATH=.*/OPENQRM_LD_LIBRARY_PATH=$oqc_db_ld_path/g' -e 's/OPENQRM_ORACLE_HOME=.*/OPENQRM_ORACLE_HOME=$oqc_db_home/g' -e 's/OPENQRM_TNS_ADMIN=.*/OPENQRM_TNS_ADMIN=$oqc_db_tns/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
			} else {
				$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"sed -i -e 's/^OPENQRM_DATABASE_SERVER=.*/OPENQRM_DATABASE_SERVER=$oqc_db_server/g' -e 's/^OPENQRM_DATABASE_NAME=.*/OPENQRM_DATABASE_NAME=$oqc_db_name/g' -e 's/^OPENQRM_DATABASE_USER=.*/OPENQRM_DATABASE_USER=$oqc_db_user/g' -e 's/^OPENQRM_DATABASE_PASSWORD=.*/OPENQRM_DATABASE_PASSWORD=$oqc_db_password/g' $OPENQRM_SERVER_BASE_DIR/openqrm/etc/openqrm-server.conf\"";
			}
			shell_exec($config_command);
			sleep(1);
			$lock_command = "touch $db_config_lock && chmod 777 $db_config_lock";
			shell_exec($lock_command);
			if (!wait_for_statfile($db_config_lock)) {
				$strMsg="Error saving Database configuration <br>";
				$step=3;
			}
			if (file_exists($db_config_lock)) {
				unlink($db_config_lock);
			}
			// init token plus timeout
			$cmd_token = md5(uniqid(rand(), true));
			$cmd_token .= ".".$refresh_loop_max;
			// restore last backup ?
			if ($oqc_db_restore == 1) {
				$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm init_config restore\"";
			} else {
				$config_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/sbin/openqrm-exec -i 127.0.0.1 -t $cmd_token -c \"$OPENQRM_SERVER_BASE_DIR/openqrm/bin/openqrm init_config\"";
			}
			shell_exec($config_command);
			if (!wait_for_fileremoval("./unconfigured")) {
				$strMsg="Error initialized the openQRM Server !<br>Please check /var/log/messages for more info.";
				$step=3;
			} else {
				// delay a bit for openQRM startup
				sleep(4);
				$strMsg = "";
				$step=4;
			}
			redirect($strMsg);
			break;
	}
}

function openqrm_server_config_select_nic() {
	global $thisfile;
	global $html;
	global $oqc_available_nics;

	$table = $html->tablebuilder("oqc_nic");
	$arHead = array();
	$arHead['oqc_nic'] = array();
	$arHead['oqc_nic']['title'] ='Available Networkcards';
	$arBody = array();
	$nic_count=0;
	foreach ($oqc_available_nics as $nic) {
		$arBody[] = array(
			'oqc_nic' => $nic,
		);
		if ($nic_count == 0) {
			$first_nic = $nic;
		}
		$nic_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->identifier = 'oqc_nic';
	$table->identifier_name = 'identifier';
	$table->identifier_checked = array($first_nic);
	$table->sort = 'oqc_nic';
	$table->sort_link = false;
	$table->sort_form = false;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->actions_name = 'action';
	$table->actions = array('next');
	$table->max = $nic_count;

	// set template
	$t = $html->template('tpl/configure1.tpl.php');
	$t->add($table, 'nic_table');
	return $t->get_string();
}

function openqrm_server_config_select_db() {
	global $thisfile;
	global $html;

	$table = $html->tablebuilder("oqc_db_type");
	$arHead = array();
	$arHead['oqc_db_type'] = array();
	$arHead['oqc_db_type']['title'] ='Database Type';
	$arBody = array();
	$arBody[] = array(
		'oqc_db_type' => "mysql",
	);
	$arBody[] = array(
		'oqc_db_type' => "postgres",
	);
	/*
	$arBody[] = array(
		'oqc_db_type' => "oracle",
	);
	$arBody[] = array(
		'oqc_db_type' => "db2",
	);
	 */
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "radio";
	$table->identifier = 'oqc_db_type';
	$table->identifier_name = 'identifier';
	$table->identifier_checked = array('mysql');
	$table->sort_link = false;
	$table->sort_form = false;
	$table->sort = 'oqc_db_type';
	$table->actions_name = 'action';
	$table->head = $arHead;
	$table->body = $arBody;
	$table->actions = array('select');
	$table->max = count($arBody);

	// set template
	$t = $html->template('tpl/configure2.tpl.php');
	$t->add($table, 'db_table');
	return $t->get_string();
}

function openqrm_server_config_db_setup() {
	global $thisfile;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_DATABASE_TYPE;
	global $OPENQRM_DATABASE_SERVER;
	global $OPENQRM_DATABASE_NAME;
	global $OPENQRM_DATABASE_USER;
	global $OPENQRM_DATABASE_PASSWORD;
	global $html;

	$table = $html->tablebuilder("oqc_db_setup");
	$arHead = array();

	$arHead['oqc_db_key'] = array();
	$arHead['oqc_db_key']['title'] ="Type";

	$arHead['oqc_db_value'] = array();
	$arHead['oqc_db_value']['title'] =$OPENQRM_DATABASE_TYPE;

	$arBody = array();
	$arBody[] = array(
		'oqc_db_key' => "Database Server",
		'oqc_db_value' => "<input type='text' name='oqc_db_server' value=\"$OPENQRM_DATABASE_SERVER\">",
	);
	$arBody[] = array(
		'oqc_db_key' => "Database Name",
		'oqc_db_value' => "<input type='text' name='oqc_db_name' value=\"$OPENQRM_DATABASE_NAME\">",
	);
	$arBody[] = array(
		'oqc_db_key' => "Database User",
		'oqc_db_value' => "<input type='text' name='oqc_db_user' value=\"$OPENQRM_DATABASE_USER\">",
	);
	$arBody[] = array(
		'oqc_db_key' => "Database Password",
		'oqc_db_value' => "<input type='password' name='oqc_db_password' value=\"$OPENQRM_DATABASE_PASSWORD\">",
	);
	// for oracle we need 3 extra fields
	if (!strcmp($OPENQRM_DATABASE_TYPE, "oracle")) {
		$arBody[] = array(
			'oqc_db_key' => "Oracle library path",
			'oqc_db_value' => "<input type='text' name='oqc_db_ld_path' value=\"$OPENQRM_LD_LIBRARY_PATH\">",
		);
		$arBody[] = array(
			'oqc_db_key' => "Oracle home direcctory",
			'oqc_db_value' => "<input type='text' name='oqc_db_home' value=\"$OPENQRM_ORACLE_HOME\">",
		);
		$arBody[] = array(
			'oqc_db_key' => "TNS-Admin path",
			'oqc_db_value' => "<input type='text' name='oqc_db_tns' value=\"$OPENQRM_TNS_ADMIN\">",
		);
	}

	$arBody[] = array(
		'oqc_db_key' => "Restore last backup",
		'oqc_db_value' => "<input type='checkbox' name='oqc_db_restore' value='1' />",
	);

	$table = $html->table();
	$table->css = 'htmlobject_table';
	foreach($arBody as $v) {
		$tr = $html->tr();
		$td = $html->td();
		$td->add($v['oqc_db_key']);
		$tr->add($td);
		$td = $html->td();
		$td->add($v['oqc_db_value']);
		$tr->add($td);
		$table->add($tr);
	}

	// set template
	$t = $html->template('tpl/configure3.tpl.php');
	$t->add($table, 'db_config_table');
	$t->add($thisfile, 'thisfile');
	return $t->get_string();
}

function openqrm_server_config_db_final() {
	global $thisfile;
	global $html;
	echo "<meta http-equiv=\"refresh\" content=\"10; URL=index.php\">";
	$t = $html->template('tpl/configure4.tpl.php');
	return $t->get_string();
}

$output = array();
switch ($step) {
	case "1":
		$output[0]['value'] = openqrm_server_config_select_nic();
		break;
	case "2":
		$output[0]['value'] = openqrm_server_config_select_db();
		break;
	case "3":
		$output[0]['value'] = openqrm_server_config_db_setup();
		break;
	case "4":
		$output[0]['value'] = openqrm_server_config_db_final();
		break;
	default:
		$output[0]['value'] = openqrm_server_config_select_nic();
		break;
}

$output[0]['label']   = 'openQRM Server Confguration';
$output[0]['onclick'] = false;
$output[0]['active']  = true;
$output[0]['request']  = '&step='.$step;
$output[0]['target']  = $html->thisfile;

$tab = $html->tabmenu('tab');
$tab->message_param = 'msg';
$tab->css = 'htmlobject_tabs';
$tab->add($output);

echo $tab->get_string();
?>
</body>
</html>
