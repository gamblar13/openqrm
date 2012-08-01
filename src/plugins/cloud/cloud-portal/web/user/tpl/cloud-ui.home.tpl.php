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

<style type="text/css">
	.pageturn_middle {
		visibility:hidden;
	}
	.pageturn_box {
		height: 0px;
	}

	* {
		margin: 0;
		padding: 0;
	}

	hr {
		width: 350px;
	}
	img {
		border: none;
	}

	#ImageBoxOverlay {
		background-color: #999999;
		z-index: 1000;
	}
	#ImageBoxOuterContainer {
		z-index: 1000;
	}
	#ImageBoxCaption {
		background-color: #F4F4EC;
	}
	#ImageBoxContainer {
		width: 250px;
		height: 250px;
		background-color: #F4F4EC;
	}
	#ImageBoxCaptionText {
		font-weight: bold;
		padding-bottom: 5px;
		font-size: 13px;
		color: #000;
	}
	#ImageBoxCaptionImages {
		margin: 0;
	}
	#ImageBoxNextImage {
		background-image: url(img/spacer.gif);
		background-color: transparent;
	}
	#ImageBoxPrevImage {
		background-image: url(img/spacer.gif);
		background-color: transparent;
	}
	#ImageBoxNextImage:hover {
		background-image: url(img/next_image.jpg);
		background-repeat:	no-repeat;
		background-position: right top;
	}
	#ImageBoxPrevImage:hover {
		background-image: url(img/prev_image.jpg);
		background-repeat:	no-repeat;
		background-position: left bottom;
	}

	#carousel {
		position: absolute;
		left: 130px;
		width: 450px;
		height: 240px;
		background: #ffffff url(img/background.png) repeat-x scroll 35px 20px
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
		font-weight: bold;
		font-size: 15px;
		display: block;
		clear: both;

	}


</style>


<script type="text/javascript">

		window.onload = function() {
			$('#carousel').Carousel(
				{
					itemWidth: 130,
					itemHeight: 60,
					itemMinWidth: 50,
					items: 'a',
					reflections: 1,
					rotationSpeed: 1
				}
			);
			$.ImageBox.init(
				{
					loaderSRC: 'img/forward.gif',
					closeHTML: '<img src="img/forward.gif" />'
				}
			);
		};
</script>

<div id="content_container">

<form action="{thisfile}">

<div id="carousel">

<a href="index.php?project_tab_ui=1&cloud_ui=create" title="<div id=no_action></div>" rel="imagebox">
<img src="img/add.png" title="" width="32" height="32">
<span class="label">{cloud_ui_create}</span>
</a>

<a href="index.php?project_tab_ui=2&cloud_ui=requests" title="<div id=no_action></div>" rel="imagebox">
<img src="img/manage.png" title="" width="32" height="32">
<span class="label">{cloud_ui_requests}</span>
</a>

<a href="index.php?project_tab_ui=3&cloud_ui=appliances" title="<div id=no_action></div>" rel="imagebox">
<img src="img/plugin.png" title="" width="32" height="32">
<span class="label">{cloud_ui_appliances}</span>
</a>

<a href="index.php?project_tab_ui=4&cloud_ui=account" title="<div id=no_action></div>" rel="imagebox">
<img src="img/user.png" title="" width="32" height="32">
<span class="label">{cloud_ui_account}</span>
</a>

<a href="index.php?project_tab_ui=5&cloud_ui=profiles" title="<div id=no_action></div>" rel="imagebox">
<img src="img/resource.png" title="" width="32" height="32">
<span class="label">{cloud_ui_profiles}</span>
</a>

<a href="index.php?project_tab_ui=9&cloud_ui=transaction" title="<div id=no_action></div>" rel="imagebox">
<img src="img/locked.png" title="" width="32" height="32">
<span class="label">{cloud_ui_transaction}</span>
</a>

<a href="#" onClick="javascript:window.open('vcd/index.php','','location=0,status=0,scrollbars=1,width=910,height=740,left=100,top=50,screenX=100,screenY=50');" title="<div id=no_action></div>" rel="imagebox">
<img src="img/plugin.png" title="" width="32" height="32">
<span class="label">{cloud_ui_vcd}</span>
</a>


<a href="#" onClick="javascript:window.open('vid/index.php','','location=0,status=0,scrollbars=1,width=920,height=820,left=300,top=50,screenX=300,screenY=50');" title="<div id=no_action></div>" rel="imagebox">
<img src="img/plugin.png" title="" width="32" height="32">
<span class="label">{cloud_ui_vid}</span>
</a>

</div>

</form>

</div>
