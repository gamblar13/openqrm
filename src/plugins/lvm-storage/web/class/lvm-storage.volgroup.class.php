<?php
/**
 * lvm-Storage Edit Storage
 *
 * This file is part of openQRM.
 * 
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 * 
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
 */

class lvm_storage_volgroup
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'lvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "lvm_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'lvm_tab';
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
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
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

		$this->statfile = 'storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
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
			$t = $this->response->html->template($this->tpldir.'/lvm-storage-volgroup.tpl.php');
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
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $this->volgroup, $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			$t->add($this->lang['canceled'], 'canceled');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_lvm'], $this->response->html->request()->get('storage_id'));
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
		if(strpos($this->deployment->type, 'lvm') !== false) {
			$resource_icon_default="/openqrm/base/img/resource.png";
			$storage_icon="/openqrm/base/plugins/lvm-storage/img/storage.png";
			$state_icon="/openqrm/base/img/".$this->resource->state.".png";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
				$state_icon="/openqrm/base/img/unknown.png";
			}
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/".$storage_icon)) {
				$resource_icon_default=$storage_icon;
			}

			// Storage info
			$d['state'] = "<img src=$state_icon>";
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['name'] = $this->storage->name;
			$d['deployment'] = $this->deployment->type;
			$d['id'] = $this->storage->id;

			// Volgroup info
			$lines = explode("\n", file_get_contents('storage/'.$this->resource->id.'.vg.stat'));
			foreach($lines as $line) {
				$line = explode("@", $line);
				if(isset($line[0]) && $line[0] === $this->volgroup) {
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
			if(file_exists($file)) {
				$lines = explode("\n", file_get_contents($file));
				if(count($lines) >= 1) {
					$disabled = array();
					foreach($lines as $line) {
						if($line !== '') {
							$line = explode('@', $line);
							$mode = substr($line[3], 0, 1);
							$s    = '&#160;';
							$c    = '&#160;';
							$r    = '&#160;';
							$src  = '&#160;';
							if( $line[0] === $this->deployment->type ) {
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
							} else {
								$disabled[] = $line[1];
							}

							$body[] = array(
								'icon'   => $d['icon'],
								'deploy' => $line[0],
								'name'   => $line[1],
								'attr'   => $line[3],
								'source' => $src,
								'size'   => number_format(substr($line[4], 0, strpos($line[4], '.')), 0, '', '.').' MB',
								'snap'   => $s,
								'clone'  => $c,
								'resize' => $r,
							);
						}
					}
				}
			}

			$h['icon']['title']      = '&#160;';
			$h['icon']['sortable']   = false;
			$h['name']['title']      = $this->lang['table_name'];
			$h['deploy']['title']    = $this->lang['table_deployment'];
			$h['attr']['title']      = $this->lang['table_attr'];
			$h['source']['title']    = $this->lang['table_source'];
			$h['size']['title']      = $this->lang['table_size'];
			$h['snap']['title']      = '&#160;';
			$h['snap']['sortable']   = false;
			$h['clone']['title']     = '&#160;';
			$h['clone']['sortable']  = false;
			$h['resize']['title']    = '&#160;';
			$h['resize']['sortable'] = false;

			$table = $this->response->html->tablebuilder('lvols', $this->response->get_array($this->actions_name, 'volgroup'));
			$table->sort                = 'name';
			$table->limit               = 20;
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
