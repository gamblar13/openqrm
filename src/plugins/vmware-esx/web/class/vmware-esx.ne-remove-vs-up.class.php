<?php
/**
 * ESX Hosts remove Uplink from VSwitch
 *
 * This file is part of openQRM.
 *
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Matt Rechenburg <matt@openqrm-enterprise.com>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class vmware_esx_ne_remove_vs_up
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'uplink';
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
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->__rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->response->params['appliance_id'] = $this->response->html->request()->get('appliance_id');
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$appliance_id = $this->response->html->request()->get('appliance_id');
		if($appliance_id === '') {
			return false;
		}
		$vs_name = $this->response->html->request()->get('vs_name');
		if($vs_name === '') {
			return false;
		}
		$uplink = $this->response->html->request()->get('uplink');
		if($uplink === '') {
			return false;
		}
		// set ENV
		$virtualization = new virtualization();
		$appliance	= new appliance();
		$resource	= new resource();
		$openqrm	= new resource();
		$appliance->get_instance_by_id($appliance_id);
		$resource->get_instance_by_id($appliance->resources);
		$openqrm->get_instance_by_id(0);
		$virtualization->get_instance_by_id($appliance->virtualization);
		$this->resource		= $resource;
		$this->openqrm		= $openqrm;
		$this->appliance	= $appliance;
		$this->virtualization = $virtualization;
		$this->statfile_vm = 'vmware-esx-stat/'.$resource->ip.'.vm_list';
		$this->statfile_ne = 'vmware-esx-stat/'.$resource->ip.'.net_config';
		$this->statfile_ds = 'vmware-esx-stat/'.$resource->ip.'.ds_list';
		$this->vmware_mac_base = "00:50:56";
		$this->vs_name = $vs_name;
		$this->uplink = $uplink;
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
		$this->init();
		$response = $this->ne_remove_vs_up();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'ne', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/vmware-esx-ne-remove-vs-up.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add(sprintf($this->lang['label'], $this->uplink), 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove Uplink from VSwitch
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function ne_remove_vs_up() {
		$response = $this->get_response();
		$uplink	= $response->html->request()->get('uplink');
		$vs_name = $response->html->request()->get('vs_name');
		$form     = $response->form;
		if( $uplink !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			$d['param_f'.$i]['label']                       = $uplink;
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
			$d['param_f'.$i]['object']['attrib']['name']    = 'uplink';
			$d['param_f'.$i]['object']['attrib']['value']   = $uplink;
			$d['param_f'.$i]['object']['attrib']['checked'] = true;
			$i++;
			// add vs_name
			$d['param_f'.$i]['label']                       = ' ';
			$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
			$d['param_f'.$i]['object']['attrib']['type']    = 'hidden';
			$d['param_f'.$i]['object']['attrib']['name']    = 'vs_name';
			$d['param_f'.$i]['object']['attrib']['value']   = $vs_name;
			
			$form->add($d);
//			if(!$form->get_errors() && $response->submit()) {
			if($response->submit()) {
				global $OPENQRM_SERVER_BASE_DIR;
				if ($vs_name === 'vSwitch0') {
					$response->msg = sprintf($this->lang['msg_not_removing'], $uplink);
				} else {
					$command  = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx-network remove_vs_up -i ".$this->resource->ip." -n ".$vs_name." -u ".$uplink;
					if(file_exists($this->statfile_ne)) {
						unlink($this->statfile_ne);
					}
					$this->resource->send_command($this->openqrm->ip, $command);
					while (!file_exists($this->statfile_ne)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					$response->msg = sprintf($this->lang['msg_removed'], $uplink);
				}
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'ne_remove_vs_up');
		$response->form = $form;
		return $response;
	}

}
?>
