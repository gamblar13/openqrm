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
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
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
		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}

		$response = $this->response;
		// identifier array
		$identifier_ar = $this->response->html->request()->get($this->identifier_name);
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
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
	}
}



?>


