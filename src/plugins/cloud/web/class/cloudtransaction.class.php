<?php
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


// This class represents a cloudtransaction object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once $RootDir."/include/openqrm-database-functions.php";
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/kernel.class.php";
require_once $RootDir."/class/plugin.class.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/class/openqrm_server.class.php";
// cloud user class for updating ccus from the cloud zones master
// cloud config for getting the cloud zones config
require_once $RootDir."/plugins/cloud/class/clouduser.class.php";
require_once $RootDir."/plugins/cloud/class/cloudusergroup.class.php";
require_once $RootDir."/plugins/cloud/class/cloudconfig.class.php";
require_once $RootDir."/plugins/cloud/class/cloudtransactionfailed.class.php";


$CLOUD_TRANSACTION_TABLE="cloud_transaction";
global $CLOUD_TRANSACTION_TABLE;
$CLOUD_TRANSACTION_FAILED_TABLE="cloud_transaction_failed";
global $CLOUD_TRANSACTION_FAILED_TABLE;

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class cloudtransaction {

	var $id = '';
	var $time = '';
	var $cr_id = '';
	var $cu_id = '';
	var $ccu_charge = '';
	var $ccu_balance = '';
	var $reason = '';
	var $comment = '';


	//--------------------------------------------------
	/**
	* Constructor
	*/
	//--------------------------------------------------
	function cloudtransaction() {
		$this->init();
	}

	//--------------------------------------------------
	/**
	* init storage environment
	* @access public
	*/
	//--------------------------------------------------
	function init() {
		global $CLOUD_TRANSACTION_TABLE, $OPENQRM_SERVER_BASE_DIR;
		$this->_event = new event();
		$this->_db_table = $CLOUD_TRANSACTION_TABLE;
		$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
	}

// ---------------------------------------------------------------------------------
// methods to create an instance of a cloudtransaction object filled from the db
// ---------------------------------------------------------------------------------

	// returns an transaction from the db selected by id or name
	function get_instance($id, $cr_id) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		if ("$id" != "") {
			$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_id=$id");
		} else if ("$cr_id" != "") {
			$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_cr_id=$cr_id");
		} else if ("$cu_id" != "") {
			$cloudtransaction_array = &$db->Execute("select * from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id");
		} else {
			$event->log("get_instance", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "Could not create instance of event without data", "", "", 0, 0, 0);
			return;
		}

		foreach ($cloudtransaction_array as $index => $cloudtransaction) {
			$this->id = $cloudtransaction["ct_id"];
			$this->time = $cloudtransaction["ct_time"];
			$this->cr_id = $cloudtransaction["ct_cr_id"];
			$this->cu_id = $cloudtransaction["ct_cu_id"];
			$this->ccu_charge = $cloudtransaction["ct_ccu_charge"];
			$this->ccu_balance = $cloudtransaction["ct_ccu_balance"];
			$this->reason = $cloudtransaction["ct_reason"];
			$this->comment = $cloudtransaction["ct_comment"];
		}
		return $this;
	}

	// returns an cloudtransaction from the db selected by id
	function get_instance_by_id($id) {
		$this->get_instance($id, "", "");
		return $this;
	}

	// returns an cloudtransaction from the db selected by the cr_id
	function get_instance_by_cr_id($cr_id) {
		$this->get_instance("", $cr_id, "");
		return $this;
	}

	// returns an cloudtransaction from the db selected by the cu_id
	function get_instance_by_cu_id($cu_id) {
		$this->get_instance("", "", $cu_id);
		return $this;
	}

	// ---------------------------------------------------------------------------------
	// general cloudtransaction methods
	// ---------------------------------------------------------------------------------




	// checks if given cloudtransaction id is free in the db
	function is_id_free($cloudtransaction_id) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$rs = &$db->Execute("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_id=$cloudtransaction_id");
		if (!$rs)
			$event->log("is_id_free", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		if ($rs->EOF) {
			return true;
		} else {
			return false;
		}
	}


	// adds cloudtransaction to the database
	function add($cloudtransaction_fields) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		if (!is_array($cloudtransaction_fields)) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "cloudtransaction_field not well defined", "", "", 0, 0, 0);
			return 1;
		}
		// set stop time and status to now
		$now=$_SERVER['REQUEST_TIME'];
		$db=openqrm_get_db_connection();
		$result = $db->AutoExecute($CLOUD_TRANSACTION_TABLE, $cloudtransaction_fields, 'INSERT');
		if (! $result) {
			$event->log("add", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "Failed adding new cloudtransaction to database", "", "", 0, 0, 0);
		}
	}



	// removes cloudtransaction from the database
	function remove($cloudtransaction_id) {
		global $CLOUD_TRANSACTION_TABLE;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("delete from $CLOUD_TRANSACTION_TABLE where ct_id=$cloudtransaction_id");
	}



	// function to push a new transaction to the stack
	function push($cr_id, $cu_id, $ccu_charge, $ccu_balance, $reason, $comment) {

		$transaction_fields['ct_id'] = openqrm_db_get_free_id('ct_id', $this->_db_table);
		$transaction_fields['ct_time'] = $_SERVER['REQUEST_TIME'];
		$transaction_fields['ct_cr_id'] = $cr_id;
		$transaction_fields['ct_cu_id'] = $cu_id;
		$transaction_fields['ct_ccu_charge'] = $ccu_charge;
		$transaction_fields['ct_ccu_balance'] = $ccu_balance;
		$transaction_fields['ct_reason'] = $reason;
		$transaction_fields['ct_comment'] = $comment;
		$new_ct_id = $transaction_fields['ct_id'];
		$this->add($transaction_fields);

		// check if we need to sync with the cloud-zones master
		$cz_conf = new cloudconfig();
		$cz_client = $cz_conf->get_value(35);			// 35 is cloud_zones_client
		if (!strcmp($cz_client, "true")) {
			$this->sync($transaction_fields['ct_id'], true);
		}

	}


	// function to sync a new transaction with the cloud zones master
	function sync($ct_id, $insert_into_failed) {
		global $CLOUD_TRANSACTION_FAILED_TABLE;
		global $OPENQRM_SERVER_IP_ADDRESS;
		global $event;

		$this->get_instance_by_id($ct_id);
		// get cloud user
		$local_transaction_cloud_user = new clouduser();
		$local_transaction_cloud_user->get_instance_by_id($this->cu_id);
		// get cloud-zones config parameters from main config
		$cz_conf = new cloudconfig();
		$cloud_zones_master_ip = $cz_conf->get_value(36);			// 36 is cloud_zones_master_ip
		// check if cloud_external_ip is set
		$cloud_external_ip = $cz_conf->get_value(37);			// 37 is cloud_external_ip
		if (!strlen($cloud_external_ip)) {
			$cloud_external_ip = $OPENQRM_SERVER_IP_ADDRESS;
		}
		// get the admin user, the zone master will automatically authenticate against this user
		$openqrm_admin_user = new user("openqrm");
		$openqrm_admin_user->set_user();
		// url for the wdsl
		$url = "https://".$cloud_zones_master_ip."/openqrm/boot-service/cloud-zones-soap.wsdl";
		// turn off the WSDL cache
		ini_set("soap.wsdl_cache_enabled", "0");
		// create the soap-client
		$client = new SoapClient($url, array('soap_version' => SOAP_1_2, 'trace' => 1, 'login'=> $openqrm_admin_user->name, 'password' => $openqrm_admin_user->password ));
//			var_dump($client->__getFunctions());
		try {
			$send_transaction_parameters = $openqrm_admin_user->name.",".$openqrm_admin_user->password.",".$cloud_external_ip.",".$local_transaction_cloud_user->name.",".$this->id.",".$this->time.",".$this->cr_id.",".$this->ccu_charge.",".$this->reason.",".$this->comment;
			$new_local_ccu_value = $client->CloudZonesSync($send_transaction_parameters);
			// update users ccus values with return from master
			$local_transaction_cloud_user->set_users_ccunits($this->cu_id, $new_local_ccu_value);
			$event->log("push", $_SERVER['REQUEST_TIME'], 5, "cloudtransaction.class.php", "Synced transaction! User:".$this->cu_id."/CR:".$this->cr_id."/Global CCU:".$new_local_ccu_value, "", "", 0, 0, 0);
			return true;

		} catch (Exception $e) {
			$soap_error_msg = $e->getMessage();
			$event->log("push", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", "Could not sync transaction! User:".$this->cu_id."/CR:".$this->cr_id."/Charge:".$this->ccu_charge."/".$soap_error_msg, "", "", 0, 0, 0);
			if ($insert_into_failed) {
				// add to failed transactions
				$cloudtransactionfailed = new cloudtransactionfailed();
				$failed_transaction_fields['tf_id'] = openqrm_db_get_free_id('tf_id', $CLOUD_TRANSACTION_FAILED_TABLE);
				$failed_transaction_fields['tf_ct_id'] = $ct_id;
				$cloudtransactionfailed->add($failed_transaction_fields);
			}
			return false;
		}
	}



	// returns the number of cloudtransactions for an cloudtransaction type per user
	function get_count_per_clouduser($cu_id) {
		global $CLOUD_TRANSACTION_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(ct_id) as num from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	// returns the number of cloudtransactions for an cloudtransaction type
	function get_count() {
		global $CLOUD_TRANSACTION_TABLE;
		$count=0;
		$db=openqrm_get_db_connection();
		$rs = $db->Execute("select count(ct_id) as num from $CLOUD_TRANSACTION_TABLE");
		if (!$rs) {
			print $db->ErrorMsg();
		} else {
			$count = $rs->fields["num"];
		}
		return $count;
	}


	// returns a list of all cloudtransaction names
	function get_list() {
		global $CLOUD_TRANSACTION_TABLE;
		$query = "select ct_id, ct_cr_id from $CLOUD_TRANSACTION_TABLE";
		$cloudtransaction_name_array = array();
		$cloudtransaction_name_array = openqrm_db_get_result_double ($query);
		return $cloudtransaction_name_array;
	}


	// returns a list of all cloudtransaction ids
	function get_all_ids() {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$cloudtransaction_list = array();
		$query = "select ct_id from $CLOUD_TRANSACTION_TABLE";
		$db=openqrm_get_db_connection();
		$rs = $db->Execute($query);
		if (!$rs)
			$event->log("get_list", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		else
		while (!$rs->EOF) {
			$cloudtransaction_list[] = $rs->fields;
			$rs->MoveNext();
		}
		return $cloudtransaction_list;

	}



	// returns a list of cloudtransaction ids per user
	function get_transactions_per_user($cu_id, $limit) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id order by ct_id DESC", $limit, 0);
		$cloudtransaction_array = array();
		if (!$recordSet) {
			$event->log("get_transactions_per_user", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cloudtransaction_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cloudtransaction_array;
	}


	// returns a list of cloudtransaction ids per cr_id
	function get_transactions_per_cr($cr_id, $limit) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select ct_id from $CLOUD_TRANSACTION_TABLE where ct_cr_id=$cr_id order by ct_id DESC", $limit, 0);
		$cloudtransaction_array = array();
		if (!$recordSet) {
			$event->log("get_transactions_per_cr", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cloudtransaction_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cloudtransaction_array;
	}


	// displays the cloudtransaction-overview per user
	function display_overview_per_clouduser($cu_id, $offset, $limit, $sort, $order) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $CLOUD_TRANSACTION_TABLE where ct_cu_id=$cu_id order by $sort $order", $limit, $offset);
		$cloudtransaction_array = array();
		if (!$recordSet) {
			$event->log("display_overview_per_clouduser", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cloudtransaction_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cloudtransaction_array;
	}




	// displays the cloudtransaction-overview
	function display_overview($offset, $limit, $sort, $order) {
		global $CLOUD_TRANSACTION_TABLE;
		global $event;
		$db=openqrm_get_db_connection();
		$recordSet = &$db->SelectLimit("select * from $CLOUD_TRANSACTION_TABLE order by $sort $order", $limit, $offset);
		$cloudtransaction_array = array();
		if (!$recordSet) {
			$event->log("display_overview", $_SERVER['REQUEST_TIME'], 2, "cloudtransaction.class.php", $db->ErrorMsg(), "", "", 0, 0, 0);
		} else {
			while (!$recordSet->EOF) {
				array_push($cloudtransaction_array, $recordSet->fields);
				$recordSet->MoveNext();
			}
			$recordSet->Close();
		}
		return $cloudtransaction_array;
	}







// ---------------------------------------------------------------------------------

}

?>