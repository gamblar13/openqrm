<?php
/**
 * lxc-Storage deploy Templates
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class lxc_storage_deploy
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lxc_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lxc_storage_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lxc_identifier';
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
var $prefix_tab = 'lxc_tab';
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
		$this->volgroup = $this->response->html->request()->get('volgroup');
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
		$response = $this->deploy();
		$this->response->redirect(
			$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
		);
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function deploy() {
		$response = '';
		$template = $this->response->html->request()->get('template');
		$lvol = $this->response->html->request()->get('lvol');
		$volgroup = $this->response->html->request()->get('volgroup');
		$storage_id = $this->response->html->request()->get('storage_id');
		if (( $template !== '' ) && ( $lvol !== '' ) && ( $volgroup !== '' ) && ( $storage_id !== '' )) {
			$storage          = new storage();
			$storage->get_instance_by_id($storage_id);
			$resource         = new resource();
			$resource->get_instance_by_id($storage->resource_id);
			$command  = $this->openqrm->get('basedir').'/plugins/lxc-storage/bin/openqrm-lxc-storage deploy_lxc_template';
			$command .= ' -n '.$lvol;
			$command .= ' -v '.$volgroup;
			$command .= ' -t '.$template;
			$resource->send_command($resource->ip, $command);
			$response->msg = sprintf($this->lang['msg_deployed'], $template, $lvol);
		} else {
			$response = '';
		}
		return $response;
	}


}
?>
