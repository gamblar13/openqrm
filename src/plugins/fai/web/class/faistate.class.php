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


// This class represents a faistate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class faistate {

var $id = '';
var $resource_id = '';
var $install_start = '';
var $timeout = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function faistate() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $OPENQRM_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_db_table = "fai_state";
	$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}





// ---------------------------------------------------------------------------------
// methods to create an instance of a faistate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$faistate_array = &$db->Execute("select * from ".$this->_db_table." where fai_id=".$id);
	} else if ("$resource_id" != "") {
		$faistate_array = &$db->Execute("select * from ".$this->_db_table." where fai_resource_id=".$resource_id);
	} else {
		$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "faistate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($faistate_array as $index => $faistate) {
		$this->id = $faistate["fai_id"];
		$this->resource_id = $faistate["fai_resource_id"];
		$this->install_start = $faistate["fai_install_start"];
		$this->timeout = $faistate["fai_timeout"];
	}
	return $this;
}

// returns an faistate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an faistate from the db selected by the resource_id
function get_instance_by_resource_id($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general faistate methods
// ---------------------------------------------------------------------------------




// checks if given faistate id is free in the db
function is_id_free($faistate_id) {
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select fai_id from ".$this->_db_table." where fai_id=".$faistate_id);
	if (!$rs)
		$this->_event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "faistate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds faistate to the database
function add($faistate_fields) {
	if (!is_array($faistate_fields)) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "faistate.class.php", "faistate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($this->_db_table, $faistate_fields, 'INSERT');
	if (! $result) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "faistate.class.php", "Failed adding new faistate to database", "", "", 0, 0, 0);
	}
}



// removes faistate from the database
function remove($faistate_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->_db_table." where fai_id=".$faistate_id);
}


// removes faistate from the database by resource id
function remove_by_resource_id($faistate_resource_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->_db_table." where fai_resource_id=".$faistate_resource_id);
}



// returns the number of faistates for an faistate type
function get_count() {
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(fai_id) as num from ".$this->_db_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all faistate ids
function get_all_ids() {
	$faistate_list = array();
	$query = "select fai_id from ".$this->_db_table;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$this->_event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "faistate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$faistate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $faistate_list;

}





// ---------------------------------------------------------------------------------

}

?>

