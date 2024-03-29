<?php
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
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/deployment.class.php";

// special cloud classes
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudusergroup.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudrequest.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudmailer.class.php";
require_once "$RootDir/plugins/cloud/class/cloudvm.class.php";
require_once "$RootDir/plugins/cloud/class/cloudimage.class.php";
require_once "$RootDir/plugins/cloud/class/cloudappliance.class.php";
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";

// only if puppet is available
if (file_exists("$RootDir/plugins/puppet/class/puppet.class.php")) {
	require_once "$RootDir/plugins/puppet/class/puppet.class.php";
}

// our parent class
require_once "$RootDir/plugins/cloud/class/cloudsoap.class.php";

global $CLOUD_REQUEST_TABLE;
global $event;


// user/role authentication
if (!strstr($OPENQRM_USER->role, "administrator")) {
	$event->log("AuthenticateSoapUser", $_SERVER['REQUEST_TIME'], 1, "cloud-soap-server.php", "Un-Authorized access to openQRM SOAP-Service from $OPENQRM_USER->name", "", "", 0, 0, 0);
	exit();
}



class cloudsoapadmin extends cloudsoap {


// ######################### cloud methods #####################################

// ######################### cloud user methods ################################


	//--------------------------------------------------
	/**
	* Get a list of Cloud Users
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array List of Cloud User names
	*/
	//--------------------------------------------------
	function CloudUserGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 3) {
			$event->log("cloudsoap->CloudUserGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
			return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserGetList", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		// $event->log("cloudsoap->CloudUserGetList", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Providing list of available Cloud Users", "", "", 0, 0, 0);
		$clouduser = new clouduser();
		$clouduser_list = $clouduser->get_list();
		$clouduser_name_list = array();
		foreach($clouduser_list as $cloudusers) {
			$clouduser_name_list[] = $cloudusers['label'];
		}
		return $clouduser_name_list;
	}


	//--------------------------------------------------
	/**
	* Creates a Cloud Users
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,cloud-user-password, user-email, group, forename, lastname, street, city, country, phone, ccus, lang
	* @return int id of the new Cloud User
	*/
	//--------------------------------------------------
	function CloudUserCreate($method_parameters) {
		global $CloudDir;
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		$clouduser_password = $parameter_array[4];
		$clouduser_email = $parameter_array[5];
		$clouduser_group_name = $parameter_array[6];
		$clouduser_forename = $parameter_array[7];
		$clouduser_lastname = $parameter_array[8];
		$clouduser_street = $parameter_array[9];
		$clouduser_city = $parameter_array[10];
		$clouduser_country = $parameter_array[11];
		$clouduser_phone = $parameter_array[12];
		$clouduser_ccus = $parameter_array[13];
		$clouduser_lang = $parameter_array[14];

		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 15) {
				$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		// user input checking
		if (!strlen($clouduser_name)) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud user name is empty. Not adding new user.", "", "", 0, 0, 0);
			return;
		}
		if (!strlen($clouduser_email)) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud user email is empty. Not adding new user.", "", "", 0, 0, 0);
			return;
		}
		// email valid ?
		$cloud_email = new clouduser();
		if (!$cloud_email->checkEmail($clouduser_email)) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud user email address is invalid. Not adding new user.", "", "", 0, 0, 0);
			return;
		}
		// set the user group
		if (!strlen($clouduser_group_name)) {
			$user_fields['cu_cg_id'] = 0;  // 0 = default user group
		} else {
			$cloudusergroup = new cloudusergroup();
			$cloudusergroup->get_instance_by_name($clouduser_group_name);
			$user_fields['cu_cg_id'] = $cloudusergroup->id;
		}
		// set defaults
		if (!strlen($clouduser_forename)) {
			$user_fields['cu_forename'] = "Cloud-User";
		} else {
			$user_fields['cu_forename'] = $clouduser_forename;
		}
		if (!strlen($clouduser_lastname)) {
			$user_fields['cu_lastname'] = $clouduser_name;
		} else {
			$user_fields['cu_lastname'] = $clouduser_lastname;
		}
		if (!strlen($clouduser_street)) {
			$user_fields['cu_street'] = "na";
		} else {
			$user_fields['cu_street'] = $clouduser_street;
		}
		if (!strlen($clouduser_city)) {
			$user_fields['cu_city'] = "na";
		} else {
			$user_fields['cu_city'] = $clouduser_city;
		}
		if (!strlen($clouduser_country)) {
			$user_fields['cu_country'] = "na";
		} else {
			$user_fields['cu_country'] = $clouduser_country;
		}
		if (!strlen($clouduser_phone)) {
			$user_fields['cu_phone'] = "0";
		} else {
			$user_fields['cu_phone'] = $clouduser_phone;
		}
		if (!strlen($clouduser_ccus)) {
			// check how many ccunits to give for a new user
			$cc_conf = new cloudconfig();
			$cc_auto_give_ccus = $cc_conf->get_value(12);  // 12 is auto_give_ccus
			$user_fields['cu_ccunits'] = $cc_auto_give_ccus;
		} else {
			$user_fields['cu_ccunits'] = $clouduser_ccus;
		}

		// username free ?
		$cl_user = new clouduser();
		if (!$cl_user->is_name_free($clouduser_name)) {
			$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name already exists in the Cloud. Not adding !", "", "", 0, 0, 0);
			return;
		}
		$event->log("cloudsoap->CloudUserCreate", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Creating new Cloud Users $clouduser_name", "", "", 0, 0, 0);
		// create user_fields array
		$user_fields['cu_name'] = $clouduser_name;
		$user_fields['cu_password'] = $clouduser_password;
		$user_fields['cu_email'] = $clouduser_email;
		// enabled by default
		$user_fields['cu_status'] = 1;
		$user_fields['cu_lang'] = $clouduser_lang;
		// get a new clouduser id
		$user_fields['cu_id'] = openqrm_db_get_free_id('cu_id', $cl_user->_db_table);
		$cl_user->add($user_fields);
		// add user to htpasswd
		$username = $user_fields['cu_name'];
		$password = $user_fields['cu_password'];
		$cloud_htpasswd = "$CloudDir/user/.htpasswd";
		if (file_exists($cloud_htpasswd)) {
			$openqrm_server_command="htpasswd -b $CloudDir/user/.htpasswd $username $password";
		} else {
			$openqrm_server_command="htpasswd -c -b $CloudDir/user/.htpasswd $username $password";
		}
		$output = shell_exec($openqrm_server_command);

		// set user permissions and limits, set to 0 (infinite) by default
		$cloud_user_limit = new clouduserlimits();
		$cloud_user_limits_fields['cl_id'] = openqrm_db_get_free_id('cl_id', $cloud_user_limit->_db_table);
		$cloud_user_limits_fields['cl_cu_id'] = $user_fields['cu_id'];
		$cloud_user_limits_fields['cl_resource_limit'] = 0;
		$cloud_user_limits_fields['cl_memory_limit'] = 0;
		$cloud_user_limits_fields['cl_disk_limit'] = 0;
		$cloud_user_limits_fields['cl_cpu_limit'] = 0;
		$cloud_user_limits_fields['cl_network_limit'] = 0;
		$cloud_user_limit->add($cloud_user_limits_fields);

		return $user_fields['cu_id'];
	}


	//--------------------------------------------------
	/**
	* Removes a Cloud Users
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
	function CloudUserRemove($method_parameters) {
		global $CloudDir;
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		// check all user input
		for ($i = 0; $i <= 3; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 4) {
			$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
			return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cl_user = new clouduser();
		if ($cl_user->is_name_free($clouduser_name)) {
			$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
			return 1;
		}
		$event->log("cloudsoap->CloudUserRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Removing Cloud Users $clouduser_name", "", "", 0, 0, 0);
		// remove user from htpasswd
		$openqrm_server_command="htpasswd -D $CloudDir/user/.htpasswd $clouduser_name";
		$output = shell_exec($openqrm_server_command);
		// remove permissions and limits
		$cl_user->get_instance_by_name($clouduser_name);
		$cloud_user_limit = new clouduserlimits();
		$cloud_user_limit->remove_by_cu_id($cl_user->id);
		$cl_user->remove_by_name($clouduser_name);
		return 0;
	}


	//--------------------------------------------------
	/**
	* Set the Cloud Users CCUs
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,ccunits
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
	function CloudUserSetCCUs($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		$clouduser_ccus = $parameter_array[4];
		// check all user input
		for ($i = 0; $i <= 4; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 5) {
				$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cl_user = new clouduser();
		if ($cl_user->is_name_free($clouduser_name)) {
			$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
			return 1;
		}
		$event->log("cloudsoap->CloudUserSetCCUs", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting Cloud Users $clouduser_name CCUs to $clouduser_ccus", "", "", 0, 0, 0);
		$cl_user->get_instance_by_name($clouduser_name);
		$cu_id = $cl_user->id;
		$cl_user->set_users_ccunits($cu_id, $clouduser_ccus);
		return 0;
	}



	//--------------------------------------------------
	/**
	* Set the Cloud Users Limits
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,resource_limit,memory_limit,disk_limit,cpu_limit,network_limit
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
	function CloudUserSetLimits($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		$resource_limit = $parameter_array[4];
		$memory_limit = $parameter_array[5];
		$disk_limit = $parameter_array[6];
		$cpu_limit = $parameter_array[7];
		$network_limit = $parameter_array[8];
		// check all user input
		for ($i = 0; $i <= 8; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 9) {
				$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cl_user = new clouduser();
		if ($cl_user->is_name_free($clouduser_name)) {
			$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
			return 1;
		}
		$event->log("cloudsoap->CloudUserSetLimits", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting Cloud Limits for Cloud Users $clouduser_name", "", "", 0, 0, 0);
		$cloud_user_limits_fields = array();
		$cloud_user_limits_fields['cl_resource_limit'] = $resource_limit;
		$cloud_user_limits_fields['cl_memory_limit'] = $memory_limit;
		$cloud_user_limits_fields['cl_disk_limit'] = $disk_limit;
		$cloud_user_limits_fields['cl_cpu_limit'] = $cpu_limit;
		$cloud_user_limits_fields['cl_network_limit'] = $network_limit;
		$cl_user->get_instance_by_name($clouduser_name);
		$clouduser_limit = new clouduserlimits();
		$clouduser_limit->get_instance_by_cu_id($cl_user->id);
		$clouduser_limit->update($clouduser_limit->id, $cloud_user_limits_fields);
		return 0;
	}



	// ######################### cloud usergroups methods #############################


	//--------------------------------------------------
	/**
	* Get an array of Cloud Usergroups
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password
	* @return array clouduser limits
	*/
	//--------------------------------------------------
	function CloudUserGroupGetList($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		// check all user input
		for ($i = 0; $i <= 2; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserGetUserGroups", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 3) {
			$event->log("cloudsoap->CloudUserGetUserGroups", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
			return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserGetUserGroups", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		$cloud_user_group = new cloudusergroup();
		$cloud_user_group_id_array = $cloud_user_group->get_all_ids();
		$cloud_user_groups_array = array();
		foreach($cloud_user_group_id_array as $cg_arr) {
			$cloud_user_group->get_instance_by_id($cg_arr['cg_id']);
			$cloud_user_groups_array[] = $cloud_user_group->name;
		}
		return $cloud_user_groups_array;
	}





	//--------------------------------------------------
	/**
	* Creates a Cloud Users Group
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,cloud-user-password, user-email
	* @return int id of the new Cloud User
	*/
	//--------------------------------------------------
	function CloudUserGroupCreate($method_parameters) {
		global $CloudDir;
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cloudusergroup_name = $parameter_array[3];
		$cloudusergroup_role_id = $parameter_array[4];
		$cloudusergroup_description = $parameter_array[5];
		// check all user input
		for ($i = 0; $i <= 5; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 6) {
				$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		// user input checking
		if (!strlen($cloudusergroup_name)) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User Group name is empty. Not adding new User Group.", "", "", 0, 0, 0);
			return;
		}
		if (!strlen($cloudusergroup_role_id)) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User Group role-id is empty. Not adding new User Group.", "", "", 0, 0, 0);
			return;
		}
		if (!strlen($cloudusergroup_description)) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User Group description is empty. Not adding new User Group.", "", "", 0, 0, 0);
			return;
		}

		// usergrpoup name free ?
		$cl_usergroup = new cloudusergroup();
		if (!$cl_usergroup->is_name_free($cloudusergroup_name)) {
			$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User Group name $cloudusergroup_name already exists in the Cloud. Not adding !", "", "", 0, 0, 0);
			return;
		}
		$event->log("cloudsoap->CloudUserGroupCreate", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Creating new Cloud User Group $cloudusergroup_name", "", "", 0, 0, 0);
		// create usergroups_fields array
		$usergroups_fields['cg_name'] = $cloudusergroup_name;
		$usergroups_fields['cg_role_id'] = $cloudusergroup_role_id;
		$usergroups_fields['cg_description'] = "$cloudusergroup_description";
		// get a new cloudusergroup id
		$usergroups_fields['cg_id'] = openqrm_db_get_free_id('cg_id', $cl_usergroup->_db_table);
		$cl_usergroup->add($usergroups_fields);
		return $usergroups_fields['cg_id'];
	}


	//--------------------------------------------------
	/**
	* Removes a Cloud User Group
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
	function CloudUserGroupRemove($method_parameters) {
		global $CloudDir;
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cloudusergroup_name = $parameter_array[3];
		// check all user input
		for ($i = 0; $i <= 3; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 4) {
			$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
			return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cl_usergroup = new cloudusergroup();
		if ($cl_usergroup->is_name_free($cloudusergroup_name)) {
			$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User Group name $cloudusergroup_name does not exists in this Cloud !", "", "", 0, 0, 0);
			return 1;
		}
		$event->log("cloudsoap->CloudUserGroupRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Removing Cloud User Group $cloudusergroup_name", "", "", 0, 0, 0);
		$cl_usergroup->remove_by_name($cloudusergroup_name);
		return 0;
	}






	// ######################### cloud transaction methods #############################



	//--------------------------------------------------
	/**
	* Add a new Cloud Tranaction on behalf of a Users + CR
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-user-name,cr_id,ccu_charge,ccu_balance,reason,comment
	* @return int 0 for success, 1 for error
	*/
	//--------------------------------------------------
	function CloudPushTransaction($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$clouduser_name = $parameter_array[3];
		$cr_id = $parameter_array[4];
		$ccu_charge = $parameter_array[5];
		$ccu_balance = $parameter_array[6];
		$reason = $parameter_array[7];
		$comment = $parameter_array[8];
		// check all user input
		for ($i = 0; $i <= 8; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 9) {
				$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cl_user = new clouduser();
		if ($cl_user->is_name_free($clouduser_name)) {
			$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud User name $clouduser_name does not exists in the Cloud !", "", "", 0, 0, 0);
			return 1;
		}
		$cl_user->get_instance_by_name($clouduser_name);
		$event->log("cloudsoap->CloudPushTransaction", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Setting Cloud Limits for Cloud Users $clouduser_name", "", "", 0, 0, 0);
		$ct = new cloudtransaction();
		$ct->push($cr_id, $cl_user->id, $ccu_charge, $ccu_balance, $reason, $comment);
		return 0;
	}




	// ######################### cloud request methods #############################


	//--------------------------------------------------
	/**
	* Sets the state of a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-request-id, cloud-request-state
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudRequestSetState($method_parameters) {
		global $event;
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cr_id = $parameter_array[3];
		$cr_state = $parameter_array[4];
		// check all user input
		for ($i = 0; $i <= 4; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 5) {
				$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
				return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		// set request
		$cr_request = new cloudrequest();
		$cr_request->setstatus($cr_id, $cr_state);
		$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Set Cloud request $cr_id to state $cr_state", "", "", 0, 0, 0);
		return 0;
	}

	//--------------------------------------------------
	/**
	* Removes a Cloud request
	* @access public
	* @param string $method_parameters
	*  -> mode,user-name,user-password,cloud-request-id
	* @return int 0 for success, 1 for failure
	*/
	//--------------------------------------------------
	function CloudRequestRemove($method_parameters) {
		$event = new event();
		$parameter_array = explode(',', $method_parameters);
		$mode = $parameter_array[0];
		$username = $parameter_array[1];
		$password = $parameter_array[2];
		$cr_id = $parameter_array[3];
		// check all user input
		for ($i = 0; $i <= 3; $i++) {
			if(!$this->check_param($parameter_array[$i])) {
				$event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Not allowing user-intput with special-characters : $parameter_array[$i]", "", "", 0, 0, 0);
				return;
			}
		}
		// check parameter count
		$parameter_count = count($parameter_array);
		if ($parameter_count != 4) {
			$event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Wrong parameter count $parameter_count ! Exiting.", "", "", 0, 0, 0);
			return;
		}
		// check authentication
		if (!$this->check_user($mode, $username, $password)) {
			$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "User authentication failed (mode $mode)", "", "", 0, 0, 0);
			return;
		}
		// check for admin
		if (strcmp($mode, "admin")) {
			$event->log("cloudsoap->CloudRequestSetState", $_SERVER['REQUEST_TIME'], 2, "cloud-soap-server.php", "Cloud method only available in admin mode", "", "", 0, 0, 0);
			return;
		}
		$cr_request = new cloudrequest();
		$cr_request->remove($cr_id);
		$event->log("cloudsoap->CloudRequestRemove", $_SERVER['REQUEST_TIME'], 5, "cloud-soap-server.php", "Removing Cloud request $cr_id", "", "", 0, 0, 0);
		return 0;
	}





// #############################################################################

}


?>