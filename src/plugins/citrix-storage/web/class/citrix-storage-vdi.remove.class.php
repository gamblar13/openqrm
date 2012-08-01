<?php
/**
 * citrix-Storage Remove Volume(s)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_vdi_remove
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_vdi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_vdi_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'citrix_identifier';
/**
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'citrix_tab';
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
		$storage_id       = $this->response->html->request()->get('storage_id');
		$storage          = new storage();
		$resource         = new resource();
		$deployment       = new deployment();
		$this->storage    = $storage->get_instance_by_id($storage_id);
		$this->resource   = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment = $deployment->get_instance_by_id($storage->type);
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
		$response = $this->remove();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/citrix-storage-vdi-remove.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {
		$response = $this->get_response();
		$vdi_names  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $vdi_names !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($vdi_names as $ex) {
				$d['param_f'.$i]['label']                       = $ex;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$name       = $this->openqrm->admin()->name;
				$pass       = $this->openqrm->admin()->password;
				$storage    = new storage();
				$resource   = new resource();
				$deployment = new deployment();

				$storage->get_instance_by_id($this->response->html->request()->get('storage_id'));
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);

				$errors     = array();
				$message    = array();
				foreach($vdi_names as $key => $vdi_name) {
					// check if an appliance is still using the volume as an image
					$image = new image();
					$image->get_instance_by_name($vdi_name);

					// check if it is still in use
					$appliance = new appliance();
					$appliances_using_resource = $appliance->get_ids_per_image($image->id);
					if (count($appliances_using_resource) > 0) {
						$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
						$errors[] = sprintf($this->lang['msg_vm_image_still_in_use'], $vdi_name, $image->id, $appliances_using_resource_str);
					} else {
						$command  = $this->openqrm->get('basedir').'/plugins/citrix-storage/bin/openqrm-citrix-storage remove';
						$command .= ' -i '.$this->resource->ip;
						$command .= ' -n '.$vdi_name;
						$command .= ' -t '.$deployment->type;
						$command .= ' -u '.$name.' -p '.$pass;

						$file = $this->openqrm->get('basedir').'/plugins/citrix-storage/web/citrix-storage-stat/'.$this->resource->ip.'.vdi_list';
						if($this->file->exists($file)) {
							$this->file->remove($file);
						}
						$openqrm = new resource();
						$openqrm->get_instance_by_id(0);
						$openqrm->send_command($openqrm->ip, $command);
						while (!$this->file->exists($file)) {
							usleep(10000); // sleep 10ms to unload the CPU
							clearstatcache();
						}

						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['msg_removed'], $vdi_name);
						// remove the image of the volume
						$image->remove_by_name($vdi_name);
					}

				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
		} else {
			$response->msg = '';
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
		$form = $response->get_form($this->actions_name, 'remove');
		$response->form = $form;
		return $response;
	}

}
?>
