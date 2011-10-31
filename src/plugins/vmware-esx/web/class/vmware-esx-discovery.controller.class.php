<?php
/**
 * VMware ESX Host auto-discovery Controller
 *
 * This file is part of openQRM.
 *
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Matt Rechenburg <matt@openqrm-enterprise.com>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class vmware_esx_discovery_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'vmware_esx_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "vmware_esx_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'vmware_esx_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'vmw_esx_ad_id';
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
		'tab' => 'ESX Hosts auto-discovery',
		'label' => 'Discovered ESX Hosts',
		'action_edit' => 'edit',
		'table_name' => 'Name',
		'table_id' => 'Id',
		'table_mac' => 'MAC',
		'table_ip' => 'IP',
		'table_hostname' => 'Hostname',
		'table_user' => 'User',
		'table_password' => 'Password',
		'table_comment' => 'Comment',
		'table_type' => 'Type',
		'error_no_storage' => '<b>No storage configured yet!</b><br><br>Please create a NFS Storage first!',
		'new_storage' => 'New Storage',
		'please_wait' => 'Auto-Discovering ESX Server. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit NFS-storage',
		'label' => 'NFS Volumes on storage %s',
		'lang_id' => 'ID',
		'lang_name' => 'Name',
		'lang_resource' => 'Resource',
		'lang_state' => 'State',
		'action_add' => 'Add new Volume',
		'action_refresh' => 'Reload Page',
		'action_manual' => 'Manual Configuration',
		'action_snap' => 'snap',
		'action_remove' => 'remove',
		'table_name' => 'Name',
		'table_export' => 'Export',
		'error_no_nfs' => 'Storage %s is not of type nfs-deployment',
		'please_wait' => 'Loading Volumes. Please wait ..',
		'manual_configured' => 'Storage is manually configured and can not be be edited by openQrm',
	),
	'add' => array (
		'tab' => 'Add ESX Host',
		'label' => 'Add ESX Host to openQRM',
		'form_name' => 'Name',
		'mac_address' => 'MAC Address',
		'ip_address' => 'IP Address',
		'hostname' => 'Hostname',
		'user' => 'User',
		'password' => 'Password',
		'comment' => 'Comment',
		'msg_added' => 'Added ESX Host %s',
		'no_id_given' => 'Please selecdt an ESX Host',
		'error_exists' => 'ESX Host %s allready integrated',
		'error_integrating' => 'Error connecting to ESX Host %s with the given username and password',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding ESX Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'manual' => array (
		'tab' => 'Manual NFS Configuration',
		'label' => 'Manual Configuration',
		'explanation_1' => 'In case the NFS Storage server is not managed by openQRM please use this form to manually create the list of exported paths to server-images on the NFS Storage server.',
		'explanation_2' => 'Please notice that in case a manual configuration exist openQRM will not send any automated Storage-authentication commands to this NFS Storage-server!',
		'please_wait' => 'Saving Manual Configuration. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
		'saved' => 'Manual Configuration has been saved',
	),
	'snap' => array (
		'label' => 'Snap Volume(s)',
		'msg_snaped' => 'Snaped %s as %s',
		'error_exists' => 'Volume %s allready exists',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Snaping Volume(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'delete' => array (
		'tab' => 'Remove ESX Host(s) from discovery',
		'label' => 'Remove ESX Host(s) from discovery',
		'vmware_esx_host_discvoery_confirm_delete' => 'Remove the following ESX Hosts from discovery?',
		'msg_removed' => 'Removed ESX Host %s',
		'please_wait' => 'Removing ESX Host(s). Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($user) {
		$this->user    = $user;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		require_once $this->rootdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->rootdir."/class/htmlobjects/");
		$this->html->lang = $this->user->translate($this->html->lang, $this->rootdir."/plugins/vmware-esx/lang", 'htmlobjects.ini');
		$this->lang = $this->user->translate($this->lang, $this->rootdir."/plugins/vmware-esx/lang", 'vmware-esx-discovery.ini');
		$this->tpldir = $this->rootdir.'plugins/vmware-esx/tpl';
		require_once $this->rootdir."/plugins/vmware-esx/class/vmware-esx-discovery.class.php";
		$this->db = new vmware_esx_discovery();
		$this->response = $this->html->response();
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
		$vmware_esx_discovery = new vmware_esx_discovery();
		$discovered_esx_hosts = $this->db->get_count();
//		if ($discovered_esx_hosts < 1) {
//			$this->action = "rescan";
//		}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'delete':
				$content[] = $this->select(false);
				$content[] = $this->delete(true);
			break;
			case 'rescan':
				$this->rescan();
				$this->action = 'select';
				$content[] = $this->select(true);
			break;
			case 'add':
				$content[] = $this->select(false);
				$content[] = $this->add(true);
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
	 * Select discovered ESX Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.select.class.php');
			$controller = new vmware_esx_discovery_select($this->response, $this->db);
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
	 * Edit vmware-esx Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.edit.class.php');
			$controller                  = new vmware_esx_discovery_edit($this->response, $this->db);
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
	 * Remove vmware-esx Host from discovery
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function delete( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.delete.class.php');
			$controller                  = new vmware_esx_discovery_delete($this->response, $this->db);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['delete'];
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
	 * Integrates a new discovered ESX Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/vmware-esx/class/vmware-esx-discovery.add.class.php');
			$controller                = new vmware_esx_discovery_add($this->response, $this->db);
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
	 * Rescan for ESX Host, triggers auto-discovery
	 *
	 * @access public
	 */
	//--------------------------------------------
	function rescan() {
		global $OPENQRM_SERVER_BASE_DIR;
		require_once $this->rootdir."/plugins/vmware-esx/class/vmware-esx-discovery.class.php";
		$vmware_esx_discovery = new vmware_esx_discovery();
		$command  = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/vmware-esx/bin/openqrm-vmware-esx-autodiscovery";
		$resource = new resource();
		$resource->get_instance_by_id(0);
		$file = 'vmware-esx-stat/autodiscovery_finished';
		if(file_exists($file)) {
			unlink($file);
		}
		$resource->send_command($resource->ip, $command);
		while (!file_exists($file)) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}
		// read discovery file
		if(file_exists($file)) {
			$lines = explode("\n", file_get_contents($file));
			if(count($lines) >= 1) {
				foreach($lines as $line) {
					if($line !== '') {
						$line = explode('@', $line);
						$esx_ip = $line[0];
						$esx_mac = $line[1];
						// check if discovered already
						if ((strlen($esx_mac)) && (strlen($esx_ip))) {
							if (($vmware_esx_discovery->mac_discoverd_already($esx_mac)) && ($vmware_esx_discovery->ip_discoverd_already($esx_ip))) {
								$esx_comment = "Added by auto-discovery";
								$vmware_esx_discovery_fields['vmw_esx_ad_mac'] = $esx_mac;
								$vmware_esx_discovery_fields['vmw_esx_ad_ip'] = $esx_ip;
								$vmware_esx_discovery_fields['vmw_esx_ad_hostname'] = $esx_ip;
								$vmware_esx_discovery_fields['vmw_esx_ad_user'] = "root";
								$vmware_esx_discovery_fields['vmw_esx_ad_password'] = "";
								$vmware_esx_discovery_fields['vmw_esx_ad_comment'] = $esx_comment;
								$vmware_esx_discovery_fields['vmw_esx_ad_is_integrated '] = 0;
								$vmware_esx_discovery->add($vmware_esx_discovery_fields);
							}
							unset($esx_mac);
							unset($esx_ip);
						}
					}
				}
			}
		}
		unlink($file);
		return true;
	}

}
?>
