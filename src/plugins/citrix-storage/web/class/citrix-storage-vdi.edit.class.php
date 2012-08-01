<?php
/**
 * citrix-Storage Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class citrix_storage_vdi_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'citrix_storage_vdi_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "citrix_storage_vdi_msg";
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

		$this->statfile = $this->openqrm->get('basedir').'/plugins/citrix-storage/web/citrix-storage-stat/'.$this->resource->ip.'.vdi_list';
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
			$t = $this->response->html->template($this->tpldir.'/citrix-storage-vdi-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->lang['lang_attr'], 'lang_attr');
			$t->add($this->lang['lang_pv'], 'lang_pv');
			$t->add($this->lang['lang_size'], 'lang_size');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $this->storage->name), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->lang['canceled'], 'canceled');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_citrix'], $this->response->html->request()->get('storage_id'));
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
		if(strpos($this->deployment->type, 'citrix') !== false) {
			$resource_icon_default = "/img/resource.png";
			$storage_icon = "/plugins/citrix-storage/img/plugin.png";
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

			$a = $this->response->html->a();
			$a->label = $this->lang['action_add'];
			$a->css   = 'add';
			$a->href  = $this->response->get_url($this->actions_name, "add");
			$d['add'] = $a;

			$body = array();

			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					$disabled = array();
					$t = $this->response->html->template($this->openqrm->get('webdir').'/js/openqrm-progressbar.js');
					foreach($lines as $line) {
						if($line !== '') {
							$citrix_line = trim($line);
							$citrix_parameter_arr = explode(":", $citrix_line);
							$citrix_volume_uuid = $citrix_parameter_arr[0];
							$citrix_volume_name = ltrim($citrix_parameter_arr[1], "@");
							$citrix_volume_name = str_replace("@", " ", $citrix_volume_name);
							$citrix_volume_description = ltrim($citrix_parameter_arr[2], "@");
							$citrix_volume_description = str_replace("@", " ", $citrix_volume_description);
							$citrix_volume_sr_uuid = $citrix_parameter_arr[3];
							$citrix_volume_size = ltrim($citrix_parameter_arr[4], "@");
							$citrix_volume_size = $citrix_volume_size/1024;
							$citrix_volume_size = $citrix_volume_size/1024;
							$citrix_volume_size = number_format($citrix_volume_size, 0, '', '.');
							$volume_size = $citrix_volume_size.' MB';
							$c    = '&#160;';
							// get physical size from image
							$citrix_volume_psize = '';
							$image = new image();
							$image->get_instance_by_name($citrix_volume_name);
							if ((isset($image->id)) && ($image->id > 1)) {
								$citrix_volume_psize = $image->capabilities;
								$citrix_volume_psize = str_replace('SIZE=', '', $citrix_volume_psize);
								$citrix_volume_psize = $citrix_volume_psize.' MB';
							}
							if (!strlen($citrix_volume_psize)) {
								$citrix_volume_psize=$volume_size;
							}

							$c = $this->response->html->a();
							$c->title   = $this->lang['action_clone'];
							$c->label   = $this->lang['action_clone'];
							$c->handler = 'onclick="wait();"';
							$c->css     = 'clone';
							$c->href    = $this->response->get_url($this->actions_name, "clone").'&vdi_name='.$citrix_volume_name;

							
							$body[] = array(
								'icon'   => $d['icon'],
								'name'   => $citrix_volume_name,
								'uuid' => $citrix_volume_uuid,
								'description'   => $citrix_volume_description,
//								'psize'   => $citrix_volume_psize,
								'vsize'   => $volume_size,
								'clone'  => $c,
							);
						}
					}
				}
			}

			$h['icon']['title']      = '&#160;';
			$h['icon']['sortable']   = false;
			$h['name']['title']      = $this->lang['table_name'];
			$h['uuid']['title']    = $this->lang['table_uuid'];
			$h['description']['title']      = $this->lang['table_description'];
//			$h['psize']['title']      = $this->lang['table_psize'];
			$h['vsize']['title']      = $this->lang['table_psize'];
			$h['clone']['title']     = '&#160;';
			$h['clone']['sortable']  = false;

			$table = $this->response->html->tablebuilder('lvols', $this->response->get_array($this->actions_name, 'edit'));
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
			$table->identifier_disabled = $disabled;
			$table->actions_name        = $this->actions_name;
			$table->actions             = array($this->lang['action_remove']);

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}
?>
