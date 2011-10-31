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
<h1><img border=0 src="/openqrm/base/plugins/vmware-esx/img/manager.png"> {label}</h1>
<form action="{thisfile}" method="GET">
{form}
<div id="form">
{name}
{vlan}
{vs_name}

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
