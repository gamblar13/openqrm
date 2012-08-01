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
<h1><img border=0 src="{baseurl}/plugins/aoe-storage/img/plugin.png"> {label}</h1>
<form action="{thisfile}" method="GET">
{form}
<div id="form">
	<div style="float:left;">{name}</div>
	<div style="float:left; width: 250px; margin: 0 0 0 15px;">
		<input type="button" id="namegenerate" onclick="namegen(); return false;" class="password-button" value="{lang_name_generate}" style="display:none;">
	</div>
	<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	{size}
	<div id="buttons">{submit}&#160;{cancel}</div>
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

</form>

<script type="text/javascript">

function namegen() {
	var name = "";
	var name_characters = "0123456789";
	var one_random_char;
	for (j=0; j<6; j++) {
		one_random_char = name_characters.charAt(Math.floor(Math.random()*name_characters.length));
		name += one_random_char;
	}
	document.getElementById('name').value = 'aoe'+name;
}
document.getElementById('namegenerate').style.display = 'inline';
</script>



<script type="text/javascript">
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
