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




class wakeuponlan_disable
{

var $tpldir;
var $identifier_name;
var $lang;
var $actions_name = 'wakeuponlan-disable';
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
				$resource_disable = new resource();
				$resource_disable->get_instance_by_id($ident_id);
				$resource_disable->set_resource_capabilities("SFO", "0");
				$response_message .= $this->lang['wakeuponlan_disabled_resource']."".$ident_id."<br>";
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


