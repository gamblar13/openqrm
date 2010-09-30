
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
$private_id_arr = htmlobject_request('cg_id');


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
                        $private_cg_id = $private_id_arr[$id];
                        if ($private_cg_id == -1) {
                            $private_name = "Hide";
                        } else if ($private_cg_id == 0) {
                            $private_name = "Default Cloud User Group";
                        } else {
                            $pcloudusergroup = new cloudusergroup();
                            $pcloudusergroup->get_instance_by_id($private_cg_id);
                            $private_name = $pcloudusergroup->name;
                        }
                        $strMsg .= "Setting resource $id to $private_name ( $private_cg_id )....<br>";


                        // check if existing, if not create, otherwise update
                        unset($cloud_private_resource);
                        $cloud_private_resource = new cloudrespool();
                        $cloud_private_resource->get_instance_by_resource($id);
                        if (strlen($cloud_private_resource->id)) {
                            if ($private_cg_id == -1) {
                                // remove from table
                                $cloud_private_resource->remove($cloud_private_resource->id);
                            } else {
                                // update
                                $private_cloud_resource_fields["rp_cg_id"] = $private_cg_id;
                                $cloud_private_resource->update($cloud_private_resource->id, $private_cloud_resource_fields);
                                unset($private_cloud_resource_fields);
                            }

                        } else {
                            // create
                            if ($private_cg_id >= 0) {
                                // create array for add
                                $private_cloud_resource_fields["rp_id"]=openqrm_db_get_free_id('rp_id', $cloud_private_resource->_db_table);
                                $private_cloud_resource_fields["rp_resource_id"] = $id;
                                $private_cloud_resource_fields["rp_cg_id"] = $private_cg_id;
                                $cloud_private_resource->add($private_cloud_resource_fields);
                                unset($private_cloud_resource_fields);
                            }
                        }


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

    // private-resource enabled ?
    $cp_conf = new cloudconfig();
    $show_resource_pools = $cp_conf->get_value(25);	// resource_pools enabled ?
    if (strcmp($show_resource_pools, "true")) {
        $strMsg = "<strong>Resource Pooling is not enabled in this Cloud !</strong>";
        return $strMsg;
        exit(0);
    }
	// get external name
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

	$arHead['resource_selector'] = array();
	$arHead['resource_selector']['title'] ='Assign to';
	$arHead['resource_selector']['sortable'] = false;

	$arBody = array();

    // prepare selector array
    $cloud_user_group_sel = new cloudusergroup();
    $cloud_user_group_arr = $cloud_user_group_sel->get_list();
    $cloud_user_group_arr = array_reverse($cloud_user_group_arr);
    $cloud_user_group_arr[] = array('value'=> '-1', 'label'=> 'Hide');
    $cloud_user_group_arr = array_reverse($cloud_user_group_arr);

	// db select
    $resource_count = 0;
	$resource_list = new resource();
	$resource_array = $resource_list->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($resource_array as $index => $im) {
		$resource_id = $im["resource_id"];
        $resource = new resource();
        $resource->get_instance_by_id($resource_id);

        // is a private resource already ?
        $private_resource = new cloudrespool();
        $private_resource->get_instance_by_resource($resource->id);
        if (strlen($private_resource->id)) {
            if ($private_resource->cg_id > 0) {
                $cloud_user = new cloudusergroup();
                $cloud_user->get_instance_by_id($private_resource->cg_id);
                $pi_selected = $cloud_user->id;
            } else if ($private_resource->cg_id == 0) {
                 $pi_selected = 0;
            } else {
                $pi_selected = -1;
            }
        } else {
            $pi_selected = -1;
        }
        $resource_pool_select = htmlobject_select("cg_id[$resource->id]", $cloud_user_group_arr, '', array($pi_selected));

        // prepare resource list
		if ($resource->id == 0) {
			$resource_icon_default="/openqrm/base/img/logo.png";
			$resource_type = "openQRM";
            $resource_mac = "x:x:x:x:x:x";
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
                } else {
                    // vm
    				$resource_type = "<nobr>".$virtualization->name." on Res. ".$resource->vhostid."</nobr>";
                }
			} else {
				$resource_type = "Unknown";
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
			'resource_selector' => $resource_pool_select,
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
	$t->setFile('tplfile', './tpl/' . 'cloud-resource-pools-tpl.php');
	$t->setVar(array(
        'external_portal_name' => $external_portal_name,
		'cloud_private_resource_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Resource Pools', 'value' => cloud_resource_pool_selector());
echo htmlobject_tabmenu($output);

?>