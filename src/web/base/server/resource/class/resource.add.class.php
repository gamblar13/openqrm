<?php
/**
 * Resource Add
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class resource_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'resource_tab';
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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
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
		$virtualization = new virtualization();
		$virtualization_list = array();
		$virtualization_list = $virtualization->get_list();
		$virtualization_link_section = "";
		// filter out the virtualization hosts
		foreach ($virtualization_list as $id => $virt) {
			$virtualization_id = $virt['value'];
			$available_virtualization = new virtualization();
			$available_virtualization->get_instance_by_id($virtualization_id);
			if (strstr($available_virtualization->type, "-vm")) {
				$virtualization_plugin_name = str_replace("-vm", "", $available_virtualization->type);
				$virtualization_name = substr($available_virtualization->name, 0, -2);
				if (strrpos($available_virtualization->type, "-storage")) {
					$new_vm_link = "/openqrm/base/index.php?iframe=/openqrm/base/plugins/".$virtualization_plugin_name."/".$virtualization_plugin_name."-vm-manager.php";
				} else {
					$new_vm_link = "/openqrm/base/index.php?iframe=/openqrm/base/plugins/".$virtualization_plugin_name."/".$virtualization_plugin_name."-manager.php";
				}

// TODO - remove old link compatiblity section
				switch ($virtualization_plugin_name) {
					case 'citrix':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name;
						break;
					case 'citrix-storage':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name;
						break;
					case 'kvm':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name."&controller=kvm-vm";
						break;
					case 'vmware-esx':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=vmware-esx';
						break;
					case 'kvm-storage':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=kvm-storage-vm';
						break;
					case 'xen-storage':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=xen-storage-vm';
						break;
					case 'openvz-storage':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=openvz-storage-vm';
						break;
					case 'lxc-storage':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=lxc-storage-vm';
						break;
					case 'xen':
						$new_vm_link = "/openqrm/base/index.php?plugin=".$virtualization_plugin_name.'&controller=xen-vm';
						break;
				}
				$virtualization_link_section .= "<a href='".$new_vm_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_vm'], $virtualization_plugin_name)."' alt='".sprintf($this->lang['create_vm'], $virtualization_plugin_name)."' src='/openqrm/base/plugins/".$virtualization_plugin_name."/img/plugin.png' border=0> ".$virtualization_name." ".$this->lang['vm']."</a><br>";
			}
		}
		if (!strlen($virtualization_link_section)) {
			$virtualization_link_section = $this->lang['start_vm_plugin'];
		}
		// local-server plugin enabled and started
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/openqrm/base/plugins/local-server/.running")) {
			$local_server_plugin_link = "<a href='/openqrm/base/index.php?plugin=local-server&controller=local-server-about&local_server_about_action=usage' style='text-decoration: none'><img title='".$this->lang['integrate_local_server']."' alt='".$this->lang['integrate_local_server']."' src='/openqrm/base/plugins/local-server/img/plugin.png' border=0> ".$this->lang['integrate_local_server']."</a>";
		} else {
			$local_server_plugin_link = $this->lang['start_local_server'];
		}

		// manual add new resource
		$manual_new_resource_link = "<a href='/openqrm/base/index.php?base=resource&resource_action=new' style='text-decoration: none'><img title='".$this->lang['manual_new_resource']."' alt='".$this->lang['manual_new_resource']."' src='/openqrm/base/img/resource.png' border=0> ".$this->lang['manual_new_resource']."</a>";
		


		$t = $this->response->html->template($this->tpldir.'/resource-add.tpl.php');
		$t->add($virtualization_link_section, 'resource_virtual');
		$t->add($local_server_plugin_link, 'resource_local');
		$t->add($manual_new_resource_link, 'resource_new');
		$t->add($this->lang['title'], 'label');
		$t->add($this->lang['vm_type'], 'vm_type');
		$t->add($this->lang['local'], 'local');
		$t->add($this->lang['unmanaged'], 'unmanaged');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
