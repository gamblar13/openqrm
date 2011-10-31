<?php
/**
 * ESX Host discovery
 *
 * This file is part of openQRM.
 *
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Matt Rechenburg <matt@openqrm-enterprise.com>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */


// This class represents a cloud user in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";

$event = new event();
global $event;

class vmware_esx_discovery {

	var $vmw_esx_ad_id = '';
	var $vmw_esx_ad_ip = '';
	var $vmw_esx_ad_mac = '';
	var $vmw_esx_ad_hostname = '';
	var $vmw_esx_ad_user = '';
	var $vmw_esx_ad_password = '';
	var $vmw_esx_ad_comment = '';
	var $vmw_esx_ad_is_integrated = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function __construct() {
		global $OPENQRM_SERVER_BASE_DIR;
		$VMWARE_ESX_DISCOVERY_TABLE="vmw_esx_auto_discovery";
		$this->_event = new event();
		$this->_db_table = $VMWARE_ESX_DISCOVERY_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a vmware_esx_discovery object filled from the db
// ---------------------------------------------------------------------------------

	// returns an vmware_esx_discovery object from the db selected by id, mac or ip
	function get_instance($id, $mac, $ip) {
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$vmware_esx_discovery_array = &$db->Execute("select * from $this->_db_table where vmw_esx_ad_id=$id");
		} else if ("$mac" != "") {
			$vmware_esx_discovery_array = &$db->Execute("select * from $this->_db_table where vmw_esx_ad_mac='$mac'");
		} else if ("$ip" != "") {
			$vmware_esx_discovery_array = &$db->Execute("select * from $this->_db_table where vmw_esx_ad_ip='$ip'");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Could not create instance of vmware_esx_discovery without data", "", "", 0, 0, 0);
			return;
		}
		foreach ($vmware_esx_discovery_array as $index => $vmware_esx_discovery) {
			$this->vmw_esx_ad_id = $vmware_esx_discovery["vmw_esx_ad_id"];
			$this->vmw_esx_ad_ip = $vmware_esx_discovery["vmw_esx_ad_ip"];
			$this->vmw_esx_ad_mac = $vmware_esx_discovery["vmw_esx_ad_mac"];
			$this->vmw_esx_ad_hostname = $vmware_esx_discovery["vmw_esx_ad_hostname"];
			$this->vmw_esx_ad_user = $vmware_esx_discovery["vmw_esx_ad_user"];
			$this->vmw_esx_ad_password = $vmware_esx_discovery["vmw_esx_ad_password"];
			$this->vmw_esx_ad_comment = $vmware_esx_discovery["vmw_esx_ad_comment"];
			$this->vmw_esx_ad_is_integrated = $vmware_esx_discovery["vmw_esx_ad_is_integrated"];
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
	// general vmware_esx_discovery methods
	// ---------------------------------------------------------------------------------




	// checks if given vmware_esx_discovery id is free in the db
	function is_id_free($vmware_esx_discovery_id) {
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_id=$vmware_esx_discovery_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// checks if given vmware_esx_discovery mac is already in the db
	function mac_discoverd_already($vmware_esx_discovery_mac) {
		global $event;
		$db=openqrm_get_db_connection();

		$rs = &$db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_mac='$vmware_esx_discovery_mac'");
		if (!$rs)
			$event->log("mac_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}

	// checks if given vmware_esx_discovery ip is already in the db
	function ip_discoverd_already($vmware_esx_discovery_ip) {
		global $event;
		$db=openqrm_get_db_connection();

		$rs = &$db->Execute("select vmw_esx_ad_id from $this->_db_table where vmw_esx_ad_ip='$vmware_esx_discovery_ip'");
		if (!$rs)
			$event->log("ip_discoverd_already", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds vmware_esx_discovery to the database
	function add($vmware_esx_discovery_fields) {
		global $event;
		if (!is_array($vmware_esx_discovery_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "vmware_esx_discoverygroup_fields not well defined", "", "", 0, 0, 0);
			return 1;
		}
		if (!isset($vmware_esx_discovery_fields['vmw_esx_ad_id'])) {
			$vmware_esx_discovery_fields['vmw_esx_ad_id'] = openqrm_db_get_free_id('vmw_esx_ad_id', $this->_db_table);
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($this->_db_table, $vmware_esx_discovery_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Failed adding new vmware_esx_discoverygroup to database", "", "", 0, 0, 0);
		}
	}


	// updates vmware_esx_discovery in the database
	function update($vmware_esx_discovery_id, $vmware_esx_discovery_fields) {
		global $event;
		if (!is_array($vmware_esx_discovery_fields)) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Unable to update vmware_esx_discoverygroup $vmware_esx_discovery_id", "", "", 0, 0, 0);
			return 1;
		}
		$db=openqrm_get_db_connection();
		unset($vmware_esx_discovery_fields["vmw_esx_ad_id"]);
		$result = $db->AutoExecute($this->_db_table, $vmware_esx_discovery_fields, 'UPDATE', "vmw_esx_ad_id = $vmware_esx_discovery_id");
		if (! $result) {
			$event->log("update", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", "Failed updating vmware_esx_discoverygroup $vmware_esx_discovery_id", "", "", 0, 0, 0);
		}
	}


	// removes vmware_esx_discovery from the database
	function remove($vmware_esx_discovery_id) {
		$this->get_instance_by_id($vmware_esx_discovery_id);
		$username = $this->cloud_zones_user_name;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where vmw_esx_ad_id=$vmware_esx_discovery_id");
	}

	// removes vmware_esx_discovery from the database by vmware_esx_discovery_mac
	function remove_by_name($vmware_esx_discovery_mac) {
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $this->_db_table where vmw_esx_ad_mac='$vmware_esx_discovery_mac'");
	}


	// returns the number of vmware_esx_discoverys for an vmware_esx_discovery type
	function get_count() {
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(vmw_esx_ad_mac) as num from $this->_db_table");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}



	// returns a list of all vmware_esx_discovery ids
	function get_all_ids() {
		global $event;
		$vmware_esx_discovery_list = array();
		$query = "select vmw_esx_ad_mac from $this->_db_table";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$vmware_esx_discovery_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $vmware_esx_discovery_list;

	}



	// displays the vmware_esx_discovery-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $this->_db_table order by $sort $order", $limit, $offset);
		$vmware_esx_discovery_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "vmware-esx-discovery.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($vmware_esx_discovery_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $vmware_esx_discovery_array;
	}









// ---------------------------------------------------------------------------------

}

?>
