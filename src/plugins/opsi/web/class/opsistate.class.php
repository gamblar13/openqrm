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


// This class represents a opsistate object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

$LOCAL_STORAGE_STATE_TABLE="opsi_state";
global $LOCAL_STORAGE_STATE_TABLE;

class opsistate {

var $id = '';
var $resource_id = '';
var $install_start = '';
var $timeout = '';

//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function opsistate() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	$this->_event = new event();
	$this->_db_table = "opsi_state";
}





// ---------------------------------------------------------------------------------
// methods to create an instance of a opsistate object filled from the db
// ---------------------------------------------------------------------------------

// returns an appliance from the db selected by id or name
function get_instance($id, $resource_id) {
	$db=openqrm_get_db_connection();
	if ("$id" != "") {
		$opsistate_array = &$db->Execute("select * from ".$this->_db_table." where opsi_id=".$id);
	} else if ("$resource_id" != "") {
		$opsistate_array = &$db->Execute("select * from ".$this->_db_table." where opsi_resource_id=".$resource_id);
	} else {
		$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "opsistate.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
		return;
	}
	foreach ($opsistate_array as $index => $opsistate) {
		$this->id = $opsistate["opsi_id"];
		$this->resource_id = $opsistate["opsi_resource_id"];
		$this->install_start = $opsistate["opsi_install_start"];
		$this->timeout = $opsistate["opsi_timeout"];
	}
	return $this;
}

// returns an opsistate from the db selected by id
function get_instance_by_id($id) {
	$this->get_instance($id, "");
	return $this;
}

// returns an opsistate from the db selected by the resource_id
function get_instance_by_resource_id($resource_id) {
	$this->get_instance("", $resource_id);
	return $this;
}


// ---------------------------------------------------------------------------------
// general opsistate methods
// ---------------------------------------------------------------------------------




// checks if given opsistate id is free in the db
function is_id_free($opsistate_id) {
	$db=openqrm_get_db_connection();
	$rs = &$db->Execute("select opsi_id from ".$this->_db_table." where opsi_id=".$opsistate_id);
	if (!$rs)
		$this->_event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "opsistate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	if ($rs->EOF) {
		return true;
	} else {
		return false;
	}
}


// adds opsistate to the database
function add($opsistate_fields) {
	if (!is_array($opsistate_fields)) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsistate.class.php", "opsistate_field not well defined", "", "", 0, 0, 0);
		return 1;
	}
	// set stop time and status to now
	$now=$_SERVER['REQUEST_TIME'];
	$db=openqrm_get_db_connection();
	$result = $db->AutoExecute($this->_db_table, $opsistate_fields, 'INSERT');
	if (! $result) {
		$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsistate.class.php", "Failed adding new opsistate to database", "", "", 0, 0, 0);
	}
}



// removes opsistate from the database
function remove($opsistate_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->_db_table." where opsi_id=".$opsistate_id);
}


// removes opsistate from the database by resource id
function remove_by_resource_id($opsistate_resource_id) {
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("delete from ".$this->_db_table." where opsi_resource_id=".$opsistate_resource_id);
}



// returns the number of opsistates for an opsistate type
function get_count() {
	$count=0;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute("select count(opsi_id) as num from ".$this->_db_table);
	if (!$rs) {
		print $db->ErrorMsg();
	} else {
		$count = $rs->fields["num"];
	}
	return $count;
}




// returns a list of all opsistate ids
function get_all_ids() {
	$opsistate_list = array();
	$query = "select opsi_id from ".$this->_db_table;
	$db=openqrm_get_db_connection();
	$rs = $db->Execute($query);
	if (!$rs)
		$this->_event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "opsistate.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
	else
	while (!$rs->EOF) {
		$opsistate_list[] = $rs->fields;
		$rs->MoveNext();
	}
	return $opsistate_list;

}





// ---------------------------------------------------------------------------------

}

?>

