<?php
/**
 * Citrix XenServer Host auto-discovery Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_discovery_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_msg";
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
var $identifier_name = 'xenserver_ad_id';
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
		'tab' => 'XenServer Hosts auto-discovery',
		'label' => 'Discovered Citrix XenServer Hosts',
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
		'error_no' => '<b>No XenServer Host appliance configured yet!</b><br><br>Please create a Citrix XenServer Host first!',
		'new' => 'New Appliance',
		'please_wait' => 'Auto-Discovering Citrix XenServer Server. Please wait ..',
	),
	'edit' => array (
		'tab' => 'Edit Citrix XenServer Appliance',
		'label' => 'Citrix XenServer VMs on Appliance %s',
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
		'error_no_nfs' => 'Appliance %s is not of type Citrix XenServer Host',
		'please_wait' => 'Loading VMs. Please wait ..',
	),
	'add' => array (
		'tab' => 'Add XenServer Host',
		'label' => 'Add Citrix XenServer Host to openQRM',
		'form_name' => 'Name',
		'mac_address' => 'MAC Address',
		'ip_address' => 'IP Address',
		'hostname' => 'Hostname',
		'user' => 'User',
		'password' => 'Password',
		'comment' => 'Comment',
		'msg_added' => 'Added XenServer Host %s',
		'no_id_given' => 'Please selecdt an XenServer Host',
		'error_exists' => 'XenServer Host %s allready integrated',
		'error_integrating' => 'Error connecting to XenServer Host %s with the given username and password',
		'error_name' => 'Name must be %s',
		'please_wait' => 'Adding Citrix XenServer Host. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'delete' => array (
		'tab' => 'Remove XenServer Host(s) from openQRM',
		'label' => 'Remove Citrix XenServer Host(s) from openQRM',
		'citrix_host_discovery_confirm_delete' => 'Remove the following Citrix XenServer Hosts from openQRM?',
		'msg_removed' => 'Removed Citrix XenServer Host %s',
		'please_wait' => 'Removing Citrix XenServer Host(s). Please wait ..',
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
		$this->basedir  = $this->openqrm->get('basedir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/plugins/citrix/lang", 'citrix-discovery.ini');
		$this->tpldir   = $this->rootdir.'/plugins/citrix/tpl';
		require_once $this->rootdir."/plugins/citrix/class/citrix-discovery.class.php";
		$this->discovery = new citrix_discovery();
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
		$citrix_discovery = new citrix_discovery();
		$discovered_esx_hosts = $this->discovery->get_count();
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
	 * Select discovered XenServer Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-discovery.select.class.php');
			$controller = new citrix_discovery_select($this->openqrm, $this->response);
			$controller->discovery      = $this->discovery;
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
	 * Edit citrix Host for integration
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-discovery.edit.class.php');
			$controller                  = new citrix_discovery_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['edit'];
			$controller->discovery      = $this->discovery;
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
	 * Remove citrix Host from discovery
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function delete( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-discovery.delete.class.php');
			$controller                  = new citrix_discovery_delete($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->prefix_tab      = $this->prefix_tab;
			$controller->lang            = $this->lang['delete'];
			$controller->discovery      = $this->discovery;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['delete']['tab'];
		$content['value']   = $data;
		$content['hidden']  = true;
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
	 * Integrates a new discovered XenServer Host
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/plugins/citrix/class/citrix-discovery.add.class.php');
			$controller                = new citrix_discovery_add($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->tpldir;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang['add'];
			$controller->rootdir       = $this->rootdir;
			$controller->prefix_tab    = $this->prefix_tab;
			$controller->discovery      = $this->discovery;
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
	 * Rescan for XenServer Host, triggers auto-discovery
	 *
	 * @access public
	 */
	//--------------------------------------------
	function rescan() {
		require_once $this->rootdir."/plugins/citrix/class/citrix-discovery.class.php";
		$citrix_discovery = new citrix_discovery();
		$command  = $this->basedir."/plugins/citrix/bin/openqrm-citrix-autodiscovery";
		$resource = new resource();
		$resource->get_instance_by_id(0);
		$file = $this->rootdir.'/plugins/citrix/citrix-stat/autodiscovery_finished';
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
							if (($citrix_discovery->mac_discoverd_already($esx_mac)) && ($citrix_discovery->ip_discoverd_already($esx_ip))) {
								$esx_comment = "Added by auto-discovery";
								$citrix_discovery_fields['xenserver_ad_mac'] = $esx_mac;
								$citrix_discovery_fields['xenserver_ad_ip'] = $esx_ip;
								$citrix_discovery_fields['xenserver_ad_hostname'] = $esx_ip;
								$citrix_discovery_fields['xenserver_ad_user'] = "root";
								$citrix_discovery_fields['xenserver_ad_password'] = "";
								$citrix_discovery_fields['xenserver_ad_comment'] = $esx_comment;
								$citrix_discovery_fields['xenserver_ad_is_integrated '] = 0;
								$citrix_discovery->add($citrix_discovery_fields);
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
