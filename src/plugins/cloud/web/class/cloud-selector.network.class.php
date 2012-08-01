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


class cloud_selector_network
{

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
		$this->rootdir  = $this->openqrm->get('rootdir');
		$this->webdir  = $this->openqrm->get('webdir');
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudselector.class.php";
		$this->cloudselector = new cloudselector();
	}

	//--------------------------------------------
	/**
	 * Action Cpu
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->network();

		// prepare the array for the network-interface select
		$max_network_interfaces = $this->cloud_config->get_value_by_key('max_network_interfaces');
		$html = new htmlobject($this->webdir."/class/htmlobjects/");
		$s = $html->select();
		$s->id = 'product_quantity';
		$s->name = 'product_quantity';
		$s->css = 'htmlobject_select';
		for ($mnet = 1; $mnet <= $max_network_interfaces; $mnet++) {
			$o = $html->option();
			$o->value = $mnet;
			$o->label = $mnet;
			$s->add($o);
		}

		$template = $this->response->html->template($this->tpldir."/cloud-selector-network.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($response->table, 'table');
		$template->add($this->lang['cloud_selector_network'], 'cloud_selector_network');
		$template->add($this->lang['cloud_selector_products'], 'cloud_selector_products');
		$template->add($this->lang['cloud_selector_add_product'], 'cloud_selector_add_product');
		$template->add($this->lang['cloud_selector_howto_add_product'], 'cloud_selector_howto_add_product');
		$template->add($this->lang['cloud_selector_equals'], 'cloud_selector_equals');
		$template->add($this->lang['cloud_selector_ccu_per_hour'], 'cloud_selector_ccu_per_hour');
		$template->add($this->lang['cloud_selector_product_name'], 'cloud_selector_product_name');
		$template->add($this->lang['cloud_selector_product_description'], 'cloud_selector_product_description');
		$template->add($s, 'network_select');

		$template->add($response->form);
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function network() {

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'network');
		$response->form = $form;

		$head['id']['title'] = $this->lang['cloud_selector_product_id'];
		$head['quantity']['title'] = $this->lang['cloud_selector_product_quantity'];
		$head['price']['title'] = $this->lang['cloud_selector_product_price'];
		$head['name']['title'] = $this->lang['cloud_selector_product_name'];
		$head['description']['title'] = $this->lang['cloud_selector_product_description'];
		$head['state']['title'] = $this->lang['cloud_selector_product_state'];
		$head['action_up']['title'] = '&#160;';
		$head['action_up']['sortable'] = false;
		$head['action_down']['title'] = '&#160;';
		$head['action_down']['sortable'] = false;

		$table = $response->html->tablebuilder( 'cloud-selector-table', $this->response->get_array($this->actions_name, 'network'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'cloud_selector_table';
		$table->head            = $head;
		$table->sort            = 'sort_id';
		$table->autosort        = true;
		$table->max				= $this->cloudselector->get_count_by_type("network");
		$table->identifier      = 'id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_type = "radio";
		$table->actions         = array('remove');
		$table->actions_name    = $this->actions_name;
		$table->form_action	    = $this->response->html->thisfile;
		$table->sort_link       = false;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$cloud_selector_array = $this->cloudselector->display_overview_per_type("network");
		$ta = '';
		foreach ($cloud_selector_array as $index => $cz) {

			$cloudproduct_sort_id = $cz["sort_id"];
			// sorting
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_selector_product_sort_up'];
			$a->label   = $this->lang['cloud_selector_product_sort_up'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'up';
			$a->href    = $this->response->get_url($this->actions_name, "up").'&cloud_selector_id='.$cz["id"];
			$product_sorting_up_action = $a->get_string();
			
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_selector_product_sort_down'];
			$a->label   = $this->lang['cloud_selector_product_sort_down'];
			$a->handler = 'onclick="wait();"';
			$a->css     = 'down';
			$a->href    = $this->response->get_url($this->actions_name, "down").'&cloud_selector_id='.$cz["id"];
			$product_sorting_down_action = $a->get_string();

			// state
			$product_state = $cz["state"];
			$product_state_action = '';
			if ($product_state == 1) {
				// disable action
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_selector_product_disable'];
				$a->label   = $this->lang['cloud_selector_product_disable'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'disable';
				$a->href    = $this->response->get_url($this->actions_name, "disable").'&cloud_selector_id='.$cz["id"];
			} else {
				// disable action
				$a = $this->response->html->a();
				$a->title   = $this->lang['cloud_selector_product_enable'];
				$a->label   = $this->lang['cloud_selector_product_enable'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'enable';
				$a->href    = $this->response->get_url($this->actions_name, "enable").'&cloud_selector_id='.$cz["id"];
			}
			$product_state_action = $a->get_string();

			$ta[] = array(
				'id' => $cz["id"],
				'quantity' => $cz["quantity"],
				'price' => $cz["price"],
				'name' => $cz["name"],
				'description' => $cz["description"],
				'state' => $product_state_action,
				'action_up' => $product_sorting_up_action,
				'action_down' => $product_sorting_down_action,

			);
		}
		$table->body = $ta;

		$response->table = $table;
		return $response;
	}




}

?>


