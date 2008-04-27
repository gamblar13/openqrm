<link rel="stylesheet" type="text/css" href="../../css/htmlobject.css" />
<link rel="stylesheet" type="text/css" href="event.css" />

<?php
$thisfile = basename($_SERVER['PHP_SELF']);

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/event.class.php";
require_once "$RootDir/include/htmlobject.inc.php";

function event_display() {
global $OPENQRM_USER;
global $thisfile;

	$event_tmp = new event();
	$table = new htmlobject_db_table('event_priority');

	$disp = '<h1>Event List</h1>';

	$arHead = array();
	$arHead['event_priority'] = array();
	$arHead['event_priority']['title'] ='Status';

	$arHead['event_id'] = array();
	$arHead['event_id']['title'] ='ID';

	$arHead['event_time'] = array();
	$arHead['event_time']['title'] ='Time';

	$arHead['event_source'] = array();
	$arHead['event_source']['title'] ='Source';

	$arHead['event_description'] = array();
	$arHead['event_description']['title'] ='Description';
	$arHead['event_description']['sortable'] = false;

	$arBody = array();
	$event_array = $event_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);


	foreach ($event_array as $index => $event_db) {
		$event = new event();
		$event->get_instance_by_id($event_db["event_id"]);
		$prio_icon="transition.png";
		switch ($event->priority) {
			case 0: $prio_icon = "off.png"; 	break;
			case 1: $prio_icon = "error.png";	break;
			case 2: $prio_icon = "error.png";	break;
			case 3:	$prio_icon = "error.png";	break;
			case 4:	$prio_icon = "transition.png"; 	break;
			case 5:	$prio_icon = "active.png"; 	break;
			case 6:	$prio_icon = "idle.png"; 	break;
			case 7:	$prio_icon = "idle.png"; 	break;
		}
		// acknowledged ?
		if ($event->status == 1) {
			$prio_icon="idle.png";
		}
		$arBody[] = array(
			'event_priority' => '<img src="/openqrm/base/img/'.$prio_icon.'">',
			'event_id' => $event_db["event_id"],
			'event_time' => date('d F Y h:i:s', $event->time),
			'event_source' => $event->source,
			'event_description' => $event->description,
		);

	}

	$table = new htmlobject_db_table();
	$table->id = 'Tabelle';
	$table->css = 'htmlobject_table';
	$table->border = 1;
	$table->cellspacing = 0;
	$table->cellpadding = 3;
	$table->form_action = $thisfile;
	$table->head = $arHead;
	$table->body = $arBody;
	if ($OPENQRM_USER->role == "administrator") {
		$table->bottom = array('delete');
		$table->identifier = 'event_id';
	}
	$table->max = $event_tmp->get_count();
	$table->lang_label_sort = 'sortiere nach';
	$table->lang_button_refresh = 'aktualisieren';
	#$table->limit = 10;
	
	return $disp.$table->get_string();
}


$output = array();
$output[] = array('label' => 'Event-List', 'value' => event_display(""));
echo htmlobject_tabmenu($output);

?>