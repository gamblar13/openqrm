<!--
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->

<h1><img border=0 src="/openqrm/base/img/edit.png"> {title}</h1>
<form action="{thisfile}">
{form}
<div id='cloud_config_update_left'>
	{cloud_admin_email}
	{auto_provision}
	{external_portal_url}
	{request_physical_systems}
	{default_clone_on_deploy}
	{max_resources_per_cr}
	{auto_create_vms}
	{max_disk_size}
	{max_network_interfaces}
	{show_ha_checkbox}
	{show_puppet_groups}
	{auto_give_ccus}
	{max_apps_per_user}
	{public_register_enabled}
	{cloud_enabled}
	{cloud_billing_enabled}
	{show_sshterm_login}
	{cloud_nat}
	{show_collectd_graphs}
	{show_disk_resize}
	{show_private_image}
	{cloud_selector}
	{cloud_currency}
	{cloud_1000_ccus}
	{resource_pooling}
	{ip-management}

</div>

<div id='cloud_config_update_right'>
	{max-parallel-phase-one-actions}
	{max-parallel-phase-two-actions}
	{max-parallel-phase-three-actions}
	{max-parallel-phase-four-actions}
	{max-parallel-phase-five-actions}
	{max-parallel-phase-six-actions}
	{max-parallel-phase-seven-actions}
	{appliance_hostname}
	{cloud_zones_client}
	{cloud_zones_master_ip}
	{cloud_external_ip}
	{deprovision_warning}
	{deprovision_pause}

</div>

<div class="floatbreaker">&#160;</div>

<div id='cloud_config_update_bottom'>
	<div id='cloud_config_update_submit'>
		{submit}{cancel}
		<br><br>
	</div>
</div>
</form>


