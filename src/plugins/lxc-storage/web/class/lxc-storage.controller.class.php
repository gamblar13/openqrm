<?php
/**
 * lxc-Storage Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class lxc_storage_controller
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
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lxc_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'lxc_identifier';
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
		'tab' => 'Select LXC-Storage',
		'label' => 'Select LXC-Storage',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a LXC-Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading. Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Select Volume group',
		'label' => 'Select Volume group on LXC-Storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_deployment' => 'Deployment',
		'action_edit' => 'select',
		'table_name' => 'Name',
		'table_pv' => 'PV',
		'table_lv' => 'LV',
		'table_sn' => 'SN',
		'table_attr' => 'Attr',
		'table_vsize' => 'Vsize',
		'table_vfree' => 'VFree',
		'error_no_lxc' => 'Storage %s is not of type lxc-storage',
		'please_wait' => 'Loading. Please wait ..',
	),
	'volgroup' => array (
		'tab' => 'Edit Volume group',
		'label' => 'Edit Volume group %s on LXC-Storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_deployment' => 'Deployment',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_attr' => 'Attr',
		'lang_pv' => 'PV / LV / SN',
		'lang_size' => 'Vsize / Vfree',
		'action_add' => 'Add new logical volume',
		'action_remove' => 'remove',
		'action_resize' => 'resize',
		'action_snap' => 'snap',
		'action_clone' => 'clone',
		'action_deploy' => 'deploy',
		'action_sync_in_progress' => 'Source of synchronisation - Please wait', 
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_name' => 'Lvol',
		'table_deployment' => 'Deployment',
		'table_attr' => 'Attr',
		'table_size' => 'Size',
		'table_source' => 'Source',
		'error_no_lxc' => 'Storage %s is not of type lxc-deployment',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add Logical Volume',
		'label' => 'Add Logical Volume to Volume group %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'msg_added' => 'Added Logical Volume %s',
		'msg_add_failed' => 'Failed adding Logical Volume %s',
		'error_exists' => 'Logical Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'lang_name_generate' => 'generate name',
		'please_wait' => 'Adding Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'snap' => array (
		'label' => 'Snap Logical Volume %s',
		'tab' => 'Snap Logical Volume',
		'msg_snaped' => 'Snaped %s to %s',
		'msg_snap_failed' => 'Snapping failed for %s to %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'please_wait' => 'Snaping Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'label' => 'Clone Logical Volume %s',
		'tab' => 'Clone Logical Volume',
		'msg_cloned' => 'Cloned %s as %s',
		'msg_clone_failed' => 'Clone failed for %s as %s',
		'form_name' => 'Name',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove Logical Volume(s)',
		'msg_removed' => 'Removed Logical Volume %s',
		'msg_vm_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing Logical Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'resize' => array (
		'label' => 'Resize Logical Volume %s',
		'tab' => 'Resize Logical Volume',
		'size' => 'min. %s MB, max. %s MB',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'error_size_undercut' => 'Size undercuts %s MB',
		'msg_resized' => 'Resized Logical Volume %s',
		'please_wait' => 'Resizing Logical Volume. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'templates' => array (
		'tab' => 'Deploy Templates',
		'label' => 'Deploy OS Templates to Volume %s on LXC-Storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_size' => 'Size (MB)',
		'lang_comment' => 'Description',
		'lang_deployment' => 'Deployment',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'lang_update' => 'Edit Template description',
		'templates' => 'Templates',
		'action_add' => 'Upload new Template',
		'action_deploy' => 'deploy',
		'action_upload' => 'upload',
		'action_delete' => 'delete',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'delete' => array (
		'tab' => 'Remove Templates',
		'label' => 'Remove Templates',
		'msg_removed' => 'Removed Template %s',
		'please_wait' => 'Removing Template. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'upload' => array (
		'tab' => 'Upload Templates',
		'label' => 'Upload a new OS Template',
		'form_url' => 'Template URL',
		'msg_uploaded' => 'Uploading Template %s',
		'please_wait' => 'Uploading Template. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'deploy' => array (
		'tab' => 'Deploy Template',
		'label' => 'Deploy OS Template',
		'msg_deployed' => 'Deploying Template %s to Volume %s',
		'please_wait' => 'Deploying Template. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'update' => array (
		'tab' => 'Update Template',
		'label' => 'Update description of Template %s',
		'form_comment' => 'Description',
		'msg_updated' => 'Updated Template %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),

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
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/lxc-storage/lang", 'lxc-storage.ini');
		$this->tpldir   = $this->rootdir.'/plugins/lxc-storage/tpl';
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
			$this->action = "volgroup";
		}
		if($this->action === '') {
			$this->action = 'select';
		}
		// Set response and reload statfile
		if($this->action !== 'select') {
			$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
			if($this->action !== 'remove') {
				$this->reload('vg');
			}
			if($this->action !== 'edit') {
				$this->response->params['volgroup'] = $this->response->html->request()->get('volgroup');
			}
		}
		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'volgroup':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->add(true);
			break;
			case 'resize':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->resize(true);
			break;
			case 'snap':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->snap(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->duplicate(true);
			break;
			case $this->lang['volgroup']['action_remove']:
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->remove(true);
			break;
			case 'templates':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->templates(true);
			break;
			case 'delete':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->delete(true);
			break;
			case 'upload':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->upload(true);
			break;
			case 'deploy':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->deploy(true);
			break;
			case 'update':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->volgroup(false);
				$content[] = $this->update(true);
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
	 * Select Storages of type lxc
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.select.class.php');
			$controller = new lxc_storage_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
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
	 * Edit lxc-storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.edit.class.php');
			$controller                  = new lxc_storage_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['edit'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['tab'];
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
	 * Edit lxc volgroup
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function volgroup( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->response->html->request()->get('reload') !== 'false') {
				$this->reload('lv');
			}
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.volgroup.class.php');
			$controller                  = new lxc_storage_volgroup($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['volgroup'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['volgroup']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'volgroup' );
		$content['onclick'] = false;
		if($this->action === 'volgroup'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add new Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.add.class.php');
			$controller                = new lxc_storage_add($this->openqrm, $this->response, $this);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['add'];
			$controller->rootdir       = $this->rootdir;
			$controller->prefix_tab    = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['add']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'add' );
		$content['onclick'] = false;
		if($this->action === 'add'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Remove Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.remove.class.php');
			$controller                  = new lxc_storage_remove($this->openqrm, $this->response);
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
		if($this->action === 'remove' || $this->action === $this->lang['volgroup']['action_remove']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Snapshot Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function snap( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.snap.class.php');
			$controller                  = new lxc_storage_snap($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['snap'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['snap']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'snap' );
		$content['onclick'] = false;
		if($this->action === 'snap' || $this->action === $this->lang['edit']['action_snap']){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * duplicate (clone) Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function duplicate( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.clone.class.php');
			$controller                  = new lxc_storage_clone($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['clone'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['clone']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'clone' );
		$content['onclick'] = false;
		if($this->action === 'clone' || $this->action === $this->lang['edit']['action_clone']){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * resize Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function resize( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.resize.class.php');
			$controller                  = new lxc_storage_resize($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['resize'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['resize']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'resize' );
		$content['onclick'] = false;
		if($this->action === 'resize'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * templates list
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function templates( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			$this->reload_templates();
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.templates.class.php');
			$controller                  = new lxc_storage_templates($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['templates'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['templates']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'templates' );
		$content['onclick'] = false;
		if($this->action === 'templates'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * delete templates
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function delete( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.delete.class.php');
			$controller                  = new lxc_storage_delete($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['delete'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['delete']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'delete' );
		$content['onclick'] = false;
		if($this->action === 'delete'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * upload templates
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function upload( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.upload.class.php');
			$controller                  = new lxc_storage_upload($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['upload'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['upload']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'upload' );
		$content['onclick'] = false;
		if($this->action === 'upload'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * deploy template
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function deploy( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.deploy.class.php');
			$controller                  = new lxc_storage_deploy($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['deploy'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['deploy']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'deploy' );
		$content['onclick'] = false;
		if($this->action === 'deploy'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * update templates
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function update( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/lxc-storage/class/lxc-storage.update.class.php');
			$controller                  = new lxc_storage_update($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['update'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['update']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'update' );
		$content['onclick'] = false;
		if($this->action === 'update'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * Reload Exports
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload($mode) {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');

		$storage_id = $this->response->html->request()->get('storage_id');
		$volgroup   = $this->response->html->request()->get('volgroup');

		$storage = new storage();
		$resource = new resource();
		$deployment = new deployment();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$command = '';
		$file = '';
		// reload volume group
		if($mode === 'vg') {
			$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/web/storage/'.$resource->id.'.vg.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/bin/openqrm-lxc-storage post_vg -t '.$deployment->type;
		}
		// reload logical volumes
		if($mode === 'lv') {
			$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/web/storage/'.$resource->id.'.'.$volgroup.'.lv.stat';
			$command .= $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/bin/openqrm-lxc-storage post_lv';
 			$command .=  ' -v '.$volgroup.' -t '.$deployment->type;
		}
		$command .= ' -u '.$this->openqrm->admin()->name.' -p '.$this->openqrm->admin()->password;
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}




	//--------------------------------------------
	/**
	 * Reload Templates
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_templates() {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');

		$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/web/storage/lxc-templates.stat';
		$command = $OPENQRM_SERVER_BASE_DIR.'/plugins/lxc-storage/bin/openqrm-lxc-storage get_lxc_templates';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$openqrm_server = new openqrm_server();
		$openqrm_server->send_command($command);
		while (!$this->file->exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}


}
?>
