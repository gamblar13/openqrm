<?php
/**
 * kvm-Storage Edit Storage
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class kvm_storage_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'kvm_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "kvm_storage_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'kvm_tab';
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
		$this->statfile   = $this->openqrm->get('basedir').'/plugins/kvm-storage/web/storage/'.$resource->id.'.vg.stat';
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
			$t = $this->response->html->template($this->tpldir.'/kvm-storage-edit.tpl.php');
			$t->add($this->response->html->thisfile, "thisfile");
			$t->add($data);
			$t->add($this->lang['lang_id'], 'lang_id');
			$t->add($this->lang['lang_name'], 'lang_name');
			$t->add($this->lang['lang_resource'], 'lang_resource');
			$t->add($this->lang['lang_deployment'], 'lang_deployment');
			$t->add($this->lang['lang_state'], 'lang_state');
			$t->add($this->openqrm->get('baseurl'), 'baseurl');
			$t->add($this->prefix_tab, 'prefix_tab');
			$t->add(sprintf($this->lang['label'], $data['name']), 'label');
			$t->add($this->lang['please_wait'], 'please_wait');
			return $t;
		} else {
			$msg = sprintf($this->lang['error_no_kvm'], $this->response->html->request()->get('storage_id'));
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
		if(strpos($this->deployment->type, 'kvm') !== false) {
			$resource_icon_default = "/img/resource.png";
			$storage_icon = "/plugins/kvm-storage/img/plugin.png";
			$state_icon = $this->openqrm->get('baseurl')."/img/".$this->resource->state.".png";
			if ($this->file->exists($this->openqrm->get('webdir').$storage_icon)) {
				$resource_icon_default = $storage_icon;
			}
			$resource_icon_default = $this->openqrm->get('baseurl').$resource_icon_default;

			$d['state'] = "<img src=$state_icon>";
			$d['icon'] = "<img width=24 height=24 src=$resource_icon_default>";
			$d['resource'] = $this->resource->id.' / '.$this->resource->ip;
			$d['deployment'] = $this->deployment->type;
			$d['name'] = $this->storage->name;
			$d['id'] = $this->storage->id;

			$body = array();
			$file = $this->statfile;
			if($this->file->exists($file)) {
				$lines = explode("\n", $this->file->get_contents($file));
				if(count($lines) >= 1) {
					foreach($lines as $line) {
						if($line !== '') {
							$line  = explode('@', $line);
							$name  = substr($line[0], strripos($line[0], '/'));

							//handle format send by df
							$line[5] = str_replace('MB', '.00', $line[5]);
							$line[6] = str_replace('MB', '.00', $line[6]);

							$vsize = number_format(substr($line[5], 0, strpos($line[5], '.')), 0, '', '.').' MB';
							$vfree = str_replace('m', '', $line[6]);
							if($vfree !== '0') {
								$vfree = number_format(substr($line[6], 0, strpos($line[6], '.')), 0, '', '.');
							}
							$a = $this->response->html->a();
							$a->title   = $this->lang['action_edit'];
							$a->label   = $this->lang['action_edit'];
							$a->handler = 'onclick="wait();"';
							$a->css     = 'edit';
							$a->href    = $this->response->get_url($this->actions_name, "volgroup").'&volgroup='.$name;

							if($d['deployment'] === 'kvm-lvm-deployment') {								
								$body[] = array(
									'icon' => $d['icon'],
									'name'   => $name,
									'pv' => $line[1],
									'lv' => $line[2],
									'sn' => $line[3],
									'attr' => $line[4],
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'edit' => $a->get_string(),
								);
							}
							if($d['deployment'] === 'kvm-bf-deployment') {
								$body[] = array(
									'icon' => $d['icon'],
									'name'   => $name,
									'vsize' => $vsize,
									'vfree' => $vfree.' MB',
									'edit' => $a->get_string(),
								);
							}
						}
					}
				}
			}

			if($d['deployment'] === 'kvm-lvm-deployment') {
				$h['icon']['title'] = '&#160;';
				$h['icon']['sortable'] = false;
				$h['name']['title'] = $this->lang['table_name'];
				$h['pv']['title'] = $this->lang['table_pv'];
				$h['lv']['title'] = $this->lang['table_lv'];
				$h['sn']['title'] = $this->lang['table_sn'];
				$h['attr']['title'] = $this->lang['table_attr'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;
			}
			if($d['deployment'] === 'kvm-bf-deployment') {
				$h['icon']['title'] = '&#160;';
				$h['icon']['sortable'] = false;
				$h['name']['title'] = $this->lang['table_name'];
				$h['vsize']['title'] = $this->lang['table_vsize'];
				$h['vfree']['title'] = $this->lang['table_vfree'];
				$h['edit']['title'] = '&#160;';
				$h['edit']['sortable'] = false;
			}

			$table = $this->response->html->tablebuilder('exports', $this->response->get_array($this->actions_name, 'edit'));
			$table->sort            = 'name';
			$table->limit           = 10;
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
			$table->form_action     = $this->response->html->thisfile;
			$table->head            = $h;
			$table->body            = $body;

			$d['table'] = $table->get_string();
			return $d;
		} else {
			return false;
		}
	}

}
?>
