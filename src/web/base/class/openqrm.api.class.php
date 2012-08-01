<?php
/**
 * Openqrm Content
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openqrm_api
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* absolute path to webroot
* @access public
* @var string
*/
var $rootdir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm_controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->response   = $this->controller->response;
		$this->openqrm    = $this->controller->openqrm;
		$this->file       = $this->controller->openqrm->file();
		$this->user       = $this->controller->openqrm->user();

		$this->openqrm->init();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get('action');
		switch( $action ) {
			case 'get_top_status':
				$this->get_top_status();
			break;
			case 'get_info_box':
				$this->get_info_box();
			break;
			case 'set_language':
				$this->set_language();
			break;
			case 'plugin':
				$this->plugin();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get values for top status
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_top_status() {
		$appliance = new appliance();
		$appliance_all = $appliance->get_count();
		$appliance_active = $appliance->get_count_active();

		$resource = new resource();
		$resource_all = $resource->get_count("all");
		$resource_active = $resource->get_count("online");
		$resource_inactive = $resource->get_count("offline");
		$resource_error = $resource->get_count("error");

		$event = new event();
		$event_error_count = $event->get_count('error');
		$event_active_count = $event->get_count('active');
		echo $appliance_all."@".$appliance_active."@".$resource_all."@".$resource_active."@".$resource_inactive."@".$resource_error."@".$event_error_count."@".$event_active_count;
	}

	//--------------------------------------------
	/**
	 * Set language
	 *
	 * @access public
	 */
	//--------------------------------------------
	function set_language() {
		$name = $this->response->html->request()->get('user');
		$lang = $this->response->html->request()->get('lang');
		$user = new user($name);
		$user->set_user_language($name, $lang);
	}


	//--------------------------------------------
	/**
	 * Get values for info box
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_info_box() {
		$now = $_SERVER['REQUEST_TIME'];
		$bd = $this->openqrm->get('baseurl');
		echo "<br>";
		echo "openQRM Enterprise developed by openQRM Enterprise GmbH.<br>
			All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.<br>
			This source code is released under the GNU General Public License version 2	unless otherwise agreed with openQRM Enterprise GmbH.
			The latest version of this license can be found at:<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;src/doc/LICENSE.txt<br>
			By using this software, you acknowledge having read this license and agree to be bound thereby.<br>";
		echo "<br>";
		echo "<hr>";
		echo '<div id="openqrm_enterprise_footer" style="float:right;text-align:left;"><small><a href="http://www.openqrm-enterprise.com/" style="text-decoration:none;" target="_BLANK">openQRM&nbsp;Enterprise&nbsp;-&nbsp;&copy;&nbsp;2012&nbsp;openQRM Enterprise GmbH&nbsp;&nbsp;</a></small></div>';
		echo "<br>";
	}

	//--------------------------------------------
	/**
	 * Load plugins
	 *
	 * @access public
	 */
	//--------------------------------------------
	function plugin() {
		$plugin = $this->response->html->request()->get('plugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('controller') !== '') {
			$class = $this->response->html->request()->get('controller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';
		$path   = $this->controller->rootdir.'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
		if($this->file->exists($path)) {
			require_once($path);
			$controller = new $class($this->openqrm, $this->response);
			$controller->api();
		}
	}


}
?>
