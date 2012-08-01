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
<h1><img border=0 src="{baseurl}/img/appliance.png"> {label}</h1>
<div id="form">
	<form action="{thisfile}" method="POST">
	<div style="float:left;">
		{form}
		{cpus}
		{cpuspeed}
		{cpumodel}
		{nics}
		{memory}
		{swap}
		{resource}
		{image}
		{kernel}
		{virtualization}
		{capabilities}
		{comment}
		<div id="buttons">
			{submit}
			{cancel}
		</div>
	</div>
	<div style="float:left;width:200px;" class="dirbox">
		<div style="margin:0 0 0 15px;">
			<h3 onclick="$('#list1').slideToggle('slow');"><img src="{baseurl}/img/ha.png" style="width: 16px; height: 16px;" alt="ha"> {lang_ha}</h3>
			<div class="dirlist" id="list1">
				{plugin_ha}
			</div>
			<script type="text/javascript">$('#list1').slideToggle('fast');</script>

			<h3 onclick="$('#list2').slideToggle('slow');"><img src="{baseurl}/img/datacenter.png" style="width: 16px; height: 16px;" alt="network"> {lang_net}</h3>
			<div class="dirlist" id="list2">
				{plugin_net}
			</div>
			<script type="text/javascript">$('#list2').slideToggle('fast');</script>

			<h3 onclick="$('#list3').slideToggle('slow');"><img src="{baseurl}/img/user.png" style="width: 16px; height: 16px;" alt="management"> {lang_mgmt}</h3>
			<div class="dirlist" id="list3">
				{plugin_mgmt}
			</div>
			<script type="text/javascript">$('#list3').slideToggle('fast');</script>

			<h3 onclick="$('#list4').slideToggle('slow');"><img src="{baseurl}/img/monitoring.png" style="width: 16px; height: 16px;" alt="monitoring"> {lang_moni}</h3>
			<div class="dirlist" id="list4">
				{plugin_moni}
			</div>
			<script type="text/javascript">$('#list4').slideToggle('fast');</script>

			<h3 onclick="$('#list5').slideToggle('slow');"><img src="{baseurl}/img/manage.png" style="width: 16px; height: 16px;" alt="misc"> {lang_dep}</h3>
			<div class="dirlist" id="list5">
				{plugin_dep}
			</div>
			<script type="text/javascript">$('#list5').slideToggle('fast');</script>

			<h3 onclick="$('#list6').slideToggle('slow');"><img src="{baseurl}/img/manage.png" style="width: 16px; height: 16px;" alt="misc"> {lang_misc}</h3>
			<div class="dirlist" id="list6">
				{plugin_misc}
			</div>
			<script type="text/javascript">$('#list6').slideToggle('fast');</script>

			<h3 onclick="$('#list7').slideToggle('slow');"><img src="{baseurl}/img/enterprise.png" style="width: 16px; height: 16px;" alt="misc"> {lang_enter}</h3>
			<div class="dirlist" id="list7">
				{plugin_enter}
			</div>
			<script type="text/javascript">$('#list7').slideToggle('fast');</script>
		</div>
	</div>
	<div class="floatbreaker">&#160;</div>

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

</form>

<script type="text/javascript">
tmp = document.getElementById('tab_{prefix_tab}0');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { cancel(); };
}
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
