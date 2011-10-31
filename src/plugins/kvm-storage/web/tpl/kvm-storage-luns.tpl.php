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

    Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->
<h1><img border=0 src="/openqrm/base/plugins/kvm-storage/img/volumes.png"> Volumes at storage location <b>{kvm_storage_location}</b> on storage <b>{storage_name}</b></h1>
{lun_table}
<br><br>
<form action="{formaction}" method="GET">
<h1>Add new volume to storage location <b>{kvm_storage_location}</b></h1>
<div style="float:left;">
{kvm_volume_name}
{kvm_volume_size}
</div>
{hidden_kvm_storage_location}
{hidden_kvm_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

