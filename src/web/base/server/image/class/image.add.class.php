<?php
/**
 * Image Add
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class image_add
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'image_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "image_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'image_tab';
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

		$plugin = new plugin();
		$enabled_plugins = $plugin->enabled();
		$storage_link_section = '';

		foreach ($enabled_plugins as $index => $plugin_name) {
			if (strstr($plugin_name, "-storage")) {
				$new_image_link = "/openqrm/base/index.php?iframe=/openqrm/base/plugins/".$plugin_name."/".$plugin_name."-manager.php";

// TODO - remove old link compatiblity section
				switch ($plugin_name) {
					case 'nfs-storage':
					case 'aoe-storage':
					case 'iscsi-storage':
					case 'lvm-storage':
					case 'kvm-storage':
					case 'xen-storage':
					case 'local-storage':
					case 'tmpfs-storage':
						$new_image_link = "/openqrm/base/index.php?plugin=".$plugin_name;
						break;
				}
// TODO some of the storage plugins do not have a storage-manager
				switch ($plugin_name) {
					default:
						$storage_link_section .= "<a href='".$new_image_link."' style='text-decoration: none'><img title='".sprintf($this->lang['create_image'], $plugin_name)."' alt='".sprintf($this->lang['create_image'], $plugin_name)."' src='/openqrm/base/plugins/".$plugin_name."/img/plugin.png' border=0> ".$plugin_name." ".$this->lang['volume']."</a><br>";
						break;
				}
			}
		}
		if (!strlen($storage_link_section)) {
			$storage_link_section = $this->lang['start_storage_plugin'];
		}

		$t = $this->response->html->template($this->tpldir.'/image-add.tpl.php');
		$t->add($storage_link_section, 'image_new');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['title'], 'title');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
