<?php
/**
 * collectd Appliance
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class collectd_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'collectd_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "collectd_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'collectd_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'collectd_identifier';
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
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/collectd/tpl';
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
		$response = $this->select();
		$t = $this->response->html->template($this->tpldir.'/collectd-select.tpl.php');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;

	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->response;
		$appliance = new appliance();
		$active_state_icon="/openqrm/base/img/active.png";
		$inactive_state_icon="/openqrm/base/img/idle.png";
		$resource_icon_default="/openqrm/base/img/resource.png";

		$table = $this->response->html->tablebuilder('collectd', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max   = $appliance->get_count();

		$table->init();

		$h['appliance_id']['title']    = $this->lang['id'];
		$h['appliance_id']['sortable'] = true;
		$h['appliance_id']['hidden']   = true;

		$h['appliance']['title']    = $this->lang['appliance'];
		$h['appliance']['sortable'] = false;

		$h['appliance_name']['title']    = $this->lang['name'];
		$h['appliance_name']['sortable'] = true;
		$h['appliance_name']['hidden']   = true;

		$h['appliance_resources']['title']    = $this->lang['resource'];
		$h['appliance_resources']['sortable'] = true;
		$h['appliance_resources']['hidden']   = true;

		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;

		$result = $appliance->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$b = array();
		foreach($result as $k => $v) {

			$collectd_appliance = new appliance();
			$collectd_appliance->get_instance_by_id($v['appliance_id']);
			$appliance_name = $collectd_appliance->name;
			$resource_id = '';
			$resource_ip = '';
			if ($collectd_appliance->resources < 0) {
				$resource_id = '-';
				$resource_ip = 'auto-select';
			} else if ($collectd_appliance->resources > 0) {
				$resource = new resource();
				$resource->get_instance_by_id($collectd_appliance->resources);
				$appliance_name = $collectd_appliance->name;
				$resource_id = $resource->id;
				$resource_ip = $resource->ip;
			} else if ($collectd_appliance->resources == 0) {
				$appliance_name="openqrm";
				$resource_id = 0;
				$resource_ip = 'openQRM';
			}

			$tmp = array();
			$tmp['appliance_id'] = $v['appliance_id'];
			$tmp['appliance_name'] = $v['appliance_name'];
			$tmp['appliance_resources'] = $v['appliance_resources'];
			$tmp['appliance']  = '<b>'.$this->lang['id'].':</b> '.$v['appliance_id'].'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['name'].':</b> '.$v['appliance_name'].'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['resource'].':</b> '.$resource_id.' / '.$resource_ip.'<br>';

			// graphs available already ?
			$collectd_graph = '';
			$graph_html = $this->rootdir."/plugins/collectd/graphs/".$appliance_name."/index.html";
			$graph_link = "/openqrm/base/plugins/collectd/graphs/".$appliance_name;
			if (file_exists($graph_html)) {
				$a          = $response->html->a();
				$a->href    = $graph_link;
				$a->label   = $this->lang['system_statistics'];
				$a->title   = $this->lang['system_statistics'];
				$a->css     = 'graphs';
				$a->handler = 'onclick="wait();"';
				$tmp['edit'] = $a->get_string();
			} else {
				$tmp['edit'] = '<img src="/openqrm/base/img/progress.gif" width="30" height="30" alt="'.$this->lang['graphs_available_soon'].'" title="'.$this->lang['graphs_available_soon'].'"/>';
			}
			unset($resource);
			$b[] = $tmp;
		}

		$table->css          = 'htmlobject_table';
		$table->border       = 0;
		$table->id           = 'Tabelle';
		$table->form_action	= $this->response->html->thisfile;
		$table->head         = $h;
		$table->body         = $b;
		$table->sort_params  = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form    = true;
		$table->sort_link    = false;
		$table->autosort     = false;
		$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
				);
		$response->table = $table;
		return $response;
	}


}
?>
