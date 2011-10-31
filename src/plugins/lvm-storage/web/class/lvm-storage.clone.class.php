<?php
/**
 * LVM-Storage clone Volume
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
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class lvm_storage_clone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm-storage-action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-lvm-storage";
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
var $prefix_tab = 'lvm_tab';
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
	 * @param db $file
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->volgroup = $this->response->html->request()->get('volgroup');
		$this->lvol = $this->response->html->request()->get('lvol');
		$this->response->params['lvol'] = $this->lvol;

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
		$response = $this->duplicate();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/lvm-storage-clone.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add(sprintf($this->lang['label'], $this->lvol), 'label');
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
			global $OPENQRM_SERVER_BASE_DIR;
			$name        = $form->get_request('name');
			$command     = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/lvm-storage/bin/openqrm-lvm-storage clone';
			$command    .= ' -t '.$this->deployment->type;
			$command    .= ' -v '.$this->volgroup;
			$command    .= ' -n '.$this->lvol;
			$command    .= ' -m '.$this->set_max();
			$command    .= ' -s '.$name;
			$command    .= ' -u '.$GLOBALS['OPENQRM_ADMIN']->name.' -p '.$GLOBALS['OPENQRM_ADMIN']->password;
			if($this->deployment->type === 'lvm-iscsi-deployment') {
				$image    = new image();
				$command .= ' -i '.$image->generatePassword(12);
			}

			$statfile = 'storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
			if (file_exists($statfile)) {
				$lines = explode("\n", file_get_contents($statfile));
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
			if(isset($error)) {
				$response->error = $error;
			} else {
				#if(file_exists($statfile)) {
				#	unlink($statfile);
				#}
				$this->resource->send_command($this->resource->ip, $command);
				#while (!file_exists($statfile)) {
				#	usleep(10000); // sleep 10ms to unload the CPU
				#	clearstatcache();
				#}
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

		#$d['size']['label']                         = $this->lang['form_size'];
		#$d['size']['required']                      = true;
		#$d['size']['validate']['regex']             = '/^[0-9]+$/i';
		#$d['size']['validate']['errormsg']          = sprintf($this->lang['error_size'], '0-9');
		#$d['size']['object']['type']                = 'htmlobject_input';
		#$d['size']['object']['attrib']['name']      = 'size';
		#$d['size']['object']['attrib']['type']      = 'text';
		#$d['size']['object']['attrib']['value']     = '';
		#$d['size']['object']['attrib']['maxlength'] = 50;

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
		$lvmax = '';
		$statfile = 'storage/'.$this->resource->id.'.'.$this->volgroup.'.lv.stat';
		if (file_exists($statfile)) {
			$lines = explode("\n", file_get_contents($statfile));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						if($line[1] === $this->lvol) {
							$lvmax = str_replace('.', '', $line[4]);
							$lvmax = str_replace('m', '', $lvmax);
							$lvmax = (int)$lvmax / 100;
						}
					}
				}
			}
		}
		return $lvmax;
	}

}
?>
