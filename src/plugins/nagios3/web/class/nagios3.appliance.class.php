<?php
/**
 * Nagios3 Appliance
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class nagios3_appliance
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'nagios3_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "nagios3_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'nagios3_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'nagios3_identifier';
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
		$this->tpldir   = $this->rootdir.'/plugins/nagios3/tpl';

		require_once($this->rootdir.'/plugins/nagios3/class/nagios3_host.class.php');
		require_once($this->rootdir.'/plugins/nagios3/class/nagios3_service.class.php');
		$this->nagios3h = new nagios3_host();
		$this->nagios3s = new nagios3_service();
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
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = 'select';
		}

		switch( $this->action ) {
			// SELECT
			case '':
			default:
			case 'select':
				$response            = $this->select();
				$data['label']       = $this->lang['label'];
				$data['table']       = $response->table;
				$data['canceled']    = $this->lang['canceled'];
				$data['please_wait'] = $this->lang['please_wait'];
				$data['prefix_tab']  = $this->prefix_tab;
				$data['baseurl']     = $this->openqrm->get('baseurl');
				$data['thisfile']    = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.appliance-select.tpl.php');
				$t->add($data);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// EDIT
			case 'edit':
				$response = $this->edit();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['canceled'] = $this->lang['canceled'];
				$data['please_wait'] = $this->lang['please_wait'];
				$data['prefix_tab'] = $this->prefix_tab;
				$data['label'] = sprintf($this->lang['label_edit'], $response->appliance->name);
				$data['baseurl'] = $this->openqrm->get('baseurl');
				$data['thisfile']    = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.appliance-edit.tpl.php');
				$t->add($response->form);
				$t->add($data);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
		}
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
		$response = $this->get_response('select');

		$nagios3h = $this->nagios3h;
		$nagios3s = $this->nagios3s;

		$appliance = new appliance();

		$table = $this->response->html->tablebuilder('n3a', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->sort = 'appliance_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max   = $appliance->get_count();

		$table->init();

		$h['appliance_id']['title']    = $this->lang['id'];
		$h['appliance_id']['sortable'] = true;
		$h['appliance_id']['hidden']   = true;

		$h['appliance_name']['title']    = $this->lang['name'];
		$h['appliance_name']['sortable'] = true;
		$h['appliance_name']['hidden']   = true;

		$h['appliance_resources']['title']    = $this->lang['resource'];
		$h['appliance_resources']['sortable'] = true;
		$h['appliance_resources']['hidden']   = true;

		$h['appliance']['title']    = $this->lang['appliance'];
		$h['appliance']['sortable'] = false;

		$h['services']['title']    = $this->lang['services'];
		$h['services']['sortable'] = false;

		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;
			


		$result = $appliance->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$b = array();
		foreach($result as $k => $v) {

			$a          = $response->html->a();
			$a->href    = $response->get_url($this->actions_name, 'edit' ).'&appliance_id='.$v['appliance_id'];
			$a->label   = $this->lang['action_edit'];
			$a->title   = $this->lang['action_edit'];
			$a->css     = 'edit';				
			$a->handler = 'onclick="wait();"';

			$appliance = $appliance->get_instance_by_id($v['appliance_id']);
			$resource = new resource();
			$resource = $resource->get_instance_by_id($appliance->resources);

			$host = new nagios3_host();
			$host = $host->get_instance_by_appliance_id($appliance->id);

			$services = array();
			if($host->appliance_services !== '' && $host->appliance_services !== 'false') {
				$s = explode(',', $host->appliance_services);
				foreach($s as $id) {
					$nagios3s = $nagios3s->get_instance_by_id($id);
					$services[] = $nagios3s->name;
				}
			}


			$tmp = array();
			$tmp['appliance_id'] = $appliance->id;
			$tmp['appliance_name'] = $appliance->name;
			$tmp['appliance_resources'] = $appliance->resources;
			$tmp['appliance']  = '<b>'.$this->lang['id'].':</b> '.$appliance->id.'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['name'].':</b> '.$appliance->name.'<br>';
			$tmp['appliance'] .= '<b>'.$this->lang['resource'].':</b> '.$resource->id.' / '.$resource->ip.'<br>';
			$tmp['services'] = implode(', ', $services);
			$tmp['edit'] = $a->get_string();
			$b[] = $tmp;
		}

		$table->css                 = 'htmlobject_table';
		$table->border              = 0;
		$table->id                  = 'Tabelle';
		$table->form_action	        = $this->response->html->thisfile;
		$table->head                = $h;
		$table->body                = $b;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = false;
		$table->limit_select        = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 40, "text" => 40),
				array("value" => 50, "text" => 50),
				);

		$response->table = $table;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {
		$response     = $this->get_response('edit');
		$form         = $response->form;
		$appliance_id = $response->html->request()->get('appliance_id');
		if($appliance_id !== '') {
			if(!$form->get_errors() && $response->submit()) {
				$host = new nagios3_host();
				$host = $host->get_instance_by_appliance_id($appliance_id);
				$old = explode(',', $host->appliance_services);
				$new = $form->get_request('services');
				if(isset($new[0]) && $new[0] !== '{empty}' ) {
					$fields['nagios3_appliance_services'] = implode(',', $new);
				} else {
					$fields['nagios3_appliance_services'] = 'false';
				}
				// if nagios3_host id is set -> update or remove
				if($host->id !== '') {
					// if no services -> remove
					if($fields['nagios3_appliance_services'] === 'false') {
						$error = $this->nagios3h->remove($host->id);
					} else {
						$error = $this->nagios3h->update($host->id, $fields);
					}
				} 
				else if($fields['nagios3_appliance_services'] !== 'false') {
					$fields['nagios3_host_id'] = openqrm_db_get_free_id('nagios3_host_id', $this->nagios3h->_db_table);
					$fields['nagios3_appliance_id'] = $appliance_id;
					$error = $this->nagios3h->add($fields);
				}
				// remove new ports from old
				foreach($new as $id) {
					if($id !== '{empty}') {
						if(in_array($id, $old)) {
							$key = array_search($id, $old);
							unset($old[$key]);
						}
					}
				}
				// handle ports to set
				$set = array();
				foreach($new as $id) {
					if($id !== '{empty}') {
						$set[] = $this->nagios3s->get_instance_by_id($id)->port;
					}
				}
				// handle ports to unset
				$unset = array();
				foreach($old as $id) {
					if($id !== 'false') {
						$unset[] = $this->nagios3s->get_instance_by_id($id)->port;
					}
				}
				// autoselect? active?
				if($response->appliance->state == "active" || $response->appliance->resources == 0) {
					// unset
					if(count($unset) >= 1) {
						$res  = new resource();
						$cmd  = $this->openqrm->get('basedir').'/plugins/nagios3/bin/openqrm-nagios-manager remove_service';
						$cmd .= ' -n '. $response->appliance->name;
						$cmd .= ' -i '. $res->get_instance_by_id($response->appliance->resources)->ip;
						$cmd .= ' -p '. implode(',', $unset);
						$oqs = new openqrm_server();
						$oqs->send_command($cmd);
					}
					// set
					if(count($set) >= 1) {
						$res  = new resource();
						$cmd  = $this->openqrm->get('basedir').'/plugins/nagios3/bin/openqrm-nagios-manager add';
						$cmd .= ' -n '. $response->appliance->name;
						$cmd .= ' -i '. $res->get_instance_by_id($response->appliance->resources)->ip;
						$cmd .= ' -p '. implode(',', $set);
						$oqs = new openqrm_server();
						$oqs->send_command($cmd);
					}
				}
				$response->msg = sprintf($this->lang['msg_updated'], $response->appliance->name);
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [select|edit]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode) {
		$id = $this->response->html->request()->get('appliance_id');
		if($id !== '') {
			$this->response->add('appliance_id', $id);
		}
		$response = $this->response;
		$form = $response->get_form($this->actions_name, $mode);

		if($mode === 'edit') {
			$selected = $this->nagios3h->get_instance_by_appliance_id($id);

			// get appliance
			$appliance = new appliance();
			$appliance = $appliance->get_instance_by_id($id);
			$response->appliance = $appliance;

			// get selected
			$selected = $selected->appliance_services;
			if($selected !== 'false') {
				$selected = explode(',', $selected);
			} else {
				$selected = array();
			}
			$select = array('{empty}', '&#160;');
			$content = $this->nagios3s->display_overview(0, 100000, 'nagios3_service_name', 'ASC');
			foreach($content as $v) {
				$o = $response->html->option();
				$o->value = $v['nagios3_service_id'];
				$o->label = $v['nagios3_service_name'];
				$o->title = $v['nagios3_service_description'];
				$select[] = $o;
			}
			$d['select']['label']                        = $this->lang['select_services'];
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['name']     = 'services[]';
			$d['select']['object']['attrib']['index']    = array(0,1);
			$d['select']['object']['attrib']['multiple'] = true;
			$d['select']['object']['attrib']['css']      = 'service_select';
			$d['select']['object']['attrib']['options']  = $select;
			$d['select']['object']['attrib']['selected']  = $selected;

			$form->add($d);
		}

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;
	}

}
?>
