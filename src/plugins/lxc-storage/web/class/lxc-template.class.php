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
 * This class represents an lxc object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class lxctemplate
{

/**
* lxc-template id
* @access protected
* @var int
*/
var $id = '';
/**
* lxc-template name
* @access protected
* @var string
*/
var $name = '';
/**
* lxc-template description
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
	function lxctemplate() {
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
		$this->_db_table = "lxc_templates";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an lxc object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$lxc_array = &$db->Execute("select * from $this->_db_table where lxc_template_id=$id");
		} else if ("$name" != "") {
			$lxc_array = &$db->Execute("select * from $this->_db_table where lxc_template_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", "Could not create instance of lxc without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($lxc_array as $index => $lxc) {
			$this->id = $lxc["lxc_template_id"];
			$this->name = $lxc["lxc_template_name"];
			$this->description = $lxc["lxc_template_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an lxc by id
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
	* get an instance of an lxc by name
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
	* add a new lxc
	* @access public
	* @param array $lxc_fields
	*/
	//--------------------------------------------------
	function add($lxc_fields) {
		if (!is_array($lxc_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", "field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $lxc_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", "Failed adding new lxc to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an lxc
	* <code>
	* $fields = array();
	* $fields['lxc_name'] = 'somename';
	* $fields['lxc_uri'] = 'some-uri';
	* $lxc = new lxc();
	* $lxc->update(1, $fields);
	* </code>
	* @access public
	* @param int $lxc_id
	* @param array $lxc_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($lxc_id, $lxc_fields) {
		if ($lxc_id < 0 || ! is_array($lxc_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", "Unable to update lxc $lxc_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($lxc_fields["lxc_id"]);
		$result = $db->AutoExecute($this->_db_table, $lxc_fields, 'UPDATE', "lxc_template_id = $lxc_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", "Failed updating lxc $lxc_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an lxc by id
	* @access public
	* @param int $lxc_id
	*/
	//--------------------------------------------------
	function remove($lxc_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where lxc_template_id=$lxc_id");
	}

	//--------------------------------------------------
	/**
	* remove an lxc by name
	* @access public
	* @param string $lxc_name
	*/
	//--------------------------------------------------
	function remove_by_name($lxc_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where lxc_template_name='$lxc_name'");

	}

	//--------------------------------------------------
	/**
	* get lxc name by id
	* @access public
	* @param int $lxc_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($lxc_id) {
		$db=openqrm_get_db_connection();
		$lxc_set = &$db->Execute("select lxc_template_name from $this->_db_table where lxc_template_id=$lxc_id");
		if (!$lxc_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$lxc_set->EOF) {
				return $lxc_set->fields["lxc_name"];
			} else {
				return "idle";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all lxc names
	* <code>
	* $lxc = new lxc();
	* $arr = $lxc->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select lxc_template_id, lxc_template_name from $this->_db_table order by lxc_template_id ASC";
		$lxc_name_array = array();
		$lxc_name_array = openqrm_db_get_result_double ($query);
		return $lxc_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all lxc ids
	* <code>
	* $lxc = new lxc();
	* $arr = $lxc->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$lxc_array = array();
		$query = "select lxc_template_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$lxc_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $lxc_array;
	}

	//--------------------------------------------------
	/**
	* get number of lxc accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(lxc_template_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of lxcs
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
		$lxc_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "lxc-template.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($lxc_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $lxc_array;
	}


}
?>
