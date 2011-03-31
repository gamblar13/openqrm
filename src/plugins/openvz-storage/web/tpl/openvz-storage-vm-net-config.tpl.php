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

	Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/openvz-storage/img/manager.png"> OpenVZ VM Network Configuration</h1>
{backlink}  {reloadlink}
<br><br>
<h3>VM Network settings</h3>

{openvz_network_table}

<form action="{formaction}" method="GET">
<h3>Add/Remove network card</h3>
<div id="nic_input">
	{openvz_new_nic_input}
	{hidden_openvz_server_id}
	{hidden_openvz_server_name}
	{hidden_openvz_nic_number}
	{openvz_storage_vm_bridge}
</div>
<div id="nic_submit">
	{submit}   {remove_nic}
</div>
</form>

<br>

