<!--
/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
*/
-->
<h1><img border=0 src="/openqrm/base/plugins/hybrid-cloud/img/plugin.png"> Hybrid-Cloud plugin</h1>
The hybrid-cloud-plugin provides a seamless migration-path "from" and "to" Public-Cloud Providers such as Amazone EC2, Ubuntu Enterprise Cloud and Eucalyptus.
<br>
<br>
<strong>Configure Hybrid-Cloud Account</strong>
<br>
Create a new Hybrid-Cloud Account configuration using the "Accounts" menu item.
<br>
The following informations are required :
<br>
<ul type="disc">
    <li>Hybrid-Cloud Account Name</li>
    <li>rc-config (file)</li>
    <li>SSH-Key (file)</li>
    <li>Description</li>
</ul>

The rc-config file is typically provided by the Public-Cloud Provider.
This rc-config file (installed on openQRM at e.g. /home/cloud/.eucarc)
should define all parameters for the public cloud tools
(e.g. ec2-ami-tools, ec2-api-tools or euca2ools) to work seamlessly.
<br>
<br>
A typical rc-config file for UEC looks similar to <a href="/openqrm/base/plugins/hybrid-cloud/hybrid-cloud-example-rc-config.php" title="A sample rc-config file containing the Cloud Account configuration" target="_blank">this</a>.
<br>
<br>
The Cloud ssh-key (on openQRM at e.g. /home/cloud/.euca/mykey.priv)
provides the console login to the Public Cloud systems.
<br>
<br>
<strong>Import Servers from Hybrid-Cloud</strong>
<br>
To import an Cloud Server (-> the AMI of an active EC2 Instance) follow the steps below :
<br>
<ul type="disc">
    <li>Select an Hybrid-Cloud Account to use for the import</li>
    <li>Select an active Public-Cloud Instance running the AMI to import</li>
    <li>Select an (empty) openQRM Server image (from type NFS- or LVM-NFS)</li>
</ul>
<br>
This will automatically import the AMI from the selcted Public-Cloud Instance into the (previously created) empty Server Image in openQRM.
<br>
<br>
The imported AMI now can be used with all existing "resource-types" in openQRM so e.g. it can now also
 run on a physical system or on any other virtulization type.
<br>
<br>

<strong>Export Servers to Hybrid-Cloud</strong>
<br>
To export an openQRM Image to a Public-Cloud Server as an AMI follow the steps below :
<br>
<ul type="disc">
    <li>Select an Hybrid-Cloud Account to use for the export</li>
    <li>Select the Image (from type NFS- or LVM-NFS) to turn into an AMI for the export</li>
    <li>Provide a name for the AMI, its size and architecture</li>
</ul>
<br>
This will automatically export the selected openQRM Image to the Public-Cloud Provider.
<br>
It will be available as new AMI as soon as the transfer procedure finished.
<br>
<br>
<br>



