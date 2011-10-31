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

<h1><img border=0 src="/openqrm/base/plugins/hybrid-cloud/img/plugin.png"> Account setup</h1>

{hybrid_cloud_table}

<hr>
<br>
<h4>To create a new Hybrid-Cloud Account please fill out the form with your Public-Cloud account data set</h4>
<form action="{thisfile}">
<div>
	<div style="float:left;">
	<br>
	<br>
	{hybrid_cloud_account_name}
	{hybrid_cloud_rc_config}
	{hybrid_cloud_ssh_key}
	{hybrid_cloud_description}
	{hybrid_cloud_account_type_select}
	<br>
	</div>
	<div style="float:right;">
	<br>
	<b>Help</b>
	<br>
	The Cloud rc-config file (on openQRM at e.g. /home/cloud/.eucarc)
	<br>
	should define all parameters for the public cloud tools
	<br>
	(e.g. ec2-ami-tools, ec2-api-tools or euca2ools) to work seamlessly.
	<br>
	A typical rc-config file for UEC looks similar to <a href="hybrid-cloud-example-rc-config.php" target="_BLANK">this</a>.
	<br>
	The Cloud ssh-key (on openQRM at e.g. /home/cloud/.euca/mykey.priv)
	<br>
	provides the console login to the Public Cloud systems.
	</div>
	<div style="clear:both;line-height:0px;">&#160;</div>
		{submit_save}
</div>
<hr>
</form>
