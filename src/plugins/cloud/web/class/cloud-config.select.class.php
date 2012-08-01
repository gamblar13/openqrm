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



class cloud_config_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-configselect';



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
		$table = $this->select();
		$template = $this->response->html->template($this->tpldir."/cloud-config-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($table, 'table');
		$template->add($this->lang['cloud_config_management'], 'title');
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
	function select() {
		// $this->response->html->debug();

		$head['cc_id']['title'] = $this->lang['cloud_config_id'];
		$head['cc_key']['title'] = $this->lang['cloud_config_key'];
		$head['cc_value']['title'] = $this->lang['cloud_config_value'];
		$head['cc_description']['title'] = $this->lang['cloud_config_description'];
		$head['cc_description']['sortable'] = false;

		$table = $this->response->html->tablebuilder( 'cloud_config_table', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'table_cloud_config';
		$table->head            = $head;
		$table->sort            = 'cc_id';
		$table->autosort        = false;
		$table->limit           = 50;
		$table->max				= $this->config->get_count();
		$table->actions_name    = $this->actions_name;
		$table->form_action		= $this->response->html->thisfile;
		$table->sort_link       = false;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$cloud_config_array = $this->config->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_config_array as $index => $cc) {
			$cc_id = $cc["cc_id"];

			$ta[] = array(
				'cc_id' => $cc_id,
				'cc_key' => $cc["cc_key"],
				'cc_value' => "<nobr>".$cc["cc_value"]."</nobr>",
				'cc_description' => $this->lang['cloud_config_description_'.$cc_id],
			);
		}
		$table->body = $ta;
		return $table;
	}




}

?>


