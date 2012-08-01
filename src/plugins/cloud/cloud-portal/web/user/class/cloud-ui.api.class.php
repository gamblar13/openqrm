<?php
/**
 * Openqrm Content
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class cloud_api
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* absolute path to webroot
* @access public
* @var string
*/
var $rootdir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param cloud_controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->response   = $this->controller->response;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get('action');
		switch( $action ) {
			case 'calculator':
				$this->cloud_cost_calculator();
			break;
			case 'request_details':
				$this->get_request_details();
			break;

		}
	}



	function cloud_cost_calculator() {

		require_once $this->rootdir."/plugins/cloud/class/cloudselector.class.php";
		$cloudselector = new cloudselector();

		require_once $this->rootdir."/class/kernel.class.php";
		require_once $this->rootdir."/class/virtualization.class.php";

		$virtualization_id = $this->response->html->request()->get('virtualization');
		$kernel_id = $this->response->html->request()->get('kernel');
		$memory_val = $this->response->html->request()->get('memory');
		$cpu_val = $this->response->html->request()->get('cpu');
		$disk_val = $this->response->html->request()->get('disk');
		$network_val = $this->response->html->request()->get('network');
		$ha_val = $this->response->html->request()->get('ha');
		$apps_val = $this->response->html->request()->get('apps');

		// resource type
		$cost_virtualization = 0;
		if (strlen($virtualization_id)) {
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($virtualization_id);
			$cost_virtualization = $cloudselector->get_price($virtualization->id, "resource");
		}
		// kernel
		$cost_kernel = 0;
		if (strlen($kernel_id)) {
			$kernel = new kernel();
			$kernel->get_instance_by_id($kernel_id);
			$cost_kernel = $cloudselector->get_price($kernel->id, "kernel");
		}
		// memory
		$cost_memory = 0;
		if (strlen($memory_val)) {
			$cost_memory = $cloudselector->get_price($memory_val, "memory");
		}
		// cpu
		$cost_cpu = 0;
		if (strlen($cpu_val)) {
			$cost_cpu = $cloudselector->get_price($cpu_val, "cpu");
		}
		// disk
		$cost_disk = 0;
		if (strlen($disk_val)) {
			$cost_disk = $cloudselector->get_price($disk_val, "disk");
		}
		// network
		$cost_network = 0;
		if (strlen($network_val)) {
			$cost_network = $cloudselector->get_price($network_val, "network");
		}

		// ha
		$cost_ha = 0;
		if ($ha_val == 1) {
			$cost_ha = $cloudselector->get_price($ha_val, "ha");
		}


		// puppet apps
		$cost_app_total = 0;
		if (strlen($apps_val)) {
			$apps_val = rtrim($apps_val, ':');
			$apps_val = ltrim($apps_val, ':');
			$application_array = explode(":", $apps_val);
			foreach ($application_array as $cloud_app) {
				$cost_app = $cloudselector->get_price($cloud_app, "puppet");
				$cost_app_total = $cost_app_total + $cost_app;
			}
		}

		// get cloud currency
		$cloud_currency = $this->cloudconfig->get_value_by_key('cloud_currency');   // 23 is cloud_currency
		$cloud_1000_ccus_value = $this->cloudconfig->get_value_by_key('cloud_1000_ccus');   // 24 is cloud_1000_ccus
 		// summary
		$summary_per_appliance = $cost_virtualization + $cost_kernel + $cost_memory + $cost_cpu + $cost_disk + $cost_network + $cost_app_total + $cost_ha;
		$one_ccu_cost_in_real_currency = $cloud_1000_ccus_value / 1000;
		$appliance_cost_in_real_currency_per_hour = $summary_per_appliance * $one_ccu_cost_in_real_currency;
		$appliance_cost_in_real_currency_per_hour_disp = number_format($appliance_cost_in_real_currency_per_hour, 2, ",", "");
		$appliance_cost_in_real_currency_per_day = $appliance_cost_in_real_currency_per_hour * 24;
		$appliance_cost_in_real_currency_per_day_disp = number_format($appliance_cost_in_real_currency_per_day, 2, ",", "");
		$appliance_cost_in_real_currency_per_month = $appliance_cost_in_real_currency_per_day * 31;
		$appliance_cost_in_real_currency_per_month_disp = number_format($appliance_cost_in_real_currency_per_month, 2, ",", "");

		// cost_virtualization,cost_kernel,cost_memory,cost_cpu,cost_disk,cost_network,cost_ha,cost_app_total,cloud_currency,summary_per_appliance,appliance_cost_in_real_currency_per_hour,appliance_cost_in_real_currency_per_day,appliance_cost_in_real_currency_per_month
		$cloudrequest_costs = $cost_virtualization.":".$cost_kernel.":".$cost_memory.":".$cost_cpu.":".$cost_disk.":".$cost_network.":".$cost_ha.":".$cost_app_total.":".$cloud_currency.":".$summary_per_appliance.":".$appliance_cost_in_real_currency_per_hour_disp.":".$appliance_cost_in_real_currency_per_day_disp.":".$appliance_cost_in_real_currency_per_month_disp;
		echo $cloudrequest_costs;


	}



	function get_request_details() {
	    
		require_once $this->rootdir."/class/kernel.class.php";
		require_once $this->rootdir."/class/image.class.php";
		require_once $this->rootdir."/class/virtualization.class.php";
		require_once $this->rootdir."/plugins/cloud/class/clouduser.class.php";
		require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
		
		$cr_id = $this->response->html->request()->get('cr_id');
		$this->cloud_request = new cloudrequest();
		$this->cloud_request->get_instance($cr_id);
		$cu = new clouduser();
		$cu->get_instance_by_id($this->cloud_request->cu_id);
		if ($this->cloud_request->cu_id != $this->clouduser->id) {
			// not request of the authuser
			exit(1);
		}
		$kernel = new kernel();
		$kernel->get_instance_by_id($this->cloud_request->kernel_id);
		$image = new image();
		$image->get_instance_by_id($this->cloud_request->image_id);
		$virtualization = new virtualization();
		$virtualization->get_instance_by_id($this->cloud_request->resource_type_req);

		// status
		switch ($this->cloud_request->status) {
			case 1:
				$cr_status="new";
				break;
			case 2:
				$cr_status="approve";
				break;
			case 3:
				$cr_status="active";
				break;
			case 4:
				$cr_status="deny";
				break;
			case 6:
				$cr_status="done";
				break;
			case 7:
				$cr_status="no-res";
				break;
		}
		$ha_req = '';
		if ((isset($this->cloud_request->ha_req)) && ($this->cloud_request->ha_req === 1)) {
			$ha_req = $this->lang['cloud_ui_enabled'];
		} else {
			$ha_req = '-';
		}
		$appliance_hostname = '';
		if (strlen($this->cloud_request->appliance_hostname)) {
			$appliance_hostname = $this->cloud_request->appliance_hostname;
		} else {
			$appliance_hostname = '-';
		}
		
		$appliance_applications = '';
		if (strlen($this->cloud_request->puppet_groups)) {
			$appliance_applications = $this->cloud_request->puppet_groups;
		} else {
			$appliance_applications = '-';
		}
		
		$appliance_ip_config = '';
		if (strlen($this->cloud_request->ip_mgmt)) {
			$ip_config_arr = explode(',', $this->cloud_request->ip_mgmt);
			foreach ($ip_config_arr as $ip) {
				$single_ip_config_arr = explode(':', $ip);
				$nic_no = $single_ip_config_arr[0];
				switch($single_ip_config_arr[1]) {
					case '-2':
						$appliance_ip_config .= $this->lang['cloud_ui_request_network'].' '.$nic_no.':auto<br>';
						break;
					default:
						$appliance_ip_config .= $this->lang['cloud_ui_request_network'].' '.$nic_no.':custom<br>';
						break;
				}
			}
		} else {
			$appliance_ip_config = '-';
		}

		$head['cr_key']['title'] = ' ';
		$head['cr_value']['title'] = ' ';
		
		$table = $this->response->html->tablebuilder( 'cloud-request-table', null);
		#$table->lang            = $this->locale->get_lang( 'tablebuilder.ini' );
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'cloud_request_details';
		$table->head            = $head;
		$table->sort_form       = false;
		$table->autosort        = false;
		$table->max				= 1;
		$table->init();

		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_id'],
			'cr_value' => $this->cloud_request->id,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_appliance_state'],
			'cr_value' => $cr_status,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_transaction_time'],
			'cr_value' => date("d-m-Y H-i", $this->cloud_request->request_time),
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_start'],
			'cr_value' => date("d-m-Y H-i", $this->cloud_request->start),
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_stop'],
			'cr_value' =>date("d-m-Y H-i", $this->cloud_request->stop),
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_os'],
			'cr_value' => $kernel->name,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_template'],
			'cr_value' => $image->name,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_cpu'],
			'cr_value' => $this->cloud_request->cpu_req,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_memory'],
			'cr_value' => $this->cloud_request->ram_req." MB",
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_disk'],
			'cr_value' => $this->cloud_request->disk_req." MB",
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_network'],
			'cr_value' => $this->cloud_request->network_req,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_appliance_id'],
			'cr_value' => $this->cloud_request->appliance_id,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_system_type'],
			'cr_value' => $virtualization->name,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_ha'],
			'cr_value' => $ha_req,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_hostname'],
			'cr_value' => $appliance_hostname,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_applications'],
			'cr_value' => $appliance_applications,
		);
		$ta[] = array(
			'cr_key' => $this->lang['cloud_ui_request_cloud_appliance_ip'],
			'cr_value' => $appliance_ip_config,
		);
		
		$table->body = $ta;
		echo $table->get_string();
	}




}
?>
