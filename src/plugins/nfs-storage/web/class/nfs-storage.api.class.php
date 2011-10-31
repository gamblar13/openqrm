<?php
/**
 * NFS-Storage Controller
 *
 * This file is part of openQRM.
 * 
 * openQRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 * 
 * openQRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package openqrm
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 * @license GNU General Public License, see <http://www.gnu.org/licenses/>
 * @version 1.0
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
