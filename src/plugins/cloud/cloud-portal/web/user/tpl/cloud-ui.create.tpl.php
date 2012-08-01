<!--
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->
<script type="text/javascript">

		window.onload = function() {
		
		<!-- component details slider -->
		$(".flip").click(function(){
			$(".panel").slideToggle("slow");
			var panelheight = $(".panel").height();
			if (panelheight == 0) {
				$('#cloud_request_system_profile_image').hide();
				$('#cloud_request_system_profile_space').width(1);
			} else {
				$('#cloud_request_system_profile_image').show();
				$('#cloud_request_system_profile_space').width(150);
			}
		});



		<!-- profile select -->
		var system_profile_image_src = '';
		$("#cloud_profile_select").change(function() {
			var cloud_profile = $("#cloud_profile_select option:selected").val();
			switch (cloud_profile) {
				case '1':
					$('#cloud_memory_select option:eq(0)').attr('selected', 'selected');
					$('#cloud_cpu_select option:eq(0)').attr('selected', 'selected');
					$('#cloud_disk_select option:eq(0)').attr('selected', 'selected');
					$('#cloud_network_select option:eq(0)').attr('selected', 'selected');
					system_profile_image_src = 'img/system_profile_small.png'
					break;
				case '2':
					$('#cloud_memory_select option:eq(1)').attr('selected', 'selected');
					$('#cloud_cpu_select option:eq(1)').attr('selected', 'selected');
					$('#cloud_disk_select option:eq(1)').attr('selected', 'selected');
					$('#cloud_network_select option:eq(1)').attr('selected', 'selected');
					system_profile_image_src = 'img/system_profile_medium.png'
					break;
				case '3':
					$('#cloud_memory_select option:eq(2)').attr('selected', 'selected');
					$('#cloud_cpu_select option:eq(2)').attr('selected', 'selected');
					$('#cloud_disk_select option:eq(2)').attr('selected', 'selected');
					$('#cloud_network_select option:eq(2)').attr('selected', 'selected');
					system_profile_image_src = 'img/system_profile_big.png'
					break;
			}
			$('#cloud_request_system_profile_image').attr("src", system_profile_image_src);
			cloud_cost_calculator();
		})


		<!-- onchange functions to trigger cost recalculation -->
		$("select[name=cloud_virtualization_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("select[name=cloud_kernel_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("select[name=cloud_memory_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("select[name=cloud_cpu_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("select[name=cloud_disk_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("select[name=cloud_network_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_ha_select]").change(function () {
			cloud_cost_calculator();
		}).change();
		// apps
		$("input[name=cloud_puppet_select_0]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_1]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_2]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_3]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_4]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_5]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_6]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_7]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_8]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_9]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_10]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_11]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_12]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_13]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_14]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_15]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_16]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_17]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_18]").change(function () {
			cloud_cost_calculator();
		}).change();
		$("input[name=cloud_puppet_select_19]").change(function () {
			cloud_cost_calculator();
		}).change();





		<!-- remove selected ip from the other selects -->
		$("#cloud_ip_select_0").change(function() {
			var sid = $("#cloud_ip_select_0 option:selected").val();
			var last_sid = $("#cloud_ip_select_0 option:last").val();
			if (sid != -2) {
				for (var n=1; n<=last_sid; n++) {
					var sidval = $('#cloud_ip_select_1 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_1 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_2 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_2 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_3 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_3 option:eq('+ n +')').remove();
					}
				}
			}
		})


		$("#cloud_ip_select_1").change(function() {
			var sid = $("#cloud_ip_select_1 option:selected").val();
			var last_sid = $("#cloud_ip_select_1 option:last").val();
			if (sid != -2) {
				for (var n=1; n<=last_sid; n++) {
					var sidval = $('#cloud_ip_select_0 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_0 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_2 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_2 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_3 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_3 option:eq('+ n +')').remove();
					}
				}
			}
		})


		$("#cloud_ip_select_2").change(function() {
			var sid = $("#cloud_ip_select_2 option:selected").val();
			var last_sid = $("#cloud_ip_select_2 option:last").val();
			if (sid != -2) {
				for (var n=1; n<=last_sid; n++) {
					var sidval = $('#cloud_ip_select_0 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_0 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_1 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_1 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_3 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_3 option:eq('+ n +')').remove();
					}
				}
			}
		})


		$("#cloud_ip_select_3").change(function() {
			var sid = $("#cloud_ip_select_3 option:selected").val();
			var last_sid = $("#cloud_ip_select_3 option:last").val();
			if (sid != -2) {
				for (var n=1; n<=last_sid; n++) {
					var sidval = $('#cloud_ip_select_1 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_1 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_2 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_2 option:eq('+ n +')').remove();
					}
					var sidval = $('#cloud_ip_select_0 option:eq('+ n +')').val();
					if (sidval == sid) {
						$('#cloud_ip_select_0 option:eq('+ n +')').remove();
					}
				}
			}
		})

		<!-- preset ip selects to 1 nic -->
		$('#cloud_ip_select_0').removeAttr("disabled");
		$('#cloud_ip_select_1').attr('disabled', 'true');
		$('#cloud_ip_select_2').attr('disabled', 'true');
		$('#cloud_ip_select_3').attr('disabled', 'true');

		<!-- load costs -->
		cloud_cost_calculator();
		};


		function cloud_cost_calculator() {
			var this_cloud_id = 0;
			var virtualization = $("select[name=cloud_virtualization_select]").val();
			var kernel = $("select[name=cloud_kernel_select]").val();
			var memory = $("select[name=cloud_memory_select]").val();
			var cpu = $("select[name=cloud_cpu_select]").val();
			var disk = $("select[name=cloud_disk_select]").val();
			var network = $("select[name=cloud_network_select]").val();

			var ha = 0;
			if ($("input[name=cloud_ha_select]").is(":checked")) {
				var ha = 1;
			}
			
			var apps = '';
			if ($("input[name=cloud_puppet_select_0]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_0]").val();
			}
			if ($("input[name=cloud_puppet_select_1]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_1]").val();
			}
			if ($("input[name=cloud_puppet_select_2]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_2]").val();
			}
			if ($("input[name=cloud_puppet_select_3]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_3]").val();
			}
			if ($("input[name=cloud_puppet_select_4]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_4]").val();
			}
			if ($("input[name=cloud_puppet_select_5]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_5]").val();
			}
			if ($("input[name=cloud_puppet_select_6]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_6]").val();
			}
			if ($("input[name=cloud_puppet_select_7]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_7]").val();
			}
			if ($("input[name=cloud_puppet_select_8]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_8]").val();
			}
			if ($("input[name=cloud_puppet_select_9]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_9]").val();
			}
			if ($("input[name=cloud_puppet_select_10]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_10]").val();
			}
			if ($("input[name=cloud_puppet_select_11]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_11]").val();
			}
			if ($("input[name=cloud_puppet_select_12]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_12]").val();
			}
			if ($("input[name=cloud_puppet_select_13]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_13]").val();
			}
			if ($("input[name=cloud_puppet_select_14]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_14]").val();
			}
			if ($("input[name=cloud_puppet_select_15]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_15]").val();
			}
			if ($("input[name=cloud_puppet_select_16]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_16]").val();
			}
			if ($("input[name=cloud_puppet_select_17]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_17]").val();
			}
			if ($("input[name=cloud_puppet_select_18]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_18]").val();
			}
			if ($("input[name=cloud_puppet_select_19]").is(":checked")) {
				apps = apps + ":";
				apps = apps + $("input[name=cloud_puppet_select_19]").val();
			}

			// enable/disable ip selects
			<!-- adjust ip selects according to the nic count -->
			switch (network) {
				case '1':
					$('#cloud_ip_select_0').removeAttr("disabled");
					$('#cloud_ip_select_1').attr('disabled', 'true');
					$('#cloud_ip_select_2').attr('disabled', 'true');
					$('#cloud_ip_select_3').attr('disabled', 'true');
					break;
				case '2':
					$('#cloud_ip_select_0').removeAttr("disabled");
					$('#cloud_ip_select_1').removeAttr("disabled");
					$('#cloud_ip_select_2').attr('disabled', 'true');
					$('#cloud_ip_select_3').attr('disabled', 'true');
					break;
				case '3':
					$('#cloud_ip_select_0').removeAttr("disabled");
					$('#cloud_ip_select_1').removeAttr("disabled");
					$('#cloud_ip_select_2').removeAttr("disabled");
					$('#cloud_ip_select_3').attr('disabled', 'true');
					break;
				case '4':
					$('#cloud_ip_select_0').removeAttr("disabled");
					$('#cloud_ip_select_1').removeAttr("disabled");
					$('#cloud_ip_select_2').removeAttr("disabled");
					$('#cloud_ip_select_3').removeAttr("disabled");
					break;
				default:
					$('#cloud_ip_select_0').attr('disabled', 'false');
					$('#cloud_ip_select_1').attr('disabled', 'false');
					$('#cloud_ip_select_2').attr('disabled', 'false');
					$('#cloud_ip_select_3').attr('disabled', 'false');
					break;
			}





			// send ajax request to calculator
			// this connects via soap to the specific cloud-zone server to get the costs for the request
			var cloud_calculator_url = "/cloud-portal/user/api.php?action=calculator&virtualization=" + virtualization;
			cloud_calculator_url = cloud_calculator_url + "&kernel=" + kernel;
			cloud_calculator_url = cloud_calculator_url + "&memory=" + memory;
			cloud_calculator_url = cloud_calculator_url + "&cpu=" + cpu;
			cloud_calculator_url = cloud_calculator_url + "&disk=" + disk;
			cloud_calculator_url = cloud_calculator_url + "&network=" + network;
			cloud_calculator_url = cloud_calculator_url + "&ha=" + ha;
			cloud_calculator_url = cloud_calculator_url + "&apps=" + apps;

//  alert(cloud_calculator_url);

			$.ajax({
				url : cloud_calculator_url,
				type: "POST",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					var cost_array = data.split(":");
					$("#cost_resource_type_req_val").text(cost_array[0]);;
					$("#cost_kernel_val").text(cost_array[1]);;
					$("#cost_memory_val").text(cost_array[2]);;
					$("#cost_cpu_val").text(cost_array[3]);;
					$("#cost_disk_val").text(cost_array[4]);;
					$("#cost_network_val").text(cost_array[5]);;
					$("#cost_ha_val").text(cost_array[6]);;
					$("#cost_apps_val").text(cost_array[7]);;
					$("#cost_sum_per_hour_currency").text(cost_array[8]);;
					$("#cost_sum_per_day_currency").text(cost_array[8]);;
					$("#cost_sum_per_month_currency").text(cost_array[8]);;
					$("#cost_sum_ccu").text(cost_array[9]);;
					$("#cost_sum_per_hour_val").text(cost_array[10]);;
					$("#cost_sum_per_day_val").text(cost_array[11]);;
					$("#cost_sum_per_month_val").text(cost_array[12]);;

//					alert(data);

				}
			});

		}


</script>

<style type="text/css">
div.panel,p.flip {
	margin:0px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	border:solid 1px #c3c3c3;
}
div.panel {
	height:320px;
	display:none;
}

.htmlobject_box .left {
    width: 140px;
}
.htmlobject_box input[type="text"] {
    width: 100px;
}

</style>

<div id="content_container">

<h1>{title}</h1>

<form action="{thisfile}">
{form}

<div id='cloud_request_left_column'>
	<h3>{components}</h3>
	{cloud_profile_list}
	{cloud_image_select}

	<div id='cloud_request_left_column_left_column'>

		<h3><nobr>{applications}</nobr></h3>
		<div id='cloud_scrollable_application_checkboxes'>
			{cloud_puppet_select_0}
			{cloud_puppet_select_1}
			{cloud_puppet_select_2}
			{cloud_puppet_select_3}
			{cloud_puppet_select_4}
			{cloud_puppet_select_5}
			{cloud_puppet_select_6}
			{cloud_puppet_select_7}
			{cloud_puppet_select_8}
			{cloud_puppet_select_9}
			{cloud_puppet_select_10}
			{cloud_puppet_select_11}
			{cloud_puppet_select_12}
			{cloud_puppet_select_13}
			{cloud_puppet_select_14}
			{cloud_puppet_select_15}
			{cloud_puppet_select_16}
			{cloud_puppet_select_17}
			{cloud_puppet_select_18}
			{cloud_puppet_select_19}
		</div>
	</div>

	<div id='cloud_request_left_column_right_column'>
		<h3><nobr>{ipaddresses}</nobr></h3>
		<div id='cloud_ip_selects'>
			{cloud_ip_select_0}
			{cloud_ip_select_1}
			{cloud_ip_select_2}
			{cloud_ip_select_3}
		</div>
	</div>

	<div id='cloud_submit'>
		<div style="float:left;">
		{cloud_profile_name}
		</div>
		<div style="float:right;">{submit}</div>
	</div>

</div>


<div id='cloud_request_right_column'>
	<div class="panel">

		<div id='cloud_request_right_column_left_column'>
			<p><h3>{components_details}</h3></p>
			<br>
			<p>{cloud_virtualization_select}</p>
			<p>{cloud_kernel_select}</p>
			<p>{cloud_memory_select}</p>
			<p>{cloud_cpu_select}</p>
			<p>{cloud_disk_select}</p>
			<p>{cloud_network_select}</p>
			<p>{cloud_ha_select}</p>
			<p>{cloud_hostname_input}</p>

			<div id="cost_applications"><nobr><b>+</b> {cloud_applications}</nobr></div>
		</div>

		<div id='cloud_request_right_column_right_column'>
			<h3>{ccu_per_hour}</h3>
			<div id="cost_resource_type_req_val" class="inline">0</div>
			<div id="cost_kernel_val" class="inline">0</div>
			<div id="cost_memory_val" class="inline">0</div>
			<div id="cost_cpu_val" class="inline">0</div>
			<div id="cost_disk_val" class="inline">0</div>
			<div id="cost_network_val" class="inline">0</div>
			<div id="cost_ha_val" class="inline">0</div>
			<div id="cost_apps_val" class="inline">0</div>
		</div>

		<div id='cloud_request_right_column_bottom_column'>
			<div id="cost_sum_per_hour" class="inline"><small>{per_hour}:</small></div>
			<div id="cost_sum_per_hour_val" class="inline"><small>0</small></div>
			<div id="cost_sum_per_hour_currency" class="inline"><small></small></div>
			<div id="cost_sum_per_day" class="inline"><small>{per_day}:</small></div>
			<div id="cost_sum_per_day_val" class="inline"><small>0</small></div>
			<div id="cost_sum_per_day_currency" class="inline"><small></small></div>
			<div id="cost_sum_per_month" class="inline"><small>{per_month}:</small></div>
			<div id="cost_sum_per_month_val" class="inline"><small>0</small></div>
			<div id="cost_sum_per_month_currency" class="inline"><small></small></div>
			<div id="cost_sum_ccu_total" class="inline"><small>{ccu_total}:</small></div>
			<div id="cost_sum_ccu" class="inline">0</div>
			<div id="cost_sum_ccu_title" class="inline"><small>{ccu_per_hour}</small></div>
		</div>
		
	</div>
	<p class="flip"><b>{show_details}</b></p>
</div>


<div id='cloud_request_system_profile_space'>
	<img id="cloud_request_system_profile_image" src="img/system_profile_small.png" alt="Profile"/>
</div>

</form>

</div>

