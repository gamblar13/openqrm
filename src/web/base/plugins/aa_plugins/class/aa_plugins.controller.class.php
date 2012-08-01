<?php
/**
 * aa_plugins Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class aa_plugins_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aa_plugins_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
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
	'select' => array (
		'tab' => 'Plugin Manager',
		'label' => 'Plugin Manager',
		'action_start' => 'start',
		'action_stop' => 'stop',
		'action_enable' => 'enable',
		'action_disable' => 'disable',
		'title_start' => 'click to start %s',
		'title_stop' => 'click to stop %s',
		'title_enable' => 'click to enable %s',
		'title_disable' => 'click to disable %s',
		'table_name' => 'Plugin',
		'table_type' => 'Type',
		'table_description' => 'Description',
		'table_enabled' => 'enabled',
		'table_started' => 'started',
		'lang_filter' => 'Filter by Type',
		'please_wait' => 'Loading. Please wait ..',
	), 
	'start' => array (
		'label' => 'Start Plugins(s)',
		'msg' => 'Started Plugin %s',
		'error_timeout' => 'Timeout while trying to start plugin %s',
		'error_start' => 'Could not start plugin %s',
	),
	'stop' => array (
		'label' => 'Stop Plugins(s)',
		'msg' => 'Stopped Plugin %s',
		'error_timeout' => 'Timeout while trying to stop plugin %s',
		'error_stop' => 'Could not stop plugin %s',
	),
	'enable' => array (
		'label' => 'Enable Plugins(s)',
		'msg' => 'Enabled Plugin %s',
		'error_timeout' => 'Timeout while trying to enable plugin %s',
		'error_enable' => 'Could not enable plugin %s',
		'error_enabled' => 'Plugin %s allready enabled',
		'error_dependencies' => 'Dependencies for %s failed. Please enable %s first.',
	),
	'disable' => array (
		'label' => 'Disable Plugins(s)',
		'msg' => 'Disabled Plugin %s',
		'error_timeout' => 'Timeout while trying to disable plugin %s',
		'error_in_use' => 'Plugin %s is still in use by openQRM',
		'error_disable' => 'Could not disable plugin %s',
		'error_dependencies' => 'Dependencies for %s failed. Please disable %s first.',
	),
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param openqrm $openqrm
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->tpldir   = $this->rootdir.'plugins/aa_plugins/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/aa_plugins/lang", 'aa_plugins.ini');
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

		// handle response
		$this->response->params['plugin_filter'] = $this->response->html->request()->get('plugin_filter');
		$vars = $this->response->html->request()->get('plugins');
		if($vars !== '') {
			if(!isset($vars['action'])) {
				foreach($vars as $k => $v) {
					$this->response->add('plugins['.$k.']', $v);
				}
			} else {
				foreach($vars as $k => $v) {
					unset($this->response->params['plugins['.$k.']']);
				}
			}
		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case $this->lang['select']['action_start']:
			case 'start':
				$content[] = $this->start(true);
			break;
			case $this->lang['select']['action_stop']:
			case 'stop':
				$content[] = $this->stop(true);
			break;
			case $this->lang['select']['action_enable']:
			case 'enable':
				$content[] = $this->enable(true);
			break;
			case $this->lang['select']['action_disable']:
			case 'disable':
				$content[] = $this->disable(true);
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
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.api.class.php');
		$controller = new aa_plugins_api($this);
		$controller->action();
	}

	
	//--------------------------------------------
	/**
	 * Select Plugins
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.select.class.php');
			$controller = new aa_plugins_select($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}	
	
	//--------------------------------------------
	/**
	 * Start Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.start.class.php');
			$controller                  = new aa_plugins_start($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['start'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Start';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'start' );
		$content['onclick'] = false;
		if($this->action === 'start' || $this->action === $this->lang['select']['action_start']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Stop Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.stop.class.php');
			$controller                  = new aa_plugins_stop($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['stop'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Stop';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'stop' );
		$content['onclick'] = false;
		if($this->action === 'stop' || $this->action === $this->lang['select']['action_stop']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Enable Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function enable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.enable.class.php');
			$controller                  = new aa_plugins_enable($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['enable'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Enable';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'enable' );
		$content['onclick'] = false;
		if($this->action === 'enable' || $this->action === $this->lang['select']['action_enable']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Disable Plugin
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/aa_plugins/class/aa_plugins.disable.class.php');
			$controller                  = new aa_plugins_disable($this->response, $this->file);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['disable'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'disable';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'disable' );
		$content['onclick'] = false;
		if($this->action === 'disable' || $this->action === $this->lang['select']['action_disable']){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
