<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class cloud_register_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_register';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-cloud-register";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'project_tab_register';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct() {
		$this->identifier_name	= "cloud";
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		$this->rootdir			= $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
		$this->userdir			= $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/user';
		$this->tpldir           = $this->userdir."/tpl";
		require_once $this->rootdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->rootdir."/class/htmlobjects/");
		$this->response = $this->html->response('cloud-register');
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
			$this->action = "login";
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'home':
				$content[] = $this->home(true);
				$content[] = $this->create(false);
				$content[] = $this->activate(false);
				$content[] = $this->recover(false);
			break;
			case 'login':
				$content[] = $this->login(true);
			break;
			case 'create':
				$content[] = $this->home(false);
				$content[] = $this->create(true);
				$content[] = $this->activate(false);
				$content[] = $this->recover(false);
			break;
			case 'activate':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->activate(true);
				$content[] = $this->recover(false);
			break;
			case 'recover':
				$content[] = $this->home(false);
				$content[] = $this->create(false);
				$content[] = $this->activate(false);
				$content[] = $this->recover(true);
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
	 * home
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function home( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-register.home.class.php');
			$controller = new cloud_register_home($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = "Login to the Cloud";
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'login' );
		$content['onclick'] = false;
		if($this->action === 'home'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * login
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function login( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-register.login.class.php');
			$controller = new cloud_register_login($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = "Login to Cloud";
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'login' );
		$content['onclick'] = false;
		if($this->action === 'login'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * create
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function create( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-register.create.class.php');
			$controller = new cloud_register_create($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = "Register to Cloud";
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'create' );
		$content['onclick'] = false;
		if($this->action === 'create'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * activate
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function activate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-register.activate.class.php');
			$controller = new cloud_register_activate($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = "Activate your Account";
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'activate' );
		$content['onclick'] = false;
		if($this->action === 'activate'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * recover
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function recover( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->userdir.'/class/cloud-register.recover.class.php');
			$controller = new cloud_register_recover($this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$data = $controller->action();
		}
		$content['label']   = "Recover password";
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'recover' );
		$content['onclick'] = false;
		if($this->action === 'recover'){
			$content['active']  = true;
		}
		return $content;
	}


}
?>
