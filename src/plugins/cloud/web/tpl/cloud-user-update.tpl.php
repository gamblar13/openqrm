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

 to debug add {?}


-->
<h1><img border=0 src="/openqrm/base/img/user.png"> {title} <small><a href={external_portal_name} target="_BLANK">{external_portal_name}</a></small></h1>
<div id="form" class="useredit">
<form action="{thisfile}">
	{form}

	<fieldset style="float:left;">
	    <legend>{cloud_user_data} {cloud_user_name}</legend>

		<div style="float:left; margin: 0 0 10px 0;">
			{cloud_user_password}
		</div>
		<div style="float:left; margin: 0 0 0 20px;">
			<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" class="password-button" value="{lang_password_generate}" style="display:none;"><br>
			<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" class="password-button" value="{lang_password_show}" style="display:none;">
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

	    {cloud_usergroup_id}
	    {cloud_user_email}
	    {cloud_user_forename}
	    {cloud_user_lastname}
	    {cloud_user_street}
	    {cloud_user_city}
	    {cloud_user_country}
	    {cloud_user_phone}
	    {cloud_user_ccunits}
	    {cloud_user_lang}
	</fieldset>

	<fieldset style="float:left; margin: 0 0 0 20px;">
	    <legend>{cloud_user_permissions}</legend>
		{cloud_user_resource_limit}
		{cloud_user_memory_limit}
		{cloud_user_disk_limit}
		{cloud_user_cpu_limit}
		{cloud_user_network_limit}
		<small>{cloud_user_limit_explain}</small>
	</fieldset>

	<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	<div id="buttons">{submit}&#160;{cancel}</div>

</form>
</div>

<script type="text/javascript">
var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('cloud_user_password').value = pass;
	},
	toggle : function() {
		vnc = document.getElementById('cloud_user_password');
		but = document.getElementById('passtoggle');
		if(vnc.type == 'password') {
			but.value = "{lang_password_hide}";
			np = vnc.cloneNode(true);
			np.type='text';
			vnc.parentNode.replaceChild(np,vnc);
		}
		if(vnc.type == 'text') {
			but.value = "{lang_password_show}";
			np = vnc.cloneNode(true);
			np.type='password';
			vnc.parentNode.replaceChild(np,vnc);
		}
	}
}

check = document.getElementById('cloud_user_password');
if(check) {
	document.getElementById('passgenerate').style.display = 'inline';
	document.getElementById('passtoggle').style.display = 'inline';
}
</script>

<script type="text/javascript">
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
function wait() {
	document.getElementById('formbox').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
function cancel() {
	document.getElementById('wait').style.display = 'none';
	document.getElementById('formbox').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}

var p = document.getElementById('cloud_user_password').value;
if (p.length > 0) {
	document.getElementById('passgenerate').style.display = 'inline';
	document.getElementById('passtoggle').style.display = 'inline';
}
</script>
