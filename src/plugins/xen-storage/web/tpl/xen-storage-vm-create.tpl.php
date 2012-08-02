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

<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/plugin.png"> Xen Storage Create VM</h1>

<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

<h4>Add new VM to Xen Host id {xen_server_id}</h4>
<div style="float:left;">
{xen_server_name}

<h4>VM Configuration</h4>

<hr>
<b>Virtual Hardware :</b>
<br />
<br />
{xen_server_cpus}
{xen_server_ram}
<hr>

<b>Network :</b>
<br />
<br />
{xen_server_mac}
=> connected to <select name="xen_vm_bridge">
    <option value="{xen_server_bridge_int}">{xen_server_bridge_int} (internal bridge)</option>
    <option value="{xen_server_bridge_ext}">{xen_server_bridge_ext} (external bridge)</option>
    </select>
<br />

<hr>
<b>Boot from :</b>
<br />
<br />
CD-ROM <input type="radio" name="xen_vm_boot_dev" value="cdrom" checked="checked" />  (local CD-ROM Device on the Xen storage)
<br />
ISO Image <input type="radio" name="xen_vm_boot_dev" value="iso" /> <input type="text" name="xen_vm_boot_iso" value="[/dev/loopX on the Xen storage]" size="30" />
<br />
Network <input type="radio" name="xen_vm_boot_dev" value="network" />
<br />
Local Disk <input type="radio" name="xen_vm_boot_dev" value="local" />
<br />
<br />

</div>


<div style="float:right;">
</div>

{hidden_xen_server_id}

<div style="clear:both;line-height:0px;">&#160;</div>

<div style="text-align:center;">{submit}</div>
<br>
</div>

</form>

