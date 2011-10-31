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


// This class represents a localstoragestate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$LOCAL_STORAGE_STATE_TABLE="local_storage_state";
global $LOCAL_STORAGE_STATE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class localstoragestate {

var $id = '';
var $resource_id = '';
var $install_start = '';
var $timeout = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function localstoragestate() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $LOCAL_STORAGE_STATE_TABLE, $OPENQRM_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_db_table = $LOCAL_STORAGE_STATE_TABLE;
	$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}





// ---------------------------------------------------------------------------------
// methods to create an instance of a localstoragestate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$localstoragestate_array = &$db->Execute("select * from ".$LOCAL_STORAGE_STATE_TABLE." where local_storage_id=".$id);
	} else if ("$resource_id" != "") {
		$localstoragestate_array = &$db->Execute("select * from ".$LOCAL_STORAGE_STATE_TABLE." where local_storage_resource_id=".$resource_id);
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($localstoragestate_array as $index => $localstoragestate) {
		$this->id = $localstoragestate["local_storage_id"];
		$this->resource_id = $localstoragestate["local_storage_resource_id"];
		$this->install_start = $localstoragestate["local_storage_install_start"];
		$this->timeout = $localstoragestate["local_storage_timeout"];
	}
	return $this;
}

// returns an localstoragestate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an localstoragestate from the db selected by the resource_id
function get_instance_by_resource_id($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general localstoragestate methods
// ---------------------------------------------------------------------------------




// checks if given localstoragestate id is free in the db
function is_id_free($localstoragestate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select local_storage_id from ".$LOCAL_STORAGE_STATE_TABLE." where local_storage_id=".$localstoragestate_id);
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds localstoragestate to the database
function add($localstoragestate_fields) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	if (!is_array($localstoragestate_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "localstoragestate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($LOCAL_STORAGE_STATE_TABLE, $localstoragestate_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", "Failed adding new localstoragestate to database", "", "", 0, 0, 0);
	}
}



// removes localstoragestate from the database
function remove($localstoragestate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$LOCAL_STORAGE_STATE_TABLE." where local_storage_id=".$localstoragestate_id);
}


// removes localstoragestate from the database by resource id
function remove_by_resource_id($localstoragestate_resource_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$LOCAL_STORAGE_STATE_TABLE." where local_storage_resource_id=".$localstoragestate_resource_id);
}



// returns the number of localstoragestates for an localstoragestate type
function get_count() {
	global $LOCAL_STORAGE_STATE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(local_storage_id) as num from ".$LOCAL_STORAGE_STATE_TABLE);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all localstoragestate ids
function get_all_ids() {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$localstoragestate_list = array();
	$query = "select local_storage_id from ".$LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "localstoragestate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$localstoragestate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $localstoragestate_list;

}





// ---------------------------------------------------------------------------------

}

?>

