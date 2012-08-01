<?php
/**
 * AOE-Storage Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class aoe_storage_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aoe_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aoe_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aoe_tab';
/**
* identifier name
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
		$this->openqrm = $openqrm;
		$this->file = $this->openqrm->file();
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		$storage_id = $this->response->html->request()->get('storage_id');
		if($storage_id === '') {
			return false;
		}
		// set ENV
		$deployment = new deployment();
		$storage    = new storage();
		$resource   = new resource();

		$storage->get_instance_by_id($storage_id);
		$resource->get_instance_by_id($storage->resource_id);
		$deployment->get_instance_by_id($storage->type);

		$this->resource   = $resource;
		$this->storage    = $storage;
		$this->deployment = $deployment;

		$this->statfile = $this->openqrm->get('basedir').'/plugins/aoe-storage/web/storage/'.$resource->id.'.aoe.stat';
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
		$this->init();
		$data = $this->edit();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/aoe-storage-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_vfree'], 'lang_vfree');
			$t->add($this->lang['lang_vsize'], 'lang_vsize');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_aoe'], $this->response->html->request()->get('storage_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {

		if($this->deployment->type === 'aoe-deployment') {
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/aoe-storage/img/plugin.png";
			$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = "<img width=24 height=24 src=$state_icon>";
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a->get_string();

			$body = array();
			$identifier_disabled = array();
			$file = $this->statfile;
			if(file_exists($file)) {				
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$i = 0;
					$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							if($i === 0) {
								$d['vsize'] = number_format((double)$line[0], 0, '', '.').' MB';
								$d['vfree'] = number_format((double)$line[1], 0, '', '.').' MB';
							} else {
								$name = $line[3];
								$volume_size = $line[5];

								$a = $this->response->html->a();
								$a->title   = $this->lang['action_auth'];
								$a->label   = $this->lang['action_auth'];
								$a->handler = 'onclick="wait();"';
								$a->css     = 'auth';
								$a->href    = $this->response->get_url($this->actions_name, "auth").'&volume='.$name;							
								$auth_link = $a->get_string();

								$a = $this->response->html->a();
								$a->title   = $this->lang['action_clone'];
								$a->label   = $this->lang['action_clone'];
								$a->handler = 'onclick="wait();"';
								$a->css     = 'clone';
								$a->href    = $this->response->get_url($this->actions_name, "clone").'&volume='.$name;							
								$clone_link = $a->get_string();	

								if ($volume_size == "clone_in_progress") {
									// add to disabled identifier
									$identifier_disabled[] = $name;
									$auth_link = "-";
									$clone_link = "-";
									// progressbar
									$t->add(uniqid('b'), 'id');
									$t->add('/openqrm/base/plugins/aoe-storage/storage/'.$this->resource->id.'.aoe.'.$name.'.sync_progress', 'url');
									$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
									$t->add($this->lang['action_clone_finished'], 'lang_finished');
									$volume_size = $t->get_string();
								} else {
									$volume_size = number_format((double)$line[5], 0, '', '.').' MB';
								}
								$body[] = array(
									'icon' => $d['icon'],
									'name'   => $name,
									'mac'   => $line[4],
									'interface'   => $line[0],
									'shelf'   => $line[1],
									'slot'   => $line[2],
									'size' => $volume_size,
									'auth' => $auth_link,
									'clone' => $clone_link,
								);
							}
						}
						$i++;
					}
				}
			}

			$h['icon'] = array();
			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['mac'] = array();
			$h['mac']['title'] = $this->lang['table_mac'];
			$h['interface'] = array();
			$h['interface']['title'] = $this->lang['table_interface'];
			$h['shelf'] = array();
			$h['shelf']['title'] = $this->lang['table_shelf'];
			$h['slot'] = array();
			$h['slot']['title'] = $this->lang['table_slot'];
			$h['size'] = array();
			$h['size']['title'] = $this->lang['table_size'];
			$h['auth'] = array();
			$h['auth']['title'] = '&#160;';
			$h['auth']['sortable'] = false;
			$h['clone'] = array();
			$h['clone']['title'] = '&#160;';
			$h['clone']['sortable'] = false;

			$table = $this->response->html->tablebuilder('exports', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
			$table->limit           = 20;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->id              = 'Tabelle';
			$table->css             = 'htmlobject_table';
			$table->border          = 1;
			$table->cellspacing     = 0;
			$table->cellpadding     = 3;
			$table->form_action	    = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;
			$table->identifier      = 'name';
			$table->identifier_name = $this->identifier_name;
			$table->identifier_disabled = $identifier_disabled;
			$table->actions_name    = $this->actions_name;
			$table->actions         = array($this->lang['action_remove']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}
?>
