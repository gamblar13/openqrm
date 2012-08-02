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
<style>
.htmlobject_tab_box {
	width:700px;
}
</style>

<h1><img border=0 src="/openqrm/base/plugins/xen-storage/img/plugin.png"> Xen Storage VM NET Configuration</h1>
{backlink}
<br>

<form action="{thisfile}" method="post">
{vm_config_nic1_disp}
<br>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_nic2_disp}
</form>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_nic3_disp}
</form>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_nic4_disp}
</form>
<br><hr><br>

<form action="{thisfile}" method="post">
{vm_config_nic5_disp}
</form>
<br><hr><br>

</form>


<form action="{thisfile}" method="post">
<div style="float:left;">
{vm_config_add_nic_disp}  {vm_config_nic_bridge}
</div>

<div style="float:right;">
<br>
</div>
<div style="clear:both;line-height:0px;">&#160;</div>
{submit}
</form>


