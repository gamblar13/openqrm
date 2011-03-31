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




class wakeuponlan_sleep
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'wakeuponlan-sleep';
var $openqrm_base_dir;
var $openqrm;
var $openqrm_ip;
var $event;


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
		if ($this->__response->html->request()->get($this->identifier_name) === '') {
			$this->__response->redirect($this->__response->get_url($this->actions_name, ''));
		}

		$response = $this->__response;
		// identifier array
		$identifier_ar = $this->__response->html->request()->get($this->identifier_name);
		if( $identifier_ar !== '' ) {
			$i = 0;
			foreach($identifier_ar as $ident_id) {
				// get resource
				$resource_sleep = new resource();
				$resource_sleep->get_instance_by_id($ident_id);
				// check if wakeuponlan is enabled
				$wakeuponlan_state = $resource_sleep->get_resource_capabilities("SFO");
				if ($wakeuponlan_state != 1) {
					$response_message .= $this->lang['wakeuponlan_disabled']."<br>";
					continue;
				}
				# power of resource
				$resource_sleep->send_command($resource_sleep->ip, "halt");
				// set state to off
				$resource_fields=array();
				$resource_fields["resource_state"]="off";
				$resource_sleep->update_info($ident_id, $resource_fields);
				$response_message .= $this->lang['wakeuponlan_set_resource_to_sleep']."".$ident_id."<br>";
				$i++;
			}
		}
		// redirect to select
		if(isset($response_message)) {
			$response->msg = $response_message;
			$this->__response->redirect($this->__response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
	}
}



?>


