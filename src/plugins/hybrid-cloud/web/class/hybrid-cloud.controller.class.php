<?php
/**
 * hybrid_cloud Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class hybrid_cloud_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_identifier';
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
	'account' => array(
		'tab' => 'Hybrid-Cloud Migration',
		'label' => 'Select account',
		'label_add' => 'Add new account',
		'label_help' => 'Help',
		'label_remove' => 'Remove account(s)',
		'label_edit' => 'Update account %s',
		'table_id' => 'ID',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_config' => 'Config',
		'table_ssh' => 'SSH',
		'table_description' => 'Description',
		'form_name' => 'Account name',
		'form_type' => 'Account type',
		'form_config' => 'rc-config (file)',
		'form_ssh' => 'SSH-Key (file)',
		'form_description' => 'Description',		
		'error_name' => 'Name must be %s only',
		'lang_name_generate' => 'Generate Name',
		'lang_browse' => 'browse',
		'lang_browser' => 'Filepicker',
		'lang_help' => 'The Cloud rc-config file (on openQRM at e.g. /home/cloud/.eucarc) should define all parameters for the public cloud tools
					(e.g. ec2-ami-tools, ec2-api-tools or euca2ools) to work seamlessly. A typical rc-config file for UEC looks similar to %s.
					The Cloud ssh-key (on openQRM at e.g. /home/cloud/.euca/mykey.priv)	provides the console login to the Public Cloud systems.',
		'lang_help_link' => 'this',
		'action_add' => 'Add new account',
		'action_remove' => 'remove',
		'action_import' => 'import',
		'action_export' => 'export',
		'msg_removed' => 'removed account %s',
		'msg_added' => 'added account %s',
		'msg_updated' => 'updated account %s',
		'action_edit' => 'edit',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'export' => array(
		'tab' => 'Hybrid-Cloud Export',
		'label' => 'Select an image to export to %s',
		'label_target' => 'Export image %s to %s',
		'table_name' => 'Name',
		'table_id' => 'ID',
		'table_version' => 'Version',
		'table_deployment' => 'Deployment',
		'table_isactive' => 'Active',
		'table_comment' => 'Comment',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_image' => 'Image',
		'form_name' => 'Name',
		'form_size' => 'Size',
		'form_architecture' => 'Architecture',
		'action_export' => 'export',
		'error_name' => 'Name may contain %s only',
		'msg_exported' => 'exporting image %s to account %s',
		'lang_name_generate' => 'generate name',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'import' => array(
		'tab' => 'Hybrid-Cloud Import',
		'label' => 'Select an instance to import from %s',
		'label_target' => 'Select an image to import instance %s from %s',
		'table_host' => 'Host',
		'table_id' => 'ID',
		'table_ami' => 'AMI',
		'table_type' => 'Type',
		'table_state' => 'State',
		'table_name' => 'Name',
		'table_version' => 'Version',
		'table_deployment' => 'Deployment',
		'table_isactive' => 'Active',
		'table_comment' => 'Comment',
		'table_image' => 'Image',
		'action_import' => 'import',
		'error_name' => 'Name may contain %s only',
		'msg_imported' => 'importing instance %s from account %s to image %s',
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
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/hybrid-cloud/lang", 'hybrid-cloud.ini');
		$this->tpldir   = $this->rootdir.'/plugins/hybrid-cloud/tpl';
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
			$this->action = 'account';
		}

		$content = array();
		switch( $this->action ) {
			case '':
			default:
			case 'account':
				$content[] = $this->account(true);
			break;
			case 'export':
				$content[] = $this->account(false);
				$content[] = $this->export(true);
			break;
			case 'import':
				$content[] = $this->account(false);
				$content[] = $this->import(true);
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
		require_once($this->rootdir.'/plugins/hybrid-cloud/class/hybrid-cloud.account.api.class.php');
		$controller = new hybrid_cloud_account_api($this);
		$controller->action();
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function account( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hybrid-cloud/class/hybrid-cloud.account.controller.class.php');
			$controller = new hybrid_cloud_account_controller($this);
			$controller->tpldir        = $this->tpldir;
			$controller->prefix_tab    = $this->prefix_tab;
			$controller->lang          = $this->lang['account'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['account']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'account' );
		$content['onclick'] = false;
		if($this->action === 'account'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function export( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hybrid-cloud/class/hybrid-cloud.export.controller.class.php');
			$controller = new hybrid_cloud_export_controller($this);
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['export'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['export']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'export' );
		$content['onclick'] = false;
		if($this->action === 'export'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function import( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/hybrid-cloud/class/hybrid-cloud.import.controller.class.php');
			$controller = new hybrid_cloud_import_controller($this);
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['import'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['import']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'import' );
		$content['onclick'] = false;
		if($this->action === 'import'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
