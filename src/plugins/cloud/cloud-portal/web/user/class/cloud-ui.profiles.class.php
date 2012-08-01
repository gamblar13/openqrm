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


class cloud_ui_profiles
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
		// include classes and prepare ojects
		require_once $this->rootdir."/plugins/cloud/class/cloudprofile.class.php";
		$this->cloudprofile	= new cloudprofile();
		require_once $this->rootdir."/plugins/cloud/class/cloudicon.class.php";
		$this->cloudicon	= new cloudicon();
		$this->cloud_object_icon_size=48;
		
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
		$table = $this->profiles();
		$template = $this->response->html->template("./tpl/cloud-ui.profiles.tpl.php");
		$template->add($this->response->html->thisfile, "thisfile");
		$template->add($table, 'table');
		$template->add($this->lang['cloud_ui_title']." ".$this->lang['cloud_ui_profiles'], 'title');
		return $template;
	}

	//--------------------------------------------
	/**
	 * profiles
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function profiles() {
	    
	    $head['pr_icon']['title'] = ' ';
	    $head['pr_icon']['sortable'] = false;
	    $head['pr_id']['title'] = $this->lang['cloud_ui_request_id'];
		$head['pr_name']['title'] = $this->lang['cloud_ui_account_user_name'];
	    $head['pr_description']['title'] = $this->lang['cloud_ui_comment'];
	    $head['pr_description']['sortable'] = false;
	    $head['pr_action']['title'] = ' ';
	    $head['pr_action']['sortable'] = false;
		
	    $table = $this->response->html->tablebuilder( 'cloud_profile_table', $this->response->get_array($this->actions_name, 'profiles'));
	    
	    $ta = array();
	    $default_icon="../img/resource.png";

	    $cloudprofile_array = $this->cloudprofile->display_overview_per_user($this->clouduser->id, $table->order);
	    foreach ($cloudprofile_array as $index => $cloudprofile_db) {
		    $pr_id = $cloudprofile_db["pr_id"];
		    $pr_name = $cloudprofile_db["pr_name"];
		    $pr_description = $cloudprofile_db["pr_description"];
			if (!strlen($pr_description)) {
				$pr_description = '-';
			}
		    // check if custom icon exist, otherwise use the default icon
			$ci = new cloudicon();
		    $ci->get_instance_by_details($this->clouduser->id, 1, $pr_id);
		    if (strlen($ci->filename)) {
			    $cloud_icon = "/cloud-portal/user/custom-icons/" . $ci->filename;
		    } else {
			    $cloud_icon = $default_icon;
		    }
			$profile_action = '';
			// upload custom icon action
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_upload_icon'];
			$a->label   = $this->lang['cloud_ui_upload_icon'];
			$a->handler = "";
			$a->css     = 'edit';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=profile_upload&object_type=1&'.$this->identifier_name.'='.$pr_id;
			$profile_action .= '<div id="appliance_cloud_action">';
			$profile_action .= $a->get_string();
			$profile_action .= '</div>';


			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_profile_edit'];
			$a->label   = $this->lang['cloud_ui_profile_edit'];
			$a->handler = "";
			$a->css     = 'manage';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=profile_comment&object_type=1&'.$this->identifier_name.'='.$pr_id;
			$profile_action .= '<div id="appliance_cloud_action">';
			$profile_action .= $a->get_string();
			$profile_action .= '</div>';
			
			$a = $this->response->html->a();
			$a->title   = $this->lang['cloud_ui_profile_remove'];
			$a->label   = $this->lang['cloud_ui_profile_remove'];
			$a->handler = "";
			$a->css     = 'remove';
			$a->href    = '/cloud-portal/user/index.php?cloud_ui=profile_remove&object_type=1&'.$this->identifier_name.'='.$pr_id;
			$profile_action .= '<div id="appliance_cloud_action">';
			$profile_action .= $a->get_string();
			$profile_action .= '</div>';
			
			
		    $ta[] = array(
			    'pr_icon' => "<img width=\"".$this->cloud_object_icon_size."\" height=\"".$this->cloud_object_icon_size."\" src=\"".$cloud_icon."\">",
			    'pr_id' => $pr_id,
			    'pr_name' => $pr_name,
			    'pr_description' => $pr_description,
			    'pr_action' => $profile_action,
		    );
	    }
	    $table->id = 'cloud_profiles';
	    $table->css = 'htmlobject_table';
	    $table->border = 1;
	    $table->sort = 'pr_id';
		$table->limit = 10;
	    $table->cellspacing = 0;
	    $table->cellpadding = 3;
	    $table->form_action = $this->response->html->thisfile;
	    $table->head = $head;
	    $table->body = $ta;
	    $table->autosort = true;
	    $table->sort_link = false;
	    $table->max = $this->cloudprofile->get_count_per_user($this->clouduser->id);
	    $table->body = $ta;
	    return $table;
	}


}

?>


