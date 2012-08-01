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


class cloud_ui_transaction
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';

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
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
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
		$template = $this->response->html->template("./tpl/cloud-ui.transaction.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($table, 'table');
		$template->add($this->lang['cloud_ui_transaction_management'], 'title');
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

		$head['ct_id']['title'] = $this->lang['cloud_ui_transaction_ct_id'];
		$head['ct_time']['title'] = $this->lang['cloud_ui_transaction_time'];
		$head['ct_cr_id']['title'] = $this->lang['cloud_ui_transaction_cr_id'];
		$head['ct_ccu_charge']['title'] = $this->lang['cloud_ui_transaction_ccu_charge'];
		$head['ct_ccu_balance']['title'] = $this->lang['cloud_ui_transaction_ccu_balance'];
		$head['ct_reason']['title'] = $this->lang['cloud_ui_transaction_reason'];
		$head['ct_comment']['title'] = $this->lang['cloud_ui_transaction_comment'];

		require_once $this->rootdir."/plugins/cloud/class/cloudtransaction.class.php";
		$cloud_transaction = new cloudtransaction();
		
		$table = $this->response->html->tablebuilder( 'cloud_transaction_table', $this->response->get_array($this->actions_name, 'transaction'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'cloud_transactions';
		$table->head            = $head;
		$table->sort            = 'ct_id';
		$table->order			= 'DESC';
		$table->sort_link       = false;
		$table->form_action     = $this->response->html->thisfile;
		$table->max             = $cloud_transaction->get_count_per_clouduser($this->clouduser->id);
		$table->autosort        = true;
		$table->limit_select    = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$cloud_array = $cloud_transaction->display_overview_per_clouduser($this->clouduser->id, $table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_array as $index => $ct) {
			$cloud_transaction_time = date("d-m-Y H-i", $ct["ct_time"]);
			$ta[] = array(
				'ct_id' => $ct["ct_id"],
				'ct_time' => $cloud_transaction_time,
				'ct_cr_id' => $ct["ct_cr_id"],
				'ct_ccu_charge' => "-".$ct["ct_ccu_charge"],
				'ct_ccu_balance' => $ct["ct_ccu_balance"],
				'ct_reason' => $ct["ct_reason"],
				'ct_comment' => $ct["ct_comment"],
			);
		}
		$table->body = $ta;
		return $table;
	}


}

?>


