<?php
/**
 * KVM-Storage Add new Volume
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_vdi_add
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
	function __construct($openqrm, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user       = $openqrm->user();
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
		$response = $this->add();
		if(isset($response->msg)) {
			// wizard
			if(isset($this->user->wizard_name) && $this->user->wizard_name === 'appliance' && $this->user->wizard_step == 3) {
				$this->controller->reload();
				$this->response->redirect(
					$this->response->html->thisfile.'?base=appliance&appliance_action=step'.$this->user->wizard_step.'&appliance_id='.$this->user->wizard_id.'&image_id='.$response->image_id
				);
			} else {
				$this->response->params['reload'] = 'false';
				$this->response->redirect(
					$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
				);
			}
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/citrix-storage-vdi-add.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Add
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			if(!$form->get_errors()) {
				$name     = $form->get_request('name');
				$size     = $form->get_request('size');
				$sr       = $form->get_request('sr');
				$description       = $form->get_request('description');
				$description_parameter = '';
				if (strlen($description)) {
					$description = str_replace(' ', '@', $description);
					$description_parameter = ' -d \"'.$description.'\"';
				}				
				$command  = $this->openqrm->get('basedir').'/plugins/citrix-storage/bin/openqrm-citrix-storage add';
				$command .= ' -i '.$this->resource->ip.' -x '.$sr;
				$command .= ' -n '.$name.' -m '.$size;
				$command .= $description_parameter;
				$command .= ' -t '.$this->deployment->type;
				$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;

				$statfile = $this->openqrm->get('basedir').'/plugins/citrix-storage/web/citrix-storage-stat/'.$this->resource->ip.'.vdi_list';
				if ($this->file->exists($statfile)) {
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$citrix_line = trim($line);
								$citrix_parameter_arr = explode(":", $citrix_line);
								$citrix_volume_uuid = $citrix_parameter_arr[0];
								$citrix_volume_name = ltrim($citrix_parameter_arr[1], "@");
								$check = str_replace("@", " ", $citrix_volume_name);
								if($name === $check) {
									$error = sprintf($this->lang['error_exists'], $name);
								}
							}
						}
					}
				}
				// check for image name
				$image = new image();
				$image->get_instance_by_name($name);
				if ((isset($image->id)) && ($image->id > 1)) {
				    $error = sprintf($this->lang['error_exists'], $name);
				}

				if(isset($error)) {
					$response->error = $error;
				} else {
					if($this->file->exists($statfile)) {
						$this->file->remove($statfile);
					}
					$openqrm = new resource();
					$openqrm->get_instance_by_id(0);
					$openqrm->send_command($openqrm->ip, $command);
					while (!$this->file->exists($statfile)) {
						usleep(10000); // sleep 10ms to unload the CPU
						clearstatcache();
					}
					// add check that volume $name is now in the statfile
					$created = false;
					$lines = explode("\n", $this->file->get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$citrix_line = trim($line);
								$citrix_parameter_arr = explode(":", $citrix_line);
								$citrix_volume_uuid = $citrix_parameter_arr[0];
								$citrix_volume_name = ltrim($citrix_parameter_arr[1], "@");
								$check = str_replace("@", " ", $citrix_volume_name);
								if($name === $check) {
									$created = true;
									break;
								}
							}
						}
					}

					if ($created) {
						$tables = $this->openqrm->get('table');
					    $image_fields = array();
					    $image_fields["image_id"] = openqrm_db_get_free_id('image_id', $tables['image']);
					    $image_fields['image_name'] = $name;
					    $image_fields['image_type'] = $this->deployment->type;
					    $image_fields['image_rootfstype'] = 'local';
					    $image_fields['image_storageid'] = $this->storage->id;
					    $image_fields['image_comment'] = "Image Object for volume $name";
					    $image_fields['image_rootdevice'] = $citrix_volume_uuid;
					    $image_fields['image_capabilities'] = 'SIZE='.$size;
					    $image = new image();
					    $image->add($image_fields);
					    $response->msg = sprintf($this->lang['msg_added'], $name);
						// save image id in response for the wizard
						$response->image_id = $image_fields["image_id"];

					} else {
					    $response->msg = sprintf($this->lang['msg_add_failed'], $name);
					}
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
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'add');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$sr_select_options_arr = array();
		$sr_statfile = $this->openqrm->get('basedir').'/plugins/citrix-storage/web/citrix-storage-stat/'.$this->resource->ip.'.ds_select';
		if ($this->file->exists($sr_statfile)) {
			$lines = explode("\n", $this->file->get_contents($sr_statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$citrix_line = trim($line);
						$citrix_parameter_arr = explode(":", $citrix_line);
						$citrix_volume_uuid = $citrix_parameter_arr[0];
						$citrix_volume_name = ltrim($citrix_parameter_arr[1], "@");
						$citrix_volume_name = str_replace("@", " ", $citrix_volume_name);
						$sr_select_options_arr[] = array('label' => $citrix_volume_name, 'value' => $citrix_volume_uuid);


					}
				}
			}
		}

		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = '';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$d['description']['label']                         = $this->lang['form_description'];
		$d['description']['required']                      = false;
		$d['description']['validate']['regex']             = '/^[a-z0-9. -_]+$/i';
		$d['description']['validate']['errormsg']          = sprintf($this->lang['error_description'], 'a-z0-9. -_');
		$d['description']['object']['type']                = 'htmlobject_input';
		$d['description']['object']['attrib']['id']        = 'description';
		$d['description']['object']['attrib']['name']      = 'description';
		$d['description']['object']['attrib']['type']      = 'text';
		$d['description']['object']['attrib']['value']     = '';
		$d['description']['object']['attrib']['maxlength'] = 150;

		$d['size']['label']                         = $this->lang['form_size'];
		$d['size']['required']                      = true;
		$d['size']['validate']['regex']             = '/^[0-9]+$/i';
		$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
		$d['size']['object']['type']                = 'htmlobject_input';
		$d['size']['object']['attrib']['name']      = 'size';
		$d['size']['object']['attrib']['type']      = 'text';
		$d['size']['object']['attrib']['value']     = '';
		$d['size']['object']['attrib']['maxlength'] = 50;

		$d['sr']['label']                         = $this->lang['form_sr'];
		$d['sr']['required']                      = true;
		$d['sr']['object']['type']                = 'htmlobject_select';
		$d['sr']['object']['attrib']['name']      = 'sr';
		$d['sr']['object']['attrib']['type']      = 'text';
		$d['sr']['object']['attrib']['options']	  = $sr_select_options_arr;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
?>
