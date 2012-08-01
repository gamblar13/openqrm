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


class cloud_ui_appliances
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';

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
	    $this->docrootdir = $_SERVER["DOCUMENT_ROOT"];
	    // include classes and prepare ojects
	    require_once $this->rootdir."/plugins/cloud/class/cloudappliance.class.php";
	    $this->cloudappliance	= new cloudappliance();
	    require_once $this->rootdir."/plugins/cloud/class/cloudrequest.class.php";
	    $this->cloudrequest	= new cloudrequest();
	    require_once $this->rootdir."/plugins/cloud/class/cloudnat.class.php";
	    $this->cloudnat	= new cloudnat();
	    require_once $this->rootdir."/plugins/cloud/class/cloudicon.class.php";
	    $this->cloudicon	= new cloudicon();
	    require_once $this->rootdir."/plugins/cloud/class/cloudimage.class.php";
	    $this->cloudimage	= new cloudimage();
	    require_once $this->rootdir."/class/appliance.class.php";
	    $this->appliance	= new appliance();
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
		$table = $this->select();
		$template = $this->response->html->template("./tpl/cloud-ui.appliances.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($table, 'table');
		$template->add($this->lang['cloud_ui_appliances_title'], 'title');
		$template->add($this->lang['cloud_ui_requests_title'], 'cr_details_title');
		return $template;
	}

	//--------------------------------------------
	/**
	 * select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {

	    $head['appliance_id']['title'] = $this->lang['cloud_ui_request_id'];
	    $head['appliance_icon']['title'] = '&#160;';
	    $head['appliance_icon']['sortable'] = false;
	    $head['appliance_state']['title'] = $this->lang['cloud_ui_request_appliance_state'];
	    $head['appliance_name']['title'] = $this->lang['cloud_ui_request_appliance_name'];
	    $head['appliance_config']['title'] = $this->lang['cloud_ui_request_details'];
	    $head['appliance_disk_size']['title'] = '(MB)';
	    $head['appliance_comment']['title'] = $this->lang['cloud_ui_request_appliance_comment'];
	    $head['appliance_cloud_action']['title'] = 'Actions';
	    
	    $table = $this->response->html->tablebuilder( 'cloud_table', $this->response->get_array($this->actions_name, 'appliances'));
	    $table->css             = 'htmlobject_table';
	    $table->border          = 0;
		$table->limit           = 10;
	    $table->id              = 'cloud_appliances';
	    $table->head            = $head;
	    $table->sort            = 'appliance_id';
	    $table->autosort        = true;
	    $table->sort_link       = false;
	    $table->actions_name    = $this->actions_name;
	    $table->form_action     = $this->response->html->thisfile;

	    $cloudreq_array = $this->cloudrequest->get_all_ids_per_user($this->clouduser->id);
	    $users_appliances = array();
	    // build an array of our appliance id's
	    foreach ($cloudreq_array as $cr) {
		$this->cloudrequest->get_instance_by_id($cr['cr_id']);
		if ((strlen($this->cloudrequest->appliance_id)) && ($this->cloudrequest->appliance_id != 0)) {
		    $one_app_id_arr = explode(",", $this->cloudrequest->appliance_id);
		    foreach ($one_app_id_arr as $aid) {
			$users_appliances[] .= $aid;
		    }
		}
	    }
		$show_ip_mgmt = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('ip-management'), "true")) {
			$show_ip_mgmt = true;
		}
		$sshterm_enabled = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_sshterm_login'), "true")) {
			$sshterm_enabled = true;
		}
		$show_application_ha = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_ha_checkbox'), "true")) {
			$show_application_ha = true;
		}
		$collectd_graph_enabled = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_collectd_graphs'), "true")) {
			$collectd_graph_enabled = true;
		}
		$disk_resize_enabled = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_disk_resize'), "true")) {
			$disk_resize_enabled = true;
		}
		$private_image_enabled = false;
	    if (!strcmp($this->cloudconfig->get_value_by_key('show_private_image'), "true")) {
			$private_image_enabled = true;
		}
		$show_pause_button = false;
		$show_unpause_button = false;
		$cloud_object_icon_size=48;

		// now we go over all our appliances from the users request list
	    $app_count = 0;
	    $ta = '';
	    foreach ($users_appliances as $appid) {
		    $this->appliance->get_instance_by_id($appid);

		    $sshterm_login = false;
		    $appliance_resources_str="";
		    $res_ip_loop = 0;
		    $resource = new resource();
		    $appliance_resources=$this->appliance->resources;
		    if ($appliance_resources >=0) {
			    // an appliance with a pre-selected resource
			    // get its ips from the ip-mgmt

			    // ######################## ip-mgmt find users ips ###############################
			    // here we check which ip to send to the user
			    // check ip-mgmt
			    $sshterm_login_ip = '';
			    if ($show_ip_mgmt) {
				    if (file_exists($this->rootdir."/plugins/ip-mgmt/.running")) {
					    require_once $this->rootdir."/plugins/ip-mgmt/class/ip-mgmt.class.php";
					    $ip_mgmt = new ip_mgmt();
					    $appliance_first_nic_ip_mgmt_id = $ip_mgmt->get_id_by_appliance($this->appliance->id, 1);
					    if ($appliance_first_nic_ip_mgmt_id > 0) {
						    $appliance_ip_mgmt_config_arr = $ip_mgmt->get_instance('id', $appliance_first_nic_ip_mgmt_id);
						    if (isset($appliance_ip_mgmt_config_arr['ip_mgmt_address'])) {
							    $sshterm_login_ip = $appliance_ip_mgmt_config_arr['ip_mgmt_address'];
							    $appliance_resources_str = $appliance_ip_mgmt_config_arr['ip_mgmt_address'];
							    $sshterm_login = true;
						    }
					    }
				    }
			    }

			    if (!strlen($sshterm_login_ip)) {
				    // in case no external ip was given to the appliance we show the internal ip
				    $resource->get_instance_by_id($this->appliance->resources);
				    $appliance_resources_str = $resource->ip;
				    $sshterm_login_ip =  $resource->ip;
				    $sshterm_login = true;
			    }

			    // check if we need to NAT the ip address
			    $cn_nat_enabled = $this->cloudconfig->get_value_by_key('cloud_nat');  // 18 is cloud_nat
			    if (!strcmp($cn_nat_enabled, "true")) {
				    $appliance_resources_str = $this->cloudnat->translate($appliance_resources_str);
				    $sshterm_login_ip = $this->cloudnat->translate($sshterm_login_ip);
			    }

		    } else {
			    // an appliance with resource auto-select enabled
			    $appliance_resources_str = "auto-select";
			    $sshterm_login = false;
		    }

		    // active or inactive
		    $resource_icon_default="/cloud-portal/img/resource.png";
		    $active_state_icon="/cloud-portal/img/active.png";
		    $inactive_state_icon="/cloud-portal/img/idle.png";
		    $starting_state_icon="/cloud-portal/img/starting.png";
		    if ($this->appliance->stoptime == 0 || $appliance_resources == 0)  {
			    $state_icon=$active_state_icon;
		    } else {
			    $state_icon=$inactive_state_icon;
			    $sshterm_login = false;
		    }
		    // state
		    $this->cloudappliance->get_instance_by_appliance_id($this->appliance->id);
		    switch ($this->cloudappliance->state) {
			    case 0:
				    $cloudappliance_state = "paused";
				    $sshterm_login = false;
				    $show_pause_button = false;
				    $show_unpause_button = true;
					$show_application_ha = false;
					$disk_resize_enabled = false;
					$private_image_enabled = false;
				    break;
			    case 1:
				    $cloudappliance_state = "active";
				    $sshterm_login = true;
					$show_application_ha = true;
				    $show_pause_button = true;
					$disk_resize_enabled = true;
					$private_image_enabled = true;
				    break;
		    }
		    // use resource-state in case of a starting appliance
			if (($this->appliance->resources > 0) && ($resource->imageid != 1)) {
				$resource->get_instance_by_id($this->appliance->resources);
				if (strcmp($resource->state, "active")) {
					$state_icon=$starting_state_icon;
					$sshterm_login = false;
					$show_pause_button = false;
					$show_unpause_button = false;
					$show_application_ha = false;
					$disk_resize_enabled = false;
					$private_image_enabled = false;
				}
			}
		    // check if we have a custom icon for the cloudappliance
		    $this->cloudicon->get_instance_by_details($this->clouduser->id, 2, $this->cloudappliance->id);
		    if (strlen($this->cloudicon->filename)) {
			    $resource_icon_default="custom-icons/".$this->cloudicon->filename;
		    }

		    $kernel = new kernel();
		    $kernel->get_instance_by_id($this->appliance->kernelid);
		    $image = new image();
		    $image->get_instance_by_id($this->appliance->imageid);
		    $virtualization = new virtualization();
		    $virtualization->get_instance_by_id($this->appliance->virtualization);
		    $appliance_virtualization_type=$virtualization->name;
		    // image disk size
		    $this->cloudimage->get_instance_by_image_id($image->id);
		    $cloud_image_disk_size = $this->cloudimage->disk_size;

		    // prepare actions
		    $cloudappliance_action = "";
		    // sshterm login
		    if ($sshterm_enabled) {
			    if (($sshterm_login) && (isset($sshterm_login_ip))) {

					$a = $this->response->html->a();
					$a->title   = $this->lang['cloud_ui_login'];
					$a->label   = $this->lang['cloud_ui_login'];
					$a->handler = "";
					$a->css     = 'console';
					$a->href    = '/cloud-portal/user/index.php?cloud_ui=login&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
					$cloudappliance_action .= '<div id="appliance_cloud_action">';
					$cloudappliance_action .= $a->get_string();
					$cloudappliance_action .= '</div>';
			    }
		    }
		    // application ha
		    if ($show_application_ha) {
			    $lcmc_gui="lcmc/lcmc-gui.php";
			    $icon_size = "width='21' height='21'";
			    $icon_title = $this->lang['cloud_ui_configure_application_ha'];
			    $lcmc_url = "<a style=\"text-decoration:none\" href=\"#\" onClick=\"javascript:window.open('$lcmc_gui','','location=0,status=0,scrollbars=1,width=1024,height=768,left=50,top=20,screenX=50,screenY=20');\">
				    <image border=\"0\" alt=\"".$icon_title."\" title=\"".$icon_title."\" src=\"../img/ha.png\">
				    </a>";

				$cloudappliance_action .= '<div id="appliance_cloud_action">';
			    $cloudappliance_action .= $lcmc_url;
				$cloudappliance_action .= '</div>';
		    }
		    // regular actions
		    if ($show_pause_button) {
				// pause
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_ui_confirm_pause'];
				$a->label   = $this->lang['cloud_ui_confirm_pause'];
				$a->handler = "";
				$a->css     = 'pause';
				$a->href    = '/cloud-portal/user/index.php?cloud_ui=pause&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
				$cloudappliance_action .= '<div id="appliance_cloud_action">';
				$cloudappliance_action .= $a->get_string();
				$cloudappliance_action .= '</div>';
				// restart
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_ui_confirm_restart'];
				$a->label   = $this->lang['cloud_ui_confirm_restart'];
				$a->handler = "";
				$a->css     = 'restart';
				$a->href    = '/cloud-portal/user/index.php?cloud_ui=restart&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
				$cloudappliance_action .= '<div id="appliance_cloud_action">';
				$cloudappliance_action .= $a->get_string();
				$cloudappliance_action .= '</div>';
		    }
		    if ($show_unpause_button) {
				// pause
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_ui_confirm_unpause'];
				$a->label   = $this->lang['cloud_ui_confirm_unpause'];
				$a->handler = "";
				$a->css     = 'unpause';
				$a->href    = '/cloud-portal/user/index.php?cloud_ui=unpause&'.$this->identifier_name.'[]='.$this->cloudappliance->id;
				$cloudappliance_action .= '<div id="appliance_cloud_action">';
				$cloudappliance_action .= $a->get_string();
				$cloudappliance_action .= '</div>';
		    }
		    if ($collectd_graph_enabled) {
				// system stats
			    $collectd_graph_link="/cloud-portal/user/users/".$this->clouduser->name."/".$this->appliance->name."/index.html";
			    if (file_exists($this->docrootdir.$collectd_graph_link)) {
					$a = $this->response->html->a();
					$a->title   = $this->lang['cloud_ui_collectd_graphs'];
					$a->label   = $this->lang['cloud_ui_collectd_graphs'];
					$a->handler = "";
					$a->css     = 'graphs';
					$a->href    = $collectd_graph_link;
					$a->target = '_blank';
					$cloudappliance_action .= '<div id="appliance_cloud_action">';
					$cloudappliance_action .= $a->get_string();
					$cloudappliance_action .= '</div>';

			    } else {
					$cloudappliance_action .= '<div id="appliance_cloud_action">';
				    $cloudappliance_action .= '<img src="../img/progress.gif" border="0" alt="'.$this->lang['cloud_ui_collectd_graphs_available_soon'].'" title="'.$this->lang['cloud_ui_collectd_graphs_available_soon'].'">&nbsp;';
					$cloudappliance_action .= '</div>';
			    }
		    }
			// disk resize
		    if ($disk_resize_enabled) {
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_ui_appliance_resize'];
				$a->label   = $this->lang['cloud_ui_appliance_resize'];
				$a->handler = "";
				$a->css     = 'resize';
				$a->href    = '/cloud-portal/user/index.php?cloud_ui=appliance_resize&'.$this->identifier_name.'='.$this->cloudappliance->id;
				$cloudappliance_action .= '<div id="appliance_cloud_action">';
				$cloudappliance_action .= $a->get_string();
				$cloudappliance_action .= '</div>';
			}
			// private images
		    if ($private_image_enabled) {
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_ui_appliance_private_image'];
				$a->label   = $this->lang['cloud_ui_appliance_private_image'];
				$a->handler = "";
				$a->css     = 'private';
				$a->href    = '/cloud-portal/user/index.php?cloud_ui=appliance_private&'.$this->identifier_name.'='.$this->cloudappliance->id;
				$cloudappliance_action .= '<div id="appliance_cloud_action">';
				$cloudappliance_action .= $a->get_string();
				$cloudappliance_action .= '</div>';
			}

			// appliance comment
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_appliance_comment_update'];
			$a->label   = $this->lang['cloud_ui_appliance_comment_update'];
			$a->handler = "";
			$a->css     = 'edit';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=appliance_comment&'.$this->identifier_name.'='.$this->cloudappliance->id;
			$cloudappliance_action .= '<div id="appliance_cloud_action">';
			$cloudappliance_action .= $a->get_string();
			$cloudappliance_action .= '</div>';

			// cr details
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_request_details'];
			$a->label   = $this->appliance->name;
			$a->href    = 'javascript:cloudopenPopup(\''.$this->cloudappliance->cr_id.'\');';
			$cr_details = $a->get_string();

		    $config_column = "<b>Kernel:</b> ".$kernel->name."<br><b>Image:</b> ".$image->name."<br><b>Type:</b> ".$appliance_virtualization_type."<br><b>IP:</b>".$appliance_resources_str;
		    $appliance_comment = $this->appliance->comment;
		    $ta[] = array(
			    'appliance_id' => $this->cloudappliance->id,
			    'appliance_icon' => "<img width=\"".$cloud_object_icon_size."\" height=\"".$cloud_object_icon_size."\" src=\"".$resource_icon_default."\">
				    <br><a href=\"#\" onClick=\"javascript:window.open('/cloud-portal/user/index.php?cloud_ui=profile_upload&object_type=2&cloud=".$this->cloudappliance->id."','','location=0,status=0,scrollbars=1,width=800,height=300,left=100,top=50,screenX=100,screenY=50');\"><small>Upload Icon</small></a>",
			    'appliance_state' => "<img src=\"".$state_icon."\">",
			    'appliance_name' => $cr_details,
			    'appliance_config' => $config_column,
			    'appliance_disk_size' => $cloud_image_disk_size,
			    'appliance_comment' => $appliance_comment,
			    'appliance_cloud_action' => '<div id="appliance_cloud_action_column">'.$cloudappliance_action.'</div>',
		    );
		    $app_count++;
	    }


	    $table->max = $app_count;
	    $table->body = $ta;
	    return $table;
	}


}

?>


