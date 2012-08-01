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


class cloud_ui_home
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud_ui';

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
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
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
		$template = $this->response->html->template("./tpl/cloud-ui.home.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['cloud_ui_home'], 'title');
		$template->add($this->lang['cloud_ui_create'], 'cloud_ui_create');
		$template->add($this->lang['cloud_ui_requests'], 'cloud_ui_requests');
		$template->add($this->lang['cloud_ui_appliances'], 'cloud_ui_appliances');
		$template->add($this->lang['cloud_ui_account'], 'cloud_ui_account');
		$template->add($this->lang['cloud_ui_transaction'], 'cloud_ui_transaction');
		$template->add($this->lang['cloud_ui_profiles'], 'cloud_ui_profiles');
		$template->add($this->lang['cloud_ui_vcd'], 'cloud_ui_vcd');
		$template->add($this->lang['cloud_ui_vid'], 'cloud_ui_vid');
		return $template;
	}


}

?>


