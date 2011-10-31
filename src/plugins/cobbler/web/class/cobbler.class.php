<?php
/**
 * @package openQRM
 */

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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

/**
 * This class represents an cobbler object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class cobbler
{

/**
* name of database table
* @access protected
* @var string
*/
var $_db_table;
/**
* path to openqrm basedir
* @access protected
* @var string
*/
var $_base_dir;
/**
* event object
* @access protected
* @var object
*/
var $_event;


/**
* id
* @access protected
* @var object
*/
var $id;

/**
* resource_id
* @access protected
* @var object
*/
var $resource_id;


/**
* resource_cobbler_ip
* @access protected
* @var object
*/
var $resource_cobbler_ip;

/**
* user
* @access protected
* @var object
*/
var $user;

/**
* pass
* @access protected
* @var object
*/
	var $pass;

/**
* comment
* @access protected
* @var object
*/
var $comment;


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = "cobbler";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}


	//--------------------------------------------------
	/**
	* get an instance of an cobblerobject from db
	* @access public
	* @param int $id
	* @param string $resource_id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $resource_id) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$cobbler_array = &$db->Execute("select * from $this->_db_table where cobbler_id=$id");
		} else if ("$resource_id" != "") {
			$cobbler_array = &$db->Execute("select * from $this->_db_table where cobbler_resource_id='$resource_id'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", "Could not create instance of cobbler without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($cobbler_array as $index => $cobbler) {
			$this->id = $cobbler["cobbler_id"];
			$this->resource_id = $cobbler["cobbler_resource_id"];
			$this->resource_cobbler_ip = $cobbler["cobbler_resource_cobbler_ip"];
			$this->user = $cobbler["cobbler_user"];
			$this->pass = $cobbler["cobbler_pass"];
			$this->comment = $cobbler["cobbler_comment"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an cobbler by id
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_id($id) {
		$this->get_instance($id, "");
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an cobbler by name
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_resource_id($resource_id) {
		$this->get_instance("", $resource_id);
		return $this;
	}




	//--------------------------------------------------
	/**
	* add a new cobbler
	* @access public
	* @param array $cobbler_fields
	*/
	//--------------------------------------------------
	function add($cobbler_fields) {
		if (!is_array($cobbler_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", "Cobbler_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $cobbler_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", "Failed adding new cobbler to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an cobbler
	* <code>
	* $fields = array();
	* $fields['cobbler_name'] = 'somename';
	* $fields['cobbler_uri'] = 'some-uri';
	* $cobbler = new cobbler();
	* $cobbler->update(1, $fields);
	* </code>
	* @access public
	* @param int $cobbler_id
	* @param array $cobbler_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($cobbler_id, $cobbler_fields) {
		if ($cobbler_id < 0 || ! is_array($cobbler_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", "Unable to update cobbler $cobbler_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($cobbler_fields["cobbler_id"]);
		$result = $db->AutoExecute($this->_db_table, $cobbler_fields, 'UPDATE', "cobbler_id = $cobbler_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", "Failed updating cobbler $cobbler_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an cobbler by id
	* @access public
	* @param int $cobbler_id
	*/
	//--------------------------------------------------
	function remove($cobbler_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where cobbler_id=$cobbler_id");
	}

	//--------------------------------------------------
	/**
	* remove an cobbler by resource_id
	* @access public
	* @param string $resource_id
	*/
	//--------------------------------------------------
	function remove_by_resource_id($resource_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where cobbler_resource_id='$resource_id'");

	}


	//--------------------------------------------------
	/**
	* get an array of all cobbler ids
	* <code>
	* $cobbler = new cobbler();
	* $arr = $cobbler->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$cobbler_array = array();
		$query = "select cobbler_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_ids", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$cobbler_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $cobbler_array;
	}

	//--------------------------------------------------
	/**
	* get number of cobbler accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(cobbler_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of cobblers
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {

		echo "!! $offset, $limit, $sort, $order <br>";

		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$cobbler_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cobbler.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cobbler_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cobbler_array;
	}
















}
?>
