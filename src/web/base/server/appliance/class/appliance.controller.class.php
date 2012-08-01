<?php
/**
 * Appliance Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class appliance_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
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
		'tab' => 'Appliances',
		'label' => 'Appliances',
		'action_remove' => 'remove',
		'action_start' => 'start',
		'action_stop' => 'stop',
		'action_edit' => 'edit',
		'action_release' => 'release',
		'action_add' => 'Add a new appliance',
		'action_continue' => 'Continue setup',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_kernel' => 'Kernel',
		'table_image' => 'Image',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'appliance_create_in_progress' => 'Appliance create in progress by user %s',
		'resource_release' => 'release resource',
		'lang_filter' => 'Filter by appliance type',
		'please_wait' => 'Loading. Please wait ..',
	),
	'step1' => array (
		'label' => 'Add appliance (1/5)',
		'title' => 'Add a new appliance',
		'msg' => 'Added appliance %s',
		'form_name' => 'Name',
		'form_comment' => 'Comment',
		'lang_name_generate' => 'generate name',
		'error_name' => 'Name must be %s',
		'error_comment' => 'Comment must be %s',
		'appliance_create_in_progress_event' => 'Appliance %s create in progress (step %s) by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'error_exists' => 'Appliance %s is already in use.',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step2' => array (
		'label' => 'Add appliance (2/5)',
		'title' => 'Select a resource for appliance %s',
		'msg' => 'Added resource %s to appliance %s',
		'or' => 'or',
		'action_add' => 'new resource',
		'appliance_create_in_progress_event' => 'Appliance %s create in progress (step %s) by user %s',
		'form_resource' => 'Resource',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step3' => array (
		'label' => 'Add appliance  (3/5)',
		'title' => 'Select an image for appliance %s',
		'msg' => 'Added image %s to appliance %s',
		'or' => 'or',
		'action_add' => 'new image',
		'form_image' => 'Image',
		'appliance_create_in_progress_event' => 'Appliance %s create in progress (step %s) by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step4' => array (
		'label' => 'Add appliance  (4/5)',
		'title' => 'Edit Image for appliance %s',
		'msg' => 'Editing Image of appliance %s',
		'form_image' => 'Do you want to edit the Image details?',
		'notice_openqrm_image_not_editable' => 'The Image of the openQRM Server cannot be edited. Skipping edit!',
		'appliance_create_in_progress_event' => 'Appliance %s create in progress (step %s) by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step5' => array (
		'label' => 'Add appliance  (5/5)',
		'title' => 'Select a kernel for appliance %s',
		'msg' => 'Added kernel %s to appliance %s',
		'form_kernel' => 'Kernel',
		'appliance_create_in_progress_event' => 'Appliance %s create in progress (step %s) by user %s',
		'appliance_created' => 'Appliance %s created by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove appliance(s)',
		'msg' => 'Removed appliance %s',
		'msg_still_active' => 'Not removing appliance %s!<br>It is still active.',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'start' => array (
		'label' => 'Start appliance(s)',
		'msg' => 'Started appliance %s',
		'msg_no_resource' => 'Could not find any available resource for appliance %s',
		'msg_always_active' => 'An appliance with the openQRM Server as resource is always active!',
		'msg_already_active' => 'Not starting already aktive appliance %s',
		'msg_reource_not_idle' => 'Resource %s is not in idle state. Not starting appliance %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'stop' => array (
		'label' => 'Stop appliance(s)',
		'msg' => 'Stopped appliance %s',
		'msg_always_active' => 'An appliance with the openQRM Server as resource is always active!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'release' => array (
		'label' => 'Release appliance(s) resource',
		'msg' => 'Released appliance %s resource',
		'msg_openqrm' => 'The openQRM appliance resource cannot be released!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'edit' => array (
		'label' => 'Edit appliance',
		'title' => 'Edit appliance %s',
		'msg' => 'Updated appliance %s',
		'option_auto' => 'auto',
		'lang_ha' => 'Ha',
		'lang_misc' => 'Misc',
		'lang_mgmt' => 'Management',
		'lang_moni' => 'Monitoring',
		'lang_net' => 'Network',
		'lang_enter' => 'Enterprise',
		'lang_dep' => 'Deployment',
		'form_comment' => 'Comment',
		'form_cpus' => 'Cpus',
		'form_cpuspeed' => 'Cpuspeed',
		'form_cpumodel' => 'Cpumodel',
		'form_capabilities' => 'Capabilities',
		'form_virtualization' => 'Virtualization',
		'form_resource' => 'Resource',
		'form_image' => 'Image',
		'form_kernel' => 'Kernel',
		'form_nics' => 'Nics',
		'form_memory' => 'Memory',
		'form_swap' => 'Swap',
		'action_resource' => 'change resource %s',
		'action_image' => 'change image %s',
		'action_kernel' => 'change kernel %s',
		'no_plugin_available' => 'No action available',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	)
);

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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->tpldir   = $this->rootdir.'/server/appliance/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/appliance/lang", 'appliance.ini');
//		$response->html->debug();

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
			case 'step1':
				$content[] = $this->select(false);
				$content[] = $this->step1(true);
			break;
			case 'step2':
				$content[] = $this->select(false);
				$content[] = $this->step2(true);
			break;
			case 'step3':
				$content[] = $this->select(false);
				$content[] = $this->step3(true);
			break;
			case 'step4':
				$content[] = $this->select(false);
				$content[] = $this->step4(true);
			break;
			case 'step5':
				$content[] = $this->select(false);
				$content[] = $this->step5(true);
			break;
			case $this->lang['select']['action_remove']:
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case $this->lang['select']['action_start']:
			case 'start':
				$content[] = $this->select(false);
				$content[] = $this->start(true);
			break;
			case $this->lang['select']['action_stop']:
			case 'stop':
				$content[] = $this->select(false);
				$content[] = $this->stop(true);
			break;
			case $this->lang['select']['action_release']:
			case 'release':
				$content[] = $this->release(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'redirect':
				$this->redirect(true);
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
		require_once($this->rootdir.'/server/appliance/class/appliance.api.class.php');
		$controller = new appliance_api($this);
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
			require_once($this->rootdir.'/server/appliance/class/appliance.select.class.php');
			$controller = new appliance_select($this->openqrm, $this->response);
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
	 * Add appliance step 1 (Name)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step1( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step1.class.php');
			$controller                  = new appliance_step1($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step1'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step1']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step1' );
		$content['onclick'] = false;
		if($this->action === 'step1'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add appliance step 2 (Resource)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step2( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step2.class.php');
			$controller                  = new appliance_step2($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step2'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step2']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step2' );
		$content['onclick'] = false;
		if($this->action === 'step2'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add appliance step 3 (Image)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step3( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step3.class.php');
			$controller                  = new appliance_step3($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step3'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step3']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step3' );
		$content['onclick'] = false;
		if($this->action === 'step3'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add appliance step 4 (Image-Edit)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step4( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step4.class.php');
			$controller                  = new appliance_step4($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step4'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step4']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step4' );
		$content['onclick'] = false;
		if($this->action === 'step4'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add appliance step 5 (Kernel)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step5( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step5.class.php');
			$controller                  = new appliance_step5($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step5'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step5']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step5' );
		$content['onclick'] = false;
		if($this->action === 'step5'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Edit appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.edit.class.php');
			$controller                  = new appliance_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.remove.class.php');
			$controller                  = new appliance_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Remove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove' || $this->action === $this->lang['select']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Start appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.start.class.php');
			$controller                  = new appliance_start($this->openqrm, $this->response);
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
	 * Stop appliance
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.stop.class.php');
			$controller                  = new appliance_stop($this->openqrm, $this->response);
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
	 * Release appliance resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function release( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.release.class.php');
			$controller                  = new appliance_release($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['release'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Release';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'release' );
		$content['onclick'] = false;
		if($this->action === 'release' || $this->action === $this->lang['select']['action_release']){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access public
	 * @param bool $hidden
	 * @return null
	 */
	//--------------------------------------------
	function redirect( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.redirect.class.php');
			$controller                  = new appliance_redirect($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = '';
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
	}

}
?>
