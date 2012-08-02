<?php
/**
 * NFS-Storage Controller
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class nfs_storage_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $nfs_storage_controller
	 */
	//--------------------------------------------
	function __construct($nfs_storage_controller) {
		$this->controller = $nfs_storage_controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->html;
		$this->response   = $this->html->response();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'monitor':
				$this->monitor();
			break;
		}
	}



	function monitor() {
		$filename     = '/etc/exports';
		$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
		$currentmodif = filemtime($filename);
		while ($currentmodif <= $lastmodif) // check if the data file has been modified
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		  $currentmodif = filemtime($filename);
		}
		echo 'changed';

	}






}
?>
