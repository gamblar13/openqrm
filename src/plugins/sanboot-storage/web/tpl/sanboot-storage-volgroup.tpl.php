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
<h1><img src="{baseurl}/plugins/sanboot-storage/img/plugin.png"> {label}</h1>

<div id="form">
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
	{table}

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
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('wait').style.display = 'none';
	document.getElementById('form').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
</script>
