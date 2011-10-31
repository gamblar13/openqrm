
<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="cloud.css" />

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
$thisfile = basename($_SERVER['PHP_SELF']);
$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
$BaseDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/';
$CloudDir = $_SERVER["DOCUMENT_ROOT"].'/cloud-portal/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/htmlobject.inc.php";
// special clouduser class
require_once "$RootDir/plugins/cloud/class/clouduser.class.php";
require_once "$RootDir/plugins/cloud/class/cloudusergroup.class.php";
require_once "$RootDir/plugins/cloud/class/clouduserslimits.class.php";
require_once "$RootDir/plugins/cloud/class/cloudconfig.class.php";
require_once "$RootDir/plugins/cloud/class/cloudtransaction.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;




function cloud_transaction_list() {
	global $thisfile;

	$table = new htmlobject_table_identifiers_checked('ct_id', 'DESC');
	$arHead = array();

	$arHead['ct_id'] = array();
	$arHead['ct_id']['title'] ='ID';

	$arHead['ct_time'] = array();
	$arHead['ct_time']['title'] ='Time';

	$arHead['ct_cr_id'] = array();
	$arHead['ct_cr_id']['title'] ='CR';

	$arHead['ct_cu_id'] = array();
	$arHead['ct_cu_id']['title'] ='User';

	$arHead['ct_ccu_charge'] = array();
	$arHead['ct_ccu_charge']['title'] ='Charge';

	$arHead['ct_ccu_balance'] = array();
	$arHead['ct_ccu_balance']['title'] ='Balance';

	$arHead['ct_reason'] = array();
	$arHead['ct_reason']['title'] ='Reason';

	$arHead['ct_comment'] = array();
	$arHead['ct_comment']['title'] ='Comment';

	$arBody = array();

	// db select
	$ct_count = 0;
	$cloudtransaction = new cloudtransaction();
	$cloudtransaction_array = $cloudtransaction->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($cloudtransaction_array as $index => $ct) {
		// format time
		$ct_time = date("d-m-Y H-i", $ct["ct_time"]);
		// get user name
		$ct_user = new clouduser();
		$ct_user->get_instance_by_id($ct["ct_cu_id"]);



		$arBody[] = array(
			'ct_id' => $ct["ct_id"],
			'ct_time' => $ct_time,
			'ct_cr_id' => $ct["ct_cr_id"],
			'ct_cu_id' => $ct_user->name,
			'ct_ccu_charge' => "-".$ct["ct_ccu_charge"],
			'ct_ccu_balance' => $ct["ct_ccu_balance"],
			'ct_reason' => $ct["ct_reason"],
			'ct_comment' => $ct["ct_comment"],
		);
		$ct_count++;
	}

	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->identifier_type = "checkbox";
	$table->head = $arHead;
	$table->body = $arBody;
	$table->max = $cloudtransaction->get_count();

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-transactions-tpl.php');
	$t->setVar(array(
		'cloud_transaction_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Transactions', 'value' => cloud_transaction_list());
echo htmlobject_tabmenu($output);

?>