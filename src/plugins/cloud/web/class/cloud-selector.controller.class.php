<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class cloud_selector_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'cloud_selector';
/**
* message param
* @access public
* @var string
*/
var $message_param = "msg-cloud-request";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'project_tab';
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
	'cloud_selector_name' => 'Cloud Product Manager',
	'cloud_selector_title' => 'Cloud Products on portal ',
	'cloud_selector_cpu' => 'CPU',
	'cloud_selector_disk' => 'Disk',
	'cloud_selector_ha' => 'Highavailability',
	'cloud_selector_kernel' => 'Kernel',
	'cloud_selector_memory' => 'Memory',
	'cloud_selector_network' => 'Network',
	'cloud_selector_puppet' => 'Applications',
	'cloud_selector_resource' => 'Virtualization',
	'cloud_selector_products' => 'Products',
	'cloud_selector_add_product' => 'Add new CPU Product to the Cloud',
	'cloud_selector_howto_add_product' => 'Use the slider to select product quantity and how much CCU to charge per hour',
	'cloud_selector_equals' => 'equals',
	'cloud_selector_ccu_per_hour' => 'CCU/h',
	'cloud_selector_product_name' => 'Product Name',
	'cloud_selector_product_description' => 'Product Description',
	'cloud_selector_product_id' => 'ID',
	'cloud_selector_product_quantity' => 'Quantity',
	'cloud_selector_product_price' => 'Price',
	'cloud_selector_product_state' => 'Status',
	'cloud_selector_add_successful' => 'Successfully added Cloud Product',
	'cloud_selector_product_exists' => 'Cloud %s Product with Quantity %s already exists. Not adding!',
	'cloud_selector_remove_successful' => 'Successfully removed Cloud Product',
	'cloud_selector_remove' => 'Remove',
	'cloud_selector_product_disable' => 'Disable',
	'cloud_selector_product_enable' => 'Enable',
	'cloud_selector_product_sort_up' => 'Up',
	'cloud_selector_product_sort_down' => 'Down',
	'cloud_selector_product_disable_successful' => 'Successfully disabled Cloud Product',
	'cloud_selector_product_enable_successful' => 'Successfully enabled Cloud Product',
	'cloud_selector_product_sort_up_successful' => 'Sorted up Cloud Product',
	'cloud_selector_product_sort_down_successful' => 'Sorted down Cloud Product',
	'cloud_selector_not_enabled_label' => 'Cloud Selector disabled',
	'cloud_selector_not_enabled' => 'The Cloud Product Mananger (Cloud Selector) is disabled. <br>Please enable it in the Main Cloud Configuration',


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
		$this->rootdir  = $this->openqrm->get('rootdir');
		$this->webdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
		$this->tpldir   = $this->webdir.'/plugins/cloud/tpl';
		$this->identifier_name = "cloud_selector_id";
		require_once $this->webdir."/class/htmlobjects/htmlobject.class.php";
		$this->html = new htmlobject($this->webdir."/class/htmlobjects/");
		$this->html->lang = $this->user->translate($this->html->lang, $this->webdir."/plugins/cloud/lang", 'htmlobjects.ini');
		require_once $this->webdir."/plugins/cloud/class/cloudconfig.class.php";
		$this->cloud_config = new cloudconfig();

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
			$this->action = "products";
		}
		$content = array();

		// enabled in main config ?
		$cloud_selector_enabled = $this->cloud_config->get_value_by_key('cloud_selector');
		if (!strcmp($cloud_selector_enabled, "true")) {
			switch( $this->action ) {
				case '':
				case 'products':
					$content[] = $this->products(true);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'cpu':
					$content[] = $this->products(false);
					$content[] = $this->cpu(true);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'disk':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(true);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'ha':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(true);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'kernel':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(true);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'memory':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(true);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'network':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(true);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(false);
				break;
				case 'puppet':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(true);
					$content[] = $this->resource(false);
				break;
				case 'resource':
					$content[] = $this->products(false);
					$content[] = $this->cpu(false);
					$content[] = $this->disk(false);
					$content[] = $this->ha(false);
					$content[] = $this->kernel(false);
					$content[] = $this->memory(false);
					$content[] = $this->network(false);
					$content[] = $this->puppet(false);
					$content[] = $this->resource(true);
				break;
				case 'add':
					$content[] = $this->add(true);
				break;
				case 'remove':
					$content[] = $this->remove(true);
				break;
				case 'disable':
					$content[] = $this->disable(true);
				break;
				case 'enable':
					$content[] = $this->enable(true);
				break;
				case 'up':
					$content[] = $this->up(true);
				break;
				case 'down':
					$content[] = $this->down(true);
				break;
			}
		} else {

			$c['label']   = $this->lang['cloud_selector_not_enabled_label'];
			$c['value']   = $this->lang['cloud_selector_not_enabled'];
			$c['onclick'] = false;
			$c['active']  = true;
			$c['target']  = $this->response->html->thisfile;
			$c['request'] = '';
			$content[] = $c;

		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * products
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function products( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.products.class.php');
			$controller = new cloud_selector_products($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_name'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'products' );
		$content['onclick'] = false;
		if($this->action === 'products'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * cpu
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function cpu( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.cpu.class.php');
			$controller = new cloud_selector_cpu($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_cpu'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'cpu' );
		$content['onclick'] = false;
		if($this->action === 'cpu'){
			$content['active']  = true;
		}
		return $content;
	}



	//--------------------------------------------
	/**
	 * disk
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disk( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.disk.class.php');
			$controller = new cloud_selector_disk($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_disk'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'disk' );
		$content['onclick'] = false;
		if($this->action === 'disk'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * ha
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function ha( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.ha.class.php');
			$controller = new cloud_selector_ha($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_ha'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'ha' );
		$content['onclick'] = false;
		if($this->action === 'ha'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * kernel
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function kernel( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.kernel.class.php');
			$controller = new cloud_selector_kernel($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_kernel'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'kernel' );
		$content['onclick'] = false;
		if($this->action === 'kernel'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * memory
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function memory( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.memory.class.php');
			$controller = new cloud_selector_memory($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_memory'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'memory' );
		$content['onclick'] = false;
		if($this->action === 'memory'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * network
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function network( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.network.class.php');
			$controller = new cloud_selector_network($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_network'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'network' );
		$content['onclick'] = false;
		if($this->action === 'network'){
			$content['active']  = true;
		}
		return $content;
	}

	
	//--------------------------------------------
	/**
	 * puppet
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function puppet( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.puppet.class.php');
			$controller = new cloud_selector_puppet($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_puppet'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'puppet' );
		$content['onclick'] = false;
		if($this->action === 'puppet'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function resource( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.resource.class.php');
			$controller = new cloud_selector_resource($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_resource'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'resource' );
		$content['onclick'] = false;
		if($this->action === 'resource'){
			$content['active']  = true;
		}
		return $content;
	}




	//--------------------------------------------
	/**
	 * add
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function add( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.add.class.php');
			$controller = new cloud_selector_add($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_add'];
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
	 * remove
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.remove.class.php');
			$controller = new cloud_selector_remove($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_remove'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * disable
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function disable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.disable.class.php');
			$controller = new cloud_selector_disable($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_disable'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'disable' );
		$content['onclick'] = false;
		if($this->action === 'disable'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * enable
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function enable( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.enable.class.php');
			$controller = new cloud_selector_enable($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_enable'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'enable' );
		$content['onclick'] = false;
		if($this->action === 'enable'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * up
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function up( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.up.class.php');
			$controller = new cloud_selector_up($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_up'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'up' );
		$content['onclick'] = false;
		if($this->action === 'up'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * down
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function down( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->webdir.'/plugins/cloud/class/cloud-selector.down.class.php');
			$controller = new cloud_selector_down($this->openqrm, $this->response);
			$controller->actions_name  = $this->actions_name;
			$controller->tpldir        = $this->webdir.'/plugins/cloud/tpl';
			$controller->identifier_name = $this->identifier_name;
			$controller->message_param = $this->message_param;
			$controller->lang          = $this->user->translate($this->lang, $this->webdir."/plugins/cloud/lang", 'cloud-selector.ini');
			$data = $controller->action();
		}
		$content['label']   = $this->lang['cloud_selector_down'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'down' );
		$content['onclick'] = false;
		if($this->action === 'down'){
			$content['active']  = true;
		}
		return $content;
	}



}
?>
