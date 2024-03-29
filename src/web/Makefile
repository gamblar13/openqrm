# this is the openQRM web Makefile
#
# openQRM Enterprise developed by openQRM Enterprise GmbH.
#
# All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
# The latest version of this license can be found here: src/doc/LICENSE.txt
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://openqrm-enterprise.com
#
# Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
#

export OPENQRM_SERVER_CONF=$(shell pwd)/../etc/openqrm-server.conf

configure:

compile:
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_htmlobjects
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_adodb
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_jquery_bundle
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_js_interface
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_jqclock
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_phplib
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_treemenu_js
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_get_treemenu_icons

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web
	. $(OPENQRM_SERVER_CONF) && cp *.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/
	# action
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/action
	. $(OPENQRM_SERVER_CONF) && cp action/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/action/
	# base
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base
	. $(OPENQRM_SERVER_CONF) && cp base/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/
	# base/include
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include
	. $(OPENQRM_SERVER_CONF) && cp base/include/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/include/
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_adodb
	# base/js
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/js
	. $(OPENQRM_SERVER_CONF) && cp base/js/*.js $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/js/
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_jquery_bundle
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_js_interface
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_jqclock
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_treemenu_js
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_treemenu_icons
	# base/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class
	. $(OPENQRM_SERVER_CONF) && cp base/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class/
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_phplib
	# base/class/htmlobjects
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/class/htmlobjects
	. $(OPENQRM_SERVER_CONF) && ../make-assistant openqrm_install_htmlobjects
	# base/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/lang
	. $(OPENQRM_SERVER_CONF) && cp base/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/lang/
	# base/tpl
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/tpl/
	# base/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css
	. $(OPENQRM_SERVER_CONF) && cp base/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/css/
	# base/img/menu
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu
	. $(OPENQRM_SERVER_CONF) && cp base/img/plugin.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/datacenter.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/image.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/kernel.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/resource.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/storage.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/active.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/transition.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/error.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/unknown.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/idle.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/add.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/appliance.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/
	. $(OPENQRM_SERVER_CONF) && cp base/img/*.gif $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/
	. $(OPENQRM_SERVER_CONF) && cp base/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/
	# base/server
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server
	# base/server/aa_server
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/aa_server/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/aa_server/tpl/
	# base/server/resource
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/resource/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/resource/tpl/
	# base/server/user
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/user/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/user/tpl/
	# base/server/image
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/image/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/image/tpl/
	# base/server/appliance
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/appliance/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/appliance/tpl/
	# base/server/storage
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/tpl/
	. $(OPENQRM_SERVER_CONF) && cp base/server/storage/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/storage/lang/
	# base/server/kernel
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/kernel/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/kernel/tpl/
	# base/server/event
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/img
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/tpl/
	. $(OPENQRM_SERVER_CONF) && cp base/server/event/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/lang/
	#. $(OPENQRM_SERVER_CONF) && cp base/server/event/img/*.* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/event/img/
	# base/server/zz_documentation
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/class
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/css
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/lang
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/img
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/server/zz_documentation/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/
	. $(OPENQRM_SERVER_CONF) && cp base/server/zz_documentation/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/class/
	. $(OPENQRM_SERVER_CONF) && cp base/server/zz_documentation/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/css/
	. $(OPENQRM_SERVER_CONF) && cp base/server/zz_documentation/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/lang/
	. $(OPENQRM_SERVER_CONF) && cp base/server/zz_documentation/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/server/zz_documentation/tpl/
	# base/plugins/aa_plugins
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/css
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/css/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/class
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/class/
	#. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/img
	#. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/img/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/lang
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/lang/*.ini $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/lang/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/tpl
	. $(OPENQRM_SERVER_CONF) && cp base/plugins/aa_plugins/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/plugins/aa_plugins/tpl/

	echo "Creating the openqrm-client boot-service package"
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/bin
	. $(OPENQRM_SERVER_CONF) && cp -a ../bin/openqrm-cmd $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/bin/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin
	. $(OPENQRM_SERVER_CONF) && cp -a ../sbin/openqrm* $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/sbin/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/include
	. $(OPENQRM_SERVER_CONF) && cp -a ../include/openqrm-functions $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/include/
	. $(OPENQRM_SERVER_CONF) && cp -a ../include/openqrm-package-functions $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/
	. $(OPENQRM_SERVER_CONF) && cp -a ../etc/init.d/openqrm-client $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/
	. $(OPENQRM_SERVER_CONF) && chmod +x $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/$$OPENQRM_RESOURCE_BASE_DIR/openqrm/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && tar -C 	$$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client/ -czf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-client.tgz `echo $$OPENQRM_RESOURCE_BASE_DIR | cut -d'/' -f2-`
	. $(OPENQRM_SERVER_CONF) && chmod 744 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/boot-service/openqrm-client.tgz
	rm -rf $$OPENQRM_BUILD_TMP_DIR/openqrm-build/openqrm-client
	
uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_WEBSERVER_DOCUMENT_ROOT/openqrm
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/*
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
