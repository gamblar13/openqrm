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
<h1><img border=0 src="/openqrm/base/plugins/lxc-storage/img/volumes.png"> Deploy Logical Volume {lvm_lun_name} on Volume group {lvm_volume_group}</h1>
{lxc_templates_table}

<br>
<form action="{formaction}" method="GET">
<h1>Download lxc-template from URL</h1>
<div style="float:left;">
<b>Please insert the URL of your lxc-template!</b>
<br>
The lxc-template can be a tar.gz or .tgz file containing just the root-filesystem.
<br>
(without any lxc-configuration nor subdirectories)
<br>
The URL must be accessible from the openQRM Server.
<br>
<br>
{lxc_template_url}
</div>
{hidden_lvm_lun_name}
{hidden_lvm_volume_group}
{hidden_lvm_storage_id}
<div style="text-align:center;">
<br>
<br>
<br>
<br>
<br>
<br>
<br>
{submit}
</div>
</form>


