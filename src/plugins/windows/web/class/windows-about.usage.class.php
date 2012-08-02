<?php
/**
 * windows-about Documentation
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class windows_about_usage
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'windows_about_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "windows_about_msg";
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
		$this->openqrm    = $openqrm;

		$this->basedir    = $this->openqrm->get('basedir');
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
		$t = $this->response->html->template($this->tpldir.'/windows-about-usage.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['setup_title'], 'setup_title');
		$t->add($this->lang['setup_preparations'], 'setup_preparations');
		$t->add($this->lang['setup_requirements1'], 'setup_requirements1');
		$t->add($this->lang['setup_requirements2'], 'setup_requirements2');
		$t->add($this->lang['setup_requirements3'], 'setup_requirements3');
		$t->add($this->lang['setup_instructions'], 'setup_instructions');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}


}
?>
