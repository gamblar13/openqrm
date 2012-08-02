<?php
/**
 * openvz-Storage Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openvz_storage_templates
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'openvz_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "openvz_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'openvz_tab';
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
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->volgroup = $this->response->params['volgroup'];
		$this->lvol = $response->html->request()->get('lvol');
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

		$this->statfile = $this->openqrm->get('basedir').'/plugins/openvz-storage/web/storage/openvz-templates.stat';

		require_once($this->openqrm->get('webdir').'/plugins/openvz-storage/class/openvz-template.class.php');
		$this->openvztemplate = new openvztemplate();
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
		$data = $this->templates();
		if($data !== false) {
			$t = $this->response->html->template($this->tpldir.'/openvz-storage-templates.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_comment'], 'lang_comment');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $this->lvol, $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->lang['canceled'], 'canceled');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_openvz'], $this->response->html->request()->get('storage_id'));
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * templates
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function templates() {
		if(strpos($this->deployment->type, 'openvz-deployment') !== false) {
			$resource_icon_default = "/img/resource.png";
			$storage_icon = "/plugins/openvz-storage/img/plugin.png";
			$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default = $storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			// Storage info
			$d['state'] = "<img src=$state_icon>";
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['deployment'] = $this->deployment->type;
			$d['id'] = $this->storage->id;

			$a = '&#160';
			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "upload");
			$d['add'] = $a;

			$body = array();

			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$name = $line[0];
							$size = $line[1];
							// description
							$comment = ' ';
							$this->openvztemplate->get_instance_by_name($name);
							if ($this->openvztemplate->id === '') {
								// add
								$openvz_fields["openvz_template_id"] = openqrm_db_get_free_id('openvz_template_id', $this->openvztemplate->_db_table);
								$openvz_fields["openvz_template_name"] = $name;
								$this->openvztemplate->add($openvz_fields);
							} else {
								if (strlen($this->openvztemplate->description)) {
									$comment = $this->openvztemplate->description;
								}
							}
							$this->openvztemplate->id = '';

							$p = $this->response->html->a();
							$p->title   = $this->lang['action_deploy'];
							$p->label   = $this->lang['action_deploy'];
							$p->handler = 'onclick="wait();"';
							$p->css     = 'deploy';
							$p->href    = $this->response->get_url($this->actions_name, "deploy").'&template='.$name.'&lvol='.$this->lvol;

							$e = $this->response->html->a();
							$e->title   = $this->lang['lang_update'];
							$e->label   = $this->lang['lang_update'];
							$e->handler = 'onclick="wait();"';
							$e->css     = 'edit';
							$e->href    = $this->response->get_url($this->actions_name, "update").'&template='.$name;

							$body[] = array(
								'icon'   => $d['icon'],
								'name'   => $name,
								'size'   => $size,
								'comment'   => $comment,
								'deploy' => $p,
								'update' => $e,
							);
						}
					}
				}
			}

			$h['icon']['title']      = '&#160;';
			$h['icon']['sortable']   = false;
			$h['name']['title']      = $this->lang['lang_name'];
			$h['size']['title']      = $this->lang['lang_size'];
			$h['comment']['title']    = $this->lang['lang_comment'];
			$h['deploy']['sortable'] = false;
			$h['update']['sortable'] = false;

			$table = $this->response->html->tablebuilder('templates', $this->response->get_array($this->actions_name, 'templates'));
			$table->sort                = 'name';
			$table->limit               = 10;
			$table->offset              = 0;
			$table->order               = 'ASC';
			$table->max                 = count($body);
			$table->autosort            = true;
			$table->sort_link           = false;
			$table->id                  = 'Tabelle';
			$table->css                 = 'htmlobject_table';
			$table->border              = 1;
			$table->cellspacing         = 0;
			$table->cellpadding         = 3;
			$table->form_action         = $this->response->html->thisfile;
			$table->head                = $h;
			$table->body                = $body;
			$table->identifier          = 'name';
			$table->identifier_name     = $this->identifier_name;
			$table->actions_name        = $this->actions_name;
			$table->actions             = array($this->lang['action_delete']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}
?>
