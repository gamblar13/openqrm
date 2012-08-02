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

class openvz_storage_volgroup
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

		$this->statfile = $this->openqrm->get('basedir').'/plugins/openvz-storage/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
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
			$t = $this->response->html->template($this->tpldir.'/openvz-storage-volgroup.tpl.php');
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
			$t->add(sprintf($this->lang['label'], $this->volgroup, $data['name']), 'label');
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
	 * Edit
	 *
	 * @access public
	 * @return array|false
	 */
	//--------------------------------------------
	function edit() {
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

			// Volgroup info
			$lines = explode("\n", $this->file->get_contents($this->openqrm->get('basedir').'/plugins/openvz-storage/web/storage/'.$this->resource->id.'.vg.stat'));
			foreach($lines as $line) {
				$line = explode("@", $line);
				if(isset($line[0]) && $line[0] === $this->volgroup) {

					//handle format sent by df
					$line[5] = str_replace('MB', '.00', $line[5]);
					$line[6] = str_replace('MB', '.00', $line[6]);

					$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '.').' MB';
					$vfree = str_replace('m', '', $line[6]);
					if($vfree !== '0') {
						$vfree = substr($line[6], 0, strpos($line[6], '.'));
					}
					$d['volgroup_name'] = $line[0];
					$d['volgroup_pv'] = $line[1];
					$d['volgroup_lv'] = $line[2];
					$d['volgroup_sn'] = $line[3];
					$d['volgroup_attr'] = $line[4];
					$d['volgroup_vsize'] = $vsize;
					$d['volgroup_vfree'] = number_format($vfree, 0, '', '.').' MB';
				}
			}

			$a = '&#160';
			if($d['volgroup_vfree'] !== '0 MB') {
				$a = $this->response->html->a();
				$a->label = $this->lang['action_add'];
				$a->css   = 'add';
				$a->href  = $this->response->get_url($this->actions_name, "add");
			}
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
							$line = explode('@', $line);
							$name = $line[1];
							$mode = substr($line[3], 0, 1);
							$s    = '&#160;';
							$c    = '&#160;';
							$r    = '&#160;';
							$src  = '&#160;';
							if ($line[4] == "clone_in_progress") {
								// add to disabled identifier
								$disabled[] = $name;
								// progressbar
								$t->add(uniqid('b'), 'id');
								$t->add('/openqrm/base/plugins/openvz-storage/storage/'.$this->resource->id.'.lvm.'.$name.'.sync_progress', 'url');
								$t->add($this->lang['action_clone_in_progress'], 'lang_in_progress');
								$t->add($this->lang['action_clone_finished'], 'lang_finished');
								$volume_size = $t->get_string();
							} else if ($line[4] == "sync_in_progress") {
								$volume_size = $this->lang['action_sync_in_progress'];
								$disabled[] = $name;
							} else {
								$volume_size = number_format(substr($line[4], 0, strpos($line[4], '.')), 0, '', '.').' MB';
							}

							if( $line[4] !== "sync_in_progress" && $line[4] !== "clone_in_progress" ) {
								if($d['volgroup_vfree'] !== '0 MB' ) {
									if($mode !== 's') {
										$s = $this->response->html->a();
										$s->title   = $this->lang['action_snap']; 
										$s->label   = $this->lang['action_snap'];
										$s->handler = 'onclick="wait();"';
										$s->css     = 'snap';
										$s->href    = $this->response->get_url($this->actions_name, "snap").'&lvol='.$line[1];
									} else {
										$disabled[] = $line[5];
										$src = $line[5];
									}
									if($vfree >= (int)substr($line[4], 0, strpos($line[4], '.'))) {
										$c = $this->response->html->a();
										$c->title   = $this->lang['action_clone'];
										$c->label   = $this->lang['action_clone'];
										$c->handler = 'onclick="wait();"';
										$c->css     = 'clone';
										$c->href    = $this->response->get_url($this->actions_name, "clone").'&lvol='.$line[1];
									}
								}
								$r = $this->response->html->a();
								$r->title   = $this->lang['action_resize'];
								$r->label   = $this->lang['action_resize'];
								$r->handler = 'onclick="wait();"';
								$r->css     = 'resize';
								$r->href    = $this->response->get_url($this->actions_name, "resize").'&lvol='.$line[1];

								$p = $this->response->html->a();
								$p->title   = $this->lang['action_deploy'];
								$p->label   = $this->lang['action_deploy'];
								$p->handler = 'onclick="wait();"';
								$p->css     = 'deploy';
								$p->href    = $this->response->get_url($this->actions_name, "templates").'&lvol='.$line[1];


							} else {
								$disabled[] = $name;
							}
							$body[] = array(
								'icon'   => $d['icon'],
								'deployment' => $line[0],
								'name'   => $name,
								'attr'   => $line[3],
								'source' => $src,
								'size'   => $volume_size,
								'snap'   => $s,
								'clone'  => $c,
								'resize' => $r,
								'templates' => $p,
							);
						}
					}
				}
			}

			$h['icon']['title']      = '&#160;';
			$h['icon']['sortable']   = false;
			$h['name']['title']      = $this->lang['table_name'];
			$h['deployment']['title']    = $this->lang['table_deployment'];
			$h['attr']['title']      = $this->lang['table_attr'];
			$h['source']['title']    = $this->lang['table_source'];
			$h['size']['title']      = $this->lang['table_size'];
			$h['snap']['title']      = '&#160;';
			$h['snap']['sortable']   = false;
			$h['clone']['title']     = '&#160;';
			$h['clone']['sortable']  = false;
			$h['resize']['title']    = '&#160;';
			$h['resize']['sortable'] = false;
			$h['templates']['title']    = '&#160;';
			$h['templates']['sortable'] = false;

			$table = $this->response->html->tablebuilder('lvols', $this->response->get_array($this->actions_name, 'volgroup'));
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
