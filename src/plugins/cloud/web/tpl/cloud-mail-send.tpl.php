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

<form action="{thisfile}">
{form}
<div id='cloud_mail'>

	<fieldset>
		<legend>{cloud_mail_data}</legend>
		<div style="float:left;">
			{cloud_mail_to}
			<br>
			{cloud_mail_subject}
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		<div style="float:left;">
			{cloud_mail_body}
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

	</fieldset>

</div>

<div id='cloud_mail_bottom'>
	<div id='cloud_mail_submit'>
		{submit}
		<br><br>
	</div>
</div>

</form>
