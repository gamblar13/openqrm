<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link type="text/css" href="css/index.css" rel="stylesheet" />
</head>
<body>

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


$docroot = $_SERVER["DOCUMENT_ROOT"];
$openqrm_web = $docroot.'/openqrm/base/';
$cloud_web = $docroot.'/cloud-portal';
require_once $openqrm_web."/include/openqrm-server-config.php";
require_once $openqrm_web."/include/user.inc.php";
require_once $openqrm_web."/include/openqrm-database-functions.php";
require_once $openqrm_web."/class/event.class.php";
// special cloud classes
require_once  $cloud_web.'/user/class/cloud-register.controller.class.php';

?>

<div id="logo">
	<img src="img/logo_big.png" alt="openQRM Cloud"/>
</div>

<div id="title">
	<h1>openQRM Cloud</h1>
</div>


<div id="login">
<?php
	$action = new cloud_register_controller();
	$output = $action->action();
	echo $output->get_string();
?>
</div>



<div id="footer">
	<small><a href="http://www.openqrm-enterprise.com/" style="text-decoration:none;" target="_BLANK">openQRM&nbsp;Enterprise&nbsp;Cloud&nbsp;-&nbsp;&copy;&nbsp;2012&nbsp;openQRM Enterprise GmbH</a></small>
</div>

</body>
</html>


