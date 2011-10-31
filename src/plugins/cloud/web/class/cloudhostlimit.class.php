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


// This class represents a cloud hostlimit in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_HOSTLIMIT_TABLE="cloud_hostlimit";
global $CLOUD_HOSTLIMIT_TABLE;
$event = new event();
global $event;

class cloudhostlimit {

var $id = '';
var $resource_id = '';
var $max_vms = '';
var $current_vms = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudhostlimit() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_HOSTLIMIT_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_HOSTLIMIT_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}




// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudhostlimit object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	global $CLOUD_HOSTLIMIT_TABLE;
	$CLOUD_HOSTLIMIT_TABLE="cloud_hostlimit";
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudhostlimit_array = &$db->Execute("select * from $CLOUD_HOSTLIMIT_TABLE where hl_id=$id");
	} else if ("$resource_id" != "") {
		$cloudhostlimit_array = &$db->Execute("select * from $CLOUD_HOSTLIMIT_TABLE where hl_resource_id=$resource_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudhostlimit_array as $index => $cloudhostlimit) {
		$this->id = $cloudhostlimit["hl_id"];
		$this->resource_id = $cloudhostlimit["hl_resource_id"];
		$this->current_vms = $cloudhostlimit["hl_current_vms"];
		$this->max_vms = $cloudhostlimit["hl_max_vms"];
	}
	return $this;
}

// returns an appliance from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an appliance from the db selected by iname
function get_instance_by_resource($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}



// ---------------------------------------------------------------------------------
// general cloudhostlimit methods
// ---------------------------------------------------------------------------------




// checks if given cloudhostlimit id is free in the db
function is_id_free($cloudhostlimit_id) {
	global $CLOUD_HOSTLIMIT_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select hl_id from $CLOUD_HOSTLIMIT_TABLE where hl_id=$cloudhostlimit_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}



// adds cloudhostlimit to the database
function add($cloudhostlimit_fields) {
	global $CLOUD_HOSTLIMIT_TABLE;
	global $event;
	if (!is_array($cloudhostlimit_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", "cloudhostlimit_fields not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_HOSTLIMIT_TABLE, $cloudhostlimit_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", "Failed adding new cloudhostlimit to database", "", "", 0, 0, 0);
	}
}


// updates cloudhostlimit in the database
function update($cloudhostlimit_id, $cloudhostlimit_fields) {
	global $CLOUD_HOSTLIMIT_TABLE;
	global $event;
	if (!is_array($cloudhostlimit_fields)) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", "Unable to update cloudhostlimit $cloudhostlimit_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($cloudhostlimit_fields["hl_id"]);
	$result = $db->AutoExecute($CLOUD_HOSTLIMIT_TABLE, $cloudhostlimit_fields, 'UPDATE', "hl_id = $cloudhostlimit_id");
	if (! $result) {
		$event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", "Failed updating cloudhostlimit $cloudhostlimit_id", "", "", 0, 0, 0);
	}
}


// removes cloudhostlimit from the database
function remove($cloudhostlimit_id) {
	global $CLOUD_HOSTLIMIT_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_HOSTLIMIT_TABLE where hl_id=$cloudhostlimit_id");
}




// returns the number of cloudhostlimit for an cloudhostlimit type
function get_count() {
	global $CLOUD_HOSTLIMIT_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(hl_id) as num from $CLOUD_HOSTLIMIT_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudhostlimit ids
function get_all_ids() {
	global $CLOUD_HOSTLIMIT_TABLE;
	global $event;
	$cloudhostlimit_list = array();
	$query = "select hl_id from $CLOUD_HOSTLIMIT_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudhostlimit_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudhostlimit_list;

}





// displays the cloudhostlimit-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_HOSTLIMIT_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_HOSTLIMIT_TABLE order by $sort $order", $limit, $offset);
	$cloudhostlimit_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudhostlimit.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudhostlimit_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudhostlimit_array;
}









// ---------------------------------------------------------------------------------

}

?>