# this is the openQRM vmware-esx-plugin Makefile
#
# This file is part of openQRM.
#
# openQRM is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2
# as published by the Free Software Foundation.
#
# openQRM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
#
# Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
#

export OPENQRM_SERVER_CONF=$(shell pwd)/../../etc/openqrm-server.conf

configure:

compile:
	../../make-assistant openqrm_cache_or_download vmNICManagement etc/build.conf/vmNICManagement.conf
	../../make-assistant openqrm_cache_or_download esxcfg-vswitch-lamw-mod etc/build.conf/esxcfg-vswitch-lamw-mod.conf
	../../make-assistant openqrm_cache_or_download addVMAdvParamOption etc/build.conf/addVMAdvParamOption.conf
	../../make-assistant openqrm_cache_or_download shutdownHostViaSOAPAPICall etc/build.conf/shutdownHostViaSOAPAPICall.conf

install:
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc
	. $(OPENQRM_SERVER_CONF) && cp etc/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d
	. $(OPENQRM_SERVER_CONF) && cp etc/init.d/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/ && chmod 700 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/init.d/*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/templates
	. $(OPENQRM_SERVER_CONF) && cp etc/templates/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/etc/templates/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/include
	. $(OPENQRM_SERVER_CONF) && cp include/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/include/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web
	. $(OPENQRM_SERVER_CONF) && cp web/*.txt $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/
	. $(OPENQRM_SERVER_CONF) && cp web/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/css
	. $(OPENQRM_SERVER_CONF) && cp web/css/*.css $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/css/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/class
	. $(OPENQRM_SERVER_CONF) && cp web/class/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/class/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/tpl
	. $(OPENQRM_SERVER_CONF) && cp web/tpl/*.php $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/tpl/
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/img
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/img/
	. $(OPENQRM_SERVER_CONF) && cp web/img/*.gif $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web/img/
	. $(OPENQRM_SERVER_CONF) && chmod 777 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/web
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin
	. $(OPENQRM_SERVER_CONF) && cp bin/openqrm* $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/openqrm*
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl
	. $(OPENQRM_SERVER_CONF) && cp bin/perl/*.pl $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/
	. $(OPENQRM_SERVER_CONF) && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/vmNICManagement/source/vmNICManagement.pl*=152 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/vmNICManagement.pl 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/vmNICManagement/source/vmNICManagement.pl*=152 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/vmNICManagement.pl; fi
	. $(OPENQRM_SERVER_CONF) && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/esxcfg-vswitch-lamw-mod/source/esxcfg-vswitch-lamw-mod.pl*=152 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/esxcfg-vswitch-lamw-mod.pl 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/esxcfg-vswitch-lamw-mod/source/esxcfg-vswitch-lamw-mod.pl*=152 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/esxcfg-vswitch-lamw-mod.pl; fi
	. $(OPENQRM_SERVER_CONF) && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/addVMAdvParamOption/source/addVMAdvParamOption.pl*=66 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/addVMAdvParamOption.pl 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/addVMAdvParamOption/source/addVMAdvParamOption.pl*=66 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/addVMAdvParamOption.pl; fi
	. $(OPENQRM_SERVER_CONF) && if ! cp $$OPENQRM_BUILD_TMP_DIR/openqrm-build/shutdownHostViaSOAPAPICall/source/shutdownHostViaSOAPAPICall.pl*=66 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/shutdownHostViaSOAPAPICall.pl 2>/dev/null; then cp ../../$$OPENQRM_BUILD_TMP_DIR/openqrm-build/shutdownHostViaSOAPAPICall/source/shutdownHostViaSOAPAPICall.pl*=66 $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/shutdownHostViaSOAPAPICall.pl; fi
	. $(OPENQRM_SERVER_CONF) && chmod +x $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/bin/perl/*.pl

	# menu icons
	. $(OPENQRM_SERVER_CONF) && mkdir -p $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/vmware-esx
	. $(OPENQRM_SERVER_CONF) && cp web/img/plugin.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/vmware-esx/
	. $(OPENQRM_SERVER_CONF) && cp web/img/manager.png $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/web/base/img/menu/vmware-esx/

uninstall:
	. $(OPENQRM_SERVER_CONF) && rm -rf $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx/*
	. $(OPENQRM_SERVER_CONF) && rmdir $(DESTINATION_DIR)$$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/vmware-esx

clean:

realclean:	clean

all:	configure compile
	
.PHONY: all configure compile install uninstall clean realclean
