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
<h1><img src="{baseurl}/plugins/hybrid-cloud/img/plugin.png"> {label}</h1>
<form action="{thisfile}" method="POST">
{form}
<div id="form" class="account">

	<div style="float:left;">
		<div style="float:left;">
			{rc_config}
		</div>
		<div style="float:left;">
			<input type="button" id="rcbutton" onclick="filepicker.init('rc_config'); return false;" class="browse-button" value="{lang_browse}" style="display:none;">
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

		<div style="float:left;">
			{ssh}
		</div>
		<div style="float:left;">
			<input type="button" id="sshbutton" onclick="filepicker.init('ssh'); return false;" class="browse-button" value="{lang_browse}" style="display:none;">
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

		{type}
		{description}
		<div id="buttons" style="margin: 10px 0 0 0;">{submit}&#160;{cancel}</div>

	</div>
	<div style="float:left; width: 350px; margin: 0 0 0 30px;">
		<b>{label_help}</b><br>
		{lang_help}
	</div>
	<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

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

</form>


<script type="text/javascript">
MousePosition.init();
function tr_hover() {}
function tr_click() {}
var filepicker = {
	target : null,
	init : function(target) {
		this.target = target;
		mouse = MousePosition.get();
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
		document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
		document.getElementById('filepicker').style.display = 'block';
		$.ajax({
			url: "{baseurl}/plugins/hybrid-cloud/api.php?{actions_name}=filepicker&path=/",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	browse : function(target) {
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		$.ajax({
			url: "{baseurl}/plugins/hybrid-cloud/api.php?{actions_name}=filepicker&path="+target,
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	insert : function(value) {
		document.getElementById(this.target).value = value;
		document.getElementById('filepicker').style.display = 'none';
	}
}
document.getElementById('rcbutton').style.display = 'inline';
document.getElementById('sshbutton').style.display = 'inline';


tmp = document.getElementById('tab_{prefix_tab}0');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
</script>
