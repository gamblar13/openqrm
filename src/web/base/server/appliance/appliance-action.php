<?php
$appliance_command = $_REQUEST["appliance_command"];
$appliance_name = $_REQUEST["appliance_name"];
?>

<html>
<head>
<title>openQRM Appliance actions</title>
<meta http-equiv="refresh" content="0; URL=appliance-overview.php?currenttab=tab2&strMsg=Processing <?php echo $appliance_command; ?> on <?php echo $appliance_name; ?>">
</head>
<body>

<?php

$RootDir = $_SERVER["DOCUMENT_ROOT"].'openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/deployment.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
global $APPLIANCE_INFO_TABLE;

// user/role authentication
if ($OPENQRM_USER->role != "administrator") {
	syslog(LOG_ERR, "openQRM-engine: Un-Authorized access to appliance-actions from $OPENQRM_USER->name!");
	exit();
}

$appliance_id = $_REQUEST["appliance_id"];
$appliance_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "appliance_", 10) == 0) {
		$appliance_fields[$key] = $value;
	}
}
unset($appliance_fields["appliance_command"]);

$deployment_id = $_REQUEST["deployment_id"];
$deployment_name = $_REQUEST["deployment_name"];
$deployment_type = $_REQUEST["deployment_type"];
$deployment_fields = array();
foreach ($_REQUEST as $key => $value) {
	if (strncmp($key, "deployment_", 11) == 0) {
		$deployment_fields[$key] = $value;
	}
}

	syslog(LOG_NOTICE, "openQRM-engine: Processing command $appliance_command on appliance $appliance_name");
	switch ($appliance_command) {
		case 'new_appliance':
			$appliance = new appliance();
			$appliance_fields["appliance_id"]=openqrm_db_get_free_id('appliance_id', $APPLIANCE_INFO_TABLE);
			$appliance->add($appliance_fields);
			break;

		case 'remove':
			$appliance = new appliance();
			$appliance->remove($appliance_id);
			break;

		case 'remove_by_name':
			$appliance = new appliance();
			$appliance->remove_by_name($appliance_name);
			break;

		case 'start':
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_id);
			$appliance->start();
			break;

		case 'stop':
			$appliance = new appliance();
			$appliance->get_instance_by_id($appliance_id);
			$appliance->stop();
			break;

		default:
			echo "No Such openQRM-command!";
			break;


	}
?>

</body>
