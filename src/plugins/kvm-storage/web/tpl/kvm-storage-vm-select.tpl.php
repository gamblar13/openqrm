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
<h1><img border=0 src="{baseurl}/plugins/kvm-storage/img/plugin.png"> {label}</h1>
{table}

<table id="wait" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{please_wait}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="{baseurl}/img/loading.gif"></td>
</tr>
</table>

<script type="text/javascript">
function wait() {
	document.getElementById('Tabelle').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
</script>
