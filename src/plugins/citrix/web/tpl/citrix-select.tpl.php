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
-->

<h1><img border=0 src="/openqrm/base/plugins/citrix/img/plugin.png"> {label}</h1>
{table}

<table id="wait" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="/openqrm/base/img/loading.gif"></td>
</tr>
</table>

<script type="text/javascript">
tmp = document.getElementById('tab_{prefix_tab}0');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
button = document.getElementsByName('management[action]');
if(button[0]) {
	button[0].onclick = function() { wait(); }
}
$('.pageturn_head a').click(function() { wait(); });
$('.pageturn_bottom a').click(function() { wait(); });
$('.actiontable input').click(function() { wait(); });
function wait() {
	document.getElementById('Tabelle').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
</script>
