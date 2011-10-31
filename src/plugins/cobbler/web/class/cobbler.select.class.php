<?php

/*
	This file is part of openQRM.

	openQRM is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2
	as published by the Free Software Foundation.

	openQRM is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

	Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/PHPLIB.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/storage.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/plugins/cobbler/class/cobbler.class.php";


class cobbler_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cobbler-select';



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
	function __construct($response, $db) {
		$this->__response = $response;
		$this->__db = $db;
		$this->thisfile = $this->__response->html->thisfile;
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
		$template = $this->__response->html->template("./tpl/cobbler-select.tpl.php");
		$template->add($this->__response->html->thisfile, "thisfile");
		$template->add($this->lang['cobbler_title'], "cobbler_title");
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

		$table = $this->__response->html->tablebuilder( 'cobbler-table', $this->__response->get_array($this->actions_name, 'select'));
		#$table->lang            = $this->locale->get_lang( 'tablebuilder.ini' );
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'Tabelle';
		$table->head            = $arHead;
		$table->sort            = 'storage_id';
		$table->autosort        = true;
		$table->identifier      = 'storage_id';
		$table->identifier_name = $this->identifier_name;
		$table->actions         = '';
		$table->actions_name    = $this->actions_name;
		$table->form_action		= $this->__response->html->thisfile;

		// here we construct the storage table

		$deployment = new deployment();
		$deployment->get_instance_by_name("cobbler-deployment");
		$storage_tmp = new storage();
		$arBody = array();
		$storage_array = $storage_tmp->display_overview_per_type($deployment->id, $table->offset, $table->limit, $table->sort, $table->order);

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
				$storage_icon="/openqrm/base/plugins/cobbler/img/storage.png";
				$state_icon="/openqrm/base/img/$storage_resource->state.png";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
					$state_icon="/openqrm/base/img/unknown.png";
				}
				if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
					$resource_icon_default=$storage_icon;
				}
				$state_content="<img width=24 height=24 src=".$resource_icon_default.">";
				if (!strcmp($storage_resource->state, "active")) {
					$state_content="<a href='http://".$storage_resource->ip."/cobbler_web/'><img width=24 height=24 src=".$resource_icon_default."><br><small>(open UI)</small></a>";
				}

				$arBody[] = array(
					'storage_state' => "<img src=".$state_icon.">",
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
			$table->max = $storage_tmp->get_count("all");

		} else {
			$table = $this->__response->html->div();
			$table->add($this->lang['cobbler_add_storages']);
		}
		return $table;
	}



}

?>


