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
require_once $RootDir."/include/openqrm-database-functions.php";
require_once $RootDir."/class/resource.class.php";
require_once $RootDir."/class/virtualization.class.php";
require_once $RootDir."/class/image.class.php";
require_once $RootDir."/class/kernel.class.php";
require_once $RootDir."/class/plugin.class.php";
require_once $RootDir."/class/event.class.php";
require_once $RootDir."/class/openqrm_server.class.php";
require_once $RootDir.'/plugins/cloud/class/clouduser.class.php';

$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/user/';
require_once($CloudDir.'/class/cloud-ui.controller.class.php');
$user = new clouduser($_SERVER['PHP_AUTH_USER']);
$user->get_instance_by_name($_SERVER['PHP_AUTH_USER']);
$controller = new cloud_ui_controller($user);
$controller->api();
?>
