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



class fai_template_select
{

var $identifier_name;
var $lang;
var $actions_name = 'fai-select';



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
		$template = $this->response->html->template($this->tpldir."/fai-template-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($this->lang['fai_title'], "fai_title");
		$template->add($table, 'table');
		$template->add($this->openqrm->get('baseurl'), 'baseurl');
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
//		$this->__response->html->debug();

		$arHead = array();
		$arHead['storage_state'] = array();
		$arHead['storage_state']['title'] =' ';
		$arHead['storage_state']['sortable'] = false;

		$arHead['storage_icon'] = array();
		$arHead['storage_icon']['title'] =' ';
		$arHead['storage_icon']['sortable'] = false;

		$arHead['storage_id'] = array();
		$arHead['storage_id']['title'] ='ID';

		$arHead['storage_name'] = array();
		$arHead['storage_name']['title'] ='Name';

		$arHead['storage_resource_id'] = array();
		$arHead['storage_resource_id']['title'] ='Res.ID';
		$arHead['storage_resource_id']['sortable'] = false;

		$arHead['storage_resource_ip'] = array();
		$arHead['storage_resource_ip']['title'] ='Ip';
		$arHead['storage_resource_ip']['sortable'] = false;

		$arHead['storage_type'] = array();
		$arHead['storage_type']['title'] ='Type';

		$arHead['storage_comment'] = array();
		$arHead['storage_comment']['title'] ='Comment';

		$table = $this->response->html->tablebuilder( 'fai-table', $this->response->get_array($this->actions_name, 'select'));
		#$table->lang            = $this->locale->get_lang( 'tablebuilder.ini' );
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'Tabelle';
		$table->head            = $arHead;
		$table->sort            = 'storage_id';
		$table->autosort        = true;
		$table->form_action	= $this->response->html->thisfile;

		// here we construct the storage table
		$deployment_tmp = new deployment();
		$deployment_tmp->get_instance_by_name("fai-deployment");
		$storage_tmp = new storage();
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
				$resource_icon_default="/img/resource.png";
				$storage_icon="/plugins/fai/img/plugin.png";
				$state_icon = $this->openqrm->get('baseurl')."/img/".$storage_resource->state.".png";
				if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
					$resource_icon_default=$storage_icon;
				}
				$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;
				$state_content="<img width='24' height='24' src=".$resource_icon_default.">";
				//if (!strcmp($storage_resource->state, "active")) {
				//	$state_content="<a href='http://".$storage_resource->ip."/fai_web/'><img width=24 height=24 src=".$resource_icon_default."><br><small>(open UI)</small></a>";
				//}

				$arBody[] = array(
					'storage_state' => "<img width='24' height='24' src=".$state_icon.">",
					'storage_icon' => $state_content,
					'storage_id' => $storage->id,
					'storage_name' => $storage->name,
					'storage_resource_id' => $storage->resource_id,
					'storage_resource_ip' => $storage_resource->ip,
					'storage_type' => "$deployment->storagedescription",
					'storage_comment' => $storage->comment,
				);
			}

			$table->body = $arBody;
			$table->max = $storage_tmp->get_count_per_type($deployment_tmp->id);

		} else {
			$table = $this->response->html->div();
			$table->add($this->lang['fai_add_storages']);
		}
		return $table;
	}



}

?>


