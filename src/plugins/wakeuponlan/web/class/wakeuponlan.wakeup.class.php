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
				$resource_wakeup = new resource();
				$resource_wakeup->get_instance_by_id($ident_id);
				$resource_wakeup_mac = $resource_wakeup->mac;
				// check if wakeuponlan is configured
				$wakeuponlan_state = $resource_wakeup->get_resource_capabilities("SFO");
				if ($wakeuponlan_state != 1) {
					$response_message .= $this->lang['wakeuponlan_disabled']."<br>";
					continue;
				}
				$wakeuponlan_command = $this->openqrm_base_dir."/plugins/wakeuponlan/bin/openqrm-wakeuponlan wakeup -m ".$resource_wakeup_mac;
				global $OPENQRM_SERVER_IP_ADDRESS;
				$OPENQRM_SERVER_IP_ADDRESS=$this->openqrm_ip;
				$this->openqrm_server->send_command($wakeuponlan_command);
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
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
	}
}



?>


