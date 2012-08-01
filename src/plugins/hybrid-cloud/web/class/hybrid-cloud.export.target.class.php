<?php
/**
 * hybrid_cloud_export Target
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class hybrid_cloud_export_target
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_export_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_export_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_export_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'hybrid_cloud_export_tab';
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
	function __construct($openqrm, $response, $controller) {
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->controller = $controller;
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
		$response = $this->select();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->controller->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-export-target.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		$t->add($this->lang['lang_name_generate'], 'lang_name_generate');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add(sprintf($this->lang['label_target'],  $response->image->name, $response->hc->account_name), 'label');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->controller->prefix_tab, 'prefix_tab');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $this->response->submit()) {

			$request = $form->get_request();
			$hc      = $response->hc;
			$image   = $response->image;

			$storage = new storage();
			$storage->get_instance_by_id($image->storageid);
			$resource = new resource();
			$resource->get_instance_by_id($storage->resource_id);

			$command  = $this->openqrm->get('basedir').'/plugins/hybrid-cloud/bin/openqrm-hybrid-cloud export_image';
			$command .= ' -i '.$hc->id;
			$command .= ' -n '.$hc->account_name;
			$command .= ' -c '.$hc->rc_config;
			$command .= ' -t '.$hc->account_type;
			$command .= ' -s '.$resource->ip.":".$image->rootdevice;
			$command .= ' -m '.$request['size'];
			$command .= ' -a '.$request['name'];
			$command .= ' -r '.$request['arch'];

			$server = new openqrm_server();
			$server->send_command($command);

			$response->msg = sprintf($this->lang['msg_exported'], $image->name, $hc->account_name );

		}
		return $response;
	}


	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'target');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$size[] = array('500', '500 MB');
		$size[] = array('1000', '1 GB');
		$size[] = array('2000', '2 GB');
		$size[] = array('3000', '3 GB');
		$size[] = array('4000', '4 GB');
		$size[] = array('5000', '5 GB');
		$size[] = array('10000', '10 GB');

		$d['size']['label']                        = $this->lang['form_size'];
		$d['size']['object']['type']               = 'htmlobject_select';
		$d['size']['object']['attrib']['name']     = 'size';
		$d['size']['object']['attrib']['index']    = array(0,1);
		$d['size']['object']['attrib']['options']  = $size;

		$arch[] = array('x86_64');
		$arch[] = array('i368');

		$d['arch']['label']                        = $this->lang['form_architecture'];
		$d['arch']['object']['type']               = 'htmlobject_select';
		$d['arch']['object']['attrib']['name']     = 'arch';
		$d['arch']['object']['attrib']['index']    = array(0,0);
		$d['arch']['object']['attrib']['options']  = $arch;

		$d['name']['label']                         = $this->lang['form_name'];
		$d['name']['required']                      = true;
		$d['name']['validate']['regex']             = '/^[a-z0-9]+$/i';
		$d['name']['validate']['errormsg']          = sprintf($this->lang['error_name'], 'a-z0-9');
		$d['name']['object']['type']                = 'htmlobject_input';
		$d['name']['object']['attrib']['name']      = 'name';
		$d['name']['object']['attrib']['id']        = 'name';
		$d['name']['object']['attrib']['type']      = 'text';
		$d['name']['object']['attrib']['maxlength'] = 50;
		$d['name']['object']['attrib']['minlength'] = 8;

		$form->add($d);
		$response->form = $form;

		require_once($this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->response->html->request()->get('hybrid_cloud_id'));
		$response->hc = $hc;

		$img = new image();
		$img->get_instance_by_id($this->response->html->request()->get('image_id'));
		$response->image = $img;

		return $response;
	}


}
?>
