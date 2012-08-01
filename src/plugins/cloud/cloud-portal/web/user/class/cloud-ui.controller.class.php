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


class cloud_ui_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_ui';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-cloud-ui";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'project_tab_ui';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* user
* @access public
* @var string
*/
var $user;
/**
* usergroup
* @access public
* @var string
*/
var $usergroup;
/**
* cloud-id
* @access public
* @var int
*/
var $cloud_id;
/**
* config
* @access public
* @var object
*/
var $config;




/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'cloud_ui_home' => 'Home',
	'cloud_ui_vcd' => 'VCD',
	'cloud_ui_vid' => 'VID',
	'cloud_ui_title' => 'Cloud',
	'cloud_ui_big_title' => 'Cloud',
	'cloud_ui_profiles' => 'Profiles',
	'cloud_ui_profile' => 'Profile',
	'cloud_ui_save_as_profile' => 'Save as Profile',
	'cloud_ui_upload_icon' => 'Upload custom Icon',
	'cloud_ui_profile_icon' => 'Profile Icon',
	'cloud_ui_profile_icon_select' => 'Select',
	'cloud_ui_upload_icon_description' => '(48px x 48px, png, gif, jpg)',
	'cloud_ui_profile_upload_successful' => 'Uploaded custom Profile Icon successfully',
	'cloud_ui_profile_edit' => 'Edit Profile',
	'cloud_ui_profile_remove' => 'Remove Profile',
	'cloud_ui_confirm_profile_remove' => 'Remove the following Cloud Profiles?',
	'cloud_ui_confirm_profile_removed' => 'Removed Cloud Profile %s',
	'cloud_ui_profile_comment_update' => 'Update Cloud Profile description',
	'cloud_ui_profile_comment_updated' => 'Updated description of Cloud Profile %s',
	'cloud_ui_profile_name_in_use' => 'Cloud profile name %s already in use. Please choose another name.',
	'cloud_ui_profile_max_reached' => 'Max Cloud Profile count reached.<br>Please remove one or more profiles before creating new ones.',
	'cloud_ui_login' => 'Login',
	'cloud_ui_appliance_comment_update' => 'Update System description',
	'cloud_ui_appliance_comment_updated' => 'Update description of System %s',
	'cloud_ui_configure_application_ha' => 'Application Highavailability',
	'cloud_ui_collectd_graphs_available_soon' => 'System statistics will be available soon',
	'cloud_ui_collectd_graphs' => 'System statistics',
	'cloud_ui_appliance_resize' => 'Resize System disk',
	'cloud_ui_appliance_private_image' => 'Create a private Image from this System',
	'cloud_ui_appliance_resized' => 'Resized disk of System %s',
	'cloud_ui_appliance_resize_size_equal' => 'New Disk size is equal as the current disk size of System %s. Not resizing!',
	'cloud_ui_appliance_resize_size_smaller' => 'New Disk size for System %s needs to be greater as the current disk size. Not resizing!',
	'cloud_ui_appliance_command_running' => 'Another Cloud command is already registerd for System %s. Please wait until it got executed!',
	'cloud_ui_appliance_command_needs_active' => 'A Cloud command can only run on System %s when it is in active state.',
	'cloud_ui_appliance_private_created' => 'Creating a private Image %s from System %s. This will take a while.',
	'cloud_ui_appliance_create_private_image' => 'Create a private Image',
	'cloud_ui_images' => 'Images',
	'cloud_ui_image_clone_one_deploy' => 'Clone-on-Deploy',
	'cloud_ui_images_title' => 'Private Images',
	'cloud_ui_on' => 'On',
	'cloud_ui_off' => 'Off',
	'cloud_ui_edit' => 'Edit',
	'cloud_ui_remove' => 'Remove',
	'cloud_ui_image_remove' => 'Remove Private Image',
	'cloud_ui_image_removed' => 'Remove Private Image %s',
	'cloud_ui_image_still_active' => 'The Cloud Image %s is still active!',
	'cloud_ui_image_edit' => 'Edit Private Image %s',
	'cloud_ui_image_updated' => 'Updated Private Image %s',

	'cloud_ui_manager' => 'Available Cloud',
	'cloud_ui_name' => 'Name',
	'cloud_ui_city' => 'City',
	'cloud_ui_department' => 'Department',
	'cloud_ui_comment' => 'Comment',
	'cloud_ui_user_id' => 'User ID',
	'cloud_ui_user' => 'User',
	'cloud_ui_status' => 'Status',
	'cloud_ui_state' => 'State',
	'cloud_ui_address' => 'Adress',
	'cloud_ui_domain' => 'Domain',
	'cloud_ui_id' => 'ID',
	'cloud_ui_enabled' => 'Enabled',
	'cloud_ui_switchto' => 'Switch to',
	'cloud_ui_no_available' => 'Currently no Cloud available! Please try again later.',
	'cloud_ui_contact' => 'Contact',
	'cloud_ui_create' => 'Create',
	'cloud_ui_account' => 'Account',
	'cloud_ui_logout' => 'Logout',
	'cloud_ui_logout_help' => 'To complete your log out, please click OK then Cancel',
	'cloud_ui_user_managed_by_ldap' => 'Password is managed by LDAP',
	'cloud_ui_account_details' => 'Account Details',
	'cloud_ui_account_user_name' => 'Name',
	'cloud_ui_account_user_id' => 'ID',
	'cloud_ui_account_user' => 'User',
	'cloud_ui_account_user_forename' => 'Forename',
	'cloud_ui_account_user_lastname' => 'Lastname',
	'cloud_ui_account_user_email' => 'Email',
	'cloud_ui_account_user_address' => 'Adress',
	'cloud_ui_account_user_city' => 'City',
	'cloud_ui_account_user_state' => 'Country',
	'cloud_ui_account_user_country' => 'Country',
	'cloud_ui_account_user_phone' => 'Phone',
	'cloud_ui_account_user_ccunits' => 'CCUs',
	'cloud_ui_account_user_update_successful' => 'Successful updated Cloud User.',
	'cloud_ui_account_user_status' => 'Status',
	'cloud_ui_account_user_password' => 'Password',
	'cloud_ui_account_user_group' => 'Group',
	'cloud_ui_account_user_street' => 'Street',
	'cloud_ui_account_user_lang' => 'Language',
	'cloud_ui_account_user_permissions' => 'Permissions',
	'cloud_ui_portal' => 'Portal',
	'cloud_ui_portal_details' => 'Direct access to the Cloud Portal',
	'cloud_ui_create_request_title' => 'Create new System in the Cloud ',
	'cloud_ui_created_request' => 'Created new System in the Cloud ',
	'cloud_ui_saved_request' => 'Saved Request as Cloud Profile',
	'cloud_ui_where_you_are' => 'You are in:',
	'cloud_ui_requests' => 'Requests',
	'cloud_ui_requests_title' => 'Your Requests in the Cloud ',
	'cloud_ui_request_id' => 'ID',
	'cloud_ui_request_status' => 'Status',
	'cloud_ui_request_start' => 'Start',
	'cloud_ui_request_stop' => 'Stop',
	'cloud_ui_request_details' => 'Details',
	'cloud_ui_request_components_details' => 'Show/Hide Details',
	'cloud_ui_request_appliance_id' => 'System ID',
	'cloud_ui_request_appliance_name' => 'Hostname',
	'cloud_ui_request_appliance_state' => 'Status',
	'cloud_ui_request_appliance_comment' => 'Comment',
	'cloud_ui_request_resource_load' => 'Load',
	'cloud_ui_request_cloud_appliance_ip' => 'IP Adress',
	'cloud_ui_request_profiles' => 'Profiles',
	'cloud_ui_create_request_profile_small' => 'Small',
	'cloud_ui_create_request_profile_medium' => 'Medium',
	'cloud_ui_create_request_profile_big' => 'Big',
	'cloud_ui_request_system_type' => 'Type',
	'cloud_ui_request_os' => 'OS',
	'cloud_ui_request_template' => 'Template',
	'cloud_ui_request_memory' => 'RAM',
	'cloud_ui_request_cpu' => 'CPU',
	'cloud_ui_request_disk' => 'Disk',
	'cloud_ui_request_network' => 'Network',
	'cloud_ui_request_applications' => 'Applications',
	'cloud_ui_request_ha' => 'Highavailable',
	'cloud_ui_request_hostname' => 'Hostname',
	'cloud_ui_request_hostnames' => 'Hostnames',
	'cloud_ui_request_ccu_per_hour' => 'CCU/h',
	'cloud_ui_request_ccu_total' => 'Total',
	'cloud_ui_request_per_hour' => 'Per Hour',
	'cloud_ui_request_per_day' => 'Per Day',
	'cloud_ui_request_per_month' => 'Per Month',
	'cloud_ui_confirm_deprovision' => 'Deprovision the following requests?',
	'cloud_ui_deprovisioned' => 'Deprovisioned request ',
	'cloud_ui_deprovision_failed' => 'Deprovision failed for request ID ',
	'cloud_ui_confirm_restart' => 'Restart the following Systems?',
	'cloud_ui_restarted' => 'Restarted System ID ',
	'cloud_ui_restart_failed' => 'Restart failed for System ID ',
	'cloud_ui_confirm_pause' => 'Pause the following Systems?',
	'cloud_ui_paused' => 'Paused System ID ',
	'cloud_ui_pause_failed' => 'Pausing failed for System ID ',
	'cloud_ui_confirm_unpause' => 'Starting the following Systems?',
	'cloud_ui_unpaused' => 'Started System ID ',
	'cloud_ui_unpause_failed' => 'Start failed for System ID ',
	'cloud_ui_create_select_components' => 'Please select the System Components',
	'cloud_ui_create_system_components_details' => 'System Components Details',
	'cloud_ui_create_select_applications' => 'Application Configuration',
	'cloud_ui_create_select_ipaddresses' => 'IP-Addresses Configuration',
	'cloud_ui_transaction' => 'Transactions',
	'cloud_ui_transaction_id' => 'ID',
	'cloud_ui_transaction_management' => 'Cloud Transaction Management',
	'cloud_ui_transaction_time' => 'Time',
	'cloud_ui_transaction_cr_id' => 'CR',
	'cloud_ui_transaction_cu_name' => 'Name',
	'cloud_ui_transaction_ccu_charge' => 'Charge',
	'cloud_ui_transaction_ccu_balance' => 'Balance',
	'cloud_ui_transaction_reason' => 'Reason',
	'cloud_ui_transaction_comment' => 'Comment',
	'cloud_ui_transaction_ct_id' => 'local ID',
	'cloud_ui_appliances' => 'Systems',
	'cloud_ui_appliances_title' => 'Your Systems in the Cloud ',
	'cloud_ui_request_resource_memtotal' => 'RAM (total)',
	'cloud_ui_request_resource_memused' => 'RAM (used)',
	'cloud_ui_custom_hostnames' => 'Custom Hostnames',
	'cloud_ui_language' => 'Language',
	'cloud_ui_language_swiss' => 'Swiss',
	'cloud_ui_language_german' => 'German',
	'cloud_ui_language_english' => 'English',
	'cloud_ui_language_spain' => 'Spanisch',
	'cloud_ui_language_french' => 'French',
	'cloud_ui_language_italian' => 'Italian',
	'cloud_ui_language_netherlands' => 'Netherlands',

);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($user = NULL) {
		$this->identifier_name	= "cloud";
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		$this->rootdir			= $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
		$this->userdir			= $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/user';
		$this->tpldir           = $this->userdir."/tpl";
		$this->clouduser        = $user;
		require_once $this->rootdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->rootdir."/class/htmlobjects/");
		require_once $this->rootdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloudconfig	= new cloudconfig();
		$this->response = $this->html->response('cloud_ui_controller');
		if (isset($user)) {
			$this->html->lang = $this->clouduser->translate($this->html->lang, $this->userdir."/lang", 'htmlobjects.ini');
		}

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "home";
		}

		$private_image_enabled = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_private_image'), "true")) {
			$private_image_enabled = true;
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'home':
				$content[] = $this->home(true);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'create':
				$content[] = $this->home(false);
				$content[] = $this->create(true);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'requests':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(true);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'deprovision':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->deprovision(true);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'appliances':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'appliance_comment':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliance_comment(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'appliance_resize':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliance_resize(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'appliance_private':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliance_private(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'login':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->deprovision(false);
				$content[] = $this->login(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'restart':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->deprovision(false);
				$content[] = $this->restart(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'pause':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->deprovision(false);
				$content[] = $this->pause(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'unpause':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->deprovision(false);
				$content[] = $this->unpause(true);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'account':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(true);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'profiles':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(true);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'profile_upload':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profile_upload(true);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'profile_remove':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profile_remove(true);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'profile_comment':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profile_comment(true);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'images':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(true);
				if ($private_image_enabled) {
					$content[] = $this->images(true);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'image_remove':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(true);
				if ($private_image_enabled) {
					$content[] = $this->image_remove(true);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'image_edit':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(true);
				if ($private_image_enabled) {
					$content[] = $this->image_edit(true);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(false);
			break;
			case 'transaction':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->requests(false);
				$content[] = $this->appliances(false);
				$content[] = $this->account(false);
				$content[] = $this->profiles(false);
				if ($private_image_enabled) {
					$content[] = $this->images(false);
				}
				$content[] = $this->vcd(false);
				$content[] = $this->vid(false);
				$content[] = $this->transaction(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * home
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function home( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.home.class.php');
			$controller = new cloud_ui_home($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang         = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_title'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'home' );
		$content['onclick'] = false;
		if($this->action === 'home'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * create
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function create( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.create.class.php');
			$controller = new cloud_ui_create($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_create'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'create' );
		$content['onclick'] = false;
		if($this->action === 'create'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * requests
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function requests( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.requests.class.php');
			$controller = new cloud_ui_requests($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_requests'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'requests' );
		$content['onclick'] = false;
		if($this->action === 'requests'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * deprovision
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function deprovision( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.deprovision.class.php');
			$controller = new cloud_ui_deprovision($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_requests'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'deprovision' );
		$content['onclick'] = false;
		if($this->action === 'deprovision'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * appliances
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function appliances( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.appliances.class.php');
			$controller = new cloud_ui_appliances($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'appliances' );
		$content['onclick'] = false;
		if($this->action === 'appliances'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * appliance_comment
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function appliance_comment( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.appliance.comment.class.php');
			$controller = new cloud_ui_appliance_comment($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'appliance_comment' );
		$content['onclick'] = false;
		if($this->action === 'appliance_comment'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * appliance_resize
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function appliance_resize( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.appliance.resize.class.php');
			$controller = new cloud_ui_appliance_resize($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'appliance_resize' );
		$content['onclick'] = false;
		if($this->action === 'appliance_resize'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * appliance_private
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function appliance_private( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.appliance.private.class.php');
			$controller = new cloud_ui_appliance_private($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'appliance_private' );
		$content['onclick'] = false;
		if($this->action === 'appliance_private'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * login
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function login( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.login.class.php');
			$controller = new cloud_ui_login($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'login' );
		$content['onclick'] = false;
		if($this->action === 'login'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * restart
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function restart( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.restart.class.php');
			$controller = new cloud_ui_restart($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'restart' );
		$content['onclick'] = false;
		if($this->action === 'restart'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * pause
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function pause( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.pause.class.php');
			$controller = new cloud_ui_pause($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'pause' );
		$content['onclick'] = false;
		if($this->action === 'pause'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * unpause
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function unpause( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.unpause.class.php');
			$controller = new cloud_ui_unpause($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_appliances'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'unpause' );
		$content['onclick'] = false;
		if($this->action === 'unpause'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * account
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function account( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.account.class.php');
			$controller = new cloud_ui_account($this->response, $this->user);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_account'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'account' );
		$content['onclick'] = false;
		if($this->action === 'account'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * profiles
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function profiles( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.profiles.class.php');
			$controller = new cloud_ui_profiles($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_profiles'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'profiles' );
		$content['onclick'] = false;
		if($this->action === 'profiles'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * profile_upload
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function profile_upload( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.profile.upload.class.php');
			$controller = new cloud_ui_profile_upload($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_profile_icon'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'profile_upload' );
		$content['onclick'] = false;
		if($this->action === 'profile_upload'){
			$content['active']  = true;
		}
		return $content;
	}

	
	//--------------------------------------------
	/**
	 * profile_remove
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function profile_remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.profile.remove.class.php');
			$controller = new cloud_ui_profile_remove($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_profile_remove'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'profile_remove' );
		$content['onclick'] = false;
		if($this->action === 'profile_remove'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * profile_comment
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function profile_comment( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.profile.comment.class.php');
			$controller = new cloud_ui_profile_comment($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_profile_edit'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'profile_comment' );
		$content['onclick'] = false;
		if($this->action === 'profile_comment'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * images
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function images( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.images.class.php');
			$controller = new cloud_ui_images($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_images'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'images' );
		$content['onclick'] = false;
		if($this->action === 'images'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * image_remove
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function image_remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.image.remove.class.php');
			$controller = new cloud_ui_image_remove($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_images'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'image_remove' );
		$content['onclick'] = false;
		if($this->action === 'image_remove'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * image_edit
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function image_edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.image.edit.class.php');
			$controller = new cloud_ui_image_edit($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_images'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'image_edit' );
		$content['onclick'] = false;
		if($this->action === 'image_edit'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * transaction
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function transaction( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-ui.transaction.class.php');
			$controller = new cloud_ui_transaction($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->clouduser       = $this->clouduser;
			$controller->cloudconfig     = $this->cloudconfig;
			$controller->message_param   = $this->message_param;
			$controller->basedir         = $this->basedir;
			$controller->rootdir         = $this->rootdir;
			$controller->userdir         = $this->userdir;
			$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_transaction'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'transaction' );
		$content['onclick'] = false;
		if($this->action === 'transaction'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * vcd
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vcd( $hidden = true ) {
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_vcd'];
		$content['value']   = '';
		$content['target']  = "javascript:openVcd('vcd/index.php');";
		$content['request'] = false;
		$content['onclick'] = false;
		if($this->action === 'vcd'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * vid
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function vid( $hidden = true ) {
		$this->lang			= $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$content['label']   = $this->lang['cloud_ui_vid'];
		$content['value']   = '';
		$content['target']  = "javascript:openVid('vid/index.php');";
		$content['request'] = false;
		$content['onclick'] = false;
		if($this->action === 'vid'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Api
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function api() {
		require_once($this->userdir.'/class/cloud-ui.api.class.php');
		$controller = new cloud_api($this);
		$controller->cloudconfig     = $this->cloudconfig;
		$controller->basedir         = $this->basedir;
		$controller->rootdir         = $this->rootdir;
		$controller->userdir         = $this->userdir;
		$controller->clouduser       = $this->clouduser;
		$controller->lang            = $this->clouduser->translate($this->lang, $this->userdir."/lang", 'cloud-ui.ini');
		$controller->action();
	}

}
?>
