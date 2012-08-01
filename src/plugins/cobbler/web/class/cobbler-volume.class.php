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
 * This class represents an cobbler_volume object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class cobbler_volume
{

/**
* cobbler_volume id
* @access protected
* @var int
*/
var $id = '';
/**
* cobbler_volume name
* @access protected
* @var string
*/
var $name = '';
/**
* cobbler_volume root
* @access protected
* @var string
*/
var $root = '';
/**
* cobbler_volume description
* @access protected
* @var string
*/
var $description = '';


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

	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cobbler_volume() {
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
		$this->_db_table = "cobbler_volumes";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an cobbler_volume object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$cobbler_volume_array = array();
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$cobbler_volume_array = &$db->Execute("select * from ".$this->_db_table." where cobbler_volume_id=".$id);
		} else if ("$name" != "") {
			$cobbler_volume_array = &$db->Execute("select * from ".$this->_db_table." where cobbler_volume_name='".$name."'");
		}
		foreach ($cobbler_volume_array as $index => $cobbler_volume) {
			$this->id = $cobbler_volume["cobbler_volume_id"];
			$this->name = $cobbler_volume["cobbler_volume_name"];
			$this->root = $cobbler_volume["cobbler_volume_root"];
			$this->description = $cobbler_volume["cobbler_volume_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an cobbler_volume by id
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
	* get an instance of an cobbler_volume by name
	* @access public
	* @param int $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}



	//--------------------------------------------------
	/**
	* add a new cobbler_volume
	* @access public
	* @param array $cobbler_volume_fields
	*/
	//--------------------------------------------------
	function add($cobbler_volume_fields) {
		if (!is_array($cobbler_volume_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $cobbler_volume_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", "Failed adding new cobbler_volume to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an cobbler_volume
	* <code>
	* $fields = array();
	* $fields['cobbler_volume_name'] = 'somename';
	* $fields['cobbler_volume_uri'] = 'some-uri';
	* $cobbler_volume = new cobbler_volume();
	* $cobbler_volume->update(1, $fields);
	* </code>
	* @access public
	* @param int $cobbler_volume_id
	* @param array $cobbler_volume_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($cobbler_volume_id, $cobbler_volume_fields) {
		if ($cobbler_volume_id < 0 || ! is_array($cobbler_volume_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", "Unable to update cobbler_volume $cobbler_volume_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($cobbler_volume_fields["cobbler_volume_id"]);
		$result = $db->AutoExecute($this->_db_table, $cobbler_volume_fields, 'UPDATE', "cobbler_volume_id = $cobbler_volume_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", "Failed updating cobbler_volume $cobbler_volume_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an cobbler_volume by id
	* @access public
	* @param int $cobbler_volume_id
	*/
	//--------------------------------------------------
	function remove($cobbler_volume_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where cobbler_volume_id=".$cobbler_volume_id);
	}

	//--------------------------------------------------
	/**
	* remove an cobbler_volume by name
	* @access public
	* @param string $cobbler_volume_name
	*/
	//--------------------------------------------------
	function remove_by_name($cobbler_volume_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where cobbler_volume_name='".$cobbler_volume_name."'");

	}


	//--------------------------------------------------
	/**
	* get cobbler_volume name by id
	* @access public
	* @param int $cobbler_volume_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($cobbler_volume_id) {
		$db=openqrm_get_db_connection();
		$cobbler_volume_set = &$db->Execute("select cobbler_volume_name from ".$this->_db_table." where cobbler_volume_id=".$cobbler_volume_id);
		if (!$cobbler_volume_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$cobbler_volume_set->EOF) {
				return $cobbler_volume_set->fields["cobbler_volume_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all cobbler_volume names
	* <code>
	* $cobbler_volume = new cobbler_volume();
	* $arr = $cobbler_volume->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select cobbler_volume_id, cobbler_volume_name from ".$this->_db_table." order by cobbler_volume_id ASC";
		$cobbler_volume_name_array = array();
		$cobbler_volume_name_array = openqrm_db_get_result_double ($query);
		return $cobbler_volume_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all cobbler_volume ids
	* <code>
	* $cobbler_volume = new cobbler_volume();
	* $arr = $cobbler_volume->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$cobbler_volume_array = array();
		$query = "select cobbler_volume_id from ".$this->_db_table;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$cobbler_volume_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $cobbler_volume_array;
	}

	//--------------------------------------------------
	/**
	* get number of cobbler_volume accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(cobbler_volume_id) as num from ".$this->_db_table);
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of cobbler_volumes
	* @access public
	* @param int $offset
	* @param int $limit
	* @param string $sort
	* @param enum $order [ASC/DESC]
	* @return array
	*/
	//--------------------------------------------------
	function display_overview($offset, $limit, $sort, $order) {
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from ".$this->_db_table." order by $sort $order", $limit, $offset);
		$cobbler_volume_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cobbler-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cobbler_volume_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cobbler_volume_array;
	}


}
?>
