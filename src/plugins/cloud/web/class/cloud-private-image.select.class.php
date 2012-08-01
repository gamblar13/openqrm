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


class cloud_private_image_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-private-imageselect';



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
		require_once $this->webdir."/plugins/cloud/class/clouduser.class.php";
		$this->cloud_user = new clouduser();
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();
		require_once $this->webdir."/plugins/cloud/class/cloudprivateimage.class.php";
		$this->cloudprivateimage = new cloudprivateimage();
		$this->image = new image();
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
		$response = $this->select();
		$template = $this->response->html->template($this->tpldir."/cloud-private-image-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($response->table, 'table');
		$template->add($this->lang['cloud_private_image_management'], 'title');
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
	function select() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select');
		$response->form = $form;

		$head['image_icon']['title'] = '&#160;';
		$head['image_icon']['sortable'] = false;
		$head['image_id']['title'] = $this->lang['cloud_private_image_id'];
		$head['image_name']['title'] = $this->lang['cloud_private_image_name'];
		$head['image_version']['title'] = $this->lang['cloud_private_image_version'];
		$head['image_type']['title'] = $this->lang['cloud_private_image_type'];
		$head['image_comment']['title'] = $this->lang['cloud_private_image_comment'];
		$head['image_assigned']['title'] = $this->lang['cloud_private_image_assigned'];
		$head['image_assigned']['sortable'] = false;
		$head['image_actions']['title'] = '&#160;';
		$head['image_actions']['sortable'] = false;

		$table = $response->html->tablebuilder( 'cloud_private_image_table', $this->response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'cloud_private_image_table';
		$table->head            = $head;
		$table->sort            = 'image_id';
		$table->autosort        = true;
		$table->max		        = $this->image->get_count();
		$table->form_action	= $this->response->html->thisfile;
		$table->sort_link       = false;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$image_icon = "<img width='24' height='24' src='/openqrm/base/img/image.png'>";
		$cloud_private_image_array = $this->image->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_private_image_array as $index => $cz) {

			// update action
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_private_image_update'];
			$a->label   = $this->lang['cloud_private_image_update'];
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, "update").'&cloud_private_image_id='.$cz["image_id"];
			if (!strlen($cz["image_version"])) {
				$cz["image_version"] = '&#160;';
			}
			if (!strlen($cz["image_comment"])) {
				$cz["image_comment"] = '&#160;';
			}

			// private image config existing
			$assigned_to = '';
			if ($this->cloudprivateimage->exists_by_image_id($cz["image_id"])) {
				$this->cloudprivateimage->get_instance_by_image_id($cz["image_id"]);
				if ($this->cloudprivateimage->cu_id > 0) {
					$this->cloud_user->get_instance_by_id($this->cloudprivateimage->cu_id);
					$assigned_to = $this->cloud_user->name;
				} else if ($this->cloudprivateimage->cu_id == 0) {
					// 0 == all
					$assigned_to = $this->lang['cloud_private_image_everybody'];
				} else if ($this->cloudprivateimage->cu_id < 0) {
					$assigned_to = $this->lang['cloud_private_image_nobody'];
				}
			} else {
				$assigned_to = $this->lang['cloud_private_image_nobody'];
			}

			$ta[] = array(

				'image_icon' => $image_icon,
				'image_id' => $cz["image_id"],
				'image_name' => $cz["image_name"],
				'image_version' => $cz["image_version"],
				'image_type' => $cz["image_type"],
				'image_comment' => $cz["image_comment"],
				'image_assigned' => $assigned_to,
				'image_actions' => $a->get_string(),
			);
		}
		$table->body = $ta;

		$response->table = $table;
		return $response;
	}




}

?>


