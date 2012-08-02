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
-->
<h1><img border=0 src="{baseurl}/plugins/nfs-storage/img/plugin.png"> NFS Volumes on storage {storage_name}</h1>
In case the NFS Storage server is not managed by openQRM please use this form to
 manually create the list of exported paths to server-images on the NFS Storage server.
<br>
<br>
<strong>Please notice that in case a manual configuration exist openQRM will not
 send any automated Storage-authentication commands to this NFS Storage-server !</strong>
<br>
<br>
{back_link}
{storage_table}
<br>
<form action="{formaction}">
<div>
	<div style="float:left;">
		<h4>Exported paths</h4>
		{export_list}
	</div>
	<div style="float:right;">
		<br>
		{exports_list_update_input}
		{hidden_nfs_storage_id}
	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>

	<div style="float:left;">
	{remove}
	</div>
	<div style="float:right;">
	{submit}
	</div>

	<div style="clear:both;line-height:0px;">&#160;</div>
</div>

</form>
