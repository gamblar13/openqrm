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
<h1><img src="/openqrm/base/plugins/lvm-storage/img/volumes.png"> {label}</h1>

<div id="top">
	<div style="width:250px;float:left;">
		<div><b>{lang_id}</b>: {id}</div>
		<div><b>{lang_name}</b>: {name}</div>
		<div><b>{lang_resource}</b>: {resource}</div>
		<div><b>{lang_deployment}</b>: {deployment}</div>
		<div><b>{lang_state}</b>: {state}</div>
	</div>

	<div style="width:300px;float:left;">
		<div><b>{lang_name}</b>: {volgroup_name}</div>
		<div><b>{lang_attr}</b>: {volgroup_attr}</div>
		<div><b>{lang_pv}</b>: {volgroup_pv} / {volgroup_lv} / {volgroup_sn}</div>
		<div><b>{lang_size}</b>: {volgroup_vsize} / {volgroup_vfree}</div>
	</div>

	<div style="float:left;">
		<div id="add">{add}</div>
	</div>
	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>
</div>

<form action="{thisfile}" method="GET">
	<div id="form">
	{table}
	</div>
</form>

<table id="wait" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="../../img/loading.gif"></td>
</tr>
</table>

<table id="cancel" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{canceled}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="../../img/loading.gif"></td>
</tr>
</table>

<script type="text/javascript">
tmp = document.getElementById('add');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
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
button = document.getElementsByName('lvols[action]');
if(button[0]) {
	button[0].onclick = function() { wait(); }
}
$('.pageturn_head a').click(function() { wait(); });
$('.pageturn_bottom a').click(function() { wait(); });
$('.actiontable input').click(function() { wait(); });
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('top').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('wait').style.display = 'none';
	document.getElementById('form').style.display = 'none';
	document.getElementById('top').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
</script>
