<?php
/**
 * @package openQRM
 */

/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/event.class.php";

/**
 * This class represents an opsi object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class opsi
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
* resource_opsi_ip
* @access protected
* @var object
*/
var $resource_opsi_ip;

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
		$this->_db_table = "opsi";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}


	//--------------------------------------------------
	/**
	* get an instance of an opsiobject from db
	* @access public
	* @param int $id
	* @param string $resource_id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $resource_id) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$opsi_array = &$db->Execute("select * from $this->_db_table where opsi_id=$id");
		} else if ("$resource_id" != "") {
			$opsi_array = &$db->Execute("select * from $this->_db_table where opsi_resource_id='$resource_id'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", "Could not create instance of opsi without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($opsi_array as $index => $opsi) {
			$this->id = $opsi["opsi_id"];
			$this->resource_id = $opsi["opsi_resource_id"];
			$this->resource_opsi_ip = $opsi["opsi_resource_opsi_ip"];
			$this->user = $opsi["opsi_user"];
			$this->pass = $opsi["opsi_pass"];
			$this->comment = $opsi["opsi_comment"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an opsi by id
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
	* get an instance of an opsi by name
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
	* add a new opsi
	* @access public
	* @param array $opsi_fields
	*/
	//--------------------------------------------------
	function add($opsi_fields) {
		if (!is_array($opsi_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", "Opsi_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $opsi_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", "Failed adding new opsi to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an opsi
	* <code>
	* $fields = array();
	* $fields['opsi_name'] = 'somename';
	* $fields['opsi_uri'] = 'some-uri';
	* $opsi = new opsi();
	* $opsi->update(1, $fields);
	* </code>
	* @access public
	* @param int $opsi_id
	* @param array $opsi_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($opsi_id, $opsi_fields) {
		if ($opsi_id < 0 || ! is_array($opsi_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", "Unable to update opsi $opsi_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($opsi_fields["opsi_id"]);
		$result = $db->AutoExecute($this->_db_table, $opsi_fields, 'UPDATE', "opsi_id = $opsi_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", "Failed updating opsi $opsi_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an opsi by id
	* @access public
	* @param int $opsi_id
	*/
	//--------------------------------------------------
	function remove($opsi_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where opsi_id=$opsi_id");
	}

	//--------------------------------------------------
	/**
	* remove an opsi by resource_id
	* @access public
	* @param string $resource_id
	*/
	//--------------------------------------------------
	function remove_by_resource_id($resource_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where opsi_resource_id='$resource_id'");

	}


	//--------------------------------------------------
	/**
	* get an array of all opsi ids
	* <code>
	* $opsi = new opsi();
	* $arr = $opsi->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$opsi_array = array();
		$query = "select opsi_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_ids", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$opsi_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $opsi_array;
	}

	//--------------------------------------------------
	/**
	* get number of opsi accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(opsi_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of opsis
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
		$opsi_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "opsi.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($opsi_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $opsi_array;
	}
















}
?>
