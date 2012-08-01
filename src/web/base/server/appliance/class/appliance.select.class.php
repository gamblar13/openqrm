<?php
/**
 * Appliance Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class appliance_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
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
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->user     = $openqrm->user();
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/appliance-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
		$h['appliance_state']['title'] ='&#160;';
		$h['appliance_state']['sortable'] = false;
		$h['appliance_icon']['title'] ='&#160;';
		$h['appliance_icon']['sortable'] = false;
		$h['appliance_id']['title'] = $this->lang['table_id'];
		$h['appliance_name']['title'] = $this->lang['table_name'];
		$h['appliance_values']['title'] = '&#160;';
		$h['appliance_values']['sortable'] = false;
		$h['appliance_comment']['title'] ='&#160;';
		$h['appliance_comment']['sortable'] = false;
		$h['appliance_edit']['title'] ='&#160;';
		$h['appliance_edit']['sortable'] = false;

		$appliance = new appliance();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		$table = $this->response->html->tablebuilder('appliance', $params);
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max = $appliance->get_count();
		$table->init();

		$appliances = $appliance->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		foreach ($appliances as $index => $appliance_db) {
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_db["appliance_id"]);
			$resource = new resource();
			$appliance_resources=$appliance_db["appliance_resources"];
			$kernel = new kernel();
			$kernel->get_instance_by_id($appliance_db["appliance_kernelid"]);
			$image = new image();
			$image->get_instance_by_id($appliance_db["appliance_imageid"]);
			$virtualization = new virtualization();
			$virtualization->get_instance_by_id($appliance_db["appliance_virtualization"]);
			$appliance_virtualization_name=$virtualization->name;
			$virtualization_plugin_name = str_replace("-vm", "", $virtualization->type);
			$resource_is_local_server = false;
			// special vm-manager for ..-storage plugins
			if (strpos($virtualization_plugin_name, "-storage")) {
				$vm_manager_file = $virtualization_plugin_name."-vm-manager.php";
			} else {
				$vm_manager_file = $virtualization_plugin_name."-manager.php";
			}

			if ($appliance_resources >=0) {
				// an appliance with a pre-selected resource
				$resource->get_instance_by_id($appliance_resources);
				$resource_state_icon="/openqrm/base/img/$resource->state.png";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$resource_state_icon)) {
					$resource_state_icon="/openqrm/base/img/unknown.png";
				}
				// idle ?
				if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
					$resource_state_icon="/openqrm/base/img/idle.png";
				}


				// if virtual, link to vm manager
				if (strpos($virtualization->type, "-vm")) {
					$host_resource = new resource();
					$host_resource->get_instance_by_id($resource->vhostid);
					$host_virtualization = new virtualization();
					$host_virtualization_name = str_replace("-vm", "", $virtualization->type);
					$host_virtualization->get_instance_by_type($host_virtualization_name);
					$host_appliance = new appliance();
					$host_appliance->get_instance_by_virtualization_and_resource($host_virtualization->id, $resource->vhostid);
					$link_to_vm_manager_resource_detail = "/openqrm/base/plugins/".$host_virtualization_name."/".$vm_manager_file."?&currenttab=tab0&action=select&identifier[]=".$host_appliance->id;
					$appliance_resources_str = " <a href='".$link_to_vm_manager_resource_detail."'><img width=12 height=12 src=".$resource_state_icon."> ".$resource->id." / ".$resource->ip."</a>";
					$link_to_vm_manager = "/openqrm/base/plugins/".$host_virtualization_name."/".$vm_manager_file."?&currenttab=tab0";
					$appliance_virtualization_name = " <a href='".$link_to_vm_manager."'>".$virtualization->name."</a>";
				} else {
					$appliance_resources_str = '<a href="'.$this->response->html->thisfile.'?base=resource"><img height="12" width="12" src="'.$resource_state_icon.'"> '.$resource->id.' / '.$resource->ip.'</a>';
				}
				//
				if (strstr($resource->capabilities, "TYPE=local-server")) {
					$resource_is_local_server = true;
				}


			} else {
				// an appliance with resource auto-select enabled
				$appliance_resources_str = "auto-select";
				$link_to_vm_manager = "/openqrm/base/plugins/".$virtualization_plugin_name."/".$vm_manager_file."?&currenttab=tab0";
				$appliance_virtualization_name = " <a href='".$link_to_vm_manager."'>".$virtualization->name."</a>";
			}

			// if virtual, link to vm manager
			if (strpos($virtualization->type, "-vm")) {
				// Type link
				switch ($virtualization->type) {
					case 'kvm-vm':
						$link = '?plugin=kvm&amp;controller=kvm-vm';
					break;
					case 'kvm-storage-vm':
						$link = '?plugin=kvm-storage&amp;controller=kvm-storage-vm';
					break;
					case 'vmware-esx-vm':
						$link = '?plugin=vmware-esx&amp;controller=vmware-esx';
					break;
					case 'xen-vm':
						$link = '?plugin=xen&amp;controller=xen-vm';
					break;
					case 'xen-storage-vm':
						$link = '?plugin=xen-storage&amp;controller=xen-storage-vm';
					break;
					case 'openvz-storage-vm':
						$link = '?plugin=openvz-storage&amp;controller=openvz-storage-vm';
					break;
					case 'lxc-storage-vm':
						$link = '?plugin=lxc-storage&amp;controller=lxc-storage-vm';
					break;
					case 'citrix-vm':
						$link = '?plugin=citrix&amp;controller=citrix';
					break;
					case 'citrix-storage-vm':
						$link = '?plugin=citrix-storage&amp;controller=citrix-storage';
					break;
				
					default:
						$link = '?plugin='.$virtualization->type;
					break; 
				}
				$appliance_virtualization_name = '<a href="'.$this->response->html->thisfile.$link.'">'.$virtualization->name.'</a>';

			}


			// if its a virtualization host link to vm-manager
			else if (strpos($virtualization->name, " Host")) {
				$link = '';
				switch ($virtualization->type) {
					case 'kvm':
						$link = '?plugin=kvm&amp;controller=kvm-vm&amp;kvm_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'vmware-esx':
						$link = '?plugin=vmware-esx&amp;controller='.$virtualization->type.'&amp;vmware_esx_action=vm&amp;appliance_id='.$appliance->id;
					break;
					case 'kvm-storage':
						$link = '?plugin=kvm-storage&amp;controller=kvm-storage-vm&amp;kvm_storage_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'xen-storage':
						$link = '?plugin=xen-storage&amp;controller=xen-storage-vm&amp;xen_storage_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'openvz-storage':
						$link = '?plugin=openvz-storage&amp;controller=openvz-storage-vm&amp;openvz_storage_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'lxc-storage':
						$link = '?plugin=lxc-storage&amp;controller=lxc-storage-vm&amp;lxc_storage_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'xen':
						$link = '?plugin=xen&amp;controller=xen-vm&amp;xen_vm_action=edit&amp;appliance_id='.$appliance->id;
					break;
					case 'citrix':
						$link = '?plugin=citrix&amp;controller='.$virtualization->type.'&amp;citrix_action=vm&amp;appliance_id='.$appliance->id;
					break;
					case 'citrix-storage':
						$link = '?plugin=citrix-storage&amp;controller='.$virtualization->type.'&amp;citrix_storage_action=vm&amp;appliance_id='.$appliance->id;
					break;
				
				}
				$appliance_virtualization_name = '<a href="'.$this->response->html->thisfile.$link.'">'.$virtualization->name.'</a>';
			}

			// active or inactive
			$resource_icon_default="/openqrm/base/img/appliance.png";
			$active_state_icon="/openqrm/base/img/active.png";
			$inactive_state_icon="/openqrm/base/img/idle.png";
			if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
				$state_icon=$active_state_icon;
			} else {
				$state_icon=$inactive_state_icon;
			}
			// additional local-server server appliances will still appear idle/stopped
			// only the master appliance can be started/stopped
			//		if ($resource_is_local_server) {
			//			$state_icon=$active_state_icon;
			//		}
			// link to image edit
			if ($image->id > 0) {
				$image_edit_link = '<a href="'.$this->response->html->thisfile.'?base=image&amp;image_action=edit&amp;image_id='.$image->id.'">'.$image->name.'</a>';
			} else {
				$image_edit_link = $image->name;
			}

			// release resource
			$release_resource = '';
			$a = $this->response->html->a();
			$a->label   = '<img title="'.$this->lang['resource_release'].'" alt="'.$this->lang['resource_release'].'" height="20" width="20" src="/openqrm/base/img/idle.png" border="0">';
			$a->handler = 'onclick="wait();"';
			$a->href    = $this->response->get_url($this->actions_name, 'release').'&appliance_id='.$appliance->id;
			if ($appliance->stoptime == 0 || $appliance_resources == 0)  {
				$release_resource = '';
			} else {
				if ($appliance->resources != -1) {
					$release_resource = ' - '.$a->get_string();
				}
			}

			$str = '<b>Kernel:</b> '.$kernel->name.'<br>
					<b>Image:</b> '.$image_edit_link.'<br>
					<b>Resource:</b> '.$appliance_resources_str.' '.$release_resource.'<br>
					<b>Type:</b> '.$appliance_virtualization_name;

			// build the plugin link section
			$appliance_link_section = '';
			// add link to continue if appliance has unfinished wizard
			$disabled = array();
			if(isset($appliance->wizard) && strpos($appliance->wizard, 'wizard') !== false) {
				$params = explode(',', $appliance->wizard);
				$wizard_step = explode('=', $params[0]);
				$wizard_user = explode('=', $params[1]);
				if ($wizard_user[1] === $this->user->name) {
					$a = $this->response->html->a();
					$a->title   = $this->lang['action_continue'];
					$a->label   = $this->lang['action_continue'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'continue';
					$a->href    = $this->response->get_url($this->actions_name, $wizard_step[1]).'&appliance_id='.$appliance->id;
					$appliance_comment = $a->get_string();
				} else {
					$appliance_comment = sprintf($this->lang['appliance_create_in_progress'], $wizard_user[1]);
				}
				$disabled[] = $appliance->id;
				$edit = false;
			} else {
				$plugin = new plugin();
				$enabled_plugins = $plugin->enabled();
				foreach ($enabled_plugins as $index => $plugin_name) {
					$plugin_appliance_link_section_hook = $this->openqrm->get('webdir')."/plugins/".$plugin_name."/openqrm-".$plugin_name."-appliance-link-hook.php";
					if (file_exists($plugin_appliance_link_section_hook)) {
						require_once "$plugin_appliance_link_section_hook";
						$appliance_get_link_function = str_replace("-", "_", "get_"."$plugin_name"."_appliance_link");
						if(function_exists($appliance_get_link_function)) {
							$alink = $appliance_get_link_function($appliance->id);
							if(is_object($alink)) {
								$alink = $alink->get_string();
							}
							$appliance_link_section .= $alink;
						}
					}
				}
				$appliance_comment = $appliance_db["appliance_comment"];
				$appliance_comment .= "<br><hr>";
				$appliance_comment .= $appliance_link_section;
			}

			// appliance edit
			if(isset($edit) && $edit === false) {
				$strEdit = '';
			} else {
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_edit'];
				$a->label   = $this->lang['action_edit'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'edit';
				$a->href    = $this->response->get_url($this->actions_name, 'edit').'&appliance_id='.$appliance->id;
				$strEdit    = $a->get_string();
			}

			$b[] = array(
				'appliance_state' => "<img  width=24 height=24 src=$state_icon>",
				'appliance_icon' => "<img width=24 height=24 src=$resource_icon_default>",
				'appliance_id' => $appliance_db["appliance_id"],
				'appliance_name' => $appliance_db["appliance_name"],
				'appliance_values' => $str,
				'appliance_comment' => $appliance_comment,
				'appliance_edit' => $strEdit,
			);

		}

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "step1");

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->max = $appliance->get_count();
		$table->head = $h;
		$table->body = $b;
		$table->form_action = $this->response->html->thisfile;
		$table->actions_name = $this->actions_name;
		$table->actions = array($this->lang['action_start'], $this->lang['action_stop'], $this->lang['action_remove']);
		$table->identifier = 'appliance_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = $disabled;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['add']    = $add->get_string();
		$d['table']  = $table;
		return $d;
	}

}
?>
