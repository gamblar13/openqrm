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
<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/plugin.png"> Xen-Storage plugin</h1>
<br>
The 'xen-storage' plugin is a combination of the "lvm-storage" and the "xen" plugin.
It provides a new storage type "xen-storage" based on lvm which is used a local-disk device
for Xen virtual machines on the same system.
<br>
<br>
<b>Please notice :
<br>
This plugin provides support to manage Xen virtual machines in the "common" way.
<br>
That means that the Xen vms are using local-disks which are logical volumes on the Xen-Storage Host.
<br>
This results in a dependency to "local-disk" devices on the Xen-Storage Host.
<br>
=> the Xen VMs depends on the logical volumes provides by Xen-Storage Host
<br>
=> VMs must be on the same Xen-Storage host where the logical volume (the VMs root-disk) is located
</b>
<br>
<br>
<b>Requirements :</b>
<br>
<ul type="disc">
	<li>A server for the Xen-Storage Host <br>(this can be a remote system integrated into openQRM e.g. via the "local-server" plugin or the openQRM server itself)</li>
	<li>The server needs VT (Virtualization Technology) Support in its CPU (requirement for Xen)</li>
	<li>lvm2 tools installed</li>
	<li>One (or more) lvm volume group(s) with free space dedicated for the Xen VM storage</li>
	<li>Xen installed</li>
	<li>One or more bridges enabled for the Xen virtual machines</li>
</ul>


<br>
<b>1. Xen Storage Management :</b>
<br>

<ul type="disc">
	<li>Create a new storage from type "xen-storage"</li>
	<li>Create a new logical volume on this storage</li>
	<li><b>Use the "local-storage" plugin to populate the new logical volume<br>
	or use the "linuxcoe-plugin" to automatically install a Linux distribution on it.<br>
	Another option is to connect to the VMs VNC console and install an OS in the regular way.</b></li>
	<li>Create an Image using the new created logical volume as root-device</li>
</ul>
Result is an openQRM Image (server-template) which can be deployed to a Xen-Storage VM
(on the same system) via an Appliance.
<br>
<br>
<b>2. Xen (Storage) VM Management :</b>
<br>
<ul type="disc">
	<li>Create a new appliance and set its resource type to "Xen-Storage Host"</li>
	<li>Create and manage Xen virtual machines via the Xen-Storage VM Manager</li>
</ul>
This results in new (idle) resources in openQRM which can be deployed with Xen-Storage volumes
(on the same system) via an Appliance.
<br>
<br>

<b>3. Xen Storage Deployment :</b>
<br>
<ul type="disc">
	<li>Create a new appliance</li>
	<li>Select an idle resource with the type "Xen-Storage VM"</li>
	<li>Select an "Xen-Storage" Image (on the same sytem as the idle resource)</li>
	<li>Set the resource type of the appliance to "Xen-Storage VM"</li>
	<li>Start the appliance</li>
</ul>
This step will "assign" the logical volume on the Xen-Storage Host as the local-disk and
boot device to the Xen-Storage VM (on the same system). The VM now boots up locally
from the logical volume specified by the Image.
<br>
<br>

<b>Requirements for VM live-migration:</b>
<br>
<ul>
<li>
Shared storage between the Xen-Storage Hosts for the location of the VM config files (/var/lib/xen-storage/openqrm)
</li>
<li>
Shared LVM volume group between the Xen-Storage Hosts
</li>
</ul>

<br>
<br>
<hr>
<br>


