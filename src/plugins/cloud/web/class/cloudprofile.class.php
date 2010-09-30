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


// This class represents a cloud request profile in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";

$CLOUD_PROFILE_TABLE="cloud_profiles";
global $CLOUD_PROFILE_TABLE;
$event = new event();
global $event;


// request status
// 1 = new
// 2 = approved
// 3 = active (provisioned)
// 4 = denied
// 5 = deprovisioned
// 6 = done
// 7 = no resource available

class cloudprofile {

var $id = '';
var $name = '';
var $cu_id = '';
var $status = '';
var $request_time = '';
var $start = '';
var $stop = '';
var $kernel_id = '';
var $image_id = '';
var $ram_req = '';
var $cpu_req = '';
var $disk_req = '';
var $network_req = '';
var $resource_quantity = '';
var $resource_type_req = '';
var $deployment_type_req = '';
var $ha_req = '';
var $shared_req = '';
var $puppet_groups = '';
var $ip_mgmt = '';
var $appliance_id = '';
var $lastbill = '';
var $description = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function cloudprofile() {
    $this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
    global $CLOUD_PROFILE_TABLE, $OPENQRM_SERVER_BASE_DIR;
    $this->_event = new event();
    $this->_db_table = $CLOUD_PROFILE_TABLE;
    $this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}




// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudprofile object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id
function get_instance($id, $name) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	if ("$id" != "") {
		$db=openqrm_get_db_connection();
		$cloudprofile_array = &$db->Execute("select * from $CLOUD_PROFILE_TABLE where pr_id=$id");
    } else if ("$name" != "") {
		$db=openqrm_get_db_connection();
		$cloudprofile_array = &$db->Execute("select * from $CLOUD_PROFILE_TABLE where pr_name='$name'");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", "Could not create instance of cloudprofile without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudprofile_array as $index => $cloudprofile) {
		$this->id = $cloudprofile["pr_id"];
		$this->name = $cloudprofile["pr_name"];
		$this->cu_id = $cloudprofile["pr_cu_id"];
		$this->status = $cloudprofile["pr_status"];
		$this->request_time = $cloudprofile["pr_request_time"];
		$this->start = $cloudprofile["pr_start"];
		$this->stop = $cloudprofile["pr_stop"];
		$this->kernel_id = $cloudprofile["pr_kernel_id"];
		$this->image_id = $cloudprofile["pr_image_id"];
		$this->ram_req = $cloudprofile["pr_ram_req"];
		$this->cpu_req = $cloudprofile["pr_cpu_req"];
		$this->disk_req = $cloudprofile["pr_disk_req"];
		$this->network_req = $cloudprofile["pr_network_req"];
		$this->resource_quantity = $cloudprofile["pr_resource_quantity"];
		$this->resource_type_req = $cloudprofile["pr_resource_type_req"];
		$this->deployment_type_req = $cloudprofile["pr_deployment_type_req"];
		$this->ha_req = $cloudprofile["pr_ha_req"];
		$this->shared_req = $cloudprofile["pr_shared_req"];
		$this->puppet_groups = $cloudprofile["pr_puppet_groups"];
		$this->ip_mgmt = $cloudprofile["pr_ip_mgmt"];
		$this->appliance_id = $cloudprofile["pr_appliance_id"];
		$this->lastbill = $cloudprofile["pr_lastbill"];
		$this->description = $cloudprofile["pr_description"];

	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by name
function get_instance_by_name($name) {
	$this->get_instance("", "$name");
	return $this;
}


// ---------------------------------------------------------------------------------
// general cloudprofile methods
// ---------------------------------------------------------------------------------




// checks if given cloudprofile id is free in the db
function is_id_free($pr_id) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select pr_id from $CLOUD_PROFILE_TABLE where pr_id=$pr_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudprofile to the database
function add($cloudprofile_fields) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	if (!is_array($cloudprofile_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", "coulduser_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set request time to now
	$now=$_SERVER['REQUEST_TIME'];
	$cloudprofile_fields['pr_request_time'] = $now;
	// set status to 1 = new
	$cloudprofile_fields['pr_status'] = 1;
	// set the appliance_id to 0
	$cloudprofile_fields['pr_appliance_id'] = 0;
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_PROFILE_TABLE, $cloudprofile_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", "Failed adding new cloudprofile to database", "", "", 0, 0, 0);
	}
}



// removes cloudprofile from the database
function remove($pr_id) {
	global $CLOUD_PROFILE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_PROFILE_TABLE where pr_id=$pr_id");
}



// updates a cloudprofile
function update($cloudprofile_id, $ci_fields) {
	global $CLOUD_PROFILE_TABLE;
    global $event;
    if ($cloudprofile_id < 0 || ! is_array($ci_fields)) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", "Unable to update Cloudimage $cloudprofile_id", "", "", 0, 0, 0);
        return 1;
    }
    $db=openqrm_get_db_connection();
    unset($ci_fields["pr_id"]);
    $result = $db->AutoExecute($CLOUD_PROFILE_TABLE, $ci_fields, 'UPDATE', "pr_id = $cloudprofile_id");
    if (! $result) {
        $this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", "Failed updating cloudprofile $cloudprofile_id", "", "", 0, 0, 0);
    }
}



// returns the number of cloudprofiles for an cloudprofile type
function get_count() {
	global $CLOUD_PROFILE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(pr_id) as num from $CLOUD_PROFILE_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}


// returns the number of cloudprofiles for an cloudprofile per user
function get_count_per_user($cu_id) {
	global $CLOUD_PROFILE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(pr_id) as num from $CLOUD_PROFILE_TABLE where pr_cu_id=$cu_id");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}





// returns a list of all cloudprofile ids + user ids
function get_list() {
	global $CLOUD_PROFILE_TABLE;
	$query = "select pr_id, pr_cu_id from $CLOUD_PROFILE_TABLE";
	$cloudprofile_name_array = array();
	$cloudprofile_name_array = openqrm_db_get_result_double ($query);
	return $cloudprofile_name_array;
}


// returns a list of all cloudprofile ids
function get_all_ids() {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$cloudprofile_list = array();
	$query = "select pr_id from $CLOUD_PROFILE_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_all_ids", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudprofile_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudprofile_list;

}


// returns a list of all cloudprofile ids per clouduser
function get_all_ids_per_user($cu_id) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$cloudprofile_list = array();
	$query = "select pr_id from $CLOUD_PROFILE_TABLE where pr_cu_id=$cu_id";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_all_ids_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudprofile_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudprofile_list;

}



// returns the cost of a request (in cc_units)
function get_cost() {
	global $event;
	$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudprofile.class.php", "Calulating bill for cr $this->id", "", "", 0, 0, 0);
	$cr_appliance_id = $this->appliance_id;
	$app_id_arr = explode(",", $cr_appliance_id);
	$cr_costs_final = 0;
	foreach ($app_id_arr as $app_id) {
		$cloud_app = new cloudappliance();
		$cloud_app->get_instance_by_appliance_id($app_id);
		// check state, only bill if active
		if ($cloud_app->state == 1) {
			// basic cost
			$cr_costs = 0;
			// + per cpu
			$cr_costs = $cr_costs + $this->cpu_req;
			// + per nic
			$cr_costs = $cr_costs + $this->network_req;
			// ha cost double
			if (!strcmp($this->ha_req, '1')) {
				$cr_costs = $cr_costs * 2;
			}
			// TODO : disk costs
			// TODO : network-traffic costs

			// sum
			$cr_costs_final = $cr_costs_final + $cr_costs;
			$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudprofile.class.php", "-> Billing active appliance $app_id (cr $this->id) = $cr_costs CC-units", "", "", 0, 0, 0);
		} else {
			$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudprofile.class.php", "-> Not billing paused appliance $app_id (cr $this->id)", "", "", 0, 0, 0);
		}
	}
	$event->log("get_costs", $_SERVER['REQUEST_TIME'], 5, "cloudprofile.class.php", "-> Final bill for cr $this->id = $cr_costs_final CC-units", "", "", 0, 0, 0);
	return $cr_costs_final;
}



// set requests lastbill
function set_requests_lastbill($pr_id, $timestamp) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_PROFILE_TABLE set pr_lastbill=$timestamp where pr_id=$pr_id");
}





// function to set the status of a request
function setstatus($pr_id, $cloud_status) {
	global $CLOUD_PROFILE_TABLE;

	switch ($cloud_status) {
		case 'new':
			$cr_status=1;
			break;
		case 'approve':
			$cr_status=2;
			break;
		case 'active':
			$cr_status=3;
			break;
		case 'deny':
			$cr_status=4;
			break;
		case 'deprovision':
			$cr_status=5;
			break;
		case 'done':
			$cr_status=6;
			break;
		case 'no-res':
			$cr_status=7;
			break;
		default:
			exit(1);
			break;
	}
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_PROFILE_TABLE set pr_status=$cr_status where pr_id=$pr_id");

}



// function to set the appliance_id of a request
function setappliance($cmd, $appliance_id) {
	global $CLOUD_PROFILE_TABLE;
	$current_appliance_ids = $this->appliance_id;
	switch ($cmd) {
		case 'add':
			if ($current_appliance_ids == 0) {
				$updated_appliance_ids = "$appliance_id";
			} else {
				$updated_appliance_ids = "$current_appliance_ids,$appliance_id";
			}
			break;
		case 'remove':
			$app_id_arr = explode(",", $current_appliance_ids);
			$loop=1;
			foreach ($app_id_arr as $app_id) {
				if (strcmp($app_id, $appliance_id)) {
					if ($loop == 1) {
						$updated_appliance_ids = $app_id;
					} else {
						$updated_appliance_ids = $updated_appliance_ids.",".$app_id;
					}
				}
			}
			break;
		default:
			exit(1);
			break;
	}
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_PROFILE_TABLE set pr_appliance_id='$updated_appliance_ids' where pr_id=$this->id");

}




// find a cr according to its appliance id
function get_pr_for_appliance($appliance_id) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$cloudprofile_array = &$db->Execute("select pr_id from $CLOUD_PROFILE_TABLE where pr_appliance_id=$appliance_id");
	foreach ($cloudprofile_array as $index => $cloudprofile) {
		return $cloudprofile["pr_id"];
	}
}





// function to re-set stop-time of a request
function extend_stop_time($pr_id, $stop_time) {
	global $CLOUD_PROFILE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("update $CLOUD_PROFILE_TABLE set pr_stop=$stop_time where pr_id=$pr_id");

}



// displays the cloudprofile-overview per user
function display_overview_per_user($cu_id, $order) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
    $db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_PROFILE_TABLE where pr_cu_id=$cu_id order by pr_id $order", -1, 0);
	$cloudprofile_array = array();
	if (!$recordSet) {
		$event->log("display_overview_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudprofile_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudprofile_array;
}



// displays the cloudprofile-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_PROFILE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_PROFILE_TABLE order by $sort $order", $limit, $offset);
	$cloudprofile_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudprofile.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudprofile_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudprofile_array;
}







// ---------------------------------------------------------------------------------

}

?>
