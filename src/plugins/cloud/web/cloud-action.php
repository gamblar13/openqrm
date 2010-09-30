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

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/



$cloud_command = $_REQUEST["cloud_command"];

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
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudusergroup.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
global $CLOUD_USER_TABLE;
global $CLOUD_REQUEST_TABLE;
global $CLOUD_USER_GROUPS_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	$event->log("authorization", $_SERVER['REQUEST_TIME'], 1, "cloud-action", "Un-Authorized access to cloud-actions from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}

// gather user parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cu_", 3) == 0) {
		$user_fields[$key] = $value;
	}
}
// gather user group parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cg_", 3) == 0) {
		$user_group_fields[$key] = $value;
	}
}

// gather request parameter in array
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "cr_", 3) == 0) {
		$request_fields[$key] = $value;
	}
}
// set ha clone-on deploy
if (!strcmp($request_fields['cr_ha_req'], "on")) {
	$request_fields['cr_ha_req']=1;
} else {
	$request_fields['cr_ha_req']=0;
}
if (!strcmp($request_fields['cr_shared_req'], "on")) {
	$request_fields['cr_shared_req']=1;
} else {
	$request_fields['cr_shared_req']=0;
}


function date_to_timestamp($date) {
	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$hour = substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$sec = 0;
	$timestamp = mktime($hour, $minute, $sec, $month, $day, $year);
	return $timestamp;
}



function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	} else {
        $url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
    }
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// functions to check user input
function is_allowed($text) {
	for ($i = 0; $i<strlen($text); $i++) {
		if (!ctype_alpha($text[$i])) {
			if (!ctype_digit($text[$i])) {
				if (!ctype_space($text[$i])) {
					return false;
				}
			}
		}
	}
	return true;
}



function check_param($param, $value) {
	global $c_error;
	if (!strlen($value)) {
		$strMsg = "$param is empty <br>";
		$c_error = 1;
		redirect($strMsg, 'tab0', "cloud-user.php");
		exit(0);
	}
	// remove whitespaces
	$value = trim($value);
	// remove any non-violent characters
	$value = str_replace(".", "", $value);
	$value = str_replace(",", "", $value);
	$value = str_replace("-", "", $value);
	$value = str_replace("_", "", $value);
	$value = str_replace("(", "", $value);
	$value = str_replace(")", "", $value);
	$value = str_replace("/", "", $value);
	if(!is_allowed($value)){
		$strMsg = "$param contains special characters <br>";
		$c_error = 1;
		redirect($strMsg, 'tab0', "cloud-user.php");
		exit(0);
	}
}


// main
$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 5, "cloud-action", "Processing cloud command $cloud_command", "", "", 0, 0, 0);

	switch ($cloud_command) {

		case 'init':
			// this command creates the following tables
			// -> cloudrequests
			// cr_id INT(5)
			// cr_cu_id INT(5)
			// cr_status INT(5)
			// cr_request_time VARCHAR(20)
			// cr_start VARCHAR(20)
			// cr_stop VARCHAR(20)
			// cr_kernel_id INT(5)
			// cr_image_id INT(5)
			// cr_ram_req VARCHAR(20)
			// cr_cpu_req VARCHAR(20)
			// cr_disk_req VARCHAR(20)
			// cr_network_req VARCHAR(255)
			// cr_resource_quantity INT(5)
			// cr_resource_type_req VARCHAR(20)
			// cr_deployment_type_req VARCHAR(50)
			// cr_ha_req VARCHAR(5)
			// cr_shared_req VARCHAR(5)
			// cr_puppet_groups VARCHAR(255)
			// cr_ip_mgmt VARCHAR(255)
			// cr_appliance_id VARCHAR(255)
			// cr_lastbill VARCHAR(20)
			// 
			// -> cloudusers
			// cu_id INT(5)
			// cu_cg_id INT(5)
			// cu_name VARCHAR(20)
			// cu_password VARCHAR(50)
			// cu_forename VARCHAR(50)
			// cu_lastname VARCHAR(50)
			// cu_email VARCHAR(50)
			// cu_street VARCHAR(100)
			// cu_city VARCHAR(100)
			// cu_country VARCHAR(100)
			// cu_phone VARCHAR(100)
			// cu_status INT(5)
			// cu_token VARCHAR(100)
			// cu_ccunits BIGINT(10)
			// 
			// -> cloudusergroup
            // cg_id INT(5)
			// cg_name VARCHAR(20)
			// cg_role_id  INT(5)
			// cg_description VARCHAR(255)
            //
			// -> clouduserslimits
			// cl_id INT(5)
			// cl_cu_id INT(5)
			// cl_resource_limit INT(5)
			// cl_memory_limit BIGINT(5)
			// cl_disk_limit BIGINT(5)
			// cl_cpu_limit INT(5)
			// cl_network_limit INT(5)
			//
			// -> cloudconfig
			// cc_id INT(5)
			// cc_key VARCHAR(50)
			// cc_value VARCHAR(50)

            // -> cloudimage
            // ci_id INT(5)
            // ci_cr_id INT(5)
            // ci_image_id INT(5)
            // ci_appliance_id INT(5)
            // ci_resource_id INT(5)
            // ci_disk_size VARCHAR(20)
            // ci_disk_rsize VARCHAR(20)
            // ci_clone_name VARCHAR(50)
            // ci_state INT(5)
			
            // -> cloudappliance
            // ca_id INT(5)
            // ca_appliance_id INT(5)
            // ca_cr_id INT(5)
            // ca_cmd INT(5)
            // ca_state INT(5)

            // -> cloudnat
            // cn_id INT(5)
            // cn_internal_net VARCHAR(50)
            // cn_external_net VARCHAR(50)

            // -> cloudtransaction
            // ct_id INT(5)
            // ct_time VARCHAR(50),
            // ct_cr_id INT(5)
            // ct_cu_id INT(5)
            // ct_ccu_charge INT(5)
            // ct_ccu_balance INT(5)
            // ct_reason VARCHAR(20)
            // ct_comment VARCHAR(255)

            // -> cloudirlc
            // cd_id INT(5)
            // cd_appliance_id INT(5)
            // cd_state INT(5)

            // -> cloudiplc
            // cp_id INT(5)
            // cp_appliance_id INT(5)
            // cp_cu_id INT(5)
            // cp_state INT(5)
            // cp_start_private VARCHAR(20)

            // -> cloudprivateimage
            // co_id INT(5)
            // co_image_id INT(5)
            // co_cu_id INT(5)
            // co_state INT(5)
            // co_comment VARCHAR(255)

            // -> cloudselector
            // id INT(5)
            // type VARCHAR(255)
            // sort_id INT(5)
            // quantity VARCHAR(255)
            // price INT(5)
            // name VARCHAR(20)
            // description VARCHAR(255)
            // state INT(5)

            // -> cloudrespool
            // rp_id INT(5)
            // rp_resource_id INT(5)
            // rp_cg_id INT(5)

            // -> cloudhostlimit
            // hl_id INT(5)
            // hl_resource_id INT(5)
            // hl_current_vms INT(5)
            // hl_max_vms INT(5)

            // -> cloudpowersaver
            // ps_id INT(5)
            // ps_frequence VARCHAR(50)
            // ps_last_check VARCHAR(50)

			// -> cloud_profiles
			// pr_id INT(5)
			// pr_name VARCHAR(20)
			// pr_cu_id INT(5)
			// pr_status INT(5)
			// pr_request_time VARCHAR(20)
			// pr_start VARCHAR(20)
			// pr_stop VARCHAR(20)
			// pr_kernel_id INT(5)
			// pr_image_id INT(5)
			// pr_ram_req VARCHAR(20)
			// pr_cpu_req VARCHAR(20)
			// pr_disk_req VARCHAR(20)
			// pr_network_req VARCHAR(255)
			// pr_resource_quantity INT(5)
			// pr_resource_type_req VARCHAR(20)
			// pr_deployment_type_req VARCHAR(50)
			// pr_ha_req VARCHAR(5)
			// pr_shared_req VARCHAR(5)
			// pr_puppet_groups VARCHAR(255)
			// pr_ip_mgmt VARCHAR(255)
			// pr_appliance_id VARCHAR(255)
			// pr_lastbill VARCHAR(20)
			// pr_description VARCHAR(255)

			// -> cloud_icons
			// ic_id INT(5)
			// ic_cu_id INT(5)
			// ic_type INT(5)
			// ic_object_id INT(5)
			// ic_filename VARCHAR(255)

			// -> cloud_matrix
			// cm_id INT(5)
			// cm_cu_id INT(5)
			// cm_description VARCHAR(255)
			// cm_row01 VARCHAR(255)
			// cm_row02 VARCHAR(255)
			// cm_row03 VARCHAR(255)
			// cm_row04 VARCHAR(255)
			// cm_row05 VARCHAR(255)
			// cm_row06 VARCHAR(255)
			// cm_row07 VARCHAR(255)
			// cm_row08 VARCHAR(255)
			// cm_row09 VARCHAR(255)
			// cm_row10 VARCHAR(255)
			// cm_row11 VARCHAR(255)
			// cm_row12 VARCHAR(255)

			// -> cloud_matrix_object
			// mo_id INT(5)
			// mo_pr_id INT(5)
			// mo_cr_id INT(5)
			// mo_ca_id INT(5)
			// mo_ne_id INT(5)
			// mo_table INT(5)
			// mo_x INT(5)
			// mo_y INT(5)
			// mo_state INT(5)

			// -> cloud_create_vm_lc
			// vc_id INT(5)
			// vc_resource_id INT(5)
			// vc_cr_id INT(5)
            // vc_cr_resource_number INT(5)
			// vc_request_time VARCHAR(20)
			// vc_vm_create_timeout INT(5)
			// vc_state INT(5)

            $create_cloud_requests = "create table cloud_requests(cr_id INT(5), cr_cu_id INT(5), cr_status INT(5), cr_request_time VARCHAR(20), cr_start VARCHAR(20), cr_stop VARCHAR(20), cr_kernel_id INT(5), cr_image_id INT(5), cr_ram_req VARCHAR(20), cr_cpu_req VARCHAR(20), cr_disk_req VARCHAR(20), cr_network_req VARCHAR(255), cr_resource_quantity INT(5), cr_resource_type_req VARCHAR(20), cr_deployment_type_req VARCHAR(50), cr_ha_req VARCHAR(5), cr_shared_req VARCHAR(5), cr_appliance_id VARCHAR(255), cr_puppet_groups VARCHAR(255), cr_ip_mgmt VARCHAR(255), cr_lastbill VARCHAR(20))";
			$create_cloud_users = "create table cloud_users(cu_id INT(5), cu_cg_id INT(5), cu_name VARCHAR(50), cu_password VARCHAR(50), cu_forename VARCHAR(50), cu_lastname VARCHAR(50), cu_email VARCHAR(50), cu_street VARCHAR(100), cu_city VARCHAR(100), cu_country VARCHAR(100), cu_phone VARCHAR(100), cu_status INT(5), cu_token VARCHAR(100), cu_ccunits BIGINT(10))";
			$create_cloud_usergroups = "create table cloud_usergroups(cg_id INT(5), cg_name VARCHAR(50), cg_role_id INT(5), cg_description VARCHAR(255))";
			$create_cloud_users_limit = "create table cloud_users_limits(cl_id INT(5), cl_cu_id INT(5), cl_resource_limit INT(5), cl_memory_limit BIGINT(10), cl_disk_limit BIGINT(10), cl_cpu_limit INT(5), cl_network_limit INT(5))";
			$create_cloud_config = "create table cloud_config(cc_id INT(5), cc_key VARCHAR(50), cc_value VARCHAR(50))";
			$create_cloud_image = "create table cloud_image(ci_id INT(5), ci_cr_id INT(5), ci_image_id INT(5), ci_appliance_id INT(5), ci_resource_id INT(5), ci_disk_size VARCHAR(20), ci_disk_rsize VARCHAR(20), ci_clone_name VARCHAR(50), ci_state INT(5))";
			$create_cloud_appliance = "create table cloud_appliance(ca_id INT(5), ca_appliance_id INT(5), ca_cr_id INT(5), ca_cmd INT(5), ca_state INT(5))";
			$create_cloud_nat = "create table cloud_nat(cn_id INT(5), cn_internal_net VARCHAR(50), cn_external_net VARCHAR(50))";
			$create_cloud_transaction = "create table cloud_transaction(ct_id INT(5), ct_time VARCHAR(50), ct_cr_id INT(5), ct_cu_id INT(5), ct_ccu_charge INT(5), ct_ccu_balance INT(5), ct_reason VARCHAR(20), ct_comment VARCHAR(255))";
			$create_cloud_image_resize_life_cycle = "create table cloud_irlc(cd_id INT(5), cd_appliance_id INT(5), cd_state INT(5))";
			$create_cloud_image_private_life_cycle = "create table cloud_iplc(cp_id INT(5), cp_appliance_id INT(5), cp_cu_id INT(5), cp_state INT(5), cp_start_private VARCHAR(20))";
			$create_cloud_image_private = "create table cloud_private_image(co_id INT(5), co_image_id INT(5), co_cu_id INT(5), co_comment VARCHAR(255), co_state INT(5))";
			$create_cloud_selector = "create table cloud_selector(id INT(5), type VARCHAR(255), sort_id INT(5), quantity VARCHAR(255), price INT(5), name VARCHAR(20), description VARCHAR(255), state INT(5))";
			$create_cloud_resource_pool = "create table cloud_respool(rp_id INT(5), rp_resource_id INT(5), rp_cg_id INT(5))";
			$create_cloud_host_limit = "create table cloud_hostlimit(hl_id INT(5), hl_resource_id INT(5), hl_current_vms INT(5), hl_max_vms INT(5))";
			$create_cloud_power_saver = "create table cloud_power_saver(ps_id INT(5), ps_frequence VARCHAR(50), ps_last_check VARCHAR(50))";
            $create_cloud_profiles = "create table cloud_profiles(pr_id INT(5), pr_name VARCHAR(20), pr_cu_id INT(5), pr_status INT(5), pr_request_time VARCHAR(20), pr_start VARCHAR(20), pr_stop VARCHAR(20), pr_kernel_id INT(5), pr_image_id INT(5), pr_ram_req VARCHAR(20), pr_cpu_req VARCHAR(20), pr_disk_req VARCHAR(20), pr_network_req VARCHAR(255), pr_resource_quantity INT(5), pr_resource_type_req VARCHAR(20), pr_deployment_type_req VARCHAR(50), pr_ha_req VARCHAR(5), pr_shared_req VARCHAR(5), pr_appliance_id VARCHAR(255), pr_puppet_groups VARCHAR(255), pr_ip_mgmt VARCHAR(255), pr_lastbill VARCHAR(20), pr_description VARCHAR(255))";
            $create_cloud_icons = "create table cloud_icons(ic_id INT(5), ic_cu_id INT(5), ic_type INT(5), ic_object_id INT(5), ic_filename VARCHAR(255))";
            $create_cloud_matrix = "create table cloud_matrix(cm_id INT(5), cm_cu_id INT(5), cm_description VARCHAR(255), cm_row01 VARCHAR(255), cm_row02 VARCHAR(255), cm_row03 VARCHAR(255), cm_row04 VARCHAR(255), cm_row05 VARCHAR(255), cm_row06 VARCHAR(255), cm_row07 VARCHAR(255), cm_row08 VARCHAR(255), cm_row09 VARCHAR(255), cm_row10 VARCHAR(255), cm_row11 VARCHAR(255), cm_row12 VARCHAR(255))";
            $create_cloud_matrix_object = "create table cloud_matrix_object(mo_id INT(5), mo_pr_id INT(5), mo_cr_id INT(5), mo_ca_id INT(5), mo_ne_id INT(5), mo_table INT(5), mo_x INT(5), mo_y INT(5), mo_state INT(5))";
            $create_cloud_create_vm_lc = "create table cloud_create_vm_lc(vc_id INT(5), vc_resource_id INT(5), vc_cr_id INT(5), vc_cr_resource_number INT(5), vc_request_time VARCHAR(20), vc_vm_create_timeout INT(5), vc_state INT(5))";
            $db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($create_cloud_requests);
			$recordSet = &$db->Execute($create_cloud_users);
			$recordSet = &$db->Execute($create_cloud_usergroups);
			$recordSet = &$db->Execute($create_cloud_users_limit);
			$recordSet = &$db->Execute($create_cloud_config);
			$recordSet = &$db->Execute($create_cloud_image);
			$recordSet = &$db->Execute($create_cloud_appliance);
			$recordSet = &$db->Execute($create_cloud_nat);
			$recordSet = &$db->Execute($create_cloud_transaction);
			$recordSet = &$db->Execute($create_cloud_image_resize_life_cycle);
			$recordSet = &$db->Execute($create_cloud_image_private_life_cycle);
			$recordSet = &$db->Execute($create_cloud_image_private);
			$recordSet = &$db->Execute($create_cloud_selector);
			$recordSet = &$db->Execute($create_cloud_resource_pool);
			$recordSet = &$db->Execute($create_cloud_host_limit);
			$recordSet = &$db->Execute($create_cloud_power_saver);
			$recordSet = &$db->Execute($create_cloud_profiles);
			$recordSet = &$db->Execute($create_cloud_icons);
			$recordSet = &$db->Execute($create_cloud_matrix);
			$recordSet = &$db->Execute($create_cloud_matrix_object);
			$recordSet = &$db->Execute($create_cloud_create_vm_lc);

			// create the default configuration
			$create_default_cloud_config1 = "insert into cloud_config(cc_id, cc_key, cc_value) values (1, 'cloud_admin_email', 'root@localhost')";
			$recordSet = &$db->Execute($create_default_cloud_config1);
			$create_default_cloud_config2 = "insert into cloud_config(cc_id, cc_key, cc_value) values (2, 'auto_provision', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config2);
			$create_default_cloud_config3 = "insert into cloud_config(cc_id, cc_key) values (3, 'external_portal_url')";
			$recordSet = &$db->Execute($create_default_cloud_config3);
			$create_default_cloud_config4 = "insert into cloud_config(cc_id, cc_key, cc_value) values (4, 'request_physical_systems', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config4);
			$create_default_cloud_config5 = "insert into cloud_config(cc_id, cc_key, cc_value) values (5, 'default_clone_on_deploy', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config5);
			$create_default_cloud_config6 = "insert into cloud_config(cc_id, cc_key, cc_value) values (6, 'max_resources_per_cr', '5')";
			$recordSet = &$db->Execute($create_default_cloud_config6);
			$create_default_cloud_config7 = "insert into cloud_config(cc_id, cc_key, cc_value) values (7, 'auto_create_vms', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config7);
			$create_default_cloud_config8 = "insert into cloud_config(cc_id, cc_key, cc_value) values (8, 'max_disk_size', '100000')";
			$recordSet = &$db->Execute($create_default_cloud_config8);
			$create_default_cloud_config9 = "insert into cloud_config(cc_id, cc_key, cc_value) values (9, 'max_network_interfaces', '4')";
			$recordSet = &$db->Execute($create_default_cloud_config9);
			$create_default_cloud_config10 = "insert into cloud_config(cc_id, cc_key, cc_value) values (10, 'show_ha_checkbox', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config10);
			$create_default_cloud_config11 = "insert into cloud_config(cc_id, cc_key, cc_value) values (11, 'show_puppet_groups', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config11);
			$create_default_cloud_config12 = "insert into cloud_config(cc_id, cc_key, cc_value) values (12, 'auto_give_ccus', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config12);
			$create_default_cloud_config13 = "insert into cloud_config(cc_id, cc_key, cc_value) values (13, 'max_apps_per_user', '10')";
			$recordSet = &$db->Execute($create_default_cloud_config13);
			$create_default_cloud_config14 = "insert into cloud_config(cc_id, cc_key, cc_value) values (14, 'public_register_enabled', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config14);
			$create_default_cloud_config15 = "insert into cloud_config(cc_id, cc_key, cc_value) values (15, 'cloud_enabled', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config15);
			$create_default_cloud_config16 = "insert into cloud_config(cc_id, cc_key, cc_value) values (16, 'cloud_billing_enabled', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config16);
			$create_default_cloud_config17 = "insert into cloud_config(cc_id, cc_key, cc_value) values (17, 'show_sshterm_login', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config17);
			$create_default_cloud_config18 = "insert into cloud_config(cc_id, cc_key, cc_value) values (18, 'cloud_nat', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config18);
			$create_default_cloud_config19 = "insert into cloud_config(cc_id, cc_key, cc_value) values (19, 'show_collectd_graphs', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config19);
			$create_default_cloud_config20 = "insert into cloud_config(cc_id, cc_key, cc_value) values (20, 'show_disk_resize', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config20);
			$create_default_cloud_config21 = "insert into cloud_config(cc_id, cc_key, cc_value) values (21, 'show_private_image', 'false')";
			$recordSet = &$db->Execute($create_default_cloud_config21);
			$create_default_cloud_config22 = "insert into cloud_config(cc_id, cc_key, cc_value) values (22, 'cloud_selector', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config22);
			$create_default_cloud_config23 = "insert into cloud_config(cc_id, cc_key, cc_value) values (23, 'cloud_currency', 'US')";
			$recordSet = &$db->Execute($create_default_cloud_config23);
			$create_default_cloud_config24 = "insert into cloud_config(cc_id, cc_key, cc_value) values (24, 'cloud_1000_ccus', '1')";
			$recordSet = &$db->Execute($create_default_cloud_config24);
			$create_default_cloud_config25 = "insert into cloud_config(cc_id, cc_key, cc_value) values (25, 'resource_pooling', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config25);
			$create_default_cloud_config26 = "insert into cloud_config(cc_id, cc_key, cc_value) values (26, 'ip-management', 'true')";
			$recordSet = &$db->Execute($create_default_cloud_config26);
			$create_default_cloud_config27 = "insert into cloud_config(cc_id, cc_key, cc_value) values (27, 'max-parallel-phase-one-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config27);
			$create_default_cloud_config28 = "insert into cloud_config(cc_id, cc_key, cc_value) values (28, 'max-parallel-phase-two-actions', '3')";
			$recordSet = &$db->Execute($create_default_cloud_config28);
			$create_default_cloud_config29 = "insert into cloud_config(cc_id, cc_key, cc_value) values (29, 'max-parallel-phase-three-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config29);
			$create_default_cloud_config30 = "insert into cloud_config(cc_id, cc_key, cc_value) values (30, 'max-parallel-phase-four-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config30);
			$create_default_cloud_config31 = "insert into cloud_config(cc_id, cc_key, cc_value) values (31, 'max-parallel-phase-five-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config31);
			$create_default_cloud_config32 = "insert into cloud_config(cc_id, cc_key, cc_value) values (32, 'max-parallel-phase-six-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config32);
			$create_default_cloud_config33 = "insert into cloud_config(cc_id, cc_key, cc_value) values (33, 'max-parallel-phase-seven-actions', '0')";
			$recordSet = &$db->Execute($create_default_cloud_config33);

            // fill in default cloud products for the cloudselector
            $create_default_cloudselector_config = "insert into `cloud_selector` VALUES (1,'cpu',0,'1',1,'1 CPU','1 CPU',1),(2,'cpu',1,'2',2,'2 CPUs','2 CPUs',1),(3,'cpu',2,'4',4,'4 CPUs','4 CPUs',1),(4,'disk',0,'2000',2,'2 GB','2 GB Disk Space',1),(5,'disk',1,'5000',5,'5 GB','5 GB Disk Space',1),(6,'disk',2,'10000',10,'10 GB','10 GB Disk Space',1),(7,'disk',3,'20000',20,'20 GB','20 GB Disk Space',1),(8,'disk',4,'50000',50,'50 GB','50 GB Disk Space',1),(9,'disk',5,'100000',100,'100 GB','100 GB Disk Space',1),(11,'memory',1,'512',2,'512 MB','512 MB Memory',1),(10,'memory',0,'256',1,'256 MB','256 MB Memory',1),(12,'memory',2,'1024',4,'1 GB','1 GB Memory',1),(13,'memory',3,'2048',8,'2 GB','2 GB Memory',1),(16,'network',2,'3',3,'3 NICs','3 Network Cards',1),(15,'network',1,'2',2,'2 NICs','2 Network Cards',1),(14,'network',0,'1',1,'1 NIC','1 Network Card',1),(17,'network',3,'4',4,'4 NICs','4 Network Cards',1),(18,'quantity',0,'1',1,'1','1 CloudAppliance',1),(19,'quantity',1,'2',2,'2','2 CloudAppliances',1),(20,'quantity',2,'3',3,'3','3 CloudAppliances',1),(21,'quantity',3,'4',4,'4','4 CloudAppliances',1),(22,'quantity',4,'5',5,'5','5 CloudAppliances',1);";
			$recordSet = &$db->Execute($create_default_cloudselector_config);
            // create default user group
            $create_default_usergroup = "insert into `cloud_usergroups` VALUES (0,'Default',0,'The default CloudUser Group');";
			$recordSet = &$db->Execute($create_default_usergroup);
            // default power-saver config
            $create_default_power_saver_config = "insert into `cloud_power_saver` VALUES (0,'1800','');";
			$recordSet = &$db->Execute($create_default_power_saver_config);


		    $db->Close();
			break;

		case 'uninstall':
			$drop_cloud_requests = "drop table cloud_requests";
			$drop_cloud_users = "drop table cloud_users";
			$drop_cloud_usergroups = "drop table cloud_usergroups";
			$drop_cloud_users_limit = "drop table cloud_users_limits";
			$drop_cloud_config = "drop table cloud_config";
			$drop_cloud_image = "drop table cloud_image";
			$drop_cloud_appliance = "drop table cloud_appliance";
			$drop_cloud_nat = "drop table cloud_nat";
			$drop_cloud_transaction = "drop table cloud_transaction";
			$drop_cloud_image_resize_life_cycle = "drop table cloud_irlc";
			$drop_cloud_image_private_life_cycle = "drop table cloud_iplc";
			$drop_cloud_image_private = "drop table cloud_private_image";
			$drop_cloud_selector = "drop table cloud_selector";
			$drop_cloud_resource_pool = "drop table cloud_respool";
			$drop_cloud_hostlimit = "drop table cloud_hostlimit";
			$drop_cloud_power_saver = "drop table cloud_power_saver";
			$drop_cloud_profiles = "drop table cloud_profiles";
			$drop_cloud_icons = "drop table cloud_icons";
			$drop_cloud_matrix = "drop table cloud_matrix";
			$drop_cloud_matrix_object = "drop table cloud_matrix_object";
			$drop_cloud_create_vm_lc = "drop table cloud_create_vm_lc";
			$db=openqrm_get_db_connection();
			$recordSet = &$db->Execute($drop_cloud_requests);
			$recordSet = &$db->Execute($drop_cloud_users);
			$recordSet = &$db->Execute($drop_cloud_usergroups);
			$recordSet = &$db->Execute($drop_cloud_users_limit);
			$recordSet = &$db->Execute($drop_cloud_config);
			$recordSet = &$db->Execute($drop_cloud_image);
			$recordSet = &$db->Execute($drop_cloud_appliance);
			$recordSet = &$db->Execute($drop_cloud_nat);
			$recordSet = &$db->Execute($drop_cloud_transaction);
			$recordSet = &$db->Execute($drop_cloud_image_resize_life_cycle);
			$recordSet = &$db->Execute($drop_cloud_image_private_life_cycle);
			$recordSet = &$db->Execute($drop_cloud_image_private);
			$recordSet = &$db->Execute($drop_cloud_selector);
			$recordSet = &$db->Execute($drop_cloud_resource_pool);
			$recordSet = &$db->Execute($drop_cloud_hostlimit);
			$recordSet = &$db->Execute($drop_cloud_power_saver);
			$recordSet = &$db->Execute($drop_cloud_profiles);
			$recordSet = &$db->Execute($drop_cloud_icons);
			$recordSet = &$db->Execute($drop_cloud_matrix);
			$recordSet = &$db->Execute($drop_cloud_matrix_object);
			$recordSet = &$db->Execute($drop_cloud_create_vm_lc);
		    $db->Close();
			break;

		case 'create_user':
			$user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $CLOUD_USER_TABLE);
			// enabled by default
			$user_fields['cu_status'] = 1;
			$username = $user_fields['cu_name'];
			$password = $user_fields['cu_password'];
			$c_error = 0;
			// checks
			check_param("Username", $user_fields['cu_name']);
			check_param("Password", $user_fields['cu_password']);
			check_param("Lastname", $user_fields['cu_lastname']);
			check_param("Forename", $user_fields['cu_forename']);
			check_param("Street", $user_fields['cu_street']);
			check_param("City", $user_fields['cu_city']);
			check_param("Country", $user_fields['cu_country']);
			check_param("Phone", $user_fields['cu_phone']);

			// email valid ?
			$cloud_email = new clouduser();
            if (strcmp($user_fields['cu_email'], "@localhost")) {
                if (!$cloud_email->checkEmail($user_fields['cu_email'])) {
                    $strMsg = "Email address is invalid. <br>";
                    $c_error = 1;
                    redirect($strMsg, 'tab0', "cloud-user.php");
                    exit(0);
                }
            }

			// password min 6 characters
			if (strlen($user_fields['cu_password'])<6) {
				$strMsg .= "Password must be at least 6 characters long <br>";
				$c_error = 1;
				redirect($strMsg, 'tab0', "cloud-user.php");
				exit(0);
			}
			// username min 4 characters
			if (strlen($user_fields['cu_name'])<4) {
				$strMsg .= "Username must be at least 4 characters long <br>";
				$c_error = 1;
				redirect($strMsg, 'tab0', "cloud-user.php");
				exit(0);
			}
			// does username already exists ?
			$c_user = new clouduser();
			if (!$c_user->is_name_free($user_fields['cu_name'])) {
				$uname = $user_fields['cu_name'];
				$strMsg .= "A user with the name $uname already exist. Please choose another username <br>";
				$c_error = 1;
				redirect($strMsg, 'tab0', "cloud-user.php");
				exit(0);
			}

            if ($c_error == 0) {
                // check how many ccunits to give for a new user
                $cc_conf = new cloudconfig();
                $cc_auto_give_ccus = $cc_conf->get_value(12);  // 12 is auto_give_ccus
                $user_fields['cu_ccunits'] = $cc_auto_give_ccus;
                $cl_user = new clouduser();
                $cl_user->add($user_fields);
                // add user to htpasswd
                $cloud_htpasswd = "$CloudDir/user/.htpasswd";
                if (file_exists($cloud_htpasswd)) {
                    $openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
                } else {
                    $openqrm_server_command="htpasswd -c -b $CloudDir/user/.htpasswd $username $password";
                }
                $output = shell_exec($openqrm_server_command);

                // set user permissions and limits, set to 0 (infinite) by default
                $cloud_user_limit = new clouduserlimits();
                $cloud_user_limits_fields['cl_id'] = openqrm_db_get_free_id('cl_id', $cloud_user_limit->_db_table);
                $cloud_user_limits_fields['cl_cu_id'] = $user_fields['cu_id'];
                $cloud_user_limits_fields['cl_resource_limit'] = 0;
                $cloud_user_limits_fields['cl_memory_limit'] = 0;
                $cloud_user_limits_fields['cl_disk_limit'] = 0;
                $cloud_user_limits_fields['cl_cpu_limit'] = 0;
                $cloud_user_limits_fields['cl_network_limit'] = 0;
                $cloud_user_limit->add($cloud_user_limits_fields);

                // send mail to user
                // get admin email
                $cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
                // get external name
                $external_portal_name = $cc_conf->get_value(3);  // 3 is the external name
                if (!strlen($external_portal_name)) {
                    $external_portal_name = "http://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
                }
                $email = $user_fields['cu_email'];
                $forename = $user_fields['cu_forename'];
                $lastname = $user_fields['cu_lastname'];
                $rmail = new cloudmailer();
                $rmail->to = "$email";
                $rmail->from = "$cc_admin_email";
                $rmail->subject = "openQRM Cloud: Your account has been created";
                $rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/welcome_new_cloud_user.mail.tmpl";
                $arr = array('@@USER@@'=>"$username", '@@PASSWORD@@'=>"$password", '@@EXTERNALPORTALNAME@@'=>"$external_portal_name", '@@FORENAME@@'=>"$forename", '@@LASTNAME@@'=>"$lastname", '@@CLOUDADMIN@@'=>"$cc_admin_email");
                $rmail->var_array = $arr;
                $rmail->send();

                $strMsg = "Added user $usecrname";
                redirect($strMsg, 'tab0', "cloud-user.php");
            }
			break;


		case 'create_user_group':
			$user_group_fields['cg_id'] = openqrm_db_get_free_id('cg_id', $CLOUD_USER_GROUPS_TABLE);
			// enabled by default
			$user_group_fields['cg_role_id'] = 1;
			// checks
			check_param("Groupname", $user_group_fields['cg_name']);
			check_param("Description", $user_group_fields['cg_description']);

            $cl_user_group = new cloudusergroup();
            $cl_user_group->add($user_group_fields);
            $strMsg = "Added user group $cg_name";
            redirect($strMsg, 'tab0', "cloud-usergroup.php");
			break;


        case 'create_request':
		
			// check if the user has ccunits
			$cr_cu_id = $request_fields['cr_cu_id'];
			$cl_user = new clouduser();
			$cl_user->get_instance_by_id($cr_cu_id);
			// check if billing is enabled
			$cb_config = new cloudconfig();
			$cloud_billing_enabled = $cb_config->get_value(16);	// 16 is cloud_billing_enabled
			if ($cloud_billing_enabled == 'true') {
				if ($cl_user->ccunits < 1) {
					$strMsg = "User does not have any ccunits ! Not adding the request";
					echo "$strMsg <br>";
					flush();
					sleep(4);
					redirect($strMsg, 'tab0', "cloud-manager.php");
					exit(0);
				}
			}

			// check user limits
			$cloud_user_limit = new clouduserlimits();
			$cloud_user_limit->get_instance_by_cu_id($cr_cu_id);
			$resource_quantity = $request_fields['cr_resource_quantity'];
			$ram_req = $request_fields['cr_ram_req'];
			$disk_req = $request_fields['cr_disk_req'];
			$cpu_req = $request_fields['cr_cpu_req'];
			$network_req = $request_fields['cr_network_req'];

			if (!$cloud_user_limit->check_limits($resource_quantity, $ram_req, $disk_req, $cpu_req, $network_req)) {
				$strMsg = "User exceeds its Cloud-Limits ! Not adding the request";
				echo "$strMsg <br>";
				flush();
				sleep(4);
				redirect($strMsg, 'tab0', "cloud-manager.php");
				exit(0);
			}

			// parse start date
			$startt = $request_fields['cr_start'];
			$tstart = date_to_timestamp($startt);
			$request_fields['cr_start'] = $tstart;
			// parse stop date
			$stopp = $request_fields['cr_stop'];
			$tstop = date_to_timestamp($stopp);
			$request_fields['cr_stop'] = $tstop;

			// set the eventual selected puppet groups
			if(htmlobject_request('puppet_groups') != '') {
				$puppet_groups_array = htmlobject_request('puppet_groups');
				if (is_array($puppet_groups_array)) {
					foreach($puppet_groups_array as $puppet_group) {
						$puppet_groups_str .= "$puppet_group,";
					}
					// remove last ,
					$puppet_groups_str = rtrim($puppet_groups_str, ",");
					$request_fields['cr_puppet_groups'] = $puppet_groups_str;
				}
			}

			// get next free id
			$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
			$cr_request = new cloudrequest();
			// set lastbill to empty
			$request_fields['cr_lastbill'] = '';
			// add request
			$cr_request->add($request_fields);

			// send mail to admin
			// get admin email
			$cc_conf = new cloudconfig();
			$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
			$cr_id = $request_fields['cr_id'];
			$cu_name = $cl_user->name;
			$cu_email = $cl_user->email;
			$rmail = new cloudmailer();
			$rmail->to = "$cc_admin_email";
			$rmail->from = "$cc_admin_email";
			$rmail->subject = "openQRM Cloud: New request from user $cu_name";
			$rmail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
			$arr = array('@@USER@@'=>"$cu_name", '@@ID@@'=>"$cr_id", '@@OPENQRM_SERVER_IP_ADDRESS@@'=>"$OPENQRM_SERVER_IP_ADDRESS");
			$rmail->var_array = $arr;
			$rmail->send();

			$strMsg = "Adding new Cloud request";
			echo "$strMsg <br>";
			flush();
			sleep(1);
			redirect($strMsg, 'tab0', "cloud-manager.php");

			break;

		default:
			$event->log("$cloud_command", $_SERVER['REQUEST_TIME'], 3, "cloud-action", "No such event command ($cloud_command)", "", "", 0, 0, 0);
			break;


	}






?>
