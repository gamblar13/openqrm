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

<h1><img border=0 src="{baseurl}/plugins/nagios3/img/plugin.png"> {label}</h1>
<div id="form">
	<form action="{thisfile}" method="GET">
		{form}
		{select}
		<div style="margin: 15px 0px 15px 35px;">{or_manually_add}</div>
		{manual_port}
		{manual_type}
		{manual_service}
		{manual_description}
		<div class="buttons">{submit}&#160;{cancel}</div>
	</form>
</div>


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
tmp = document.getElementById('tab_{prefix_tab}0');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
	document.getElementById('cancel').style.display = 'none';
}
function cancel() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
</script>
