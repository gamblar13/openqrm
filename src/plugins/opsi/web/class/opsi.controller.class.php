<?php
/*
	This file is part of openQRM.

	openQRM is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2
	as published by the Free Software Foundation.

	openQRM is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

	Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class opsi_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'opsi';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-opsi";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'project_tab';
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
var $lang = array(
	'opsi_configuration' => 'OPSI Manager',
	'opsi_title' => 'OPSI Manager',
	'opsi_add_storages' => 'Please add an Opsi Server as Storage first!',
	'opsi_id' => 'ID',
	'opsi_mac' => 'MAC Adress',
	'opsi_ip' => 'IP Adress',
	'opsi_user' => 'User',
	'opsi_password' => 'Password',
	'opsi_comment' => 'Comment',
	'opsi_actions' => 'Actions',
);

var $openqrm_base_dir;
var $openqrm;
var $openqrm_ip;
var $event;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($user) {
/*		$this->file             = $file;
		$this->ini              = $this->file->files()->get_ini(DATADIR.'/project.ini');
		$this->response         = $response->html->response();
		$this->response->params = $response->params;
		$this->tpldir           = CLASSDIR.'/plugins/cms/templates';
 *
 */

		$this->user             = $user;

		$this->identifier_name = "opsi_ident";
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		require_once $this->rootdir."/plugins/opsi/class/opsi.class.php";
		$this->db = new opsi();


		require_once "$RootDir/class/event.class.php";
		require_once "$RootDir/class/resource.class.php";
		require_once "$RootDir/class/openqrm_server.class.php";
		require_once "$RootDir/include/openqrm-server-config.php";
		global $OPENQRM_SERVER_BASE_DIR;
		$this->openqrm_base_dir = $OPENQRM_SERVER_BASE_DIR;
		$openqrm_server = new openqrm_server();
		$this->openqrm = $openqrm_server;
		$this->openqrm_ip = $openqrm_server->get_ip_address();
		$event = new event();
		$this->event = $event;

		require_once $this->rootdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->rootdir."/class/htmlobjects/");
		$this->html->lang = $this->user->translate($this->html->lang, $this->rootdir."/plugins/opsi/lang", 'htmlobjects.ini');
		$this->response = $this->html->response('opsi-construct');

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		}
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
		}
		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * select
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/opsi/class/opsi.select.class.php');
			$controller = new opsi_select($this->response, $this->db);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
//			$controller->message_param = $this->message_param;
//			$controller->settings      = DATADIR.'/project.ini';
			$controller->lang          = $this->user->translate($this->lang, $this->rootdir."/plugins/opsi/lang", 'opsi.ini');
			$data = $controller->action();
		}
		$this->lang			= $this->user->translate($this->lang, $this->rootdir."/plugins/opsi/lang", 'opsi.ini');
		$content['label']   = $this->lang['opsi_configuration'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}




}
?>
