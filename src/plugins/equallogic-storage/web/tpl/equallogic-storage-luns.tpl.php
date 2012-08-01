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
<h1><img border=0 src="/openqrm/base/plugins/equallogic-storage/img/plugin.png"> Equallogic-Storage {storage_name}</h1>
{storage_table}
<br><br>
{lun_table}
<br><br>
<form action="{formaction}" method="GET">
<h1>Add Equallogic Lun :</h1>
<div style="float:left;">
{equallogic_lun_name}
{equallogic_lun_size}
</div>
{hidden_equallogic_storage_id}
<div style="text-align:center;">{submit}</div>
</form>

