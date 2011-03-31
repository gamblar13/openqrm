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

<h1><img border=0 src="/openqrm/base/plugins/openvz-storage/img/manager.png"> OpenVZ VM Configuration</h1>
{backlink}  {reloadlink}
<h3>OpenVZ VM Network</h3>
{openvz_vm_network_table}
<form action="{formaction}" method="GET">
{hidden_openvz_server_id}
{hidden_openvz_server_name}
{hidden_openvz_vm_network_count}
<br>
{openvz_vm_mac_input}
{addnet}
</form>
<br>

<h3>OpenVZ VM Configuration</h3>
{openvz_parameter_table}
<h3>Adjust VM settings</h3>
<form action="{formaction}" method="GET">
{hidden_openvz_server_id}
{hidden_openvz_server_name}
{openvz_parameter_select}
{openvz_parameter_input}
{openvz_parameter_limit}
{submit}
</form>
