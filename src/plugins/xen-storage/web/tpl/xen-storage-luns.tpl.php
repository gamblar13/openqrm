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
<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/plugin.png"> Volumes of storage location {xen_storage_location} on storage {storage_name}</h1>
{lun_table}
<br><br>
<form action="{formaction}" method="GET">
<h1>Add new logical volume to storage location {xen_storage_location}</h1>
<div style="float:left;">
{xen_volume_name}
{xen_volume_size}
</div>
{hidden_xen_storage_location}
{hidden_xen_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

