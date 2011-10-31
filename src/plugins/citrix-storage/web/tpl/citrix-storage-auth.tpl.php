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
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>
<form action="{formaction}" method="GET">

<h1><img border=0 src="/openqrm/base/plugins/citrix-storage/img/manager.png"> Authenticate Citrix-storage XenServer {citrix_storage_server_id}</h1>

<div style="float:left;">
	{citrix_storage_server_user}
	{citrix_storage_server_password}
</div>
{hidden_citrix_storage_server_id}
{hidden_citrix_storage_server_ip}
{hidden_action}
<div style="text-align:center;">
	{submit}
	<br>
	<br>
	<br>
	<br>
	<strong>{backlink}</strong>
</div>



</form>
