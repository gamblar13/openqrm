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
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/plugin.png"> Xen Storage VM Manager</h1>

<div style="float:left;">
{xen_server_table}
</div>

<div style="float:left;">
<h4>Xen vms on resource {xen_server_id}/{xen_server_name}</h4>
{xen_vm_table}
</div>

</form>

