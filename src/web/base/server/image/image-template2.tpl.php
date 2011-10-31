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
	width:800px;
}
</style>
<h1>Select a Deployment Template</h1>
<form action="{formaction}" method="GET">
{local_deployment_hidden_image_id}
{local_deployment_hidden_storage_id}
{local_deployment_hidden_local_deployment_method}

	<div style="float:left;">
	{local_deployment_templates_select}
	{local_deployment_persistent}
	</div>
	<div style="float:right;">
	{local_deployment_additional_parameter}
	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>

	<div style="float:left;">
	<br>
	{submit}
	</div>
	<div style="float:right;">
	<br>
	</div>

</form>
