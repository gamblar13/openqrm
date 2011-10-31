
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

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


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";


function drbdmc_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/drbdmc/img/plugin.png\"> Drbdmc plugin</h1>";
	$disp .= "<br>";
	$disp .= "The drbdmc-plugin integrates the DRBD-MC (DRBD Management Console) into openQRM.";
	$disp .= " It provdes a convinient way to configure Highavailability (pacemaker/heartbeat/corosync)";
	$disp .= "  for appliactions on the appliances via an intuitive GUI.";
	$disp .= "<br>";
	$disp .= "<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => drbdmc_about());
echo htmlobject_tabmenu($output);

?>


