<?php
/**
 * Openqrm Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openqrm_controller
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
		$this->tpldir = $this->rootdir.'/tpl';
		require_once($this->rootdir.'/class/openqrm.class.php');
		$this->openqrm = new openqrm();
		require_once($this->openqrm->get('classdir').'/htmlobjects/htmlobject.class.php');
		$html = new htmlobject($this->openqrm->get('classdir')."/htmlobjects/");

		$request = $html->request();
		$request->filter = 	array (
			array ( 'pattern' => '~</?script.+~i', 'replace' => ''),
			array ( 'pattern' => '~</?iframe.+~i', 'replace' => ''),
			array ( 'pattern' => '~</?object.+~i', 'replace' => ''),
			array ( 'pattern' => '~on.+=~i', 'replace' => ''),
			array ( 'pattern' => '~javascript~i', 'replace' => ''),
		);
		// other useful filter
		//	array ( 'pattern' => '~://~', 'replace' => ':&frasl;&frasl;'),

		$html->lang = $this->openqrm->user()->translate($html->lang, $this->rootdir."/lang", 'htmlobjects.ini');
		$this->response = $html->response();
		$this->request  = $this->response->html->request();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {

		$ti = microtime(true);
		$style = '';
		$script = '';
		$basetarget = '<base target="MainFrame"></base>';
		if($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$path   = '/plugins/'.$plugin.'/css/'.$plugin.'.css';
			if($this->openqrm->file()->exists($this->rootdir.$path)) {
				$style = '<link type="text/css" href="'.$this->openqrm->get('baseurl').$path.'" rel="stylesheet">';
			}
			$path   = '/server/'.$plugin.'/js/'.$plugin.'.js';
			if($this->openqrm->file()->exists($this->rootdir.$path)) {
				$script = '<script src="'.$this->openqrm->get('baseurl').$path.'" type="text/javascript"></script>';
			}
			$basetarget = '';
			$this->response->params['plugin'] = $plugin;
			if($this->request->get('controller') !== '') {
				$this->response->params['controller'] = $this->request->get('controller');
			}
		}
		else if ( $this->request->get('base') !== '') {
			$plugin = $this->request->get('base');
			$path   = '/server/'.$plugin.'/css/'.$plugin.'.css';
			if($this->openqrm->file()->exists($this->rootdir.$path)) {
				$style = '<link rel="stylesheet" href="'.$this->openqrm->get('baseurl').$path.'" type="text/css">';
			}
			$path   = '/server/'.$plugin.'/js/'.$plugin.'.js';
			if($this->openqrm->file()->exists($this->rootdir.$path)) {
				$script = '<script src="'.$this->openqrm->get('baseurl').$path.'" type="text/javascript"></script>';
			}
			$basetarget = '';
			$this->response->params['base'] = $plugin;
			if($this->request->get('controller') !== '') {
				$this->response->params['controller'] = $this->request->get('controller');
			}
		}
		$t = $this->response->html->template($this->tpldir.'/index.tpl.php');
		$t->add($this->openqrm->get('baseurl'), "baseurl");
		$t->add($this->openqrm->user()->lang, "lang");
		$t->add($basetarget, "basetarget");
		$t->add($style, "style");
		$t->add($script, "script");
		$t->add($this->top(), "top");
		$t->add($this->menu(), "menu");
		$t->add($this->content(), "content");

		$memory = '';
		if(function_exists('memory_get_peak_usage')) {
			$memory = memory_get_peak_usage(false);
		}
		$t->add('Memory: '.$memory.' bytes', 'memory');
		$ti = (microtime(true) - $ti);
		$t->add('Time: '.$ti.' sec', 'time');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Api
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/class/openqrm.api.class.php');
		$controller = new openqrm_api($this);
		$controller->action();
	}

	//--------------------------------------------
	/**
	 * Build Top of page
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function top() {
		require_once($this->rootdir.'/class/openqrm.top.class.php');
		$controller = new openqrm_top($this->response, $this->openqrm->file(), $this->openqrm->user());
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Build menu
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function menu() {
		require_once($this->rootdir.'/class/openqrm.menu.class.php');
		$controller = new openqrm_menu($this->response, $this->openqrm->file(), $this->openqrm->user());
		$controller->tpldir = $this->tpldir;
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Handle content
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function content() {
		require_once($this->rootdir.'/class/openqrm.content.class.php');
		$controller = new openqrm_content($this->response, $this->openqrm->file(), $this->openqrm->user(), $this->openqrm);
		$controller->tpldir = $this->tpldir;
		$controller->rootdir = $this->rootdir;
		$data = $controller->action();
		return $data;
	}

}
