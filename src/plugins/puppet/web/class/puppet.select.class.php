<?php
/**
 * puppet Appliance
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class puppet_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'puppet_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "puppet_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'puppet_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'puppet_identifier';
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
		$this->tpldir   = $this->rootdir.'/plugins/puppet/tpl';

		require_once($this->rootdir.'/plugins/puppet/class/puppet.class.php');
		$this->puppet = new puppet();

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
		$t = $this->response->html->template($this->tpldir.'/puppet-select.tpl.php');
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

		$table = $this->response->html->tablebuilder('puppet', $this->response->get_array($this->actions_name, 'select'));
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


		$h['groups']['title']    = $this->lang['groups'];
		$h['groups']['sortable'] = false;
		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;

		$result = $appliance->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$b = array();
		foreach($result as $k => $v) {

			$resource = new resource();
			$resource = $resource->get_instance_by_id($v['appliance_resources']);

			$tmp = array();
			$tmp['appliance_id'] = $v['appliance_id'];
			$tmp['appliance_name'] = $v['appliance_name'];
			$tmp['appliance_resources'] = $v['appliance_resources'];
			$tmp['appliance']  = '<b>'.$this->lang['id'].':</b> '.$v['appliance_id'].'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['name'].':</b> '.$v['appliance_name'].'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['resource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';

			$groups = $this->puppet->get_groups($v['appliance_name']);
			$tmp['groups'] = implode(', ', $groups);

			$a          = $response->html->a();
			$a->href    = $response->get_url($this->actions_name, 'edit' ).'&appliance_id='.$v['appliance_id'];
			$a->label   = $this->lang['action_edit'];
			$a->title   = $this->lang['action_edit'];
			$a->css     = 'edit';				
			$a->handler = 'onclick="wait();"';
			$tmp['edit'] = $a->get_string();

			$b[] = $tmp;
		}

		$table->css          = 'htmlobject_table';
		$table->border       = 0;
		$table->id           = 'Tabelle';
		$table->form_action	 = $this->response->html->thisfile;
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
