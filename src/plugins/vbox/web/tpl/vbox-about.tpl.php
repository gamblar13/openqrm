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

<div style="float:left;">

<h1><img border=0 src="/openqrm/base/plugins/vbox/img/plugin.png"> VirtualBox plugin</h1>
<strong>This plugin is tested with VirtualBox version 3.1.2</strong>
<br>
<br>
The VirtualBox plugin adds support for VirtualBox-Virtualization to openQRM.
 Appliances with the resource-type 'VirtualBox Host' are listed in the VirtualBox-Manager and
 can be managed via the openQRM GUI. Additional to the regular partition commands
 like create/start/stop/remove the VirtualBox-plugin provides a configuration form per vm
 to re-configure the partition as needed (e.g. adding a virtual network card or harddisks).
<br>
<br>
Hint:
<br>
The openQRM-server itself can be used as a resource for an VirtualBox-Host appliance.
 In this case network-bridging should be setup on openQRM-server system before
 installing openQRM. At least an "internal" bridge for the openQRM management network
 is needed. The name for this bridge can be configured in the VirtualBox plugin-configuration file
 via the parameter OPENQRM_PLUGIN_VBOX_BRIDGE_NET.
<br>
<br>
Additional an external bridges (e.g. pointing to the internet) can be setup and configured
 via the OPENQRM_PLUGIN_VBOX_BRIDGE_NET1-4 parameter in the VirtualBox plugin-configuration file.
<br>
openQRM then will create every first (virtual) network-card for the VirtualBox vms on the internal
 bridge and every other on the external one. With this 2-bridge setup every vm will then
 have its first nic pointing to the openQRM management network (doing the pxe-boot)
 and every other nic will point e.g. to the internet.
<br>
<br>
<br>
<b>How to use :</b>
<br>

<ul>
<li>
Create an appliance and set its resource-type to 'VirtualBox Host'
</li><li>
Use the 'VM Manager' in the Vbox-Plugin menu to create a new Vbox-Server virtual-machines on the Host
</li><li>
 The created Vbox-Server vm is then booting into openQRM as regular resources
</li>
</ul>
<br>

</div>
