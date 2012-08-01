<?php
/**
 * Removes discovered XenServer Hosts
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_discovery_delete
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_action';
/**
* name of identifier
* @access public
* @var string
*/
var $identifier_name = 'xenserver_ad_id';

/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_msg";
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
		$this->rootdir = $this->openqrm->get('webdir');
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

		if ($this->response->html->request()->get($this->identifier_name) === '') {
			$this->response->redirect($this->response->get_url($this->actions_name, ''));
		}
		$response = $this->delete();
//		if(isset($response->error)) {
//			$_REQUEST[$this->message_param] = $response->error;
//		}
		if(isset($response->msg)) {
			$this->response->redirect($this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg));
		}
		$template = $this->response->html->template($this->tpldir.'/citrix-storage-discovery-delete.tpl.php');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->add($this->lang['citrix_host_discovery_confirm_delete'], 'confirm_delete');
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
		if(!$form->get_errors() && $this->response->submit()) {
			$request = $form->get_request($this->identifier_name);

			if(isset($request) && is_array($request)) {
				$errors  = array();
				$message = array();
				foreach($request as $key => $esx_ad_id) {
					// get name before delete
					$this->discovery->get_instance_by_id($esx_ad_id);

					// appliance exist ?
					$appliance = new appliance();
					$appliance->get_instance_by_name($this->discovery->xenserver_ad_hostname);
					if ($appliance->id > 0) {
						$resource = new resource();
						$resource->get_instance_by_id($appliance->resources);
						if ($resource->id > 0) {
							// kernel exist ?
							$kernel_name = "XenServer".$resource->id;
							$kernel = new kernel();
							$kernel->get_instance_by_name($kernel_name);
							if ($kernel->id > 1) {
								$kernel->remove($kernel->id);
							}
							// image exist ?
							$image_name = "XenServer".$resource->id;
							$image = new image();
							$image->get_instance_by_name($image_name);
							if ($image->id > 1) {
								$image->remove($image->id);
							}
							// local storage exists ?
							$local_storage_name = "resource".$resource->id;
							$local_storage = new storage();
							$local_storage->get_instance_by_name($local_storage_name);
							if ($local_storage->id > 0) {
								$local_storage->remove($local_storage->id);
							}
							// citrix storage exists ?
							$citrix_storage_name = "XenServer".$resource->id;
							$citrix_storage = new storage();
							$citrix_storage->get_instance_by_name($citrix_storage_name);
							if ($citrix_storage->id > 0) {
								$citrix_storage->remove($citrix_storage->id);
							}
							$resource->remove($resource->id, $resource->mac);
						}
						$appliance->remove($appliance->id);
					}
					//delete here;
					$error = $this->discovery->remove($esx_ad_id);
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
		$todelete = $this->response->html->request()->get($this->identifier_name);
		$response =$this->response;
		$form     = $response->get_form($this->actions_name, 'delete');
		if( $todelete !== '' ) {
			$i = 0;
			foreach($todelete as $esx_ad_id) {
				$this->discovery->get_instance_by_id($esx_ad_id);
				$d['param_f'.$i]['label']                       = "<nobr>ID: ".$esx_ad_id."<br>".$this->discovery->xenserver_ad_ip."/".$this->discovery->xenserver_ad_mac."</nobr>";
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
