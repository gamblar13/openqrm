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
 * This class represents an fai object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class fai
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
* resource_fai_ip
* @access protected
* @var object
*/
var $resource_fai_ip;

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
		$this->_db_table = "fai";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}


	//--------------------------------------------------
	/**
	* get an instance of an faiobject from db
	* @access public
	* @param int $id
	* @param string $resource_id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $resource_id) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$fai_array = &$db->Execute("select * from $this->_db_table where fai_id=$id");
		} else if ("$resource_id" != "") {
			$fai_array = &$db->Execute("select * from $this->_db_table where fai_resource_id='$resource_id'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", "Could not create instance of fai without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($fai_array as $index => $fai) {
			$this->id = $fai["fai_id"];
			$this->resource_id = $fai["fai_resource_id"];
			$this->resource_fai_ip = $fai["fai_resource_fai_ip"];
			$this->user = $fai["fai_user"];
			$this->pass = $fai["fai_pass"];
			$this->comment = $fai["fai_comment"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an fai by id
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
	* get an instance of an fai by name
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
	* add a new fai
	* @access public
	* @param array $fai_fields
	*/
	//--------------------------------------------------
	function add($fai_fields) {
		if (!is_array($fai_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", "Fai_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $fai_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", "Failed adding new fai to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an fai
	* <code>
	* $fields = array();
	* $fields['fai_name'] = 'somename';
	* $fields['fai_uri'] = 'some-uri';
	* $fai = new fai();
	* $fai->update(1, $fields);
	* </code>
	* @access public
	* @param int $fai_id
	* @param array $fai_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($fai_id, $fai_fields) {
		if ($fai_id < 0 || ! is_array($fai_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", "Unable to update fai $fai_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($fai_fields["fai_id"]);
		$result = $db->AutoExecute($this->_db_table, $fai_fields, 'UPDATE', "fai_id = $fai_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", "Failed updating fai $fai_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an fai by id
	* @access public
	* @param int $fai_id
	*/
	//--------------------------------------------------
	function remove($fai_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where fai_id=$fai_id");
	}

	//--------------------------------------------------
	/**
	* remove an fai by resource_id
	* @access public
	* @param string $resource_id
	*/
	//--------------------------------------------------
	function remove_by_resource_id($resource_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where fai_resource_id='$resource_id'");

	}


	//--------------------------------------------------
	/**
	* get an array of all fai ids
	* <code>
	* $fai = new fai();
	* $arr = $fai->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$fai_array = array();
		$query = "select fai_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_ids", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$fai_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $fai_array;
	}

	//--------------------------------------------------
	/**
	* get number of fai accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(fai_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of fais
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
		$fai_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "fai.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($fai_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $fai_array;
	}
















}
?>
