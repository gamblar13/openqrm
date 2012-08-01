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


class cloud_ui_images
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
	    $this->docrootdir = $_SERVER["DOCUMENT_ROOT"];
	    // include classes and prepare ojects
	    require_once $this->rootdir."/plugins/cloud/class/cloudimage.class.php";
	    $this->cloudimage	= new cloudimage();
	    require_once $this->rootdir."/plugins/cloud/class/cloudprivateimage.class.php";
	    $this->cloudprivateimage	= new cloudprivateimage();
	    require_once $this->rootdir."/class/appliance.class.php";
	    $this->appliance	= new appliance();
		$this->image		= new image();
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
		$table = $this->select();
		$template = $this->response->html->template("./tpl/cloud-ui.images.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($table, 'table');
		$template->add($this->lang['cloud_ui_images_title'], 'title');
		$template->add($this->lang['cloud_ui_requests_title'], 'cr_details_title');
		return $template;
	}

	//--------------------------------------------
	/**
	 * select
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {

		$head['image_icon']['title'] =' ';
		$head['image_icon']['sortable'] = false;
		$head['co_id']['title'] = $this->lang['cloud_ui_request_id'];
		$head['image_name']['title'] = $this->lang['cloud_ui_name'];
		$head['image_name']['sortable'] = false;
		$head['image_isactive']['title'] = $this->lang['cloud_ui_request_appliance_state'];
		$head['image_isactive']['sortable'] = false;
		$head['private_image_clone_on_deploy']['title'] = $this->lang['cloud_ui_image_clone_one_deploy'];
		$head['private_image_clone_on_deploy']['sortable'] = false;
		$head['co_comment']['title'] = $this->lang['cloud_ui_request_appliance_comment'];
		$head['image_action']['title'] = ' ';
		$head['image_action']['sortable'] = false;

	    $table = $this->response->html->tablebuilder( 'image_table', $this->response->get_array($this->actions_name, 'images'));
	    $table->css             = 'htmlobject_table';
	    $table->border          = 0;
		$table->limit           = 10;
	    $table->id              = 'cloud_images';
	    $table->head            = $head;
	    $table->sort            = 'co_id';
	    $table->sort_link       = false;
	    $table->autosort        = true;
	    $table->actions_name    = $this->actions_name;
	    $table->form_action     = $this->response->html->thisfile;

		$arBody = array();
		$private_image_array =  $this->cloudprivateimage->display_overview_per_user($this->clouduser->id, $table->order);
		$private_image_count = 0;
		foreach ($private_image_array as $index => $private_image_db) {
			$pco_id = $private_image_db["co_id"];
			$pcomment = $private_image_db["co_comment"];
			if (!strlen($pcomment)) {
				$pcomment = '-';
			}
			$this->cloudprivateimage->get_instance_by_id($pco_id);
			// get the image name
			$this->image->get_instance_by_id($this->cloudprivateimage->image_id);
			// set the active icon
			$isactive_icon = "/cloud-portal/user/img/pause.png";
			if ($this->image->isactive == 1) {
				$isactive_icon = "/cloud-portal/user/img/unpause.png";
			}
			$image_isactive_icon = "<img src=".$isactive_icon." width='24' height='24' alt='State'>";
			$clone_on_deploy_status = '';
			if ($this->cloudprivateimage->clone_on_deploy == 0) {
				$clone_on_deploy_status = $this->lang['cloud_ui_off'];
			} else if ($this->cloudprivateimage->clone_on_deploy == 1) {
				$clone_on_deploy_status = $this->lang['cloud_ui_on'];
			}
			// image actions
			$image_action = '';
			// edit
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_edit'];
			$a->label   = $this->lang['cloud_ui_edit'];
			$a->handler = "";
			$a->css     = 'edit';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=image_edit&'.$this->identifier_name.'='.$this->cloudprivateimage->id;
			$image_action .= '<div id="appliance_cloud_action">';
			$image_action .= $a->get_string();
			$image_action .= '</div>';

			// remove
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_remove'];
			$a->label   = $this->lang['cloud_ui_remove'];
			$a->handler = "";
			$a->css     = 'remove';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=image_remove&'.$this->identifier_name.'='.$this->cloudprivateimage->id;
			$image_action .= '<div id="appliance_cloud_action">';
			$image_action .= $a->get_string();
			$image_action .= '</div>';


			$arBody[] = array(
				'image_icon' => '<img src="/cloud-portal/user/img/private.png">',
				'co_id' => $pco_id,
				'image_name' => $this->image->name,
				'image_isactive' => $image_isactive_icon,
				'private_image_clone_on_deploy' => $clone_on_deploy_status,
				'co_comment' => $pcomment,

				'image_action' => $image_action,

			);
			$private_image_count++;
		}

		$table->body = $arBody;
		$table->max = $private_image_count;

	    return $table;
	}


}

?>


