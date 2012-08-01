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
<h1><img border=0 src="{baseurl}/img/resource.png"> {label}</h1>
{form_docu}
<br>
<br>
<b>{form_auto_resource}</b>
<br>
<br>

<form action="{thisfile}" method="GET">
<div style="float:left;">
	<h3>{form_add_resource}</h3>
	{form}
	{name}
	{ip}
	{mac}
</div>
<div style="text-align:center;">
	<br>
	<br>
	<br>
	<br>
	{submit} {cancel}
</div>
</form>

