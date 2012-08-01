<!--
/*
# openQRM Enterprise developed by openQRM Enterprise GmbH.
#
# All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
# The latest version of this license can be found here: src/doc/LICENSE.txt
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://openqrm-enterprise.com
#
# Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->
<h1>Please select a Database type<div id="steps"><a href="/openqrm">step 1</a> - <strong>step 2</strong> - step 3</div></h1>
<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>

<div id="form">
	<div id="config_table">{db_table}</div>
	<div id="config_text">Select the Database type to use for<br>storing the openQRM data.</div>
	<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>
</div>

<table id="wait" style="display:none; border:0px none; margin: 0 0 0 20px;">
<tr>
	<td style="vertical-align:middle;border:0px none;">Loading. Please wait ...</td>
	<td style="vertical-align:middle;border:0px none;"><img src="img/loading.gif"></td>
</tr>
</table>

<div id="openqrm_logo">
	<a href="http://www.openqrm.com" target="_BLANK" id="openqrmhref">
		<img src="/openqrm/base/img/logo.png" border="0" alt="Your open-source Cloud computing platform">
	</a>
</div>
<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>


<script type="text/javascript">
button = document.getElementsByTagName('a');
if(button[0]) {
	for(i in button) {
		if(button[i].id != 'openqrmhref') {
			button[i].onclick = function() { wait(); }
		}
	}
}
$('.actiontable input').click(function() { wait(); });
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
</script>
