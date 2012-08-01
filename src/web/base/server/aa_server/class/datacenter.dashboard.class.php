<?php
/**
 * Datacenter Dashboard
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class datacenter_dashboard
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'datacenter_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "datacenter_msg";

/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'datacenter_tab';
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
		$t = $this->response->html->template($this->tpldir.'/datacenter-dashboard.tpl.php');
		$t->add($this->lang['title'], 'title');
		$t->add($this->lang['datacenter_load_overall'], 'datacenter_load_overall');
		$t->add($this->lang['resource_overview'], 'resource_overview');
		$t->add($this->lang['resource_load_overall'], 'resource_load_overall');
		$t->add($this->lang['resource_load_physical'], 'resource_load_physical');
		$t->add($this->lang['resource_load_vm'], 'resource_load_vm');
		$t->add($this->lang['resource_available_overall'], 'resource_available_overall');
		$t->add($this->lang['resource_available_physical'], 'resource_available_physical');
		$t->add($this->lang['resource_available_vm'], 'resource_available_vm');
		$t->add($this->lang['resource_error_overall'], 'resource_error_overall');
		$t->add($this->lang['appliance_overview'], 'appliance_overview');
		$t->add($this->lang['appliance_load_overall'], 'appliance_load_overall');
		$t->add($this->lang['appliance_load_peak'], 'appliance_load_peak');
		$t->add($this->lang['appliance_error_overall'], 'appliance_error_overall');
		$t->add($this->lang['storage_overview'], 'storage_overview');
		$t->add($this->lang['storage_load_overall'], 'storage_load_overall');
		$t->add($this->lang['storage_load_peak'], 'storage_load_peak');
		$t->add($this->lang['storage_error_overall'], 'storage_error_overall');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}


}
?>
