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

	Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/

// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
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

// novnc defines
$novnc_web_port_range_start = 6000;
$novnc_proxy_port_range_start = 6800;



function openqrm_novnc_remote_console($vncserver, $vncport, $vm_res_id, $vm_mac, $resource_name) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;
	global $novnc_web_port_range_start;
	global $novnc_proxy_port_range_start;

	// start the novnc proxy
	$novnc_start_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/novnc/bin/openqrm-novnc-manager remoteconsole -n ".$resource_name." -d ".$vm_res_id." -m ".$vm_mac." -i ".$vncserver." -v ".$vncport;
	$output = shell_exec($novnc_start_command);

	// calcualte the web + proxy port
	$novnc_web_port = $novnc_web_port_range_start + $vm_res_id;
	$novnc_proxy_port = $novnc_proxy_port_range_start + $vm_res_id;
	// open the console window
	$left=50+($vm_res_id*50);
	$top=100+($vm_res_id*50);
	// build the url to forward
	$redirect_url="http://".$OPENQRM_SERVER_IP_ADDRESS.":".$novnc_web_port."/vnc.html?host=".$OPENQRM_SERVER_IP_ADDRESS."&port=".$novnc_web_port;
	sleep(1);

?>
<script type="text/javascript">
function open_remote_console (url) {
	remote_console_window = window.open(url, "<?php echo $resource_name; ?>", "width=800,height=600,scrollbars=1,left=<?php echo $left; ?>,top=<?php echo $top; ?>");
	remote_console_window.focus();
}
open_remote_console("<?php echo $redirect_url; ?>");
</script>
<?php



}



// this functions implements the stop action for the vnc remote console
function openqrm_novnc_disable_remote_console($vncserver, $vncport, $vm_res_id, $vm_mac, $resource_name) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;
	global $openqrm_server;

	// stop the novnc proxy
	$novnc_stop_command = $OPENQRM_SERVER_BASE_DIR."/openqrm/plugins/novnc/bin/openqrm-novnc-manager disable-remoteconsole -n ".$resource_name." -d ".$vm_res_id." -m ".$vm_mac." -i ".$vncserver." -v ".$vncport;
	$output = shell_exec($novnc_stop_command);


}


?>


