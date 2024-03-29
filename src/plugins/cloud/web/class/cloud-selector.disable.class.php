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



class cloud_selector_disable
{
var $tpldir;
var $lang;
var $actions_name = 'cloud_selector';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
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
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudselector.class.php";
		$this->cloudselector = new cloudselector();
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
		$response = $this->disable();
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, $this->response->product_type, $this->message_param, $response->msg));
		}
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function disable() {
		$cloud_selector_id = $this->response->html->request()->get('cloud_selector_id');
		$this->cloudselector->get_instance_by_id($cloud_selector_id);
		$product_type = $this->cloudselector->type;
		$this->response->msg = $this->lang['cloud_selector_product_disable_successful'];
		$this->response->product_type = $product_type;
		$this->cloudselector->set_state($cloud_selector_id, 0);
		return $this->response;
	}

}












?>
