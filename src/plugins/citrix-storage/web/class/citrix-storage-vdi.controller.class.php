<?php
/**
 * citrix-Storage Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_vdi_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_vdi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_vdi_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'citrix_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'citrix_identifier';
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
		'tab' => 'Select Citrix XenServer',
		'label' => 'Select Citrix XenServer Storage',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_recource' => 'Resource',
		'table_type' => 'Type',
		'table_deployment' => 'Deployment',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a Citrix XenServer Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Loading Citrix VDI(s). Please wait ..',
	), 
	'edit' => array (
		'tab' => 'Manage VDI',
		'label' => 'Manage VDI(s) on Citrix XenServer Storage %s',
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
		'action_sync_in_progress' => 'Source of synchronisation - Please wait', 
		'action_clone_in_progress' => 'Synchronisation in progress - Please wait',
		'action_clone_finished' => 'Syncronisation finished!',
		'table_name' => 'VDI',
		'table_uuid' => 'UUID',
		'table_description' => 'Description',
		'table_psize' => 'Size',
		'table_vsize' => 'Used',
		'table_source' => 'Source',
		'error_no_citrix' => 'Storage %s is not of type citrix-deployment',
		'please_wait' => 'Loading Citrix VDI(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add VDI',
		'label' => 'Add a VDI',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'form_sr' => 'Storage Ressource',
		'form_description' => 'Description',
		'msg_added' => 'Added VDI %s',
		'msg_add_failed' => 'Failed adding VDI %s',
		'error_exists' => 'VDI %s allready exists',
		'error_name' => 'Name must be %s',
		'error_description' => 'Description must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'lang_name_generate' => 'generate name',
		'please_wait' => 'Adding VDI. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'snap' => array (
		'label' => 'Snap VDI %s',
		'tab' => 'Snap VDI',
		'msg_snaped' => 'Snaped %s to %s',
		'msg_snap_failed' => 'Snapping failed for %s to %s',
		'form_name' => 'Name',
		'form_size' => 'Size (max: %s MB)',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'please_wait' => 'Snaping VDI. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'clone' => array (
		'label' => 'Clone VDI %s',
		'tab' => 'Clone VDI',
		'msg_cloned' => 'Cloned %s as %s',
		'msg_clone_failed' => 'Clone failed for %s as %s',
		'form_name' => 'Name',
		'lang_name_generate' => 'generate name',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Cloning VDI. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove VDI(s)',
		'msg_removed' => 'Removed VDI %s',
		'msg_vm_image_still_in_use' => 'Volume %s of Image id %s is still in use by appliance(s) %s',
		'please_wait' => 'Removing VDI(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'resize' => array (
		'label' => 'Resize VDI %s',
		'tab' => 'Resize VDI',
		'size' => 'min. %s MB, max. %s MB',
		'error_size' => 'Size must be %s',
		'error_size_exeeded' => 'Size exeeds %s MB',
		'error_size_undercut' => 'Size undercuts %s MB',
		'msg_resized' => 'Resized VDI %s',
		'please_wait' => 'Resizing VDI. Please wait ..',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/citrix-storage/lang", 'citrix-storage-vdi.ini');
		$this->tpldir   = $this->rootdir.'/plugins/citrix-storage/tpl';
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
			$this->action = "edit";
		}
		if($this->action === '') {
			$this->action = 'select';
		}
		// Set response and reload statfile
		if($this->action !== 'select') {
			$this->response->params['storage_id'] = $this->response->html->request()->get('storage_id');
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
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->add(true);
			break;
			case 'resize':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->resize(true);
			break;
			case 'snap':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->snap(true);
			break;
			case 'clone':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->duplicate(true);
			break;
			case $this->lang['edit']['action_remove']:
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->edit(false);
				$content[] = $this->remove(true);
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
		require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage.api.class.php');
		$controller = new citrix_storage_vdi_api($this);
		$controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Select Storages of type citrix
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.select.class.php');
			$controller = new citrix_storage_vdi_select($this->openqrm, $this->response);
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
	 * Edit citrix-storage
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			if($this->reload()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.edit.class.php');
				$controller                  = new citrix_storage_vdi_edit($this->openqrm, $this->response);
				$controller->actions_name    = $this->actions_name;
				$controller->tpldir          = $this->tpldir;
				$controller->message_param   = $this->message_param;
				$controller->identifier_name = $this->identifier_name;
				$controller->prefix_tab      = $this->prefix_tab;
				$controller->lang            = $this->lang['edit'];
				$data = $controller->action();
			}
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
			if($this->reload()) {
				require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.add.class.php');
				$controller                = new citrix_storage_vdi_add($this->openqrm, $this->response, $this);
				$controller->actions_name  = $this->actions_name;
				$controller->tpldir        = $this->tpldir;
				$controller->message_param = $this->message_param;
				$controller->lang          = $this->lang['add'];
				$controller->rootdir       = $this->rootdir;
				$controller->prefix_tab    = $this->prefix_tab;
				$data = $controller->action();
			}
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
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.remove.class.php');
			$controller                  = new citrix_storage_vdi_remove($this->openqrm, $this->response);
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
		if($this->action === 'remove' || $this->action === $this->lang['edit']['action_remove']){
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
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.clone.class.php');
			$controller                  = new citrix_storage_vdi_clone($this->openqrm, $this->response);
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
			require_once($this->rootdir.'/plugins/citrix-storage/class/citrix-storage-vdi.resize.class.php');
			$controller                  = new citrix_storage_vdi_resize($this->openqrm, $this->response);
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
	 * Reload Exports
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload() {
		$OPENQRM_SERVER_BASE_DIR = $this->openqrm->get('basedir');
		$storage_id = $this->response->html->request()->get('storage_id');
		$storage = new storage();
		$resource = new resource();
		$deployment = new deployment();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/citrix-storage/web/citrix-storage-stat/'.$resource->ip.'.vdi_list';
		$command = $OPENQRM_SERVER_BASE_DIR.'/plugins/citrix-storage/bin/openqrm-citrix-storage post_vdi -i '.$resource->ip;
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



	//--------------------------------------------
	/**
	 * Reload DataStore states
	 *
	 * @access public
	 */
	//--------------------------------------------
	function reload_ds() {
		$storage_id = $this->response->html->request()->get('storage_id');
		$storage = new storage();
		$resource = new resource();
		$deployment = new deployment();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$file = $OPENQRM_SERVER_BASE_DIR.'/plugins/citrix-storage/web/citrix-storage-stat/'.$resource->ip.'.ds_list';
		$command = $OPENQRM_SERVER_BASE_DIR.'/plugins/citrix-storage/bin/openqrm-citrix-storage post_ds_list -i '.$resource->ip;
		$openqrm = new resource();
		$openqrm->get_instance_by_id(0);
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($openqrm->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		return true;
	}





}
?>
