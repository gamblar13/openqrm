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
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/lxc-storage/img/manager.png"> LXC VM Configuration</h1>
{backlink}  {reloadlink}
<br><br>
<h3>Adjust VM settings</h3>
<form action="{formaction}" method="GET">
{hidden_lxc_server_id}
{hidden_lxc_server_name}
{lxc_parameter_select}
{lxc_parameter_input}
{submit}
</form>

<br><br>
<h3>LXC VM Configuration</h3>
{lxc_parameter_table}
