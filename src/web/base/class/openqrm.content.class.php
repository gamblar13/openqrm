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

class openqrm_content
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

var $lang = array(
		'event' => array (
		'ltimeout' => '',
		'lclients' => ''
	)
);


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user, $openqrm) {
		$this->response = $response;
		$this->openqrm  = $openqrm;
		$this->file     = $file;
		$this->user     = $user;
		$this->request  = $this->response->html->request();
		$this->lang     = $this->user->translate($this->lang, $this->openqrm->get('basedir')."/web/base/server/event/lang", 'event-content.ini');

//		$response->html->debug();
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
		$t = $this->response->html->template($this->tpldir.'/index_content.tpl.php');
		$t->add($this->content(), 'content');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Build content
	 *
	 * @access public
	 * @return htmlobject_tabmenu | string
	 */
	//--------------------------------------------
	function content() {
		if($this->request->get('iframe') !== '') {
			$str = '<iframe name="MainFrame" id="MainFrame" src="'.$this->request->get('iframe').'" scrolling="auto" height="1" style="width:100%;display:block;"></iframe>';
			return $str;
		} 
		else if ($this->request->get('base') !== '') {
			$plugin = $this->request->get('base');
			$name   = $plugin;
			$class  = $plugin;
			if($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name  = $class;
			}
			$class  = str_replace('-', '_', $class).'_controller';
			$path   = $this->rootdir.'/server/'.$plugin.'/class/'.$name.'.controller.class.php';
			if($this->file->exists($path)) {
				$this->openqrm->init();
				require_once($path);
				$controller = new $class($this->openqrm, $this->response);
				$data = $controller->action();
				return $data;
			}
		}
		else if($this->request->get('plugin') !== '') {
			$plugin = $this->request->get('plugin');
			$name   = $plugin;
			$class  = $plugin;
			if($this->request->get('controller') !== '') {
				$class = $this->request->get('controller');
				$name  = $class;
			}
			$class  = str_replace('-', '_', $class).'_controller';
			$path   = $this->rootdir.'/plugins/'.$plugin.'/class/'.$name.'.controller.class.php';
			if($this->file->exists($path)) {
				$this->openqrm->init();
				require_once($path);
				$controller = new $class($this->openqrm, $this->response);
				$data = $controller->action();
				return $data;
			} else {
				// handle plugins not oop
				$path = $this->rootdir.'/plugins/'.$plugin.'/'.$name.'-manager.php';
				if($this->file->exists($path)) {
					$params = '';
					foreach($_REQUEST as $k => $v) {
						if(is_string($v)) {
							$params .= '&'.$k.'='.$v;		
						}
						if(is_array($v)) {
							foreach($v as $key => $value) {
								$params .= '&'.$k.'['.$key.']'.'='.$value;
							}
						}
					}
					$str = '<iframe name="MainFrame" id="MainFrame" src="plugins/'.$plugin.'/'.$name.'-manager.php?'.$params.'" scrolling="auto" height="1" style="width:100%;display:block;"></iframe>';
					return $str;
				} else {
					return 'plugin '.$name.' not found';
				}
			}
		} else {
			// default page - datacenter overview
			$path   = $this->rootdir.'/server/aa_server/class/datacenter.controller.class.php';
			$this->openqrm->init();
			require_once($path);
			$controller = new datacenter_controller($this->openqrm, $this->response);
			$data = $controller->action();
			return $data;
		}
	}


}
?>
