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


// This class represents a cobblerresource object in openQRM

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


class cobblerresource {

var $id = '';
var $resource_id = '';


//--------------------------------------------------
/**
* Constructor
*/
//--------------------------------------------------
function cobblerresource() {
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
	$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "cobblerresource.class.php", "Setting boot-sequence of resource ".$resource_id." to ".$boot.".", "", "", 0, 0, 0);
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
	$cobbler_resource = new resource();
	$cobbler_resource->get_instance_by_id($resource_id);
	// is it a vm ?
	if ($cobbler_resource->vhostid == $resource_id) {
		return;
	}
	$cobbler_resource_virtualization = new virtualization();
	$cobbler_resource_virtualization->get_instance_by_id($cobbler_resource->vtype);
	switch($cobbler_resource_virtualization->type) {
		case 'kvm-vm':
			$cobbler_resource_vhost = new resource();
			$cobbler_resource_vhost->get_instance_by_id($cobbler_resource->vhostid);
			$cobbler_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm/bin/openqrm-kvm setboot -m ".$cobbler_resource->mac." -b ".$boot_sequence;
			$cobbler_resource_vhost->send_command($cobbler_resource_vhost->ip, $cobbler_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "cobblerresource.class.php", "Resource ".$resource_id." is a KVM VM on Host ".$cobbler_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
		case 'kvm-storage-vm':
			$cobbler_resource_vhost = new resource();
			$cobbler_resource_vhost->get_instance_by_id($cobbler_resource->vhostid);
			$cobbler_resource_set_boot_commmand = $this->_base_dir."/openqrm/plugins/kvm-storage/bin/openqrm-kvm-storage-vm setboot -m ".$cobbler_resource->mac." -b ".$boot_sequence;
			$cobbler_resource_vhost->send_command($cobbler_resource_vhost->ip, $cobbler_resource_set_boot_commmand);
			$event->log("set_boot", $_SERVER['REQUEST_TIME'], 5, "cobblerresource.class.php", "Resource ".$resource_id." is a KVM-Storage VM on Host ".$cobbler_resource_vhost->id.".", "", "", 0, 0, 0);
			break;
	}

}



// ---------------------------------------------------------------------------------

}

?>

