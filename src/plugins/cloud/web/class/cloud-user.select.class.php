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



class cloud_user_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-userselect';



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
		// central user management ?
		$central_user_management = false;
		if (file_exists($this->webdir."/plugins/ldap/.running")) {
			$central_user_management = true;
		}
		$this->central_user_management = $central_user_management;
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
		$template = $this->response->html->template($this->tpldir."/cloud-user-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($response->table, 'table');
		$template->add($this->lang['cloud_user_management'], 'title');
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

		// to get the user group name
		require_once $this->webdir."/plugins/cloud/class/clouduser.class.php";
		require_once $this->webdir."/plugins/cloud/class/cloudusergroup.class.php";
		$cloud_group = new cloudusergroup();
		$cloud_user = new clouduser();
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";

		$head['cu_status']['title'] = '&#160;';
		$head['cu_status']['sortable'] = false;
		$head['cu_id']['title'] = $this->lang['cloud_user_id'];
		$head['cu_name']['title'] = $this->lang['cloud_user_name'];
		$head['cu_cg_id']['title'] = $this->lang['cloud_user_group'];
		$head['cu_informations']['title'] = $this->lang['cloud_user_informations'];
		$head['cu_informations']['sortable'] = false;
		$head['cu_ccunits']['title'] = $this->lang['cloud_user_ccunits'];
		$head['cu_actions']['title'] =  '&#160;';
		$head['cu_actions']['sortable'] =  false;

		$table = $response->html->tablebuilder( 'cloud_user_table', $response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->limit           = 10;
		$table->id              = 'cloud_users';
		$table->head            = $head;
		$table->sort            = 'cu_id';
		$table->sort_link       = false;
		$table->autosort        = false;
		$table->max		        = $cloud_user->get_count();
		$table->identifier      = 'cu_id';
		$table->identifier_name = $this->identifier_name;
		if ($this->central_user_management) {
			$table->actions = array('enable', 'disable');
		} else {
			$table->actions = array('enable', 'disable', 'delete');
		}
		$table->actions_name    = $this->actions_name;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$cloud_user_array = $cloud_user->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_user_array as $index => $cz) {
			$cloud_user->get_instance_by_id($cz['cu_id']);
			$cloud_user_informations = $cloud_user->forename." ".$cloud_user->lastname."<br>".$cloud_user->email."<br>".$cloud_user->city." - ".$cloud_user->country;
			// user group
			$cloud_group->get_instance_by_id($cz['cu_cg_id']);
			// lang
			if (!strlen($cz['cu_lang'])) {
				$cz['cu_lang'] = "-";
			}
			// status
			$user_state = '';
			if ($cz['cu_status'] == 1) {
			    $user_state = '<img title="'.$this->lang['cloud_user_active'].'" alt="'.$this->lang['cloud_user_active'].'" height="20" width="20" src="/openqrm/base/img/active.png" border="0">';
			} else {
			    $user_state = '<img title="'.$this->lang['cloud_user_inactive'].'" alt="'.$this->lang['cloud_user_inactive'].'" height="20" width="20" src="/openqrm/base/img/idle.png" border="0">';
			}
			// update action
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_user_update'];
			$a->label   = $this->lang['cloud_user_update'];
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, "update").'&cloud_user_id='.$cz["cu_id"];

			$ta[] = array(
				'cu_status' => $user_state,
				'cu_id' => $cz["cu_id"],
				'cu_name' => $cz['cu_name'],
				'cu_cg_id' => $cloud_group->name,
				'cu_informations' => $cloud_user_informations,
				'cu_ccunits' => $cz['cu_ccunits'],
				'cu_actions' => $a->get_string(),
			);
		}

		$table->body = $ta;

		$response->table = $table;
		return $response;
	}




}

?>


