<?php
/**
 * local-storage clone Volume
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class local_storage_template_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "local_storage_msg";
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
var $prefix_tab = 'local_storage_tab';
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
		$this->response                 = $response;
		$this->file                     = $openqrm->file();
		$this->openqrm                  = $openqrm;
		$this->user						= $openqrm->user();
		$this->volgroup                 = $this->response->html->request()->get('volgroup');
		$this->lvol                     = $this->response->html->request()->get('lvol');
		$this->response->params['lvol'] = $this->lvol;
		$storage_id                     = $this->response->html->request()->get('storage_id');
		$storage                        = new storage();
		$resource                       = new resource();
		$deployment                     = new deployment();
		$this->storage                  = $storage->get_instance_by_id($storage_id);
		$this->resource                 = $resource->get_instance_by_id($storage->resource_id);
		$this->deployment               = $deployment->get_instance_by_id($storage->type);
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
		$response = $this->duplicate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/local-storage-template-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * clone
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function duplicate() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {
			$name     = $form->get_request('name');
			$command  = $this->openqrm->get('basedir').'/plugins/local-storage/bin/openqrm-local-storage clone';
			$command .= ' -t '.$this->deployment->type;
			$command .= ' -v '.$this->volgroup;
			$command .= ' -n '.$this->lvol;
			$command .= ' -s '.$name;
			$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;

			$statfile = $this->openqrm->get('basedir').'/plugins/local-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
			if ($this->file->exists($statfile)) {
				$lines = explode("\n", $this->file->get_contents($statfile));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$check = $line[1];
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
				$file = $this->openqrm->get('basedir').'/plugins/local-storage/web/storage/'.$this->resource->id.'.lvm.'.$name.'.sync_progress';
				if($this->file->exists($file)) {
					$this->file->remove($file);
				}
				$root_device_ident = $this->openqrm->get('basedir').'/plugins/local-storage/web/storage/'.$this->resource->id.'.'.$name.'.root_device';
				if($this->file->exists($root_device_ident)) {
					$this->file->remove($root_device_ident);
				}
				$this->resource->send_command($this->resource->ip, $command);
				while (!$this->file->exists($file))
				{
				  usleep(10000);
				  clearstatcache();
				}
				// wait for the root-device identifier
				while (!$this->file->exists($root_device_ident))
				{
				  usleep(10000);
				  clearstatcache();
				}
				$root_device = $this->file->get_contents($root_device_ident);
				$this->file->remove($root_device_ident);
				$response->msg = sprintf($this->lang['msg_cloned'], $this->lvol, $name);
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
		$form = $response->get_form($this->actions_name, 'clone');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');
	
		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9._]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['value']     = $this->lvol.'_clone';
		$d['name']['object']['attrib']['maxlength'] = 50;

		$form->add($d);
		$response->form = $form;
		return $response;
	}


	//--------------------------------------------
	/**
	 * Set max
	 *
	 * @access protected
	 * @return bool
	 */
	//--------------------------------------------
	function set_max() {
		$vgmax = '';
		$kvmax = '';
		$statfile = $this->openqrm->get('basedir').'/plugins/local-storage/web/storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
		if ($this->file->exists($statfile)) {
			$lines = explode("\n", $this->file->get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $this->lvol) {
							$kvmax = str_replace('.', '', $line[4]);
							$kvmax = str_replace('m', '', $kvmax);
							$kvmax = (int)$kvmax / 100;
						}
					}
				}
			}
		}
		return $kvmax;
	}

}
?>
