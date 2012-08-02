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
<form action="{formaction}" method="GET">

<h1><img border=0 src="/openqrm/base/plugins/vbox/img/plugin.png"> VirtualBox Create VM</h1>

<div style="border: solid 1px #ccc; padding: 10px 10px 0 10px;">

<h4>Add new VM to VBOX Host id {vbox_server_id}</h4>
<div style="float:left;">
{vbox_server_name}

<h4>VM Configuration</h4>
{vbox_server_cpus}
{vbox_server_mac}
{vbox_server_ram}
{vbox_server_disk}
{vbox_server_swap}
</div>


<div style="float:right;">
    <div>
    <br>
    </div>
</div>

{hidden_vbox_server_id}

<div style="clear:both;line-height:0px;">&#160;</div>

<div style="text-align:center;">{submit}</div>
<br>
</div>

</form>

