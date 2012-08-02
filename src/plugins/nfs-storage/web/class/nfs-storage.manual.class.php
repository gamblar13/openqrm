<?php
/**
 * NFS-Storage Manual Configuration
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
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
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
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
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
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
			$config     = $form->get_request('config');
			$storage_id = $this->response->html->request()->get('storage_id');
			$storage    = new storage();
			$resource   = new resource();
			$storage->get_instance_by_id($storage_id);
			$resource->get_instance_by_id($storage->resource_id);
			$file = $this->openqrm->get('basedir')."/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";
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
				if($this->file->exists($file)) {
					$this->file->remove($file);
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
		$file = $this->openqrm->get('basedir')."/plugins/nfs-storage/web/storage/".$resource->id.".nfs.stat.manual";
	
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
