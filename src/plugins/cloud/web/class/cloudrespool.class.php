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


// This class represents a cloud resource pool in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";

$CLOUD_RESOURCE_POOL_TABLE="cloud_respool";
global $CLOUD_RESOURCE_POOL_TABLE;
$event = new event();
global $event;

class cloudrespool {

	var $id = '';
	var $resource_id = '';
	var $cg_id = '';

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudrespool() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_RESOURCE_POOL_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_RESOURCE_POOL_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}




	// ---------------------------------------------------------------------------------
	// methods to create an instance of a cloudrespool object filled from the db
	// ---------------------------------------------------------------------------------

	// returns an appliance from the db selected by id or name
	function get_instance($id, $resource_id) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		$CLOUD_RESOURCE_POOL_TABLE="cloud_respool";
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$clouduser_array = &$db->Execute("select * from $CLOUD_RESOURCE_POOL_TABLE where rp_id=$id");
		} else if ("$resource_id" != "") {
			$clouduser_array = &$db->Execute("select * from $CLOUD_RESOURCE_POOL_TABLE where rp_resource_id='$resource_id'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			return;
		}

		foreach ($clouduser_array as $index => $clouduser) {
			$this->id = $clouduser["rp_id"];
			$this->resource_id = $clouduser["rp_resource_id"];
			$this->cg_id = $clouduser["rp_cg_id"];
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
	// general cloudrespool methods
	// ---------------------------------------------------------------------------------




	// checks if given cloudrespool id is free in the db
	function is_id_free($cloudrespool_id) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select rp_id from $CLOUD_RESOURCE_POOL_TABLE where rp_id=$cloudrespool_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}



	// adds cloudrespool to the database
	function add($cloudrespool_fields) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		global $event;
		if (!is_array($cloudrespool_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", "clouduser_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($CLOUD_RESOURCE_POOL_TABLE, $cloudrespool_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", "Failed adding new clouduser to database", "", "", 0, 0, 0);
		}
	}


	// updates cloudrespool in the database
	function update($cloudrespool_id, $cloudrespool_fields) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		global $event;
		if (!is_array($cloudrespool_fields)) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", "Unable to update clouduser $cloudrespool_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($cloudrespool_fields["clouduser_id"]);
		$result = $db->AutoExecute($CLOUD_RESOURCE_POOL_TABLE, $cloudrespool_fields, 'UPDATE', "rp_id = $cloudrespool_id");
		if (! $result) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", "Failed updating clouduser $cloudrespool_id", "", "", 0, 0, 0);
		}
	}


	// removes cloudrespool from the database
	function remove($cloudrespool_id) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $CLOUD_RESOURCE_POOL_TABLE where rp_id=$cloudrespool_id");
	}




	// returns the number of cloudrespool for an clouduser type
	function get_count() {
		global $CLOUD_RESOURCE_POOL_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(rp_id) as num from $CLOUD_RESOURCE_POOL_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all cloudrespool ids
	function get_all_ids() {
		global $CLOUD_RESOURCE_POOL_TABLE;
		global $event;
		$clouduser_list = array();
		$query = "select rp_id from $CLOUD_RESOURCE_POOL_TABLE";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$clouduser_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $clouduser_list;

	}





	// displays the clouduser-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $CLOUD_RESOURCE_POOL_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $CLOUD_RESOURCE_POOL_TABLE order by $sort $order", $limit, $offset);
		$clouduser_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudrespool.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($clouduser_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $clouduser_array;
	}









// ---------------------------------------------------------------------------------

}

?>