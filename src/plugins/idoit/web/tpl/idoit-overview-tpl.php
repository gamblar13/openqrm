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


<h1><img border=0 src="/openqrm/base/plugins/idoit/img/plugin.png"> Automatic I-doit Configuration</h1>
<form action="{thisfile}" method="POST">
<br>
Click on the button below to automatic map and

<br>
document the openQRM network into I-doit.
<br>
<br>
Please notice this procedure will take some time.
<br>
You can check the status of this
<br>
action in the <a href="../../server/event/event-overview.php">event-list</a>
<br>
<br>
<input type='hidden' name='action' value='map'>
<input type='submit' value='Map openQRM Network'>
<br>
<br>
<br>
<br>
{automap}
<br>
<br>
<br>
<br>
</form>
