<?php
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/

// This class represents a applicance managed by openQRM
// The applicance abstrations consists of the combination of 
// - 1 boot-image (kernel.class)
// - 1 (or more) server-filesystem/rootfs (image.class)
// - requirements (cpu-number, cpu-speed, memory needs, etc)
// - configuration (clustered, high-available, deployment type, etc)
// - available and required resources (resource.class)


$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/class/htmlobjects/htmlobject.class.php";


class htmlobject_openqrm extends htmlobject
{	
	function htmlobject_openqrm() {
		parent::htmlobject($_SERVER["DOCUMENT_ROOT"].'/openqrm/base/class/htmlobjects');
	}


	function box() {
		$html      = parent::box();
		$html->css = 'htmlobject_box';
		return $html;
	}


}
