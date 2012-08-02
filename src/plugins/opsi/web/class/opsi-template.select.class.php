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


class opsi_template_select
{

var $identifier_name;
var $lang;
var $actions_name = 'opsi-select';



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
		$this->thisfile = $this->response->html->thisfile;
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
		$template = $this->response->html->template($this->tpldir.'/opsi-template-select.tpl.php');
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['opsi_title'], "opsi_title");
		$template->add($table, 'table');
		return $template;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
//		$this->response->html->debug();

		$arHead = array();
		$arHead['storage_state'] = array();
		$arHead['storage_state']['title'] ='&#160;';
		$arHead['storage_state']['sortable'] = false;

		$arHead['storage_icon'] = array();
		$arHead['storage_icon']['title'] ='&#160;';
		$arHead['storage_icon']['sortable'] = false;

		$arHead['storage_id'] = array();
		$arHead['storage_id']['title'] = $this->lang['opsi_id'];

		$arHead['storage_name'] = array();
		$arHead['storage_name']['title'] = $this->lang['opsi_name'];

		$arHead['storage_resource_id'] = array();
		$arHead['storage_resource_id']['title'] ='Res. '.$this->lang['opsi_id'];
		$arHead['storage_resource_id']['sortable'] = false;

		$arHead['storage_resource_ip'] = array();
		$arHead['storage_resource_ip']['title'] = $this->lang['opsi_ip'];
		$arHead['storage_resource_ip']['sortable'] = false;

		$arHead['storage_type'] = array();
		$arHead['storage_type']['title'] = $this->lang['opsi_type'];

		$arHead['storage_comment'] = array();
		$arHead['storage_comment']['title'] = $this->lang['opsi_comment'];
		$arHead['storage_comment']['sortable'] = false;

		$arHead['edit']['title'] = '&#160;';
		$arHead['edit']['sortable'] = false;


		// here we construct the storage table
		$deployment_tmp = new deployment();
		$deployment_tmp->get_instance_by_name("opsi-deployment");
		$storage_tmp = new storage();

		$table = $this->response->html->tablebuilder( 'opsi-table', $this->response->get_array($this->actions_name, 'select'));
		$table->css         = 'htmlobject_table';
		$table->border      = 0;
		$table->limit       = 10;
		$table->id          = 'Tabelle';
		$table->head        = $arHead;
		$table->sort        = 'storage_id';
		$table->max         = $storage_tmp->get_count_per_type($deployment_tmp->id);
		$table->form_action	= $this->response->html->thisfile;
		$table->init();

		$arBody = array();
		$storage_array = $storage_tmp->display_overview_per_type($deployment_tmp->id, $table->offset, $table->limit, $table->sort, $table->order);

		if(count($storage_array) > 0) {

			foreach ($storage_array as $index => $storage_db) {
				$storage_action = "";
				$storage = new storage();
				$storage->get_instance_by_id($storage_db["storage_id"]);

				$storage_resource = new resource();
				$storage_resource->get_instance_by_id($storage->resource_id);
				$deployment = new deployment();
				$deployment->get_instance_by_id($storage->type);
				$resource_icon_default="/openqrm/base/img/resource.png";
				$storage_icon="/openqrm/base/plugins/local-storage/img/storage.png";
				$state_icon="/openqrm/base/img/$storage_resource->state.png";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
					$state_icon="/openqrm/base/img/unknown.png";
				}
				if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
					$resource_icon_default=$storage_icon;
				}
				$storage_icon='<img width="24" height="24" src="'.$resource_icon_default.'">';
				$edit = '';
				if (!strcmp($storage_resource->state, "active")) {
					$edit = '<a href="https://'.$storage_resource->ip.':4447/configed/" target="_BLANK" class="edit">open UI</a>';
				}

				$arBody[] = array(
					'storage_state' => '<img src="'.$state_icon.'">',
					'storage_icon' => $storage_icon,
					'storage_id' => $storage->id,
					'storage_name' => $storage->name,
					'storage_resource_id' => $storage->resource_id,
					'storage_resource_ip' => $storage_resource->ip,
					'storage_type' => "$deployment->storagedescription",
					'storage_comment' => $storage->comment,
					'edit' => $edit,
				);
			}
			$table->autosort = false;
			$table->sort_link = false;
			$table->body = $arBody;

		} else {
			$table = $this->response->html->div();
			$table->add($this->lang['opsi_add_storages']);
		}
		return $table;
	}



}

?>


