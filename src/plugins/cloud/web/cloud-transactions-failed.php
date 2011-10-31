
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
require_once "$RootDir/plugins/cloud/class/cloudtransactionfailed.class.php";

global $OPENQRM_SERVER_BASE_DIR;
$refresh_delay=5;

function redirect_list($strMsg, $currenttab = 'tab0') {
	global $thisfile;
	$url = $thisfile.'?strMsg='.urlencode($strMsg).'&currenttab='.$currenttab."&redirect=yes";
	echo "<meta http-equiv=\"refresh\" content=\"0; URL=$url\">";
	exit;
}


// check if we got some actions to do
$strMsg = '';
if (htmlobject_request('redirect') != 'yes') {
	if(htmlobject_request('action') != '') {
		switch (htmlobject_request('action')) {
			case 'sync':
				if (isset($_REQUEST['identifier'])) {
					foreach($_REQUEST['identifier'] as $id) {
						$cloudtransaction_f = new cloudtransactionfailed();
						$cloudtransaction_f->get_instance_by_id($id);
						$cloudtransaction_s = new cloudtransaction();
						$cloudtransaction_s->get_instance_by_id($cloudtransaction_f->ct_id);
						if ($cloudtransaction_s->sync($cloudtransaction_f->ct_id, false)) {
							$cloudtransaction_f->remove($id);
							$strMsg .= "Synced Cloud Transaction ".$id."<br>";
						} else {
							$strMsg .= "Syncing Cloud Transaction ".$id." failed!<br>";
						}
					}
					redirect_list($strMsg, 'tab0');
				}
				break;
		}
	}
}


function cloud_transaction_failed_list() {
	global $OPENQRM_USER;
	global $thisfile;

	$table = new htmlobject_db_table('tf_id', 'DESC');
	$arHead = array();

	$arHead['tf_id'] = array();
	$arHead['tf_id']['title'] ='ID';

	$arHead['tf_ct_id'] = array();
	$arHead['tf_ct_id']['title'] ='Local ID';

	$arHead['tf_time'] = array();
	$arHead['tf_time']['title'] ='Time';

	$arHead['tf_cr_id'] = array();
	$arHead['tf_cr_id']['title'] ='CR';

	$arHead['tf_cu_id'] = array();
	$arHead['tf_cu_id']['title'] ='User';

	$arHead['tf_ccu_charge'] = array();
	$arHead['tf_ccu_charge']['title'] ='Charge';

	$arHead['tf_ccu_balance'] = array();
	$arHead['tf_ccu_balance']['title'] ='Balance';

	$arHead['tf_reason'] = array();
	$arHead['tf_reason']['title'] ='Reason';

	$arHead['tf_comment'] = array();
	$arHead['tf_comment']['title'] ='Comment';

	$arBody = array();

	// db select
	$tf_count = 0;
	$cloudtransaction_failed = new cloudtransactionfailed();
	$cloudtransaction_array = $cloudtransaction_failed->display_overview($table->offset, $table->limit, $table->sort, $table->order);
	foreach ($cloudtransaction_array as $index => $ct) {
		$cloudtransaction_failed->get_instance_by_id($ct["tf_id"]);
		$cloudtransaction = new cloudtransaction();
		$cloudtransaction->get_instance_by_id($cloudtransaction_failed->ct_id);
		// format time
		$tf_time = date("d-m-Y H-i", $cloudtransaction->time);
		// get user name
		$tf_user = new clouduser();
		$tf_user->get_instance_by_id($cloudtransaction->cu_id);

		$arBody[] = array(
			'tf_id' => $ct["tf_id"],
			'tf_ct_id' => $cloudtransaction->id,
			'tf_time' => $tf_time,
			'tf_cr_id' => $cloudtransaction->cr_id,
			'tf_cu_id' => $tf_user->name,
			'tf_ccu_charge' => "-".$cloudtransaction->ccu_charge,
			'tf_ccu_balance' => $cloudtransaction->ccu_balance,
			'tf_reason' => $cloudtransaction->reason,
			'tf_comment' => $cloudtransaction->comment,
		);
		$tf_count++;
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
	$table->max = $cloudtransaction_failed->get_count();
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('sync');
		$table->identifier = 'tf_id';
	}

	//------------------------------------------------------------ set template
	$t = new Template_PHPLIB();
	$t->debug = false;
	$t->setFile('tplfile', './tpl/' . 'cloud-transactions-failed-tpl.php');
	$t->setVar(array(
		'cloud_transaction_failed_table' => $table->get_string(),
	));
	$disp =  $t->parse('out', 'tplfile');
	return $disp;


}



$output = array();
$output[] = array('label' => 'Failed Transactions', 'value' => cloud_transaction_failed_list());
echo htmlobject_tabmenu($output);

?>