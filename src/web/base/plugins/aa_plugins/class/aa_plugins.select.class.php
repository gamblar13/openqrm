<?php
/**
 * Plugins Select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class aa_plugins_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
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
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($response, $file) {
		$this->response = $response;
		$this->file     = $file;
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
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/aa_plugins-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['lang_filter'], 'lang_filter');
		$t->add($this->lang['please_wait'], 'please_wait');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider
	 */
	//--------------------------------------------
	function select() {

		$icon_started = '<img src="/openqrm/base/img/start.png" border="0" alt="click to stop">';
		$icon_stopped = '<img src="/openqrm/base/img/stop.png" border="0" alt="click to start">';
		$icon_enabled = '<img src="/openqrm/base/img/disable.png" border="0" alt="click to disable">';
		$icon_disabled = '<img src="/openqrm/base/img/enable.png" border="0" alt="click to enable">';

		$plugin = new plugin();
		$plugins_available = $plugin->available();
		$plugins_enabled = $plugin->enabled();
		$plugins_started = $plugin->started();

		$h = array();
		$h['icon']['title'] ='&#160;';
		$h['icon']['sortable'] = false;
		$h['name']['title'] = $this->lang['table_name'];
		$h['type']['title'] = $this->lang['table_type'];
		$h['description']['title'] = $this->lang['table_description'];
		$h['description']['sortable'] = false;
		$h['enabled']['title'] = $this->lang['table_enabled'];
		$h['started']['title'] = $this->lang['table_started'];

		$table = $this->response->html->tablebuilder('plugins', $this->response->get_array($this->actions_name, 'select'));
		$table->max = count($plugins_available);
		$table->init();
		$tps = $table->get_params();
		$tp = '';
		foreach($tps['plugins'] as $k => $v) {
			$tp .= '&plugins['.$k.']='.$v;
		}

		$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$thisfile = $this->response->html->thisfile;
		$b = array();
		$plugtype = array();
		$i = 0;
		foreach ($plugins_available as $index => $plugin_name) {
			$tmp = $plugin->get_config($plugin_name);
			$plugin_description = $tmp['description'];
			$plugin_type =  $tmp['type'];
			$plugtype[] = $plugin_type;
			if (!strlen($this->response->html->request()->get('plugin_filter')) || strstr($this->response->html->request()->get('plugin_filter'), $plugin_type )) {
				$b[$i] = array();
				// add icon for type
				$plugin_type_icon = $plugin_type;
				switch ($plugin_type) {
					case "storage":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/storage.png">';
						break;
					case "virtualization":
						// mark the virtualization-storage plugins with both icons
						if (strstr($plugin_name, "-storage")) {
							$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/virtualization.png">';
							$plugin_type_icon .= '<img title="storage" alt="storage" src="/openqrm/base/img/storage.png">';
						} else {
							$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/virtualization.png">';
						}
						break;
					case "cloud":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/cloud.png">';
						break;
					case "enterprise":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/enterprise.png">';
						break;
					case "deployment":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/deployment.png">';
						break;
					case "network":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/datacenter.png">';
						break;
					case "HA":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/ha.png">';
						break;
					case "monitoring":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/monitoring.png">';
						break;
					case "management":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/user.png">';
						break;
					case "misc":
						$plugin_type_icon = '<img title="'.$plugin_type.'" alt="'.$plugin_type.'" src="/openqrm/base/img/manage.png">';
						break;
				}
				if (!in_array($plugin_name, $plugins_enabled)) {

					$a = $this->response->html->a();
					$a->label    = $icon_disabled;
					$a->href     = $this->response->get_url($this->actions_name, "enable");
					$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
					$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
					$a->href    .= $tp;
					$a->handler  = 'onclick="wait();"';
					$a->css      = 'enable';
					$a->title    = sprintf($this->lang['title_enable'], $plugin_name);

					$b[$i]['icon'] = '<img src="/openqrm/base/img/plugin.png">';
					$b[$i]['name'] = $plugin_name;
					$b[$i]['type'] = $plugin_type_icon;
					$b[$i]['description'] = $plugin_description;
					$b[$i]['enabled'] = $a->get_string();
					$b[$i]['started'] = '&#160;';
				} else {
					$plugin_icon_path="$RootDir/plugins/$plugin_name/img/plugin.png";
					$plugin_icon="/openqrm/base/plugins/$plugin_name/img/plugin.png";
					$plugin_icon_default="/openqrm/base/plugins/aa_plugins/img/plugin.png";
					if ($this->file->exists($plugin_icon_path)) {
						$plugin_icon_default=$plugin_icon;
					}

					$a = $this->response->html->a();
					$a->label    = $icon_enabled;
					$a->href     = $this->response->get_url($this->actions_name, "disable");
					$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
					$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
					$a->href    .= $tp;
					$a->handler  = 'onclick="wait();"';
					$a->css      = 'disable';
					$a->title    = sprintf($this->lang['title_disable'], $plugin_name);

					$b[$i]['icon'] = '<img src="'.$plugin_icon_default.'">';
					$b[$i]['name'] = $plugin_name;
					$b[$i]['type'] = $plugin_type_icon;
					$b[$i]['description'] = $plugin_description;
					$b[$i]['enabled'] = $a->get_string();
					// started ?
					if (!in_array($plugin_name, $plugins_started)) {
						$a = $this->response->html->a();
						$a->label    = $icon_stopped;
						$a->href     = $this->response->get_url($this->actions_name, "start");
						$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
						$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
						$a->href    .= $tp;
						$a->handler  = 'onclick="wait();"';
						$a->css      = 'start';
						$a->title    = sprintf($this->lang['title_start'], $plugin_name);

						$b[$i]['started'] = $a->get_string();
					} else {
						$a = $this->response->html->a();
						$a->label    = $icon_started;
						$a->href     = $this->response->get_url($this->actions_name, "stop");
						$a->href    .= '&'.$this->identifier_name.'[]='.$plugin_name;
						$a->href    .= '&plugin_filter='.$this->response->html->request()->get('plugin_filter');
						$a->href    .= $tp;
						$a->handler  = 'onclick="wait();"';
						$a->css      = 'stop';
						$a->title    = sprintf($this->lang['title_stop'], $plugin_name);

						$b[$i]['started'] = $a->get_string();
					}
				}
			}
			$i++;
		}

		$plugs = array();
		$plugs[] = array('','');
		$plugtype = array_unique($plugtype);
		foreach($plugtype as $p) {
			$plugs[] = array($p,$p);
		}
		$select = $this->response->html->select();
		$select->add($plugs, array(0,1));
		$select->name = 'plugin_filter';
		$select->handler = 'onchange="wait();this.form.submit();return false;"';
		$select->selected = array($this->response->html->request()->get('plugin_filter'));
		$box = $this->response->html->box();
		$box->add($select);
		$box->id = 'plugins_filter';
		$box->css = 'htmlobject_box';
		$box->label = $this->lang['lang_filter'];

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->sort = 'name';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = true;
		$table->sort_link = false;
		$table->add_headrow($box->get_string());
		$table->head = $h;
		$table->body = $b;
		$table->max = count($b);
		$table->form_action = $this->response->html->thisfile;
		$table->actions_name = $this->actions_name;
		$table->actions = array(
							$this->lang['action_enable'],
							$this->lang['action_disable'],
							$this->lang['action_start'],
							$this->lang['action_stop']
						);
		$table->identifier = 'name';
		$table->identifier_name = $this->identifier_name;

		return $table;
	}

}
?>
