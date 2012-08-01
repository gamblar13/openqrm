<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


// This class represents a cobblerstate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$LOCAL_STORAGE_STATE_TABLE="cobbler_state";
global $LOCAL_STORAGE_STATE_TABLE;
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cobblerstate {

var $id = '';
var $resource_id = '';
var $install_start = '';
var $timeout = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function cobblerstate() {
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
// methods to create an instance of a cobblerstate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$cobblerstate_array = &$db->Execute("select * from ".$LOCAL_STORAGE_STATE_TABLE." where cobbler_id=".$id);
	} else if ("$resource_id" != "") {
		$cobblerstate_array = &$db->Execute("select * from ".$LOCAL_STORAGE_STATE_TABLE." where cobbler_resource_id=".$resource_id);
	} else {
		$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cobblerstate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($cobblerstate_array as $index => $cobblerstate) {
		$this->id = $cobblerstate["cobbler_id"];
		$this->resource_id = $cobblerstate["cobbler_resource_id"];
		$this->install_start = $cobblerstate["cobbler_install_start"];
		$this->timeout = $cobblerstate["cobbler_timeout"];
	}
	return $this;
}

// returns an cobblerstate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an cobblerstate from the db selected by the resource_id
function get_instance_by_resource_id($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general cobblerstate methods
// ---------------------------------------------------------------------------------




// checks if given cobblerstate id is free in the db
function is_id_free($cobblerstate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select cobbler_id from ".$LOCAL_STORAGE_STATE_TABLE." where cobbler_id=".$cobblerstate_id);
	if (!$rs)
		$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cobblerstate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds cobblerstate to the database
function add($cobblerstate_fields) {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	if (!is_array($cobblerstate_fields)) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobblerstate.class.php", "cobblerstate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($LOCAL_STORAGE_STATE_TABLE, $cobblerstate_fields, 'INSERT');
	if (! $result) {
		$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobblerstate.class.php", "Failed adding new cobblerstate to database", "", "", 0, 0, 0);
	}
}



// removes cobblerstate from the database
function remove($cobblerstate_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$LOCAL_STORAGE_STATE_TABLE." where cobbler_id=".$cobblerstate_id);
}


// removes cobblerstate from the database by resource id
function remove_by_resource_id($cobblerstate_resource_id) {
	global $LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$LOCAL_STORAGE_STATE_TABLE." where cobbler_resource_id=".$cobblerstate_resource_id);
}



// returns the number of cobblerstates for an cobblerstate type
function get_count() {
	global $LOCAL_STORAGE_STATE_TABLE;
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(cobbler_id) as num from ".$LOCAL_STORAGE_STATE_TABLE);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all cobblerstate ids
function get_all_ids() {
	global $LOCAL_STORAGE_STATE_TABLE;
	global $event;
	$cobblerstate_list = array();
	$query = "select cobbler_id from ".$LOCAL_STORAGE_STATE_TABLE;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cobblerstate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$cobblerstate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $cobblerstate_list;

}





// ---------------------------------------------------------------------------------

}

?>

