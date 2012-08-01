<?php

/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class cloud_ui_profile_upload
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud-ui';

/**
* user
* @access public
* @var string
*/
var $user;
/**
* cloud-id
* @access public
* @var int
*/
var $cloud_id;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->response = $response;
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
		$this->clouddir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
	    require_once $this->rootdir."/plugins/cloud/class/cloudappliance.class.php";
	    $this->cloudappliance	= new cloudappliance();
	    require_once $this->rootdir."/class/appliance.class.php";
	    $this->appliance	= new appliance();
		require_once $this->rootdir."/plugins/cloud/class/cloudicon.class.php";
		$this->cloudicon = new cloudicon();
		require_once $this->rootdir."/class/file.handler.class.php";
		$this->file_handler = new file_handler();
		require_once $this->rootdir."/class/file.upload.class.php";
		$this->file_upload = new file_upload($this->file_handler);
		require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
		$this->cloudprofile	= new cloudprofile();
		require_once $this->rootdir."/plugins/cloud/class/cloudicon.class.php";
		$this->cloudicon	= new cloudicon();
		$this->upload_dir = $this->clouddir."/user/custom-icons/";
	}

	//--------------------------------------------
	/**
	 * Action New
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->profile_upload();
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			if ($this->object_type == 1) {
				$this->response->redirect($this->response->get_url($this->actions_name, 'profiles', $this->message_param, $response->msg));
			} else if ($this->object_type == 2) {
				echo '<script language="JavaScript" type="text/javascript">
					window.close();
				</script>';
				flush();
//				$this->response->redirect($this->response->get_url($this->actions_name, 'appliances', $this->message_param, $response->msg));
			}
		}

		$template = $this->response->html->template("./tpl/cloud-ui.profile-upload.tpl.php");
		// profile or appliance icon ?
		if ($this->object_type == 1) {
			$template->add($this->lang['cloud_ui_upload_icon']." - ".$this->lang['cloud_ui_profile']." ".$this->cloudprofile->name, 'title');
		} else if ($this->object_type == 2) {
			$this->cloudappliance->get_instance_by_id($this->response->html->request()->get($this->identifier_name));
			$this->appliance->get_instance_by_id($this->cloudappliance->appliance_id);
			$template->add($this->lang['cloud_ui_upload_icon']." - ".$this->lang['cloud_ui_request_hostname']." ".$this->appliance->name, 'title');
		}
		$template->add($this->lang['cloud_ui_upload_icon_description'], 'cloud_ui_upload_icon_description');
		$template->add($response->form->get_elements());
		$template->add($response->html->thisfile, "thisfile");
		$template->group_elements(array('param_' => 'form'));
		return $template;
	}

	//--------------------------------------------
	/**
	 * profile_upload
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function profile_upload() {

		$this->pr_id = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->pr_id);
		$this->cloudprofile->get_instance_by_id($this->pr_id);
		
		$this->object_type = $this->response->html->request()->get('object_type');
		$this->response->add('object_type', $this->object_type);

		$response = $this->get_response();
		$form     = $response->form;
		$user_error = false;
		if(!$form->get_errors()	&& $response->submit()) {
			$name = $_FILES["cloud_icon"]["name"];
			$short_name = basename($name);
			// check filename extension
			$fextension = substr($short_name, strlen($short_name)-3);
			switch ($fextension) {
				case 'jpg':
					break;
				case 'JPG':
					break;
				case 'png':
					break;
				case 'PNG':
					break;
				case 'gif':
					break;
				case 'GIF':
					break;
				case '':
					$response->error = '';
					$user_error = true;
					break;
				default:
					$response->error = 'Only jpg/png/gif files allowed for upload!';
					$user_error = true;
					break;
			}
			// generate unique filename
			$icon_token = md5(uniqid(rand(), true));
			$icon_filename = $icon_token.".".$fextension;
			if ($this->cloudprofile->cu_id != $this->clouduser->id) {
				// not request of the authuser
				exit(1);
			}
			if (!$user_error) {
			
				$error = $this->file_upload->upload('cloud_icon', $this->upload_dir, $icon_filename);
				if (!is_array($error)) {
					// upload success
					$this->cloudicon->get_instance_by_details($this->clouduser->id, $this->object_type, $this->pr_id);
					if (strlen($this->cloudicon->filename)) {
						// remove old file
						unlink($this->upload_dir.$this->cloudicon->filename);
						// update cloudicon object
						$cloud_icon_arr = array(
								'ic_filename' => $icon_filename,
						);
						$this->cloudicon->update($this->cloudicon->id, $cloud_icon_arr);
					} else {
						// add cloudicon object
						$cloud_icon_id  = openqrm_db_get_free_id('ic_id', $this->cloudicon->_db_table);
						$cloud_icon_arr = array(
								'ic_id' => $cloud_icon_id,
								'ic_cu_id' => $this->clouduser->id,
								'ic_type' => $this->object_type,
								'ic_object_id' => $this->pr_id,
								'ic_filename' => $icon_filename,
						);
						$this->cloudicon->add($cloud_icon_arr);
					}
					// success msg
					$response->msg = $this->lang['cloud_ui_profile_upload_successful'];
				} else {
					// upload went wrong
					$response->error = $error['msg'];
				}
			}
		}
		return $response;
	}



	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, "profile_upload");

		$d = array();
		
		$d['cloud_icon']['label']                     = $this->lang['cloud_ui_profile_icon'];
		$d['cloud_icon']['required']                  = false;
		$d['cloud_icon']['object']['type']            = 'htmlobject_input';
		$d['cloud_icon']['object']['attrib']['type']  = 'file';
		$d['cloud_icon']['object']['attrib']['id']    = "cloud_icon";
		$d['cloud_icon']['object']['attrib']['name']  = "cloud_icon";

		$form->add($d);
		$response->form = $form;
		return $response;
	}


}

?>


