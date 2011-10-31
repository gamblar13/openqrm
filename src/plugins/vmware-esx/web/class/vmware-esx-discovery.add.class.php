<?php
/**
 * Add discovered ESX Hosts to openQRM
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

class vmware_esx_discovery_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* name of identifier
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_ad_id';

/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response, $db) {
		$this->__response = $response;
		$this->__rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->__db = $db;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$response = $this->add();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->__response->redirect($this->__response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$template = $response->html->template($this->tpldir.'/vmware-esx-discovery-add.tpl.php');
		$template->add($this->lang['label'], 'title');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function add() {
		global $OPENQRM_SERVER_BASE_DIR;
		$response = $this->get_response("add");
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// check if already integrated
			$this->__db->get_instance_by_id($data['vmware_esx_ad_id']);
			if ($this->__db->vmw_esx_ad_is_integrated > 0) {
				$response->msg = sprintf($this->lang['error_exists'], $this->__db->vmw_esx_ad_id);
				return $response;
			}

			if(!$form->get_errors()) {
				$esx_ip = $data['vmw_esx_ad_ip'];
				$esx_mac = $data['vmw_esx_ad_mac'];
				$esx_hostname = $data['vmw_esx_ad_hostname'];
				$esx_user = $data['vmw_esx_ad_user'];
				$esx_password = $data['vmw_esx_ad_password'];
				$esx_comment = $data['vmw_esx_ad_comment'];

				// create the resource
				$esx_resource = new resource();
				// check if mac already exist
				$esx_resource->get_instance_by_mac($esx_mac);
				if ($esx_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->__db->vmw_esx_ad_id);
					return $response;
				}
				// check if mac already exist
				$esx_resource->get_instance_by_ip($esx_ip);
				if ($esx_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->__db->vmw_esx_ad_id);
					return $response;
				}

				// now we check if the given credentials work
				$openqrm_server_resource = new resource();
				$openqrm_server_resource->get_instance_by_id(0);
				$command  = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx configure -i ".$esx_ip." -eu ".$esx_user." -ep ".$esx_password." -eh ".$esx_hostname;
				$file = "vmware-esx-stat/".$esx_ip.".integrated_successful";
				if(file_exists($file)) {
					unlink($file);
				}
				$openqrm_server_resource->send_command($openqrm_server_resource->ip, $command);
				while (!file_exists($file)) // check if the data file has been modified
				{
				  usleep(10000); // sleep 10ms to unload the CPU
				  clearstatcache();
				}
				// read discovery file
				if(file_exists($file)) {
					$lines = explode("\n", file_get_contents($file));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								if (!strstr($line, "success")) {
									$response->msg = sprintf($this->lang['error_integrating'], $this->__db->vmw_esx_ad_id);
									return $response;
								}
							}
						}
					}
				}
				unlink($file);

				// no resource yet, ready to create
				$esx_virtualization = new virtualization();
				$esx_virtualization->get_instance_by_type('vmware-esx');

				$esx_resource_fields["resource_ip"]=$esx_ip;
				$esx_resource_fields["resource_mac"]=$esx_mac;
				$esx_resource_fields["resource_localboot"]=1;
				$esx_resource_fields["resource_vtype"]=1;
				$esx_resource_fields["resource_hostname"]=$esx_hostname;
				$esx_resource_fields["resource_capabilities"]='TYPE=local-server';

				// get the new resource id from the db
				$new_resource_id=openqrm_db_get_free_id('resource_id', 'resource_info');
				$esx_resource_fields["resource_id"]=$new_resource_id;
				$esx_resource_fields["resource_vhostid"]=$new_resource_id;
				$esx_resource->add($esx_resource_fields);

				// create storage server
				$storage_fields["storage_name"] = "ESX".$new_resource_id;
				$storage_fields["storage_resource_id"] = $new_resource_id;
				$deployment = new deployment();
				$deployment->get_instance_by_type('local-server');
				$storage_fields["storage_type"] = $deployment->id;
				$storage_fields["storage_comment"] = "ESX Storage resource $new_resource_id";
				$storage_fields["storage_capabilities"] = 'TYPE=local-server';
				$storage = new storage();
				$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', 'storage_info');
				$storage->add($storage_fields);

				// create image
				$image_fields["image_id"]=openqrm_db_get_free_id('image_id', 'image_info');
				$image_fields["image_name"] = "ESX".$new_resource_id;
				$image_fields["image_type"] = $deployment->type;
				$image_fields["image_rootdevice"] = 'local disk';
				$image_fields["image_rootfstype"] = 'local disk';
				$image_fields["image_storageid"] = $storage_fields["storage_id"];
				$image_fields["image_comment"] = "ESX image resource $new_resource_id";
				$image_fields["image_capabilities"] = 'TYPE=local-server';
				$image = new image();
				$image->add($image_fields);

				// create kernel
				$kernel_fields["kernel_id"] = openqrm_db_get_free_id('kernel_id', 'kernel_info');
				$kernel_fields["kernel_name"] = "ESX".$new_resource_id;
				$kernel_fields["kernel_version"] = "ESX".$new_resource_id;
				$kernel_fields["kernel_capabilities"] = 'TYPE=local-server';
				$kernel = new kernel();
				$kernel->add($kernel_fields);

				// create appliance
				$next_appliance_id=openqrm_db_get_free_id('appliance_id', 'appliance_info');
				$appliance_fields["appliance_id"]=$next_appliance_id;
				$appliance_fields["appliance_name"]=$esx_hostname;
				$appliance_fields["appliance_kernelid"]=$kernel_fields["kernel_id"];
				$appliance_fields["appliance_imageid"]=$image_fields["image_id"];
				$appliance_fields["appliance_resources"]="$new_resource_id";
				$appliance_fields['appliance_virtualization']=$esx_virtualization->id;
				$appliance_fields["appliance_capabilities"]='TYPE=local-server';
				$appliance_fields["appliance_comment"]="ESX appliance resource $new_resource_id";
				// set start time, reset stoptime, set state
				$now=$_SERVER['REQUEST_TIME'];
				$appliance_fields["appliance_starttime"]=$now;
				$appliance_fields["appliance_stoptime"]=0;
				$appliance_fields['appliance_state']='active';
				$appliance = new appliance();
				$appliance->add($appliance_fields);

				// update resource fields with kernel + image
				$kernel->get_instance_by_id($kernel_fields["kernel_id"]);
				$resource_fields["resource_kernel"]=$kernel->name;
				$resource_fields["resource_kernelid"]=$kernel_fields["kernel_id"];
				$image->get_instance_by_id($image_fields["image_id"]);
				$resource_fields["resource_image"]=$image->name;
				$resource_fields["resource_imageid"]=$image_fields["image_id"];
				$esx_resource->update_info($new_resource_id, $resource_fields);

				// add + start hook
				$appliance->get_instance_by_id($next_appliance_id);
				$now=$_SERVER['REQUEST_TIME'];
				$appliance_fields = array();
				$appliance_fields['appliance_starttime']=$now;
				$appliance_fields["appliance_stoptime"]=0;
				$appliance_fields['appliance_state']='active';
				// fill in the rest of the appliance info in the array for the plugin hook
				$appliance_fields["appliance_id"]=$next_appliance_id;
				$appliance_fields["appliance_name"]=$appliance->name;
				$appliance_fields["appliance_kernelid"]=$appliance->kernelid;
				$appliance_fields["appliance_imageid"]=$appliance->imageid;
				$appliance_fields["appliance_cpunumber"]=$appliance->cpunumber;
				$appliance_fields["appliance_cpuspeed"]=$appliance->cpuspeed;
				$appliance_fields["appliance_cpumodel"]=$appliance->cpumodel;
				$appliance_fields["appliance_memtotal"]=$appliance->memtotal;
				$appliance_fields["appliance_swaptotal"]=$appliance->swaptotal;
				$appliance_fields["appliance_nics"]=$appliance->nics;
				$appliance_fields["appliance_capabilities"]=$appliance->capabilities;
				$appliance_fields["appliance_cluster"]=$appliance->cluster;
				$appliance_fields["appliance_ssi"]=$appliance->ssi;
				$appliance_fields["appliance_resources"]=$appliance->resources;
				$appliance_fields["appliance_highavailable"]=$appliance->highavailable;
				$appliance_fields["appliance_virtual"]=$appliance->virtual;
				$appliance_fields["appliance_virtualization"]=$appliance->virtualization;
				$appliance_fields["appliance_virtualization_host"]=$appliance->virtualization_host;
				$appliance_fields["appliance_comment"]=$appliance->comment;
				$appliance_fields["appliance_event"]=$appliance->event;

				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_start_appliance_hook = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
					if (file_exists($plugin_start_appliance_hook)) {
						$event->log("integrate", $_SERVER['REQUEST_TIME'], 5, "vmware-esx-discovery.add.class.php", "Found plugin $plugin_name handling start-appliance event.", "", "", 0, 0, $appliance->resources);
						require_once "$plugin_start_appliance_hook";
						$appliance_function="openqrm_"."$plugin_name"."_appliance";
						$appliance_function=str_replace("-", "_", $appliance_function);
						// start
						$appliance_function("start", $appliance_fields);
					}
				}
				// set to started
				$active_appliance_fields['appliance_stoptime']='';
				$active_appliance_fields['appliance_starttime']=$now;
				$active_appliance_fields['appliance_state']='active';
				$appliance->update($next_appliance_id, $appliance_fields);

				// update discovery host to integrated = 1
				$discovery_esx_fields['vmw_esx_ad_is_integrated']=1;
				$this->__db->update($data['vmware_esx_ad_id'], $discovery_esx_fields);

				// success msg
				$response->msg = sprintf($this->lang['msg_added'], $this->__db->vmw_esx_ad_id);
			}
		}
		return $response;
	}



	function get_response() {

		$esx_ad_id_arr = $this->__response->html->request()->get($this->identifier_name);
		if (is_array($esx_ad_id_arr)) {
			$esx_ad_id = $esx_ad_id_arr[0];
			$this->__db->get_instance_by_id($esx_ad_id);
		}
		$response = $this->__response;
		$form = $response->get_form($this->actions_name, "add");

		$d = array();

		$d['vmware_esx_ad_id']['label']                     = "Discovery ID";
		$d['vmware_esx_ad_id']['required']                  = true;
		$d['vmware_esx_ad_id']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmware_esx_ad_id']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['vmware_esx_ad_id']['object']['type']            = 'htmlobject_input';
		$d['vmware_esx_ad_id']['object']['attrib']['type']  = 'text';
		$d['vmware_esx_ad_id']['object']['attrib']['id']    = 'vmware_esx_ad_id';
		$d['vmware_esx_ad_id']['object']['attrib']['name']  = 'vmware_esx_ad_id';
		$d['vmware_esx_ad_id']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_id;

		$d['vmw_esx_ad_ip']['label']                     = $this->lang['ip_address'];
		$d['vmw_esx_ad_ip']['required']                  = true;
//		$d['vmw_esx_ad_ip']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmw_esx_ad_ip']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['vmw_esx_ad_ip']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_ip']['object']['attrib']['type']  = 'text';
		$d['vmw_esx_ad_ip']['object']['attrib']['id']    = 'vmw_esx_ad_ip';
		$d['vmw_esx_ad_ip']['object']['attrib']['name']  = 'vmw_esx_ad_ip';
		$d['vmw_esx_ad_ip']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_ip;

		$d['vmw_esx_ad_mac']['label']                     = $this->lang['mac_address'];
		$d['vmw_esx_ad_mac']['required']                  = true;
//		$d['vmw_esx_ad_mac']['validate']['regex']         = '~^[0-9]+$~i';
		$d['vmw_esx_ad_mac']['validate']['errormsg']      = 'MAC must be [0-9] only';
		$d['vmw_esx_ad_mac']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_mac']['object']['attrib']['type']  = 'text';
		$d['vmw_esx_ad_mac']['object']['attrib']['id']    = 'vmw_esx_ad_mac';
		$d['vmw_esx_ad_mac']['object']['attrib']['name']  = 'vmw_esx_ad_mac';
		$d['vmw_esx_ad_mac']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_mac;

		$d['vmw_esx_ad_hostname']['label']                     = $this->lang['hostname'];
		$d['vmw_esx_ad_hostname']['required']                  = true;
//		$d['vmw_esx_ad_hostname']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['vmw_esx_ad_hostname']['validate']['errormsg']      = 'Hostname must be [a-z0-9] only';
		$d['vmw_esx_ad_hostname']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_hostname']['object']['attrib']['type']  = 'text';
		$d['vmw_esx_ad_hostname']['object']['attrib']['id']    = 'vmw_esx_ad_hostname';
		$d['vmw_esx_ad_hostname']['object']['attrib']['name']  = 'vmw_esx_ad_hostname';
		$d['vmw_esx_ad_hostname']['object']['attrib']['value']  = '';

		$d['vmw_esx_ad_user']['label']                     = $this->lang['user'];
		$d['vmw_esx_ad_user']['required']                  = true;
		$d['vmw_esx_ad_user']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['vmw_esx_ad_user']['validate']['errormsg']      = 'User must be [a-z0-9] only';
		$d['vmw_esx_ad_user']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_user']['object']['attrib']['type']  = 'text';
		$d['vmw_esx_ad_user']['object']['attrib']['id']    = 'vmw_esx_ad_user';
		$d['vmw_esx_ad_user']['object']['attrib']['name']  = 'vmw_esx_ad_user';
		$d['vmw_esx_ad_user']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_user;

		$d['vmw_esx_ad_password']['label']                     = $this->lang['password'];
		$d['vmw_esx_ad_password']['required']                  = true;
		$d['vmw_esx_ad_password']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['vmw_esx_ad_password']['validate']['errormsg']      = 'Password must be [a-z0-9] only';
		$d['vmw_esx_ad_password']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_password']['object']['attrib']['type']  = 'password';
		$d['vmw_esx_ad_password']['object']['attrib']['id']    = 'vmw_esx_ad_password';
		$d['vmw_esx_ad_password']['object']['attrib']['name']  = 'vmw_esx_ad_password';
		$d['vmw_esx_ad_password']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_password;

		$d['vmw_esx_ad_comment']['label']                     = $this->lang['comment'];
		$d['vmw_esx_ad_comment']['required']                  = true;
		$d['vmw_esx_ad_comment']['validate']['regex']         =  '~^[a-z0-9- ]+$~i';
		$d['vmw_esx_ad_comment']['validate']['errormsg']      = 'Comment must be [a-z0-9] only';
		$d['vmw_esx_ad_comment']['object']['type']            = 'htmlobject_input';
		$d['vmw_esx_ad_comment']['object']['attrib']['type']  = 'text';
		$d['vmw_esx_ad_comment']['object']['attrib']['id']    = 'vmw_esx_ad_comment';
		$d['vmw_esx_ad_comment']['object']['attrib']['name']  = 'vmw_esx_ad_comment';
		$d['vmw_esx_ad_comment']['object']['attrib']['value']  = $this->__db->vmw_esx_ad_comment;

		$form->add($d);
		$response->form = $form;
		return $response;
	}



}
?>
