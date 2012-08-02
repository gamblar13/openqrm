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
<h1><img border=0 src="/openqrm/base/plugins/vmware-esx/img/plugin.png"> VMware ESX plugin</h1>
The VMware ESX plugin is designed for "network-deployment" and is intended to be used with
 one of the Storage plugins in openQRM (e.g. lvm-storage, iscsi-storage, nfs-storage etc.).
<br>
<br>
<strong>Requirements:</strong>
<ul type="disc">
	<li>Please install the latest VMware vSphere Perl SDK on the openQRM Server system.</li>
	<li>Please enable and start the "dhcpd" and "tftpd" plugin</li>
	<li>Please enable and start one of the storage plugins</li>
	<li>Create volumes on the Storage</li>
	<li>Create "Images" from the created volumes</li>
</ul>

<br>
<br>
<strong>Workflow:</strong>
<ul type="disc">
	<li>Use the "Discovery" Manager to discover ESX Hosts in your network</li>
	<li>Integrate one or more discovered ESX Hosts into openQRM via the "Discovery" Manager</li>
	<li>Use the "DataStore Manager" to connect one or more NAS and/or iSCSI Storages</li>
	<li>Use the "vSwitch Manager" to configure the Networks and vSwitches on the ESX Host</li>
	<li>Use the "VM Manager" to create Virtual Machines</li>
</ul>
<br>

Created Virtual Machines will be automatically added and integerated into openQRM.
 They will come up as new, "idle" resources (white icon). The can now be used in combination
 with one of the network-deployment "Images" (as created in the Requirements section) via an "Appliance".
<br>
<br>

<strong>Please notice:</strong>
<ul type="disc">
	<li>Since this VMware ESX integration focus on "network-deployment" the (local) disk is used
 for swap space only. The actually root-disk (the "Image") is provided by one of the Storage plugins
 and dynamically attached by the Appliance start.</li>

	<li>Updating a VM re-creates its (local) disk on the Datastore. It will destroy all content on the disk!</li>
</ul>
<br>
<br>

<strong>This plugin is tested with VMware ESXi 4.1.0 in combination with the VMware vSphere Perl API 4.1.0</strong>

<br>
<br>
<br>
<br>
<br>


