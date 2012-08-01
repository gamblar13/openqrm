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
 * This class represents an fai_volume object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class fai_volume
{

/**
* fai_volume id
* @access protected
* @var int
*/
var $id = '';
/**
* fai_volume name
* @access protected
* @var string
*/
var $name = '';
/**
* fai_volume root
* @access protected
* @var string
*/
var $root = '';
/**
* fai_volume description
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
	function fai_volume() {
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
		$this->_db_table = "fai_volumes";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an fai_volume object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$fai_volume_array = array();
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$fai_volume_array = &$db->Execute("select * from ".$this->_db_table." where fai_volume_id=".$id);
		} else if ("$name" != "") {
			$fai_volume_array = &$db->Execute("select * from ".$this->_db_table." where fai_volume_name='".$name."'");
		}
		foreach ($fai_volume_array as $index => $fai_volume) {
			$this->id = $fai_volume["fai_volume_id"];
			$this->name = $fai_volume["fai_volume_name"];
			$this->root = $fai_volume["fai_volume_root"];
			$this->description = $fai_volume["fai_volume_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an fai_volume by id
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
	* get an instance of an fai_volume by name
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
	* add a new fai_volume
	* @access public
	* @param array $fai_volume_fields
	*/
	//--------------------------------------------------
	function add($fai_volume_fields) {
		if (!is_array($fai_volume_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $fai_volume_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", "Failed adding new fai_volume to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an fai_volume
	* <code>
	* $fields = array();
	* $fields['fai_volume_name'] = 'somename';
	* $fields['fai_volume_uri'] = 'some-uri';
	* $fai_volume = new fai_volume();
	* $fai_volume->update(1, $fields);
	* </code>
	* @access public
	* @param int $fai_volume_id
	* @param array $fai_volume_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($fai_volume_id, $fai_volume_fields) {
		if ($fai_volume_id < 0 || ! is_array($fai_volume_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", "Unable to update fai_volume $fai_volume_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($fai_volume_fields["fai_volume_id"]);
		$result = $db->AutoExecute($this->_db_table, $fai_volume_fields, 'UPDATE', "fai_volume_id = $fai_volume_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", "Failed updating fai_volume $fai_volume_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an fai_volume by id
	* @access public
	* @param int $fai_volume_id
	*/
	//--------------------------------------------------
	function remove($fai_volume_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where fai_volume_id=".$fai_volume_id);
	}

	//--------------------------------------------------
	/**
	* remove an fai_volume by name
	* @access public
	* @param string $fai_volume_name
	*/
	//--------------------------------------------------
	function remove_by_name($fai_volume_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from ".$this->_db_table." where fai_volume_name='".$fai_volume_name."'");

	}


	//--------------------------------------------------
	/**
	* get fai_volume name by id
	* @access public
	* @param int $fai_volume_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($fai_volume_id) {
		$db=openqrm_get_db_connection();
		$fai_volume_set = &$db->Execute("select fai_volume_name from ".$this->_db_table." where fai_volume_id=".$fai_volume_id);
		if (!$fai_volume_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$fai_volume_set->EOF) {
				return $fai_volume_set->fields["fai_volume_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all fai_volume names
	* <code>
	* $fai_volume = new fai_volume();
	* $arr = $fai_volume->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select fai_volume_id, fai_volume_name from ".$this->_db_table." order by fai_volume_id ASC";
		$fai_volume_name_array = array();
		$fai_volume_name_array = openqrm_db_get_result_double ($query);
		return $fai_volume_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all fai_volume ids
	* <code>
	* $fai_volume = new fai_volume();
	* $arr = $fai_volume->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$fai_volume_array = array();
		$query = "select fai_volume_id from ".$this->_db_table;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$fai_volume_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $fai_volume_array;
	}

	//--------------------------------------------------
	/**
	* get number of fai_volume accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(fai_volume_id) as num from ".$this->_db_table);
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of fai_volumes
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
		$fai_volume_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "fai-volume.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($fai_volume_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $fai_volume_array;
	}


}
?>
