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



class cloud_request_select
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-requestselect';



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
		$response = $this->select();
		$template = $this->response->html->template($this->tpldir."/cloud-request-select.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($response->table, 'table');
		$template->add($this->lang['cloud_request_management'], 'title');
		$template->add($this->lang['cloud_request'], 'cloud_request');
		$template->add($this->lang['cloud_request_details'], 'cloud_request_details');
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

		require_once $this->webdir."/plugins/cloud/class/clouduser.class.php";
		$cloud_user = new clouduser();
		require_once $this->webdir."/plugins/cloud/class/cloudusergroup.class.php";
		$cloud_group = new cloudusergroup();
		require_once $this->webdir."/plugins/cloud/class/cloudrequest.class.php";
		$cloud_request = new cloudrequest();

		
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";

		$head['cr_status']['title'] = '&#160;';
		$head['cr_status']['sortable'] = false;
		$head['cr_id']['title'] = $this->lang['cloud_request_id'];
		$head['cr_cu_id']['title'] = $this->lang['cloud_request_user'];
		$head['cr_request_time']['title'] = $this->lang['cloud_request_time'];
		$head['cr_start']['title'] = $this->lang['cloud_request_start_time'];
		$head['cr_stop']['title'] = $this->lang['cloud_request_stop_time'];
		$head['cr_appliance_id']['title'] = $this->lang['cloud_request_app_id'];
		$head['cr_details']['title'] = '&#160;';
		$head['cr_details']['sortable'] = false;

		$table = $response->html->tablebuilder( 'cloud_request_table', $response->get_array($this->actions_name, 'select'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'cloud_requests';
		$table->head            = $head;
		$table->sort            = 'cr_id';
		$table->order           = 'DESC';
		$table->sort_link       = false;
		$table->autosort        = false;
		$table->limit           = 10;
		$table->max		= $cloud_request->get_count();
		$table->identifier      = 'cr_id';
		$table->identifier_name = $this->identifier_name;
		$table->actions         = array('approve', 'cancel', 'delete', 'deny', 'deprovision');
		$table->actions_name    = $this->actions_name;
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 40, "text" => 40),
			array("value" => 50, "text" => 50),
		);
		$table->init();

		$cloud_request_array = $cloud_request->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$ta = '';
		foreach ($cloud_request_array as $index => $cz) {
			$cloud_request->get_instance_by_id($cz['cr_id']);
			$cloud_user->get_instance_by_id($cloud_request->cu_id);
			
			// status
			switch ($cloud_request->status) {
				case 1:
					$cr_status="new";
					break;
				case 2:
					$cr_status="approve";
					break;
				case 3:
					$cr_status="active";
					break;
				case 4:
					$cr_status="deny";
					break;
				case 6:
					$cr_status="done";
					break;
				case 7:
					$cr_status="no-res";
					break;
				default:
					$cr_status="";
					break;
			}

			// details action
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_request_details'];
			$a->label   = $this->lang['cloud_request_details'];
			$a->handler = 'onclick="javascript:cloudopenPopup('.$cloud_request->id.'); return false;"';
			$a->css     = 'edit';
			$a->href    = $this->response->get_url($this->actions_name, 'details')."&".$this->identifier_name."=".$cloud_request->id;

			$ta[] = array(
				'cr_status' => $cr_status,
				'cr_id' => $cloud_request->id,
				'cr_cu_id' => $cloud_user->name,
				'cr_request_time' => date("d-m-Y H-i", $cloud_request->request_time),
				'cr_start' => date("d-m-Y H-i", $cloud_request->start),
				'cr_stop' => date("d-m-Y H-i", $cloud_request->stop),
				'cr_appliance_id' => $cloud_request->appliance_id,
				'cr_details' => $a->get_string(),
			);
		}

		$table->body = $ta;

		$response->table = $table;
		return $response;
	}




}

?>


