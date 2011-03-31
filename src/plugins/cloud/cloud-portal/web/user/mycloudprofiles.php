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
$DocRoot = $_SERVER["DOCUMENT_ROOT"];
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
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
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudprofile.class.php";
require_once "$RootDir/plugins/cloud/class/cloudicon.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $CLOUD_PROFILE_TABLE;
$cloud_object_icon_size=48;
global $cloud_object_icon_size;

// who are you ?
$auth_user = $_SERVER['PHP_AUTH_USER'];
global $auth_user;

function redirect2profile($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	} else {
		$url = $url.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}



function check_allowed_input_for_profile($text) {
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

$cp_conf = new cloudconfig();
// check if we got some actions to do
if (htmlobject_request('action') != '') {
	switch (htmlobject_request('action')) {

		case 'delete':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$cprofile = new cloudprofile();
					$cprofile->get_instance_by_id($id);
					$cp_user = new clouduser();
					$cp_user->get_instance_by_name("$auth_user");
					if ($cp_user->id != $cprofile->cu_id) {
						$strMsg = "Cloudprofile $id is not owned by $auth_user  $cp_user->id  ! Skipping ... <br>";
						redirect2profile($strMsg, 'tab2', "mycloud.php");
						exit(0);
					}
					// remove custom icon if exists
					$remove_cloud_icon = new cloudicon();
					$remove_cloud_icon->get_instance_by_details($cp_user->id, 1, $id);
					if (strlen($remove_cloud_icon->filename)) {
						$rcloud_icon = "custom-icons/" . $remove_cloud_icon->filename;
						unlink($rcloud_icon);
						// remove icon object
						$remove_cloud_icon->remove($remove_cloud_icon->id);
					}
					// remove logic cloudprofile
					$cprofile->remove($id);
					$strMsg .= "Removed Cloud profile $id.<br>";

				}
			}
			redirect2profile($strMsg, 'tab2', "mycloud.php");
			break;


		case 'description':
			if (isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					$cprofile = new cloudprofile();
					$cprofile->get_instance_by_id($id);
					$cp_user = new clouduser();
					$cp_user->get_instance_by_name("$auth_user");
					if ($cp_user->id != $cprofile->cu_id) {
						$strMsg = "Cloudprofile $id is not owned by $auth_user  $cp_user->id  ! Skipping ... <br>";
						redirect2profile($strMsg, 'tab2', "mycloud.php");
						exit(0);
					}
					$updated_profile_comment_arr = htmlobject_request('pr_description');
					$updated_profile_comment = $updated_profile_comment_arr["$id"];
					$updated_profile_comment_check = trim($updated_profile_comment);
					// remove any non-violent characters
					$updated_profile_comment_check = str_replace(" ", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace(".", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace(",", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace("-", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace("_", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace("(", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace(")", "", $updated_profile_comment_check);
					$updated_profile_comment_check = str_replace("/", "", $updated_profile_comment_check);
					if(!check_allowed_input_for_profile($updated_profile_comment_check)){
						$strMsg = "Comment contains special characters, skipping update <br>";
						redirect2profile($strMsg, 'tab2', "mycloud.php");
						exit(0);
					}
					$cloud_pimage = new cloudprofile();
					$ar_request = array(
						'pr_description' => "$updated_profile_comment",
					);
					$cloud_pimage->update($id, $ar_request);
					$strMsg .= "Updated description of Cloudprofile $id<br>";

				}
			}
			redirect2profile($strMsg, 'tab2', "mycloud.php");
			break;

// ######################## end of cloud-image actions #####################



	}
}






function mycloud_profiles() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $thisfile;
	global $auth_user;
	global $cloud_object_icon_size;

	$table = new htmlobject_table_builder('pr_id', 'ASC', '', '', 'profiles');

	$arHead = array();

	$arHead['pr_icon'] = array();
	$arHead['pr_icon']['title'] ='Icon';
	$arHead['pr_icon']['sortable'] = false;

	$arHead['pr_id'] = array();
	$arHead['pr_id']['title'] ='ID';

	$arHead['pr_name'] = array();
	$arHead['pr_name']['title'] ='Name';

	$arHead['pr_upload_icon'] = array();
	$arHead['pr_upload_icon']['title'] ='Action';
	$arHead['pr_upload_icon']['sortable'] = false;

	$arHead['pr_description'] = array();
	$arHead['pr_description']['title'] ='Description';

	$arBody = array();
	$profile_count = 0;
	$default_icon="../img/resource.png";

	$cl_user = new clouduser();
	$cl_user->get_instance_by_name("$auth_user");
	$cloudprofile = new cloudprofile();
	$cloudprofile_array = $cloudprofile->display_overview_per_user($cl_user->id, $table->order);
	foreach ($cloudprofile_array as $index => $cloudprofile_db) {
		$pr_id = $cloudprofile_db["pr_id"];
		$pr_name = $cloudprofile_db["pr_name"];
		$pr_description = $cloudprofile_db["pr_description"];
		// check if custom icon exist, otherwise use the default icon
		$custom_cloud_icon = new cloudicon();
		$custom_cloud_icon->get_instance_by_details($cl_user->id, 1, $pr_id);
		if (strlen($custom_cloud_icon->filename)) {
			$cloud_icon = "/cloud-portal/user/custom-icons/" . $custom_cloud_icon->filename;
		} else {
			$cloud_icon = $default_icon;
		}
		// file upload action
		$profile_icon_upload = "<a href=\"#\" onClick=\"javascript:window.open('mycloudiconupload.php?object_type=1&object_id=$pr_id','','location=0,status=0,scrollbars=1,width=390,height=170,left=200,top=150,screenX=200,screenY=150');\">Upload custom icon</a>";
		$arBody[] = array(
			'pr_icon' => "<img width=\"".$cloud_object_icon_size."\" height=\"".$cloud_object_icon_size."\" src=\"".$cloud_icon."\"><input type=hidden name=\"currenttab\" value=\"tab2\">",
			'pr_id' => $pr_id,
			'pr_name' => $pr_name,
			'pr_upload_icon' => $profile_icon_upload,
			'pr_description' => "<input type=text name=\"pr_description[$pr_id]\" value=\"$pr_description\">",
		);
		$profile_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->autosort = true;
	#$table->sort = "";
	$table->bottom = array('delete', 'description');
	$table->identifier = 'pr_id';
	$table->max = $profile_count;


	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './' . 'mycloudprofiles-tpl.php');
	$t->setVar(array(
		'thisfile' => $thisfile,
		'currentab' => htmlobject_input('currenttab', array("value" => 'tab2', "label" => ''), 'hidden'),
		'cloud_profile_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;
}



?>
