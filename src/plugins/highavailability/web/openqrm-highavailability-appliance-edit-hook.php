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
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
global $OPENQRM_SERVER_IP_ADDRESS;
$event = new event();
global $event;



function get_highavailability_appliance_edit($appliance_id) {
	global $event;
	global $OPENQRM_SERVER_BASE_DIR;
	global $OPENQRM_SERVER_IP_ADDRESS;
	global $OPENQRM_EXEC_PORT;

	$p_appliance = new appliance();
	$p_appliance->get_instance_by_id($appliance_id);
	
	$html = new htmlobject($OPENQRM_SERVER_BASE_DIR.'/openqrm/web/base/class/htmlobjects');
	$a = $html->a();
	$a->label = '<img title="Highavailability" alt="Highavailability" height="24" width="24" src="/openqrm/base/plugins/highavailability/img/plugin.png" border="0">';
	$a->href = $html->thisfile.'?plugin=highavailability&highavailability_action=edit&appliance_id='.$appliance_id;

	if ($p_appliance->resources == 0 || $p_appliance->highavailable != '1') {
		$a = "";
	}
	return $a;
}


?>


