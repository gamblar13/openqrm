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


<h1><img border=0 src="{baseurl}/plugins/cloud/img/plugin.png"> {cloud_documentation_soap_title}</h1>
<form action="{thisfile}">
    
<div id='cloud_soap_top'>
    <fieldset>
	    <legend>{cloud_documentation_soap_design_title}</legend>
	    {cloud_documentation_soap_design}
    </fieldset>
</div>
    
<div id='cloud_soap_base'>

    <div id='cloud_soap_left'>
	<fieldset>
	    <legend>{cloud_documentation_soap_admin_label}</legend>
	    {cloud_documentation_soap_admin_functions}
	    <br>
	    {cloud_documentation_soap_admin_function_list}
	    <br><br>
	    {cloud_documentation_soap_admin_wsdl}
	</fieldset>
    </div>
    
    <div id='cloud_soap_right'>

	<fieldset>
	    <legend>{cloud_documentation_soap_user_label}</legend>
	    {cloud_documentation_soap_user_functions}
	    <br>
	    {cloud_documentation_soap_user_function_list}
	    <br><br>
	    {cloud_documentation_soap_user_wsdl}
	</fieldset>

    </div>
    
    <div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
    
</div>

</form>

