
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

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/


// error_reporting(E_ALL);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/include/htmlobject.inc.php";


function highavailability_about() {
	global $OPENQRM_SERVER_BASE_DIR;
	$disp = "<h1><img border=0 src=\"/openqrm/base/plugins/highavailability/img/plugin.png\"> High-Availability plugin</h1>";
	$disp = $disp."<br>";
	$disp = $disp."The highavailability-plugin automatically provides high-availability for the appliances managed by openQRM.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<b>How to use :</b>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."Simply use the HA-Manager to select the appliances which should be high-available.";
	$disp = $disp." In case of an error openQRM will try to find a new resource fitting to the appliance profile and re-start/re-deploy the appliance.";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	$disp = $disp."<br>";
	return $disp;
}


$output = array();
$output[] = array('label' => 'About', 'value' => highavailability_about());
echo htmlobject_tabmenu($output);

?>


