<?php
/*
  This file is part of openQRM.

	openQRM is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2
	as published by the Free Software Foundation.

	openQRM is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

	Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


// This class represents a localstorageresource object in openQRM

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/openqrm-database-functions.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/plugin.class.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/openqrm_server.class.php";

global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$event = new event();
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $event;


class localstorageresource {

var $id = '';
var $resource_id = '';


//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function localstorageresource() {
	$this->init();
}

//--------------------------------------------------
/**
* init storage environment
* @access public
*/
//--------------------------------------------------
function init() {
	global $OPENQRM_SERVER_BASE_DIR;
	$this->_event = new event();
	$this->_base_dir = $OPENQRM_SERVER_BASE_DIR;
}


// ---------------------------------------------------------------------------------
// methods to set a resource boot-sequence
// This is especially needed for KVM VMs since the boot-sequence "nc" does
// not use the local disk for boot if set by pxe. -> bug in kvm
// ---------------------------------------------------------------------------------

function set_boot($resource_id, $boot) {
	global $event;
	$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "localstorageresource.class.php", "Setting boot-sequence of resource ".$resource_id." to ".$boot.".", "", "", 0, 0, 0);
	$boot_sequence = "net";
	switch($boot) {
		case '0':
			// netboot
			$boot_sequence = "net";
			break;
		case '1':
			// local boot
			$boot_sequence = "local";
			break;
	}
	$local_storage_resource = new resource();
	$local_storage_resource->get_instance_by_id($resource_id);
	// is it a vm ?
	if ($local_storage_resource->vhostid == $resource_id) {
		return;
	}
	$local_storage_resource_virtualization = new virtualization();
	$local_storage_resource_virtualization->get_instance_by_id($local_storage_resource->vtype);
	switch($local_storage_resource_virtualization->type) {
		case 'kvm-vm':
			$local_storage_resource_vhost = new resource();
			$local_storage_resource_vhost->get_instance_by_id($local_storage_resource->vhostid);
			$local_storage_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm/bin/openqrm-kvm setboot -m ".$local_storage_resource->mac." -b ".$boot_sequence;
			$local_storage_resource_vhost->send_command($local_storage_resource_vhost->ip, $local_storage_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "localstorageresource.class.php", "Resource ".$resource_id." is a KVM VM on Host ".$local_storage_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
		case 'kvm-storage-vm':
			$local_storage_resource_vhost = new resource();
			$local_storage_resource_vhost->get_instance_by_id($local_storage_resource->vhostid);
			$local_storage_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm setboot -m ".$local_storage_resource->mac." -b ".$boot_sequence;
			$local_storage_resource_vhost->send_command($local_storage_resource_vhost->ip, $local_storage_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "localstorageresource.class.php", "Resource ".$resource_id." is a KVM-Storage VM on Host ".$local_storage_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
	}

}



// ---------------------------------------------------------------------------------

}

?>

