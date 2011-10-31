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

 to debug add {?}


-->


<h1>{title}</h1>

<form action="{thisfile}">
{form}
<div id='vmware_esx_add_left_column'>

	{vmware_esx_ad_id}
	{vmw_esx_ad_ip}
	{vmw_esx_ad_mac}
	{vmw_esx_ad_hostname}
	{vmw_esx_ad_user}
	{vmw_esx_ad_password}
	{vmw_esx_ad_comment}

</div>
<div id='vmware_esx_add_right_column'>
	


</div>
<div id='vmware_esx_add_bottom'>
	<div id='vmware_esx_add_submit'>
		{submit}{cancel}
		<br><br>
	</div>
</div>

</form>
