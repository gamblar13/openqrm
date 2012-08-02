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
//-->
<script type="text/javascript">
MousePosition.init();
function tr_hover() {}
function tr_click() {}
var filepicker = {
	init : function() {
		mouse = MousePosition.get();
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
		document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
		document.getElementById('filepicker').style.display = 'block';
		$.ajax({
			url: "{baseurl}/plugins/xen-storage/api-vm.php?appliance_id={appliance_id}&{actions_name}=filepicker&path=/",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	browse : function(target) {
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		$.ajax({
			url: "{baseurl}/plugins/xen-storage/api-vm.php?appliance_id={appliance_id}&{actions_name}=filepicker&path="+target,
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	insert : function(value) {
		document.getElementById('iso_path').value = value;
		document.getElementById('filepicker').style.display = 'none';
	}
}

var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('vnc').value = pass;
		document.getElementById('vnc_1').value = pass;
	},
	toggle : function() {
		vnc = document.getElementById('vnc');
		but = document.getElementById('passtoggle');
		if(vnc.type == 'password') {
			but.value = "{lang_password_hide}";
			np = vnc.cloneNode(true);
			np.type='text';
			vnc.parentNode.replaceChild(np,vnc);
		}
		if(vnc.type == 'text') {
			but.value = "{lang_password_show}";
			np = vnc.cloneNode(true);
			np.type='password';
			vnc.parentNode.replaceChild(np,vnc);
		}
	}
}
function namegen() {
	var name = "";
	var name_characters = "0123456789";
	var one_random_char;
	for (j=0; j<10; j++) {
		one_random_char = name_characters.charAt(Math.floor(Math.random()*name_characters.length));
		name += one_random_char;
	}
	document.getElementById('name').value = 'kvm_'+name;
}
</script>

<h1><img border=0 src="{baseurl}/plugins/xen-storage/img/plugin.png"> {label}</h1>

<div id="formbox">
	<form action="{thisfile}" method="GET">
	{form}

	<fieldset>
		<legend>{lang_basic}</legend>
		<div style="float:left;">
			{name}
		</div>
		<div style="float:right; width: 250px;">
			&#160;
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	</fieldset>

	<fieldset>
		<legend>{lang_hardware}</legend>
		{cpus}
		{memory}
	</fieldset>

	<fieldset>
		<legend>{lang_net}</legend>

		<fieldset style="">
			<legend>{lang_net_0}</legend>
			{mac}
			{bridge}
		</fieldset>

		<div>
			<fieldset style="float:left;">
				<legend>{lang_net_1}</legend>
				{net1}
				{mac1}
				{bridge1}
			</fieldset>
			<fieldset style="float:right;">
				<legend>{lang_net_2}</legend>
				{net2}
				{mac2}
				{bridge2}
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>

		<div>
			<fieldset style="float:left;">
				<legend>{lang_net_3}</legend>
				{net3}
				{mac3}
				{bridge3}
			</fieldset>
			<fieldset style="float:right;">
				<legend>{lang_net_4}</legend>
				{net4}
				{mac4}
				{bridge4}
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_boot}</legend>
		{boot_cd}
		<div>
			{boot_iso}
			{boot_iso_path}
			<input type="button" id="browsebutton" onclick="filepicker.init(); return false;" class="browse-button" value="{lang_browse}" style="display:none;">
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
		{boot_net}
		{boot_local}
	</fieldset>

	<div id="buttons">
	{submit}
	{cancel}
	</div>

	</form>
</div>

	<table id="wait" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="{baseurl}/img/loading.gif"></td>
	</tr>
	</table>

	<table id="cancel" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{canceled}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="{baseurl}/img/loading.gif"></td>
	</tr>
	</table>


<div id="filepicker" style="display:none;position:absolute;top:15;left:15px;"  class="function-box">
	<div class="functionbox-capation-box" 
			id="caption"
			onclick="MousePosition.init();"
			onmousedown="Drag.init(document.getElementById('filepicker'));"
			onmouseup="document.getElementById('filepicker').onmousedown = null;">
		<div class="functionbox-capation">
			{lang_browser}
			<input type="button" id ="close" class="functionbox-closebutton" value="X" onclick="document.getElementById('filepicker').style.display = 'none';">
		</div>
	</div>
	<div id="canvas"></div>
</div>



<script type="text/javascript">
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
tmp = document.getElementById('tab_{prefix_tab}2');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
function wait() {
	document.getElementById('formbox').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('wait').style.display = 'none';
	document.getElementById('formbox').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}

document.getElementById('browsebutton').style.display = 'inline';
document.getElementById('passgenerate').style.display = 'inline';
document.getElementById('passtoggle').style.display = 'inline';
</script>
