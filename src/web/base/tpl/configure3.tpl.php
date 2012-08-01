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
<h1>Configure the Database connection<div id="steps"><a href="/openqrm">step 1</a> - <a href="/openqrm/base/configure.php?step=2">step 2</a> - <strong>step 3</strong></div></h1>
<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>

<div id="form">
	<form action="{thisfile}" method="POST">
	<div id="config_table">
		{db_config_table}
		<div style="text-align:right; margin:20px 0 0 0;">
			<input type="hidden" name="step" value="4">
			<input type="submit" name="action" value="initialize" onclick="wait();">
		</div>
	</div>
	<div id="config_text">
	Fill in the Database name, the Database Server and a username plus password
	to setup the Database connection.
	</div>
	<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>
	</form>
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

