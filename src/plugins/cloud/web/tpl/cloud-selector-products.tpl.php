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
			$('#carousel').Carousel(
				{
					itemWidth: 130,
					itemHeight: 60,
					itemMinWidth: 50,
					items: 'a',
					reflections: 0,
					rotationSpeed: 0.5
				}
			);

		};
</script>

<style>
.htmlobject_tab_box {
	width:600px;
}

a {
	text-decoration:none
}


#cloudselector_title {
    height: 40px;
    left: 95px;
    position: absolute;
    top: 20px;
}

#carousel {
	position: absolute;
	top: 300px;
	left: 380px;
	width: 600px;
	height: 220px;
	background: #ffffff url(/openqrm/base/img/background.png) no-repeat scroll 140px 80px
}
#carousel a {
	position: absolute;
	width: 110px;
	color: #666666;
	text-decoration: none;
	font-weight: bold;
	text-align: center;

}
#carousel a .label {
	display: block;
	clear: both;

}

</style>



<h1><img border=0 src="/openqrm/base/plugins/cloud/img/plugin.png"> {title} <small><a href="{external_portal_name}" target="_BLANK">{external_portal_name}</a></small></h1>


<div id="carousel">

<div id="cpu" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=cpu"><img src="/openqrm/base/plugins/cloud/img/calculator.png" border="0" width="24" height="24" alt="cloudproduct_cpu"/>
<br><strong>{cloud_selector_cpu}</strong></a>
</div>

<div id="disk" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=disk">&nbsp;<img src="/openqrm/base/img/deployment.png" border="0" width="24" height="24" alt="cloudproduct_disk"/>
<br><strong>{cloud_selector_disk}</strong></a>
</div>

<div id="ha" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=ha"><img src="/openqrm/base/img/ha.png" border="0" width="24" height="24" alt="cloudproduct_ha"/>
<br><strong>{cloud_selector_ha}</strong></a>
</div>

<div id="kernel" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=kernel">&nbsp;&nbsp;<img src="/openqrm/base/img/kernel.png" border="0" width="24" height="24" alt="cloudproduct_kernel"/>
<br><strong>{cloud_selector_kernel}</strong></a>
</div>

<div id="memory" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=memory">&nbsp;&nbsp;&nbsp;<img src="/openqrm/base/img/monitoring.png" border="0" width="24" height="24" alt="cloudproduct_memory"/>
<br><strong>{cloud_selector_memory}</strong></a>
</div>

<div id="network" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=network">&nbsp;&nbsp;&nbsp;<img src="/openqrm/base/img/datacenter.png" border="0" width="24" height="24" alt="cloudproduct_network"/>
<br><strong>{cloud_selector_network}</strong></a>
</div>

<div id="puppet" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=puppet">&nbsp;&nbsp;&nbsp;<img src="/openqrm/base/img/datacenter.png" border="0" width="24" height="24" alt="cloudproduct_puppet"/>
<br><strong>{cloud_selector_puppet}</strong></a>
</div>

<div id="resource" class="link">
<a href="index.php?plugin=cloud&controller=cloud-selector&cloud_selector=resource">&nbsp;&nbsp;&nbsp;<img src="/openqrm/base/img/virtualization.png" border="0" width="24" height="24" alt="cloudproduct_resource"/>
<br><strong>{cloud_selector_resource}</strong></a>
</div>


</div>


