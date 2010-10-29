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

    Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
*/


	$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
	require_once "$RootDir/include/openqrm-server-config.php";
	require_once "$RootDir/include/openqrm-database-functions.php";
	require_once "$RootDir/class/event.class.php";

/**
 * This class represents an hybrid-cloud object
 *
 * @package openQRM
 * @author Matt Rechenburg <mattr_sf@users.sourceforge.net>
 * @version 1.0
 */


class hybrid_cloud
{

/**
* hybrid-cloud id
* @access protected
* @var int
*/
var $id = '';
/**
* hybrid-cloud account_name
* @access protected
* @var string
*/
var $account_name = '';
/**
* hybrid-cloud account_type
* @access protected
* @var string
*/
var $account_type = '';
/**
* hybrid-cloud rc-config file
* @access protected
* @var string
*/
var $rc_config = '';
/**
* hybrid-cloud ssh-key file
* @access protected
* @var string
*/
var $ssh_key = '';
/**
* hybrid-cloud account description
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
	function hybrid_cloud() {
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
		$this->_db_table = "hybrid_cloud_accounts";
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

	//--------------------------------------------------
	/**
	* get an instance of an hybrid-cloud object from db
	* @access public
	* @param int $id
	* @param string $name
	* @return object
	*/
	//--------------------------------------------------
	function get_instance($id, $name) {
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$hybrid_cloud_array = &$db->Execute("select * from $this->_db_table where hybrid_cloud_id=$id");
		} else if ("$name" != "") {
			$hybrid_cloud_array = &$db->Execute("select * from $this->_db_table where hybrid_cloud_account_name='$name'");
		} else {
			$this->_event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", "Could not create instance of hybrid-cloud without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($hybrid_cloud_array as $index => $hybrid_cloud) {
			$this->id = $hybrid_cloud["hybrid_cloud_id"];
			$this->account_name = $hybrid_cloud["hybrid_cloud_account_name"];
			$this->account_type = $hybrid_cloud["hybrid_cloud_account_type"];
			$this->rc_config = $hybrid_cloud["hybrid_cloud_rc_config"];
			$this->ssh_key = $hybrid_cloud["hybrid_cloud_ssh_key"];
			$this->description = $hybrid_cloud["hybrid_cloud_description"];
		}
		return $this;
	}

	//--------------------------------------------------
	/**
	* get an instance of an hybrid-cloud by id
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
	* get an instance of an hybrid-cloud by name
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
	* add a new hybrid-cloud
	* @access public
	* @param array $hybrid_cloud_fields
	*/
	//--------------------------------------------------
	function add($hybrid_cloud_fields) {
		if (!is_array($hybrid_cloud_fields)) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", "Fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $hybrid_cloud_fields, 'INSERT');
		if (! $result) {
			$this->_event->log("add", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", "Failed adding new hybrid-cloud to database", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* update an hybrid-cloud
	* <code>
	* $fields = array();
	* $fields['hybrid_cloud_name'] = 'somename';
	* $fields['hybrid_cloud_uri'] = 'some-uri';
	* $hybrid-cloud = new hybrid-cloud();
	* $hybrid-cloud->update(1, $fields);
	* </code>
	* @access public
	* @param int $hybrid_cloud_id
	* @param array $hybrid_cloud_fields
	* @return bool
	*/
	//--------------------------------------------------
	function update($hybrid_cloud_id, $hybrid_cloud_fields) {
		if ($hybrid_cloud_id < 0 || ! is_array($hybrid_cloud_fields)) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", "Unable to update hybrid-cloud $hybrid_cloud_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($hybrid_cloud_fields["hybrid_cloud_id"]);
		$result = $db->AutoExecute($this->_db_table, $hybrid_cloud_fields, 'UPDATE', "hybrid_cloud_id = $hybrid_cloud_id");
		if (! $result) {
			$this->_event->log("update", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", "Failed updating hybrid-cloud $hybrid_cloud_id", "", "", 0, 0, 0);
		}
	}

	//--------------------------------------------------
	/**
	* remove an hybrid-cloud by id
	* @access public
	* @param int $hybrid_cloud_id
	*/
	//--------------------------------------------------
	function remove($hybrid_cloud_id) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hybrid_cloud_id=$hybrid_cloud_id");
	}

	//--------------------------------------------------
	/**
	* remove an hybrid-cloud by name
	* @access public
	* @param string $hybrid_cloud_name
	*/
	//--------------------------------------------------
	function remove_by_name($hybrid_cloud_name) {
		// remove from db
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where hybrid_cloud_account_name='$hybrid_cloud_name'");
	
	}

	//--------------------------------------------------
	/**
	* get hybrid-cloud name by id
	* @access public
	* @param int $hybrid_cloud_id
	* @return string
	*/
	//--------------------------------------------------
	function get_name($hybrid_cloud_id) {
		$db=openqrm_get_db_connection();
		$hybrid_cloud_set = &$db->Execute("select hybrid_cloud_account_name from $this->_db_table where hybrid_cloud_id=$hybrid_cloud_id");
		if (!$hybrid_cloud_set) {
			$this->_event->log("get_name", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			if (!$hybrid_cloud_set->EOF) {
				return $hybrid_cloud_set->fields["hybrid_cloud_name"];
			} else {
				return "not found";
			}
		}
	}



	//--------------------------------------------------
	/**
	* get an array of all hybrid-cloud names
	* <code>
	* $hybrid-cloud = new hybrid-cloud();
	* $arr = $hybrid-cloud->get_list();
	* // $arr[0]['value']
	* // $arr[0]['label']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_list() {
		$query = "select hybrid_cloud_id, hybrid_cloud_account_name from $this->_db_table order by hybrid_cloud_id ASC";
		$hybrid_cloud_name_array = array();
		$hybrid_cloud_name_array = openqrm_db_get_result_double ($query);
		return $hybrid_cloud_name_array;
	}


	//--------------------------------------------------
	/**
	* get an array of all hybrid-cloud ids
	* <code>
	* $hybrid-cloud = new hybrid-cloud();
	* $arr = $hybrid-cloud->get_ids();
	* // $arr['value']
	* </code>
	* @access public
	* @return array
	*/
	//--------------------------------------------------
	function get_ids() {
		$hybrid_cloud_array = array();
		$query = "select hybrid_cloud_id from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$hybrid_cloud_array[] = $rs->fields;
			$rs->MoveNext();
		}
		return $hybrid_cloud_array;
	}

	//--------------------------------------------------
	/**
	* get number of hybrid-cloud accounts
	* @access public
	* @return int
	*/
	//--------------------------------------------------
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(hybrid_cloud_id) as num from $this->_db_table");
		if (!$rs) {
			$this->_event->log("get_count", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	//--------------------------------------------------
	/**
	* get an array of hybrid-clouds
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
		$hybrid_cloud_array = array();
		if (!$recordSet) {
			$this->_event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "hybrid-cloud.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($hybrid_cloud_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}		
		return $hybrid_cloud_array;
	}


}
?>
