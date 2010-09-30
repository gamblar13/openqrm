
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
require_once "$RootDir/plugins/cloud/class/cloudrespool.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $OPENQRM_WEB_PROTOCOL;
// get the cu_id array
$ps_app_id_arr = htmlobject_request('ps_id');


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
                        $ps_appliance = new appliance();
                        $ps_appliance->get_instance_by_id($id);
                        // is this the openQRM Server itself ?
                        if ($ps_appliance->resources == 0) {
                            continue;
                        }
                        $ps_resource = new resource();
                        $ps_resource->get_instance_by_id($ps_appliance->resources);
                        $ps_state = $ps_app_id_arr[$ps_appliance->id];
                        $ps_resource->set_resource_capabilities('CPS', $ps_state);
                        $strMsg .= "Setting appliance $id to power-save mode $ps_state ....<br>";
                    }
                    redirect_private($strMsg, 'tab0');
                }
                break;



        }
    }
}


function cloud_resource_pool_selector() {

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

	$table = new htmlobject_table_identifiers_checked('appliance_id');
	$arHead = array();

	$arHead['appliance_icon'] = array();
	$arHead['appliance_icon']['title'] ='';

    $arHead['appliance_id'] = array();
	$arHead['appliance_id']['title'] ='ID';

	$arHead['appliance_name'] = array();
	$arHead['appliance_name']['title'] ='Name';

	$arHead['appliance_resources'] = array();
	$arHead['appliance_resources']['title'] ='Resource';

	$arHead['appliance_virtualization'] = array();
	$arHead['appliance_virtualization']['title'] ='Type';

	$arHead['appliance_selector'] = array();
	$arHead['appliance_selector']['title'] ='Action';

	$arBody = array();

    // prepare selector array
    $cloud_power_save_selector_arr[] = array('value'=> '1', 'label'=> 'Enabled');
    $cloud_power_save_selector_arr[] = array('value'=> '0', 'label'=> 'Disabled');

	// db select
    $appliance_count = 0;
	$appliance_list = new appliance();
	$appliance_array = $appliance_list->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($appliance_array as $index => $ps_app) {
        unset($cloud_power_save_select);
		$appliance_id = $ps_app["appliance_id"];
        $appliance = new appliance();
        $appliance->get_instance_by_id($appliance_id);
        $resource = new resource();
        $resource->get_instance_by_id($appliance->resources);

        // prepare resource list
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
			$resource_type = "openQRM";
            $resource_mac = "x:x:x:x:x:x";
		} else {
            $resource_mac = $resource->mac;
			$resource_icon_default="/openqrm/base/img/resource.png";
            // the appliance virtualization type
            $virtualization = new virtualization();
            $virtualization->get_instance_by_id($appliance->virtualization);
            if (strstr($virtualization->name, "Host")) {
                // check if power-saving is already set
                $power_saving_parameter = $resource->get_resource_capabilities('CPS');
                if ($power_saving_parameter == 1) {
                    $cloud_power_save_select = htmlobject_select("ps_id[$appliance->id]", $cloud_power_save_selector_arr, '', array(1));
                } else {
                    $cloud_power_save_select = htmlobject_select("ps_id[$appliance->id]", $cloud_power_save_selector_arr, '', array(0));
                }
            }
            $resource_type = "<nobr>".$virtualization->name."</nobr>";

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
			'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
			'appliance_id' => $appliance->id,
			'appliance_name' => $appliance->name,
			'appliance_resources' => $appliance->resources."/".$resource->ip,
			'appliance_virtualization' => $resource_type,
			'appliance_selector' => $cloud_power_save_select,
		);
        $appliance_count++;
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
		$table->identifier = 'appliance_id';
	}
    // do not show the openQRM server and idle resource
    $appliance_max = $appliance_list->get_count();
	$table->max = $appliance_max-1;

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-power-saver-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
		'cloud_power_saver_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Power Saver', 'value' => cloud_resource_pool_selector());
echo htmlobject_tabmenu($output);

?>