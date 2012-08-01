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
	window.onload = function() {

		$("#add_nics").change(function() {
			var nics = $("#add_nics option:selected").val();
			switch (nics) {
				case '0':
					$('#mac1').attr('disabled', 'true');
					$('#mac2').attr('disabled', 'true');
					$('#mac3').attr('disabled', 'true');
					$('#mac4').attr('disabled', 'true');
					$('#type1').attr('disabled', 'true');
					$('#type2').attr('disabled', 'true');
					$('#type3').attr('disabled', 'true');
					$('#type4').attr('disabled', 'true');
					break;
				case '1':
					$('#mac1').removeAttr("disabled");
					$('#mac2').attr('disabled', 'true');
					$('#mac3').attr('disabled', 'true');
					$('#mac4').attr('disabled', 'true');
					$('#type1').removeAttr("disabled");
					$('#type2').attr('disabled', 'true');
					$('#type3').attr('disabled', 'true');
					$('#type4').attr('disabled', 'true');
					break;
				case '2':
					$('#mac1').removeAttr("disabled");
					$('#mac2').removeAttr("disabled");
					$('#mac3').attr('disabled', 'true');
					$('#mac4').attr('disabled', 'true');
					$('#type1').removeAttr("disabled");
					$('#type2').removeAttr("disabled");
					$('#type3').attr('disabled', 'true');
					$('#type4').attr('disabled', 'true');
					break;
				case '3':
					$('#mac1').removeAttr("disabled");
					$('#mac2').removeAttr("disabled");
					$('#mac3').removeAttr("disabled");
					$('#mac4').attr('disabled', 'true');
					$('#type1').removeAttr("disabled");
					$('#type2').removeAttr("disabled");
					$('#type3').removeAttr("disabled");
					$('#type4').attr('disabled', 'true');
					break;
				case '4':
					$('#mac1').removeAttr("disabled");
					$('#mac2').removeAttr("disabled");
					$('#mac3').removeAttr("disabled");
					$('#mac4').removeAttr("disabled");
					$('#type1').removeAttr("disabled");
					$('#type2').removeAttr("disabled");
					$('#type3').removeAttr("disabled");
					$('#type4').removeAttr("disabled");
					break;
			}
		})
		$('#mac1').removeAttr("disabled");
		$('#mac2').attr('disabled', 'true');
		$('#mac3').attr('disabled', 'true');
		$('#mac4').attr('disabled', 'true');
		$('#type1').removeAttr("disabled");
		$('#type2').attr('disabled', 'true');
		$('#type3').attr('disabled', 'true');
		$('#type4').attr('disabled', 'true');

	};


	function namegen() {
		var name = "";
		var name_characters = "0123456789";
		var one_random_char;
		for (j=0; j<6; j++) {
			one_random_char = name_characters.charAt(Math.floor(Math.random()*name_characters.length));
			name += one_random_char;
		}
		document.getElementById('name').value = 'cs'+name;
	}

</script>
<h1><img border=0 src="/openqrm/base/plugins/citrix-storage/img/plugin.png"> {label}</h1>
	<form action="{thisfile}" method="GET">

	<div id="formbox">
	{form}

	<fieldset>
		<legend>{lang_basic}</legend>
		<div style="float:left;">
			{name}
		</div>
		<div style="float:left; width: 250px; margin: 0 0 0 20px;">
			<input type="button" id="namegenerate" onclick="namegen(); return false;" class="password-button" value="{lang_name_generate}" style="display:none;">
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
			{template}
	</fieldset>

	<fieldset>
		<legend>{lang_hardware}</legend>
		{cpu}
		{memory}
		{datastore}		
	</fieldset>

	<fieldset>
		<legend>{add_nics}</legend>

		<fieldset style="">
			<legend>{lang_net_0}</legend>
			{mac}
		</fieldset>

		<div>
			<fieldset style="float:left;">
				<legend>{lang_net_1}</legend>
				{mac1}
			</fieldset>
			<fieldset style="float:right;">
				<legend>{lang_net_2}</legend>
				{mac2}
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>

		<div>
			<fieldset style="float:left;">
				<legend>{lang_net_3}</legend>
				{mac3}
			</fieldset>
			<fieldset style="float:right;">
				<legend>{lang_net_4}</legend>
				{mac4}
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_boot}</legend>
		{boot_order}
	</fieldset>

	<div id="buttons">
	{submit}
	{cancel}
	</div>

</div>
</form>

<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	<table id="wait" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="/openqrm/base/img/loading.gif"></td>
	</tr>
	</table>

	<table id="cancel" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{canceled}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="/openqrm/base/img/loading.gif"></td>
	</tr>
	</table>


<script type="text/javascript">
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('buttons').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('buttons').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
document.getElementById('namegenerate').style.display = 'inline';

</script>
