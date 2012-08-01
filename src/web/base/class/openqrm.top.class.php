<?php
/**
 * Openqrm Top
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openqrm_top
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user) {
		$this->response = $response;
		$this->file     = $file;
		$this->user     = $user;
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
		$t = $this->response->html->template($this->tpldir.'/index_top.tpl.php');
		$t->add("time()", 'timestamp');
		$t->add($this->user->name, 'username');
		$t->add($this->user->lang, 'userlang');
		switch ($this->user->lang) {
			case 'de';
				$t->add("selected='selected'", 'selected_lang_de');
				$t->add("", 'selected_lang_en');
				break;
			case 'en';
				$t->add("", 'selected_lang_de');
				$t->add("selected='selected'", 'selected_lang_en');
				break;
		}

		return $t;
	}

}
