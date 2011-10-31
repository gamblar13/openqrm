<!--
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
//-->

<script type="text/javascript">


	window.onload = function() {

		$("#add_nics").change(function() {
			nic_number_changed();
		})
		nic_number_changed();
	};


	function nic_number_changed() {
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

	}



</script>


<h1><img border=0 src="/openqrm/base/plugins/vmware-esx/img/manager.png"> {label}</h1>
<form action="{thisfile}" method="GET">
{form}
<div id="form">

<div id='left_column'>
{name}
<hr>
{memory}
{cpu}
<hr>
{disk}
{datastore}
<hr>
{type}
<hr>
{vnc}

{vm_mac}
{vm_id}

</div>

<div id='center_column'>
{add_nics}
<hr>
{mac1}
{type1}
<hr>
{mac2}
{type2}
<hr>
{mac3}
{type3}
<hr>
{mac4}
{type4}
</div>

<div id='right_column'>
{boot_order}
<hr>
</div>


</div>
<div id="buttons">
{submit}
{cancel}
</div>

	<table id="wait" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="img/loading.gif"></td>
	</tr>
	</table>

	<table id="cancel" style="display:none; border:0px none;">
	<tr>
		<td style="vertical-align:middle;border:0px none;">{canceled}</td>
		<td style="vertical-align:middle;border:0px none;"><img src="img/loading.gif"></td>
	</tr>
	</table>

</form>

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
</script>
