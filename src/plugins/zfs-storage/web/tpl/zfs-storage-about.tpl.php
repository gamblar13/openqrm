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
<h1><img border=0 src="/openqrm/base/plugins/zfs-storage/img/plugin.png"> ZFS-storage plugin</h1>
<br>
The ZFS-storage plugin integrates ZFS-Target Storage into openQRM.
 It adds a new storage-type 'zfs-storage' and a new deployment-type 'iscsi-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>ZFS-storage type :</b>
<br>
A linux-box (resource) with the ZFS-target installed should be used to create
 a new Storage-server through the openQRM-GUI. The ZFS-storage system can be either
 deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
openQRM then automatically manages the ZFS-disks (Luns) on the ZFS-storage server.
<br>
<br>
<b>ZFS-deployment type :</b>
<br>
The ZFS-deployment type supports to boot servers/resources from the ZFS-stoage server.
 Server images created with the 'iscsi-root' deployment type are stored on Storage-server
 from the storage-server type 'zfs-storage'. During startup of an appliance they are directly
 attached to the resource as its rootfs via the iscsi-protokol.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Create an ZFS-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create a Disk-shelf on the ZFS-storage using the 'Luns' link (ZFS-plugin menu)
</li><li>
Create an (ZFS-) Image ('Add Image' in the Image-overview).
 Then select the ZFS-storage server and select an ZFS-device name as the images root-device.
</li><li>
Create an Appliance using one of the available kernel and the ZFS-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>


