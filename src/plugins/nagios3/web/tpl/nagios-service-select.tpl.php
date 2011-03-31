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

    Copyright 2009, Matthias Rechenburg <matt@openqrm.com>
*/
-->
<h1><img border=0 src="/openqrm/base/plugins/nagios3/img/setup.png"> Manage Nagios Services</h1>

{nagios_service_table}

<hr>

<div style="float:left;">
<form action="{formaction}" method="GET">
<b>Please select service ports to add :</b>
<br>
{nagios_add_service_select_box}
<br>
{submit}
</form>
</div>

<div style="float:right;">
<form action="{formaction}" method="GET">
<br>
<b>Custom Service Name:</b>
<br>
<input type="text" name="nagios3_add_service_name" value="" />
<br>
<b>Custom Service Port:</b>
<br>
<input type="text" name="nagios3_add_service_port_arr[]" value="" />
<br>
<br>
{submit}
</form>
</div>
<div style="clear:both;line-height:0px;">&#160;</div>

<br>
<hr>
<br>
<br>
