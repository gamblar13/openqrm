<SCRIPT LANGUAGE="JavaScript">
<!-- Original:  ataxx@visto.com -->

function getRandomNum(lbound, ubound) {
	return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
}

function getRandomChar(number, lower, upper, other, extra) {
	var numberChars = "0123456789";
	var lowerChars = "abcdefghijklmnopqrstuvwxyz";
	var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var otherChars = "`~!@#$%^&*()-_=+[{]}\\|;:'\",<.>/? ";
	var charSet = extra;
	if (number == true)
		charSet += numberChars;
	if (lower == true)
		charSet += lowerChars;
	if (upper == true)
		charSet += upperChars;
	if (other == true)
		charSet += otherChars;
	return charSet.charAt(getRandomNum(0, charSet.length));
}
function getPassword(length, extraChars, firstNumber, firstLower, firstUpper, firstOther, latterNumber, latterLower, latterUpper, latterOther) {
	var rc = "";
	if (length > 0)
		rc = rc + getRandomChar(firstNumber, firstLower, firstUpper, firstOther, extraChars);
	for (var idx = 1; idx < length; ++idx) {
		rc = rc + getRandomChar(latterNumber, latterLower, latterUpper, latterOther, extraChars);
	}
	return rc;
}

function statusMsg(msg) {
	window.status=msg;
	return true;
}


</script>

<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />

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
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudusergroup.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;

// check if we got some actions to do
if(htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'delete':
			foreach($_REQUEST['identifier'] as $id) {
				$cl_user_group = new cloudusergroup();
				$cl_user_group->get_instance_by_id($id);
				// remove from db
				$cl_user_group->remove($id);
			}
			break;

	}
}




function cloud_user_group_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
	$table = new htmlobject_db_table('cg_id', 'ASC');
	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}
	$arHead = array();

	$arHead['cg_id'] = array();
	$arHead['cg_id']['title'] ='ID';

	$arHead['cg_name'] = array();
	$arHead['cg_name']['title'] ='Name';

	$arHead['cg_description'] = array();
	$arHead['cg_description']['title'] ='Description';

	$arBody = array();

	// db select
	$cl_user_group_count = 0;
	$cl_user_group = new cloudusergroup();
	$user_group_array = $cl_user_group->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($user_group_array as $index => $cg) {
		$arBody[] = array(
			'cg_id' => $cg["cg_id"],
			'cg_name' => $cg["cg_name"],
			'cg_descrption' => $cg["cg_description"],
		);
		$cl_user_group_count++;
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
		$table->bottom = array('delete');
		$table->identifier = 'cg_id';
	}
	$table->max = $cl_user_group->get_count();
	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-user-group-manager-tpl.php');
	$t->setVar(array(
		'thisfile' => $thisfile,
		'external_portal_name' => $external_portal_name,
		'cloud_user_group_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



function cloud_create_user_group() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
	$cc_conf = new cloudconfig();
	// get external name
	$external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}
	$cg_name = htmlobject_input('cg_name', array("value" => '', "label" => 'Group name'), 'text', 20);
	$cg_description = htmlobject_input('cg_description', array("value" => '', "label" => 'Description'), 'text', 50);
	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-user-group-create-tpl.php');
	$t->setVar(array(
		'cg_name' => $cg_name,
		'cg_description' => $cg_description,
		'thisfile' => 'cloud-action.php',
		'external_portal_name' => $external_portal_name,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





$output = array();

// if ldap is enabled do not allow access the the openQRM cloud user administration
if (file_exists("$RootDir/plugins/ldap/.running")) {
	unset($output);
	$output[] = array('label' => 'Disabled', 'value' => "The openQRM Cloud User-Management is disabled by the LDAP-Plugin!");
} else {
	if(htmlobject_request('action') != '') {
		switch (htmlobject_request('action')) {
			case 'create':
				$output[] = array('label' => 'Create Cloud User', 'value' => cloud_create_user_group());
				break;
			default:
				$output[] = array('label' => 'Cloud User Manager', 'value' => cloud_user_group_manager());
				break;
		}
	} else {
		$output[] = array('label' => 'Cloud User Manager', 'value' => cloud_user_group_manager());
	}
}
echo htmlobject_tabmenu($output);
?>
