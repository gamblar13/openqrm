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
<script type="text/javascript">

		window.onload = function() {
		<!-- user details slider -->
		$(".flip").click(function(){
			$(".panel").slideToggle("slow");
			var panelheight = $(".panel").height();
			if (panelheight == 0) {
				$('#cloud_account_user_image').hide();
				$('#cloud_account_user_space').width(1);
			} else {
				$('#cloud_account_user_image').show();
				$('#cloud_account_user_space').width(150);
			}
		});
		$('#cloud_usergroup').attr('disabled', true);
		$('#cloud_user_name').attr('disabled', true);


		};
</script>

<style type="text/css">
div.panel,p.flip {
	margin:0px;
	padding:5px;
	text-align:center;
	background:#EEEEEE;
	border:solid 1px #c3c3c3;
}
div.panel {
	height:270px;
	display:none;
}


</style>

<div id="content_container">

<h1>{title}</h1>
<form action="{thisfile}">
{account_details}


<div id="cloud_account_left_column">
	{cu_cg_id}
	{cu_name}
	{cu_password}
	{cu_email}
	{cu_forename}
	{cu_lastname}
	{cu_street}
	{cu_city}
	{cu_country}
	{cu_phone}
	<div id="cloud-account-submit">{submit}</div>
</div>

<div id="cloud_account_right_column">
	<div class="panel">
		<p><h3>{cloud_user_details}</h3></p>
		<p>{cloud_user_ccus} : {cloud_user_ccus_value}</p>
		<p>{cloud_user_lang} : {cloud_user_lang_value}</p>

		<p><h3>{cloud_user_limits}</h3></p>
		<p>{cloud_userlimit_resource_limit} : {cloud_userlimit_resource_limit_value}</p>
		<p>{cloud_userlimit_memory_limit} : {cloud_userlimit_memory_limit_value}</p>
		<p>{cloud_userlimit_disk_limit} : {cloud_userlimit_disk_limit_value}</p>
		<p>{cloud_userlimit_cpu_limit} : {cloud_userlimit_cpu_limit_value}</p>
		<p>{cloud_userlimit_network_limit} : {cloud_userlimit_network_limit_value}</p>

	</div>
	<p class="flip"><b>{show_details}</b></p>

</div>


<div id="cloud_account_user_space">
	<img id="cloud_account_user_image" src="img/locked.png" alt="Details"/>
</div>

</div>

</form>

