<?php
/**
 * Resource Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class resource_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'resource_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'resource_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "resource_msg";
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
var $lang = array();

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
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/resource-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$d = array();

		$h = array();
		$h['resource_state']['title'] ='&#160;';
		$h['resource_state']['sortable'] = false;
		$h['resource_icon']['title'] ='&#160;';
		$h['resource_icon']['sortable'] = false;
		$h['resource_id']['title'] = $this->lang['table_id'];
		$h['resource_hostname']['title'] = $this->lang['table_name'];
		$h['resource_mac']['title'] = $this->lang['table_mac'];
		$h['resource_ip']['title'] = $this->lang['table_ip'];
		$h['resource_type']['title'] = $this->lang['table_type'];
		$h['resource_type']['sortable'] = false;
		$h['resource_memtotal']['title'] = $this->lang['table_memory'];
		$h['resource_cpunumber']['title'] = $this->lang['table_cpu'];
		$h['resource_nics']['title'] = $this->lang['table_nics'];
		$h['resource_load']['title'] = $this->lang['table_load'];

		$resource = new resource();
		$params  = $this->response->get_array($this->actions_name, 'select');
		$b       = array();

		unset($params['resource_filter']);

		$table = $this->response->html->tablebuilder('resource', $params);
		$table->offset = 0;
		$table->sort = 'resource_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max = $resource->get_count('all');

		$table->init();

		$resources = $resource->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		foreach ($resources as $index => $resource_db) {
			// prepare the values for the array
			$resource = new resource();
			$resource->get_instance_by_id($resource_db["resource_id"]);
			$res_id = $resource->id;
			$mem_total = $resource_db['resource_memtotal'];
			$mem_used = $resource_db['resource_memused'];
			$mem = "$mem_used/$mem_total";
			$swap_total = $resource_db['resource_swaptotal'];
			$swap_used = $resource_db['resource_swapused'];
			$swap = "$swap_used/$swap_total";
			if ($resource->id == 0) {
				$resource_icon_default="/openqrm/base/img/logo.png";
				$resource_type = "openQRM";
				$resource_mac = "x:x:x:x:x:x";
			} else {
				$resource_mac = $resource_db["resource_mac"];
				$resource_icon_default="/openqrm/base/img/resource.png";
				// the resource_type
				if ((strlen($resource->vtype)) && (!strstr($resource->vtype, "NULL"))){
					// find out what should be preselected
					$virtualization = new virtualization();
					$virtualization->get_instance_by_id($resource->vtype);
					if ($resource->id == $resource->vhostid) {
						// physical system
						$resource_type = "<nobr>".$virtualization->name."</nobr>";
					} else {
						// vm
						switch ($virtualization->type) {
							// vm controller
							case 'kvm-vm':
								$link = '?plugin=kvm&controller=kvm-vm';
							break;
							case 'kvm-storage-vm':
								$link = '?plugin=kvm-storage&controller=kvm-storage-vm';
							break;
							case 'xen-vm':
								$link = '?plugin=xen&controller=xen-vm';
							break;
							case 'xen-storage-vm':
								$link = '?plugin=xen-storage&controller=xen-storage-vm';
							break;
							case 'openvz-storage-vm':
								$link = '?plugin=openvz-storage&controller=openvz-storage-vm';
							break;
							case 'lxc-storage-vm':
								$link = '?plugin=lxc-storage&controller=lxc-storage-vm';
							break;
							// central controller
							case 'vmware-esx-vm':
								$link = '?plugin=vmware-esx&controller=vmware-esx';
							break;
							case 'citrix-storage-vm':
								$link = '?plugin=citrix-storage&controller=citrix-storage';
							break;
							case 'citrix-vm':
								$link = '?plugin=citrix&controller=citrix';
							break;
							default:
								$link = '';
							break;
						}
						$resource_type_link_text = "<nobr>".$virtualization->name." on Res. ".$resource->vhostid."</nobr>";
						$a = $this->response->html->a();
						$a->name  = '';
						$a->label = $resource_type_link_text;
						$a->href  = $this->response->html->thisfile.$link;
						$resource_type = $a->get_string();
					}
				} else {
					$resource_type = "Unknown";
				}

			}
			$state_icon="/openqrm/base/img/".$resource->state.".png";
			// idle ?
			if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
				$state_icon="/openqrm/base/img/idle.png";
			}
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}

			$resource_cpus = $resource_db["resource_cpunumber"];
			if (!strlen($resource_cpus)) {
				$resource_cpus = '?';
			}
			$resource_nics = $resource_db["resource_nics"];
			if (!strlen($resource_nics)) {
				$resource_nics = '?';
			}
			isset($resource_db["resource_hostname"]) ? $name = $resource_db["resource_hostname"] : $name = '&#160;';
			isset($resource_db["resource_nics"]) ? $nics = $resource_db["resource_nics"] : $nics = '&#160;';
			isset($resource_db["resource_load"]) ? $load = $resource_db["resource_load"] : $load = '&#160;';

			if ($this->response->html->request()->get('resource_filter') === '' ||	($this->response->html->request()->get('resource_filter') == $resource->vtype )) {
				$b[] = array(
					'resource_state' => "<img width=24 height=24 src=$state_icon>",
					'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
					'resource_id' => $resource_db["resource_id"],
					'resource_hostname' => $name,
					'resource_mac' => $resource_mac,
					'resource_ip' => $resource_db["resource_ip"],
					'resource_type' => $resource_type,
					'resource_memtotal' => $mem,
					'resource_cpunumber' => $resource_cpus,
					'resource_nics' => $nics,
					'resource_load' => $load,
				);
			}
		}

		$virtulization_types = new virtualization();
		$filter = array();
		$filter[] = array('value' => '', 'label' => '');
		$filter = array_merge($filter, $virtulization_types->get_list());

		$select = $this->response->html->select();
		$select->add($filter, array('value','label'));
		$select->name = 'resource_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('resource_filter'));
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'resource_filter';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$add = $this->response->html->a();
		$add->title   = $this->lang['action_add'];
		$add->label   = $this->lang['action_add'];
		$add->handler = 'onclick="wait();"';
		$add->css     = 'add';
		$add->href    = $this->response->get_url($this->actions_name, "add");
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = false;
		$table->sort_link = false;
		$table->head = $h;
		$table->body = $b;
		$table->actions_name = $this->actions_name;
		$table->actions = array($this->lang['action_reboot'], $this->lang['action_poweroff'], $this->lang['action_remove']);
		$table->identifier = 'resource_id';
		$table->identifier_name = $this->identifier_name;
		$table->identifier_disabled = array(0);
		$table->limit_select = array(
			array("value" => 10, "text" => 10),
			array("value" => 20, "text" => 20),
			array("value" => 30, "text" => 30),
			array("value" => 50, "text" => 50),
			array("value" => 100, "text" => 100),
		);

		$d['form']   = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['add']    = $add->get_string();
		$d['filter'] = $box->get_string();
		$d['table']  = $table;
		return $d;
	}

}
?>
