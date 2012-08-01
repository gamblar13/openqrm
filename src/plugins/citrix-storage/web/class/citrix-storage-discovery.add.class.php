<?php
/**
 * Add discovered XenServer Hosts to openQRM
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_discovery_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_action';
/**
* name of identifier
* @access public
* @var string
*/
var $identifier_name = 'xenserver_ad_id';

/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_msg";
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
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->rootdir = $this->openqrm->get('webdir');
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
		if($this->response->html->request()->get($this->identifier_name) !== '' || $this->response->html->request()->get('citrix_storage_ad_id') !== '') {
			$response = $this->add();
			if(isset($response->error)) {
				$_REQUEST[$this->message_param] = $response->error;
			}
			else if(isset($response->msg)) {
				$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
			}
			$t = $response->html->template($this->tpldir.'/citrix-storage-discovery-add.tpl.php');
			$t->add($this->lang['label'], 'title');
			$t->add($response->form->get_elements());
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->lang['canceled'], 'canceled');
			$t->add($response->html->thisfile, "thisfile");
			$t->group_elements(array('param_' => 'form'));
			return $t;
		} else {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select'));
		}
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
		$response = $this->get_response("add");
		$form     = $response->form;

		if(!$form->get_errors()	&& $response->submit()) {
			$data = $form->get_request();
			// check if already integrated
			$this->discovery->get_instance_by_id($data['citrix_storage_ad_id']);
			if ($this->discovery->xenserver_ad_is_integrated > 0) {
				$response->msg = sprintf($this->lang['error_exists'], $this->discovery->xenserver_ad_id);
				return $response;
			}

			if(!$form->get_errors()) {
				$esx_ip = $data['xenserver_ad_ip'];
				$esx_mac = $data['xenserver_ad_mac'];
				$esx_hostname = $data['xenserver_ad_hostname'];
				$esx_user = $data['xenserver_ad_user'];
				$esx_password = $data['xenserver_ad_password'];
				$esx_comment = $data['xenserver_ad_comment'];

				// create the resource
				$esx_resource = new resource();
				// check if mac already exist
				$esx_resource->get_instance_by_mac($esx_mac);
				if ($esx_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->discovery->xenserver_ad_id);
					return $response;
				}
				// check if mac already exist
				$esx_resource->get_instance_by_ip($esx_ip);
				if ($esx_resource->id > 0) {
					$response->msg = sprintf($this->lang['error_exists'], $this->discovery->xenserver_ad_id);
					return $response;
				}

				// now we check if the given credentials work
				$openqrm_server_resource = new resource();
				$openqrm_server_resource->get_instance_by_id(0);
				$command  = $this->openqrm->get('basedir')."/plugins/citrix-storage/bin/openqrm-citrix-storage configure -i ".$esx_ip." -eu ".$esx_user." -ep ".$esx_password." -eh ".$esx_hostname;
				$file = $this->rootdir."/plugins/citrix-storage/citrix-storage-stat/".$esx_ip.".integrated_successful";
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
									$response->msg = sprintf($this->lang['error_integrating'], $this->discovery->xenserver_ad_id);
									return $response;
								}
							}
						}
					}
				}
				unlink($file);

				// no resource yet, ready to create
				$esx_virtualization = new virtualization();
				$esx_virtualization->get_instance_by_type('citrix-storage');

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

				// create storage server type local-server
				$storage_fields["storage_name"] = "resource".$new_resource_id;
				$storage_fields["storage_resource_id"] = $new_resource_id;
				$deployment = new deployment();
				$deployment->get_instance_by_type('local-server');
				$storage_fields["storage_type"] = $deployment->id;
				$storage_fields["storage_comment"] = "XenServer Storage resource $new_resource_id";
				$storage_fields["storage_capabilities"] = 'TYPE=local-server';
				$storage = new storage();
				$storage_fields["storage_id"]=openqrm_db_get_free_id('storage_id', 'storage_info');
				$storage->add($storage_fields);

				// create storage server type citrix-storage
				$storage_fields2["storage_name"] = "XenServer".$new_resource_id;
				$storage_fields2["storage_resource_id"] = $new_resource_id;
				$deployment->get_instance_by_type('citrix-deployment');
				$storage_fields2["storage_type"] = $deployment->id;
				$storage_fields2["storage_comment"] = "XenServer Storage resource $new_resource_id";
				$storage_fields2["storage_capabilities"] = '';
				$storage_fields2["storage_id"]=openqrm_db_get_free_id('storage_id', 'storage_info');
				$storage->add($storage_fields2);
				
				// create image
				$image_fields["image_id"]=openqrm_db_get_free_id('image_id', 'image_info');
				$image_fields["image_name"] = "XenServer".$new_resource_id;
				$image_fields["image_type"] = $deployment->type;
				$image_fields["image_rootdevice"] = 'local disk';
				$image_fields["image_rootfstype"] = 'local disk';
				$image_fields["image_storageid"] = $storage_fields["storage_id"];
				$image_fields["image_comment"] = "XenServer image resource $new_resource_id";
				$image_fields["image_capabilities"] = 'TYPE=local-server';
				$image = new image();
				$image->add($image_fields);

				// create kernel
				$kernel_fields["kernel_id"] = openqrm_db_get_free_id('kernel_id', 'kernel_info');
				$kernel_fields["kernel_name"] = "XenServer".$new_resource_id;
				$kernel_fields["kernel_version"] = "XenServer".$new_resource_id;
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
				$appliance_fields["appliance_comment"]="XenServer appliance resource $new_resource_id";
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
					$plugin_start_appliance_hook = $this->openqrm->get('basedir')."/plugins/$plugin_name/openqrm-$plugin_name-appliance-hook.php";
					if (file_exists($plugin_start_appliance_hook)) {
						$event->log("integrate", $_SERVER['REQUEST_TIME'], 5, "citrix-storage-discovery.add.class.php", "Found plugin $plugin_name handling start-appliance event.", "", "", 0, 0, $appliance->resources);
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
				$discovery_esx_fields['xenserver_ad_is_integrated']=1;
				$discovery_esx_fields['xenserver_ad_hostname']=$esx_hostname;
				$this->discovery->update($data['citrix_storage_ad_id'], $discovery_esx_fields);

				// success msg
				$response->msg = sprintf($this->lang['msg_added'], $this->discovery->xenserver_ad_id);
			}
		}
		return $response;
	}



	function get_response() {

		$esx_ad_id_arr = $this->response->html->request()->get($this->identifier_name);
		if (is_array($esx_ad_id_arr)) {
			$esx_ad_id = $esx_ad_id_arr[0];
			$this->discovery->get_instance_by_id($esx_ad_id);
		}
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "add");

		$citrix_storage_username = '';
		if (strlen($this->discovery->xenserver_ad_user)) {
			$citrix_storage_username = $this->discovery->xenserver_ad_user;
		} else {
			$citrix_storage_username = "root";
		}

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d = array();

		$d['citrix_storage_ad_id']['label']                     = "Discovery ID";
		$d['citrix_storage_ad_id']['required']                  = true;
		$d['citrix_storage_ad_id']['validate']['regex']         = '~^[0-9]+$~i';
		$d['citrix_storage_ad_id']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['citrix_storage_ad_id']['object']['type']            = 'htmlobject_input';
		$d['citrix_storage_ad_id']['object']['attrib']['type']  = 'text';
		$d['citrix_storage_ad_id']['object']['attrib']['id']    = 'citrix_storage_ad_id';
		$d['citrix_storage_ad_id']['object']['attrib']['name']  = 'citrix_storage_ad_id';
		$d['citrix_storage_ad_id']['object']['attrib']['value']  = $this->discovery->xenserver_ad_id;

		$d['xenserver_ad_ip']['label']                     = $this->lang['ip_address'];
		$d['xenserver_ad_ip']['required']                  = true;
//		$d['xenserver_ad_ip']['validate']['regex']         = '~^[0-9]+$~i';
		$d['xenserver_ad_ip']['validate']['errormsg']      = 'ID must be [0-9] only';
		$d['xenserver_ad_ip']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_ip']['object']['attrib']['type']  = 'text';
		$d['xenserver_ad_ip']['object']['attrib']['id']    = 'xenserver_ad_ip';
		$d['xenserver_ad_ip']['object']['attrib']['name']  = 'xenserver_ad_ip';
		$d['xenserver_ad_ip']['object']['attrib']['value']  = $this->discovery->xenserver_ad_ip;

		$d['xenserver_ad_mac']['label']                     = $this->lang['mac_address'];
		$d['xenserver_ad_mac']['required']                  = true;
//		$d['xenserver_ad_mac']['validate']['regex']         = '~^[0-9]+$~i';
		$d['xenserver_ad_mac']['validate']['errormsg']      = 'MAC must be [0-9] only';
		$d['xenserver_ad_mac']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_mac']['object']['attrib']['type']  = 'text';
		$d['xenserver_ad_mac']['object']['attrib']['id']    = 'xenserver_ad_mac';
		$d['xenserver_ad_mac']['object']['attrib']['name']  = 'xenserver_ad_mac';
		$d['xenserver_ad_mac']['object']['attrib']['value']  = $this->discovery->xenserver_ad_mac;

		$d['xenserver_ad_hostname']['label']                     = $this->lang['hostname'];
		$d['xenserver_ad_hostname']['required']                  = true;
//		$d['xenserver_ad_hostname']['validate']['regex']         = '~^[a-z0-9]+$~i';
		$d['xenserver_ad_hostname']['validate']['errormsg']      = 'Hostname must be [a-z0-9] only';
		$d['xenserver_ad_hostname']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_hostname']['object']['attrib']['type']  = 'text';
		$d['xenserver_ad_hostname']['object']['attrib']['id']    = 'xenserver_ad_hostname';
		$d['xenserver_ad_hostname']['object']['attrib']['name']  = 'xenserver_ad_hostname';
		$d['xenserver_ad_hostname']['object']['attrib']['value']  = $this->discovery->xenserver_ad_ip;

		$d['xenserver_ad_user']['label']                     = $this->lang['user'];
		$d['xenserver_ad_user']['required']                  = true;
		$d['xenserver_ad_user']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['xenserver_ad_user']['validate']['errormsg']      = 'User must be [a-z0-9] only';
		$d['xenserver_ad_user']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_user']['object']['attrib']['type']  = 'text';
		$d['xenserver_ad_user']['object']['attrib']['id']    = 'xenserver_ad_user';
		$d['xenserver_ad_user']['object']['attrib']['name']  = 'xenserver_ad_user';
		$d['xenserver_ad_user']['object']['attrib']['value']  = $citrix_storage_username;

		$d['xenserver_ad_password']['label']                     = $this->lang['password'];
		$d['xenserver_ad_password']['required']                  = true;
		$d['xenserver_ad_password']['validate']['regex']         =  '~^[a-z0-9]+$~i';
		$d['xenserver_ad_password']['validate']['errormsg']      = 'Password must be [a-z0-9] only';
		$d['xenserver_ad_password']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_password']['object']['attrib']['type']  = 'password';
		$d['xenserver_ad_password']['object']['attrib']['id']    = 'xenserver_ad_password';
		$d['xenserver_ad_password']['object']['attrib']['name']  = 'xenserver_ad_password';
		$d['xenserver_ad_password']['object']['attrib']['value']  = $this->discovery->xenserver_ad_password;

		$d['xenserver_ad_comment']['label']                     = $this->lang['comment'];
		$d['xenserver_ad_comment']['required']                  = true;
		$d['xenserver_ad_comment']['validate']['regex']         =  '~^[a-z0-9- ]+$~i';
		$d['xenserver_ad_comment']['validate']['errormsg']      = 'Comment must be [a-z0-9] only';
		$d['xenserver_ad_comment']['object']['type']            = 'htmlobject_input';
		$d['xenserver_ad_comment']['object']['attrib']['type']  = 'text';
		$d['xenserver_ad_comment']['object']['attrib']['id']    = 'xenserver_ad_comment';
		$d['xenserver_ad_comment']['object']['attrib']['name']  = 'xenserver_ad_comment';
		$d['xenserver_ad_comment']['object']['attrib']['value']  = $this->discovery->xenserver_ad_comment;

		$form->add($d);
		$response->form = $form;
		return $response;
	}



}
?>
