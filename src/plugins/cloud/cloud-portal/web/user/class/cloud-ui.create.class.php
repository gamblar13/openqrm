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
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/kernel.class.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/appliance.class.php";
require_once $RootDir."/class/deployment.class.php";

class cloud_ui_create
{

var $identifier_name;
var $lang;
var $actions_name;

var $cloud_max_applications = 20;
var $cloud_max_network = 4;

/**
* user
* @access public
* @var string
*/
var $user;
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

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		// include classes and prepare ojects
		require_once $this->rootdir."/plugins/cloud/class/clouduserslimits.class.php";
		$this->clouduserlimits	= new clouduserlimits();
		require_once $this->rootdir."/plugins/cloud/class/cloudselector.class.php";
		$this->cloudselector	= new cloudselector();
		require_once $this->rootdir."/plugins/cloud/class/cloudprivateimage.class.php";
		$this->cloudprivateimage	= new cloudprivateimage();
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloudrequest	= new cloudrequest();
		require_once $this->rootdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloudmailer	= new cloudmailer();
		require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
		$this->cloudprofile	= new cloudprofile();

	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->create();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'requests', $this->message_param, $response->msg));
		}
		$template = $this->response->html->template("./tpl/cloud-ui.create.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_create_request_title'], 'title');
		$template->add($this->lang['cloud_ui_create_select_components'], 'components');
		$template->add($this->lang['cloud_ui_create_select_applications'], 'applications');
		$template->add($this->lang['cloud_ui_request_applications'], 'cloud_applications');
		$template->add($this->lang['cloud_ui_create_system_components_details'], 'components_details');
		$template->add($this->lang['cloud_ui_request_components_details'], 'show_details');
		$template->add($this->lang['cloud_ui_request_ccu_per_hour'], 'ccu_per_hour');
		$template->add($this->lang['cloud_ui_request_ccu_total'], 'ccu_total');
		$template->add($this->lang['cloud_ui_request_per_hour'], 'per_hour');
		$template->add($this->lang['cloud_ui_request_per_day'], 'per_day');
		$template->add($this->lang['cloud_ui_request_per_month'], 'per_month');
		$template->add($this->lang['cloud_ui_create_select_ipaddresses'], 'ipaddresses');
		$template->add($response->form->get_elements());
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * create
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function create() {
		$errors  = array();
		$message = array();
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			if(!$form->get_errors()) {

//				print_r($data);
//				exit(0);

				$puppet_groups_str = '';
				$ip_mgmt_config_str = '';
				// add request
				$now = $_SERVER['REQUEST_TIME'];
				$cr["cr_cu_id"] = $this->clouduser->id;
				$stop_time = $now + 830000000;
				$cr['cr_start'] = $now;
				$cr['cr_request_time'] = $now;
				$cr['cr_stop'] = $stop_time;
				$cr['cr_resource_quantity'] = 1;
				// form data
				$cr['cr_resource_type_req'] = $data['cloud_virtualization_select'];
				$cr['cr_kernel_id'] = $data['cloud_kernel_select'];
				$cr['cr_image_id'] = $data['cloud_image_select'];
				$cr['cr_ram_req'] = $data['cloud_memory_select'];
				$cr['cr_cpu_req'] = $data['cloud_cpu_select'];
				$cr['cr_disk_req'] = $data['cloud_disk_select'];
				$cr['cr_network_req'] = $data['cloud_network_select'];
				// hostname input
				if (isset($data['cloud_hostname_input'])) {
					$cr['cr_appliance_hostname'] = $data['cloud_hostname_input'];
				}
				// apps
				for ($a = 0; $a < $this->cloud_max_applications; $a++) {
					if (isset($data['cloud_puppet_select_'.$a])) {
						$puppet_groups_str .= $data['cloud_puppet_select_'.$a].",";
					}
				}
				$puppet_groups_str = rtrim($puppet_groups_str, ",");
				$cr['cr_puppet_groups'] = $puppet_groups_str;
				// ips
				$max_network_interfaces = $this->cloudconfig->get_value_by_key('max_network_interfaces');
				for ($a = 0; $a <= $max_network_interfaces; $a++) {
					if (isset($data['cloud_ip_select_'.$a])) {
						$ip_mgmt_id = $data['cloud_ip_select_'.$a];
						if ($ip_mgmt_id != -1) {
							$nic_no = $a + 1;
							$ip_mgmt_config_str .= $nic_no.":".$ip_mgmt_id.",";
						}
					}
				}
				$ip_mgmt_config_str = rtrim($ip_mgmt_config_str, ",");
				$cr['cr_ip_mgmt'] = $ip_mgmt_config_str;
				// ha
				if (isset($data['cloud_ha_select'])) {
					$cr["cr_ha_req"] = 1;
				}
				// clone on deploy
				$clone_on_deploy = $this->cloudconfig->get_value_by_key('default_clone_on_deploy');
				if (!strcmp($clone_on_deploy, "true")) {
				    $cr["cr_shared_req"] = 1;
				} else {
				    $cr["cr_shared_req"] = 0;
				}
				
				// save as profile or request directly
				if (isset($data['cloud_profile_name'])) {
					$profile_name = $data['cloud_profile_name'];
					// check if profile name is existing already
					$this->cloudprofile->get_instance_by_name($profile_name);
					if (strlen($this->cloudprofile->name)) {
						$errors[] = sprintf($this->lang['cloud_ui_profile_name_in_use'], $profile_name);
					}
					// check max profile number
					$pr_count = $this->cloudprofile->get_count_per_user($this->clouduser->id);
					if ($pr_count >= $this->cloudprofile->max_profile_count) {
						$errors[] = $this->lang['cloud_ui_profile_max_reached'];
					}
					// add profile
					if(count($errors) === 0) {

						// remap fields from cr to pr
						$pr['pr_request_time'] = $cr['cr_request_time'];
						$pr['pr_start'] = $cr['cr_start'];
						$pr['pr_stop'] = $cr['cr_stop'];
						$pr['pr_kernel_id'] = $cr['cr_kernel_id'];
						$pr['pr_image_id'] = $cr['cr_image_id'];
						$pr['pr_ram_req'] = $cr['cr_ram_req'];
						$pr['pr_cpu_req'] = $cr['cr_cpu_req'];
						$pr['pr_disk_req'] = $cr['cr_disk_req'];
						$pr['pr_network_req'] = $cr['cr_network_req'];
						$pr['pr_resource_quantity'] = $cr['cr_resource_quantity'];
						$pr['pr_resource_type_req'] = $cr['cr_resource_type_req'];
						$pr['pr_ha_req'] = $cr['cr_ha_req'];
						$pr['pr_shared_req'] = $cr['cr_shared_req'];
						$pr['pr_puppet_groups'] = $cr['cr_puppet_groups'];
						$pr['pr_ip_mgmt'] = $cr['cr_ip_mgmt'];
						$pr['pr_name'] = $profile_name;
						// hostname
						if (isset($cr['cr_appliance_hostname'])) {
							$pr['pr_appliance_hostname'] = $cr['cr_appliance_hostname'];
						}
						$pr['pr_cu_id'] = $this->clouduser->id;
						$pr['pr_id'] = openqrm_db_get_free_id('pr_id', $this->cloudprofile->_db_table);
						$this->cloudprofile->add($pr);
						
						$response->msg = $this->lang['cloud_ui_saved_request'];
					} else {
						$msg = array_merge($errors, $message);
						$response->error = join('<br>', $msg);
					}
					
				} else {
					
					// $cr['cr_id'] = openqrm_db_get_free_id('cr_id', $this->cloudrequest->_db_table);
					$cr['cr_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$this->cloudrequest->add($cr);
					// mail to admin
					$cc_admin_email = $this->cloudconfig->get_value_by_key('cloud_admin_email');
					$this->cloudmailer->to = $cc_admin_email;
					$this->cloudmailer->from = $cc_admin_email;
					$this->cloudmailer->subject = "openQRM Cloud: New request from user ".$this->clouduser->name;
					$this->cloudmailer->template = $this->basedir."/plugins/cloud/etc/mail/new_cloud_request.mail.tmpl";
					$arr = array('@@USER@@' => $this->clouduser->name, '@@ID@@' => $cr['cr_id'], '@@OPENQRM_SERVER_IP_ADDRESS@@' => $_SERVER['SERVER_NAME']);
					$this->cloudmailer->var_array = $arr;
					$this->cloudmailer->send();
					// success msg
					$response->msg = $this->lang['cloud_ui_created_request'];
				}
			}
		}
		return $response;
	}



	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "create");

		// array of the available profiles
		$cloud_profile_select_arr[] = array("value" => '1', "label" => $this->lang['cloud_ui_create_request_profile_small']);
		$cloud_profile_select_arr[] = array("value" => '2', "label" => $this->lang['cloud_ui_create_request_profile_medium']);
		$cloud_profile_select_arr[] = array("value" => '3', "label" => $this->lang['cloud_ui_create_request_profile_big']);

		// pre-define select arrays
		$kernel_list = '';
		$cloud_image_select_arr = '';
		$cloud_virtualization_select_arr = '';
		$virtualization_list_select = '';
		$cloud_memory_select_arr = '';
		$cloud_cpu_select_arr = '';
		$cloud_disk_select_arr = '';
		$cloud_network_select_arr = '';
		$cloud_ha_select_arr = '';
		$cloud_puppet_select_arr = array();
		$ip_mgmt_list_per_user_arr = array();

		// global limits
		$max_resources_per_cr = 1;
		$max_disk_size = $this->cloudconfig->get_value_by_key('max_disk_size');
		$max_network_interfaces = $this->cloudconfig->get_value_by_key('max_network_interfaces');
		$max_apps_per_user = $this->cloudconfig->get_value_by_key('max_apps_per_user');
		// user limits
		$this->clouduserlimits->get_instance_by_cu_id($this->clouduser->id);
		$cloud_user_resource_limit = $this->clouduserlimits->resource_limit;
		$cloud_user_memory_limit = $this->clouduserlimits->memory_limit;
		$cloud_user_disk_limit = $this->clouduserlimits->disk_limit;
		$cloud_user_cpu_limit = $this->clouduserlimits->cpu_limit;
		$cloud_user_network_limit = $this->clouduserlimits->network_limit;

		// big switch ##############################################################
		//  : either show what is provided in the cloudselector
		//  : or show what is available
		// check if cloud_selector feature is enabled
		$cloud_selector_enabled = $this->cloudconfig->get_value_by_key('cloud_selector');	// cloud_selector
		if (!strcmp($cloud_selector_enabled, "true")) {
			// show what is provided by the cloudselectors

			// cpus
			$product_array = $this->cloudselector->display_overview_per_type("cpu");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_cpu = $cloudproduct["quantity"];
					if ($cloud_user_cpu_limit != 0) {
						 if ($cs_cpu <= $cloud_user_cpu_limit) {
							$available_cpunumber[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
						 }
					} else {
						$available_cpunumber[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
					}
				}
			}

			// disk size
			$product_array = $this->cloudselector->display_overview_per_type("disk");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_disk = $cloudproduct["quantity"];
					if ($cs_disk <= $max_disk_size) {
						if ($cloud_user_disk_limit != 0) {
							 if ($cs_disk <= $cloud_user_disk_limit) {
								$disk_size_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
							 }
						} else {
							$disk_size_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
						}
					}
				}
			}

			// ha
			// check if to show ha
			$show_ha = false;
			$show_ha_checkbox = $this->cloudconfig->get_value_by_key('show_ha_checkbox');	// show_ha_checkbox
			if (!strcmp($show_ha_checkbox, "true")) {
				// is ha enabled ?
				if (file_exists($this->rootdir."/plugins/highavailability/.running")) {
					$product_array = $this->cloudselector->display_overview_per_type("ha");
					foreach ($product_array as $index => $cloudproduct) {
						// is product enabled ?
						if ($cloudproduct["state"] == 1) {
							$show_ha = true;
						}
					}
				}
			}


			// kernel
			$product_array = $this->cloudselector->display_overview_per_type("kernel");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$kernel_list[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
				}
			}

			// memory sizes
			$product_array = $this->cloudselector->display_overview_per_type("memory");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_memory = $cloudproduct["quantity"];
					if ($cloud_user_memory_limit != 0) {
						 if ($cs_memory <= $cloud_user_memory_limit) {
							$available_memtotal[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
						 }
					} else {
						$available_memtotal[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
					}
				}
			}

			// network cards
			$product_array = $this->cloudselector->display_overview_per_type("network");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$cs_metwork = $cloudproduct["quantity"];
					if ($cs_metwork <= $max_network_interfaces) {
						if ($cloud_user_network_limit != 0) {
							 if ($cs_metwork <= $cloud_user_network_limit) {
								$max_network_interfaces_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
							 }
						} else {
							$max_network_interfaces_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
						}
					}
				}
			}

			// puppet classes
			// check if to show puppet
			$show_puppet_groups = $this->cloudconfig->get_value_by_key('show_puppet_groups');	// show_puppet_groups
			$show_puppet = '';
			if (!strcmp($show_puppet_groups, "true")) {
				// is puppet enabled ?
				if (file_exists($this->rootdir."/plugins/puppet/.running")) {
					$product_array = $this->cloudselector->display_overview_per_type("puppet");
					foreach ($product_array as $index => $cloudproduct) {
						// is product enabled ?
						if ($cloudproduct["state"] == 1) {
							$puppet_product_name = $cloudproduct["name"];
							$puppet_class_name = $cloudproduct["quantity"];
							$cloud_puppet_select_arr[] = array("value" => $puppet_class_name, "label" => $puppet_product_name);
							$product_puppet_description_arr[$puppet_product_name] = $cloudproduct["description"];
						}
					}
				}
			}

			// virtualization types
			$product_array = $this->cloudselector->display_overview_per_type("resource");
			foreach ($product_array as $index => $cloudproduct) {
				// is product enabled ?
				if ($cloudproduct["state"] == 1) {
					$virtualization_list_select[] = array("value" => $cloudproduct["quantity"], "label" => $cloudproduct["name"]);
				}
			}



		// else -> big switch ##############################################################
		} else {
			// show what is available in openQRM
			$kernel = new kernel();
			$kernel_list = array();
			$kernel_list = $kernel->get_list();
			// remove the openqrm kernelfrom the list
			// print_r($kernel_list);
			array_shift($kernel_list);

			// virtualization types
			$virtualization = new virtualization();
			$virtualization_list = array();
			$virtualization_list_select = array();
			$virtualization_list = $virtualization->get_list();
			// check if to show physical system type
			$cc_request_physical_systems = $this->cloudconfig->get_value_by_key('request_physical_systems');	// request_physical_systems
			if (!strcmp($cc_request_physical_systems, "false")) {
				array_shift($virtualization_list);
			}
			// filter out the virtualization hosts
			foreach ($virtualization_list as $id => $virt) {
				if (!strstr($virt['label'], "Host")) {
					$virtualization_list_select[] = array("value" => $virt['value'], "label" => $virt['label']);

				}
			}

			// prepare the array for the network-interface select
			$max_network_interfaces_select = array();
			$max_network_interfaces = $this->cloudconfig->get_value_by_key('max_network_interfaces');	// max_network_interfaces
			for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
				$max_network_interfaces_select[] = array("value" => $mnet, "label" => $mnet);
			}

			// get list of available resource parameters
			$resource_p = new resource();
			$resource_p_array = $resource_p->get_list();
			// remove openQRM resource
			array_shift($resource_p_array);
			// gather all available values in arrays
			$available_cpunumber_uniq = array();
			$available_cpunumber = array();
			$available_cpunumber[] = array("value" => "0", "label" => "any");
			$available_memtotal_uniq = array();
			$available_memtotal = array();
			$available_memtotal[] = array("value" => "0", "label" => "any");
			foreach($resource_p_array as $res) {
				$res_id = $res['resource_id'];
				$tres = new resource();
				$tres->get_instance_by_id($res_id);
				if ((strlen($tres->cpunumber)) && (!in_array($tres->cpunumber, $available_cpunumber_uniq))) {
					$available_cpunumber[] = array("value" => $tres->cpunumber, "label" => $tres->cpunumber." CPUs");
					$available_cpunumber_uniq[] .= $tres->cpunumber;
				}
				if ((strlen($tres->memtotal)) && (!in_array($tres->memtotal, $available_memtotal_uniq))) {
					$available_memtotal[] = array("value" => $tres->memtotal, "label" => $tres->memtotal." MB");
					$available_memtotal_uniq[] .= $tres->memtotal;
				}
			}

			// disk size select
			$disk_size_select[] = array("value" => 1000, "label" => '1 GB');
			if (2000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 2000, "label" => '2 GB');
			}
			if (3000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 3000, "label" => '3 GB');
			}
			if (4000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 4000, "label" => '4 GB');
			}
			if (5000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 5000, "label" => '5 GB');
			}
			if (10000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 10000, "label" => '10 GB');
			}
			if (20000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 20000, "label" => '20 GB');
			}
			if (50000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 50000, "label" => '50 GB');
			}
			if (100000 <= $max_disk_size) {
				$disk_size_select[] = array("value" => 100000, "label" => '100 GB');
			}

			// check if to show puppet
			$show_puppet_groups = $this->cloudconfig->get_value_by_key('show_puppet_groups');	// show_puppet_groups
			if (!strcmp($show_puppet_groups, "true")) {
				// is puppet enabled ?
				if (file_exists($this->rootdir."/plugins/puppet/.running")) {
					require_once $this->rootdir."/plugins/puppet/class/puppet.class.php";
					$puppet_group_dir = $this->rootdir."/plugins/puppet/puppet/manifests/groups";
					global $puppet_group_dir;
					$puppet_group_array = array();
					$puppet = new puppet();
					$puppet_group_array = $puppet->get_available_groups();
					foreach ($puppet_group_array as $index => $puppet_g) {
						$puid=$index+1;
						$puppet_info = $puppet->get_group_info($puppet_g);
						$cloud_puppet_select_arr[] = array("value" => $puppet_g, "label" => $puppet_g);
						$product_puppet_description_arr[$puppet_g] = $puppet_info;
					}
				}
			}

			// check if to show ha
			$show_ha_checkbox = $this->cloudconfig->get_value_by_key('show_ha_checkbox');	// show_ha_checkbox
			if (!strcmp($show_ha_checkbox, "true")) {
				// is ha enabled ?
				if (file_exists($this->rootdir."/plugins/highavailability/.running")) {
					$show_ha = true;
				}
			}


		// end of big switch #######################################################
		}

		// show available images or private images which are enabled
		$image = new image();
		$image_list = array();
		$image_list_tmp = array();
		$image_list_tmp = $image->get_list();
		// remove the openqrm + idle image from the list
		//print_r($image_list);
		array_shift($image_list_tmp);
		array_shift($image_list_tmp);
		// check if private image feature is enabled
		$show_private_image = $this->cloudconfig->get_value_by_key('show_private_image');	// show_private_image
		if (!strcmp($show_private_image, "true")) {
			// private image feature enabled
			$private_image_list = $this->cloudprivateimage->get_all_ids();
			foreach ($private_image_list as $index => $cpi) {
				$cpi_id = $cpi["co_id"];
				$this->cloudprivateimage->get_instance_by_id($cpi_id);
				if ($this->clouduser->id == $this->cloudprivateimage->cu_id) {
					$priv_im = new image();
					$priv_im->get_instance_by_id($this->cloudprivateimage->image_id);
					// do not show active images
					if ($priv_im->isactive == 1) {
						continue;
					}
					// only show the non-shared image to the user if it is not attached to a resource
					// because we don't want users to assign the same image to two appliances
					$priv_cloud_im = new cloudimage();
					$priv_cloud_im->get_instance_by_image_id($this->cloudprivateimage->image_id);
					if($priv_cloud_im->resource_id == 0 || $priv_cloud_im->resource_id == -1) {
							$image_list[] = array("value" => $priv_im->id, "label" => $priv_im->name);
					}
				} else if ($this->cloudprivateimage->cu_id == 0) {
					$priv_im = new image();
					$priv_im->get_instance_by_id($this->cloudprivateimage->image_id);
					if ($priv_im->isactive == 1) {
						continue;
					}
					$image_list[] = array("value" => $priv_im->id, "label" => $priv_im->name);
				}
			}

		} else {
			// private image feature is not enabled
			// do not show the image-clones from other requests
			foreach($image_list_tmp as $list) {
				$iname = $list['label'];
				$iid = $list['value'];
				$iimage = new image();
				$iimage->get_instance_by_id($iid);
				// do not show active images
				if ($iimage->isactive == 1) {
					continue;
				}
				if (!strstr($iname, ".cloud_")) {
					$image_list[] = array("value" => $iid, "label" => $iname);
				}
			}
		}

		// check ip-mgmt
		$show_ip_mgmt = $this->cloudconfig->get_value_by_key('ip-management');	// ip-mgmt enabled ?
		$ip_mgmt_select = '';
		$ip_mgmt_title = '';
		if (!strcmp($show_ip_mgmt, "true")) {
			if (file_exists($this->rootdir."/plugins/ip-mgmt/.running")) {
				require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
				$ip_mgmt = new ip_mgmt();
				$ip_mgmt_list_per_user = $ip_mgmt->get_list_by_user($this->clouduser->cg_id);
				$ip_mgmt_list_per_user_arr[] = array("value" => -2, "label" => "Auto");
				$ip_mgmt_list_per_user_arr[] = array("value" => -1, "label" => "None");
				foreach($ip_mgmt_list_per_user as $list) {
					$ip_mgmt_id = $list['ip_mgmt_id'];
					$ip_mgmt_name = trim($list['ip_mgmt_name']);
					$ip_mgmt_address = trim($list['ip_mgmt_address']);
					$ip_mgmt_list_per_user_arr[] = array("value" => $ip_mgmt_id, "label" => $ip_mgmt_name."-".$ip_mgmt_address);
				}
			}
		}

		// check if cloud_selector feature is enabled
		$cloud_appliance_hostname = '';
		$cloud_appliance_hostname_input = '';
		$cloud_appliance_hostname_help = '';
		$cloud_appliance_hostname_enabled = $this->cloudconfig->get_value_by_key('appliance_hostname');	// appliance_hostname
		if (!strcmp($cloud_appliance_hostname_enabled, "true")) {
			$cloud_appliance_hostname = 'Hostname setup';
//			$cloud_appliance_hostname_input = htmlobject_input('cr_appliance_hostname', array("value" => '', "label" => ' '), 'text', 10);
			$cloud_appliance_hostname_help = '<small>Multiple appliances get the postfix <b>_[#no]</b></small>';
		}

		$cloud_memory_select_arr = array();
		if(isset($available_memtotal)) {
			$cloud_memory_select_arr = $available_memtotal;
		}
		$cloud_disk_select_arr = array();
		if(isset($disk_size_select)) {
			$cloud_disk_select_arr = $disk_size_select;
		}
		$cloud_image_select_arr = $image_list;
		$cloud_virtualization_select_arr = $virtualization_list_select;
		$cloud_cpu_select_arr = $available_cpunumber;
		$cloud_network_select_arr = $max_network_interfaces_select;
		$cloud_ha_select_arr = $show_ha;
		$cloud_kernel_select_arr = $kernel_list;

		$d = array();
		$d['cloud_profile_list']['label']                     = $this->lang['cloud_ui_request_profiles'];
		$d['cloud_profile_list']['object']['type']            = 'htmlobject_select';
		$d['cloud_profile_list']['object']['attrib']['type']  = 'text';
		$d['cloud_profile_list']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_profile_list']['object']['attrib']['id']    = "cloud_profile_select";
		$d['cloud_profile_list']['object']['attrib']['name']  = "cloud_profile_select";
		$d['cloud_profile_list']['object']['attrib']['options']    = $cloud_profile_select_arr;

		$d['cloud_virtualization_select']['label']                          = $this->lang['cloud_ui_request_system_type'];
		$d['cloud_virtualization_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_virtualization_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_virtualization_select']['object']['attrib']['id']         = 'cloud_virtualization_select';
		$d['cloud_virtualization_select']['object']['attrib']['name']       = 'cloud_virtualization_select';
		$d['cloud_virtualization_select']['object']['attrib']['options']    = $cloud_virtualization_select_arr;

		$d['cloud_kernel_select']['label']                          = $this->lang['cloud_ui_request_os'];
		$d['cloud_kernel_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_kernel_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_kernel_select']['object']['attrib']['id']         = 'cloud_kernel_select';
		$d['cloud_kernel_select']['object']['attrib']['name']       = 'cloud_kernel_select';
		$d['cloud_kernel_select']['object']['attrib']['options']    = $cloud_kernel_select_arr;

		$d['cloud_image_select']['label']                          = $this->lang['cloud_ui_request_template'];
		$d['cloud_image_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_image_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_image_select']['object']['attrib']['id']         = 'cloud_image_select';
		$d['cloud_image_select']['object']['attrib']['name']       = 'cloud_image_select';
		$d['cloud_image_select']['object']['attrib']['options']    = $cloud_image_select_arr;

		$d['cloud_memory_select']['label']                          = $this->lang['cloud_ui_request_memory'];
		$d['cloud_memory_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_memory_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_memory_select']['object']['attrib']['id']         = 'cloud_memory_select';
		$d['cloud_memory_select']['object']['attrib']['name']       = 'cloud_memory_select';
		$d['cloud_memory_select']['object']['attrib']['options']    = $cloud_memory_select_arr;

		$d['cloud_cpu_select']['label']                          = $this->lang['cloud_ui_request_cpu'];
		$d['cloud_cpu_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_cpu_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_cpu_select']['object']['attrib']['id']         = 'cloud_cpu_select';
		$d['cloud_cpu_select']['object']['attrib']['name']       = 'cloud_cpu_select';
		$d['cloud_cpu_select']['object']['attrib']['options']    = $cloud_cpu_select_arr;

		$d['cloud_disk_select']['label']                          = $this->lang['cloud_ui_request_disk'];
		$d['cloud_disk_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_disk_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_disk_select']['object']['attrib']['id']         = 'cloud_disk_select';
		$d['cloud_disk_select']['object']['attrib']['name']       = 'cloud_disk_select';
		$d['cloud_disk_select']['object']['attrib']['options']    = $cloud_disk_select_arr;

		$d['cloud_network_select']['label']                          = $this->lang['cloud_ui_request_network'];
		$d['cloud_network_select']['object']['type']                 = 'htmlobject_select';
		$d['cloud_network_select']['object']['attrib']['index'] = array('value', 'label');
		$d['cloud_network_select']['object']['attrib']['id']         = 'cloud_network_select';
		$d['cloud_network_select']['object']['attrib']['name']       = 'cloud_network_select';
		$d['cloud_network_select']['object']['attrib']['options']    = $cloud_network_select_arr;

		// puppet apps
		$product_loop = 0;
		if (count($cloud_puppet_select_arr) > 0) {
			foreach($cloud_puppet_select_arr as $application) {
				$product_name = $application['label'];
				$product_description = $application['value'];
				$d['cloud_puppet_select_'.$product_loop]['label']                          = $product_name;
				$d['cloud_puppet_select_'.$product_loop]['object']['type']                 = 'htmlobject_input';
				$d['cloud_puppet_select_'.$product_loop]['object']['attrib']['type']		  = 'checkbox';
				$d['cloud_puppet_select_'.$product_loop]['object']['attrib']['id']         = 'cloud_puppet_select'.$product_loop;
				$d['cloud_puppet_select_'.$product_loop]['object']['attrib']['name']       = 'cloud_puppet_select_'.$product_loop;
				$d['cloud_puppet_select_'.$product_loop]['object']['attrib']['value']       = $product_description;
				$d['cloud_puppet_select_'.$product_loop]['object']['attrib']['title']       = $product_puppet_description_arr[$product_name];
				$product_loop++;
			}
			for ($f = $product_loop; $f < $this->cloud_max_applications; $f++) {
				$d['cloud_puppet_select_'.$f]                          = ' ';
			}
		} else {
			for ($f = $product_loop; $f < $this->cloud_max_applications; $f++) {
				$d['cloud_puppet_select_'.$f]                          = ' ';
			}
		}

		// ips
		$ip_loop = 0;
		if (count($ip_mgmt_list_per_user_arr) > 0) {
			for($i = 0; $i < $max_network_interfaces; $i++) {
				$nic_no = $ip_loop + 1;
				$d['cloud_ip_select_'.$ip_loop]['label']                          = $this->lang['cloud_ui_request_network']." ".$nic_no;
				$d['cloud_ip_select_'.$ip_loop]['object']['type']                 = 'htmlobject_select';
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['index'] = array('value', 'label');
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['id']         = 'cloud_ip_select_'.$ip_loop;
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['name']       = 'cloud_ip_select_'.$ip_loop;
				$d['cloud_ip_select_'.$ip_loop]['object']['attrib']['options']       = $ip_mgmt_list_per_user_arr;
				$ip_loop++;
			}
			for ($f = $ip_loop; $f < 4; $f++) {
				$d['cloud_ip_select_'.$f]                          = ' ';
			}
		} else {
			for ($f = $ip_loop; $f < 4; $f++) {
				$d['cloud_ip_select_'.$f]                          = ' ';
			}
		}

		// ha
		if ($cloud_ha_select_arr) {
			$d['cloud_ha_select']['label']                          = $this->lang['cloud_ui_request_ha'];
			$d['cloud_ha_select']['object']['type']                 = 'htmlobject_input';
			$d['cloud_ha_select']['object']['attrib']['type']		  = 'checkbox';
			$d['cloud_ha_select']['object']['attrib']['id']         = 'cloud_ha_select';
			$d['cloud_ha_select']['object']['attrib']['name']       = 'cloud_ha_select';
		} else {
			$d['cloud_ha_select']                          = ' ';
		}


		// check if user are allowed to set the hostname
		$cloud_user_hostnames = false;
		$cloud_user_hostnames = $this->cloudconfig->get_value_by_key('appliance_hostname');
		if (!strcmp($cloud_user_hostnames, "true")) {
			$d['cloud_hostname_input']['label']                     = $this->lang['cloud_ui_request_hostname'];
			$d['cloud_hostname_input']['required']                  = false;
			$d['cloud_hostname_input']['validate']['regex']         = '~^[a-z0-9]+$~i';
			$d['cloud_hostname_input']['validate']['errormsg']      = 'Hostname must be [a-z] only';
			$d['cloud_hostname_input']['object']['type']            = 'htmlobject_input';
			$d['cloud_hostname_input']['object']['attrib']['type']  = 'text';
			$d['cloud_hostname_input']['object']['attrib']['id']    = 'cloud_hostname_input';
			$d['cloud_hostname_input']['object']['attrib']['name']  = 'cloud_hostname_input';
		} else {
			$d['cloud_hostname_input']                    = ' ';
		}

		// save as profile
		$d['cloud_profile_name']['label']                     = $this->lang['cloud_ui_save_as_profile']." ".$this->lang['cloud_ui_name'];
		$d['cloud_profile_name']['required']                  = false;
		$d['cloud_profile_name']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['cloud_profile_name']['validate']['errormsg']      = 'Profile name must be [a-z] only';
		$d['cloud_profile_name']['object']['type']            = 'htmlobject_input';
		$d['cloud_profile_name']['object']['attrib']['type']  = 'text';
		$d['cloud_profile_name']['object']['attrib']['id']    = 'cloud_profile_name';
		$d['cloud_profile_name']['object']['attrib']['name']  = 'cloud_profile_name';
		
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}

?>


