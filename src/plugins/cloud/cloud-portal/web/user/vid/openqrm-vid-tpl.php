<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<link rel="stylesheet" href="vid.css" type="text/css" media="screen" />
		<script type="text/javascript" src="../js/js/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="../js/js/jquery-ui-1.7.1.custom.min.js"></script>
		<script type="text/javascript" src="../../js/drag.js"></script>
		<!-- initialize drag and drop -->
		<script type="text/javascript">


		// get a single items html content, loading via an ajax call
		function getCellContent(cellname) {
			// get the html content of the cell via an ajax call
			var cell_content = $.ajax({
				 url: 'vid.php?action=LoadItem&oid=' + cellname,
				 type: "GET",
				 cache: false,
				 async: false,
				 success: function (html) {
					 if (html==1) {
						alert('Ajax-error in getCellContent ' +cellname);
					 }
				 }
			}).responseText;
			return cell_content;
		}


		// get the overall status of the cloud datacenter
		// green = ok
		// orange = in transition
		// red = error
		function getDatacenterStatus() {
			// get the html content of the cell via an ajax call
			var dc_status = $.ajax({
				 url: 'vid.php?action=GetStatus',
				 type: "GET",
				 cache: false,
				 async: false,
				 success: function (html) {
					 if (html==1) {
						alert('Ajax-error in getDatacenterStatus');
					 }
				 }
			}).responseText;
			return dc_status;
		}


	   // to play sound alerts
		function DHTMLSound(surl) {
			document.getElementById("soundspan").innerHTML=
				"<embed src='"+surl+"' hidden=true autostart=true loop=false>";
		}



		window.onload = function () {

			REDIPS.drag.init();
			REDIPS.drag.trash_ask = false;
			REDIPS.drag.drop_option = 'single';
			REDIPS.drag.myhandler_clicked    = function () {}
			REDIPS.drag.myhandler_moved      = function () {}
			//REDIPS.drag.myhandler_notmoved   = function () {}
			REDIPS.drag.myhandler_dropped    = function () {
													// first check if its a profile, if yes, fix the matrix for the transformation
													var id_str = REDIPS.drag.obj.id;
													if (id_str.search('p') == 0) {
														REDIPS.drag.enable_drag(false);
													} else if (id_str.search('w') == 0) {
														REDIPS.drag.enable_drag(false);
													}
													// get data from main table
													var main_content = REDIPS.drag.save_content(1);
													// prepare ajax call to save the matrix
													var cell_data = $.ajax({
														url: 'vid.php?action=Save&' + main_content,
														type: "GET",
														cache: false,
														async: false,
														success: function (html) {
															if (html==1) {
																alert('Ajax-error in drop-event');
															} else {
																// if a profile was dropped we get a new div id from the ajax response
																if (id_str.search('p') == 0) {
																	// apply new name from responseText
																	REDIPS.drag.obj.id = html;
																	// load data for the item
																	REDIPS.drag.obj.innerHTML = getCellContent(REDIPS.drag.obj.id);
																	// re-enable dragging
																	REDIPS.drag.enable_drag(true);
																} else if (id_str.search('a') == 0) {
//                                                                    alert("app got moved " + REDIPS.drag.obj.id);
																} else if (id_str.search('n') == 0) {
//                                                                    alert("app got moved " + REDIPS.drag.obj.id);
																} else if (id_str.search('w') == 0) {
																	// apply new name from responseText
																	REDIPS.drag.obj.id = html;
																	// load data for the item
																	REDIPS.drag.obj.innerHTML = getCellContent(REDIPS.drag.obj.id);
																	// re-enable dragging
																	REDIPS.drag.enable_drag(true);

																}
															}
														}
													}).responseText;
												}
			REDIPS.drag.myhandler_switched   = function () {}
			REDIPS.drag.myhandler_clonedend1 = function () {}
			REDIPS.drag.myhandler_clonedend2 = function () {}
			REDIPS.drag.myhandler_notcloned  = function () {}
			REDIPS.drag.myhandler_deleted    = function () {
													// ajax remove call
													$.ajax({
														url: 'vid.php?action=Remove&oid=' + REDIPS.drag.obj.id,
														type: "GET",
														cache: false,
														async: false,
														success: function (html) {
															if (html==1) {
																alert('Ajax-error in remove-event.');
															}
														}
													});
												}
			REDIPS.drag.myhandler_undeleted  = function () {}
			REDIPS.drag.myhandler_cloned     = function () {}
		}
		function toggle_confirm(chk) {
			REDIPS.drag.trash_ask = chk.checked;
		}
		function toggle_delete_cloned(chk) {
			REDIPS.drag.delete_cloned = chk.checked;
		}
		function toggle_dragging(chk) {
			REDIPS.drag.enable_drag(chk.checked);
		}
		function set_drop_option(radio_button) {
			REDIPS.drag.drop_option = radio_button.value;
		}

		</script>

		<title>openQRM Cloud - Visual Infrastructure Designer</title>
	</head>
	<body>

	<div id="logo" class="logo">
		<a href="http://www.openqrm.com" target="_blank" style="text-decoration: none"><img src="../../img/logo_big.png" alt="The open-source Cloud Computing Platform" border="0"/></a>
	</div>

	<div id="overallstatusbox">
		<center><small>Infrastructure Status</small></center>
	</div>

	<div id="soundcheckbox">
		<form name=soundform>
		<input name='soundalerts' type='checkbox'><small>Enable Sound Alert</small>
		</form>
	</div>

	<div id="refreshdelaybox">
		<form name=refreshdelayform>
			<select name="refreshdelay" size="1">
			  <option value="5000">5 sec</option>
			  <option value="10000" selected>10 sec</option>
			  <option value="30000">30 sec</option>
			  <option value="60000">60 sec</option>
			</select> <small>Refresh</small>
		</form>
	</div>

	<div id="titlebox">
		<a href="http://www.openqrm.com" target="_BLANK"><b>openQRM's Visual Infrastructure Designer</b></a>
	</div>

	<br>
	<br>

		<div id="drag">
			<table id="table2">
				<colgroup><col width="50"/><col width="50"/><col width="50"/><col width="50"/><col width="50"/></colgroup>
				<tr style="background-color: #eee">
					<td id="noop" class="mark" title="You can not drop here"><small>Profiles</small></td>
					{cloud_profile_inentory}
				</tr>

				<tr style="background-color: #eee">
					<td id="noop" class="mark" title="You can not drop here"><small>Network</small></td>
					<td><div id="w1" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_vertical.png'></div></td>
					<td><div id="w2" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_horizontal.png'></div></td>
					<td><div id="w3" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_up_right.png'></div></td>
					<td><div id="w4" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_up_left.png'></div></td>
					<td><div id="w5" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_down_right.png'></div></td>
					<td><div id="w6" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_down_left.png'></div></td>
					<td><div id="w7" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_switch_up.png'></div></td>
					<td><div id="w8" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_switch_right.png'></div></td>
					<td><div id="w9" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_switch_down.png'></div></td>
					<td><div id="w10" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_switch_left.png'></div></td>
					<td><div id="w11" class="drag t3 clone" title="Network Connection"><img height="48" width="48" src='../../img/net_switch.png'></div></td>
				</tr>
			</table>

			<table id="table3">
				<colgroup><col width="50"/><col width="50"/><col width="50"/><col width="50"/><col width="50"/></colgroup>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
				</tr>
				<tr style="background-color: #eee">
					<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
					<td class="trash" title="Trash"><img src='../../img/trash.png'></td>
				</tr>
			</table>
		</div>

		<span id='soundspan'></span>

		<div id="sponsorbox">
			 <a href="http://www.openqrm-enterprise.com" target="_BLANK"><small>sponsored by openQRM Enterprise</small></a>
		</div>


		<script type="text/javascript">


		// create DIV element and append to the table cell
		function createCell(cell, text, style){
			// create DIV element
			var div = document.createElement('div');
			div.id = text;
			div.setAttribute('class', style);
			// set DIV class attribute for IE (?!)
			div.setAttribute('className', style);
			div.style.border="border: 0px solid white; cursor: move;";
			div.innerHTML = getCellContent(text);
			// append DIV to the table cell
			cell.appendChild(div);
		}

		// places new div on row/cell
		function placeCell(id, row, cell){
			var tbl = document.getElementById('table3');
			tbl.rows[row].deleteCell(cell);
			createCell(tbl.rows[row].insertCell(cell), id, 'drag t3');
		}

		// function to evaluate the returned array from load
		var printArray = function (x, idx) {
			if (x != 0) {
				// get row number from idx
				var target_row = 0;
				var target_cell = idx;
				if (idx > 15) {
					target_row = 1;
					target_cell = idx - 16;
				}
				if (idx > 31) {
					target_row = 2;
					target_cell = idx - 32;
				}
				if (idx > 47) {
					target_row = 3;
					target_cell = idx - 48;
				}
				if (idx > 63) {
					target_row = 4;
					target_cell = idx - 64;
				}
				if (idx > 79) {
					target_row = 5;
					target_cell = idx - 80;
				}
				if (idx > 95) {
					target_row = 6;
					target_cell = idx - 96;
				}
				if (idx > 111) {
					target_row = 7;
					target_cell = idx - 112;
				}
				if (idx > 127) {
					target_row = 8;
					target_cell = idx - 128;
				}
				if (idx > 143) {
					target_row = 9;
					target_cell = idx - 144;
				}
				if (idx > 159) {
					target_row = 10;
					target_cell = idx - 160;
				}
				if (idx > 175) {
					target_row = 11;
					target_cell = idx - 176;
				}
				placeCell(x, target_row, target_cell);
			}
		}


		// function to update each appliance matrix cell
		var matrix_Array = function (x, idx) {
			var cell_id = x.replace(/_.*/g, "");
			if (cell_id.search('a') == 0) {
				// check that ca still exists
				var adiv_content = getCellContent(cell_id);
				if (adiv_content.length > 0) {
					document.getElementById(cell_id).innerHTML = getCellContent(cell_id);
				} else {
					// finx x/y coordinates
					var first_separator = x.indexOf('_');
					var second_separator = x.indexOf('_', first_separator + 1);
					var remove_cell_y = x.substr(first_separator + 1, second_separator - (first_separator + 1));
					var remove_cell_x = x.substr(second_separator + 1);
					// get matrix reference and remove cell
					var tbl = document.getElementById('table3');
					tbl.rows[remove_cell_y].deleteCell(remove_cell_x);
					createCell(tbl.rows[remove_cell_y].insertCell(remove_cell_x), '0', '');
					// rescan the matrix afer placing the cell
					main_content_remove_rescan = REDIPS.drag.save_content(1);
					// prepare ajax call to save the matrix
					var remove_response = $.ajax({
						url: 'vid.php?action=Save&' + main_content_remove_rescan,
						type: "GET",
						cache: false,
						async: false,
						success: function (html) {
							if (html==1) {
								alert('Ajax-error in saving Matrix after removing custom Cloudappliance');
							}
						}
					}).responseText;
				}
			}
		}


		// reload the complete matrix via ajax
		function reloadMatrix() {
			var matrix_content = REDIPS.drag.save_content(1);
			var matrix_content_str = matrix_content.toString();
			matrix_content_str = matrix_content_str.replace(/p/g, "");
			matrix_content_str = matrix_content_str.replace(/\[/g, "");
			matrix_content_str = matrix_content_str.replace(/\]/g, "");
			matrix_content_str = matrix_content_str.replace(/=/g, "");
			var matrix_content_array = matrix_content_str.split('&');
			matrix_content_array.forEach(matrix_Array);

			// get the overall system status
			var overall_status = getDatacenterStatus();
			switch(overall_status) {
				case 'green':
					document.getElementById("overallstatusbox").style.backgroundColor = '#05fd05';
					break;
				case 'orange':
					document.getElementById("overallstatusbox").style.backgroundColor = '#ffa800';
					if (document.soundform.soundalerts.checked) {
						DHTMLSound('/cloud-portal/snd/orange.wav');
					}
					break;
				case 'red':
					document.getElementById("overallstatusbox").style.backgroundColor = '#DF0101';
					if (document.soundform.soundalerts.checked) {
						DHTMLSound('/cloud-portal/snd/red.wav');
					}
					break;
				default:
					// this is a new custom ca found, so we place it on the next free cell
					var next_empty_x = 0;
					var next_empty_y = 0;
					var found_empty_cell = 0;
					var main_content_custom = REDIPS.drag.save_content(1);
					if (main_content_custom.length > 0) {
						var matrix_content_custom_str = main_content_custom.toString();
						for (var cy = 0; cy <12; cy++) {
							for (var cx = 0; cx < 16; cx++) {
								var cteststr = "_" + cy + "_" + cx;
								if (matrix_content_custom_str.search(cteststr) <= 0) {
									found_empty_cell = 1;
									next_empty_y = cy;
									next_empty_x = cx;
									break;
								}
							}
							if (found_empty_cell == 1) {
								break;
							}
						}
					}
					// now place the new cell on the matrix
					placeCell(overall_status, next_empty_y, next_empty_x);
					REDIPS.drag.init();
					// rescan the matrix afer placing the cell
					main_content_custom_rescan = REDIPS.drag.save_content(1);
					// prepare ajax call to save the matrix
					var save_response = $.ajax({
						url: 'vid.php?action=Save&' + main_content_custom_rescan,
						type: "GET",
						cache: false,
						async: false,
						success: function (html) {
							if (html==1) {
								alert('Ajax-error in saving Matrix after adding custom Cloudappliance');
							}
						}
					}).responseText;
					document.getElementById("overallstatusbox").style.backgroundColor = '#05fd05';
					break;

			}

			// get refresh delay from select box
			setTimeout("reloadMatrix()", document.refreshdelayform.refreshdelay.options[document.refreshdelayform.refreshdelay.selectedIndex].value);
		}



		// loads the full matrix table via ajax
		function loadMatrix(){
			var table_data = $.ajax({
				 url: 'vid.php?action=Load',
				 type: "GET",
				 cache: false,
				 async: false,
				 success: function (html) {
					 if (html==1) {
						alert('Ajax-error in loadMatrix');

					 }
				 }
			 }).responseText;
			var table_array = table_data.split(',');
			table_array.forEach(printArray);
		}


		// finally load the table-data
		loadMatrix();
		setTimeout("reloadMatrix()", 1000 );
		</script>


	</body>
</html>
