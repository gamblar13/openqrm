<?php
/**
 * novnc Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class novnc_login
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'novnc_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'novnc_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "novnc_msg";
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

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
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->user     = $openqrm->user();
		$this->basedir  = $this->openqrm->get('basedir');
		$this->webdir   = $this->openqrm->get('webdir');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$response = $this->login();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/novnc-login.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $response->appliance->name), 'label');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function login() {
		$this->appliance_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->appliance_id);
		
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if(!$form->get_errors()) {
				$port        = $form->get_request('port');
				$appliance_id = $this->response->html->request()->get($this->identifier_name);
				// use the appliance link hook
				require_once $this->webdir."/plugins/novnc/openqrm-novnc-remote-console-hook.php";
				$msg = array();
				// get the parameters from the plugin config file
				$novnc_config=$this->basedir."/plugins/novnc/etc/openqrm-plugin-novnc.conf";
				$store = openqrm_parse_conf($novnc_config);
				extract($store);
				$appliance = new appliance();
				$resource = new resource();
				$appliance->get_instance_by_id($appliance_id);
				$resource->get_instance_by_id($appliance->resources);
				$resource_vnc_port = $this->response->html->request()->get('port');
				$resource_mac = $resource->mac;
				// special mac for openQRM
				if ($resource->id == 0) {
					$resource_mac = "x:x:x:x:x:x";
				}
				openqrm_novnc_remote_console($resource->ip, $resource_vnc_port, $appliance_id, $resource_mac, $resource->hostname);
				$response->msg = sprintf($this->lang['login_msg'], $appliance->name);
			}
		}
		return $response;
	}

	
	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'login');
		$appliance_id = $this->response->html->request()->get($this->identifier_name);
		$appliance = new appliance();
		$appliance->get_instance_by_id($appliance_id);
		$response->appliance = $appliance;
		
		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		for ($n=1; $n<999; $n++) {
			$port_arr[] = array($n, $n);
		}
		$d['port']['label']                         = $this->lang['form_port'];
		$d['port']['object']['type']                = 'htmlobject_select';
		$d['port']['object']['attrib']['name']      = 'port';
		$d['port']['object']['attrib']['index']     = array(0,1);
		$d['port']['object']['attrib']['options']   = $port_arr;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	
	
}
?>
