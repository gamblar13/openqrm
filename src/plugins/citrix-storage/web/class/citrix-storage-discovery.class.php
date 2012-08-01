<?php
/**
 * XenServer Host discovery
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */


// This class represents a cloud user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";

$event = new event();
global $event;

class citrix_storage_discovery {

	var $xenserver_ad_id = '';
	var $xenserver_ad_ip = '';
	var $xenserver_ad_mac = '';
	var $xenserver_ad_hostname = '';
	var $xenserver_ad_user = '';
	var $xenserver_ad_password = '';
	var $xenserver_ad_comment = '';
	var $xenserver_ad_is_integrated = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $OPENQRM_SERVER_BASE_DIR;
		$CITRIX_STORAGE_DISCOVERY_TABLE="xenserver_auto_discovery";
		$this->_event = new event();
		$this->_db_table = $CITRIX_STORAGE_DISCOVERY_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a citrix_storage_discovery object filled from the db
// ---------------------------------------------------------------------------------

	// returns an citrix_storage_discovery object from the db selected by id, mac or ip
	function get_instance($id, $mac, $ip) {
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$citrix_storage_discovery_array = &$db->Execute("select * from $this->_db_table where xenserver_ad_id=$id");
		} else if ("$mac" != "") {
			$citrix_storage_discovery_array = &$db->Execute("select * from $this->_db_table where xenserver_ad_mac='$mac'");
		} else if ("$ip" != "") {
			$citrix_storage_discovery_array = &$db->Execute("select * from $this->_db_table where xenserver_ad_ip='$ip'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", "Could not create instance of citrix_storage_discovery without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($citrix_storage_discovery_array as $index => $citrix_storage_discovery) {
			$this->xenserver_ad_id = $citrix_storage_discovery["xenserver_ad_id"];
			$this->xenserver_ad_ip = $citrix_storage_discovery["xenserver_ad_ip"];
			$this->xenserver_ad_mac = $citrix_storage_discovery["xenserver_ad_mac"];
			$this->xenserver_ad_hostname = $citrix_storage_discovery["xenserver_ad_hostname"];
			$this->xenserver_ad_user = $citrix_storage_discovery["xenserver_ad_user"];
			$this->xenserver_ad_password = $citrix_storage_discovery["xenserver_ad_password"];
			$this->xenserver_ad_comment = $citrix_storage_discovery["xenserver_ad_comment"];
			$this->xenserver_ad_is_integrated = $citrix_storage_discovery["xenserver_ad_is_integrated"];
		}
		return $this;
	}


	// returns an appliance from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "", "");
		return $this;
	}

	// returns an appliance from the db selected by mac
	function get_instance_by_mac($mac) {
		$this->get_instance("", $mac, "");
		return $this;
	}

	// returns an appliance from the db selected by ip
	function get_instance_by_ip($ip) {
		$this->get_instance("", "", $ip);
		return $this;
	}


	// ---------------------------------------------------------------------------------
	// general citrix_storage_discovery methods
	// ---------------------------------------------------------------------------------




	// checks if given citrix_storage_discovery id is free in the db
	function is_id_free($citrix_storage_discovery_id) {
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select xenserver_ad_id from $this->_db_table where xenserver_ad_id=$citrix_storage_discovery_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// checks if given citrix_storage_discovery mac is already in the db
	function mac_discoverd_already($citrix_storage_discovery_mac) {
		global $event;
		$db=openqrm_get_db_connection();

		$rs = &$db->Execute("select xenserver_ad_id from $this->_db_table where xenserver_ad_mac='$citrix_storage_discovery_mac'");
		if (!$rs)
			$event->log("mac_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}

	// checks if given citrix_storage_discovery ip is already in the db
	function ip_discoverd_already($citrix_storage_discovery_ip) {
		global $event;
		$db=openqrm_get_db_connection();

		$rs = &$db->Execute("select xenserver_ad_id from $this->_db_table where xenserver_ad_ip='$citrix_storage_discovery_ip'");
		if (!$rs)
			$event->log("ip_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds citrix_storage_discovery to the database
	function add($citrix_storage_discovery_fields) {
		global $event;
		if (!is_array($citrix_storage_discovery_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", "citrix_storage_discoverygroup_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		if (!isset($citrix_storage_discovery_fields['xenserver_ad_id'])) {
			$citrix_storage_discovery_fields['xenserver_ad_id'] = openqrm_db_get_free_id('xenserver_ad_id', $this->_db_table);
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $citrix_storage_discovery_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", "Failed adding new citrix_storage_discoverygroup to database", "", "", 0, 0, 0);
		}
	}


	// updates citrix_storage_discovery in the database
	function update($citrix_storage_discovery_id, $citrix_storage_discovery_fields) {
		global $event;
		if (!is_array($citrix_storage_discovery_fields)) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", "Unable to update citrix_storage_discoverygroup $citrix_storage_discovery_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($citrix_storage_discovery_fields["xenserver_ad_id"]);
		$result = $db->AutoExecute($this->_db_table, $citrix_storage_discovery_fields, 'UPDATE', "xenserver_ad_id = $citrix_storage_discovery_id");
		if (! $result) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", "Failed updating citrix_storage_discoverygroup $citrix_storage_discovery_id", "", "", 0, 0, 0);
		}
	}


	// removes citrix_storage_discovery from the database
	function remove($citrix_storage_discovery_id) {
		$this->get_instance_by_id($citrix_storage_discovery_id);
		$username = $this->cloud_zones_user_name;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where xenserver_ad_id=$citrix_storage_discovery_id");
	}

	// removes citrix_storage_discovery from the database by citrix_storage_discovery_mac
	function remove_by_name($citrix_storage_discovery_mac) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where xenserver_ad_mac='$citrix_storage_discovery_mac'");
	}


	// returns the number of citrix_storage_discoverys for an citrix_storage_discovery type
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(xenserver_ad_mac) as num from $this->_db_table");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all citrix_storage_discovery ids
	function get_all_ids() {
		global $event;
		$citrix_storage_discovery_list = array();
		$query = "select xenserver_ad_mac from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$citrix_storage_discovery_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $citrix_storage_discovery_list;

	}



	// displays the citrix_storage_discovery-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$citrix_storage_discovery_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "citrix-storage-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($citrix_storage_discovery_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $citrix_storage_discovery_array;
	}









// ---------------------------------------------------------------------------------

}

?>
