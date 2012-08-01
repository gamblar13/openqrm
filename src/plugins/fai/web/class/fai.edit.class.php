<?php
/**
 * Local-Storage Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class fai_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'fai_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "fai_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'local_tab';
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
		require_once($this->openqrm->get('webdir').'/plugins/fai/class/fai-volume.class.php');
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
			$t = $this->response->html->template($this->tpldir.'/fai-edit.tpl.php');
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
			$msg = sprintf($this->lang['error_no_local'], $this->response->html->request()->get('storage_id'));
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
		if($this->deployment->type === 'fai-deployment') {
			$resource_icon_default="/img/resource.png";
			$storage_icon="/plugins/fai/img/plugin.png";
			$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = '<img width="24" height="24" src="'.$state_icon.'">';
			$d['icon'] = '<img width="24" height="24" src="'.$resource_icon_default.'">';
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

			$table = $this->response->html->tablebuilder('exports', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'fai_volume_name';
			$table->limit           = 10;
			$table->offset          = 0;
			$table->order           = 'ASC';
			$table->max             = count($body);
			$table->autosort        = true;
			$table->sort_link       = false;
			$table->init();

			$fai_volume = new fai_volume();
			$fai_volume_arr = $fai_volume->display_overview($table->offset, $table->limit, $table->sort, $table->order);
			if(count($fai_volume_arr) >= 1) {
				foreach($fai_volume_arr as $k => $v) {

					$c = $this->response->html->a();
					$c->title   = $this->lang['action_clone'];
					$c->label   = $this->lang['action_clone'];
					$c->handler = 'onclick="wait();"';
					$c->css     = 'clone';
					$c->href    = $this->response->get_url($this->actions_name, "clone").'&volume='.$v['fai_volume_name'];

					// edit image
					$local_image = new image();
					$local_image->get_instance_by_name($v['fai_volume_name']);
					$e = $this->response->html->a();
					$e->title   = $this->lang['action_edit'];
					$e->label   = $this->lang['action_edit'];
					$e->handler = 'onclick="wait();"';
					$e->css     = 'edit';
					$e->href    = '/openqrm/base/index.php?base=image&image_action=edit&image_id='.$local_image->id;

					$body[] = array(
						'icon' => $d['icon'],
						'id' => $v['fai_volume_id'],
						'name'   => $v['fai_volume_name'],
						'root'   => $v['fai_volume_root'],
						'description' => $v['fai_volume_description'],
						'actions' => $c,
						'edit' => $e,
					);
				}
			}

			$h['icon'] = array();
			$h['icon']['title'] = '&#160;';
			$h['icon']['sortable'] = false;
			$h['id'] = array();
			$h['id']['title'] = $this->lang['table_id'];
			$h['id']['sortable'] = false;
			$h['name'] = array();
			$h['name']['title'] = $this->lang['table_name'];
			$h['root'] = array();
			$h['root']['title'] = $this->lang['table_root'];
			$h['description'] = array();
			$h['description']['title'] = $this->lang['table_description'];
			$h['actions'] = array();
			$h['actions']['title'] = '&#160;';
			$h['actions']['sortable'] = false;
			$h['edit'] = array();
			$h['edit']['title'] = '&#160;';
			$h['edit']['sortable'] = false;

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
