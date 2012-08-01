<input id="servertime" type="hidden" value="{timestamp}">
<div class="logo">
	<img src="img/logo.png">
	<div class="openqrmVersion"></div>
</div>


<div class="top">
	<a id="Event_box" href="index.php?base=event&event_filter=error" style="display:none;">Error(s) <span id="events_critical"></span></a>
	<a id="Event_active_box" href="index.php?base=event&event_filter=active" style="display:none;">Active <span id="events_active"></span></a>
	<a id="Support_box" href="http://www.openqrm-enterprise.com" target="_blank">Support</a>
	<a id="Info_box" href="javascript:openPopup()">Info</a>
	<a id="Docu_box" href="index.php?base=zz_documentation&controller=documentation">Documentation</a>
	<a id="Login_box" href="index.php?base=user">{username}</a>
	<div id="Watch"></div>

	<div class="floatbreaker">&#160;</div>

	<div id="language">
		<div>
			<form>
			<img id="language_image" src="img/{userlang}.gif" width="16" height="11" alt="de">
			<input id="username" type="hidden" value="{username}">
			<input id="userlang" type="hidden" value="{userlang}">
			<select id="language_select" name="language" onchange="set_language();">
			<option value="en" class="imagebacked" style="background-image: url(img/en.gif)" {selected_lang_en}>&nbsp;en</option>
			<option value="de" class="imagebacked" style="background-image: url(img/de.gif)" {selected_lang_de}>&nbsp;de</option>
			</select>
			</form>
			<div class="floatbreaker">&#160;</div>
		</div>

		<div class="floatbreaker">&#160;</div>
	</div>

	<div class="floatbreaker">&#160;</div>
</div>

<div class="main">
	<div class="div_box" id="Appliance_box">
		<div class="appliances headline">Appliances</div>
		<div class="appliances active">active <span id="appliances_active">&#160;</span></div>
		<div class="appliances total">total <span id="appliances_total">&#160;</span></div>
		<div class="floatbreaker">&#160;</div>
	</div>
	<div class="div_box" id="Resource_box">
		<div class="resources headline">Resources</div>
		<div class="resources active">active <span id="resources_active">&#160;</span></div>
		<div class="resources error">error <span id="resources_error">&#160;</span></div>
		<div class="resources off">off <span id="resources_off">&#160;</span></div>
		<div class="resources total">total <span id="resources_total">&#160;</span></div>
		<div class="floatbreaker">&#160;</div>
	</div>	
	<div class="floatbreaker">&#160;</div>
</div>




<div id="popupInfo">
	<a id="popupInfoClose">x</a>
	<h1><img src="img/logo.png"> openQRM Community 5.0</h1>
	<div id="infoScrollArea">
		<p id="infoArea">
		</p>
	</div>
</div>
<div id="backgroundPopup"></div>


<script type="text/javascript">
$(document).ready(function(){
	st = parseFloat( $("#servertime").val() ) * 1000;
	$("#Watch").clock({"timestamp":st,"format":"24","calendar":"false"});
	get_top_status();
	$("#popupInfoClose").click(function(){
		disablePopup();
	});
	$("#backgroundPopup").click(function(){
		disablePopup();
	});

});


function get_top_status() {
	$.ajax({
		url: "api.php?action=get_top_status",
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			var status_array = response.split("@");
			var appliance_all = parseInt(status_array[0]);
			var appliance_active = parseInt(status_array[1]);
			var resource_all = parseInt(status_array[2]);
			var resource_active = parseInt(status_array[3]);
			var resource_inactive = parseInt(status_array[4]);
			var resource_error = parseInt(status_array[5]);
			var event_error = parseInt(status_array[6]);
			var event_active = parseInt(status_array[7]);

			$("#appliances_total").html(appliance_all);
			$("#appliances_active").html(appliance_active);

			$("#resources_total").html(resource_all);
			$("#resources_active").html(resource_active);
			$("#resources_off").html(resource_inactive);
			$("#resources_error").html(resource_error);

			$("#events_critical").html(event_error);
			if(event_error > 0) {
				document.getElementById('Event_box').style.display = 'block';
			} else {
				document.getElementById('Event_box').style.display = 'none';
			}

			if(event_active > 0) {
				$("#events_active").html(event_active);
				document.getElementById('Event_active_box').style.display = 'block';
			} else {
				document.getElementById('Event_active_box').style.display = 'none';
			}
		}
	});
	setTimeout("get_top_status()", 5000);
}


function set_language() {
	var username = $("#username").val();
	var selected_lang = $("#language_select").val();

	$.ajax({
		url: "api.php?action=set_language&user=" + username + "&lang=" + selected_lang,
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			window.location.reload();
		}
	});

}


var popupStatus = 0;
function loadPopup(){
	if(popupStatus==0){
		$("#backgroundPopup").css({
			"opacity": "0.7"
		});
		$("#backgroundPopup").fadeIn("slow");
		$("#popupInfo").fadeIn("slow");
		popupStatus = 1;
	}
}

function disablePopup(){
	if(popupStatus==1){
		$("#backgroundPopup").fadeOut("slow");
		$("#popupInfo").fadeOut("slow");
		popupStatus = 0;
	}
}


function centerPopup(){
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $("#popupInfo").height();
	var popupWidth = $("#popupInfo").width();
	$("#popupInfo").css({
		"position": "absolute",
		"top": "120px",
		"left": "400px" 
	});
	$("#backgroundPopup").css({
		"height": windowHeight
	});
}


function openPopup() {
	centerPopup();
	loadPopup();
	get_info_box();
}



function get_info_box() {
	$.ajax({
		url: "api.php?action=get_info_box",
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {

			$("#infoArea").html(response);
		}
	});
}


</script>
