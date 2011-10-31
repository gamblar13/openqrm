
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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
require_once "$RootDir/class/resource.class.php";
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
require_once "$RootDir/plugins/cloud/class/cloudprivateimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrespool.class.php";
// ip mgmt class
if (file_exists("$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php")) {
    require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";
}
global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;
// get the cu_id array
$private_id_arr = htmlobject_request('cg_id');


function redirect_private($strMsg, $currenttab = 'tab0') {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab."&redirect=yes";
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
$strMsg = '';
if (htmlobject_request('redirect') != 'yes') {
	if(htmlobject_request('action') != '') {
		switch (htmlobject_request('action')) {
			case 'set':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $ip_mgmt_name) {
						$private_cg_id = $private_id_arr[$ip_mgmt_name];
						if ($private_cg_id == -1) {
							$private_name = "Hide";
						} else if ($private_cg_id == 0) {
							$private_name = "Default Cloud User Group";
						} else {
							$pcloudusergroup = new cloudusergroup();
							$pcloudusergroup->get_instance_by_id($private_cg_id);
							$private_name = $pcloudusergroup->name;
						}
						$strMsg .= "Setting IP Network $ip_mgmt_name to $private_name ( $private_cg_id )....<br>";

						$private_cloud_ip_mgmt_fields["ip_mgmt_user_id"] = $private_cg_id;
						$ip_mgmt = new ip_mgmt();
						$ip_mgmt->update($ip_mgmt_name, $private_cloud_ip_mgmt_fields);
					}
					redirect_private($strMsg, 'tab0');
				}
				break;



		}
	}
}


function cloud_ip_mgmt_pool_selector() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;
	global $RootDir;

	// private-resource enabled ?
	$cp_conf = new cloudconfig();
	$show_ip_mgmt = $cp_conf->get_value(26);	// ip-mgmt enabled ?
	if (strcmp($show_ip_mgmt, "true")) {
		$strMsg = "<strong>IP-Management is not enabled in this Cloud !</strong>";
		return $strMsg;
		exit(0);
	} else {
		// is the ip-mgmt plugin enabled ?
		if (!file_exists("$RootDir/plugins/ip-mgmt/.running")) {
			$strMsg = "<strong>The IP-Management plug-in is not enabled in this openQRM Server !</strong>";
			return $strMsg;
			exit(0);
		}
	}
	// get external name
	$external_portal_name = $cp_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$table = new htmlobject_table_identifiers_checked('ip_mgmt_name');
	$arHead = array();

	$arHead['ip_mgmt_icon'] = array();
	$arHead['ip_mgmt_icon']['title'] ='';

	$arHead['ip_mgmt_name'] = array();
	$arHead['ip_mgmt_name']['title'] ='Name';

	$arHead['ip_mgmt_selector'] = array();
	$arHead['ip_mgmt_selector']['title'] ='Assign to';
	$arHead['ip_mgmt_selector']['sortable'] = false;

	$arBody = array();

	// prepare selector array
	$cloud_user_group_sel = new cloudusergroup();
	$cloud_user_group_arr = $cloud_user_group_sel->get_list();
	$cloud_user_group_arr = array_reverse($cloud_user_group_arr);
	$cloud_user_group_arr[] = array('value'=> '-1', 'label'=> 'Hide');
	$cloud_user_group_arr = array_reverse($cloud_user_group_arr);

	// db select
	$ip_mgmt_count = 0;
	$ip_mgmt_list = new ip_mgmt();
	$ip_mgmt_array = $ip_mgmt_list->get_names();

	foreach ($ip_mgmt_array as $ip_mgmt_name) {
		// find out which is selected
		$ip_mgmt_sel = new ip_mgmt();
		$ip_mgmt_lib_by_name = $ip_mgmt_sel->get_list($ip_mgmt_name);
		$pi_selected = $ip_mgmt_lib_by_name[$ip_mgmt_name]['first']['ip_mgmt_user_id'];
		if (!strlen($pi_selected)) {
			$pi_selected=-1;
		}
		$ip_mgmt_icon_default="/openqrm/base/plugins/cloud/img/cloudipgroups.png";
		$ip_mgmt_pool_select = htmlobject_select("cg_id[$ip_mgmt_name]", $cloud_user_group_arr, '', array($pi_selected));
		$arBody[] = array(
			'ip_mgmt_icon' => "<img width=24 height=24 src=$ip_mgmt_icon_default>",
			'ip_mgmt_name' => $ip_mgmt_name,
			'ip_mgmt_selector' => $ip_mgmt_pool_select,
		);
		$ip_mgmt_count++;
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
		$table->bottom = array('set');
		$table->identifier = 'ip_mgmt_name';
	}
	$table->max = $ip_mgmt_list->get_count();

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-ip-manager-tpl.php');
	$t->setVar(array(
		'external_portal_name' => $external_portal_name,
		'cloud_private_ip_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Ip-Addresses', 'value' => cloud_ip_mgmt_pool_selector());
echo htmlobject_tabmenu($output);

?>