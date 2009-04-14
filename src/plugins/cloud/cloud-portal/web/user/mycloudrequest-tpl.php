<style>
.htmlobject_tab_box {
	width:850px;
}
</style>
<form action="{formaction}">

{currentab}

<h1>Create new Cloud Request</h1>

{subtitle}

<div>
	<div style="float:left;">

	{cloud_user}
	{cloud_request_start}
	<br>
	{cloud_request_stop}
	<br>

	{cloud_resource_quantity}
	{cloud_resource_type_req}
	{cloud_kernel_id}
	{cloud_image_id}
	{cloud_ram_req}
	{cloud_cpu_req}
	{cloud_disk_req}
	{cloud_network_req}
	{cloud_ha}
	{cloud_clone_on_deploy}

	{cloud_command}

	</div>

	<div style="float:right;">
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		<b><u>Global Cloud Limits</u></b>
        <br>
        <small>(set by the Cloud-Administrator)</small>
        <br>
		{cloud_global_limits}
		</div>
        <br>
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
		<b><u>Cloud User Limits</u></b>
        <br>
        <small>(0 = no limit set)</small>
        <br>
		{cloud_user_limits}
		</div>

    </div>

	<div style="float:right;">
		<div style="padding: 10px 10px 0 10px;">
		&nbsp;&nbsp;&nbsp;
		</div>
	</div>

	<div style="float:right;">
		<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">
        <b><u>Applications</u></b>
        <br>
		{cloud_show_puppet}
		</div>
	</div>



<div style="clear:both;line-height:0px;">&#160;</div>
</div>
<div style="text-align:center;">{submit_save}</div>

</form>
