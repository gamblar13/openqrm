<?php
/**
 * Removes discovered ESX Hosts
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

class vmware_esx_discovery_delete
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* name of identifier
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_ad_id';

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
	function __construct($response, $db) {
		$this->__response = $response;
		$this->__rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->__db = $db;
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

		if ($this->__response->html->request()->get($this->identifier_name) === '') {
			$this->__response->redirect($this->__response->get_url($this->actions_name, ''));
		}
		$response = $this->delete();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->__response->redirect($this->__response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$template = $this->__response->html->template($this->tpldir.'/vmware-esx-discovery-delete.tpl.php');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['vmware_esx_host_discvoery_confirm_delete'], 'confirm_delete');
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function delete() {

		$response = $this->get_response();
		$form = $response->form;
		if(!$form->get_errors() && $this->__response->submit()) {
			$request = $form->get_request($this->identifier_name);

			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();
				foreach($request as $key => $esx_ad_id) {
					// get name before delete
					$this->__db->get_instance_by_id($esx_ad_id);

					//delete here;
					$error = $this->__db->remove($esx_ad_id);
					$message[] = sprintf($this->lang['msg_removed'], $esx_ad_id);
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		}
		return $response;
	}



	function get_response() {
		$todelete = $this->__response->html->request()->get($this->identifier_name);
		$response =$this->__response;
		$form     = $response->get_form($this->actions_name, 'delete');
		if( $todelete !== '' ) {
			$i = 0;
			foreach($todelete as $esx_ad_id) {
				$this->__db->get_instance_by_id($esx_ad_id);
				$d['param_f'.$i]['label']                       = "<nobr>ID: ".$esx_ad_id."<br>".$this->__db->vmw_esx_ad_ip."/".$this->__db->vmw_esx_ad_mac."</nobr>";
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';
				$d['param_f'.$i]['object']['attrib']['id']      = $this->identifier_name.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $esx_ad_id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
		}
		$form->add($d);
		$response->form = $form;
		return $response;
	}



}
?>
