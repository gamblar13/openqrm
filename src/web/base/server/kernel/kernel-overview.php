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

$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
require_once "$RootDir/class/openqrm_server.class.php";
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;

function redirect($strMsg, $currenttab = 'tab0', $url = '') {
	global $thisfile;
	if($url == '') {
		$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab;
	}
	header("Location: $url");
	exit;
}


if(htmlobject_request('action') != '' && $OPENQRM_USER->role == "administrator") {
	$strMsg = '';

	switch (htmlobject_request('action')) {
		case 'remove':
			$kernel = new kernel();
			if(isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					// check that this is not the default kernel
					if ($id == 1) {
						$strMsg .= "Not removing the default kernel!<br>";
						continue;
					}
					// check that this kernel is not in use any more
					$kernel_is_used_by_appliance = "";
					$remove_error = 0;
					$appliance = new appliance();
					$appliance_id_list = $appliance->get_all_ids();
					foreach($appliance_id_list as $appliance_list) {
						$appliance_id = $appliance_list['appliance_id'];
						$app_kernel_remove_check = new appliance();
						$app_kernel_remove_check->get_instance_by_id($appliance_id);
						if ($app_kernel_remove_check->kernelid == $id) {
							$kernel_is_used_by_appliance .= $appliance_id." ";
							$remove_error = 1;
						}
					}
					if ($remove_error == 1) {
						$strMsg .= "Kernel id ".$id." is used by appliance(s): ".$kernel_is_used_by_appliance." <br>";
						$strMsg .= "Not removing kernel id ".$id." !<br>";
						continue;
					}

					$strMsg .= $kernel->remove($id);
				}
			}
			redirect($strMsg);
			break;

		case 'set-default':
			$kernel = new kernel();
			if(isset($_REQUEST['identifier'])) {
				foreach($_REQUEST['identifier'] as $id) {
					// update default kernel in db
					$kernel->get_instance_by_id($id);
					$ar_kernel_update = array(
						'kernel_name' => "default",
						'kernel_version' => $kernel->version,
						'kernel_capabilities' => $kernel->capabilities,
					);
					$kernel->update(1, $ar_kernel_update);
					// send set-default kernel command to openQRM
					$openqrm_server->send_command("openqrm_server_set_default_kernel $kernel->name");
					$strMsg .= "Set kernel ".$kernel->name." as the default kernel";
					break;
				}
				redirect($strMsg);
			}
			break;

		case 'update':
			$kernel_id = htmlobject_request("kernel_id");
			$kernel_comment = htmlobject_request("kernel_comment");

			// check that this kernel is not in use any more
			$kernel_is_used_by_appliance = "";
			$update_error = 0;
			$appliance = new appliance();
			$appliance_id_list = $appliance->get_all_ids();
			foreach($appliance_id_list as $appliance_list) {
				$appliance_id = $appliance_list['appliance_id'];
				$app_kernel_update_check = new appliance();
				$app_kernel_update_check->get_instance_by_id($appliance_id);
				if (!strcmp($app_kernel_update_check->state, "stopped")) {
					continue;
				}
				if ($app_kernel_update_check->kernelid == $kernel_id) {
					$kernel_is_used_by_appliance .= $appliance_id." ";
					$update_error = 1;
				}
			}
			if ($update_error == 1) {
				$strMsg .= "Kernel id ".$kernel_id." is used by appliance(s): ".$kernel_is_used_by_appliance." <br>";
				$strMsg .= "Not updating kernel id ".$kernel_id." !<br>";
			} else {
				$kernel = new kernel();
				$kernel_fields = array();
				$kernel_fields['kernel_comment'] = $kernel_comment;
				$kernel->update($kernel_id, $kernel_fields);
				$strMsg .= "Updated kernel id ".$kernel_id." <br>";
			}
			redirect($strMsg);
			break;

	}

}




function kernel_display() {
	global $OPENQRM_USER;
	global $thisfile;

	$kernel_tmp = new kernel();
	$table = new htmlobject_db_table('kernel_id');

	$disp = '<h1>Kernel List</h1>';
	$disp .= '<br>';

	$arHead = array();
	$arHead['kernel_icon'] = array();
	$arHead['kernel_icon']['title'] ='';

	$arHead['kernel_id'] = array();
	$arHead['kernel_id']['title'] ='ID';

	$arHead['kernel_name'] = array();
	$arHead['kernel_name']['title'] ='Name';

	$arHead['kernel_version'] = array();
	$arHead['kernel_version']['title'] ='Version';

	$arHead['kernel_comment'] = array();
	$arHead['kernel_comment']['title'] ='Comment';

	$arBody = array();
	$kernel_array = $kernel_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

	$kernel_icon = "/openqrm/base/img/kernel.png";
	foreach ($kernel_array as $index => $kernel_db) {
		$kernel = new kernel();
		$kernel->get_instance_by_id($kernel_db["kernel_id"]);
		$arBody[] = array(
			'kernel_icon' => "<img width=20 height=20 src=$kernel_icon>",
			'kernel_id' => $kernel_db["kernel_id"],
			'kernel_name' => $kernel_db["kernel_name"],
			'kernel_version' => $kernel_db["kernel_version"],
			'kernel_comment' => $kernel_db["kernel_comment"],
		);

	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	$table->identifier_disabled = array(1);
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('edit', 'set-default', 'remove');
		$table->identifier = 'kernel_id';
	}
	$table->limit_select = array(
		array("value" => 10, "text" => 10),
		array("value" => 20, "text" => 20),
		array("value" => 30, "text" => 30),
		array("value" => 50, "text" => 50),
		array("value" => 100, "text" => 100),
	);
	$kernel_max = $kernel_tmp->get_count();
	$table->max = $kernel_max - 1;
	#$table->limit = 10;

	return $disp.$table->get_string();
}


function kernel_form() {

	$disp = "<h1>New Kernel</h1>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>New kernels should be added on the openqrm server with the following command:</b><br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>/usr/share/openqrm/bin/openqrm kernel add -n name -v version -u username -p password [-l location] [-i initramfs/ext2] [-t path-to-initrd-template-file]<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>name</b> can be any identifier as long as it has no spaces or other special characters; it is used as part of the filename.<br>";
	$disp = $disp."<b>version</b> should be the version for the kernel you want to install. If the filenames are called vmlinuz-2.6.26-2-amd64 then 2.6.26-2-amd64 is the version of this kernel.<br>";
	$disp = $disp."<b>username</b> and <b>password</b> are the credentials to openqrm itself.<br>";
	$disp = $disp."<b>location</b> is the root directory for the kernel you want to install. The files that are used are \${location}/boot/vmlinuz-\${version}, \${location}/boot/initrd.img-\${version} and \${location}/lib/modules/\${version}/*<br>";
	$disp = $disp."<b>initramfs/ext2</b> should specify the type of initrd image you want to generate. Most people want to use <b>initramfs</b> here.<br>";
	$disp = $disp."<b>path-to-initrd-template-file</b> should point to an openqrm initrd template. These can be found in the openqrm base dir under etc/templates.<br>";
	$disp = $disp."<br>";
	$disp = $disp."Example:<br>";
	$disp = $disp."/usr/share/openqrm/bin/openqrm kernel add -n openqrm-kernel-1 -v 2.6.29 -u openqrm -p openqrm -i initramfs -l / -t /usr/share/openqrm/etc/templates/openqrm-initrd-template.debian.x86_64.tgz<br>";
	$disp = $disp."<br>";
	return $disp;
}


function kernel_edit($kernel_id) {
	global $thisfile;
	if (!strlen($kernel_id))  {
		echo "No Kernel selected!";
		exit(0);
	}

	$kernel = new kernel();
	$kernel->get_instance_by_id($kernel_id);

	$disp = "<h1>Edit Kernel</h1>";
	$disp = $disp."<form action='".$thisfile."' method=post>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>".$kernel->name."</b><br>";
	$disp = $disp.htmlobject_input('kernel_comment', array("value" => $kernel->comment, "label" => 'Comment'), 'text', 100);
	//	$disp = $disp.htmlobject_input('kernel_version', array("value" => $kernel->version, "label" => ' Kernel version'), 'text', 20);
	$disp = $disp."<input type=hidden name=kernel_id value=$kernel_id>";
	$disp = $disp."<input type=hidden name=action value='update'>";
	$disp = $disp."<input type=submit value='Update'>";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."";
	$disp = $disp."</form>";
	return $disp;
}





$output = array();
if($OPENQRM_USER->role == "administrator") {
	if(htmlobject_request('action') != '') {
		if(isset($_REQUEST['identifier'])) {
			switch (htmlobject_request('action')) {
				case 'edit':
					foreach($_REQUEST['identifier'] as $id) {
						$output[] = array('label' => 'Edit Kernel', 'value' => kernel_edit($id));
						break;
					}
					break;
			}
		} else {
			$output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display());
			$output[] = array('label' => 'New', 'value' => kernel_form());
		}
	} else {
		$output[] = array('label' => 'Kernel-Admin', 'value' => kernel_display());
		$output[] = array('label' => 'New', 'value' => kernel_form());
	}
}


?>
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="kernel.css" />

<?php
echo htmlobject_tabmenu($output);
?>
