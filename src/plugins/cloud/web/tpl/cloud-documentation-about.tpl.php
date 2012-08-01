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


<h1><img border=0 src="{baseurl}/plugins/cloud/img/plugin.png"> {title} <small><a href={external_portal_name} target="_BLANK">{external_portal_name}</a></small></h1>


<div id="documentation_content" style="float:left;">


<fieldset>
    <legend>{cloud_documentation_label}</legend>
    {cloud_documentation_intro}
</fieldset>

<fieldset>
    <legend>{cloud_documentation_setup_title}</legend>
    {cloud_documentation_setup}
    <br>
    {cloud_documentation_setup_steps}
</fieldset>

<fieldset>
    <legend>{cloud_documentation_users}</legend>
    {cloud_documentation_create_user}
</fieldset>

<fieldset>
    <legend>{cloud_documentation_ip_management}</legend>
    {cloud_documentation_ip_management_setup}
</fieldset>

<fieldset>
    <legend>{cloud_documentation_soap}</legend>
    {cloud_documentation_api}
</fieldset>

<fieldset>
    <legend>{cloud_documentation_lockfile}</legend>
    {cloud_documentation_lockfile_details}
</fieldset>


</div>


<div id="plugin_content" style="float:right;">

	<fieldset>
		<legend>{cloud_documentation_type_title}</legend>
			<div id="plugin_type" style="float:left;">
			{cloud_documentation_type_content}
			</div>
	</fieldset>


	<fieldset>
		<legend>{cloud_documentation_tested_title}</legend>
			<div id="tested_with" style="float:left;">
			{cloud_documentation_tested_content}
			</div>
	</fieldset>

</div>


