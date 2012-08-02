<?php
/**
 * local-storage Remove Volume(s)
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class local_storage_template_drain
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "local_storage_msg";
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'local_storage_identifier';
/**
* openqrm rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'local_storage_tab';
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
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;
		$this->volgroup = $this->response->html->request()->get('volgroup');
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
		$response = $this->drain();
		if(isset($response->msg)) {
			$this->response->params['reload'] = 'false';
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/local-storage-template-drain.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->prefix_tab, 'prefix_tab');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Drain
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function drain() {
		$response = $this->get_response();
		$lvols  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $lvols !== '' ) {

			$submit = $form->get_elements('submit');
			$submit->handler = 'onclick="wait();"';
			$form->add($submit, 'submit');

			$submit = $form->get_elements('cancel');
			$submit->handler = 'onclick="cancel();"';
			$form->add($submit, 'cancel');

			$i = 0;
			foreach($lvols as $ex) {
				$d['param_f'.$i]['label']                       = $ex.'.img';
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $ex;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$name       = $this->openqrm->admin()->name;
				$pass       = $this->openqrm->admin()->password;
				$storage    = new storage();
				$resource   = new resource();
				$deployment = new deployment();

				$storage->get_instance_by_id($this->response->html->request()->get('storage_id'));
				$resource->get_instance_by_id($storage->resource_id);
				$deployment->get_instance_by_id($storage->type);

				$errors     = array();
				$message    = array();
				foreach($lvols as $key => $lvol) {
					// check if an appliance is still using the volume as an image
					$image = new image();
					$image->get_instance_by_name($lvol);

					// check if it is still in use
					$appliance = new appliance();
					$appliances_using_resource = $appliance->get_ids_per_image($image->id);
					if (count($appliances_using_resource) > 0) {
						$appliances_using_resource_str = implode(",", $appliances_using_resource[0]);
						$errors[] = sprintf($this->lang['msg_vm_image_still_in_use'], $lvol, $image->id, $appliances_using_resource_str);
					} else {
						$command  = $this->openqrm->get('basedir').'/plugins/local-storage/bin/openqrm-local-storage drain';
						$command .= ' -n '.$lvol;
						$command .= ' -v '.$this->volgroup;
						$command .= ' -t '.$deployment->type;
						$command .= ' -u '.$name.' -p '.$pass;

						$file = $this->openqrm->get('basedir').'/plugins/local-storage/web/storage/'.$resource->id.'.'.$this->volgroup.'.lv.stat';
						if($this->file->exists($file)) {
							$this->file->remove($file);
						}
						$resource->send_command($resource->ip, $command);
						while (!$this->file->exists($file)) {
							usleep(10000); // sleep 10ms to unload the CPU
							clearstatcache();
						}

						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['msg_removed'], $lvol);
					}

				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
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
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'drain');
		$response->form = $form;
		return $response;
	}

}
?>
