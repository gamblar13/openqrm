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

	Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-server-config.php";
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * This class represents an openvz object
 *
 * @package openQRM
 * @author openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @version 1.0
 */


class openvztemplate
{

/**
* openvz-template id
* @access protected
* @var int
*/
var $id = '';
/**
* openvz-template name
* @access protected
* @var string
*/
var $name = '';
/**
* openvz-template description
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
	function openvztemplate() {
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
		$this->_db_table = "openvz_templates";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an openvz object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$openvz_array = &$db->Execute("select * from $this->_db_table where openvz_template_id=$id");
		} else if ("$name" != "") {
			$openvz_array = &$db->Execute("select * from $this->_db_table where openvz_template_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", "Could not create instance of openvz without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($openvz_array as $index => $openvz) {
			$this->id = $openvz["openvz_template_id"];
			$this->name = $openvz["openvz_template_name"];
			$this->description = $openvz["openvz_template_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an openvz by id
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
	* get an instance of an openvz by name
	* @access public
	* @param int $id
	* @return object
	*/
	//--------------------------------------------------
	function get_instance_by_name($name) {
		$this->get_instance("", $name);
		return $this;
	}

	//--------------------------------------------------
	/**
	* add a new openvz
	* @access public
	* @param array $openvz_fields
	*/
	//--------------------------------------------------
	function add($openvz_fields) {
		if (!is_array($openvz_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", "field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $openvz_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", "Failed adding new openvz to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an openvz
	* <code>
	* $fields = array();
	* $fields['openvz_name'] = 'somename';
	* $fields['openvz_uri'] = 'some-uri';
	* $openvz = new openvz();
	* $openvz->update(1, $fields);
	* </code>
	* @access public
	* @param int $openvz_id
	* @param array $openvz_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($openvz_id, $openvz_fields) {
		if ($openvz_id < 0 || ! is_array($openvz_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", "Unable to update openvz $openvz_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($openvz_fields["openvz_id"]);
		$result = $db->AutoExecute($this->_db_table, $openvz_fields, 'UPDATE', "openvz_template_id = $openvz_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", "Failed updating openvz $openvz_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an openvz by id
	* @access public
	* @param int $openvz_id
	*/
	//--------------------------------------------------
	function remove($openvz_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where openvz_template_id=$openvz_id");
	}

	//--------------------------------------------------
	/**
	* remove an openvz by name
	* @access public
	* @param string $openvz_name
	*/
	//--------------------------------------------------
	function remove_by_name($openvz_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where openvz_template_name='$openvz_name'");

	}

	//--------------------------------------------------
	/**
	* get openvz name by id
	* @access public
	* @param int $openvz_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($openvz_id) {
		$db=openqrm_get_db_connection();
		$openvz_set = &$db->Execute("select openvz_template_name from $this->_db_table where openvz_template_id=$openvz_id");
		if (!$openvz_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$openvz_set->EOF) {
				return $openvz_set->fields["openvz_name"];
			} else {
				return "idle";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all openvz names
	* <code>
	* $openvz = new openvz();
	* $arr = $openvz->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select openvz_template_id, openvz_template_name from $this->_db_table order by openvz_template_id ASC";
		$openvz_name_array = array();
		$openvz_name_array = openqrm_db_get_result_double ($query);
		return $openvz_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all openvz ids
	* <code>
	* $openvz = new openvz();
	* $arr = $openvz->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$openvz_array = array();
		$query = "select openvz_template_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$openvz_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $openvz_array;
	}

	//--------------------------------------------------
	/**
	* get number of openvz accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(openvz_template_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of openvzs
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
		$recordSet = &$db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$openvz_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "openvz-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($openvz_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $openvz_array;
	}


}
?>
