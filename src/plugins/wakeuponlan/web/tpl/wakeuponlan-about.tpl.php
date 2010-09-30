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

    Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->
<h1><img border=0 src="/openqrm/base/plugins/wakeuponlan/img/plugin.png"> WAKEUPONLAN plugin</h1>
The "wakeuponlan" plugin integrates the "Wake up on LAN" (WOL) technology into openQRM.
It supports to wake up physical systems (resources) in openQRM by sending a "magic network package".
<br>
<br>
Via a provided "appliance start hook" the "wakeuponlan" plugin is capable to startup the appliance physical resources from the "off" state.
<br>
<br>
To use it please just enable "Wake up on LAN" in the BIOS of the physical resources in openQRM.
<br>
