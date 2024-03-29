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
//$ti = microtime(true);

// check if configured already
if (file_exists("./unconfigured")) {
    header("Location: configure.php");
}

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/openqrm.controller.class.php";
require_once($RootDir.'/include/user.inc.php');
$action = new openqrm_controller();
$output = $action->action();
echo $output->get_string();

//if(function_exists('memory_get_peak_usage')) {
//	$memory = memory_get_peak_usage(false);
//}
//echo 'memory: '.$memory.' byte<br>';
//$ti = (microtime(true) - $ti);
//echo 'time: '.$ti.' sec';
?>
