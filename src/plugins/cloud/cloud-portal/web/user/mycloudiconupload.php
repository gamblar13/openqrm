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

$uploaddir = 'custom-icons/';
$object_id = htmlobject_request('object_id');
$object_type = htmlobject_request('object_type');
// 1 = cloud-profile
// 2 = cloud-appliance

// check that we have all parameters
if(!isset($object_id)) {
	echo "ERROR: Object ID not given!";
	exit(1);
}
if(!isset($object_type)) {
	echo "ERROR: Object type not given!";
	exit(1);
}
$auth_user = $_SERVER['PHP_AUTH_USER'];
$pr_user = new clouduser();
$pr_user->get_instance_by_name("$auth_user");
if (!strlen($pr_user->id)) {
	echo "ERROR: User does not exist in this Cloud!";
	exit(1);
}

$window_close_trigger = "";
// actions
if(isset($_POST['upload'])) {
	foreach ($_FILES["pic"]["error"] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES["pic"]["tmp_name"][$key];
			$name = $_FILES["pic"]["name"][$key];
			$short_name = basename($name);
			// check filename extension
			$fextension = substr($short_name, strlen($short_name)-3);
			switch ($fextension) {
				case 'jpg':
					break;
				case 'JPG':
					break;
				case 'png':
					break;
				case 'PNG':
					break;
				case 'gif':
					break;
				case 'GIF':
					break;
				default:
					echo "ERROR: Only jpg/png/gif files allowed for upload!";
					exit(1);
					break;
			}
			// generate unique filename
			$icon_token = md5(uniqid(rand(), true));
			$icon_filename = $icon_token.".".$fextension;
			// copy the uploaded file
			$uploadfile = $uploaddir . $icon_filename;
			if (move_uploaded_file($tmp_name, $uploadfile)) {
				$cloudicon = new cloudicon();
				$cloudicon->get_instance_by_details($pr_user->id, $object_type, $object_id);
				if (strlen($cloudicon->filename)) {
					// remove old file
					unlink($uploaddir.$cloudicon->filename);
					// update cloudicon object
					$cloud_icon_arr = array(
							'ic_filename' => $icon_filename,
					);
					$cloudicon->update($cloudicon->id, $cloud_icon_arr);
				} else {
					// add cloudicon object
					$cloud_icon_id  = openqrm_db_get_free_id('ic_id', $cloudicon->_db_table);
					$cloud_icon_arr = array(
							'ic_id' => $cloud_icon_id,
							'ic_cu_id' => $pr_user->id,
							'ic_type' => $object_type,
							'ic_object_id' => $object_id,
							'ic_filename' => $icon_filename,
					);
					$cloudicon->add($cloud_icon_arr);
				}
				echo "Success: File ".$name." uploaded.<br/>";
				$window_close_trigger = "window.close();";
			} else {
				echo "Error: File ".$name." cannot be uploaded.<br/>";
			}
		}
		// just one file
		break;
	}
}



// prepare the template


switch ($object_type) {
	case '1':
		$object_type_name = "profile ".$object_id;
		break;
	case '2':
		$object_type_name = "appliance ".$object_id;
		break;
}


//------------------------------------------------------------ set template
$t = new Template_PHPLIB();
$t->debug = false;
$t->setFile('tplfile', './' . 'mycloudiconupload-tpl.php');
$t->setVar(array(
	'cloud_object' => $object_type_name,
	'window_close_trigger' => $window_close_trigger,



));

$disp =  $t->parse('out', 'tplfile');
echo $disp;

?>

