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
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once $RootDir."/include/user.inc.php";
require_once $RootDir."/class/openqrm.class.php";
require_once $RootDir."/plugins/aoe-storage/class/aoe-storage-about.controller.class.php";
require_once $RootDir."/class/htmlobjects/htmlobject.class.php";
$html = new htmlobject($RootDir."/class/htmlobjects/");
$response = $html->response();
$openqrm = new openqrm();
$openqrm->init();

$action = new aoe_storage_about_controller($openqrm, $response);
$output = $action->action();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>AOE Storage Documentation</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="css/aoe-storage.css" />
<link type="text/css" href="/openqrm/base/js/jquery/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/openqrm/base/js/jquery/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="/openqrm/base/js/interface/interface.js"></script>
<script type="text/javascript" src="js/helpers.js"></script>
</head>
<body>
<?php echo $output->get_string(); ?>
</body>
</html>
