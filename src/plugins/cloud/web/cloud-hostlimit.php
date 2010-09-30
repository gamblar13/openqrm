
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

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// error_reporting(E_ALL);
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
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
require_once "$RootDir/plugins/cloud/class/cloudhostlimit.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;
// get the cu_id array
$hl_id_arr = htmlobject_request('hl_id');


function redirect_private($strMsg, $currenttab = 'tab0') {
	global $thisfile;
    $url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab."&redirect=yes";
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
if (htmlobject_request('redirect') != 'yes') {
    if(htmlobject_request('action') != '') {
        switch (htmlobject_request('action')) {
            case 'set':
                if (isset($_REQUEST['identifier'])) {
                    foreach($_REQUEST['identifier'] as $id) {
                        $presource = new resource();
                        $presource->get_instance_by_id($id);
                        $resource_hostlimit = $hl_id_arr[$id];
                        if (strlen($resource_hostlimit)) {
                            $strMsg .= "Setting resource $id hostlimit to $resource_hostlimit VMs ...<br>";
                            unset($set_hostlimit);
                            $set_hostlimit = new cloudhostlimit();
                            $set_hostlimit->get_instance_by_resource($id);
                            unset($cloud_hostlimit_fields);
                            if (strlen($set_hostlimit->id)) {
                                // update
                                $cloud_hostlimit_fields["hl_max_vms"] = $resource_hostlimit;
                                $set_hostlimit->update($set_hostlimit->id, $cloud_hostlimit_fields);
                            } else {
                                // add
                                $cloud_hostlimit_fields["hl_id"]=openqrm_db_get_free_id('hl_id', $set_hostlimit->_db_table);
                                $cloud_hostlimit_fields["hl_resource_id"] = $id;
                                $cloud_hostlimit_fields["hl_max_vms"] = $resource_hostlimit;
                                $cloud_hostlimit_fields["hl_current_vms"] = 0;
                                $set_hostlimit->add($cloud_hostlimit_fields);
                                unset($cloud_hostlimit_fields);
                            }
                        }
                    }
                    redirect_private($strMsg, 'tab0');
                }
                break;
        }
    }
}


function cloud_hostlimit_manager() {

	global $OPENQRM_USER;
	global $OPENQRM_SERVER_IP_ADDRESS;
    global $OPENQRM_WEB_PROTOCOL;
	global $thisfile;

	// get external name
    $cp_conf = new cloudconfig();
	$external_portal_name = $cp_conf->get_value(3);  // 3 is the external name
	if (!strlen($external_portal_name)) {
		$external_portal_name = "$OPENQRM_WEB_PROTOCOL://$OPENQRM_SERVER_IP_ADDRESS/cloud-portal";
	}

	$table = new htmlobject_table_identifiers_checked('resource_id');
	$arHead = array();

	$arHead['resource_icon'] = array();
	$arHead['resource_icon']['title'] ='';

    $arHead['resource_id'] = array();
	$arHead['resource_id']['title'] ='ID';

	$arHead['resource_name'] = array();
	$arHead['resource_name']['title'] ='Name';

	$arHead['resource_mac'] = array();
	$arHead['resource_mac']['title'] ='Mac';

	$arHead['resource_ip'] = array();
	$arHead['resource_ip']['title'] ='Ip';

	$arHead['resource_type'] = array();
	$arHead['resource_type']['title'] ='Type';

	$arHead['resource_max_vms'] = array();
	$arHead['resource_max_vms']['title'] ='Max-VMs';

	$arBody = array();

    // prepare selector array
    $cloud_hostlimit_arr[] = array('value'=> '-1', 'label'=> 'no limit');
    $cloud_hostlimit_arr[] = array('value'=> '0', 'label'=> 'no VM');
    $cloud_hostlimit_arr[] = array('value'=> '1', 'label'=> '1 VM');
    for ($i=2; $i<=100; $i++) {
        $cloud_hostlimit_arr[] = array('value'=> "$i", 'label'=> "$i VMs");
    }

	// db select
    $resource_count = 0;
	$resource_list = new resource();
	$resource_array = $resource_list->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($resource_array as $index => $im) {
		$resource_id = $im["resource_id"];
        $resource = new resource();
        $resource->get_instance_by_id($resource_id);
        // prepare pre-select
        $hostlimit = new cloudhostlimit();
        $hostlimit->get_instance_by_resource($resource_id);
        if (strlen($hostlimit->max_vms)) {
            $host_preselected = $hostlimit->max_vms;
        } else {
            $host_preselected = -1;
        }
        // prepare resource list
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
			$resource_type = "openQRM";
            $resource_mac = "x:x:x:x:x:x";
            $resource_hostlimit_select = htmlobject_select("hl_id[$resource->id]", $cloud_hostlimit_arr, '', array($host_preselected));
		} else {
            $resource_mac = $resource->mac;
			$resource_icon_default="/openqrm/base/img/resource.png";

            // the resource_type
			if ((strlen($resource->vtype)) && (!strstr($resource->vtype, "NULL"))){
				// find out what should be preselected
            	$virtualization = new virtualization();
				$virtualization->get_instance_by_id($resource->vtype);
                if ($resource->id == $resource->vhostid) {
                    // physical system
    				$resource_type = "<nobr>".$virtualization->name."</nobr>";
                    $resource_hostlimit_select = htmlobject_select("hl_id[$resource->id]", $cloud_hostlimit_arr, '', array($host_preselected));

                } else {
                    // vm
    				$resource_type = "<nobr>".$virtualization->name." on Res. ".$resource->vhostid."</nobr>";
                    $resource_hostlimit_select = "";
                }
			} else {
				$resource_type = "Unknown";
                $resource_hostlimit_select = "";
			}

		}
		$state_icon="/openqrm/base/img/$resource->state.png";
		// idle ?
		if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
			$state_icon="/openqrm/base/img/idle.png";
		}
		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
			$state_icon="/openqrm/base/img/unknown.png";
		}



		$arBody[] = array(
			'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'resource_id' => $resource->id,
			'resource_name' => $resource->hostname,
			'resource_mac' => $resource_mac,
			'resource_ip' => $resource->ip,
			'resource_type' => $resource_type,
			'resource_max_vms' => $resource_hostlimit_select,
		);
        $resource_count++;
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
		$table->identifier = 'resource_id';
	}
    // do not show the openQRM server and idle resource
    $resource_max = $resource_list->get_count("all");
	$table->max = $resource_max-1;

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-hostlimit-manager-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
		'cloud_max_vms_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Host Limits', 'value' => cloud_hostlimit_manager());
echo htmlobject_tabmenu($output);

?>