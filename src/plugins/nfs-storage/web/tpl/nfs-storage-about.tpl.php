<h1><img border=0 src="/openqrm/base/plugins/nfs-storage/img/plugin.png"> Nfs-storage plugin</h1>
<br>
The Nfs-storage plugin integrates Nfs Storage-servers into openQRM.
 It adds a new storage-type 'nfs-storage' and a new deployment-type 'nfs-root' to
 the openQRM-server during initialization.
<br>
<br>
<b>Nfs-storage type :</b>
<br>
A linux-box (resource) with 'nfs-server' installed should be used to create
 a new Storage-server through the openQRM-GUI. The Nfs-storage system can be either
 deployed via openQRM or integrated into openQRM with the 'local-server' plugin.
openQRM then automatically manages the exports on the Nfs-storage server.
<br>
<br>
<b>Nfs-deployment type :</b>
<br>
The Nfs-deployment type supports to boot servers/resources from the Nfs-stoage server.
 Server images created with the 'nfs-root' deployment type are stored on Storage-server
 from the storage-server type 'nfs-storage'. During startup of an appliance they are directly
 attached to the resource as its rootfs via the nfs-protokol.
<br>
<br>
<b>How to use :</b>
<br>
<ul>
<li>
Create an Nfs-storage server via the 'Storage-Admin' (Storage menu)
</li><li>
Create an new nfs-export on the Nfs-storage using the 'Exports' link (Nfs-plugin menu)
</li><li>
Create an (Nfs-) Image ('Add Image' in the Image-overview).
 Then select the Nfs-storage server and choose one of the Nfs-storage-devices as the 'root-device'.
</li><li>
Create an Appliance using one of the available kernel and the Nfs-Image created in the previous steps.
</li><li>
Start the Appliance
</li>
</ul>
<br>
<br>

