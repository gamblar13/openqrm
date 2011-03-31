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




class wakeuponlan_wakeup
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'wakeuponlan-wakeup';
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
				$resource_wakeup = new resource();
				$resource_wakeup->get_instance_by_id($ident_id);
				$resource_wakeup_mac = $resource_wakeup->mac;
				// check if wakeuponlan is configured
				$wakeuponlan_state = $resource_wakeup->get_resource_capabilities("SFO");
				if ($wakeuponlan_state != 1) {
					$response_message .= $this->lang['wakeuponlan_disabled']."<br>";
					continue;
				}
				$wakeuponlan_command = $this->openqrm_base_dir."/openqrm/plugins/wakeuponlan/bin/openqrm-wakeuponlan wakeup -m ".$resource_wakeup_mac;
				global $OPENQRM_SERVER_IP_ADDRESS;
				$OPENQRM_SERVER_IP_ADDRESS=$this->openqrm_ip;
				$this->openqrm->send_command($wakeuponlan_command);
				// set state to transition
				$resource_fields=array();
				$resource_fields["resource_state"]="transition";
				$resource_wakeup->update_info($ident_id, $resource_fields);
				$response_message .= $this->lang['wakeuponlan_woke_up_resource']."".$ident_id."<br>";
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


