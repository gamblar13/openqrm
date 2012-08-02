<?php
/**
 * Nagios3 Services
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class nagios3_services
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
				$response      = $this->select();

				$href          = $response->html->a();
				$href->href    = $response->get_url($this->actions_name, 'add' );
				$href->label   = $this->lang['action_add'];
				$href->css     = 'add';
				$href->handler = 'onclick="wait();"';

				$data['add']         = $href;
				$data['label']       = $this->lang['label'];
				$data['table']       = $response->table;
				$data['canceled']    = $this->lang['canceled'];
				$data['please_wait'] = $this->lang['please_wait'];
				$data['prefix_tab']  = $this->prefix_tab;
				$data['baseurl']     = $this->openqrm->get('baseurl');
				$data['thisfile']    = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.services-select.tpl.php');
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
				$data['label'] = sprintf($this->lang['label_edit'], $response->name);
				$data['baseurl'] = $this->openqrm->get('baseurl');
				$data['thisfile']    = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.services-edit.tpl.php');
				$t->add($response->form);
				$t->add($data);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// ADD
			case 'add':
				$response = $this->add();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['canceled']        = $this->lang['canceled'];
				$data['please_wait']     = $this->lang['please_wait'];
				$data['prefix_tab']      = $this->prefix_tab;
				$data['label']           = $this->lang['label_add'];
				$data['baseurl']         = $this->openqrm->get('baseurl');
				$data['or_manually_add'] = $this->lang['or_manually_add'];
				$data['thisfile']        = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.services-add.tpl.php');
				$t->add($response->form);
				$t->add($data);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// DELETE
			case $this->lang['action_delete']:
			case 'delete':
				$response = $this->delete();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['label']       = $this->lang['label_delete'];
				$data['canceled']    = $this->lang['canceled'];
				$data['please_wait'] = $this->lang['please_wait'];
				$data['prefix_tab']  = $this->prefix_tab;
				$data['baseurl']     = $this->openqrm->get('baseurl');
				$data['thisfile']    = $response->html->thisfile;
				$t = $response->html->template($this->tpldir.'/nagios3.services-delete.tpl.php');
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

		$nagios3 = $this->nagios3s;

		$table = $this->response->html->tablebuilder('n3s', $this->response->get_array($this->actions_name, 'select'));
		$table->offset = 0;
		$table->sort = 'nagios3_service_id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->max   = $nagios3->get_count();

		$table->init();

		$h['nagios3_service_id']['title']    = $this->lang['id'];
		$h['nagios3_service_id']['sortable'] = true;
		$h['nagios3_service_name']['title']    = $this->lang['name'];
		$h['nagios3_service_name']['sortable'] = true;
		$h['nagios3_service_port']['title']    = $this->lang['port'];
		$h['nagios3_service_port']['sortable'] = true;
		$h['nagios3_service_type']['title']    = $this->lang['type'];
		$h['nagios3_service_type']['sortable'] = true;
		$h['nagios3_service_description']['title']    = $this->lang['description'];
		$h['nagios3_service_description']['sortable'] = false;
		$h['edit']['title']    = '&#160;';
		$h['edit']['sortable'] = false;


		$result = $nagios3->display_overview($table->offset, $table->limit, $table->sort, $table->order);
		$b = array();
		foreach($result as $k => $v) {
			$a          = $response->html->a();
			$a->href    = $response->get_url($this->actions_name, 'edit' ).'&id='.$v['nagios3_service_id'];
			$a->label   = $this->lang['action_edit'];
			$a->title   = $this->lang['action_edit'];
			$a->css     = 'edit';				
			$a->handler = 'onclick="wait();"';

			$tmp = array();
			$tmp['nagios3_service_id'] = $v['nagios3_service_id'];
			$tmp['nagios3_service_name'] = $v['nagios3_service_name'];				
			$tmp['nagios3_service_port'] = $v['nagios3_service_port'];
			$tmp['nagios3_service_type'] = $v['nagios3_service_type'];
			$tmp['nagios3_service_description'] = $v['nagios3_service_description'];
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
		$table->identifier          = 'nagios3_service_id';
		$table->identifier_name     = $this->identifier_name;
		$table->actions             = array($this->lang['action_delete']);
		$table->actions_name        = $this->actions_name;
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
		$response = $this->get_response('edit');
		$form     = $response->form;
		$id       = $response->html->request()->get('id');
		if($id !== '') {
			$nagios3 = $this->nagios3s;
			$nagios3->get_instance_by_id($id);
			$d['manual_description']['label']                         = $this->lang['manual_description'];
			$d['manual_description']['object']['type']                = 'htmlobject_input';
			$d['manual_description']['object']['attrib']['type']      = 'text';
			$d['manual_description']['object']['attrib']['maxlength'] = 255;
			$d['manual_description']['object']['attrib']['name']      = 'manual_description';
			if(isset($nagios3->description)) {
				$d['manual_description']['object']['attrib']['value'] = $nagios3->description;
			}
			if(isset($nagios3->name)) {
				$response->name = $nagios3->name;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				if($form->get_request('manual_description')) {
					$fields['nagios3_service_description'] = $form->get_request('manual_description');
				}
				$error = $nagios3->update($id, $fields);
				$response->msg = sprintf($this->lang['msg_updated'], $nagios3->name);
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * ADD
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function add() {

		$response = $this->get_response('add');
		$form = $response->form;
		if(	$response->submit() && !$form->get_request('auto') ) {
			if(!$form->get_request('manual_port')) {
				$form->set_error('manual_port', sprintf($response->html->lang['form']['error_required'], $this->lang['manual_port']));
			}
			if(!$form->get_request('manual_service')) {
				$form->set_error('manual_service', sprintf($response->html->lang['form']['error_required'], $this->lang['manual_service']));
			}
			if(!$form->get_request('manual_description')) {
				$form->set_error('manual_description', sprintf($response->html->lang['form']['error_required'], $this->lang['manual_description']));
			}
		}
		if(!$form->get_errors() && $response->submit()) {
			// ignore auto if manual values are set
			if($form->get_request('manual_port') && $form->get_request('manual_service')) {
				$fields['nagios3_service_port'] = $form->get_request('manual_port');
				$fields['nagios3_service_name'] = $form->get_request('manual_service');
				if($form->get_request('manual_type')) {
					$fields['nagios3_service_type'] = $form->get_request('manual_type');
				} else {
					$fields['nagios3_service_type'] = 'tcp';
				}
				if($form->get_request('manual_description')) {
					$fields['nagios3_service_description'] = $form->get_request('manual_description');
				}
			}
			else if($form->get_request('auto')) {
				$v = explode('@',$form->get_request('auto')); 
				$fields['nagios3_service_port'] = $v[0];
				$fields['nagios3_service_name'] = $v[1];
				$fields['nagios3_service_type'] = $v[2];
				$fields['nagios3_service_description'] = $v[3];
			}		
			// check port in use			
			$nagios3 = new nagios3_service();
			$result = $nagios3->get_instance_by_port($fields['nagios3_service_port']);
			if($result->port !== '') {
				$response->error = sprintf($this->lang['error_port_in_use'], $fields['nagios3_service_port']);
			}
			if(!isset($response->error)) {
				$fields['nagios3_service_id'] = openqrm_db_get_free_id('nagios3_service_id', $nagios3->_db_table);
				$error = $nagios3->add($fields);
				if(!isset($error)) {
					$response->msg = sprintf($this->lang['msg_added'], $fields['nagios3_service_name']);
 				}
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function delete() {
		$response = $this->get_response('delete');
		$ids      = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $ids !== '' ) {
			$i = 0;
			foreach($ids as $id) {
				$nagios3 = $this->nagios3s;
				$nagios3->get_instance_by_id($id);
				$d['param_f'.$i]['label']                       = $nagios3->name;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {

				$host = $this->nagios3h->display_overview(0, 100000, 'nagios3_host_id', 'ASC');
				$tmp  = '';
				foreach($host as $v) {
					$tmp .= $v['nagios3_appliance_services'].',';
				}
				$used = explode(',', $tmp);
				$used = array_unique($used);
				$errors  = array();
				$message = array();
				$nagios3 = $this->nagios3s;
				foreach($ids as $key => $id) {
					if(!in_array($id, $used)) {
						$nagios3->get_instance_by_id($id);
						$error = $nagios3->remove($id);
						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['msg_deleted'], $nagios3->name);
					} 
					else {
						$errors[] = sprintf($this->lang['error_in_use'], $nagios3->get_instance_by_id($id)->name);
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
			else if($form->get_errors()) {
				$response->error = join('<br>', $form->get_errors());
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
	 * @param enum $mode [select|insert|edit|account|delete]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode) {
		$response = $this->response;
		if($mode === 'edit') {
			$id = $response->html->request()->get('id');
			if($id !== '') {
				$response->add('id', $id);
			}
		}
		$form = $response->get_form($this->actions_name, $mode);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		if($mode === 'add') {

			$content = $this->file->get_contents("/etc/services");
			$lines = explode("\n", $content);
			$select[] = array('', '');
			foreach($lines as $line) {
				if (strstr($line, "/tcp")) {
					$line = trim($line);
					$service_start = strpos($line, "/tcp");
					$service_description_start = strpos($line, "#");
					if ($service_description_start > 0) {
						$service_description = substr($line, $service_description_start+2);
						// remove description
						$line = substr($line, 0, $service_description_start);
					} else {
						$service_description = "No description available";
					}
					// find /
					$first_slash = strpos($line, '/');
					$line = substr($line, 0, $first_slash);
					list($service_name, $service_port) = sscanf($line, "%s %d");
					$select[] = array($service_port.'@'.$service_name.'@tcp@'.$service_description, 'Port:'.$service_port.' '.$service_name);
				}
			}
			$d['select']['label']                        = $this->lang['select_service'];
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['name']     = 'auto';
			$d['select']['object']['attrib']['index']    = array(0,1);
			$d['select']['object']['attrib']['options']  = $select;

			$d['manual_port']['label']                         = $this->lang['manual_port'];
			$d['manual_port']['validate']['regex']             = '/^[0-9]+$/i';
			$d['manual_port']['validate']['errormsg']          = $this->lang['error_manual_port'];
			$d['manual_port']['object']['type']                = 'htmlobject_input';
			$d['manual_port']['object']['attrib']['type']      = 'text';
			$d['manual_port']['object']['attrib']['maxlength'] = 5;
			$d['manual_port']['object']['attrib']['name']      = 'manual_port';

			$type[] = array('tcp', 'tcp');
			$d['manual_type']['label']                       = $this->lang['manual_type'];
			$d['manual_type']['object']['type']              = 'htmlobject_select';
			$d['manual_type']['object']['attrib']['index']   = array(0,1);
			$d['manual_type']['object']['attrib']['options'] = $type;
			$d['manual_type']['object']['attrib']['name']    = 'manual_type';

			$d['manual_service']['label']                         = $this->lang['manual_service'];
			$d['manual_service']['object']['type']                = 'htmlobject_input';
			$d['manual_service']['object']['attrib']['type']      = 'text';
			$d['manual_service']['object']['attrib']['maxlength'] = 50;
			$d['manual_service']['object']['attrib']['name']      = 'manual_service';

			$d['manual_description']['label']                         = $this->lang['manual_description'];
			$d['manual_description']['object']['type']                = 'htmlobject_input';
			$d['manual_description']['object']['attrib']['type']      = 'text';
			$d['manual_description']['object']['attrib']['maxlength'] = 255;
			$d['manual_description']['object']['attrib']['name']      = 'manual_description';

			$form->add($d);
		}

		$response->form = $form;
		return $response;
	}


}
?>
