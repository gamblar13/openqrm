<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link type="text/css" href="css/htmlobject.css" rel="stylesheet" />
<link type="text/css" href="js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
<link type="text/css" href="css/jquery.css" rel="stylesheet" />
<link type="text/css" href="css/cloud.css" rel="stylesheet" />
<link type="text/css" href="css/cloud-custom-branding.css" rel="stylesheet" />
<script type="text/javascript" src="js/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/interface.js"></script>
</head>
<body>

<div id='cloud_logo'>
<img src="img/logo_big.png" alt="openQRM Cloud"/>
</div>

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
require_once $RootDir.'/include/openqrm-server-config.php';
require_once $RootDir.'/include/user.inc.php';
require_once $RootDir.'/include/openqrm-database-functions.php';
require_once $RootDir.'/class/event.class.php';
// special cloud classes
require_once  $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/user/class/cloud-ui.controller.class.php';
require_once  $RootDir.'/plugins/cloud/class/clouduser.class.php';

$user = new clouduser($_SERVER['PHP_AUTH_USER']);
$user->get_instance_by_name($_SERVER['PHP_AUTH_USER']);
$action = new cloud_ui_controller($user);
$lang_arr = $action->lang;

if (isset($_REQUEST['cloud_lang'])) {
	$cloud_lang = $_REQUEST['cloud_lang'];
} else {
	$cloud_lang = 'en';
}

if (isset($_REQUEST['cloud_lang_submit'])) {
	$user->set_users_language($user->id, $cloud_lang);
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=index.php\">";
}

// add custom-branding header
require_once "custom-branding-header.php";

echo "<div id='cloud_top_menu'>";

echo "<form action='index.php'>";

echo "<div id='cloud_logout'>";
$logout_explain = $lang_arr['cloud_ui_logout_help'];
echo $lang_arr['cloud_ui_user']." ".$user->name."&nbsp;&nbsp;<a href='../logout.php' style='text-decoration: none' title='".$logout_explain."'>".$lang_arr['cloud_ui_logout']."&nbsp;&nbsp;<img src='img/logout.png' width='16' height='16' alt='".$logout_explain."'/></a>";
echo "</div>";

echo "</form>";

// language select
echo "<form action='index.php'>";
echo "<div id='cloud_language_select'>";
echo "<input name=Submit value=Submit type=hidden>";
echo "<input name=cloud_lang_submit value=cloud_lang_submit type=hidden>";
$lang_en = $lang_arr['cloud_ui_language_english'];
$lang_fr = $lang_arr['cloud_ui_language_french'];
$lang_de = $lang_arr['cloud_ui_language_german'];
$lang_it = $lang_arr['cloud_ui_language_italian'];
$lang_nl = $lang_arr['cloud_ui_language_netherlands'];
$lang_es = $lang_arr['cloud_ui_language_spain'];
// $lang_ch = $lang_arr['cloud_ui_language_swiss'];
$lang_de_selected = '';
$lang_en_selected = '';
switch ($user->lang) {
//	case 'ch';
//		$lang_ch_selected = "selected='selected'";
//		break;
	case 'de';
		$lang_de_selected = "selected='selected'";
		break;
	case 'en';
		$lang_en_selected = "selected='selected'";
		break;
	case 'es';
		$lang_es_selected = "selected='selected'";
		break;
	case 'fr';
		$lang_fr_selected = "selected='selected'";
		break;
	case 'it';
		$lang_it_selected = "selected='selected'";
		break;
	case 'nl';
		$lang_nl_selected = "selected='selected'";
		break;

}
echo "<nobr><label>".$lang_arr['cloud_ui_language']." </label><select id='cloud_lang' name='cloud_lang' onchange='this.form.submit();'>";
echo "<option value='en' class='imagebacked' style='background-image: url(img/en.gif)'; ".$lang_en_selected.">&nbsp;&nbsp;&nbsp;".$lang_en."</option>";
echo "<option value='fr' class='imagebacked' style='background-image: url(img/fr.gif)'; ".$lang_fr_selected.">&nbsp;&nbsp;&nbsp;".$lang_fr."</option>";
echo "<option value='de' class='imagebacked' style='background-image: url(img/de.gif)'; ".$lang_de_selected.">&nbsp;&nbsp;&nbsp;".$lang_de."</option>";
echo "<option value='it' class='imagebacked' style='background-image: url(img/it.gif)'; ".$lang_it_selected.">&nbsp;&nbsp;&nbsp;".$lang_it."</option>";
echo "<option value='nl' class='imagebacked' style='background-image: url(img/nl.gif)'; ".$lang_nl_selected.">&nbsp;&nbsp;&nbsp;".$lang_nl."</option>";
echo "<option value='es' class='imagebacked' style='background-image: url(img/es.gif)'; ".$lang_es_selected.">&nbsp;&nbsp;&nbsp;".$lang_es."</option>";
//echo "<option value='ch' class='imagebacked' style='background-image: url(img/ch.gif)'; ".$lang_ch_selected.">&nbsp;&nbsp;&nbsp;".$lang_ch."</option>";


echo "</select><nobr>";
echo "</div>";
echo "</form>";

echo "</div>";


// title needs translation
echo "<div id='cloud_title'>";
echo "<h1>".$lang_arr['cloud_ui_title']."</h1><br><br>";
echo "</div>";

$output = $action->action();
echo $output->get_string();

// add custom-branding footer
require_once "custom-branding-footer.php";

?>
<div id="openqrm_enterprise_footer"><small><a href="http://www.openqrm-enterprise.com/" style="text-decoration:none;" target="_BLANK">openQRM&nbsp;Enterprise&nbsp;Cloud&nbsp;-&nbsp;&copy;&nbsp;2012&nbsp;openQRM Enterprise GmbH</a></small></div>

<script type="text/javascript">
function openVid(url) {
Vid = window.open(url,'Vid','location=0,status=0,scrollbars=1,width=920,height=820,left=300,top=50,screenX=300,screenY=50');
Vid.focus();
}
function openVcd(url) {
Vcd = window.open(url,'Vcd','location=0,status=0,scrollbars=1,width=910,height=740,left=100,top=50,screenX=100,screenY=50');
Vcd.focus();
}
</script>
</body>
</html>


