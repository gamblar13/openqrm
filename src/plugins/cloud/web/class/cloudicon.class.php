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


// This class represents a cloudicon object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$CLOUD_ICON_TABLE="cloud_icons";
global $CLOUD_ICON_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudicon {

var $id = '';
var $cu_id = '';
var $type = '';
var $object_id = '';
var $filename = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudicon() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_ICON_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_ICON_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudicon object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $cu_id, $type, $object_id) {
	global $CLOUD_ICON_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cloudicon_array = &$db->Execute("select * from $CLOUD_ICON_TABLE where ic_id=$id");
	} else if ("$cu_id" != "") {
		$cloudicon_array = &$db->Execute("select * from $CLOUD_ICON_TABLE where ic_cu_id=$cu_id and ic_type=$type and ic_object_id=$object_id");
	} else if ("$object_id" != "") {
		$cloudicon_array = &$db->Execute("select * from $CLOUD_ICON_TABLE where ic_type=$type and ic_object_id=$object_id");
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", "Could not create instance of cloudicon without data", "", "", 0, 0, 0);
		return;
	}

	foreach ($cloudicon_array as $index => $cloudicon) {
		$this->id = $cloudicon["ic_id"];
		$this->cu_id = $cloudicon["ic_cu_id"];
		$this->type = $cloudicon["ic_type"];
		$this->object_id = $cloudicon["ic_object_id"];
		$this->filename = $cloudicon["ic_filename"];
	}
	return $this;
}

// returns an cloudicon from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "", "", "", "");
	return $this;
}

// returns an cloudicon from the db selected by the cu_id, type and object_id
function get_instance_by_details($cu_id, $type, $object_id) {
	$this->get_instance("", $cu_id, $type, $object_id);
	return $this;
}




// ---------------------------------------------------------------------------------
// general cloudicon methods
// ---------------------------------------------------------------------------------




// checks if given cloudicon id is free in the db
function is_id_free($cloudicon_id) {
	global $CLOUD_ICON_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select ic_id from $CLOUD_ICON_TABLE where ic_id=$cloudicon_id");
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cloudicon to the database
function add($cloudicon_fields) {
	global $CLOUD_ICON_TABLE;
	global $event;
	if (!is_array($cloudicon_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", "cloudicon_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($CLOUD_ICON_TABLE, $cloudicon_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", "Failed adding new cloudicon to database", "", "", 0, 0, 0);
	}
}



// removes cloudicon from the database
function remove($cloudicon_id) {
	global $CLOUD_ICON_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from $CLOUD_ICON_TABLE where ic_id=$cloudicon_id");
}


// updates a cloudicon
function update($ic_id, $ic_fields) {
	global $CLOUD_ICON_TABLE;
	global $event;
	if ($ic_id < 0 || ! is_array($ic_fields)) {
		$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", "Unable to update Cloudimage $ic_id", "", "", 0, 0, 0);
		return 1;
	}
	$db=openqrm_get_db_connection();
	unset($ic_fields["ic_id"]);
	$result = $db->AutoExecute($this->_db_table, $ic_fields, 'UPDATE', "ic_id = $ic_id");
	if (! $result) {
		$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", "Failed updating Cloudimage $ic_id", "", "", 0, 0, 0);
	}
}



// returns the number of cloudicons for an cloudicon type
function get_count() {
	global $CLOUD_ICON_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(ic_id) as num from $CLOUD_ICON_TABLE");
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}



// returns a list of all cloudicon ids
function get_all_ids() {
	global $CLOUD_ICON_TABLE;
	global $event;
	$cloudicon_list = array();
	$query = "select ic_id from $CLOUD_ICON_TABLE";
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cloudicon_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cloudicon_list;

}




// displays the cloudicon-overview
function display_overview($offset, $limit, $sort, $order) {
	global $CLOUD_ICON_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$recordSet = &$db->SelectLimit("select * from $CLOUD_ICON_TABLE order by $sort $order", $limit, $offset);
	$cloudicon_array = array();
	if (!$recordSet) {
		$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudicon.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	} else {
		while (!$recordSet->EOF) {
			array_push($cloudicon_array, $recordSet->fields);
			$recordSet->MoveNext();
		}
		$recordSet->Close();
	}
	return $cloudicon_array;
}









// ---------------------------------------------------------------------------------

}


?>