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


class cloud_selector_products
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'cloud_selector';



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
		$this->webdir  = $this->openqrm->get('webdir');
		$this->rootdir  = $this->openqrm->get('basedir');
		require_once $this->webdir."/plugins/cloud/class/clouduser.class.php";
		$this->cloud_user = new clouduser();
		require_once $this->webdir."/plugins/cloud/class/cloudrequest.class.php";
		$this->cloud_selector = new cloudrequest();
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudmailer.class.php";
		$this->cloud_mailer = new cloudmailer();

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
		$external_portal_name = $this->cloud_config->get_value_by_key('external_portal_url');
		if (!strlen($external_portal_name)) {
			$openqrm_server = new openqrm_server();
			$openqrm_server_ip = $openqrm_server->get_ip_address();
			$external_portal_name = "http://".$openqrm_server_ip."/cloud-portal";
		}

		$template = $this->response->html->template($this->tpldir."/cloud-selector-products.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($external_portal_name, 'external_portal_name');
		$template->add($this->lang['cloud_selector_title'], 'title');
		$template->add($this->lang['cloud_selector_cpu'], 'cloud_selector_cpu');
		$template->add($this->lang['cloud_selector_disk'], 'cloud_selector_disk');
		$template->add($this->lang['cloud_selector_ha'], 'cloud_selector_ha');
		$template->add($this->lang['cloud_selector_kernel'], 'cloud_selector_kernel');
		$template->add($this->lang['cloud_selector_memory'], 'cloud_selector_memory');
		$template->add($this->lang['cloud_selector_network'], 'cloud_selector_network');
		$template->add($this->lang['cloud_selector_puppet'], 'cloud_selector_puppet');
		$template->add($this->lang['cloud_selector_resource'], 'cloud_selector_resource');
		$template->group_elements(array('param_' => 'form'));

		return $template;
	}





}

?>


