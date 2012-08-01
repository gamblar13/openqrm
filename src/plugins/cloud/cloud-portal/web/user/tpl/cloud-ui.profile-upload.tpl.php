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

{?}

-->


<div id="content_container">
	<h2>{title}</h2>
	
	<small>{cloud_ui_upload_icon_description}</small>
	
	<div id="iconuploadbox">
		<form action="{thisfile}" method="post" enctype="multipart/form-data">
		{form}
		{cloud_icon}

		{submit}{cancel}
		</form>
	</div>
</div>