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



class cloud_selector_add
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
		$response = $this->add();
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
	function add() {
		$product_name = $this->response->html->request()->get('product_name');
		$product_type = $this->response->html->request()->get('product_type');
		$product_quantity = $this->response->html->request()->get('product_quantity');
		$product_price = $this->response->html->request()->get('product_price');
		$product_description = $this->response->html->request()->get('product_description');
		if ($this->cloudselector->product_exists($product_type, $product_quantity)) {
			$this->response->msg = sprintf($this->lang['cloud_selector_product_exists'], $product_type, $product_quantity);
		} else {
			$new_product_id = openqrm_db_get_free_id('id', $this->cloudselector->_db_table);
			$next_free_sort_id = $this->cloudselector->get_next_free_sort_id($product_type);
			$new_product['id'] = $new_product_id;
			$new_product['type'] = $product_type;
			$new_product['sort_id'] = $next_free_sort_id;
			$new_product['quantity'] = $product_quantity;
			$new_product['price'] = $product_price;
			$new_product['name'] = $product_name;
			$new_product['description'] = $product_description;
			$new_product['state'] = 1;
			$this->cloudselector->add($new_product);
			$this->response->msg = $this->lang['cloud_selector_add_successful'];
			$this->response->product_type = $product_type;
		}
		return $this->response;
	}

}












?>
