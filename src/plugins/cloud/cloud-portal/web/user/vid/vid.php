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


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudselector.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprofile.class.php";
require_once "$RootDir/plugins/cloud/class/cloudicon.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmatrix.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmatrixobject.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_REQUEST_TABLE;
global $event;
$matrix_default_icon = "../../img/resource.png";
global $matrix_default_icon;
$matrix_in_deployment_icon = "../../img/in_deployment.gif";
global $matrix_in_deployment_icon;
// the size of all icons in the matrix
$cloud_object_icon_size=48;
global $cloud_object_icon_size;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;
// default stop date for new requests : 10 years.
$DEFAULT_REQUEST_TIME=315360000;


// check all user input
function check_post_param($value) {
	global $event;
	// removed allowed characters from the string
	$value = str_replace("_", "", $value);
	$value = str_replace(",", "", $value);
	$value = str_replace("_", "", $value);
	$value = str_replace("&", "", $value);
	if (strlen($value)) {
		if(!ctype_alnum($value)){
			$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "ALERT: Corrupted post parameters", "", "", 0, 0, 0);
			return false;
		}
	}
	return true;
}

// object id
$cm_oid = htmlobject_request('oid');
global $cm_oid;
if (!check_post_param($cm_oid)) {
		exit(false);
}
// ca id
$cloud_appliance_id = htmlobject_request('ca_id');
global $cloud_appliance_id;
if (!check_post_param($cloud_appliance_id)) {
		exit(false);
}
// matrix array
if (isset($_REQUEST['p'])) {
	$arr_input_check = $_REQUEST['p'];
	if (is_array($arr_input_check)) {
		$arr_input_check_str = implode(",", $arr_input_check);
		if (!check_post_param($arr_input_check_str)) {
				exit(false);
		}
	}
}

// evaluate actions
if (htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {
		case 'Save':
			$cm_cloud_user = new clouduser();
			$cm_cloud_user->get_instance_by_name("$auth_user");
			// if the cloudmatrix for the user does not exist this will create it automatically
			$cloud_matrix = new cloudmatrix();
			$cloud_matrix->get_instance_by_cloud_user_id($cm_cloud_user->id);
			$arr = $_REQUEST['p'];

			// ############################# save## ############################

			// create an empty array, updating specific objects in it in the next step
			for ($x = 0; $x <16; $x++) {
				for ($y = 0; $y <12; $y++) {
					$save_matrix_arr[$y][$x]=0;
				}
			}
			// open loop through each array element
			foreach ($arr as $p){
				// detach values from each parameter
				list($id, $row, $column) = explode('_', $p);
				// remove clone flag
				if (strpos($id, 'c')) {
					$id = substr($id, 0, strpos($id, 'c'));
				}
				$sobject_type = substr($id, 0, 1);
				$sobject_id = substr($id, 1);
				$ccma = new cloudmatrixobject();
				switch ($sobject_type) {
					case 'a':
						// update cma object
						if (strlen($sobject_id)) {
							$ccma->get_instance_by_id($sobject_id);
							$update_ccmo_fields['mo_y'] = $row;
							$update_ccmo_fields['mo_x'] = $column;
							$ccma->update($sobject_id, $update_ccmo_fields);
							// save all cloudappliances in an array, later we use this to check for deleted cloudappliances
							$cloud_appliances_in_save[] = $sobject_id;
						}
						break;

					case 'p':
						// check if billing is enabled, if yes the user must have ccus
						$cloud_config = new cloudconfig();
						$cloud_billing_enabled = $cloud_config->get_value(16);	// 16 is cloud_billing_enabled
						if ($cloud_billing_enabled == 'true') {
							if ($cm_cloud_user->ccunits < 1) {
								$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "CloudUser ".$cm_cloud_user->name." does not have any CCUs any more. Not submitting new request.", "", "", 0, 0, 0);
								exit(false);
							}
						}
						// get profile
						$apply_cloud_profile = new cloudprofile();
						$apply_cloud_profile->get_instance_by_id($sobject_id);
						// check user limits #######################
						$cloud_user_limit = new clouduserlimits();
						$cloud_user_limit->get_instance_by_cu_id($cm_cloud_user->id);
						if (!$cloud_user_limit->check_limits(1, $apply_cloud_profile->ram_req, $apply_cloud_profile->disk_req, $apply_cloud_profile->cpu_req, $apply_cloud_profile->network_req)) {
							$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "CloudUser ".$cm_cloud_user->name." exceeds its Cloud-Limits. Not submitting new request.", "", "", 0, 0, 0);
							exit(false);
						}
						// create cma
						$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Requesting Cloud Profile $sobject_id", "", "", 0, 0, 0);
						$request_fields['cr_cu_id'] = $cm_cloud_user->id;
						$request_fields['cr_status'] = $apply_cloud_profile->status;
						$request_fields['cr_request_time'] = $_SERVER['REQUEST_TIME'];
						$request_fields['cr_start'] = $_SERVER['REQUEST_TIME'];
						$request_fields['cr_stop'] = $_SERVER['REQUEST_TIME'] + $DEFAULT_REQUEST_TIME;
						$request_fields['cr_kernel_id'] = $apply_cloud_profile->kernel_id;
						$request_fields['cr_image_id'] = $apply_cloud_profile->image_id;
						$request_fields['cr_ram_req'] = $apply_cloud_profile->ram_req;
						$request_fields['cr_cpu_req'] = $apply_cloud_profile->cpu_req;
						$request_fields['cr_disk_req'] = $apply_cloud_profile->disk_req;
						$request_fields['cr_network_req'] = $apply_cloud_profile->network_req;
						$request_fields['cr_resource_quantity'] = $apply_cloud_profile->resource_quantity;
						$request_fields['cr_resource_type_req'] = $apply_cloud_profile->resource_type_req;
						$request_fields['cr_deployment_type_req'] = $apply_cloud_profile->deployment_type_req;
						$request_fields['cr_ha_req'] = $apply_cloud_profile->ha_req;
						$request_fields['cr_shared_req'] = $apply_cloud_profile->shared_req;
						$request_fields['cr_puppet_groups'] = $apply_cloud_profile->puppet_groups;
						$request_fields['cr_ip_mgmt'] = $apply_cloud_profile->ip_mgmt;
						$request_fields['cr_appliance_id'] = $apply_cloud_profile->appliance_id;
						$request_fields['cr_lastbill'] = $apply_cloud_profile->lastbill;
						$request_fields['cr_id'] = openqrm_db_get_free_id('cr_id', $CLOUD_REQUEST_TABLE);
						$new_cr_id = $request_fields['cr_id'];
						$cr_request = new cloudrequest();
						$cr_request->add($request_fields);
						$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Submitted Cloud Request $new_cr_id", "", "", 0, 0, 0);

						// create new cloudmatrixobject to track the transformation of the cloudprofile to a cloudappliance
						$mo_request_fields['mo_pr_id'] = $sobject_id;
						$mo_request_fields['mo_cr_id'] = $new_cr_id;
						$mo_request_fields['mo_ca_id'] = 0;
						$mo_request_fields['mo_table'] = 2;
						$mo_request_fields['mo_ne_id'] = 0;
						$mo_request_fields['mo_x'] = $column;
						$mo_request_fields['mo_y'] = $row;
						$mo_request_fields['mo_state'] = 0;
						$mo_request_fields['mo_id'] = openqrm_db_get_free_id('mo_id', $ccma->_db_table);
						$new_mo_id = $mo_request_fields['mo_id'];
						$ccma->add($mo_request_fields);
						// add cma id
						$sobject_id = $new_mo_id;
						// here we transform the profile into an cloudmatrixobject
						$sobject_type = 'a';
						$scloud_app_identifier = $sobject_type.$sobject_id;
						// this echo is for the ajax profile drop event, this will be the new name of the cell
						echo "$scloud_app_identifier";
						break;


					case 'w':
						// create new cloudmatrixobject to track the network connection
						$mo_request_fields['mo_pr_id'] = 0;
						$mo_request_fields['mo_cr_id'] = 0;
						$mo_request_fields['mo_ca_id'] = 0;
						$mo_request_fields['mo_ne_id'] = $sobject_id;
						$mo_request_fields['mo_table'] = 2;
						$mo_request_fields['mo_x'] = $column;
						$mo_request_fields['mo_y'] = $row;
						$mo_request_fields['mo_state'] = 0;
						$mo_request_fields['mo_id'] = openqrm_db_get_free_id('mo_id', $ccma->_db_table);
						$new_mo_id = $mo_request_fields['mo_id'];
						$ccma->add($mo_request_fields);
						// add cma id
						$sobject_id = $new_mo_id;
						$sobject_type = 'n';
						// here we transform the network connectin
						$scloud_net_identifier = $sobject_type.$sobject_id;
						// this echo is for the ajax profile drop event, this will be the new name of the cell
						echo "$scloud_net_identifier";
						break;

					case 'n':
						// the network object got moved and we update x/y
						if (strlen($sobject_id)) {
							$ccma->get_instance_by_id($sobject_id);
							$update_ccmo_fields['mo_y'] = $row;
							$update_ccmo_fields['mo_x'] = $column;
							$ccma->update($sobject_id, $update_ccmo_fields);
						}
						break;


				}
				//$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "!! saving object type $sobject_type - $sobject_id in column $column row $row", "", "", 0, 0, 0);
				$save_matrix_arr[$row][$column]=$sobject_type.$sobject_id;
			}

			$m = $save_matrix_arr;
			//$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", print_r($m), "", "", 0, 0, 0);

			$cloud_matrix_update_arr = array(
					'cm_row01' => $m[0][0].",".$m[0][1].",".$m[0][2].",".$m[0][3].",".$m[0][4].",".$m[0][5].",".$m[0][6].",".$m[0][7].",".$m[0][8].",".$m[0][9].",".$m[0][10].",".$m[0][11].",".$m[0][12].",".$m[0][13].",".$m[0][14].",".$m[0][15],
					'cm_row02' => $m[1][0].",".$m[1][1].",".$m[1][2].",".$m[1][3].",".$m[1][4].",".$m[1][5].",".$m[1][6].",".$m[1][7].",".$m[1][8].",".$m[1][9].",".$m[1][10].",".$m[1][11].",".$m[1][12].",".$m[1][13].",".$m[1][14].",".$m[1][15],
					'cm_row03' => $m[2][0].",".$m[2][1].",".$m[2][2].",".$m[2][3].",".$m[2][4].",".$m[2][5].",".$m[2][6].",".$m[2][7].",".$m[2][8].",".$m[2][9].",".$m[2][10].",".$m[2][11].",".$m[2][12].",".$m[2][13].",".$m[2][14].",".$m[2][15],
					'cm_row04' => $m[3][0].",".$m[3][1].",".$m[3][2].",".$m[3][3].",".$m[3][4].",".$m[3][5].",".$m[3][6].",".$m[3][7].",".$m[3][8].",".$m[3][9].",".$m[3][10].",".$m[3][11].",".$m[3][12].",".$m[3][13].",".$m[3][14].",".$m[3][15],
					'cm_row05' => $m[4][0].",".$m[4][1].",".$m[4][2].",".$m[4][3].",".$m[4][4].",".$m[4][5].",".$m[4][6].",".$m[4][7].",".$m[4][8].",".$m[4][9].",".$m[4][10].",".$m[4][11].",".$m[4][12].",".$m[4][13].",".$m[4][14].",".$m[4][15],
					'cm_row06' => $m[5][0].",".$m[5][1].",".$m[5][2].",".$m[5][3].",".$m[5][4].",".$m[5][5].",".$m[5][6].",".$m[5][7].",".$m[5][8].",".$m[5][9].",".$m[5][10].",".$m[5][11].",".$m[5][12].",".$m[5][13].",".$m[5][14].",".$m[5][15],
					'cm_row07' => $m[6][0].",".$m[6][1].",".$m[6][2].",".$m[6][3].",".$m[6][4].",".$m[6][5].",".$m[6][6].",".$m[6][7].",".$m[6][8].",".$m[6][9].",".$m[6][10].",".$m[6][11].",".$m[6][12].",".$m[6][13].",".$m[6][14].",".$m[6][15],
					'cm_row08' => $m[7][0].",".$m[7][1].",".$m[7][2].",".$m[7][3].",".$m[7][4].",".$m[7][5].",".$m[7][6].",".$m[7][7].",".$m[7][8].",".$m[7][9].",".$m[7][10].",".$m[7][11].",".$m[7][12].",".$m[7][13].",".$m[7][14].",".$m[7][15],
					'cm_row09' => $m[8][0].",".$m[8][1].",".$m[8][2].",".$m[8][3].",".$m[8][4].",".$m[8][5].",".$m[8][6].",".$m[8][7].",".$m[8][8].",".$m[8][9].",".$m[8][10].",".$m[8][11].",".$m[8][12].",".$m[8][13].",".$m[8][14].",".$m[8][15],
					'cm_row10' => $m[9][0].",".$m[9][1].",".$m[9][2].",".$m[9][3].",".$m[9][4].",".$m[9][5].",".$m[9][6].",".$m[9][7].",".$m[9][8].",".$m[9][9].",".$m[9][10].",".$m[9][11].",".$m[9][12].",".$m[9][13].",".$m[9][14].",".$m[9][15],
					'cm_row11' => $m[10][0].",".$m[10][1].",".$m[10][2].",".$m[10][3].",".$m[10][4].",".$m[10][5].",".$m[10][6].",".$m[10][7].",".$m[10][8].",".$m[10][9].",".$m[10][10].",".$m[10][11].",".$m[10][12].",".$m[10][13].",".$m[10][14].",".$m[10][15],
					'cm_row12' => $m[11][0].",".$m[11][1].",".$m[11][2].",".$m[11][3].",".$m[11][4].",".$m[11][5].",".$m[11][6].",".$m[11][7].",".$m[11][8].",".$m[11][9].",".$m[11][10].",".$m[11][11].",".$m[11][12].",".$m[11][13].",".$m[11][14].",".$m[11][15],
			);

			$cloud_matrix->update($cloud_matrix->id, $cloud_matrix_update_arr);
			exit();
			break;


		case 'Load':
			$cm_cloud_user = new clouduser();
			$cm_cloud_user->get_instance_by_name("$auth_user");
			// if the cloudmatrix for the user does not exist this will create it automatically
			$cloud_matrix = new cloudmatrix();
			$cloud_matrix->get_instance_by_cloud_user_id($cm_cloud_user->id);
			echo $cloud_matrix->row01.",\n";
			echo $cloud_matrix->row02.",\n";
			echo $cloud_matrix->row03.",\n";
			echo $cloud_matrix->row04.",\n";
			echo $cloud_matrix->row05.",\n";
			echo $cloud_matrix->row06.",\n";
			echo $cloud_matrix->row07.",\n";
			echo $cloud_matrix->row08.",\n";
			echo $cloud_matrix->row09.",\n";
			echo $cloud_matrix->row10.",\n";
			echo $cloud_matrix->row11.",\n";
			echo $cloud_matrix->row12;
			exit();
			break;


		case 'LoadItem':
			$cm_cloud_user = new clouduser();
			$cm_cloud_user->get_instance_by_name("$auth_user");
			// if the cloudmatrix for the user does not exist this will create it automatically
			$cloud_matrix = new cloudmatrix();
			$cloud_matrix->get_instance_by_cloud_user_id($cm_cloud_user->id);
			// check object type + id
			$li_oid_type = substr($cm_oid, 0, 1);
			$li_oid_id = substr($cm_oid, 1);
			$li_cmo = new cloudmatrixobject();
			$li_cmo->get_instance_by_id($li_oid_id);
			switch ($li_oid_type) {
				case 'a':
					// li_cmo still valid ?
					if (!strlen($li_cmo->cr_id)) {
						break;
					}
					$li_return_info_link_str = '';
					// check state and set icon + background according
					$li_cr = new cloudrequest();
					$li_cr->get_instance($li_cmo->cr_id);
					// check cr state, if > 3 means it was external deprovisioned
					if ($li_cr->status > 3) {
						// remove cmo
						$li_cmo->remove($li_oid_id);
						// exist without output div content, this will remove the cell from the matrix
						exit();
					}
					// check if it has an appliance set (!= 0)
					if ($li_cr->appliance_id != 0) {
						// check for custom icon
						$li_icon = new cloudicon();
						$li_icon->get_instance_by_details($cm_cloud_user->id, 2, $li_cmo->ca_id);
						if (strlen($li_icon->filename)) {
							$li_icon_filename = "../custom-icons/".$li_icon->filename;
						} else if (($li_cmo->pr_id != 0) && ($li_cmo->ca_id != 0)) {
							// if we have a profile id, check if we have an icon there, if yes, clone it
							$li_icon->get_instance_by_details($cm_cloud_user->id, 1, $li_cmo->pr_id);
							if (strlen($li_icon->filename)) {
								// physically copy the icon file
								$lfextension = substr($li_icon->filename, strlen($li_icon->filename)-3);
								$licon_token = md5(uniqid(rand(), true));
								$licon_filename = "../custom-icons/".$licon_token.".".$lfextension;
								if (!copy("../custom-icons/".$li_icon->filename, $licon_filename)) {
									$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Failed to clone icon ../custom-icons/".$li_icon->filename, "", "", 0, 0, 0);
								}
								// create new cloudicon object
								$lcloud_icon_id  = openqrm_db_get_free_id('ic_id', $li_icon->_db_table);
								$lcloud_icon_arr = array(
										'ic_id' => $lcloud_icon_id,
										'ic_cu_id' => $cm_cloud_user->id,
										'ic_type' => 2,
										'ic_object_id' => $li_cmo->ca_id,
										'ic_filename' => $licon_token.".".$lfextension,
								);
								$li_icon->add($lcloud_icon_arr);
								$li_icon_filename = "../custom-icons/" . $li_icon->filename;
							} else {
								$li_icon_filename = "../../img/resource.png";
							}
						} else {
							$li_icon_filename = "../../img/resource.png";
						}
						// check for state, take the resource stat
						// we need to get the possible array of appliances in the cr, we show just the first of each cr
						$li_appliance_id = $li_cr->appliance_id;
						$app_id_arr = explode(",", $li_appliance_id);
						foreach ($app_id_arr as $app_id) {
							$cloud_app = new cloudappliance();
							$cloud_app->get_instance_by_appliance_id($app_id);
							$ĺi_app = new appliance();
							$ĺi_app->get_instance_by_id($app_id);
							$li_resource = new resource();
							if ($ĺi_app->resources > 0) {
								$li_resource->get_instance_by_id($ĺi_app->resources);
								switch ($li_resource->state) {
									case 'active':
										// check that it is fully deployed and not idle after unpause
										if ($li_resource->imageid == 1) {
											$li_icon_filename = "../../img/in_deployment.gif";
											$li_icon_title = "Starting CloudAppliance ".$ĺi_app->name;
											// started but not fully active
											$update_li_cmo_fields['mo_state'] = 1;
										} else {
											$li_icon_filename = $li_icon_filename;
											$li_icon_title = "Active CloudAppliance ".$ĺi_app->name;
											// active state for the cmo
											$update_li_cmo_fields['mo_state'] = 2;
										}
										break;
									case 'error':
										$li_icon_filename = "../../img/error.png";
										$li_icon_title = "Error on CloudAppliance ".$ĺi_app->name;
										// active state for the cmo
										$update_li_cmo_fields['mo_state'] = 4;
										break;
									default:
										$li_icon_filename = "../../img/in_deployment.gif";
										$li_icon_title = "Starting CloudAppliance ".$ĺi_app->name;
										// started but not fully active
										$update_li_cmo_fields['mo_state'] = 1;
										break;

								}
							} else {
								// appliance is paused
								$li_icon_filename = "../../img/pause.png";
								$li_icon_title = "Paused CloudAppliance ".$ĺi_app->name;
								// started but not fully active
								$update_li_cmo_fields['mo_state'] = 3;

							}
							// set info link
							$cloud_app_identifier = "a".$li_oid_id;
							$li_return_info_link_str .= "<br><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('vid.php?action=Info&oid=$cloud_app_identifier','','location=0,status=0,scrollbars=1,width=400,height=450,left=200,top=50,screenX=200,screenY=50');\">#</a></small>";
							// update cmo with ca id + set state
							$update_li_cmo_fields['mo_ca_id'] = $cloud_app->id;
							$li_cmo->update($li_cmo->id, $update_li_cmo_fields);
							// just the first ca of the cr
							break;
						}

					} else {
						// in deployment icon
						$li_icon_filename = "../../img/in_deployment.gif";
						$li_icon_title = "Deployment started for Cloudrequest ".$li_cmo->cr_id;

					}
					$li_return_str = "<img height=\"".$cloud_object_icon_size."\" width=\"".$cloud_object_icon_size."\" alt=\"".$li_icon_title.'" title="'.$li_icon_title.'" src="'.$li_icon_filename."\">";
					$li_return_str .= $li_return_info_link_str;
					echo $li_return_str;
					break;


				case 'n':
					// define the network icon mapping
					$netcon_icon[1] = "../../img/net_vertical.png";
					$netcon_icon[2] = "../../img/net_horizontal.png";
					$netcon_icon[3] = "../../img/net_up_right.png";
					$netcon_icon[4] = "../../img/net_up_left.png";
					$netcon_icon[5] = "../../img/net_down_right.png";
					$netcon_icon[6] = "../../img/net_down_left.png";
					$netcon_icon[7] = "../../img/net_switch_up.png";
					$netcon_icon[8] = "../../img/net_switch_right.png";
					$netcon_icon[9] = "../../img/net_switch_down.png";
					$netcon_icon[10] = "../../img/net_switch_left.png";
					$netcon_icon[11] = "../../img/net_switch.png";
					$li_net_icon = $netcon_icon[$li_cmo->ne_id];
					echo "<img height=\"".$cloud_object_icon_size."\" width=\"".$cloud_object_icon_size."\" src=\"".$li_net_icon."\">";
					break;
			}


			exit();
			break;


		case 'Remove':
			$cm_cloud_user = new clouduser();
			$cm_cloud_user->get_instance_by_name("$auth_user");
			// if the cloudmatrix for the user does not exist this will create it automatically
			$cloud_matrix = new cloudmatrix();
			$cloud_matrix->get_instance_by_cloud_user_id($cm_cloud_user->id);
			// check if we the object to remove is an cma, if yes, deprovision the request of the cma
			$rcm_oid_type = substr($cm_oid, 0, 1);
			switch ($rcm_oid_type) {
				case 'a':
					$rcm_oid_id = substr($cm_oid, 1);
					$rcma = new cloudmatrixobject();
					$rcma->get_instance_by_id($rcm_oid_id);
					$rcr = new cloudrequest();
					$rcr->setstatus($rcma->cr_id, 'deprovision');
					$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Setting Cloudrequest $rcma->cr_id to deprovision", "", "", 0, 0, 0);
					$rcma->remove($rcma->id);
					break;

				case 'n':
					$rcm_oid_id = substr($cm_oid, 1);
					$rcma = new cloudmatrixobject();
					$rcma->get_instance_by_id($rcm_oid_id);
					$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Removing network connection $rcma->id (Wire $rcma->ne_id)", "", "", 0, 0, 0);
					$rcma->remove($rcma->id);
					break;
			}
			// update matrix
			$row01_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row01.",");
			$row02_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row02.",");
			$row03_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row03.",");
			$row04_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row04.",");
			$row05_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row05.",");
			$row06_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row06.",");
			$row07_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row07.",");
			$row08_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row08.",");
			$row09_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row09.",");
			$row10_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row10.",");
			$row11_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row11.",");
			$row12_update =  str_replace($cm_oid.",", "0,", $cloud_matrix->row12.",");
			$cloud_matrix_remove_arr = array(
					'cm_row01' => substr($row01_update, 0, strlen($row01_update)-1),
					'cm_row02' => substr($row02_update, 0, strlen($row02_update)-1),
					'cm_row03' => substr($row03_update, 0, strlen($row03_update)-1),
					'cm_row04' => substr($row04_update, 0, strlen($row04_update)-1),
					'cm_row05' => substr($row05_update, 0, strlen($row05_update)-1),
					'cm_row06' => substr($row06_update, 0, strlen($row06_update)-1),
					'cm_row07' => substr($row07_update, 0, strlen($row07_update)-1),
					'cm_row08' => substr($row08_update, 0, strlen($row08_update)-1),
					'cm_row09' => substr($row09_update, 0, strlen($row09_update)-1),
					'cm_row10' => substr($row10_update, 0, strlen($row10_update)-1),
					'cm_row11' => substr($row11_update, 0, strlen($row11_update)-1),
					'cm_row12' => substr($row12_update, 0, strlen($row12_update)-1),
			);
			$cloud_matrix->update($cloud_matrix->id, $cloud_matrix_remove_arr);
			exit();
			break;



		case 'Pause':
			$cloud_appliance_id = htmlobject_request('ca_id');
			// get the user
			$clouduser = new clouduser();
			$clouduser->get_instance_by_name($auth_user);
			$cloud_appliance_pause = new cloudappliance();
			$cloud_appliance_pause->get_instance_by_id($cloud_appliance_id);
			$appliance_pause = new appliance();
			$appliance_pause->get_instance_by_id($cloud_appliance_pause->appliance_id);
			// is it ours ?
			$cl_request = new cloudrequest();
			$cl_request->get_instance_by_id($cloud_appliance_pause->cr_id);
			if ($cl_request->cu_id != $clouduser->id) {
				exit(false);
			}
			$action_return_string="";
			// check if no other command is currently running
			if ($cloud_appliance_pause->cmd != 0) {
				$action_return_string = "Another command is already registerd for Cloud appliance ".$appliance_pause->name.". Please wait until it got executed";
				echo $action_return_string;
				exit(false);
			}
			// check that state is active
			if ($cloud_appliance_pause->state == 1) {
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				$cloud_appliance_pause->set_cmd($cloud_appliance_pause->id, "stop");
				$cloud_appliance_pause->set_state($cloud_appliance_pause->id, "paused");
				$action_return_string = "Registered Cloud appliance ".$appliance_pause->name." to stop (pause)";
				// send mail to cloud-admin
				$armail = new cloudmailer();
				$armail->to = "$cc_admin_email";
				$armail->from = "$cc_admin_email";
				$armail->subject = "openQRM Cloud: Cloud Appliance ".$appliance_pause->name." registered for stop (pause)";
				$armail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/paused_cloud_appliance.mail.tmpl";
				$arr = array('@@USER@@'=>"$clouduser->name", '@@CLOUD_APPLIANCE_ID@@'=>"$cloud_appliance_id");
				$armail->var_array = $arr;
				$armail->send();
				echo $action_return_string;
				exit();
			} else {
				$action_return_string = "Can only pause Cloud appliance ".$appliance_pause->name." if it is in active state";
				echo $action_return_string;
				exit(false);
			}
			exit();
			break;


		case 'unPause':
			$cloud_appliance_id = htmlobject_request('ca_id');
			// get the user
			$clouduser = new clouduser();
			$clouduser->get_instance_by_name($auth_user);
			$cloud_appliance_pause = new cloudappliance();
			$cloud_appliance_pause->get_instance_by_id($cloud_appliance_id);
			$appliance_pause = new appliance();
			$appliance_pause->get_instance_by_id($cloud_appliance_pause->appliance_id);
			// is it ours ?
			$cl_request = new cloudrequest();
			$cl_request->get_instance_by_id($cloud_appliance_pause->cr_id);
			if ($cl_request->cu_id != $clouduser->id) {
				exit(false);
			}
			$action_return_string="";
			// check if no other command is currently running
			if ($cloud_appliance_pause->cmd != 0) {
				$action_return_string = "Another command is already registerd for Cloud appliance ".$appliance_pause->name.". Please wait until it got executed";
				echo $action_return_string;
				exit(false);
			}

			// check if it is in state paused
			if ($cloud_appliance_pause->state == 0) {
				$cloud_appliance_pause->set_cmd($cloud_appliance_pause->id, "start");
				$cloud_appliance_pause->set_state($cloud_appliance_pause->id, "active");
				$action_return_string = "Registered Cloud appliance ".$appliance_pause->name." to start (unpause)";

				// send mail to cloud-admin
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				$armail = new cloudmailer();
				$armail->to = "$cc_admin_email";
				$armail->from = "$cc_admin_email";
				$armail->subject = "openQRM Cloud: Cloud Appliance ".$appliance_pause->name." registered for start (unpause)";
				$armail->template = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/cloud/etc/mail/unpaused_cloud_appliance.mail.tmpl";
				$arr = array('@@USER@@'=>"$clouduser->name", '@@CLOUD_APPLIANCE_ID@@'=>"$cloud_appliance_id");
				$armail->var_array = $arr;
				$armail->send();
				echo $action_return_string;
				exit();
			} else {
				$action_return_string = "Can only pause Cloud appliance ".$appliance_pause->name." if it is in paused state";
				echo $action_return_string;
				exit(false);
			}
			exit();
			break;



		case 'Restart':
			// get the user
			$clouduser = new clouduser();
			$clouduser->get_instance_by_name($auth_user);
			$cloud_appliance_pause = new cloudappliance();
			$cloud_appliance_pause->get_instance_by_id($cloud_appliance_id);
			$appliance_pause = new appliance();
			$appliance_pause->get_instance_by_id($cloud_appliance_pause->appliance_id);
			// is it ours ?
			$cl_request = new cloudrequest();
			$cl_request->get_instance_by_id($cloud_appliance_pause->cr_id);
			if ($cl_request->cu_id != $clouduser->id) {
				exit(false);
			}
			$action_return_string="";
			// check if no other command is currently running
			if ($cloud_appliance_pause->cmd != 0) {
				$action_return_string = "Another command is already registerd for Cloud appliance ".$appliance_pause->name.". Please wait until it got executed";
				echo $action_return_string;
				exit(false);
			}
			// check that state is active
			if ($cloud_appliance_pause->state == 1) {
				// get admin email
				$cc_conf = new cloudconfig();
				$cc_admin_email = $cc_conf->get_value(1);  // 1 is admin_email
				$cloud_appliance_pause->set_cmd($cloud_appliance_pause->id, "restart");
				$action_return_string = "Registered Cloud appliance ".$appliance_pause->name." to restart (reboot)";
				echo $action_return_string;
				exit();
			} else {
				$action_return_string = "Can only restart Cloud appliance ".$appliance_pause->name." if it is in active state";
				echo $action_return_string;
				exit(false);
			}
			exit();
			break;



		case 'GetStatus':
			$st_overall_state = 1;
			$st_cloud_user = new clouduser();
			$st_cloud_user->get_instance_by_name("$auth_user");
			// get users active crs
			$st_cr = new cloudrequest();
			$st_active_cloudrequest = $st_cr->get_all_active_ids_per_user($st_cloud_user->id);
			foreach($st_active_cloudrequest as $st_cr) {
				$st_cr_id = $st_cr['cr_id'];
				// get cmo
				unset($st_cmo);
				$st_cmo = new cloudmatrixobject();
				$st_cmo->get_instance_by_cr_id($st_cr_id);
				// if we do not have a valid cmo here this means
				// that we have detected a custom ca without profile
				if (!strlen($st_cmo->id)) {
					// we need to check and wait until the cr has a ca set
					$st_custom_cr = new cloudrequest();
					$st_custom_cr->get_instance($st_cr_id);
					if ($st_custom_cr->appliance_id == 0) {
						continue;
					}
					// cr->appliance_id can be a comma seaprated array
					// get only the first ca
					$st_custom_ca = new cloudappliance();
					$st_custom_app_array = explode(",", $st_custom_cr->appliance_id);
					if (is_array($st_custom_app_array)) {
						foreach($st_custom_app_array as $st_app_id) {
							$st_custom_ca->get_instance_by_appliance_id($st_app_id);
							break;
						}
					} else {
						continue;
					}
					// here we have everything to create the new cmo
					$stmo_request_fields['mo_pr_id'] = 0;
					$stmo_request_fields['mo_cr_id'] = $st_cr_id;
					$stmo_request_fields['mo_ca_id'] = $st_custom_ca->id;
					$stmo_request_fields['mo_table'] = 2;
					$stmo_request_fields['mo_ne_id'] = 0;
					$stmo_request_fields['mo_x'] = 0;
					$stmo_request_fields['mo_y'] = 0;
					$stmo_request_fields['mo_state'] = 0;
					$stmo_request_fields['mo_id'] = openqrm_db_get_free_id('mo_id', $st_cmo->_db_table);
					$new_stmo_id = $stmo_request_fields['mo_id'];
					$st_cmo->add($stmo_request_fields);
					$event->log("openqrm-vid", $_SERVER['REQUEST_TIME'], 5, "openqrm-vid.php", "Creating new cloudmatrixobject $new_stmo_id for custom cr $st_cr_id", "", "", 0, 0, 0);
					// state 4 triggers a reload to place the new detected ca
					// stop the loop here, place cas one by one
					echo "a".$new_stmo_id;
					exit();

				} else {
					// evaluate state
					switch ($st_cmo->state) {
						// no ca yet
						case 0:
							if ($st_overall_state < 2) {
								// in transition now
								$st_overall_state = 2;
							}
							break;
						case 1:
							if ($st_overall_state < 2) {
								// unknown state, starting or off
								$st_overall_state = 2;
							}
							break;
						case 2:
							// active app, no action
							break;
						case 3:
							if ($st_overall_state < 2) {
								// in pause
								$st_overall_state = 2;
							}
							break;
						case 4:
							if ($st_overall_state < 3) {
								// in error
								$st_overall_state = 3;
							}
							break;
					}
				}
			}
			// evaluate return string
			switch ($st_overall_state) {
				case 1:
					echo "green";
					break;
				case 2:
					echo "orange";
					break;
				case 3:
					echo "red";
					break;

			}
			exit();
			break;




		case 'Info':
			// this action will return html code for the object tooltips
			$info_cloud_user = new clouduser();
			$info_cloud_user->get_instance_by_name("$auth_user");
			$object_type = substr($cm_oid, 0, 1);
			$object_id = substr($cm_oid, 1);
			// organize output according the object type
			switch ($object_type) {
				case 'p':
					$info_cloud_profile = new cloudprofile();
					$info_cloud_profile->get_instance_by_id($object_id);
					$info_cloud_icon = new cloudicon();
					$info_cloud_icon->get_instance_by_details($info_cloud_user->id, 1, $object_id);
					if (strlen($info_cloud_icon->filename)) {
						$info_cloud_icon_image = "../custom-icons/" . $info_cloud_icon->filename;
					} else {
						$info_cloud_icon_image = "../../img/resource.png";
					}
					$ic_object = "<img height=\"$cloud_object_icon_size\" width=\"$cloud_object_icon_size\" src=\"$info_cloud_icon_image\">";

					$info_kernel = new kernel();
					$info_kernel->get_instance_by_id($info_cloud_profile->kernel_id);
					$info_image = new image();
					$info_image->get_instance_by_id($info_cloud_profile->image_id);
					$info_virtualization = new virtualization();
					$info_virtualization->get_instance_by_id($info_cloud_profile->resource_type_req);

					$table = new htmlobject_table_builder();
					$arHead = array();

					$arHead['vid_key'] = array();
					$arHead['vid_key']['title'] ='Profile';

					$arHead['vid_value'] = array();
					$arHead['vid_value']['title'] = "$info_cloud_profile->name";

					$arBody = array();

					$arBody[] = array(
						'vid_key' => "<b>Name:</b>",
						'vid_value' => $info_cloud_profile->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Description:</b>",
						'vid_value' => $info_cloud_profile->description,
					);
					$arBody[] = array(
						'vid_key' => "<b>Kernel:</b>",
						'vid_value' => $info_kernel->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Image:</b>",
						'vid_value' => $info_image->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Memory:</b>",
						'vid_value' => $info_cloud_profile->ram_req." MB",
					);
					$arBody[] = array(
						'vid_key' => "<b>CPU:</b>",
						'vid_value' => $info_cloud_profile->cpu_req,
					);
					$arBody[] = array(
						'vid_key' => "<b>Disk:</b>",
						'vid_value' => $info_cloud_profile->disk_req." MB",
					);
					$arBody[] = array(
						'vid_key' => "<b>Network:</b>",
						'vid_value' => $info_cloud_profile->network_req,
					);
					$arBody[] = array(
						'vid_key' => "<b>Type:</b>",
						'vid_value' => $info_virtualization->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>HA:</b>",
						'vid_value' => $info_cloud_profile->ha_req,
					);
					$arBody[] = array(
						'vid_key' => "<b>Applications:</b>",
						'vid_value' => $info_cloud_profile->puppet_groups,
					);

					$table->id = 'InfoTab';
					$table->css = 'htmlobject_table';
					$table->border = 1;
					$table->cellspacing = 0;
					$table->cellpadding = 3;
					$table->form_action = $thisfile;
					$table->sort='';
					$table->head = $arHead;
					$table->body = $arBody;
					$table->max = 100;

					//------------------------------------------------------------ set template
					$t = new Template_PHPLIB();
					$t->debug = false;
					$t->setFile('tplfile', './' . 'vid-info-tpl.php');
					$t->setVar(array(
						'cloud_object_type' => "Profile",
						'cloud_object' => $info_cloud_profile->name,
						'object_logo' => $ic_object,
						'object_table' => $table->get_string(),
					));

					$disp =  $t->parse('out', 'tplfile');
					echo $disp;
					exit();
					break;


				case 'a':
					$info_cma = new cloudmatrixobject();
					$info_cma->get_instance_by_id($object_id);
					$info_cloud_appliance = new cloudappliance();
					$info_cloud_appliance->get_instance_by_id($info_cma->ca_id);
					$info_appliance = new appliance();
					$info_appliance->get_instance_by_id($info_cloud_appliance->appliance_id);
					$info_resource = new resource();
					$info_resource->get_instance_by_id($info_appliance->resources);
					$info_actions = "";
					$info_ssh_login = '';
					// collectd enabled ?
					$collectd_graph_enabled = false;
					$cc_conf = new cloudconfig();
					$show_collectd_graph = $cc_conf->get_value(19);	// show_collectd_graph
					if (!strcmp($show_collectd_graph, "true")) {
						if (file_exists("$RootDir/plugins/collectd/.running")) {
							$collectd_graph_enabled = true;
						}
					}
					// appliaction ha enabled ?
					$show_application_ha = false;
					$show_ha_checkbox = $cc_conf->get_value(10);	// show_ha_checkbox
					if (!strcmp($show_ha_checkbox, "true")) {
						// is drdbmc enabled ?
						if (file_exists("$RootDir/plugins/drbdmc/.running")) {
							$show_application_ha = true;
						}
					}
					// ip-mgmt enabled ?
					$sshterm_login_ip = '';
					$appliance_resources_str = '';
					$show_ip_mgmt = $cc_conf->get_value(26);	// ip-mgmt enabled ?
					if (!strcmp($show_ip_mgmt, "true")) {
						if (file_exists("$RootDir/plugins/ip-mgmt/.running")) {
							require_once "$RootDir/plugins/ip-mgmt/class/ip-mgmt.class.php";

							$ip_mgmt = new ip_mgmt();
							$appliance_first_nic_ip_mgmt_id = $ip_mgmt->get_id_by_appliance($info_appliance->id, 1);
							if ($appliance_first_nic_ip_mgmt_id > 0) {
								$appliance_ip_mgmt_config_arr = $ip_mgmt->get_instance('id', $appliance_first_nic_ip_mgmt_id);
								if (isset($appliance_ip_mgmt_config_arr['ip_mgmt_address'])) {
									$sshterm_login_ip = $appliance_ip_mgmt_config_arr['ip_mgmt_address'];
									$appliance_resources_str = $appliance_ip_mgmt_config_arr['ip_mgmt_address'];
								}
							}
						}
					}
					
					// get the icon
					$info_cloud_icon = new cloudicon();
					$info_cloud_icon->get_instance_by_details($info_cloud_user->id, 2, $info_cloud_appliance->id);
					if (strlen($info_cloud_icon->filename)) {
						$info_cloud_icon_image = "../custom-icons/" . $info_cloud_icon->filename;
					} else {
						$info_cloud_icon_image = "../../img/resource.png";
					}
					// ssh login only when active
					switch ($info_cloud_appliance->state) {
						case '1':
							// check the real resource state to only show ssh login when the resource is fully started
							switch ($info_resource->state) {
								case 'active':
									// check that it is fully deployed and not idle after unpause
									if ($info_resource->imageid == 1) {
										$info_app_state = "In transition";
										$info_cloud_icon_image = "../../img/in_deployment.gif";
									} else {
										// fully deployed
										$info_app_state = "Active";
										// ssh login
										if (file_exists("$RootDir/plugins/sshterm/.running")) {
											// get the parameters from the plugin config file
											$OPENQRM_PLUGIN_SSHTERM_CONFIG_FILE="$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/sshterm/etc/openqrm-plugin-sshterm.conf";
											$store = openqrm_parse_conf($OPENQRM_PLUGIN_SSHTERM_CONFIG_FILE);
											extract($store);
											$info_resource = new resource();
											$info_resource->get_instance_by_id($info_appliance->resources);
											// internal or ip-mgmt ip ?
											if (!strlen($sshterm_login_ip)) {
												$sshterm_login_ip = $info_resource->ip;
												$appliance_resources_str = $info_resource->ip;
											}

											$sshterm_login_url="https://$sshterm_login_ip:$OPENQRM_PLUGIN_WEBSHELL_PORT";
											$info_ssh_login = "<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('$sshterm_login_url','','location=0,status=0,scrollbars=1,width=580,height=420,left=400,top=100,screenX=400,screenY=100');\">
												<image border=\"0\" height=\"24\" width=\"24\" alt=\"SSH-Login to ".$info_appliance->name."\" title=\"SSH-Login to ".$info_appliance->name."\" src=\"../../img/login.png\"> SSH-Login
												</a>";
										}
										// application ha
										if ($show_application_ha) {
											$drbdmc_gui="../drbdmc/drbdmc-gui.php";
											$icon_size = "width='21' height='21'";
											$icon_title = "Configure appliaction highavailability";
											$drbdmc_url = "<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('$drbdmc_gui','','location=0,status=0,scrollbars=1,width=1024,height=768,left=50,top=20,screenX=50,screenY=20');\">
												<image border=\"0\" height=\"24\" width=\"24\" alt=\"".$icon_title."\" title=\"".$icon_title."\" src=\"../../img/ha_console.png\">
												</a>";
											$info_actions .= $drbdmc_url;
										}
										// add regular actions
										$info_actions .= "
										   <a href=\"\" onClick=\"runaction('Pause', ".$info_cloud_appliance->id.");\">
										   <input type=\"image\" border=\"0\" height=\"24\" width=\"24\" alt=\"Pause appliance".$info_appliance->name."\" title=\"Pause appliance ".$info_appliance->name."\" src=\"../../img/pause.png\">
										   </a>";
										$info_actions .= "
										   <a href=\"\" onClick=\"runaction('Restart', ".$info_cloud_appliance->id.");\">
										   <input type=\"image\" border=\"0\" height=\"24\" width=\"24\" alt=\"Restart appliance".$info_appliance->name."\" title=\"Restart appliance ".$info_appliance->name."\" src=\"../../img/restart.png\">
										   </a>";
									}
									break;

								case 'off':
									$info_app_state = "Off";
									$info_cloud_icon_image = "../../img/off.png";
									break;

								case 'error':
									$info_app_state = "Error";
									$info_cloud_icon_image = "../../img/error.png";
									break;

								default:
									$info_app_state = "In transition";
									$info_cloud_icon_image = "../../img/in_deployment.gif";
									break;
							}
							break;
						case '0':
							$info_app_state = "Paused";
							$info_cloud_icon_image = "../../img/pause.png";
							$info_actions .= "
							   <a href=\"\" onClick=\"runaction('unPause', ".$info_cloud_appliance->id.");\">
							   <input type=\"image\" border=\"0\" height=\"24\" width=\"24\" alt=\"Unpause appliance".$info_appliance->name."\" title=\"Unpause appliance ".$info_appliance->name."\" src=\"../../img/unpause.png\">
							   </a>";
							break;
					}
					// set object image now with states
					$ic_object = "<img height=\"$cloud_object_icon_size\" width=\"$cloud_object_icon_size\" src=\"$info_cloud_icon_image\">";
					// other infos
					$info_kernel = new kernel();
					$info_kernel->get_instance_by_id($info_appliance->kernelid);
					$info_image = new image();
					$info_image->get_instance_by_id($info_appliance->imageid);
					$info_virtualization = new virtualization();
					$info_virtualization->get_instance_by_id($info_appliance->virtualization);
					// collectd stats ?
					if ($collectd_graph_enabled) {
						$collectd_graph_link="/cloud-portal/user/users/".$info_cloud_user->name."/".$info_appliance->name."/index.html";
						if (file_exists($DocRoot.$collectd_graph_link)) {
							$cloudappliance_action = "<a href=\"$collectd_graph_link\" target=\"_BLANK\">";
							$cloudappliance_action .= "<img src=\"../../img/graphs.png\" border=\"0\" width=\"24\" height=\"24\" alt=\"System Graphs for appliance ".$info_appliance->name."\" title=\"System Graphs for appliance ".$info_appliance->name."\">";
							$cloudappliance_action .= "</a>";
							$info_actions .= $cloudappliance_action;
						}
					}

					$table = new htmlobject_table_builder();
					$arHead = array();

					$arHead['vid_key'] = array();
					$arHead['vid_key']['title'] ='Appliance';

					$arHead['vid_value'] = array();
					$arHead['vid_value']['title'] = $info_appliance->name;

					$arBody = array();

					$arBody[] = array(
						'vid_key' => "<b>Name:</b>",
						'vid_value' => $info_appliance->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Description:</b>",
						'vid_value' => $info_appliance->comment,
					);
					$arBody[] = array(
						'vid_key' => "<b>Kernel:</b>",
						'vid_value' => $info_kernel->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Image:</b>",
						'vid_value' => $info_image->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>Memory:</b>",
						'vid_value' => $info_resource->memtotal." MB",
					);
					$arBody[] = array(
						'vid_key' => "<b>CPU:</b>",
						'vid_value' => $info_resource->cpunumber,
					);

					// add ip address if existing
					$appliance_network_info = $info_appliance->nics;
					if (strlen($appliance_resources_str)) {
						$appliance_network_info .= ' - IP: '.$appliance_resources_str;
					}
					$arBody[] = array(
						'vid_key' => "<b>Network:</b>",
						'vid_value' => $appliance_network_info,
					);
					$arBody[] = array(
						'vid_key' => "<b>Type:</b>",
						'vid_value' => $info_virtualization->name,
					);
					$arBody[] = array(
						'vid_key' => "<b>State:</b>",
						'vid_value' => $info_app_state,
					);
					$arBody[] = array(
						'vid_key' => "<b>Login:</b>",
						'vid_value' => $info_ssh_login,
					);
					$arBody[] = array(
						'vid_key' => "<b>Actions:</b>",
						'vid_value' => $info_actions,
					);

					$table->id = 'InfoTab';
					$table->css = 'htmlobject_table';
					$table->border = 1;
					$table->cellspacing = 0;
					$table->cellpadding = 3;
					$table->form_action = $thisfile;
					$table->sort='';
					$table->head = $arHead;
					$table->body = $arBody;
					$table->max = 100;

					//------------------------------------------------------------ set template
					$t = new Template_PHPLIB();
					$t->debug = false;
					$t->setFile('tplfile', './' . 'vid-info-tpl.php');
					$t->setVar(array(
						'cloud_object_type' => "Appliance",
						'cloud_object' => $info_appliance->name,
						'object_logo' => $ic_object,
						'object_table' => $table->get_string(),
					));

					$disp =  $t->parse('out', 'tplfile');
					echo $disp;
					exit();
					break;

			}
			exit();
			break;


	}
}







function visual_infrastructure_designer() {

	global $thisfile;
	global $auth_user;
	global $RootDir;
	global $event;
	global $matrix_default_icon;
	global $matrix_in_deployment_icon;
	global $cloud_object_icon_size;

	$cl_user = new clouduser();
	$cl_user->get_instance_by_name("$auth_user");
	$cc_conf = new cloudconfig();

	// prepare the profile row
	$cloud_profile_inentory = "";
	$empty_matrix_cells = "";
	$cloud_profile_count = 0;
	$cloud_profile = new cloudprofile();
	$cloud_profile_id_list = $cloud_profile->get_all_ids_per_user($cl_user->id);
	foreach($cloud_profile_id_list as $profile) {
		$profile_id = $profile['pr_id'];
		// check for custom icon
		$cpicon = new cloudicon();
		$cpicon->get_instance_by_details($cl_user->id, 1, $profile_id);
		if (strlen($cpicon->filename)) {
			$cpicon_filename = "../custom-icons/" . $cpicon->filename;
		} else {
			$cpicon_filename = "../../img/resource.png";
		}
		// profile name in the ui
		$cloud_profile_identifier = "p".$profile_id;
		$gcloud_profile = new cloudprofile();
		$gcloud_profile->get_instance_by_id($profile_id);

		$cloud_profile_inentory .= "<td>
			<div id=\"".$cloud_profile_identifier."\" class=\"drag t3 clone\" title=\"Cloud Profile ".$gcloud_profile->name."\">
			<img height=\"$cloud_object_icon_size\" width=\"$cloud_object_icon_size\" src=\"$cpicon_filename\">
			<br><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('vid.php?action=Info&oid=$cloud_profile_identifier','','location=0,status=0,scrollbars=1,width=400,height=450,left=200,top=50,screenX=200,screenY=50');\">#</a></small>
			</div>
			</td>";
		$cloud_profile_count++;
	}

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'openqrm-vid-tpl.php');
	$t->setVar(array(
		'cloud_profile_inentory' => $cloud_profile_inentory,
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}





$output = visual_infrastructure_designer();
echo $output;

?>



