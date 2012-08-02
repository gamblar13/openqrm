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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;



function openqrm_opsi_resource_virtual_command($cmd, $resource_fields) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;

	$resource_id = $resource_fields["resource_id"];
	$resource = new resource();
	$resource->get_instance_by_id($resource_id);
	$resource_ip = $resource->ip;
	$event->log("openqrm_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-opsi-resource-virtual-command-hook.php", "Handling $cmd command of resource $resource->id on windows host", "", "", 0, 0, 0);

	switch($cmd) {
		case "reboot":
			$event->log("openqrm_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-opsi-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = "dbclient -K 10 -y -i $OPENQRM_SERVER_BASE_DIR/openqrm/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /r /f /t 2'";
			$openqrm_server->send_command($virtual_command);
			sleep(2);
			$openqrm_server->send_command($virtual_command);
			break;
		case "halt":
			$event->log("openqrm_opsi_resource_virtual_command", $_SERVER['REQUEST_TIME'], 5, "openqrm-opsi-resource-virtual-command-hook.php", "Handling $cmd command", "", "", 0, 0, 0);
			$virtual_command = "dbclient -K 10 -y -i $OPENQRM_SERVER_BASE_DIR/openqrm/etc/dropbear/dropbear_rsa_host_key -p 22 root@$resource_ip 'shutdown.exe /s /f /t 2'";
			$openqrm_server->send_command($virtual_command);
			sleep(2);
			$openqrm_server->send_command($virtual_command);
			break;

	}
}



?>
