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

		function updateProgressBars() {

		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_dc_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				var data_array = data.split(',');
				$("#progressbar_dc_load_overall_val").html(data_array[0]);
				$("#progressbar_dc_load_overall").progressbar("option", "value", data_array[0]*10);

				$("#progressbar_storage_load_overall_val").html(data_array[1]);
				$("#progressbar_storage_load_overall").progressbar("option", "value", data_array[1]*10);
				$("#progressbar_storage_load_peak_val").html(data_array[2]);
				$("#progressbar_storage_load_peak").progressbar("option", "value", data_array[2]*10);

				$("#progressbar_appliances_load_overall_val").html(data_array[3]);
				$("#progressbar_appliances_load_overall").progressbar("option", "value", data_array[3]*10);
				$("#progressbar_appliances_load_peak_val").html(data_array[4]);
				$("#progressbar_appliances_load_peak").progressbar("option", "value", data_array[4]*10);

				$("#resources_all").html(data_array[5]);
				$("#resources_all_physical").html(data_array[6]);
				$("#resources_all_virtual").html(data_array[7]);

				$("#resources_available").html(data_array[8]);
				$("#resources_available_physical").html(data_array[9]);
				$("#resources_available_virtual").html(data_array[10]);
				$("#resources_in_error").html(data_array[11]);

				$("#appliance_error").html(data_array[12]);

				$("#storage_error").html(data_array[13]);
			}
		});


		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_event_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				$("#events_summary").html(data);

			}
		});

		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_appliance_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				$("#appliance_summary").html(data);

			}
		});

		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_resource_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				$("#resource_summary").html(data);

			}
		});

		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_storage_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				$("#storage_summary").html(data);

			}
		});

		$.ajax({
			url : "server/aa_server/dc-overview.php?action=get_cloud_status",
			type: "POST",
			cache: false,
			async: false,
			dataType: "html",
			success : function (data) {
				$("#cloud_summary").html(data);

			}
		});

		setTimeout(updateProgressBars, 10000);

		}




		window.onload = function() {

			$("#progressbar_dc_load_overall").progressbar({ value: 0 });
			$("#progressbar_dc_load_peak").progressbar({ value: 0 });

			$("#progressbar_storage_load_overall").progressbar({ value: 0 });
			$("#progressbar_storage_load_peak").progressbar({ value: 0 });

			$("#progressbar_appliances_load_overall").progressbar({ value: 0 });
			$("#progressbar_appliances_load_peak").progressbar({ value: 0 });
			setTimeout(updateProgressBars, 1000);

			$('#carousel').Carousel(
				{
					itemWidth: 130,
					itemHeight: 80,
					itemMinWidth: 100,
					items: 'a',
					reflections: 0,
					rotationSpeed: 0.5
				}
			);

			$.ImageBox.init(
				{
					loaderSRC: 'img/loading.gif',
					closeHTML: '<img src="img/close.png" />'
				}
			);
		};
	</script>
	<style type="text/css" media="screen">
		* {
			margin: 0;
			padding: 0;
		}

		.htmlobject_table {
			width: 800px!important;
		}

		.htmlobject_tab_box {
			width:1045px;
		}
		hr {
			width: 350px;
		}
		img {
			border: none;
		}

		h3.resources, h3.appliances, h3.storage {
			background: no-repeat;
			padding: 5px 0 10px 40px;
		}

		h3.resources {
			background-image: url(/openqrm/base/img/menu/resource.png);
			width:220px;
		}
		h3.appliances {
			background-image: url(/openqrm/base/img/appliance.png);
			width:220px;
		}
		h3.storage {
			background-image: url(/openqrm/base/img/storage.png);
			width:220px;
		}

		#ImageBoxOverlay {
			background-color: #999999;
			z-index: 1000;
		}
		#ImageBoxOuterContainer {
			z-index: 1000;
		}
		#ImageBoxCaption {
			background-color: #F4F4EC;
			width: 900px!important;
		}
		#ImageBoxContainer {
			width: 900px!important;
			height: 250px;
			background-color: #F4F4EC;
		}
		#ImageBoxCaptionText {
			font-weight: bold;
			padding-bottom: 5px;
			font-size: 13px;
			color: #000;
			width: 900px!important;
		}
		#ImageBoxCaptionImages {
			margin: 0;
			width: 900px!important;

		}
		#ImageBoxNextImage {
			background-image: url(/openqrm/base/img/spacer.gif);
			background-color: transparent;
		}
		#ImageBoxPrevImage {
			background-image: url(/openqrm/base/img/spacer.gif);
			background-color: transparent;
		}
		#ImageBoxNextImage:hover {
			background-repeat:	no-repeat;
			background-position: right top;
		}
		#ImageBoxPrevImage:hover {
			background-repeat:	no-repeat;
			background-position: left bottom;
		}

		#progressbar_dc_load_overall, #progressbar_storage_load_overall, #progressbar_storage_load_peak, #progressbar_appliances_load_overall, #progressbar_appliances_load_peak {
			width: 160px;
			height: 10px;
			position: relative;
			top: -5px;
			left: 0px;
		}
		#progressbar_dc_load_overall {
			width: 260px;
		}

		#progressbar_dc_load_overall_val, #progressbar_storage_load_overall_val, #progressbar_storage_load_peak_val, #storage_error, #progressbar_appliances_load_overall_val, #progressbar_appliances_load_peak_val, #appliance_error, #resources_all, #resources_all_physical, #resources_all_virtual, #resources_available, #resources_available_physical, #resources_available_virtual, #resources_in_error {
			width: 30px;
			height: 10px;
			position: relative;
			top: -15px;
			left: 230px;
		}
		#carousel_content {
			left: 0px;
			position: relative;
			top: -150px!important;
			width: 450px;
		}
		#carousel {
			background: url("/openqrm/base/img/background.png") no-repeat scroll 90px 60px #FFFFFF;
			height: 220px;
			left: 290px;
			position: absolute;
			top: 180px;
			width: 450px;
		}
		#carousel a {
			position: absolute;
			width: 110px;
			color: #666666;
			text-decoration: none;
			font-weight: bold;
			text-align: center;

		}
		#carousel a .label {
			display: block;
			clear: both;

		}

	</style>

	<h1>{title}</h1>

	<div id="carousel_content">
		<div id="carousel">
		<a href="/openqrm/base/img/transition.png" title="<div id=events_summary><img src='/openqrm/base/img/loading.gif'></div>" rel="imagebox">
			<img src="/openqrm/base/img/transition.png" title="Data-Center Events" width="30%" />
			<span class="label">Events</span>
		</a>
		<a href="/openqrm/base/img/storage.png" title="<div id=storage_summary><img src='/openqrm/base/img/loading.gif'></div>" rel="imagebox">
			<img src="/openqrm/base/img/storage.png" title="Storage Network" width="30%" />
			<span class="label">Storage</span>
		</a>
		<a href="/openqrm/base/img/appliance.png" title="<div id=appliance_summary><img src='/openqrm/base/img/loading.gif'></div>" rel="imagebox">
			<img src="/openqrm/base/img/appliance.png" title="Appliances" width="30%" />
			<span class="label">Appliances</span>
		</a>
		<a href="/openqrm/base/img/cloud.png" title="<div id=cloud_summary><img src='/openqrm/base/img/loading.gif'></div>" rel="imagebox">
			<img src="/openqrm/base/img/cloud.png" title="openQRM Cloud" width="30%" />
			<span class="label">Cloud</span>
		</a>
		<a href="/openqrm/base/img/resource.png" title="<div id=resource_summary><img src='/openqrm/base/img/loading.gif'></div>" rel="imagebox">
			<img src="/openqrm/base/img/resource.png" title="Data-Center Resources" width="30%" />
			<span class="label">Resources</span>
		</a>
		</div>
	</div>

	{datacenter_load_overall}: <div id="progressbar_dc_load_overall_val"></div>
	<div id="progressbar_dc_load_overall"></div>

	<h3 class="resources">{resource_overview}</h3>
	{resource_load_overall}: <div id="resources_all"></div>
	{resource_load_physical}: <div id="resources_all_physical"></div>
	{resource_load_vm}: <div id="resources_all_virtual"></div>
	{resource_available_overall}: <div id="resources_available"></div>
	{resource_available_physical}: <div id="resources_available_physical"></div>
	{resource_available_vm}: <div id="resources_available_virtual"></div>
	{resource_error_overall}: <div id="resources_in_error"></div>

	<div class="left" style="display: block; width: 45%; float: left;">
		<h3 class="appliances">{appliance_overview}</h3>
		{appliance_load_overall}: <div id="progressbar_appliances_load_overall_val"></div>
		<div id="progressbar_appliances_load_overall"></div>
		{appliance_load_peak}: <div id="progressbar_appliances_load_peak_val"></div>
		<div id="progressbar_appliances_load_peak"></div>
		{appliance_error_overall}: <div id="appliance_error"></div>
	</div>
	<div class="right" style="display: block; width: 45%; float: right;">
		<h3 class="storage">{storage_overview}</h3>
		{storage_load_overall}: <div id="progressbar_storage_load_overall_val"></div>
		<div id="progressbar_storage_load_overall"></div>
		{storage_load_peak}: <div id="progressbar_storage_load_peak_val"></div>
		<div id="progressbar_storage_load_peak"></div>
		{storage_error_overall}: <div id="storage_error"></div>
	</div>



