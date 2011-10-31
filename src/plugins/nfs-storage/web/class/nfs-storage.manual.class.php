<?php
/**
 * NFS-Storage Manual Configuration
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

class nfs_storage_manual
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
		$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
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
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'manual', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/nfs-storage-manual.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang);
		$t->add($this->prefix_tab, 'prefix_tab');
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
			global $OPENQRM_SERVER_BASE_DIR;
			$config     = $form->get_request('config');
			$storage_id = $this->response->html->request()->get('storage_id');
			$storage    = new storage();
			$resource   = new resource();
			$storage->get_instance_by_id($storage_id);
			$resource->get_instance_by_id($storage->resource_id);
			$file = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";
			if(isset($config)) {
				#$openqrm = new openqrm_server();
				#unlink($file);
				#$openqrm->send_command("touch $file && chmod 777 $file");
				#while (!file_exists($file)) {
			  	#	usleep(10000); // sleep 10ms to unload the CPU
			  	#	clearstatcache();
				#}
				if (!$handle = fopen($file, 'w+')) {
					$error = "Cannot open file ($file)";
				}
				if (fwrite($handle, $config) === FALSE) {
					$error = "Cannot write to file ($file)";
				}		
				if(isset($error)) {
					$response->error = $error;
				} else {
					$response->msg = $this->lang['saved'];
				}
			} else {
				if(file_exists($file)) {
					unlink($file);
				}
				$response->msg = '';
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
		global $OPENQRM_SERVER_BASE_DIR;

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'manual');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$storage_id  = $this->response->html->request()->get('storage_id');
		$storage     = new storage();
		$resource    = new resource();
		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$file = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";
	
		$d['config']['object']['type']            = 'htmlobject_textarea';
		$d['config']['object']['attrib']['name']  = 'config';
		if(file_exists($file)) {
			$d['config']['object']['attrib']['value'] = file_get_contents($file);
		}

		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
