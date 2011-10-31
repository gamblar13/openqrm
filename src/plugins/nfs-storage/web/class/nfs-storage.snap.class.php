<?php
/**
 * NFS-Storage Snap Volume(s)
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

class nfs_storage_snap
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nfs_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nfs_storage_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nfs_identifier';
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
var $prefix_tab = 'nfs_tab';
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
	function __construct($response) {
		$this->response = $response;
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
		$response = $this->snap();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'edit', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/nfs-storage-snap.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Snap
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function snap() {
		$response = $this->get_response();
		$exports  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $exports !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($exports as $ex) {
				$d['param_f'.$i]['static']                    = true;
				$d['param_f'.$i]['object']['type']            = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']  = 'hidden';
				$d['param_f'.$i]['object']['attrib']['name']  = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value'] = $ex;

				$d['param_n'.$i]['label']                         = $ex;
				$d['param_n'.$i]['required']                      = true;
				$d['param_n'.$i]['validate']['regex']             = '/^[a-z0-9._-]+$/i';
				$d['param_n'.$i]['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9._-');
				$d['param_n'.$i]['object']['type']                = 'htmlobject_input';
				$d['param_n'.$i]['object']['attrib']['type']      = 'text';
				$d['param_n'.$i]['object']['attrib']['name']      = 'snap['.$i.']';
				$d['param_n'.$i]['object']['attrib']['value']     = $ex.'-snap';
				$d['param_n'.$i]['object']['attrib']['maxlength'] = 50;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				// set ENV
				$storage_id = $this->response->html->request()->get('storage_id');
				$storage    = new storage();
				$resource   = new resource();
				$storage->get_instance_by_id($storage_id);
				$resource->get_instance_by_id($storage->resource_id);

				$check = array();
				$statfile = "storage/".$storage->resource_id.".nfs.stat";
				if (file_exists($statfile)) {
					$lines = explode("\n", file_get_contents($statfile));
					if(count($lines) >= 1) {
						foreach($lines as $line) {
							if($line !== '') {
								$line = explode('@', $line);
								$check[] = substr($line[0], strripos($line[0], '/')+1);
							}
						}
					}
				}
				global $OPENQRM_SERVER_BASE_DIR;
				$name    = $GLOBALS['OPENQRM_ADMIN']->name;
				$pass    = $GLOBALS['OPENQRM_ADMIN']->password;
				$errors  = array();
				$message = array();
				$snaps   = $form->get_request('snap');
				foreach($exports as $key => $export) {
					$error = '';
					if(isset($snaps[$key])) {
						if(in_array($snaps[$key], $check)) {
							$error = sprintf($this->lang['error_exists'], $snaps[$key]);
						}
						if($error === '') {
							$command  = $OPENQRM_SERVER_BASE_DIR.'/openqrm/plugins/nfs-storage/bin/openqrm-nfs-storage snap';
							$command .= ' -n '.$export.' -s '.$snaps[$key];
							$command .= ' -u '.$name.' -p '.$pass;

							if(file_exists($statfile)) {
								unlink($statfile);
							}
							$resource->send_command($resource->ip, $command);
							while (!file_exists($statfile)) {
				  				usleep(10000); // sleep 10ms to unload the CPU
				  				clearstatcache();
							}

							$form->remove($this->identifier_name.'['.$key.']');
							$form->remove('snap['.$key.']');
							$message[] = sprintf($this->lang['msg_snaped'], $export, $snaps[$key]);
						} else {
							$errors[] = $error;
						}
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
		$form = $response->get_form($this->actions_name, 'snap');
		$response->form = $form;
		return $response;
	}

}
?>
